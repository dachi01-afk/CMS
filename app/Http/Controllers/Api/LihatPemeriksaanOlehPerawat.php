<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Models\Perawat;
use App\Models\PerawatDokterPoli;
use App\Models\Kunjungan;

class LihatPemeriksaanOlehPerawat extends Controller
{
    /**
     * GET /api/perawat/kunjungan-tugas
     * Semua kunjungan yg menjadi tanggung jawab perawat
     */
    public function getKunjunganTugasPerawat(Request $request)
    {
        try {
            $userId = Auth::id();

            // 🔎 Ambil perawat dari user login
            $perawat = Perawat::where('user_id', $userId)->first();
            if (!$perawat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login ini bukan perawat'
                ], 403);
            }

            $tanggal = $request->query('tanggal');
            $status  = $request->query('status');

            // 🔗 Ambil mapping perawat → dokter_poli
            $pairs = PerawatDokterPoli::with('dokterPoli')
                ->where('perawat_id', $perawat->id)
                ->get()
                ->filter(fn($x) => $x->dokterPoli)
                ->map(fn($x) => [
                    'dokter_id' => $x->dokterPoli->dokter_id,
                    'poli_id'   => $x->dokterPoli->poli_id,
                ]);

            if ($pairs->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'total' => 0,
                    'data' => []
                ]);
            }

            $query = Kunjungan::with([
                'pasien',
                'dokter',
                'poli',
                'emr'
            ])
            ->where(function ($q) use ($pairs) {
                foreach ($pairs as $p) {
                    $q->orWhere(function ($sub) use ($p) {
                        $sub->where('dokter_id', $p['dokter_id'])
                            ->where('poli_id', $p['poli_id']);
                    });
                }
            });

            if ($tanggal) {
                $query->whereDate('tanggal_kunjungan', $tanggal);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $data = $query->orderByDesc('tanggal_kunjungan')
                ->get()
                ->map(function ($k) use ($perawat) {
                    $emr = $k->emr;

                    return [
                        'kunjungan_id' => $k->id,
                        'tanggal' => $k->tanggal_kunjungan,
                        'no_antrian' => $k->no_antrian,
                        'status' => $k->status,

                        'pasien' => $k->pasien?->nama_pasien,
                        'dokter' => $k->dokter?->nama_dokter,
                        'poli'   => $k->poli?->nama_poli,

                        // 🔥 INI KUNCI UTAMA
                        'sudah_isi_vital' =>
                            $emr && $emr->perawat_id == $perawat->id,

                        'perawat_pengisi' =>
                            ($emr && $emr->perawat_id == $perawat->id)
                                ? $perawat->nama_perawat
                                : null
                    ];
                });

            return response()->json([
                'success' => true,
                'total' => $data->count(),
                'data' => $data
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/perawat/kunjungan-sudah-vital
     * Kunjungan yang BENAR-BENAR sudah diisi perawat login
     */
    public function getKunjunganSudahDiisiVitalPerawat(Request $request)
    {
        try {
            $userId = Auth::id();
            $perawat = Perawat::where('user_id', $userId)->first();

            if (!$perawat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Login ini bukan perawat'
                ], 403);
            }

            $query = Kunjungan::with(['pasien','dokter','poli','emr'])
                ->whereHas('emr', function ($q) use ($perawat) {
                    $q->where('perawat_id', $perawat->id);
                });

            $data = $query->get()->map(function ($k) use ($perawat) {
                return [
                    'kunjungan_id' => $k->id,
                    'tanggal' => $k->tanggal_kunjungan,
                    'no_antrian' => $k->no_antrian,
                    'status' => $k->status,

                    'pasien' => $k->pasien?->nama_pasien,
                    'dokter' => $k->dokter?->nama_dokter,
                    'poli'   => $k->poli?->nama_poli,

                    'perawat_pengisi' => $perawat->nama_perawat
                ];
            });

            return response()->json([
                'success' => true,
                'total' => $data->count(),
                'data' => $data
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
