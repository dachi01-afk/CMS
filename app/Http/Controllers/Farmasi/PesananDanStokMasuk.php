<?php

namespace App\Http\Controllers\Farmasi;

use App\DataTables\MutasiStokObatDataTable;
use App\Http\Controllers\Controller;
use App\Models\MutasiStokObat;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\Services\DataTable;

class PesananDanStokMasuk extends Controller
{

    public function index()
    {
        return view('farmasi.pesanan-dan-stok-masuk.pesanan-dan-stok-masuk');
    }

    public function getData()
    {
        $dataMutasi = MutasiStokObat::with(['supplier', 'farmasi'])->getData();

        // dd($dataMutasi);

        return DataTables::of($dataMutasi)
            ->addIndexColumn()
            ->addColumn('nama_supplier', fn($data) => $data->supplier->nama_supplier ?? '-')
            ->addColumn('nomor_transaksi', fn($data) => $data->nomor_transaksi ?? '-')
            ->addColumn('nomor_faktur', fn($data) => $data->nomor_faktur ?? '-')
            ->addColumn('jenis_transaksi', fn($data) => $data->jenis_transaksi ?? '-')
            ->addColumn('tanggal_transaksi', fn($data) => $data->tanggal_transaksi ?? '-')
            ->addColumn('keterangan', fn($data) => $data->keterangan ?? '-')
            ->addColumn('nama_farmasi', fn($data) => $data->farmasi->nama_farmasi ?? '-')
            ->addColumn('action', function ($data) {
                return '
    <div class="flex items-center gap-4">
        <button class="btn-print text-gray-500 hover:text-gray-700 transition-colors" 
                data-id="' . $data->id . '" title="Print">
            <i class="fa-solid fa-print text-xl"></i>
        </button>

        <button class="btn-konfirmasi text-emerald-500 hover:text-emerald-700 transition-colors" 
                data-id="' . $data->id . '" title="Transaksi Purchase Order">
            <i class="fa-solid fa-folder-plus text-2xl"></i>
        </button>

        <button class="btn-delete-poli text-red-500 hover:text-red-700 transition-colors" 
                data-id="' . $data->id . '" title="Hapus">
            <i class="fa-solid fa-trash-can text-xl"></i>
        </button>
    </div>
    ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }
}
