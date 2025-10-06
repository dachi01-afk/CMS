<?php

namespace App\Http\Controllers\Admin;

use App\Models\Kunjungan;
use App\Models\Pembayaran;
use App\Models\Administrasi;
use Illuminate\Http\Request;
use App\Models\TransaksiApoteker;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class LaporanController extends Controller
{
    public function index()
    {
        return view('admin.laporan');
    }

    public function dataKunjungan()
    {
        $query = Kunjungan::with(['dokter:id,nama_dokter', 'pasien:id,nama_pasien'])
            ->select(['id', 'dokter_id', 'pasien_id', 'tanggal_kunjungan', 'keluhan_awal']);

        return DataTables::of($query)
            ->addColumn('dokter', fn($kunjungan) => $kunjungan->dokter->nama_dokter ?? '-')
            ->addColumn('pasien', fn($kunjungan) => $kunjungan->pasien->nama_pasien ?? '-')
            // ->addColumn('action', function ($kunjungan) {
            //     return '
            // <button class="btn-edit-kunjungan text-blue-600 hover:text-blue-800 mr-2" data-id="' . $kunjungan->id . '" title="Edit">
            //     <i class="fa-regular fa-pen-to-square text-lg"></i>
            // </button>
            // <button class="btn-delete-kunjungan text-red-600 hover:text-red-800" data-id="' . $kunjungan->id . '" title="Hapus">
            //     <i class="fa-regular fa-trash-can text-lg"></i>
            // </button>';
            // })
            // ->rawColumns(['action'])
            ->make(true);
    }

    public function dataPembayaran()
    {
        $query = Pembayaran::with(['pasien:id,nama_pasien'])
            ->select(['id', 'pasien_id', 'total_tagihan', 'status', 'tanggal_pembayaran']);

        return DataTables::of($query)
            ->addColumn('pasien', fn($pembayaran) => $pembayaran->pasien->nama_pasien ?? '-')
            ->editColumn('total_tagihan', fn($pembayaran) => 'Rp ' . number_format($pembayaran->total_tagihan, 0, ',', '.'))
            ->editColumn('status', function ($pembayaran) {
                return $pembayaran->status === 'Sudah Bayar'
                    ? '<span class="text-green-600 font-semibold">Sudah Bayar</span>'
                    : '<span class="text-red-600 font-semibold">Belum Bayar</span>';
            })
            ->editColumn('tanggal_pembayaran', function ($pembayaran) {
                return \Carbon\Carbon::parse($pembayaran->tanggal_pembayaran)->format('d/m/Y H:i');
            })
            ->rawColumns(['status'])
            ->make(true);
    }

    public function dataTransaksiApoteker()
    {
        $query = TransaksiApoteker::with([
            'apoteker:id,nama_apoteker'
        ])->select(['id', 'resep_id', 'apoteker_id', 'tanggal_transaksi_apoteker', 'total_harga']);

        return DataTables::of($query)
            ->addColumn('kode_resep', fn($row) => $row->resep->id ?? '-')
            ->addColumn('nama_apoteker', fn($row) => $row->apoteker->nama_apoteker ?? '-')
            ->make(true);
    }

    public function dataAdministrasi()
    {
        $query = Administrasi::select(['id', 'laporan', 'tarif', 'periode']);

        return DataTables::of($query)
            ->make(true);
    }
}
