<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Dokter;
use App\Models\Farmasi;
use App\Models\Kunjungan;
use App\Models\Pasien;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $namaAdmin = Admin::where('user_id', $userId)->value('nama_admin') ?? 'Admin';

        return view('admin.dashboard', compact('namaAdmin'));
    }

    public function getDashboardStats()
    {
        $today = Carbon::today(config('app.timezone', 'Asia/Jakarta'))->toDateString();

        return response()->json([
            'kunjungan_hari_ini' => Kunjungan::whereDate('tanggal_kunjungan', $today)
                ->count(),
            'dokter'  => Dokter::count(),
            'pasien'  => Pasien::count(),
            'farmasi' => Farmasi::count(),
        ]);
    }

    public function getChartKunjungan(Request $request)
    {
        $periode = $this->resolvePeriode($request);

        if ($periode === 'harian') {
            $tanggal = $this->resolveSelectedDate($request);

            $total = Kunjungan::whereDate('tanggal_kunjungan', $tanggal->toDateString())->count();

            return response()->json([
                'mode_label'    => 'Harian',
                'short_label'   => $tanggal->translatedFormat('d M Y'),
                'filter_label'  => 'Tanggal ' . $tanggal->translatedFormat('d F Y'),
                'dataset_label' => 'Jumlah Kunjungan Harian',
                'labels'        => [$tanggal->translatedFormat('d M Y')],
                'values'        => [(int) $total],
            ]);
        }

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
                'Monday'    => 'Sen',
                'Tuesday'   => 'Sel',
                'Wednesday' => 'Rab',
                'Thursday'  => 'Kam',
                'Friday'    => 'Jum',
                'Saturday'  => 'Sab',
                'Sunday'    => 'Min',
            ];

            $cursor = $startOfWeek->copy();

            while ($cursor->lte($endOfWeek)) {
                $tanggalKey = $cursor->toDateString();
                $labels[] = $namaHari[$cursor->format('l')] . ' (' . $cursor->format('d/m') . ')';
                $values[] = (int) ($rawData[$tanggalKey] ?? 0);
                $cursor->addDay();
            }

            return response()->json([
                'mode_label'    => 'Mingguan',
                'short_label'   => $startOfWeek->translatedFormat('d M') . ' - ' . $endOfWeek->translatedFormat('d M Y'),
                'filter_label'  => 'Minggu ' . $startOfWeek->translatedFormat('d M Y') . ' - ' . $endOfWeek->translatedFormat('d M Y'),
                'dataset_label' => 'Jumlah Kunjungan Mingguan',
                'labels'        => $labels,
                'values'        => $values,
            ]);
        }

        if ($periode === 'bulanan') {
            $bulan = $this->resolveSelectedMonth($request);
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
                'mode_label'    => 'Bulanan',
                'short_label'   => $startOfMonth->translatedFormat('M Y'),
                'filter_label'  => 'Bulan ' . $startOfMonth->translatedFormat('F Y'),
                'dataset_label' => 'Jumlah Kunjungan Bulanan',
                'labels'        => $labels,
                'values'        => $values,
            ]);
        }

        $tahun = $this->resolveSelectedYear($request);

        $rawData = Kunjungan::selectRaw('MONTH(tanggal_kunjungan) as bulan, COUNT(*) as total')
            ->whereYear('tanggal_kunjungan', $tahun)
            ->groupBy(DB::raw('MONTH(tanggal_kunjungan)'))
            ->orderBy(DB::raw('MONTH(tanggal_kunjungan)'))
            ->pluck('total', 'bulan');

        $namaBulan = [
            1  => 'Jan',
            2  => 'Feb',
            3  => 'Mar',
            4  => 'Apr',
            5  => 'Mei',
            6  => 'Jun',
            7  => 'Jul',
            8  => 'Agu',
            9  => 'Sep',
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
            'mode_label'    => 'Tahunan',
            'short_label'   => (string) $tahun,
            'filter_label'  => 'Tahun ' . $tahun,
            'dataset_label' => 'Jumlah Kunjungan Tahunan',
            'labels'        => $labels,
            'values'        => $values,
        ]);
    }

    private function resolvePeriode(Request $request): string
    {
        $periode = $request->get('periode', 'tahunan');

        return in_array($periode, ['harian', 'mingguan', 'bulanan', 'tahunan'], true)
            ? $periode
            : 'tahunan';
    }

    private function resolveSelectedDate(Request $request): Carbon
    {
        try {
            return Carbon::parse($request->get('tanggal', now()->toDateString()));
        } catch (\Throwable $th) {
            return now();
        }
    }

    private function resolveSelectedMonth(Request $request): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m', $request->get('bulan', now()->format('Y-m')))->startOfMonth();
        } catch (\Throwable $th) {
            return now()->startOfMonth();
        }
    }

    private function resolveSelectedYear(Request $request): int
    {
        $year = (int) $request->get('tahun', now()->year);

        if ($year < 2000 || $year > 2100) {
            return (int) now()->year;
        }

        return $year;
    }

    private function parseWeekInput(?string $weekInput): Carbon
    {
        if (!$weekInput || !preg_match('/^(\d{4})-W(\d{2})$/', $weekInput, $matches)) {
            return now()->startOfWeek(Carbon::MONDAY);
        }

        $year = (int) $matches[1];
        $week = (int) $matches[2];

        return Carbon::now()->setISODate($year, $week)->startOfWeek(Carbon::MONDAY);
    }
}
