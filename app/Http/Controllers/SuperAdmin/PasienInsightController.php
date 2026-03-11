<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PasienInsightController extends Controller
{
    public function index(Request $request)
    {
        $periode = $this->resolvePeriode($request);
        $selectedDate = $this->resolveSelectedDate($request);
        $selectedMonth = $this->resolveSelectedMonth($request);
        $selectedYear = $this->resolveSelectedYear($request);

        $statusOptions = ['Pending', 'Waiting', 'Engaged', 'Payment', 'Succeed', 'Canceled'];

        $poliList = DB::table('poli')
            ->orderBy('nama_poli')
            ->get();

        $dokterList = DB::table('dokter')
            ->orderBy('nama_dokter')
            ->get();

        $patientsQuery = DB::table('pasien as ps')
            ->join('kunjungan as k', 'k.pasien_id', '=', 'ps.id');

        $this->applyFilters($patientsQuery, $request);

        $patients = $patientsQuery
            ->select(
                'ps.id',
                'ps.no_emr',
                'ps.nik',
                'ps.no_bpjs',
                'ps.nama_pasien',
                'ps.no_hp_pasien',
                'ps.jenis_kelamin',
                'ps.tanggal_lahir',
                DB::raw('COUNT(k.id) as total_kunjungan'),
                DB::raw('COUNT(DISTINCT k.poli_id) as total_poli'),
                DB::raw('MAX(k.tanggal_kunjungan) as terakhir_kunjungan')
            )
            ->groupBy(
                'ps.id',
                'ps.no_emr',
                'ps.nik',
                'ps.no_bpjs',
                'ps.nama_pasien',
                'ps.no_hp_pasien',
                'ps.jenis_kelamin',
                'ps.tanggal_lahir'
            )
            ->orderByDesc('total_kunjungan')
            ->orderBy('ps.nama_pasien')
            ->get()
            ->map(function ($item) {
                $item->umur = $item->tanggal_lahir
                    ? Carbon::parse($item->tanggal_lahir)->age . ' th'
                    : '-';

                $item->tanggal_lahir_label = $item->tanggal_lahir
                    ? Carbon::parse($item->tanggal_lahir)->translatedFormat('d M Y')
                    : '-';

                $item->terakhir_kunjungan_label = $item->terakhir_kunjungan
                    ? Carbon::parse($item->terakhir_kunjungan)->translatedFormat('d M Y')
                    : '-';

                return $item;
            });

        $visitBase = DB::table('kunjungan as k')
            ->join('pasien as ps', 'ps.id', '=', 'k.pasien_id')
            ->leftJoin('poli as pl', 'pl.id', '=', 'k.poli_id')
            ->leftJoin('dokter as d', 'd.id', '=', 'k.dokter_id');

        $this->applyFilters($visitBase, $request);

        $stats = [
            'totalPasien'       => $patients->count(),
            'totalKunjungan'    => (clone $visitBase)->count('k.id'),
            'kunjunganSelesai'  => (clone $visitBase)->where('k.status', 'Succeed')->count('k.id'),
            'poliAktif'         => (clone $visitBase)->whereNotNull('k.poli_id')->distinct()->count('k.poli_id'),
            'dokterAktif'       => (clone $visitBase)->whereNotNull('k.dokter_id')->distinct()->count('k.dokter_id'),
        ];

        $chartPerPoli = (clone $visitBase)
            ->select('pl.nama_poli', DB::raw('COUNT(k.id) as total'))
            ->whereNotNull('pl.id')
            ->groupBy('pl.id', 'pl.nama_poli')
            ->orderByDesc('total')
            ->get();

        $chartStatus = (clone $visitBase)
            ->select('k.status', DB::raw('COUNT(k.id) as total'))
            ->groupBy('k.status')
            ->orderByDesc('total')
            ->get();

        $trendChart = $this->buildTrendChart($request);
        $trendMeta = $this->getTrendMeta($periode, $selectedDate, $selectedMonth, $selectedYear);

        return view('super-admin.pasien-insight.index', [
            'patients'             => $patients,
            'poliList'             => $poliList,
            'dokterList'           => $dokterList,
            'stats'                => $stats,
            'statusOptions'        => $statusOptions,
            'filters'              => [
                'search'      => $request->get('search', ''),
                'poli_id'     => $request->get('poli_id', ''),
                'dokter_id'   => $request->get('dokter_id', ''),
                'status'      => $request->get('status', ''),
                'periode'     => $periode,
                'tanggal'     => $selectedDate->format('Y-m-d'),
                'bulan_tahun' => $selectedMonth->format('Y-m'),
                'tahun'       => (string) $selectedYear,
            ],
            'periodeLabel'         => $this->getPeriodeLabel($periode, $selectedDate, $selectedMonth, $selectedYear),
            'trendTitle'           => $trendMeta['title'],
            'trendSubtitle'        => $trendMeta['subtitle'],
            'trendDatasetLabel'    => $trendMeta['dataset_label'],
            'chartPerPoliLabels'   => $chartPerPoli->pluck('nama_poli')->values(),
            'chartPerPoliValues'   => $chartPerPoli->pluck('total')->map(fn($v) => (int) $v)->values(),
            'chartStatusLabels'    => $chartStatus->pluck('status')->values(),
            'chartStatusValues'    => $chartStatus->pluck('total')->map(fn($v) => (int) $v)->values(),
            'chartTrendLabels'     => $trendChart['labels'],
            'chartTrendValues'     => $trendChart['values'],
        ]);
    }

    public function show($id)
    {
        $pasien = DB::table('pasien as ps')
            ->leftJoin('user as u', 'u.id', '=', 'ps.user_id')
            ->where('ps.id', $id)
            ->select(
                'ps.*',
                'u.email'
            )
            ->first();

        abort_if(!$pasien, 404);

        $pasien->umur = $pasien->tanggal_lahir
            ? Carbon::parse($pasien->tanggal_lahir)->age . ' tahun'
            : '-';

        $pasien->tanggal_lahir_label = $pasien->tanggal_lahir
            ? Carbon::parse($pasien->tanggal_lahir)->translatedFormat('d M Y')
            : '-';

        $visitBase = DB::table('kunjungan as k')
            ->leftJoin('poli as pl', 'pl.id', '=', 'k.poli_id')
            ->leftJoin('dokter as d', 'd.id', '=', 'k.dokter_id')
            ->where('k.pasien_id', $id);

        $latestVisit = (clone $visitBase)
            ->select(
                'k.tanggal_kunjungan',
                'k.status',
                'pl.nama_poli',
                'd.nama_dokter'
            )
            ->orderByDesc('k.tanggal_kunjungan')
            ->orderByDesc('k.id')
            ->first();

        $riwayat = (clone $visitBase)
            ->select(
                'k.id',
                'k.tanggal_kunjungan',
                'k.no_antrian',
                'k.keluhan_awal',
                'k.status',
                'pl.nama_poli',
                'd.nama_dokter'
            )
            ->orderByDesc('k.tanggal_kunjungan')
            ->orderByDesc('k.id')
            ->get()
            ->map(function ($item) {
                $item->tanggal_kunjungan_label = $item->tanggal_kunjungan
                    ? Carbon::parse($item->tanggal_kunjungan)->translatedFormat('d M Y')
                    : '-';

                $item->status_class = $this->statusClass($item->status);
                $item->nama_poli = $item->nama_poli ?: '-';
                $item->nama_dokter = $item->nama_dokter ?: '-';
                $item->no_antrian = $item->no_antrian ?: '-';
                $item->keluhan_awal = $item->keluhan_awal ?: '-';

                return $item;
            });

        $stats = [
            'totalKunjungan'      => (clone $visitBase)->count('k.id'),
            'totalPoli'           => (clone $visitBase)->whereNotNull('k.poli_id')->distinct()->count('k.poli_id'),
            'totalDokter'         => (clone $visitBase)->whereNotNull('k.dokter_id')->distinct()->count('k.dokter_id'),
            'kunjunganTerakhir'   => $latestVisit && $latestVisit->tanggal_kunjungan
                ? Carbon::parse($latestVisit->tanggal_kunjungan)->translatedFormat('d M Y')
                : '-',
            'poliTerakhir'        => $latestVisit->nama_poli ?? '-',
            'dokterTerakhir'      => $latestVisit->nama_dokter ?? '-',
            'statusTerakhir'      => $latestVisit->status ?? '-',
            'statusTerakhirClass' => $this->statusClass($latestVisit->status ?? null),
        ];

        $chartPerPoli = (clone $visitBase)
            ->select('pl.nama_poli', DB::raw('COUNT(k.id) as total'))
            ->whereNotNull('pl.id')
            ->groupBy('pl.id', 'pl.nama_poli')
            ->orderByDesc('total')
            ->get();

        $chartStatus = (clone $visitBase)
            ->select('k.status', DB::raw('COUNT(k.id) as total'))
            ->groupBy('k.status')
            ->orderByDesc('total')
            ->get();

        $monthlyRaw = (clone $visitBase)
            ->whereDate('k.tanggal_kunjungan', '>=', Carbon::now()->startOfMonth()->subMonths(11))
            ->selectRaw("DATE_FORMAT(k.tanggal_kunjungan, '%Y-%m') as month_key, COUNT(k.id) as total")
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->pluck('total', 'month_key');

        $chartMonthlyLabels = [];
        $chartMonthlyValues = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $key = $month->format('Y-m');

            $chartMonthlyLabels[] = $month->translatedFormat('M Y');
            $chartMonthlyValues[] = (int) ($monthlyRaw[$key] ?? 0);
        }

        return view('super-admin.pasien-insight.show', [
            'pasien'               => $pasien,
            'stats'                => $stats,
            'riwayat'              => $riwayat,
            'chartPerPoliLabels'   => $chartPerPoli->pluck('nama_poli')->values(),
            'chartPerPoliValues'   => $chartPerPoli->pluck('total')->map(fn($v) => (int) $v)->values(),
            'chartStatusLabels'    => $chartStatus->pluck('status')->values(),
            'chartStatusValues'    => $chartStatus->pluck('total')->map(fn($v) => (int) $v)->values(),
            'chartMonthlyLabels'   => $chartMonthlyLabels,
            'chartMonthlyValues'   => $chartMonthlyValues,
        ]);
    }

    private function applyFilters($query, Request $request): void
    {
        $this->applyCommonFilters($query, $request);
        $this->applyPeriodFilter($query, $request);
    }

    private function applyCommonFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('ps.nama_pasien', 'like', '%' . $search . '%')
                    ->orWhere('ps.no_emr', 'like', '%' . $search . '%')
                    ->orWhere('ps.nik', 'like', '%' . $search . '%')
                    ->orWhere('ps.no_bpjs', 'like', '%' . $search . '%')
                    ->orWhere('ps.no_hp_pasien', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('poli_id')) {
            $query->where('k.poli_id', $request->poli_id);
        }

        if ($request->filled('dokter_id')) {
            $query->where('k.dokter_id', $request->dokter_id);
        }

        if ($request->filled('status')) {
            $query->where('k.status', $request->status);
        }
    }

    private function applyPeriodFilter($query, Request $request): void
    {
        $periode = $this->resolvePeriode($request);

        if ($periode === 'harian') {
            $selectedDate = $this->resolveSelectedDate($request);
            $query->whereDate('k.tanggal_kunjungan', $selectedDate->toDateString());
            return;
        }

        if ($periode === 'bulanan') {
            $selectedMonth = $this->resolveSelectedMonth($request);
            $query->whereYear('k.tanggal_kunjungan', $selectedMonth->year)
                ->whereMonth('k.tanggal_kunjungan', $selectedMonth->month);
            return;
        }

        if ($periode === 'tahunan') {
            $selectedYear = $this->resolveSelectedYear($request);
            $query->whereYear('k.tanggal_kunjungan', $selectedYear);
        }
    }

    private function buildTrendChart(Request $request): array
    {
        $periode = $this->resolvePeriode($request);
        $selectedDate = $this->resolveSelectedDate($request);
        $selectedMonth = $this->resolveSelectedMonth($request);
        $selectedYear = $this->resolveSelectedYear($request);

        $baseQuery = DB::table('kunjungan as k')
            ->join('pasien as ps', 'ps.id', '=', 'k.pasien_id')
            ->leftJoin('poli as pl', 'pl.id', '=', 'k.poli_id')
            ->leftJoin('dokter as d', 'd.id', '=', 'k.dokter_id');

        $this->applyCommonFilters($baseQuery, $request);

        if ($periode === 'harian') {
            $total = (clone $baseQuery)
                ->whereDate('k.tanggal_kunjungan', $selectedDate->toDateString())
                ->count('k.id');

            return [
                'labels' => [$selectedDate->translatedFormat('d M Y')],
                'values' => [(int) $total],
            ];
        }

        if ($periode === 'bulanan') {
            $start = $selectedMonth->copy()->startOfMonth();
            $end = $selectedMonth->copy()->endOfMonth();

            $raw = (clone $baseQuery)
                ->whereYear('k.tanggal_kunjungan', $selectedMonth->year)
                ->whereMonth('k.tanggal_kunjungan', $selectedMonth->month)
                ->selectRaw("DATE(k.tanggal_kunjungan) as label_key, COUNT(k.id) as total")
                ->groupBy('label_key')
                ->orderBy('label_key')
                ->pluck('total', 'label_key');

            $labels = [];
            $values = [];

            $cursor = $start->copy();
            while ($cursor <= $end) {
                $key = $cursor->format('Y-m-d');
                $labels[] = $cursor->format('d');
                $values[] = (int) ($raw[$key] ?? 0);
                $cursor->addDay();
            }

            return [
                'labels' => $labels,
                'values' => $values,
            ];
        }

        $raw = (clone $baseQuery)
            ->whereYear('k.tanggal_kunjungan', $selectedYear)
            ->selectRaw("DATE_FORMAT(k.tanggal_kunjungan, '%Y-%m') as label_key, COUNT(k.id) as total")
            ->groupBy('label_key')
            ->orderBy('label_key')
            ->pluck('total', 'label_key');

        $labels = [];
        $values = [];

        for ($month = 1; $month <= 12; $month++) {
            $date = Carbon::create($selectedYear, $month, 1);
            $key = $date->format('Y-m');

            $labels[] = $date->translatedFormat('M');
            $values[] = (int) ($raw[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    private function resolvePeriode(Request $request): string
    {
        $periode = $request->get('periode', 'bulanan');

        return in_array($periode, ['harian', 'bulanan', 'tahunan'], true)
            ? $periode
            : 'bulanan';
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
            return Carbon::createFromFormat('Y-m', $request->get('bulan_tahun', now()->format('Y-m')))->startOfMonth();
        } catch (\Throwable $th) {
            return now()->startOfMonth();
        }
    }

    private function resolveSelectedYear(Request $request): int
    {
        $year = (int) $request->get('tahun', now()->format('Y'));

        if ($year < 2000 || $year > 2100) {
            return (int) now()->format('Y');
        }

        return $year;
    }

    private function getPeriodeLabel(string $periode, Carbon $selectedDate, Carbon $selectedMonth, int $selectedYear): string
    {
        return match ($periode) {
            'harian'  => 'Harian · ' . $selectedDate->translatedFormat('d F Y'),
            'bulanan' => 'Bulanan · ' . $selectedMonth->translatedFormat('F Y'),
            'tahunan' => 'Tahunan · ' . $selectedYear,
            default   => 'Bulanan · ' . now()->translatedFormat('F Y'),
        };
    }

    private function getTrendMeta(string $periode, Carbon $selectedDate, Carbon $selectedMonth, int $selectedYear): array
    {
        if ($periode === 'harian') {
            return [
                'title' => 'Kunjungan Pada Tanggal Dipilih',
                'subtitle' => 'Grafik jumlah kunjungan pada tanggal ' . $selectedDate->translatedFormat('d F Y'),
                'dataset_label' => 'Kunjungan Harian',
            ];
        }

        if ($periode === 'bulanan') {
            return [
                'title' => 'Kunjungan Harian Dalam Bulan',
                'subtitle' => 'Grafik jumlah kunjungan per hari pada ' . $selectedMonth->translatedFormat('F Y'),
                'dataset_label' => 'Kunjungan Bulanan',
            ];
        }

        return [
            'title' => 'Kunjungan Bulanan Dalam Tahun',
            'subtitle' => 'Grafik jumlah kunjungan per bulan pada tahun ' . $selectedYear,
            'dataset_label' => 'Kunjungan Tahunan',
        ];
    }

    private function statusClass(?string $status): string
    {
        return match ($status) {
            'Pending'  => 'bg-amber-100 text-amber-700',
            'Waiting'  => 'bg-sky-100 text-sky-700',
            'Engaged'  => 'bg-indigo-100 text-indigo-700',
            'Payment'  => 'bg-violet-100 text-violet-700',
            'Succeed'  => 'bg-emerald-100 text-emerald-700',
            'Canceled' => 'bg-rose-100 text-rose-700',
            default    => 'bg-slate-100 text-slate-700',
        };
    }
}
