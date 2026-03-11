<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Dokter;
use App\Models\Farmasi;
use App\Models\Kunjungan;
use App\Models\Obat;
use App\Models\Pasien;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::id();
        $namaAdmin = Admin::where('user_id', $user)->value('nama_admin');
        return view('admin.dashboard', compact('namaAdmin'));
    }

    // ===============================
    // 📊 Grafik Kunjungan dengan Filter
    // ===============================
    public function getChartKunjungan(Request $request)
    {
        $periode = $request->get('periode', 'tahunan');

        // ===============================
        // HARIAN
        // ===============================
        if ($periode === 'harian') {
            $tanggal = $request->filled('tanggal')
                ? Carbon::parse($request->tanggal)
                : now();

            $total = Kunjungan::whereDate('tanggal_kunjungan', $tanggal->toDateString())->count();

            return response()->json([
                'mode_label' => 'Harian',
                'short_label' => $tanggal->format('d M Y'),
                'filter_label' => 'tanggal ' . $tanggal->format('d M Y'),
                'dataset_label' => 'Jumlah Kunjungan Harian',
                'labels' => [$tanggal->format('d M Y')],
                'values' => [$total],
            ]);
        }

        // ===============================
        // MINGGUAN
        // ===============================
        if ($periode === 'mingguan') {
            $startOfWeek = $this->parseWeekInput($request->get('minggu'));
            $endOfWeek = $startOfWeek->copy()->endOfWeek(Carbon::SUNDAY);

            $rawData = Kunjungan::selectRaw('DATE(tanggal_kunjungan) as tanggal, COUNT(*) as total')
                ->whereBetween('tanggal_kunjungan', [
                    $startOfWeek->toDateString(),
                    $endOfWeek->toDateString(),
                ])
                ->groupBy(DB::raw('DATE(tanggal_kunjungan)'))
                ->orderBy(DB::raw('DATE(tanggal_kunjungan)'))
                ->pluck('total', 'tanggal');

            $labels = [];
            $values = [];

            $namaHari = [
                'Monday' => 'Sen',
                'Tuesday' => 'Sel',
                'Wednesday' => 'Rab',
                'Thursday' => 'Kam',
                'Friday' => 'Jum',
                'Saturday' => 'Sab',
                'Sunday' => 'Min',
            ];

            $cursor = $startOfWeek->copy();

            while ($cursor->lte($endOfWeek)) {
                $tanggalKey = $cursor->toDateString();
                $labels[] = $namaHari[$cursor->format('l')] . ' (' . $cursor->format('d/m') . ')';
                $values[] = (int) ($rawData[$tanggalKey] ?? 0);
                $cursor->addDay();
            }

            return response()->json([
                'mode_label' => 'Mingguan',
                'short_label' => $startOfWeek->format('d M') . ' - ' . $endOfWeek->format('d M Y'),
                'filter_label' => 'minggu ' . $startOfWeek->format('d M Y') . ' - ' . $endOfWeek->format('d M Y'),
                'dataset_label' => 'Jumlah Kunjungan Mingguan',
                'labels' => $labels,
                'values' => $values,
            ]);
        }

        // ===============================
        // BULANAN
        // ===============================
        if ($periode === 'bulanan') {
            $bulan = $request->filled('bulan')
                ? Carbon::createFromFormat('Y-m', $request->bulan)->startOfMonth()
                : now()->startOfMonth();

            $startOfMonth = $bulan->copy()->startOfMonth();
            $endOfMonth = $bulan->copy()->endOfMonth();

            $rawData = Kunjungan::selectRaw('DAY(tanggal_kunjungan) as hari, COUNT(*) as total')
                ->whereBetween('tanggal_kunjungan', [
                    $startOfMonth->toDateString(),
                    $endOfMonth->toDateString(),
                ])
                ->groupBy(DB::raw('DAY(tanggal_kunjungan)'))
                ->orderBy(DB::raw('DAY(tanggal_kunjungan)'))
                ->pluck('total', 'hari');

            $labels = [];
            $values = [];

            for ($hari = 1; $hari <= $startOfMonth->daysInMonth; $hari++) {
                $labels[] = (string) $hari;
                $values[] = (int) ($rawData[$hari] ?? 0);
            }

            return response()->json([
                'mode_label' => 'Bulanan',
                'short_label' => $startOfMonth->format('M Y'),
                'filter_label' => 'bulan ' . $startOfMonth->translatedFormat('F Y'),
                'dataset_label' => 'Jumlah Kunjungan Bulanan',
                'labels' => $labels,
                'values' => $values,
            ]);
        }

        // ===============================
        // TAHUNAN
        // ===============================
        $tahun = (int) $request->get('tahun', now()->year);

        $rawData = Kunjungan::selectRaw('MONTH(tanggal_kunjungan) as bulan, COUNT(*) as total')
            ->whereYear('tanggal_kunjungan', $tahun)
            ->groupBy(DB::raw('MONTH(tanggal_kunjungan)'))
            ->orderBy(DB::raw('MONTH(tanggal_kunjungan)'))
            ->pluck('total', 'bulan');

        $namaBulan = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mei',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Agu',
            9 => 'Sep',
            10 => 'Okt',
            11 => 'Nov',
            12 => 'Des',
        ];

        $labels = [];
        $values = [];

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $labels[] = $namaBulan[$bulan];
            $values[] = (int) ($rawData[$bulan] ?? 0);
        }

        return response()->json([
            'mode_label' => 'Tahunan',
            'short_label' => (string) $tahun,
            'filter_label' => 'tahun ' . $tahun,
            'dataset_label' => 'Jumlah Kunjungan Tahunan',
            'labels' => $labels,
            'values' => $values,
        ]);
    }

    // ===============================
    // Helper parse week input
    // ===============================
    private function parseWeekInput(?string $weekInput): Carbon
    {
        if (!$weekInput || !preg_match('/^(\d{4})-W(\d{2})$/', $weekInput, $matches)) {
            return now()->startOfWeek(Carbon::MONDAY);
        }

        $year = (int) $matches[1];
        $week = (int) $matches[2];

        return Carbon::now()->setISODate($year, $week)->startOfWeek(Carbon::MONDAY);
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
    // 👩‍🔬 Total Farmasi
    // ===============================
    public function getTotalFarmasi()
    {
        return response()->json([
            'total' => Farmasi::count()
        ]);
    }

    // ===============================
    // 💊 Total Item Obat
    // ===============================
    public function getStokObat()
    {
        return response()->json([
            'total' => Obat::count()
        ]);
    }
}
