<?php

namespace App\Http\Controllers\Kasir;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\DiskonApproval;
use App\Models\Kasir;
use App\Models\Kunjungan;
use App\Models\MetodePembayaran;
use App\Models\OrderLayanan;
use App\Models\Pembayaran;
use App\Models\PenjualanObat;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Laravel\Facades\Image;
use Yajra\DataTables\DataTables;

class KasirController extends Controller
{
    private function getNamaKasir(): string
    {
        $user = Auth::user();

        return $user->kasir->nama_kasir ?? 'Kasir';
    }

    public function dashboard()
    {
        $namaKasir = $this->getNamaKasir();
        $serverStatus = 'Online';
        $hariIni = now()->toDateString();

        $summary = $this->getDashboardSummary($hariIni);

        $chartFilter = 'bulanan';
        $chartData = $this->buildTransaksiChart($chartFilter);

        // dd($summary);

        return view('kasir.dashboard', compact(
            'namaKasir',
            'serverStatus',
            'summary',
            'chartFilter',
            'chartData'
        ));
    }

    public function chartTransaksi(Request $request)
    {
        $filter = $this->normalizeChartFilter($request->get('filter'));

        return response()->json($this->buildTransaksiChart($filter));
    }

    private function normalizeChartFilter(?string $filter): string
    {
        $allowed = ['harian', 'mingguan', 'bulanan', 'tahunan'];

        return in_array($filter, $allowed, true) ? $filter : 'bulanan';
    }

    private function getDashboardSummary(string $hariIni): array
    {
        // =========================
        // 1. PEMBAYARAN (alur default)
        // =========================
        $pembayaranHariIni = DB::table('pembayaran')
            ->whereDate('tanggal_pembayaran', $hariIni)
            ->count();

        $pembayaranTotal = DB::table('pembayaran')->count();

        $pembayaranBerhasilHariIni = DB::table('pembayaran')
            ->where('status', 'Sudah Bayar')
            ->whereDate('tanggal_pembayaran', $hariIni)
            ->sum('total_setelah_diskon');

        $pembayaranBerhasilTotal = DB::table('pembayaran')
            ->where('status', 'Sudah Bayar')
            ->sum('total_setelah_diskon');

        // =========================
        // 2. PENJUALAN OBAT
        // =========================
        $obatHariIni = DB::table('penjualan_obat')
            ->whereDate('tanggal_transaksi', $hariIni)
            ->count();

        $obatTotal = DB::table('penjualan_obat')->count();

        $obatBerhasilHariIni = DB::table('penjualan_obat')
            ->where('status', 'Sudah Bayar')
            ->whereDate('tanggal_transaksi', $hariIni)
            ->sum('total_setelah_diskon');

        $obatBerhasilTotal = DB::table('penjualan_obat')
            ->where('status', 'Sudah Bayar')
            ->sum('total_setelah_diskon');

        // =========================
        // 3. ORDER LAYANAN NON-PEMERIKSAAN
        // =========================
        $dataOrderLayanan = OrderLayanan::with([
            'orderLayananDetail',
            'orderLayananDetail.layanan',
            'orderLayananDetail.layanan.kategoriLayanan'
        ])->whereHas('orderLayananDetail.layanan.kategoriLayanan', function ($query) {
            $query->where('nama_kategori', 'NOT LIKE', '%pemeriksaan%');
        });

        $orderanHariIni = $dataOrderLayanan->whereDate('tanggal_order', $hariIni)->count();

        $totalSeluruhOrderLayanan = $dataOrderLayanan->count('kode_transaksi');

        $pemasukanOrderanLayananHariIni = $dataOrderLayanan->where('status_order_layanan', 'Sudah Bayar')->whereDate('tanggal_order', $hariIni)->sum('total_bayar');

        $totalPemasukanOrderLayanan = $dataOrderLayanan->where('status_order_layanan', 'Sudah Bayar')->sum('total_bayar');

        // =========================
        // TOTAL
        // =========================
        $totalTransaksiHariIni = $pembayaranHariIni + $obatHariIni + $orderanHariIni;
        $totalTransaksi = $pembayaranTotal + $obatTotal + $totalSeluruhOrderLayanan;

        $pendapatanHariIni = $pembayaranBerhasilHariIni + $obatBerhasilHariIni + $pemasukanOrderanLayananHariIni;
        $pendapatanTotal = $pembayaranBerhasilTotal + $obatBerhasilTotal + $totalPemasukanOrderLayanan;

        return [
            'total_transaksi_hari_ini' => $totalTransaksiHariIni,
            'total_transaksi' => $totalTransaksi,
            'pendapatan_hari_ini' => $pendapatanHariIni,
            'pendapatan_total' => $pendapatanTotal,

            'pembayaran_hari_ini' => $pembayaranHariIni,
            'pembayaran_total' => $pembayaranTotal,
            'pembayaran_pendapatan_hari_ini' => $pembayaranBerhasilHariIni,
            'pembayaran_pendapatan_total' => $pembayaranBerhasilTotal,

            'obat_hari_ini' => $obatHariIni,
            'obat_total' => $obatTotal,
            'obat_pendapatan_hari_ini' => $obatBerhasilHariIni,
            'obat_pendapatan_total' => $obatBerhasilTotal,

            'layanan_hari_ini' => $orderanHariIni,
            'layanan_total' => $totalSeluruhOrderLayanan,
            'layanan_pendapatan_hari_ini' => $pemasukanOrderanLayananHariIni,
            'layanan_pendapatan_total' => $totalPemasukanOrderLayanan,
        ];
    }

    private function getChartFilterLabel(string $filter): string
    {
        return match ($filter) {
            'harian' => 'Harian',
            'mingguan' => 'Mingguan',
            'tahunan' => 'Tahunan',
            default => 'Bulanan',
        };
    }

