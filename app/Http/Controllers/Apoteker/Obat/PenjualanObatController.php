<?php

namespace App\Http\Controllers\Apoteker\Obat;

use App\Http\Controllers\Controller;
use App\Models\Pasien;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PenjualanObatController extends Controller
{
    public function getDataPenjualanObat()
    {
        // Ambil semua pasien + data obat yang pernah dibeli
        $dataPenjualanObat = Pasien::with('obat')->latest()->get();

        // Flatten (karena pasien bisa punya banyak obat)
        $penjualanData = collect();

        foreach ($dataPenjualanObat as $pasien) {
            foreach ($pasien->obat as $obat) {
                $penjualanData->push([
                    'nama_pasien'       => $pasien->nama_pasien,
                    'nama_obat'         => $obat->nama_obat,
                    'kode_transaksi'    => $obat->pivot->kode_transaksi,
                    'jumlah'            => $obat->pivot->jumlah,
                    'sub_total'         => $obat->pivot->sub_total,
                    'tanggal_transaksi' => $obat->pivot->tanggal_transaksi,
                ]);
            }
        }

        return DataTables::of($penjualanData)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                return '
                <button class="text-blue-600 hover:text-blue-800 mr-2">
                    <i class="fa-regular fa-pen-to-square text-lg"></i>
                </button>
                <button class="text-red-600 hover:text-red-800">
                    <i class="fa-regular fa-trash-can text-lg"></i>
                </button>
            ';
            })
            ->make(true);
    }
}
