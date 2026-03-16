<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PasienHariIniController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $search = trim((string) $request->search);

        $baseQuery = $this->baseQuery($today, $search);

        $totalPasienHariIni = $this->baseQuery($today)->count();

        $totalMenunggu = $this->baseQuery($today)
            ->whereIn('k.status', ['Pending', 'Waiting', 'Engaged', 'Payment'])
            ->count();

        $totalSelesai = $this->baseQuery($today)
            ->where('k.status', 'Succeed')
            ->count();

        $pasienHariIni = $baseQuery
            ->orderByDesc('k.id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.pasien-hari-ini.index', compact(
            'pasienHariIni',
            'totalPasienHariIni',
            'totalMenunggu',
            'totalSelesai'
        ));
    }

    public function detail($no_emr)
    {
        $today = Carbon::today()->toDateString();

        $detail = DB::table('kunjungan as k')
            ->join('pasien as p', 'p.id', '=', 'k.pasien_id')
            ->leftJoin('jadwal_dokter as jd', 'jd.id', '=', 'k.jadwal_dokter_id')
            ->leftJoin('dokter as d_kunjungan', 'd_kunjungan.id', '=', 'k.dokter_id')
            ->leftJoin('dokter as d_jadwal', 'd_jadwal.id', '=', 'jd.dokter_id')
            ->leftJoin('poli as poli_kunjungan', 'poli_kunjungan.id', '=', 'k.poli_id')
            ->leftJoin('poli as poli_jadwal', 'poli_jadwal.id', '=', 'jd.poli_id')
            ->leftJoin('emr as e', 'e.kunjungan_id', '=', 'k.id')
            ->leftJoin('perawat as pr', 'pr.id', '=', 'e.perawat_id')
            ->where('p.no_emr', $no_emr)
            ->whereDate('k.tanggal_kunjungan', $today)
            ->orderByDesc('k.id')
            ->select([
                'k.id as kunjungan_id',
                'k.no_antrian',
                'k.status',
                'k.tanggal_kunjungan',
                'k.keluhan_awal',
                'k.created_at as kunjungan_dibuat',
                'k.updated_at as kunjungan_diupdate',

                'p.id as pasien_id',
                'p.nama_pasien',
                'p.no_emr as no_rm',
                'p.no_hp_pasien as no_hp',

                'jd.hari',
                'jd.jam_awal',
                'jd.jam_selesai',

                DB::raw('COALESCE(poli_kunjungan.nama_poli, poli_jadwal.nama_poli) as nama_poli'),
                DB::raw('COALESCE(d_kunjungan.nama_dokter, d_jadwal.nama_dokter) as nama_dokter'),

                'pr.nama_perawat',

                'e.keluhan_utama',
                'e.riwayat_penyakit_dahulu',
                'e.riwayat_penyakit_keluarga',
                'e.tekanan_darah',
                'e.suhu_tubuh',
                'e.tinggi_badan',
                'e.berat_badan',
                'e.imt',
                'e.nadi',
                'e.pernapasan',
                'e.saturasi_oksigen',
                'e.diagnosis',
                'e.created_at as emr_created_at',
                'e.updated_at as emr_updated_at',
            ])
            ->first();

        if (!$detail) {
            return response()->json([
                'success' => false,
                'message' => 'Data detail kunjungan berdasarkan no EMR tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $detail,
        ]);
    }

    private function baseQuery($today, $search = '')
    {
        $query = DB::table('kunjungan as k')
            ->join('pasien as p', 'p.id', '=', 'k.pasien_id')
            ->leftJoin('jadwal_dokter as jd', 'jd.id', '=', 'k.jadwal_dokter_id')
            ->leftJoin('dokter as d_kunjungan', 'd_kunjungan.id', '=', 'k.dokter_id')
            ->leftJoin('dokter as d_jadwal', 'd_jadwal.id', '=', 'jd.dokter_id')
            ->leftJoin('poli as poli_kunjungan', 'poli_kunjungan.id', '=', 'k.poli_id')
            ->leftJoin('poli as poli_jadwal', 'poli_jadwal.id', '=', 'jd.poli_id')
            ->select([
                'k.id as kunjungan_id',
                'k.no_antrian',
                'k.status',
                'k.tanggal_kunjungan',
                'k.keluhan_awal',
                'k.created_at',
                'k.updated_at',

                'p.id as pasien_id',
                'p.no_emr',
                'p.no_emr as no_rm',
                'p.nama_pasien',
                'p.no_hp_pasien as no_hp',

                'jd.hari',
                'jd.jam_awal',
                'jd.jam_selesai',

                DB::raw('COALESCE(poli_kunjungan.nama_poli, poli_jadwal.nama_poli) as nama_poli'),
                DB::raw('COALESCE(d_kunjungan.nama_dokter, d_jadwal.nama_dokter) as nama_dokter'),
            ])
            ->whereDate('k.tanggal_kunjungan', $today)
            ->whereIn('k.status', ['Pending', 'Waiting', 'Engaged', 'Payment', 'Succeed']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('p.nama_pasien', 'like', "%{$search}%")
                    ->orWhere('p.no_emr', 'like', "%{$search}%")
                    ->orWhere('p.no_hp_pasien', 'like', "%{$search}%")
                    ->orWhere('k.no_antrian', 'like', "%{$search}%")
                    ->orWhere('d_kunjungan.nama_dokter', 'like', "%{$search}%")
                    ->orWhere('d_jadwal.nama_dokter', 'like', "%{$search}%")
                    ->orWhere('poli_kunjungan.nama_poli', 'like', "%{$search}%")
                    ->orWhere('poli_jadwal.nama_poli', 'like', "%{$search}%");
            });
        }

        return $query;
    }
}
