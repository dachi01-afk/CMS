<?php

namespace App\Http\Controllers\Testing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestingChartController extends Controller
{
    public function index()
    {
        return view('testing.testing-chart');
    }

    // public function chartKeuangan(Request $request){
    //     $filter = $request->get('filter', 'mingguan'); // default: mingguan

    //     // Query dasar
    //     $query = DB::table('pembayaran')
    //         ->whereNotNull('tanggal_pembayaran')
    //         ->where('status', 'Sudah Bayar');

    //     if ($filter === 'bulanan') {
    //         $query->selectRaw('DATE_FORMAT(tanggal_pembayaran, "%Y-%m") as periode, SUM(total_tagihan) as pemasukan')
    //             ->groupBy('periode')
    //             ->orderBy('periode', 'asc');
    //     } elseif ($filter === 'tahunan') {
    //         $query->selectRaw('YEAR(tanggal_pembayaran) as periode, SUM(total_tagihan) as pemasukan')
    //             ->groupBy('periode')
    //             ->orderBy('periode', 'asc');
    //     } else { // MINGGUAN
    //         $query->selectRaw('DATE_FORMAT(tanggal_pembayaran, "%W") as periode, SUM(total_tagihan) as pemasukan')
    //             ->whereBetween('tanggal_pembayaran', [
    //                 now()->startOfWeek(),
    //                 now()->endOfWeek()
    //             ])
    //             ->groupBy('periode')
    //             ->orderByRaw("FIELD(periode, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')");
    //     }

    //     $data = $query->get();

    //     // Handle jika kosong
    //     if ($data->isEmpty()) {
    //         return response()->json([
    //             'message' => '⚠️ Tidak ada data keuangan ditemukan untuk filter: ' . $filter,
    //             'data' => []
    //         ]);
    //     }

    //     return response()->json($data);
    // }


    public function chartKeuangan(Request $request)
    {
        $filter = $request->get('filter', 'mingguan');
        $bulan = $request->get('bulan');
        $tahun = $request->get('tahun', now()->year);

        $query = DB::table('pembayaran')
            ->whereNotNull('tanggal_pembayaran')
            ->where('status', 'Sudah Bayar');

        if ($filter === 'bulanan' && $bulan) {
            // 🔹 Hitung total per minggu dalam bulan tertentu
            $dataMentah = $query->selectRaw("
                FLOOR((DAY(tanggal_pembayaran) - 1) / 7) + 1 AS minggu,
                SUM(total_tagihan) as pemasukan
            ")
                ->whereYear('tanggal_pembayaran', $tahun)
                ->whereMonth('tanggal_pembayaran', $bulan)
                ->groupBy('minggu')
                ->orderBy('minggu', 'asc')
                ->get();

            // 🔹 Pastikan semua minggu (1–5) muncul
            $mingguLengkap = collect(range(1, 5))->map(function ($i) use ($dataMentah) {
                $data = $dataMentah->firstWhere('minggu', $i);
                return [
                    'periode' => 'Minggu ke-' . $i,
                    'pemasukan' => $data ? (float)$data->pemasukan : 0,
                ];
            });

            $data = $mingguLengkap;
        } elseif ($filter === 'tahunan') {
            // 🔹 Hitung total per bulan dalam tahun tertentu
            $dataMentah = $query->selectRaw('MONTH(tanggal_pembayaran) as bulan, SUM(total_tagihan) as pemasukan')
                ->whereYear('tanggal_pembayaran', $tahun)
                ->groupBy('bulan')
                ->orderBy('bulan', 'asc')
                ->get();

            $namaBulan = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember'
            ];

            // 🔹 Pastikan 12 bulan selalu muncul
            $data = collect(range(1, 12))->map(function ($i) use ($dataMentah, $namaBulan) {
                $data = $dataMentah->firstWhere('bulan', $i);
                return [
                    'periode' => $namaBulan[$i],
                    'pemasukan' => $data ? (float)$data->pemasukan : 0,
                ];
            });
        } else {
            // 🔹 Default: per hari dalam minggu ini
            $dataMentah = $query->selectRaw('DATE_FORMAT(tanggal_pembayaran, "%W") as hari, SUM(total_tagihan) as pemasukan')
                ->whereBetween('tanggal_pembayaran', [now()->startOfWeek(), now()->endOfWeek()])
                ->groupBy('hari')
                ->get();

            $hariUrut = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $namaHari = [
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
                'Sunday' => 'Minggu'
            ];

            // 🔹 Pastikan semua hari muncul
            $data = collect($hariUrut)->map(function ($hari) use ($dataMentah, $namaHari) {
                $data = $dataMentah->firstWhere('hari', $hari);
                return [
                    'periode' => $namaHari[$hari],
                    'pemasukan' => $data ? (float)$data->pemasukan : 0,
                ];
            });
        }

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }




    public function chartKunjungan(Request $request)
    {
        $filter = $request->get('filter', 'mingguan');

        $query = DB::table('kunjungan')->get();

        return response()->json($query);
    }
}