    private function buildTransaksiChart(string $filter): array
    {
        Carbon::setLocale('id');
        $now = now();

        switch ($filter) {
            case 'harian':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                $bucketType = 'day';
                break;

            case 'mingguan':
                $start = $now->copy()->subWeeks(11)->startOfWeek(Carbon::MONDAY);
                $end = $now->copy()->endOfWeek(Carbon::SUNDAY);
                $bucketType = 'week';
                break;

            case 'tahunan':
                $start = $now->copy()->subYears(4)->startOfYear();
                $end = $now->copy()->endOfYear();
                $bucketType = 'year';
                break;

            case 'bulanan':
            default:
                $start = $now->copy()->startOfYear();
                $end = $now->copy()->endOfYear();
                $bucketType = 'month';
                break;
        }

        $labels = [];
        $keys = [];
        $cursor = $start->copy();

        if ($bucketType === 'day') {
            while ($cursor->lte($end)) {
                $keys[] = $cursor->format('Y-m-d');
                $labels[] = $cursor->translatedFormat('d M');
                $cursor->addDay();
            }
        } elseif ($bucketType === 'week') {
            while ($cursor->lte($end)) {
                $weekStart = $cursor->copy()->startOfWeek(Carbon::MONDAY);
                $weekEnd = $cursor->copy()->endOfWeek(Carbon::SUNDAY);

                $keys[] = $weekStart->format('Y-m-d');
                $labels[] = $weekStart->translatedFormat('d M') . ' - ' . $weekEnd->translatedFormat('d M');

                $cursor->addWeek();
            }
        } elseif ($bucketType === 'month') {
            while ($cursor->lte($end)) {
                $keys[] = $cursor->format('Y-m');
                $labels[] = $cursor->translatedFormat('M Y');
                $cursor->addMonth();
            }
        } else {
            while ($cursor->lte($end)) {
                $keys[] = $cursor->format('Y');
                $labels[] = $cursor->format('Y');
                $cursor->addYear();
            }
        }

        $pembayaranMap = array_fill_keys($keys, 0);
        $obatMap = array_fill_keys($keys, 0);
        $layananMap = array_fill_keys($keys, 0);
        $totalMap = array_fill_keys($keys, 0);

        // =========================
        // PEMBAYARAN
        // =========================
        $pembayaranRows = DB::table('pembayaran')
            ->select('tanggal_pembayaran')
            ->whereNotNull('tanggal_pembayaran')
            ->whereDate('tanggal_pembayaran', '>=', $start->toDateString())
            ->whereDate('tanggal_pembayaran', '<=', $end->toDateString())
            ->get();

        foreach ($pembayaranRows as $row) {
            $tanggal = Carbon::parse($row->tanggal_pembayaran);
            $key = $this->resolveBucketKey($tanggal, $bucketType);

            if (array_key_exists($key, $pembayaranMap)) {
                $pembayaranMap[$key]++;
                $totalMap[$key]++;
            }
        }

        // =========================
        // PENJUALAN OBAT
        // =========================
        $obatRows = DB::table('penjualan_obat')
            ->select('tanggal_transaksi')
            ->whereNotNull('tanggal_transaksi')
            ->whereDate('tanggal_transaksi', '>=', $start->toDateString())
            ->whereDate('tanggal_transaksi', '<=', $end->toDateString())
            ->get();

        foreach ($obatRows as $row) {
            $tanggal = Carbon::parse($row->tanggal_transaksi);
            $key = $this->resolveBucketKey($tanggal, $bucketType);

            if (array_key_exists($key, $obatMap)) {
                $obatMap[$key]++;
                $totalMap[$key]++;
            }
        }

        // =========================
        // ORDER LAYANAN NON-PEMERIKSAAN
        // =========================
        $layananRows = DB::table('order_layanan as ol')
            ->join('order_layanan_detail as old', 'old.order_layanan_id', '=', 'ol.id')
            ->join('layanan as l', 'l.id', '=', 'old.layanan_id')
            ->join('kategori_layanan as kl', 'kl.id', '=', 'l.kategori_layanan_id')
            ->whereRaw('LOWER(kl.nama_kategori) NOT LIKE ?', ['%pemeriksaan%'])
            ->whereNotNull('ol.tanggal_order')
            ->whereDate('ol.tanggal_order', '>=', $start->toDateString())
            ->whereDate('ol.tanggal_order', '<=', $end->toDateString())
            ->select('ol.id', 'ol.tanggal_order')
            ->distinct()
            ->get();

        foreach ($layananRows as $row) {
            $tanggal = Carbon::parse($row->tanggal_order);
            $key = $this->resolveBucketKey($tanggal, $bucketType);

            if (array_key_exists($key, $layananMap)) {
                $layananMap[$key]++;
                $totalMap[$key]++;
            }
        }

        return [
            'filter' => $filter,
            'filter_label' => $this->getChartFilterLabel($filter),
            'labels' => $labels,
            'range_text' => $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y'),

            'pembayaran' => array_values($pembayaranMap),
            'obat' => array_values($obatMap),
            'layanan' => array_values($layananMap),
            'total_transaksi' => array_values($totalMap),

            'summary_total' => array_sum($totalMap),
            'summary_pembayaran' => array_sum($pembayaranMap),
            'summary_obat' => array_sum($obatMap),
            'summary_layanan' => array_sum($layananMap),
        ];
    }

    private function resolveBucketKey(Carbon $tanggal, string $bucketType): string
    {
        if ($bucketType === 'day') {
            return $tanggal->format('Y-m-d');
        }

        if ($bucketType === 'week') {
            return $tanggal->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        }

        if ($bucketType === 'month') {
            return $tanggal->format('Y-m');
        }

        return $tanggal->format('Y');
    }

    public function index()
    {
        return view('kasir.pembayaran.kasir');
    }

    public function getDataOrderLayanan(Request $request)
    {
        $dataOrderLayanan = OrderLayanan::with([
            'pasien',
            'metodePembayaran',
            'orderLayananDetail.layanan',
            'orderLayananDetail.layanan.kategoriLayanan',
        ])
            ->whereHas('orderLayananDetail.layanan.kategoriLayanan', function ($query) {
                $query->whereRaw('LOWER(nama_kategori) NOT LIKE ?', ['%pemeriksaan%']);
            })
            ->latest('tanggal_order');

        return DataTables::eloquent($dataOrderLayanan)
            ->addIndexColumn()
            ->addColumn('total_tagihan', function ($row) {
                $nominal = (float) ($row->total_bayar ?? $row->total_tagihan ?? 0);

                return $nominal > 0
                    ? 'Rp ' . number_format($nominal, 0, ',', '.')
                    : '-';
            })
            ->addColumn('tanggal_transaksi', function ($row) {
                if (!$row->tanggal_order) {
                    return '-';
                }

                return Carbon::parse($row->tanggal_order)
                    ->locale('id')
                    ->translatedFormat('d F Y H:i');
            })
            ->addColumn('bukti_pembayaran', function ($row) {
                if (!$row->bukti_pembayaran) {
                    return '-';
                }

                $url = asset('storage/' . $row->bukti_pembayaran);

                return '<a href="' . $url . '" target="_blank" class="text-sky-600 underline">Lihat</a>';
            })
            ->addColumn('action', function ($row) {
                $url = route('kasir.show.kwitansi.order.layanan', [
                    'kodeTransaksi' => $row->kode_transaksi,
                ]);

                return '
                <button class="btn-bayar-layanan text-blue-600 hover:text-blue-800"
                        data-url="' . $url . '">
                    <i class="fa-solid fa-money-bill-wave"></i> Detail / Bayar
                </button>
            ';
            })
            ->rawColumns(['bukti_pembayaran', 'action'])
            ->make(true);
    }

