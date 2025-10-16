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

    public function chartKeuangan(Request $request)
    {
        $filter = $request->get('filter', 'mingguan'); // default: mingguan

        // Query dasar
        $query = DB::table('pembayaran')
            ->whereNotNull('tanggal_pembayaran')
            ->where('status', 'Sudah Bayar');

        if ($filter === 'bulanan') {
            $query->selectRaw('DATE_FORMAT(tanggal_pembayaran, "%Y-%m") as periode, SUM(uang_yang_diterima) as pemasukan')
                ->groupBy('periode')
                ->orderBy('periode', 'asc');
        } elseif ($filter === 'tahunan') {
            $query->selectRaw('YEAR(tanggal_pembayaran) as periode, SUM(uang_yang_diterima) as pemasukan')
                ->groupBy('periode')
                ->orderBy('periode', 'asc');
        } else { // MINGGUAN
            $query->selectRaw('DATE_FORMAT(tanggal_pembayaran, "%W") as periode, SUM(uang_yang_diterima) as pemasukan')
                ->whereBetween('tanggal_pembayaran', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])
                ->groupBy('periode')
                ->orderByRaw("FIELD(periode, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')");
        }

        $data = $query->get();

        // Handle jika kosong
        if ($data->isEmpty()) {
            return response()->json([
                'message' => '⚠️ Tidak ada data keuangan ditemukan untuk filter: ' . $filter,
                'data' => []
            ]);
        }

        return response()->json($data);
    }

    public function chartKunjungan (Request $request) {
        $filter = $request->get('filter', 'mingguan');

        $query = DB::table('kunjungan')->get();

        return response()->json($query);
    }
}
