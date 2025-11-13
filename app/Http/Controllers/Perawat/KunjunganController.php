<?php

namespace App\Http\Controllers\Perawat;

use App\Http\Controllers\Controller;
use App\Models\EMR;
use App\Models\Kunjungan;
use App\Models\Perawat;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;

class KunjunganController extends Controller
{
    public function index()
    {
        return view('perawat.kunjungan.kunjungan');
    }

    public function getDataKunjunganHariIni(Request $request)
    {
        $userId = Auth::id();

        $perawat = Perawat::with('dokter', 'poli')->where('user_id', $userId)->first();

        // Kalau belum di-set dokter_id / poli_id → balikin data kosong
        if (!$perawat || empty($perawat->dokter_id) || empty($perawat->poli_id)) {
            return response()->json(['data' => []]);
        }

        $tz         = config('app.timezone', 'Asia/Jakarta');
        $todayLocal = Carbon::today($tz);
        $today      = $todayLocal->toDateString();

        // Ambil nama dokter & poli via JOIN supaya pasti muncul
        $rows = Kunjungan::query()
            ->with([
                'pasien',
                'dokter',
                'poli',
            ])
            ->whereDate('tanggal_kunjungan', $today)
            ->where('status', 'Waiting')
            ->where('dokter_id', $perawat->dokter_id)
            ->where('poli_id',   $perawat->poli_id)
            ->orderByRaw('CAST(no_antrian AS UNSIGNED)')
            ->get()
            ->map(function ($k) {
                return [
                    'kunjungan_id' => $k->id,
                    'pasien_id'    => $k->pasien_id,
                    'dokter_id'    => $k->dokter_id,
                    'poli_id'      => $k->poli_id,
                    'no_antrian'   => $k->no_antrian ?? '-',
                    'nama_pasien'  => $k->pasien->nama_pasien ?? '-',
                    'dokter'       => $k->dokter->nama_dokter ?? '-', // ← pasti ada dari JOIN
                    'poli'         => $k->poli->nama_poli ?? '-',
                    'keluhan'      => $k->keluhan_awal ?? '-',
                ];
            });

        return response()->json(['data' => $rows]);
    }

    public function updateStatusKunjunganKeEngaged($id)
    {
        $userId = Auth::id();

        // Ambil perawat yang login (untuk pembatasan scope)
        $perawat = Perawat::with('dokter', 'poli')->where('user_id', $userId)->firstOrFail();

        try {
            DB::transaction(function () use ($id, $perawat) {
                // Lock row agar aman dari balapan klik
                $k = Kunjungan::query()
                    ->lockForUpdate()
                    ->findOrFail($id);

                // Status harus Waiting
                if ($k->status !== 'Waiting') {
                    throw ValidationException::withMessages([
                        'status' => 'Kunjungan tidak berada pada status Waiting.',
                    ]);
                }

                // Jika perawat punya mapping, wajib cocok
                if (!empty($perawat->dokter_id) && (int)$k->dokter_id !== (int)$perawat->dokter_id) {
                    throw new AuthorizationException('Kunjungan bukan untuk dokter yang ditangani perawat ini.');
                }
                if (!empty($perawat->poli_id) && (int)$k->poli_id !== (int)$perawat->poli_id) {
                    throw new AuthorizationException('Kunjungan bukan untuk poli yang ditangani perawat ini.');
                }

                // Update status → Engaged
                $k->update([
                    'status'     => 'Engaged',
                    'engaged_at' => now(),
                ]);

                // (Opsional) buat EMR header jika ingin dibuat di sini
                EMR::firstOrCreate([
                    'kunjungan_id'  => $k->id,
                    'pasien_id'     => $k->pasien_id,
                    'dokter_id'     => $k->dokter_id,
                    'poli_id'       => $k->poli_id,
                    'perawat_id'    => $perawat->id,
                    'keluhan_utama' => $k->keluhan_awal,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Status kunjungan diperbarui menjadi Engaged.',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first() ?? 'Validasi status gagal.',
            ], 422);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Anda tidak berwenang memproses kunjungan ini.',
            ], 403);
        } catch (\Throwable $e) {
            Log::error('Engage error', ['id' => $id, 'err' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengubah status.',
            ], 500);
        }
    }

    // Sumber data untuk DataTables (AJAX, client-side)
    public function getDataKunjunganDenganStatusEngaged(Request $request)
    {
        $userId  = Auth::id();
        $perawat = Perawat::with(['dokter', 'poli'])->where('user_id', $userId)->firstOrFail();

        if (empty($perawat->dokter_id) || empty($perawat->poli_id)) {
            return DataTables::of(collect())->make(true);
        }

        $dataKunjunganEngaged = Kunjungan::with(['pasien', 'dokter', 'poli', 'perawat'])->where('status', 'Engaged')->where()->get();

        return DataTables::of($dataKunjunganEngaged)
            ->addIndexColumn()
            // ✅ pakai emr_id buat route action
            ->addColumn('action', function ($row) {
                if (!empty($row['emr_id'])) {
                    $url = route('perawat.form.pengisian.vital.sign', $row['emr_id']);
                    return '
                    <a href="' . $url . '"
                       class="inline-flex items-center px-3 py-1 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                       Proses
                    </a>
                ';
                }
                // fallback kalau EMR belum ada
                return '
                <span class="inline-flex items-center px-3 py-1 rounded-lg bg-gray-300 text-gray-600 cursor-not-allowed"
                      title="EMR belum dibuat">
                      EMR belum ada
                </span>
            ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    // Stub: halaman khusus vital sign (nanti kamu isi)
    public function formPengisianVitalSign($id)
    {
        // Ambil EMR + relasi yang diperlukan (pilih kolom seperlunya biar irit)
        $emr = Emr::query()
            ->select('id', 'kunjungan_id', 'resep_id', 'tekanan_darah', 'suhu_tubuh', 'nadi', 'pernapasan', 'saturasi_oksigen')
            ->with([
                'kunjungan:id,pasien_id,poli_id,jadwal_dokter_id',
                'kunjungan.pasien' => function ($q) {
                    $q->select(
                        'id',
                        'no_emr',        // kalau ada
                        'nama_pasien',
                        'tanggal_lahir', // kalau ada
                        'jenis_kelamin', // kalau ada
                        'alamat',       // kalau ada
                        'no_hp_pasien'       // kalau ada
                    );
                },
                'kunjungan.poli:id,nama_poli',
                'kunjungan.jadwalDokter.dokter:id,nama_dokter',
            ])
            ->findOrFail((int)$id);

        // Ambil objek pasien dari relasi kunjungan (bisa null-safe)
        $pasien = optional($emr->kunjungan)->pasien;

        // return $emr;

        // (Opsional) keamanan: perawat hanya boleh akses EMR poli/dokter yang sesuai
        // $perawat = \App\Models\Perawat::where('user_id', Auth::id())->first();
        // if ($perawat && ($perawat->poli_id != optional($emr->kunjungan)->poli_id)) abort(403);

        return view('perawat.kunjungan.form-pengisian-vital-sign', compact('emr', 'pasien'));
    }
}
