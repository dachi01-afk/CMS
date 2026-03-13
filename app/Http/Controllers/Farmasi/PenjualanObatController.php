<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PenjualanObatController extends Controller
{
    protected function penjualanObatJoinDetail()
    {
        return DB::table('penjualan_obat as po')->leftJoin('penjualan_obat_detail as pod', 'pod.penjualan_obat_id', '=', 'po.id');
    }

    protected function getModeLabel($periode)
    {
        if ($periode === 'harian') {
            return 'Harian';
        }

        if ($periode === 'mingguan') {
            return 'Mingguan';
        }

        if ($periode === 'bulanan') {
            return 'Bulanan';
        }

        return 'Tahunan';
    }

    protected function parseWeekValue(?string $weekValue, string $tz): Carbon
    {
        try {
            if ($weekValue && preg_match('/^(\d{4})-W(\d{2})$/', $weekValue, $matches)) {
                $year = (int) $matches[1];
                $week = (int) $matches[2];

                return Carbon::now($tz)->setISODate($year, $week, 1)->startOfDay();
            }
        } catch (\Throwable $th) {
        }

        return Carbon::now($tz)->startOfWeek(Carbon::MONDAY);
    }

    protected function parseMonthValue(?string $monthValue, string $tz): Carbon
    {
        try {
            if ($monthValue) {
                return Carbon::createFromFormat('Y-m', $monthValue, $tz)->startOfMonth();
            }
        } catch (\Throwable $th) {
        }

        return Carbon::now($tz)->startOfMonth();
    }

    protected function getChartFilterContext(Request $request, string $tz = 'Asia/Jakarta'): array
    {
        $now = Carbon::now($tz);
        $periode = $request->query('periode', $request->query('mode_periode', 'tahunan'));

        if (!in_array($periode, ['harian', 'mingguan', 'bulanan', 'tahunan'])) {
            $periode = 'tahunan';
        }

        if ($periode === 'harian') {
            try {
                $anchor = $request->query('tanggal')
                    ? Carbon::parse($request->query('tanggal'), $tz)->startOfDay()
                    : $now->copy()->startOfDay();
            } catch (\Throwable $th) {
                $anchor = $now->copy()->startOfDay();
            }

            return [
                'periode' => $periode,
                'anchor'  => $anchor,
                'raw'     => $anchor->toDateString(),
            ];
        }

        if ($periode === 'mingguan') {
            $anchor = $this->parseWeekValue($request->query('minggu'), $tz);

            return [
                'periode' => $periode,
                'anchor'  => $anchor,
                'raw'     => sprintf('%d-W%02d', $anchor->isoWeekYear, $anchor->isoWeek),
            ];
        }

        if ($periode === 'bulanan') {
            $anchor = $this->parseMonthValue($request->query('bulan'), $tz);

            return [
                'periode' => $periode,
                'anchor'  => $anchor,
                'raw'     => $anchor->format('Y-m'),
            ];
        }

        $tahun = (int) $request->query('tahun', $now->year);
        if ($tahun < 2020 || $tahun > 2100) {
            $tahun = (int) $now->year;
        }

        $anchor = Carbon::create($tahun, 1, 1, 0, 0, 0, $tz);

        return [
            'periode' => $periode,
            'anchor'  => $anchor,
            'raw'     => (string) $tahun,
        ];
    }

    protected function getDateRangeByFilter(array $filter): array
    {
        $periode = $filter['periode'];
        $anchor = $filter['anchor'];

        if ($periode === 'harian') {
            $start = $anchor->copy()->startOfDay();
            $end = $anchor->copy()->endOfDay();
            $filterLabel = $anchor->copy()->locale('id')->translatedFormat('d F Y');
        } elseif ($periode === 'mingguan') {
            $start = $anchor->copy()->startOfWeek(Carbon::MONDAY);
            $end = $anchor->copy()->endOfWeek(Carbon::SUNDAY);
            $filterLabel = $start->copy()->locale('id')->translatedFormat('d M Y') . ' - ' . $end->copy()->locale('id')->translatedFormat('d M Y');
        } elseif ($periode === 'bulanan') {
            $start = $anchor->copy()->startOfMonth();
            $end = $anchor->copy()->endOfMonth();
            $filterLabel = $anchor->copy()->locale('id')->translatedFormat('F Y');
        } else {
            $start = $anchor->copy()->startOfYear();
            $end = $anchor->copy()->endOfYear();
            $filterLabel = 'Tahun ' . $anchor->year;
        }

        return [
            'start'        => $start,
            'end'          => $end,
            'filter_label' => $filterLabel,
        ];
    }

    protected function getTodayOnlyFilterContext(string $tz = 'Asia/Jakarta'): array
    {
        $anchor = Carbon::now($tz)->startOfDay();

        return [
            'periode' => 'harian',
            'anchor'  => $anchor,
            'raw'     => $anchor->toDateString(),
        ];
    }

    public function penjualanObatPage(Request $request)
    {
        $tz = 'Asia/Jakarta';
        $isTodayOnly = (int) $request->query('today_only', 0) === 1;
        $todayLabel = Carbon::now($tz)->locale('id')->translatedFormat('d F Y');

        return view('farmasi.penjualan-obat.index', compact('isTodayOnly', 'todayLabel'));
    }

    public function penjualanObatHariIniPage()
    {
        $tz = 'Asia/Jakarta';

        $filter = $this->getTodayOnlyFilterContext($tz);
        $range = $this->getDateRangeByFilter($filter);

        $start = $range['start'];
        $end = $range['end'];
        $filterLabel = $range['filter_label'];

        $data = DB::table('penjualan_obat as po')
            ->leftJoin('pasien as p', 'p.id', '=', 'po.pasien_id')
            ->leftJoin('penjualan_obat_detail as pod', 'pod.penjualan_obat_id', '=', 'po.id')
            ->select(
                'po.id',
                'po.kode_transaksi',
                'po.tanggal_transaksi',
                'po.status',
                DB::raw('COALESCE(p.nama_pasien, "-") as nama_pasien'),
                DB::raw('COALESCE(SUM(pod.sub_total), 0) as total')
            )
            ->whereBetween('po.tanggal_transaksi', [
                $start->copy()->toDateTimeString(),
                $end->copy()->toDateTimeString(),
            ])
            ->groupBy('po.id', 'po.kode_transaksi', 'po.tanggal_transaksi', 'po.status', 'p.nama_pasien')
            ->orderByDesc('po.tanggal_transaksi')
            ->get();

        $summary = $this->penjualanObatJoinDetail()
            ->whereBetween('po.tanggal_transaksi', [
                $start->copy()->toDateTimeString(),
                $end->copy()->toDateTimeString(),
            ])
            ->selectRaw('COUNT(DISTINCT po.kode_transaksi) as total_transaksi')
            ->selectRaw("COALESCE(SUM(CASE WHEN po.status = 'Sudah Bayar' THEN pod.sub_total ELSE 0 END), 0) as total_pemasukan")
            ->first();

        return response()->json([
            'data' => $data,
            'meta' => [
                'is_today_only'   => true,
                'periode'         => 'harian',
                'mode_label'      => 'Hari Ini',
                'filter_label'    => 'Hari Ini - ' . $filterLabel,
                'total_transaksi' => (int) ($summary->total_transaksi ?? 0),
                'total_pemasukan' => (float) ($summary->total_pemasukan ?? 0),
            ],
        ]);
    }

    public function penjualanObatHariIni()
    {
        $tz = 'Asia/Jakarta';

        $filter = $this->getTodayOnlyFilterContext($tz);
        $range = $this->getDateRangeByFilter($filter);

        $start = $range['start'];
        $end = $range['end'];
        $filterLabel = $range['filter_label'];

        $data = DB::table('penjualan_obat as po')
            ->leftJoin('pasien as p', 'p.id', '=', 'po.pasien_id')
            ->leftJoin('penjualan_obat_detail as pod', 'pod.penjualan_obat_id', '=', 'po.id')
            ->select(
                'po.id',
                'po.kode_transaksi',
                'po.tanggal_transaksi',
                'po.status',
                DB::raw('COALESCE(p.nama_pasien, "-") as nama_pasien'),
                DB::raw('COALESCE(SUM(pod.sub_total), 0) as total')
            )
            ->whereBetween('po.tanggal_transaksi', [
                $start->copy()->toDateTimeString(),
                $end->copy()->toDateTimeString(),
            ])
            ->groupBy('po.id', 'po.kode_transaksi', 'po.tanggal_transaksi', 'po.status', 'p.nama_pasien')
            ->orderByDesc('po.tanggal_transaksi')
            ->get();

        $summary = $this->penjualanObatJoinDetail()
            ->whereBetween('po.tanggal_transaksi', [
                $start->copy()->toDateTimeString(),
                $end->copy()->toDateTimeString(),
            ])
            ->selectRaw('COUNT(DISTINCT po.kode_transaksi) as total_transaksi')
            ->selectRaw("COALESCE(SUM(CASE WHEN po.status = 'Sudah Bayar' THEN pod.sub_total ELSE 0 END), 0) as total_pemasukan")
            ->first();

        return response()->json([
            'data' => $data,
            'meta' => [
                'is_today_only'   => true,
                'periode'         => 'harian',
                'mode_label'      => 'Hari Ini',
                'filter_label'    => 'Hari Ini - ' . $filterLabel,
                'total_transaksi' => (int) ($summary->total_transaksi ?? 0),
                'total_pemasukan' => (float) ($summary->total_pemasukan ?? 0),
            ],
        ]);
    }

    public function penjualanObatData(Request $request)
    {
        $tz = 'Asia/Jakarta';
        $isTodayOnly = (int) $request->query('today_only', 0) === 1;

        $filter = $isTodayOnly
            ? $this->getTodayOnlyFilterContext($tz)
            : $this->getChartFilterContext($request, $tz);

        $range = $this->getDateRangeByFilter($filter);

        $start = $range['start'];
        $end = $range['end'];
        $filterLabel = $range['filter_label'];

        $data = DB::table('penjualan_obat as po')
            ->leftJoin('pasien as p', 'p.id', '=', 'po.pasien_id')
            ->leftJoin('penjualan_obat_detail as pod', 'pod.penjualan_obat_id', '=', 'po.id')
            ->select(
                'po.id',
                'po.kode_transaksi',
                'po.tanggal_transaksi',
                'po.status',
                DB::raw('COALESCE(p.nama_pasien, "-") as nama_pasien'),
                DB::raw('COALESCE(SUM(pod.sub_total), 0) as total')
            )
            ->whereBetween('po.tanggal_transaksi', [
                $start->copy()->toDateTimeString(),
                $end->copy()->toDateTimeString(),
            ])
            ->groupBy('po.id', 'po.kode_transaksi', 'po.tanggal_transaksi', 'po.status', 'p.nama_pasien')
            ->orderByDesc('po.tanggal_transaksi')
            ->get();

        $summary = $this->penjualanObatJoinDetail()
            ->whereBetween('po.tanggal_transaksi', [
                $start->copy()->toDateTimeString(),
                $end->copy()->toDateTimeString(),
            ])
            ->selectRaw('COUNT(DISTINCT po.kode_transaksi) as total_transaksi')
            ->selectRaw("COALESCE(SUM(CASE WHEN po.status = 'Sudah Bayar' THEN pod.sub_total ELSE 0 END), 0) as total_pemasukan")
            ->first();

        return response()->json([
            'data' => $data,
            'meta' => [
                'is_today_only'   => $isTodayOnly,
                'periode'         => $filter['periode'],
                'mode_label'      => $isTodayOnly ? 'Hari Ini' : $this->getModeLabel($filter['periode']),
                'filter_label'    => $isTodayOnly ? 'Hari Ini - ' . $filterLabel : $filterLabel,
                'total_transaksi' => (int) ($summary->total_transaksi ?? 0),
                'total_pemasukan' => (float) ($summary->total_pemasukan ?? 0),
            ],
        ]);
    }

    public function chartPenjualanObat(Request $request)
    {
        $tz = 'Asia/Jakarta';
        $filter = $this->getChartFilterContext($request, $tz);

        $periode = $filter['periode'];
        $anchor = $filter['anchor'];

        $labels = [];
        $seriesJumlahTransaksi = [];
        $seriesTotalPemasukan = [];
        $mapTransaksi = [];
        $mapPemasukan = [];

        $modeLabel = $this->getModeLabel($periode);
        $filterLabel = '-';
        $shortLabel = '-';
        $xTitle = '';

        if ($periode === 'harian') {
            $hari = $anchor->copy()->toDateString();

            $rows = $this->penjualanObatJoinDetail()
                ->selectRaw('HOUR(po.tanggal_transaksi) as idx')
                ->selectRaw('COUNT(DISTINCT po.kode_transaksi) as jumlah_transaksi')
                ->selectRaw("COALESCE(SUM(CASE WHEN po.status = 'Sudah Bayar' THEN pod.sub_total ELSE 0 END), 0) as total_pemasukan")
                ->whereDate('po.tanggal_transaksi', $hari)
                ->groupBy('idx')
                ->orderBy('idx')
                ->get();

            foreach ($rows as $row) {
                $mapTransaksi[(int) $row->idx] = (int) $row->jumlah_transaksi;
                $mapPemasukan[(int) $row->idx] = (float) $row->total_pemasukan;
            }

            for ($hour = 0; $hour <= 23; $hour++) {
                $labels[] = sprintf('%02d:00', $hour);
                $seriesJumlahTransaksi[] = $mapTransaksi[$hour] ?? 0;
                $seriesTotalPemasukan[] = $mapPemasukan[$hour] ?? 0;
            }

            $filterLabel = $anchor->copy()->locale('id')->translatedFormat('d F Y');
            $shortLabel = $anchor->format('d/m/Y');
            $xTitle = 'Jam';
        } elseif ($periode === 'mingguan') {
            $start = $anchor->copy()->startOfWeek(Carbon::MONDAY);
            $end = $anchor->copy()->endOfWeek(Carbon::SUNDAY);

            $rows = $this->penjualanObatJoinDetail()
                ->selectRaw('WEEKDAY(po.tanggal_transaksi) as idx')
                ->selectRaw('COUNT(DISTINCT po.kode_transaksi) as jumlah_transaksi')
                ->selectRaw("COALESCE(SUM(CASE WHEN po.status = 'Sudah Bayar' THEN pod.sub_total ELSE 0 END), 0) as total_pemasukan")
                ->whereBetween(DB::raw('DATE(po.tanggal_transaksi)'), [$start->toDateString(), $end->toDateString()])
                ->groupBy('idx')
                ->orderBy('idx')
                ->get();

            foreach ($rows as $row) {
                $mapTransaksi[(int) $row->idx] = (int) $row->jumlah_transaksi;
                $mapPemasukan[(int) $row->idx] = (float) $row->total_pemasukan;
            }

            $hariIndo = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

            for ($day = 0; $day <= 6; $day++) {
                $labels[] = $hariIndo[$day];
                $seriesJumlahTransaksi[] = $mapTransaksi[$day] ?? 0;
                $seriesTotalPemasukan[] = $mapPemasukan[$day] ?? 0;
            }

            $filterLabel = $start->copy()->locale('id')->translatedFormat('d M Y') . ' - ' . $end->copy()->locale('id')->translatedFormat('d M Y');
            $shortLabel = 'Minggu ' . $start->isoWeek;
            $xTitle = 'Hari (Minggu Dipilih)';
        } elseif ($periode === 'bulanan') {
            $start = $anchor->copy()->startOfMonth();
            $end = $anchor->copy()->endOfMonth();
            $daysInMonth = $anchor->daysInMonth;

            $rows = $this->penjualanObatJoinDetail()
                ->selectRaw('DAY(po.tanggal_transaksi) as idx')
                ->selectRaw('COUNT(DISTINCT po.kode_transaksi) as jumlah_transaksi')
                ->selectRaw("COALESCE(SUM(CASE WHEN po.status = 'Sudah Bayar' THEN pod.sub_total ELSE 0 END), 0) as total_pemasukan")
                ->whereBetween(DB::raw('DATE(po.tanggal_transaksi)'), [$start->toDateString(), $end->toDateString()])
                ->groupBy('idx')
                ->orderBy('idx')
                ->get();

            foreach ($rows as $row) {
                $mapTransaksi[(int) $row->idx] = (int) $row->jumlah_transaksi;
                $mapPemasukan[(int) $row->idx] = (float) $row->total_pemasukan;
            }

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $labels[] = sprintf('%02d', $day);
                $seriesJumlahTransaksi[] = $mapTransaksi[$day] ?? 0;
                $seriesTotalPemasukan[] = $mapPemasukan[$day] ?? 0;
            }

            $filterLabel = $anchor->copy()->locale('id')->translatedFormat('F Y');
            $shortLabel = $anchor->format('m/Y');
            $xTitle = 'Tanggal (Bulan Dipilih)';
        } else {
            $start = $anchor->copy()->startOfYear();
            $end = $anchor->copy()->endOfYear();

            $rows = $this->penjualanObatJoinDetail()
                ->selectRaw('MONTH(po.tanggal_transaksi) as idx')
                ->selectRaw('COUNT(DISTINCT po.kode_transaksi) as jumlah_transaksi')
                ->selectRaw("COALESCE(SUM(CASE WHEN po.status = 'Sudah Bayar' THEN pod.sub_total ELSE 0 END), 0) as total_pemasukan")
                ->whereBetween(DB::raw('DATE(po.tanggal_transaksi)'), [$start->toDateString(), $end->toDateString()])
                ->groupBy('idx')
                ->orderBy('idx')
                ->get();

            foreach ($rows as $row) {
                $mapTransaksi[(int) $row->idx] = (int) $row->jumlah_transaksi;
                $mapPemasukan[(int) $row->idx] = (float) $row->total_pemasukan;
            }

            $bulanIndo = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            for ($month = 1; $month <= 12; $month++) {
                $labels[] = $bulanIndo[$month - 1];
                $seriesJumlahTransaksi[] = $mapTransaksi[$month] ?? 0;
                $seriesTotalPemasukan[] = $mapPemasukan[$month] ?? 0;
            }

            $filterLabel = 'Tahun ' . $anchor->year;
            $shortLabel = (string) $anchor->year;
            $xTitle = 'Bulan (Tahun Dipilih)';
        }

        $datasets = [
            [
                'label'   => 'Jumlah Transaksi',
                'data'    => $seriesJumlahTransaksi,
                'yAxisID' => 'y',
            ],
            [
                'label'   => 'Total Pemasukan (Rp)',
                'data'    => $seriesTotalPemasukan,
                'yAxisID' => 'y1',
            ],
        ];

        return response()->json([
            'periode'      => $periode,
            'mode_label'   => $modeLabel,
            'filter_label' => $filterLabel,
            'short_label'  => $shortLabel,
            'chart_title'  => 'Grafik Penjualan Obat ' . $modeLabel . ' — ' . $filterLabel,
            'labels'       => $labels,
            'values'       => $seriesJumlahTransaksi,
            'datasets'     => $datasets,
            'meta'         => [
                'x_title' => $xTitle,
            ],

            'label'   => $labels,
            'dataset' => $datasets,
        ]);
    }
}