    public function chartKeuangan(Request $request)
    {
        $range = $request->get('range', 'harian');
        $today = Carbon::today();

        switch ($range) {
            case 'harian':
                $start = $today->copy();
                $end = $today->copy();

                $labels = collect(range(0, 23))->map(fn($h) => str_pad($h, 2, '0', STR_PAD_LEFT) . ':00');

                $subPenjualan = DB::table('penjualan_obat')
                    ->selectRaw('kode_transaksi, HOUR(tanggal_transaksi) as h, MAX(COALESCE(sub_total,0)) as total_per_transaksi')
                    ->whereDate('tanggal_transaksi', $today)
                    ->groupBy('kode_transaksi', 'h');

                $penjualan = DB::query()
                    ->fromSub($subPenjualan, 't')
                    ->selectRaw('h, SUM(total_per_transaksi) as total')
                    ->groupBy('h')
                    ->pluck('total', 'h');

                $penjualanTrx = DB::table('penjualan_obat')
                    ->selectRaw('HOUR(tanggal_transaksi) as h, COUNT(DISTINCT kode_transaksi) as trx')
                    ->whereDate('tanggal_transaksi', $today)
                    ->groupBy('h')
                    ->pluck('trx', 'h');

                $pembayaran = DB::table('pembayaran')
                    ->selectRaw('HOUR(tanggal_pembayaran) as h, SUM(COALESCE(total_tagihan,0)) as total')
                    ->whereDate('tanggal_pembayaran', $today)
                    ->groupBy('h')
                    ->pluck('total', 'h');

                $totalPenjualan = $labels->map(fn($lbl) => $penjualan[(int) substr($lbl, 0, 2)] ?? 0);
                $totalPembayaran = $labels->map(fn($lbl) => $pembayaran[(int) substr($lbl, 0, 2)] ?? 0);
                $jumlahTransaksi = $labels->map(fn($lbl) => $penjualanTrx[(int) substr($lbl, 0, 2)] ?? 0);

                $meta = [
                    'x_title' => 'Jam',
                    'tanggal' => $today->toDateString(),
                    'start' => $start->toDateString(),
                    'end' => $end->toDateString(),
                ];
                break;

            case 'mingguan':
                $start = $today->copy()->startOfWeek(Carbon::MONDAY);
                $end = $today->copy()->endOfWeek(Carbon::SUNDAY);

                $period = CarbonPeriod::create($start, $end);
                $labelsRaw = collect($period)->map(fn($d) => $d->format('Y-m-d'));

                $subPenjualan = DB::table('penjualan_obat')
                    ->selectRaw('kode_transaksi, DATE(tanggal_transaksi) as d, MAX(COALESCE(sub_total,0)) as total_per_transaksi')
                    ->whereBetween('tanggal_transaksi', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                    ->groupBy('kode_transaksi', 'd');

                $penjualan = DB::query()
                    ->fromSub($subPenjualan, 't')
                    ->selectRaw('d, SUM(total_per_transaksi) as total')
                    ->groupBy('d')
                    ->pluck('total', 'd');

                $penjualanTrx = DB::table('penjualan_obat')
                    ->selectRaw('DATE(tanggal_transaksi) as d, COUNT(DISTINCT kode_transaksi) as trx')
                    ->whereBetween('tanggal_transaksi', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                    ->groupBy('d')
                    ->pluck('trx', 'd');

                $pembayaran = DB::table('pembayaran')
                    ->selectRaw('DATE(tanggal_pembayaran) as d, SUM(COALESCE(total_tagihan,0)) as total')
                    ->whereBetween('tanggal_pembayaran', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                    ->groupBy('d')
                    ->pluck('total', 'd');

                $totalPenjualan = $labelsRaw->map(fn($d) => $penjualan[$d] ?? 0);
                $totalPembayaran = $labelsRaw->map(fn($d) => $pembayaran[$d] ?? 0);
                $jumlahTransaksi = $labelsRaw->map(fn($d) => $penjualanTrx[$d] ?? 0);

                $labels = $labelsRaw->map(fn($d) => Carbon::parse($d)->locale('id')->translatedFormat('d M'));

                $meta = [
                    'x_title' => 'Tanggal (Mingguan)',
                    'start' => $start->toDateString(),
                    'end' => $end->toDateString(),
                ];
                break;

            case 'bulanan':
                $start = $today->copy()->startOfMonth();
                $end = $today->copy()->endOfMonth();

                $period = CarbonPeriod::create($start, $end);
                $labelsRaw = collect($period)->map(fn($d) => $d->format('Y-m-d'));

                $subPenjualan = DB::table('penjualan_obat')
                    ->selectRaw('kode_transaksi, DATE(tanggal_transaksi) as d, MAX(COALESCE(sub_total,0)) as total_per_transaksi')
                    ->whereBetween('tanggal_transaksi', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                    ->groupBy('kode_transaksi', 'd');

                $penjualan = DB::query()
                    ->fromSub($subPenjualan, 't')
                    ->selectRaw('d, SUM(total_per_transaksi) as total')
                    ->groupBy('d')
                    ->pluck('total', 'd');

                $penjualanTrx = DB::table('penjualan_obat')
                    ->selectRaw('DATE(tanggal_transaksi) as d, COUNT(DISTINCT kode_transaksi) as trx')
                    ->whereBetween('tanggal_transaksi', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                    ->groupBy('d')
                    ->pluck('trx', 'd');

                $pembayaran = DB::table('pembayaran')
                    ->selectRaw('DATE(tanggal_pembayaran) as d, SUM(COALESCE(total_tagihan,0)) as total')
                    ->whereBetween('tanggal_pembayaran', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                    ->groupBy('d')
                    ->pluck('total', 'd');

                $totalPenjualan = $labelsRaw->map(fn($d) => $penjualan[$d] ?? 0);
                $totalPembayaran = $labelsRaw->map(fn($d) => $pembayaran[$d] ?? 0);
                $jumlahTransaksi = $labelsRaw->map(fn($d) => $penjualanTrx[$d] ?? 0);

                $labels = $labelsRaw->map(fn($d) => Carbon::parse($d)->format('d'));

                $meta = [
                    'x_title' => 'Tanggal (Bulanan)',
                    'bulan' => $start->format('Y-m'),
                ];
                break;

            case 'tahunan':
            default:
                $start = $today->copy()->startOfYear();
                $end = $today->copy()->endOfYear();

                $labelsYm = collect(range(1, 12))->map(fn($m) => $start->copy()->month($m)->format('Y-m'));

                $subPenjualan = DB::table('penjualan_obat')
                    ->selectRaw("kode_transaksi, DATE_FORMAT(tanggal_transaksi, '%Y-%m') as ym, MAX(COALESCE(sub_total,0)) as total_per_transaksi")
                    ->whereBetween('tanggal_transaksi', [$start, $end])
                    ->groupBy('kode_transaksi', 'ym');

                $penjualan = DB::query()
                    ->fromSub($subPenjualan, 't')
                    ->selectRaw('ym, SUM(total_per_transaksi) as total')
                    ->groupBy('ym')
                    ->pluck('total', 'ym');

                $penjualanTrx = DB::table('penjualan_obat')
                    ->selectRaw("DATE_FORMAT(tanggal_transaksi, '%Y-%m') as ym, COUNT(DISTINCT kode_transaksi) as trx")
                    ->whereBetween('tanggal_transaksi', [$start, $end])
                    ->groupBy('ym')
                    ->pluck('trx', 'ym');

                $pembayaran = DB::table('pembayaran')
                    ->selectRaw("DATE_FORMAT(tanggal_pembayaran, '%Y-%m') as ym, SUM(COALESCE(total_tagihan,0)) as total")
                    ->whereBetween('tanggal_pembayaran', [$start, $end])
                    ->groupBy('ym')
                    ->pluck('total', 'ym');

                $totalPenjualan = $labelsYm->map(fn($ym) => $penjualan[$ym] ?? 0);
                $totalPembayaran = $labelsYm->map(fn($ym) => $pembayaran[$ym] ?? 0);
                $jumlahTransaksi = $labelsYm->map(fn($ym) => $penjualanTrx[$ym] ?? 0);

                $labels = $labelsYm->map(fn($ym) => Carbon::createFromFormat('Y-m', $ym)->locale('id')->translatedFormat('M'));

                $meta = [
                    'x_title' => 'Bulan',
                    'tahun' => $start->format('Y'),
                ];
                break;
        }

        return response()->json([
            'label' => $labels,
            'dataset' => [
                [
                    'type' => 'bar',
                    'label' => 'Jumlah Transaksi',
                    'data' => $jumlahTransaksi,
                    'yAxisID' => 'y',
                ],
                [
                    'type' => 'line',
                    'label' => 'Total Penjualan Obat (Rp)',
                    'data' => $totalPenjualan,
                    'yAxisID' => 'y1',
                    'tension' => 0.3,
                ],
                [
                    'type' => 'line',
                    'label' => 'Total Tagihan Pembayaran (Rp)',
                    'data' => $totalPembayaran,
                    'yAxisID' => 'y1',
                    'tension' => 0.3,
                ],
            ],
            'meta' => $meta,
        ]);
    }

    public function getDataPembayaran()
    {
        $dataPembayaran = Pembayaran::with([
            'emr.kunjungan.pasien',
            'pembayaranDetail', // relasi ke pembayaran_detail
            'metodePembayaran',
        ])
            ->where('status', 'Belum Bayar')
            ->latest()
            ->get();

        $user = Auth::user();
        $isSuperAdmin = $user && strtolower(str_replace(' ', '', $user->role)) === 'superadmin';

        return DataTables::of($dataPembayaran)
            ->addIndexColumn()

            ->addColumn(
                'nama_pasien',
                fn($p) =>
                optional($p->emr->kunjungan->pasien)->nama_pasien ?? '-'
            )

            ->addColumn(
                'tanggal_kunjungan',
                fn($p) =>
                optional($p->emr->kunjungan)->tanggal_kunjungan ?? '-'
            )

            ->addColumn(
                'no_antrian',
                fn($p) =>
                optional($p->emr->kunjungan)->no_antrian ?? '-'
            )

            ->addColumn(
                'total_tagihan',
                fn($p) =>
                'Rp ' . number_format($p->total_tagihan ?? 0, 0, ',', '.')
            )

            ->addColumn(
                'metode_pembayaran',
                fn($p) =>
                optional($p->metodePembayaran)->nama_metode ?? '-'
            )

            ->addColumn(
                'kode_transaksi',
                fn($p) =>
                $p->kode_transaksi ?? '-'
            )

            ->addColumn('action', function ($p) use ($isSuperAdmin) {

                $urlBayar = route('kasir.transaksi', [
                    'kode_transaksi' => $p->kode_transaksi
                ]);

                $urlDelete = route('kasir.pembayaran.delete', [
                    'id' => $p->id
                ]);

                $buttons = '
                <div class="flex items-center justify-center gap-3">
                    <button class="bayarSekarang text-blue-600 hover:text-blue-800"
                            data-url="' . $urlBayar . '"
                            data-id="' . $p->id . '"
                            title="Bayar Sekarang">
                        <i class="fa-regular fa-pen-to-square"></i> Bayar
                    </button>
                </div>
                ';

                if ($isSuperAdmin) {
                    $buttons = '
                    <div class="flex items-center justify-center gap-3">
                    <button class="bayarSekarang text-blue-600 hover:text-blue-800"
                            data-url="' . $urlBayar . '"
                            data-id="' . $p->id . '"
                            title="Bayar Sekarang">
                        <i class="fa-regular fa-pen-to-square"></i> Bayar
                    </button>

                          <button class="hapusTransaksi text-rose-600 hover:text-rose-800"
                                data-url="' . $urlDelete . '"
                                data-kode="' . e($p->kode_transaksi) . '"
                                title="Hapus Transaksi">
                                <i class="fa-solid fa-trash"></i> Hapus
                            </button>
                </div>

                    
                    ';
                }

                return $buttons;
            })

            ->rawColumns(['items', 'action'])
            ->make(true);
    }

    public function deletePembayaran($id)
    {
        $pembayaran = Pembayaran::with(['emr'])->findOrFail($id);

        // proteksi: kalau sudah bayar, jangan boleh dihapus
        if ($pembayaran->status === 'Sudah Bayar') {
            return response()->json([
                'message' => 'Transaksi sudah dibayar, tidak bisa dihapus.',
            ], 422);
        }

        DB::transaction(function () use ($pembayaran) {
            // kalau mau: hapus bukti transfer kalau ada
            if ($pembayaran->bukti_pembayaran && Storage::disk('public')->exists($pembayaran->bukti_pembayaran)) {
                Storage::disk('public')->delete($pembayaran->bukti_pembayaran);
            }

            // HATI-HATI:
            // Umumnya "Transaksi menunggu" cuma pembayaran pending.
            // Biasanya cukup delete pembayaran saja.
            // Jangan delete EMR/Kunjungan/Resep kecuali kamu yakin relasinya harus ikut hilang.
            $pembayaran->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dihapus.',
        ]);
    }

    public function getDataRiwayatPembayaran()
    {
        $dataPembayaran = Pembayaran::with([
            'emr.kunjungan.pasien',
            'pembayaranDetail', // relasi ke pembayaran_detail
            'metodePembayaran',
        ])
            ->where('status', 'Sudah Bayar')
            ->latest()
            ->get();

        return DataTables::of($dataPembayaran)
            ->addIndexColumn()

            ->addColumn(
                'nama_pasien',
                fn($p) =>
                optional($p->emr->kunjungan->pasien)->nama_pasien ?? '-'
            )

            ->addColumn(
                'tanggal_kunjungan',
                fn($p) =>
                optional($p->emr->kunjungan)->tanggal_kunjungan ?? '-'
            )

            ->addColumn(
                'no_antrian',
                fn($p) =>
                optional($p->emr->kunjungan)->no_antrian ?? '-'
            )

            /**
             * ✅ TOTAL + METODE
             */
            ->addColumn(
                'total_setelah_diskon',
                fn($p) =>
                'Rp ' . number_format($p->total_setelah_diskon ?? 0, 0, ',', '.')
            )

            ->addColumn(
                'metode_pembayaran',
                fn($p) =>
                optional($p->metodePembayaran)->nama_metode ?? '-'
            )

            /**
             * ✅ KODE TRANSAKSI
             */
            ->addColumn(
                'kode_transaksi',
                fn($p) =>
                $p->kode_transaksi ?? '-'
            )

            /**
             * ✅ STATUS
             */
            ->addColumn(
                'status',
                fn($p) =>
                $p->status ?? '-'
            )

            /**
             * ✅ BUKTI PEMBAYARAN (tetap tampil)
             */
            ->addColumn('bukti_pembayaran', function ($p) {
                if (!$p->bukti_pembayaran) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }

                $url = asset('storage/' . $p->bukti_pembayaran);
                $urlEsc = e($url);

                return <<<HTML
<div class="flex flex-col items-center text-center space-y-2">
    <img src="{$urlEsc}" alt="Bukti Pembayaran"
         class="w-24 h-24 object-cover rounded-lg border border-gray-300 shadow-sm hover:scale-105 transition-transform duration-200 cursor-pointer"
         onclick="window.open('{$urlEsc}', '_blank')" />
    <a href="{$urlEsc}" target="_blank" class="text-sky-600 underline text-sm font-medium">
        Lihat Bukti Pembayaran
    </a>
</div>
HTML;
            })

            ->addColumn('action', function ($p) {
                $url = route('show.kwitansi', ['kodeTransaksi' => $p->kode_transaksi]);
                $urlEsc = e($url);

                return '<button class="cetakKuitansi text-blue-600 hover:text-blue-800" data-url="' . $urlEsc . '" title="Cetak Kwitansi"><i class="fa-solid fa-print"></i> Cetak Kwitansi</button>';
            })

            ->rawColumns([
                'nama_obat',
                'dosis',
                'jumlah',
                'nama_layanan',
                'jumlah_layanan',
                'bukti_pembayaran',
                'action',
            ])
            ->make(true);
    }

    public function transaksi($kodeTransaksi)
    {
        $dataPembayaran = Pembayaran::with([
            'emr.kunjungan.pasien',
            'emr.kunjungan.poli',
            'pembayaranDetail',
            'metodePembayaran',
        ])
            ->where('kode_transaksi', $kodeTransaksi)
            ->firstOrFail();

        $details = $dataPembayaran->pembayaranDetail ?? collect();

        $latestApproval = DiskonApproval::where('pembayaran_id', $dataPembayaran->id)
            ->latest('id')
            ->first();

        $approvalStatus = $latestApproval?->status;

        $approvalItemsRaw = $latestApproval?->diskon_items ?? [];
        if (is_string($approvalItemsRaw)) {
            $decoded = json_decode($approvalItemsRaw, true);
            $approvalItemsRaw = is_array($decoded) ? $decoded : [];
        }

        $approvalItemsById = collect($approvalItemsRaw)
            ->map(function ($item) {
                return [
                    'id' => (int) ($item['id'] ?? 0),
                    'persen' => (float) ($item['persen'] ?? 0),
                ];
            })
            ->filter(fn($item) => $item['id'] > 0)
            ->keyBy('id');

        $diskonLocked = in_array($approvalStatus, ['pending', 'approved'], true);

        $details = $details->map(function ($detail) use ($approvalStatus, $approvalItemsById) {
            $diskonDariDetail = (float) ($detail->diskon_nilai ?? 0);

            if (in_array($approvalStatus, ['pending', 'approved'], true)) {
                $diskonInput = (float) data_get($approvalItemsById->get($detail->id), 'persen', $diskonDariDetail);
            } elseif ($approvalStatus === 'rejected') {
                $diskonInput = 0;
            } else {
                $diskonInput = $diskonDariDetail;
            }

            $detail->diskon_input = $diskonInput;

            return $detail;
        });

        $itemsObat = $details->filter(fn($d) => str_starts_with((string)$d->nama_item, 'Obat:'))->values();
        $itemsLayanan = $details->filter(fn($d) => str_starts_with((string)$d->nama_item, 'Layanan:'))->values();
        $itemsLab = $details->filter(fn($d) => str_starts_with((string)$d->nama_item, 'Lab:'))->values();
        $itemsRadiologi = $details->filter(fn($d) => str_starts_with((string)$d->nama_item, 'Radiologi:'))->values();

        $totalAwal = (float) $details->sum('subtotal');
        $dataMetodePembayaran = MetodePembayaran::all();

        return view('kasir.pembayaran.transaksi', compact(
            'dataPembayaran',
            'dataMetodePembayaran',
            'itemsObat',
            'itemsLayanan',
            'itemsLab',
            'itemsRadiologi',
            'totalAwal',
            'approvalStatus',
            'approvalItemsRaw',
            'diskonLocked'
        ));
    }

    public function transaksiCash(Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:pembayaran,id'],
            'uang_yang_diterima' => ['required', 'numeric', 'min:0'],
            'kembalian' => ['nullable', 'numeric'],
            'metode_pembayaran_id' => ['required', 'exists:metode_pembayaran,id'],
            'diskon_items' => ['required', 'string'],
        ]);

        $pembayaranId = (int) $request->id;
        $pembayaran = null;

        try {
            Log::info('=== TRANSAKSI CASH START ===', [
                'pembayaran_id' => $pembayaranId,
                'timestamp' => now()->toDateTimeString(),
            ]);

            DB::transaction(function () use ($request, $pembayaranId, &$pembayaran) {

                $pemb = Pembayaran::with([
                    'emr.kunjungan.pasien.user'
                ])
                    ->lockForUpdate()
                    ->findOrFail($pembayaranId);

                if (($pemb->status ?? '') !== 'Belum Bayar') {
                    throw ValidationException::withMessages([
                        'id' => 'Transaksi ini sudah diproses / status bukan Belum Bayar.',
                    ]);
                }

                $normalizedDiskonItems = $this->validateApprovalBeforePayment(
                    $pembayaranId,
                    (string) $request->diskon_items
                );

                $mapPersen = [];
                foreach ($normalizedDiskonItems as $it) {
                    $mapPersen[(int) $it['id']] = (float) $it['persen'];
                }

                $details = DB::table('pembayaran_detail')
                    ->where('pembayaran_id', $pembayaranId)
                    ->select('id', 'subtotal')
                    ->get();

                if ($details->isEmpty()) {
                    throw ValidationException::withMessages([
                        'id' => 'Detail pembayaran kosong. Pastikan pembayaran_detail sudah terbentuk.',
                    ]);
                }

                $totalAwal = 0.0;
                $totalSetelahDiskon = 0.0;
                $potonganTotal = 0.0;

                foreach ($details as $d) {
                    $subtotal = (float) ($d->subtotal ?? 0);
                    $totalAwal += $subtotal;

                    $persen = (float) ($mapPersen[(int) $d->id] ?? 0.0);
                    if ($persen < 0) $persen = 0;
                    if ($persen > 100) $persen = 100;

                    $potongan = $subtotal * ($persen / 100);
                    if ($potongan > $subtotal) $potongan = $subtotal;

                    $after = $subtotal - $potongan;

                    $potonganTotal += $potongan;
                    $totalSetelahDiskon += $after;

                    DB::table('pembayaran_detail')
                        ->where('id', $d->id)
                        ->update([
                            'total_tagihan'        => $subtotal,
                            'diskon_tipe'          => $persen > 0 ? 'persen' : null,
                            'diskon_nilai'         => $persen,
                            'total_setelah_diskon' => $after,
                            'updated_at'           => now(),
                        ]);
                }

                if ($totalAwal <= 0) {
                    throw ValidationException::withMessages([
                        'id' => 'Total tagihan tidak valid.',
                    ]);
                }

                $uangDiterima = (float) $request->uang_yang_diterima;
                if ($uangDiterima < $totalSetelahDiskon) {
                    throw ValidationException::withMessages([
                        'uang_yang_diterima' => 'Nominal uang yang diterima belum cukup.',
                    ]);
                }

                $kembalian = $uangDiterima - $totalSetelahDiskon;
                $diskonPersenGlobal = $totalAwal > 0 ? ($potonganTotal / $totalAwal) * 100 : 0;

                // Update pembayaran
                $pemb->update([
                    'total_tagihan'        => $totalAwal,
                    'diskon_tipe'          => $potonganTotal > 0 ? 'persen' : null,
                    'diskon_nilai'         => $potonganTotal > 0 ? $diskonPersenGlobal : 0,
                    'total_setelah_diskon' => $totalSetelahDiskon,
                    'uang_yang_diterima'   => $uangDiterima,
                    'kembalian'            => $kembalian,
                    'tanggal_pembayaran'   => now(),
                    'status'               => 'Sudah Bayar',
                    'metode_pembayaran_id' => $request->metode_pembayaran_id,
                ]);

                // Update status kunjungan: Payment -> Succeed
                $kunjunganId = optional($pemb->emr)->kunjungan_id;

                if (!$kunjunganId) {
                    throw ValidationException::withMessages([
                        'id' => 'Kunjungan tidak ditemukan dari relasi pembayaran -> emr.',
                    ]);
                }

                $kunjungan = Kunjungan::lockForUpdate()->find($kunjunganId);

                if (!$kunjungan) {
                    throw ValidationException::withMessages([
                        'id' => 'Data kunjungan tidak ditemukan.',
                    ]);
                }

                $kunjungan->update([
                    'status' => 'Succeed',
                    'updated_at' => now(),
                ]);

                $pembayaran = $pemb->fresh([
                    'emr.kunjungan.pasien.user',
                    'metodePembayaran',
                ]);

                DB::afterCommit(function () use ($pembayaranId) {
                    try {
                        $pembFresh = Pembayaran::with(['emr.kunjungan.pasien.user'])->find($pembayaranId);
                        if ($pembFresh) {
                            NotificationHelper::kirimNotifikasiPembayaranSelesai($pembFresh);
                        }
                    } catch (\Throwable $e) {
                        Log::error('❌ Error sending notification for pembayaran cash', [
                            'pembayaran_id' => $pembayaranId,
                            'error' => $e->getMessage(),
                        ]);
                    }
                });
            });

            Log::info('=== TRANSAKSI CASH SUCCESS ===', [
                'pembayaran_id' => $pembayaranId,
            ]);

            return response()->json([
                'success' => true,
                'data' => $pembayaran,
                'message' => 'Uang Kembalian Rp' . number_format((float) $pembayaran->kembalian, 0, ',', '.') . '. Terimakasih 😊😊😊',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Validation error in transaksi cash', [
                'errors' => $e->errors(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('=== TRANSAKSI CASH ERROR ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'pembayaran_id' => $pembayaranId ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function transaksiTransfer(Request $request)
    {
        $request->validate([
            'id' => ['required', 'exists:pembayaran,id'],
            'bukti_pembayaran' => ['required', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,jfif', 'max:5120'],
            'metode_pembayaran_id' => ['required', 'exists:metode_pembayaran,id'],
            'diskon_items' => ['required', 'string'],
        ]);

        $pembayaranId = (int) $request->id;
        $pembayaran = null;

        try {
            Log::info('=== TRANSAKSI TRANSFER START ===', [
                'pembayaran_id' => $pembayaranId,
                'timestamp' => now()->toDateTimeString(),
            ]);

            DB::transaction(function () use ($request, $pembayaranId, &$pembayaran) {

                $pemb = Pembayaran::with(['emr.kunjungan.pasien.user'])
                    ->lockForUpdate()
                    ->findOrFail($pembayaranId);

                if (($pemb->status ?? '') !== 'Belum Bayar') {
                    throw ValidationException::withMessages([
                        'id' => 'Transaksi ini sudah diproses / status bukan Belum Bayar.',
                    ]);
                }

                $normalizedDiskonItems = $this->validateApprovalBeforePayment(
                    $pembayaranId,
                    (string) $request->diskon_items
                );

                $mapPersen = [];
                foreach ($normalizedDiskonItems as $it) {
                    $mapPersen[(int) $it['id']] = (float) $it['persen'];
                }

                $details = DB::table('pembayaran_detail')
                    ->where('pembayaran_id', $pembayaranId)
                    ->select('id', 'subtotal')
                    ->get();

                if ($details->isEmpty()) {
                    throw ValidationException::withMessages([
                        'id' => 'Detail pembayaran kosong. Pastikan pembayaran_detail sudah terbentuk.',
                    ]);
                }

                $totalAwal = 0.0;
                $totalSetelahDiskon = 0.0;
                $potonganTotal = 0.0;

                foreach ($details as $d) {
                    $subtotal = (float) ($d->subtotal ?? 0);
                    $totalAwal += $subtotal;

                    $persen = (float) ($mapPersen[(int) $d->id] ?? 0.0);
                    if ($persen < 0) $persen = 0;
                    if ($persen > 100) $persen = 100;

                    $potongan = $subtotal * ($persen / 100);
                    if ($potongan > $subtotal) $potongan = $subtotal;

                    $after = $subtotal - $potongan;

                    $potonganTotal += $potongan;
                    $totalSetelahDiskon += $after;

                    DB::table('pembayaran_detail')
                        ->where('id', $d->id)
                        ->update([
                            'total_tagihan'        => $subtotal,
                            'diskon_tipe'          => $persen > 0 ? 'persen' : null,
                            'diskon_nilai'         => $persen,
                            'total_setelah_diskon' => $after,
                            'updated_at'           => now(),
                        ]);
                }

                if ($totalAwal <= 0) {
                    throw ValidationException::withMessages([
                        'id' => 'Total tagihan tidak valid.',
                    ]);
                }

                if ($totalSetelahDiskon <= 0) {
                    throw ValidationException::withMessages([
                        'total_setelah_diskon' => 'Total setelah diskon tidak valid.',
                    ]);
                }

                $fotoPath = null;
                if ($request->hasFile('bukti_pembayaran')) {
                    $file = $request->file('bukti_pembayaran');
                    $ext = strtolower($file->getClientOriginalExtension());
                    if ($ext === 'jfif') {
                        $ext = 'jpg';
                    }

                    $fileName = time() . '_' . uniqid() . '.' . $ext;
                    $path = 'bukti-transaksi/' . $fileName;

                    if ($ext === 'svg') {
                        Storage::disk('public')->put($path, file_get_contents($file));
                    } else {
                        $image = Image::read($file);
                        $image->scale(width: 800);
                        Storage::disk('public')->put(
                            $path,
                            (string) $image->encodeByExtension($ext, quality: 80)
                        );
                    }

                    $fotoPath = $path;

                    Log::info('Bukti transfer uploaded', [
                        'path' => $fotoPath,
                    ]);
                }

                $diskonPersenGlobal = $totalAwal > 0
                    ? ($potonganTotal / $totalAwal) * 100
                    : 0;

                // Update pembayaran
                $pemb->update([
                    'total_tagihan'        => $totalAwal,
                    'diskon_tipe'          => $potonganTotal > 0 ? 'persen' : null,
                    'diskon_nilai'         => $potonganTotal > 0 ? $diskonPersenGlobal : 0,
                    'total_setelah_diskon' => $totalSetelahDiskon,
                    'bukti_pembayaran'     => $fotoPath,
                    'uang_yang_diterima'   => $totalSetelahDiskon,
                    'kembalian'            => 0,
                    'tanggal_pembayaran'   => now(),
                    'status'               => 'Sudah Bayar',
                    'metode_pembayaran_id' => $request->metode_pembayaran_id,
                ]);

                Log::info('Pembayaran updated to Sudah Bayar (Transfer)', [
                    'pembayaran_id' => $pemb->id,
                    'kode_transaksi' => $pemb->kode_transaksi,
                ]);

                // Update status kunjungan: Payment -> Succeed
                $kunjunganId = optional($pemb->emr)->kunjungan_id;

                if (!$kunjunganId) {
                    throw ValidationException::withMessages([
                        'id' => 'Kunjungan tidak ditemukan dari relasi pembayaran -> emr.',
                    ]);
                }

                $kunjungan = Kunjungan::lockForUpdate()->find($kunjunganId);

                if (!$kunjungan) {
                    throw ValidationException::withMessages([
                        'id' => 'Data kunjungan tidak ditemukan.',
                    ]);
                }

                if ($kunjungan->status !== 'Payment') {
                    throw ValidationException::withMessages([
                        'id' => 'Status kunjungan harus Payment sebelum diubah menjadi Succeed.',
                    ]);
                }

                $kunjungan->update([
                    'status' => 'Succeed',
                    'updated_at' => now(),
                ]);

                Log::info('Kunjungan status updated to Succeed (Transfer)', [
                    'kunjungan_id' => $kunjungan->id,
                    'status_lama' => 'Payment',
                    'status_baru' => 'Succeed',
                ]);

                $pembayaran = $pemb->fresh([
                    'emr.kunjungan.pasien.user',
                    'metodePembayaran',
                ]);

                DB::afterCommit(function () use ($pembayaranId) {
                    try {
                        $pembFresh = Pembayaran::with(['emr.kunjungan.pasien.user'])->find($pembayaranId);
                        if ($pembFresh) {
                            NotificationHelper::kirimNotifikasiPembayaranSelesai($pembFresh);
                        }
                    } catch (\Throwable $e) {
                        Log::error('❌ Error sending notification for pembayaran transfer', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'pembayaran_id' => $pembayaranId,
                        ]);
                    }
                });
            });

            Log::info('=== TRANSAKSI TRANSFER SUCCESS ===', [
                'pembayaran_id' => $pembayaranId,
            ]);

            return response()->json([
                'success' => true,
                'data' => $pembayaran,
                'message' => 'Bukti transfer diterima. Nominal terbayar: Rp' .
                    number_format((float) $pembayaran->uang_yang_diterima, 0, ',', '.') . '. Terimakasih 😊😊😊',
            ]);
        } catch (ValidationException $e) {
            Log::warning('Validation error in transaksi transfer', [
                'errors' => $e->errors(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('=== TRANSAKSI TRANSFER ERROR ===', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'pembayaran_id' => $pembayaranId ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran transfer: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function normalizeDiskonItemsCheckout(array $items): array
    {
        $out = [];

        foreach ($items as $it) {
            $id = (int)($it['id'] ?? 0);
            $persen = (float)($it['persen'] ?? 0);

            $persen = max(0, min(100, $persen));

            if ($id > 0 && $persen > 0) {
                $out[] = [
                    'id' => $id,
                    'persen' => $persen,
                ];
            }
        }

        usort($out, fn($a, $b) => $a['id'] <=> $b['id']);

        return $out;
    }

    private function decodeApprovalDiskonItems($raw): array
    {
        if (is_array($raw)) {
            $items = $raw;
        } elseif (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $items = is_array($decoded) ? $decoded : [];
        } else {
            $items = [];
        }

        return $this->normalizeDiskonItemsCheckout($items);
    }

    private function validateApprovalBeforePayment(int $pembayaranId, string $diskonItemsJson): array
    {
        $decoded = json_decode($diskonItemsJson, true);

        if (!is_array($decoded)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'diskon_items' => 'Format diskon_items tidak valid.',
            ]);
        }

        $normalizedRequest = $this->normalizeDiskonItemsCheckout($decoded);

        $latestApproval = \App\Models\DiskonApproval::where('pembayaran_id', $pembayaranId)
            ->latest('id')
            ->first();

        if ($latestApproval && $latestApproval->status === 'pending') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'diskon_items' => 'Pembayaran ini sedang diajukan untuk diskon. Tunggu keputusan Manager terlebih dahulu.',
            ]);
        }

        $approvedSnapshot = $latestApproval
            ? $this->decodeApprovalDiskonItems($latestApproval->diskon_items)
            : [];

        if (count($normalizedRequest) > 0) {
            if (!$latestApproval || $latestApproval->status !== 'approved') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'diskon_items' => 'Diskon belum disetujui Manager.',
                ]);
            }

            if (json_encode($normalizedRequest) !== json_encode($approvedSnapshot)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'diskon_items' => 'Data diskon berubah / tidak sesuai approval. Silakan refresh halaman transaksi.',
                ]);
            }
        } else {
            if ($latestApproval && $latestApproval->status === 'approved' && count($approvedSnapshot) > 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'diskon_items' => 'Data diskon approved tidak sesuai. Silakan refresh halaman transaksi.',
                ]);
            }
        }

        return $normalizedRequest;
    }

    public function showKwitansi($kodeTransaksi)
    {
        $dataPembayaran = Pembayaran::with([
            'emr.kunjungan.pasien',
            'metodePembayaran',
            'pembayaranDetail',
        ])->where('kode_transaksi', $kodeTransaksi)->firstOrFail();

        $details = $dataPembayaran->pembayaranDetail ?? collect();

        $namaPT = 'Royal Klinik';

        return view('kasir.pembayaran.kwitansi', compact(
            'dataPembayaran',
            'details',
            'namaPT'
        ));
    }

    public function totalTransaksiHariIni()
    {
        $total = (float) DB::table('pembayaran')
            ->whereDate('tanggal_pembayaran', Carbon::today())
            ->sum('total_tagihan');

        return response()->json(['total' => $total]);
    }

    public function totalKeseluruhanTransaksi()
    {
        $total = (float) DB::table('pembayaran')->sum('total_tagihan');

        return response()->json(['total' => $total]);
    }

    public function totalTransaksiObatHariIni()
    {
        $total = DB::table('penjualan_obat')
            ->whereDate('tanggal_transaksi', Carbon::today())
            ->whereNotNull('kode_transaksi')
            ->distinct('kode_transaksi')
            ->count('kode_transaksi');

        return response()->json(['total' => $total]);
    }

    public function totalKeseluruhanTransaksiObat()
    {
        $total = DB::table('penjualan_obat')
            ->whereNotNull('kode_transaksi')
            ->distinct('kode_transaksi')
            ->count('kode_transaksi');

        return response()->json(['total' => $total]);
    }

    public function createKasir(Request $request)
    {
        try {
            $request->validate([
                'foto_kasir' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',
                'username_kasir' => 'required|string|max:255|',
                'nama_kasir' => 'required|string|max:255',
                'email_kasir' => 'required|email',
                'no_hp_kasir' => 'nullable|string|max:20',
                'password_kasir' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'username' => $request->username_kasir,
                'email' => $request->email_kasir,
                'password' => Hash::make($request->password_kasir),
                'role' => 'Kasir',
            ]);

            $fotoPath = null;
            if ($request->hasFile('foto_kasir')) {
                $file = $request->file('foto_kasir');

                $extension = strtolower($file->getClientOriginalExtension());
                if ($extension === 'jfif') {
                    $extension = 'jpg';
                }

                $fileName = 'kasir_' . time() . '.' . $extension;
                $path = 'kasir/' . $fileName;

                if ($extension === 'svg') {
                    Storage::disk('public')->put($path, file_get_contents($file));
                } else {
                    $image = Image::read($file);
                    $image->scale(width: 800);
                    Storage::disk('public')->put($path, (string) $image->encodeByExtension($extension, quality: 80));
                }

                $fotoPath = $path;
            }

            Kasir::create([
                'user_id' => $user->id,
                'nama_kasir' => $request->nama_kasir,
                'foto_kasir' => $fotoPath,
                'no_hp_kasir' => $request->no_hp_kasir,
            ]);

            return response()->json(['message' => 'Data kasir berhasil ditambahkan.']);
        } catch (PostTooLargeException $e) {
            return response()->json([
                'message' => 'Ukuran file terlalu besar! Maksimal 5 MB.',
            ], 413);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Tidak ada respon dari server.',
                'error_detail' => $e->getMessage(),
            ], 500);
        }
    }

    public function getKasirById($id)
    {
        $data = Kasir::with('user')->findOrFail($id);

        return response()->json(['data' => $data]);
    }

    public function updateKasir(Request $request, $id)
    {
        try {
            $kasir = Kasir::findOrFail($id);
            $user = $kasir->user;

            $request->validate([
                'edit_username_kasir' => 'required|string|max:255',
                'edit_nama_kasir' => 'required|string|max:255',
                'edit_email_kasir' => 'required|email',
                'edit_foto_kasir' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp,svg,jfif|max:5120',
                'edit_no_hp_kasir' => 'nullable|string|max:20',
                'edit_password_kasir' => 'nullable|string|min:8|confirmed',
            ]);

            $user->username = $request->input('edit_username_kasir');
            $user->email = $request->input('edit_email_kasir');

            if ($request->filled('edit_password_kasir')) {
                $user->password = Hash::make($request->input('edit_password_kasir'));
            }

            $fotoPath = null;
            if ($request->hasFile('edit_foto_kasir')) {
                $file = $request->file('edit_foto_kasir');

                $extension = strtolower($file->getClientOriginalExtension());
                if ($extension === 'jfif') {
                    $extension = 'jpg';
                }

                $fileName = 'kasir_' . time() . '.' . $extension;
                $path = 'kasir/' . $fileName;

                if ($extension === 'svg') {
                    Storage::disk('public')->put($path, file_get_contents($file));
                } else {
                    $image = Image::read($file);
                    $image->scale(width: 800);
                    Storage::disk('public')->put($path, (string) $image->encodeByExtension($extension, quality: 80));
                }

                $fotoPath = $path;

                if ($kasir->foto_kasir && Storage::disk('public')->exists($kasir->foto_kasir)) {
                    Storage::disk('public')->delete($kasir->foto_kasir);
                }
            }

            $updateData = [
                'nama_kasir' => $request->edit_nama_kasir,
                'no_hp_kasir' => $request->edit_no_hp_kasir,
            ];

            $updateDataUser = ([
                'username' => $request->edit_username_kasir,
            ]);

            if ($fotoPath) {
                $updateData['foto_kasir'] = $fotoPath;
            }

            $kasir->update($updateData);
            $user->update($updateDataUser);

            return response()->json(['message' => 'Data kasir berhasil diperbarui.']);
        } catch (PostTooLargeException $e) {
            return response()->json([
                'message' => 'Ukuran file terlalu besar! Maksimal 5 MB.',
            ], 413);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Tidak ada respon dari server.',
                'error_detail' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteKasir($id)
    {
        $kasir = Kasir::findOrFail($id);

        $kasir->user->delete();
        $kasir->delete();

        if ($kasir->foto_kasir && Storage::disk('public')->exists($kasir->foto_kasir)) {
            Storage::disk('public')->delete($kasir->foto_kasir);
        }

        return response()->json(['success' => 'Data kasir berhasil dihapus.']);
    }
}
