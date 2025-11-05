<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RiwayatTransaksiController extends Controller
{
    public function index()
    {
        return view('kasir.riwayat-transaksi.riwayat-transaksi');
    }
}
