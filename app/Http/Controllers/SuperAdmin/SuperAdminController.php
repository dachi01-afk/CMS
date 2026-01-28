<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\Pasien;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Models\OrderLayanan;
use App\Models\Pembayaran;
use App\Models\PenjualanObat;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $hariIni = Carbon::now();

        $totalPasien = Pasien::count();

        $pasienHariIni = Kunjungan::whereDate('tanggal_kunjungan', now())->distinct('pasien_id')->count('pasien_id');

        $totalPembayaran = Pembayaran::count();

        $totalTransaksiLayanan = OrderLayanan::count();

        $totalTransaksiObat = PenjualanObat::count();

        $totalTransaksi = $totalPembayaran + $totalTransaksiObat + $totalTransaksiLayanan;

        $pembayaran = Pembayaran::where('status', 'Sudah Bayar')->sum('uang_yang_diterima');
        $layanan = OrderLayanan::where('status_order_layanan', 'Selesai')->sum('total_bayar');
        $obat = PenjualanObat::where('status', 'Sudah Bayar')->sum('uang_yang_diterima');

        $pendapatan = $pembayaran + $layanan + $obat;

        $pendapatanRupiah = 'Rp ' . number_format($pendapatan, 0, ',', '.');

        // dd($pendapatanRupiah);

        return view('super-admin.dashboard-super-admin', compact('totalPasien', 'pasienHariIni', 'totalTransaksi', 'pendapatanRupiah'));
    }
}
