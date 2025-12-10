<?php

namespace App\Http\Controllers\Perawat;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\DokterPoli;
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

    public function getDataKunjunganHariIni()
    {
        $userId = Auth::id();

        // Ambil perawat berdasarkan user yang login
        $perawat = Perawat::where('user_id', $userId)->first();

        if (!$perawat) {
            return response()->json(['data' => []]);
        }

        $perawatId = $perawat->id;

        $tz    = config('app.timezone', 'Asia/Jakarta');
        $today = Carbon::today($tz)->toDateString();

        $rows = Kunjungan::query()
            ->with(['pasien', 'dokter', 'poli'])
            ->whereDate('tanggal_kunjungan', $today)
            ->where('status', 'Waiting')
            // cek kunjungan ini terhubung ke perawat melalui dokter_poli & perawat_dokter_poli
            ->whereExists(function ($q) use ($perawatId) {
                $q->select(DB::raw(1))
                    ->from('perawat_dokter_poli as pdp')
                    ->join('dokter_poli as dp', 'dp.id', '=', 'pdp.dokter_poli_id')
                    // pasangan dokter & poli di kunjungan harus sama dengan di dokter_poli
                    ->whereColumn('dp.dokter_id', 'kunjungan.dokter_id')
                    ->whereColumn('dp.poli_id', 'kunjungan.poli_id')
                    ->where('pdp.perawat_id', $perawatId);
            })
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
                    'dokter'       => $k->dokter->nama_dokter ?? '-',
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
        $perawat = Perawat::with('perawatDokterPoli')->where('user_id', $userId)->firstOrFail();

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
    public function getDataKunjunganDenganStatusEngaged()
    {
        $userId = Auth::id();

        // Ambil perawat berdasarkan user yg login
        $perawat = Perawat::where('user_id', $userId)->first();

        // Kalau tidak ada perawat → balikin DT kosong (tidak 404)
        if (!$perawat) {
            return DataTables::of(collect())->make(true);
        }

        $perawatId = $perawat->id;

        // Ambil EMR yg:
        //  - perawat_id = perawat login
        //  - kunjungan.status = 'Engaged'
        //  - kunjungan (dokter_id, poli_id) memang terhubung ke perawat via pivot
        $dataKunjunganEngaged = EMR::with(['pasien', 'dokter', 'poli', 'perawat', 'kunjungan'])
            ->where('perawat_id', $perawatId)
            ->whereHas('kunjungan', function ($q) use ($perawatId) {
                $q->where('status', 'Engaged')
                    ->whereExists(function ($qq) use ($perawatId) {
                        $qq->select(DB::raw(1))
                            ->from('perawat_dokter_poli as pdp')
                            ->join('dokter_poli as dp', 'dp.id', '=', 'pdp.dokter_poli_id')
                            // pasangan dokter & poli di kunjungan harus sama dengan di dokter_poli
                            ->whereColumn('dp.dokter_id', 'kunjungan.dokter_id')
                            ->whereColumn('dp.poli_id', 'kunjungan.poli_id')
                            ->where('pdp.perawat_id', $perawatId);
                    });
            })
            ->orderBy('id', 'desc')
            ->get();

        return DataTables::of($dataKunjunganEngaged)
            ->addIndexColumn()
            ->addColumn('no_antrian', fn($emr) => optional($emr->kunjungan)->no_antrian ?? '-')
            ->addColumn('nama_pasien', fn($emr) => optional($emr->pasien)->nama_pasien ?? '-')
            ->addColumn('nama_dokter', fn($emr) => optional($emr->dokter)->nama_dokter ?? '-')
            ->addColumn('nama_poli', fn($emr) => optional($emr->poli)->nama_poli ?? '-')
            ->addColumn('keluhan_utama', fn($emr) => $emr->keluhan_utama ?? '-')
            ->addColumn('action', function ($row) {
                if (!empty($row->id)) {
                    $url = route('perawat.form.pengisian.vital.sign', $row->id);

                    return '
                    <a href="' . $url . '"
                       class="inline-flex items-center px-3 py-1 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                       Proses
                    </a>
                ';
                }

                // fallback kalau EMR belum ada (harusnya jarang kepakai di endpoint ini)
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
        $dataEMR = EMR::with('pasien', 'dokter', 'poli', 'pasien')->where('id', $id)->firstOrFail();

        $dataPasien = $dataEMR->pasien;
        $dataPoliPasien = $dataEMR->poli;
        $dataDokterPasien = $dataEMR->dokter;
        $dataIdEMR = $dataEMR->id;
        $urlBack = route('perawat.kunjungan');

        return view('perawat.kunjungan.form-pengisian-vital-sign', compact(
            'dataEMR',
            'dataPasien',
            'dataPoliPasien',
            'dataDokterPasien',
            'dataIdEMR',
            'urlBack'
        ));
    }

    public function submitDataVitalSignPasien(Request $request, $id)
    {
        try {
            // 1. VALIDASI INPUT
            $validated = $request->validate([
                'tekanan_darah'            => ['required', 'regex:/^\d{2,3}\/\d{2,3}$/'],
                'suhu_tubuh'               => ['required', 'numeric', 'between:30,45'],
                'nadi'                     => ['required', 'integer', 'between:30,220'],
                'pernapasan'               => ['required', 'integer', 'between:5,60'],
                'saturasi_oksigen'         => ['required', 'integer', 'between:50,100'],

                // 🔹 field baru, opsional
                'riwayat_penyakit_dahulu'  => ['nullable', 'string', 'max:1000'],
                'riwayat_penyakit_keluarga' => ['nullable', 'string', 'max:1000'],
            ], [
                'required' => ':attribute wajib diisi.',
                'numeric'  => ':attribute harus berupa angka.',
                'integer'  => ':attribute harus berupa bilangan bulat.',
                'between'  => ':attribute harus di antara :min dan :max.',
                'regex'    => 'Format tekanan darah harus contoh: 120/80.',
                'string'   => ':attribute harus berupa teks.',
                'max'      => ':attribute maksimal :max karakter.',
            ], [
                'tekanan_darah'            => 'Tekanan darah',
                'suhu_tubuh'               => 'Suhu tubuh',
                'nadi'                     => 'Nadi',
                'pernapasan'               => 'Pernapasan',
                'saturasi_oksigen'         => 'Saturasi oksigen',
                'riwayat_penyakit_dahulu'  => 'Riwayat penyakit dahulu',
                'riwayat_penyakit_keluarga' => 'Riwayat penyakit keluarga',
            ]);

            // 2. AMBIL PERAWAT YANG LOGIN
            $perawat = Perawat::where('user_id', Auth::id())->firstOrFail();

            // 3. AMBIL EMR + KUNJUNGAN
            $emr = EMR::with('kunjungan')->findOrFail($id);
            $kunjungan = $emr->kunjungan;

            if (!$kunjungan) {
                throw ValidationException::withMessages([
                    'emr' => 'Data kunjungan untuk EMR ini tidak ditemukan.',
                ]);
            }

            // 4. CEK HAK AKSES PERAWAT
            if (!empty($perawat->dokter_id) && $perawat->dokter_id != $kunjungan->dokter_id) {
                throw new AuthorizationException("Anda tidak berwenang mengisi vital sign untuk dokter ini.");
            }
            if (!empty($perawat->poli_id) && $perawat->poli_id != $kunjungan->poli_id) {
                throw new AuthorizationException("Anda tidak berwenang untuk poli ini.");
            }

            if ($kunjungan->status !== 'Engaged') {
                throw ValidationException::withMessages([
                    'status' => 'Kunjungan belum berada dalam status Engaged.',
                ]);
            }

            // 5. SIMPAN DATA VITAL SIGN + RIWAYAT
            $emr->update($validated);

            // 🔹 Kalau request AJAX → balas JSON (untuk Swal success + redirect manual)
            if ($request->ajax()) {
                return response()->json([
                    'success'      => true,
                    'message'      => 'Data vital sign berhasil disimpan.',
                    'redirect_url' => route('perawat.kunjungan'),
                ]);
            }

            // 🔹 fallback biasa (kalau bukan AJAX)
            return redirect()
                ->route('perawat.kunjungan')
                ->with('success', 'Data vital sign berhasil disimpan.');
        } catch (ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors'  => $e->errors(),
                ], 422);
            }

            return back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (AuthorizationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 403);
            }

            return back()
                ->with('error', $e->getMessage())
                ->withInput();
        } catch (\Throwable $e) {
            Log::error("Error vital sign EMR #$id", [
                'error' => $e->getMessage(),
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan pada sistem, coba lagi.',
                ], 500);
            }

            return back()
                ->with('error', 'Terjadi kesalahan pada sistem, coba lagi.')
                ->withInput();
        }
    }
}
