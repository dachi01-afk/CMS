<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use App\Models\EMR;
use App\Models\Konsul;
use App\Models\TesLab;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class DataMedisPasienController extends Controller
{
    public function index()
    {
        return view('admin.data_medis_pasien');
    }

    public function dataRekamMedis()
    {
        $query = EMR::with(['kunjungan.dokter:id,nama_dokter', 'kunjungan.pasien:id,nama_pasien'])
            ->select(['id', 'kunjungan_id', 'riwayat_penyakit', 'alergi', 'hasil_periksa']);

        return DataTables::of($query)
            ->addColumn('dokter', fn($rekam) => $rekam->kunjungan->dokter->nama_dokter ?? '-')
            ->addColumn('pasien', fn($rekam) => $rekam->kunjungan->pasien->nama_pasien ?? '-')
            ->addColumn('tanggal_kunjungan', fn($rekam) => $rekam->kunjungan->tanggal_kunjungan ?? '-')
            ->make(true);
    }

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

    public function dataLab()
    {
        $query = TesLab::with(['kunjungan.dokter:id,nama_dokter', 'kunjungan.pasien:id,nama_pasien'])
            ->select(['id', 'kunjungan_id', 'jenis_tes', 'hasil_tes', 'tanggal_tes']);

        return DataTables::of($query)
            ->addColumn('dokter', fn($lab) => $lab->kunjungan->dokter->nama_dokter ?? '-')
            ->addColumn('pasien', fn($lab) => $lab->kunjungan->pasien->nama_pasien ?? '-')
            ->editColumn('jenis_tes', function ($lab) {
                $jenis = json_decode($lab->jenis_tes, true);
                return is_array($jenis) ? implode(', ', $jenis) : $lab->jenis_tes;
            })
            ->make(true);
    }


    public function getDataEMR(Request $request)
    {
        if ($request->ajax()) {
            $data = EMR::with(['kunjungan.dokter', 'kunjungan.pasien'])->select('emr.*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('nama_dokter', function ($row) {
                    return optional($row->kunjungan->dokter)->nama_dokter ?? '-';
                })
                ->addColumn('nama_pasien', function ($row) {
                    return optional($row->kunjungan->pasien)->nama_pasien ?? '-';
                })
                ->addColumn('tanggal_kunjungan', function ($row) {
                    return $row->kunjungan && $row->kunjungan->tanggal_kunjungan
                        ? Carbon::parse($row->kunjungan->tanggal_kunjungan)->format('d-m-Y')
                        : '-';
                })
                ->addColumn('diagnosa', function ($row) {
                    return $row->diagnosis ?? '-';
                })
                ->addColumn('catatan', function ($row) {
                    return $row->riwayat_penyakit_sekarang ?? '-';
                })
                ->rawColumns(['nama_dokter', 'nama_pasien', 'diagnosa', 'catatan'])
                ->make(true);
        }
    }
}
