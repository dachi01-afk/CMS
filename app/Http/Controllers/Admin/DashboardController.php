<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dokter;
use App\Models\Pasien;
use App\Models\Apoteker;
use App\Models\Farmasi;
use App\Models\Obat;
use App\Models\Kunjungan;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    // ===============================
    // 📊 Grafik Kunjungan per Bulan
    // ===============================
    public function getChartKunjungan()
    {
        $data = Kunjungan::select(
            DB::raw('MONTH(tanggal_kunjungan) as bulan'),
            DB::raw('COUNT(*) as total')
        )
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        // Format untuk Chart.js
        $labels = [];
        $values = [];

        foreach ($data as $row) {
            $labels[] = date('F', mktime(0, 0, 0, $row->bulan, 1)); // nama bulan
            $values[] = $row->total;
        }

        return response()->json([
            'labels' => $labels,
            'values' => $values
        ]);
    }

    // ===============================
    // 👨‍⚕️ Total Dokter
    // ===============================
    public function getTotalDokter()
    {
        return response()->json([
            'total' => Dokter::count()
        ]);
    }

    // ===============================
    // 🧍‍♂️ Total Pasien
    // ===============================
    public function getTotalPasien()
    {
        return response()->json([
            'total' => Pasien::count()
        ]);
    }

    // ===============================
    // 💊 Total Stok Obat
    // ===============================
    public function getStokObat()
    {
        return response()->json([
            'total' => Obat::count()
        ]);
    }

    // ===============================
    // 👩‍🔬 Total Apoteker
    // ===============================
    public function getTotalFarmasi()
    {
        return response()->json([
            'total' => Farmasi::count()
        ]);
    }
}
