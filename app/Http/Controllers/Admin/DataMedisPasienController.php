<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\EMR;
use App\Models\Konsul;
use App\Models\TesLab;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Pasien;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class DataMedisPasienController extends Controller
{
    public function index()
    {
        $dataEMR = EMR::with('kunjungan.pasien', 'kunjungan.poli.dokter', 'kunjungan', 'resep.obat')->paginate(10);
        return view('admin.data_medis_pasien', compact('dataEMR'));
    }

    public function detailEMR($no_emr)
    {
        // 1) Cari pasien dari no_emr
        $pasien = Pasien::where('no_emr', $no_emr)->firstOrFail();

        // 2) Ambil semua EMR pasien ini + relasi pendukung
        $emrList = Emr::with([
            'dokter',
            'poli',
            'kunjungan',
        ])
            ->where('pasien_id', $pasien->id)
            ->orderByDesc('created_at')
            ->get();

        // 3) Kirim ke view
        return view('admin.dataMedisPasien.detail-emr', [
            'pasien'  => $pasien,
            'emrList' => $emrList,
        ]);
    }

    public function getDataEMR()
    {
        $query = Pasien::query()
            ->whereHas('emr')         // hanya pasien yang punya EMR
            ->withCount('emr as total_emr'); // hitung berapa EMR tiap pasien

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('no_emr', fn($p) => $p->no_emr ?? '-')
            ->addColumn('nama_pasien', fn($p) => $p->nama_pasien ?? '-')
            ->addColumn('total_emr', fn($p) => $p->total_emr ?? 0)
            ->addColumn('action', function ($p) {
                return '
                <button class="btn-lihat-emr text-blue-600 hover:text-blue-800 mr-2"
                        data-pasien-id="' . $p->id . '"
                        data-no-emr="' . e($p->no_emr) . '"
                        title="Lihat Detail EMR Pasien">
                    <i class="fa-solid fa-notes-medical"></i>
                    <span class="ml-1">Lihat Detail EMR Pasien</span>
                </button>
            ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function detailEMRPasien($id)
    {
        $emr = Emr::with([
            'dokter',
            'poli',
            'perawat',
            'kunjungan',
            'resep.obat',
        ])
            ->where('id', $id)->firstOrFail();

        if (!$emr) {
            // Kalau pasien belum punya EMR sama sekali
            abort(404, 'Data EMR untuk pasien ini belum tersedia.');
        }

        // 3) Kirim ke view
        return view('admin.dataMedisPasien.detail-emr-pasien', [
            'emr'    => $emr,
        ]);
    }

    public function getDataEMRById($id)
    {
        $dataEMR = EMR::with('kunjungan.pasien', 'kunjungan.poli.dokter', 'resep.obat')->findOrFail($id);

        return response()->json([
            'data' => $dataEMR,
        ]);
    }

    // public function dataRekamMedis()
    // {
    //     $query = EMR::with(['kunjungan.dokter:id,nama_dokter', 'kunjungan.pasien:id,nama_pasien'])
    //         ->select(['id', 'kunjungan_id', 'riwayat_penyakit', 'alergi', 'hasil_periksa']);

    //     return DataTables::of($query)
    //         ->addColumn('dokter', fn($rekam) => $rekam->kunjungan->dokter->nama_dokter ?? '-')
    //         ->addColumn('pasien', fn($rekam) => $rekam->kunjungan->pasien->nama_pasien ?? '-')
    //         ->addColumn('tanggal_kunjungan', fn($rekam) => $rekam->kunjungan->tanggal_kunjungan ?? '-')
    //         ->make(true);
    // }

    // public function dataKonsultasi()
    // {
    //     $query = Konsul::with([
    //         'kunjungan.dokter:id,nama_dokter',
    //         'kunjungan.pasien:id,nama_pasien'
    //     ])
    //         ->select(['id', 'kunjungan_id', 'diagnosa', 'catatan']);

    //     return DataTables::of($query)
    //         ->addColumn('dokter', fn($konsul) => $konsul->kunjungan->dokter->nama_dokter ?? '-')
    //         ->addColumn('pasien', fn($konsul) => $konsul->kunjungan->pasien->nama_pasien ?? '-')
    //         ->addColumn('tanggal_kunjungan', fn($konsul) => $konsul->kunjungan->tanggal_kunjungan ?? '-')
    //         ->make(true);
    // }

    // public function dataLab()
    // {
    //     $query = TesLab::with(['kunjungan.dokter:id,nama_dokter', 'kunjungan.pasien:id,nama_pasien'])
    //         ->select(['id', 'kunjungan_id', 'jenis_tes', 'hasil_tes', 'tanggal_tes']);

    //     return DataTables::of($query)
    //         ->addColumn('dokter', fn($lab) => $lab->kunjungan->dokter->nama_dokter ?? '-')
    //         ->addColumn('pasien', fn($lab) => $lab->kunjungan->pasien->nama_pasien ?? '-')
    //         ->editColumn('jenis_tes', function ($lab) {
    //             $jenis = json_decode($lab->jenis_tes, true);
    //             return is_array($jenis) ? implode(', ', $jenis) : $lab->jenis_tes;
    //         })
    //         ->make(true);
    // }


    // public function getDataEMR(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $data = EMR::with(['kunjungan.dokter', 'kunjungan.pasien'])->select('emr.*');

    //         return DataTables::of($data)
    //             ->addIndexColumn()
    //             ->addColumn('nama_dokter', function ($row) {
    //                 return optional($row->kunjungan->dokter)->nama_dokter ?? '-';
    //             })
    //             ->addColumn('nama_pasien', function ($row) {
    //                 return optional($row->kunjungan->pasien)->nama_pasien ?? '-';
    //             })
    //             ->addColumn('tanggal_kunjungan', function ($row) {
    //                 return $row->kunjungan && $row->kunjungan->tanggal_kunjungan
    //                     ? Carbon::parse($row->kunjungan->tanggal_kunjungan)->format('d-m-Y')
    //                     : '-';
    //             })
    //             ->addColumn('diagnosa', function ($row) {
    //                 return $row->diagnosis ?? '-';
    //             })
    //             ->addColumn('catatan', function ($row) {
    //                 return $row->riwayat_penyakit_sekarang ?? '-';
    //             })
    //             ->rawColumns(['nama_dokter', 'nama_pasien', 'diagnosa', 'catatan'])
    //             ->make(true);
    //     }
    // }
}
