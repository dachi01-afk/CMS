<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\EMR;
use App\Models\Konsul;
use App\Models\TesLab;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\DataTables;

class DataMedisPasienController extends Controller
{
    public function index()
    {
        $dataEMR = EMR::with('kunjungan.pasien', 'kunjungan.poli.dokter', 'kunjungan', 'resep.obat')->paginate(10);
        return view('admin.data_medis_pasien', compact('dataEMR'));
    }

    public function getDataEMR()
    {
        $dataEMR = EMR::with([
            'kunjungan.pasien',
            'kunjungan.poli.dokter', // cukup sekali
            'resep.obat',
        ])->get();

        return DataTables::of($dataEMR)
            ->addIndexColumn()
            ->addColumn(
                'nama_pasien',
                fn($emr) =>
                $emr->kunjungan?->pasien?->nama_pasien ?? '-'
            )
            // ->addColumn('nama_dokter', function ($emr) {
            //     $namaDokter = $emr->kunjungan?->poli?->dokter?->first()?->nama_dokter ?? '-';
            //     return $namaDokter;
            // })
            ->addColumn('nama_dokter', function ($emr) {
                $dokter = $emr->kunjungan?->poli?->dokter?->first();
                return $dokter?->nama_dokter ?? '-';
            })
            ->addColumn(
                'tanggal_kunjungan',
                fn($emr) =>
                $emr->kunjungan?->tanggal_kunjungan ?? '-'
            )
            ->addColumn(
                'keluhan_awal',
                fn($emr) =>
                $emr->kunjungan?->keluhan_awal ?? '-'
            )
            ->addColumn(
                'keluhan_utama',
                fn($emr) =>
                $emr->keluhan_utama ?? '-'
            )
            ->addColumn(
                'riwayat_penyakit_dahulu',
                fn($emr) =>
                $emr->riwayat_penyakit_dahulu ?? '-'
            )
            ->addColumn(
                'riwayat_penyakit_keluarga',
                fn($emr) =>
                $emr->riwayat_penyakit_keluarga ?? '-'
            )
            ->addColumn('tekanan_darah', fn($emr) => $emr->tekanan_darah ?? '-')
            ->addColumn('suhu_tubuh', fn($emr) => $emr->suhu_tubuh ?? '-')
            ->addColumn('nadi', fn($emr) => $emr->nadi ?? '-')
            ->addColumn('pernapasan', fn($emr) => $emr->pernapasan ?? '-')
            ->addColumn('saturasi_oksigen', fn($emr) => $emr->saturasi_oksigen ?? '-')
            ->addColumn('diagnosis', fn($emr) => $emr->diagnosis ?? '-')
            // ->addColumn('action', function ($emr) {
            //     $namaDokter = $emr->kunjungan?->poli?->dokter?->first()?->nama_dokter ?? '-';
            //     // amankan attribute HTML kalau perlu
            //     $namaAttr = e($namaDokter);
            //     return '
            //     <button class="btn-detail-emr text-blue-600 hover:text-blue-800 mr-2 text-center items-center"
            //             data-id="' . $emr->id . '"
            //             data-dokter="' . $namaAttr . '"
            //             title="Detail">
            //         <i class="fa-solid fa-circle-info text-lg"></i> Lihat Detail
            //     </button>
            // ';
            // })
            ->addColumn('action', function ($emr) {
                $dokter = $emr->kunjungan?->poli?->dokter?->first();
                $namaDokter = $dokter?->nama_dokter ?? '-';
                return '
        <button class="btn-detail-emr text-blue-600 hover:text-blue-800 mr-2 text-center items-center"
                data-id="' . $emr->id . '"
                data-dokter="' . $namaDokter . '"
                title="Detail">
            <i class="fa-solid fa-circle-info text-lg"></i> Lihat Detail
        </button>
    ';
            })
            ->rawColumns(['action'])
            ->make(true);
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
