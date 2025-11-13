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
            DB::transaction(function () use ($id, $perawat,) {
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
                EMR::firstOrCreate(
                    ['kunjungan_id' => $k->id],
                    ['pasien_id' => $k->pasien_id],
                );
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
        $perawat = Perawat::select(['id', 'user_id', 'dokter_id', 'poli_id'])
            ->where('user_id', $userId)->firstOrFail();

        if (empty($perawat->dokter_id) || empty($perawat->poli_id)) {
            return DataTables::of(collect())->make(true);
        }

        $tz    = config('app.timezone', 'Asia/Jakarta');
        $today = Carbon::now($tz)->toDateString();

        // ✅ tambahkan join ke EMR (ambil id emr)
        $raw = DB::table('kunjungan as k')
            ->leftJoin('emr as e', 'e.kunjungan_id', '=', 'k.id') // <— ini baru
            ->leftJoin('jadwal_dokter as jd', 'jd.id', '=', 'k.jadwal_dokter_id')
            ->leftJoin('dokter as d', 'd.id', '=', 'jd.dokter_id')
            ->leftJoin('poli as p', 'p.id', '=', 'k.poli_id')
            ->leftJoin('pasien as s', 's.id', '=', 'k.pasien_id')
            ->whereDate('k.tanggal_kunjungan', $today)
            ->where('k.status', 'Engaged')
            ->where('k.poli_id', $perawat->poli_id)
            ->orderByRaw('CAST(k.no_antrian AS UNSIGNED)')
            ->get([
                'k.id as kunjungan_id',
                'k.no_antrian',
                'k.keluhan_awal',
                'k.poli_id',
                'k.jadwal_dokter_id',
                's.nama_pasien',
                'p.nama_poli',
                'd.id as dokter_id_by_jd',
                'd.nama_dokter as dokter_nama_by_jd',
                'e.id as emr_id', // <— ini baru
            ]);

        $needCacheLookup = [];
        foreach ($raw as $r) {
            if (empty($r->dokter_id_by_jd)) {
                $needCacheLookup[] = (int)$r->kunjungan_id;
            }
        }

        $cacheMapDokterId = [];
        if (!empty($needCacheLookup)) {
            foreach ($needCacheLookup as $kid) {
                $c = Cache::get("kunjungan_dokter:{$kid}");
                if ($c && !empty($c['dokter_id'])) {
                    $cacheMapDokterId[$kid] = (int)$c['dokter_id'];
                }
            }
        }

        $uniqueCacheDokterIds = array_values(array_unique(array_filter(array_values($cacheMapDokterId))));
        $dokterNamaFromCache = [];
        if (!empty($uniqueCacheDokterIds)) {
            $dokList = DB::table('dokter')->whereIn('id', $uniqueCacheDokterIds)
                ->pluck('nama_dokter', 'id');
            $dokterNamaFromCache = $dokList ? $dokList->toArray() : [];
        }

        $rows = collect($raw)->filter(function ($r) use ($perawat, $cacheMapDokterId) {
            $dokByJd    = $r->dokter_id_by_jd ?? null;
            $dokByCache = $cacheMapDokterId[$r->kunjungan_id] ?? null;

            return (
                (!empty($dokByJd)    && (int)$dokByJd    === (int)$perawat->dokter_id) ||
                (!empty($dokByCache) && (int)$dokByCache === (int)$perawat->dokter_id)
            );
        })->map(function ($r) use ($cacheMapDokterId, $dokterNamaFromCache) {
            $namaDokter = $r->dokter_nama_by_jd
                ?? ($dokterNamaFromCache[$cacheMapDokterId[$r->kunjungan_id] ?? 0] ?? null)
                ?? '-';

            return [
                'kunjungan_id' => $r->kunjungan_id,
                'emr_id'       => $r->emr_id,            // <— simpan emr_id ke row
                'no_antrian'   => $r->no_antrian ?? '-',
                'nama_pasien'  => $r->nama_pasien ?? '-',
                'dokter'       => $namaDokter,
                'poli'         => $r->nama_poli ?? '-',
                'keluhan'      => $r->keluhan_awal ?? '-',
            ];
        })->values();

        return DataTables::of($rows)
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
