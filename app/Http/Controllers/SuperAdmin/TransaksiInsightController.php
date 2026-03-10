<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiInsightController extends Controller
{
    public function index(Request $request)
    {
        $periode = $this->resolvePeriode($request);
        $selectedDate = $this->resolveSelectedDate($request);
        $selectedMonth = $this->resolveSelectedMonth($request);
        $selectedYear = $this->resolveSelectedYear($request);

        $statusOptions = ['Sudah Bayar', 'Belum Bayar'];

        $poliList = DB::table('poli')
            ->orderBy('nama_poli')
            ->get();

        $dokterList = DB::table('dokter')
            ->orderBy('nama_dokter')
            ->get();

        $metodePembayaranList = DB::table('metode_pembayaran')
            ->orderBy('nama_metode')
            ->get();

        $baseQuery = DB::table('pembayaran as p')
            ->leftJoin('emr as e', 'e.id', '=', 'p.emr_id')
            ->leftJoin('pasien as ps', 'ps.id', '=', 'e.pasien_id')
            ->leftJoin('kunjungan as k', 'k.id', '=', 'e.kunjungan_id')
            ->leftJoin('poli as pl_emr', 'pl_emr.id', '=', 'e.poli_id')
            ->leftJoin('poli as pl_kunj', 'pl_kunj.id', '=', 'k.poli_id')
            ->leftJoin('dokter as dr_emr', 'dr_emr.id', '=', 'e.dokter_id')
            ->leftJoin('dokter as dr_kunj', 'dr_kunj.id', '=', 'k.dokter_id')
            ->leftJoin('metode_pembayaran as mp', 'mp.id', '=', 'p.metode_pembayaran_id');

        $this->applyFilters($baseQuery, $request);

        $transaksis = (clone $baseQuery)
            ->leftJoin('pembayaran_detail as pd', 'pd.pembayaran_id', '=', 'p.id')
            ->select(
                'p.id',
                'p.emr_id',
                'p.kode_transaksi',
                'p.tanggal_pembayaran',
                'p.status',
                'p.total_tagihan',
                'p.diskon_tipe',
                'p.diskon_nilai',
                'p.total_setelah_diskon',
                'p.uang_yang_diterima',
                'p.kembalian',
                'p.catatan',
                'p.created_at',
                'mp.nama_metode',
                'ps.no_emr',
                'ps.nama_pasien',
                'ps.no_bpjs',
                'k.tanggal_kunjungan',
                DB::raw('COALESCE(pl_emr.nama_poli, pl_kunj.nama_poli) as nama_poli'),
                DB::raw('COALESCE(dr_emr.nama_dokter, dr_kunj.nama_dokter) as nama_dokter'),
                DB::raw('COUNT(pd.id) as total_item'),
                DB::raw('COALESCE(SUM(pd.qty), 0) as total_qty')
            )
            ->groupBy(
                'p.id',
                'p.emr_id',
                'p.kode_transaksi',
                'p.tanggal_pembayaran',
                'p.status',
                'p.total_tagihan',
                'p.diskon_tipe',
                'p.diskon_nilai',
                'p.total_setelah_diskon',
                'p.uang_yang_diterima',
                'p.kembalian',
                'p.catatan',
                'p.created_at',
                'mp.nama_metode',
                'ps.no_emr',
                'ps.nama_pasien',
                'ps.no_bpjs',
                'k.tanggal_kunjungan',
                'pl_emr.nama_poli',
                'pl_kunj.nama_poli',
                'dr_emr.nama_dokter',
                'dr_kunj.nama_dokter'
            )
            ->orderByRaw('COALESCE(p.tanggal_pembayaran, p.created_at) DESC')
            ->get()
            ->map(function ($item) {
                $tanggalTransaksi = $item->tanggal_pembayaran ?: $item->created_at;

                $item->tanggal_transaksi_label = $tanggalTransaksi
                    ? Carbon::parse($tanggalTransaksi)->translatedFormat('d M Y H:i')
                    : '-';

                $item->tanggal_kunjungan_label = $item->tanggal_kunjungan
                    ? Carbon::parse($item->tanggal_kunjungan)->translatedFormat('d M Y')
                    : '-';

                $item->status_class = $this->statusPembayaranClass($item->status);

                $item->diskon_label = '-';
                if (!is_null($item->diskon_nilai) && (float) $item->diskon_nilai > 0) {
                    $item->diskon_label = $item->diskon_tipe === 'persen'
                        ? rtrim(rtrim(number_format((float) $item->diskon_nilai, 2, ',', '.'), '0'), ',') . '%'
                        : 'Rp ' . number_format((float) $item->diskon_nilai, 0, ',', '.');
                }

                $item->final_total = !is_null($item->total_setelah_diskon)
                    ? (float) $item->total_setelah_diskon
                    : (float) ($item->total_tagihan ?? 0);

                $item->nama_pasien = $item->nama_pasien ?: '-';
                $item->nama_poli = $item->nama_poli ?: '-';
                $item->nama_dokter = $item->nama_dokter ?: '-';
                $item->nama_metode = $item->nama_metode ?: '-';
                $item->no_emr = $item->no_emr ?: '-';
                $item->no_bpjs = $item->no_bpjs ?: '-';
                $item->catatan = $item->catatan ?: '-';

                return $item;
            });

        $statsBase = clone $baseQuery;

        $totalPendapatan = (clone $statsBase)
            ->where('p.status', 'Sudah Bayar')
            ->selectRaw('COALESCE(SUM(COALESCE(p.total_setelah_diskon, p.total_tagihan, 0)), 0) as total_pendapatan')
            ->value('total_pendapatan');

        $rataRataTransaksi = (clone $statsBase)
            ->where('p.status', 'Sudah Bayar')
            ->avg(DB::raw('COALESCE(p.total_setelah_diskon, p.total_tagihan)'));

        $stats = [
            'totalTransaksi'    => (clone $statsBase)->count('p.id'),
            'transaksiSukses'   => (clone $statsBase)->where('p.status', 'Sudah Bayar')->count('p.id'),
            'transaksiPending'  => (clone $statsBase)->where('p.status', 'Belum Bayar')->count('p.id'),
            'totalPendapatan'   => (float) ($totalPendapatan ?? 0),
            'rataRataTransaksi' => (float) ($rataRataTransaksi ?? 0),
            'pasienTerlibat'    => (clone $statsBase)->whereNotNull('e.pasien_id')->distinct()->count('e.pasien_id'),
        ];

        $chartStatus = (clone $statsBase)
            ->select('p.status', DB::raw('COUNT(p.id) as total'))
            ->groupBy('p.status')
            ->orderByDesc('total')
            ->get();

        $chartMetode = (clone $statsBase)
            ->select(DB::raw("COALESCE(mp.nama_metode, 'Tanpa Metode') as nama_metode"), DB::raw('COUNT(p.id) as total'))
            ->groupBy('nama_metode')
            ->orderByDesc('total')
            ->get();

        $trendChart = $this->buildTrendChart($request);
        $trendMeta = $this->getTrendMeta($periode, $selectedDate, $selectedMonth, $selectedYear);

        return view('super-admin.transaksi-insight.index', [
            'transaksis'                => $transaksis,
            'stats'                     => $stats,
            'poliList'                  => $poliList,
            'dokterList'                => $dokterList,
            'metodePembayaranList'      => $metodePembayaranList,
            'statusOptions'             => $statusOptions,
            'filters'                   => [
                'search'               => $request->get('search', ''),
                'status'               => $request->get('status', ''),
                'poli_id'              => $request->get('poli_id', ''),
                'dokter_id'            => $request->get('dokter_id', ''),
                'metode_pembayaran_id' => $request->get('metode_pembayaran_id', ''),
                'periode'              => $periode,
                'tanggal'              => $selectedDate->format('Y-m-d'),
                'bulan_tahun'          => $selectedMonth->format('Y-m'),
                'tahun'                => (string) $selectedYear,
            ],
            'periodeLabel'              => $this->getPeriodeLabel($periode, $selectedDate, $selectedMonth, $selectedYear),
            'trendTitle'                => $trendMeta['title'],
            'trendSubtitle'             => $trendMeta['subtitle'],
            'trendDatasetLabel'         => $trendMeta['dataset_label'],
            'chartStatusLabels'         => $chartStatus->pluck('status')->values(),
            'chartStatusValues'         => $chartStatus->pluck('total')->map(fn ($v) => (int) $v)->values(),
            'chartMetodeLabels'         => $chartMetode->pluck('nama_metode')->values(),
            'chartMetodeValues'         => $chartMetode->pluck('total')->map(fn ($v) => (int) $v)->values(),
            'chartTrendLabels'          => $trendChart['labels'],
            'chartTrendValues'          => $trendChart['values'],
        ]);
    }

    public function show($id)
    {
        $transaksi = DB::table('pembayaran as p')
            ->leftJoin('emr as e', 'e.id', '=', 'p.emr_id')
            ->leftJoin('pasien as ps', 'ps.id', '=', 'e.pasien_id')
            ->leftJoin('kunjungan as k', 'k.id', '=', 'e.kunjungan_id')
            ->leftJoin('poli as pl_emr', 'pl_emr.id', '=', 'e.poli_id')
            ->leftJoin('poli as pl_kunj', 'pl_kunj.id', '=', 'k.poli_id')
            ->leftJoin('dokter as dr_emr', 'dr_emr.id', '=', 'e.dokter_id')
            ->leftJoin('dokter as dr_kunj', 'dr_kunj.id', '=', 'k.dokter_id')
            ->leftJoin('metode_pembayaran as mp', 'mp.id', '=', 'p.metode_pembayaran_id')
            ->where('p.id', $id)
            ->select(
                'p.*',
                'e.kunjungan_id',
                'e.pasien_id',
                'ps.nama_pasien',
                'ps.no_emr',
                'ps.no_bpjs',
                'ps.no_hp_pasien',
                'ps.nik',
                'k.no_antrian',
                'k.status as status_kunjungan',
                'k.tanggal_kunjungan',
                'k.keluhan_awal',
                'mp.nama_metode',
                DB::raw('COALESCE(pl_emr.nama_poli, pl_kunj.nama_poli) as nama_poli'),
                DB::raw('COALESCE(dr_emr.nama_dokter, dr_kunj.nama_dokter) as nama_dokter')
            )
            ->first();

        abort_if(!$transaksi, 404);

        $transaksi->tanggal_transaksi_label = ($transaksi->tanggal_pembayaran ?: $transaksi->created_at)
            ? Carbon::parse($transaksi->tanggal_pembayaran ?: $transaksi->created_at)->translatedFormat('d M Y H:i')
            : '-';

        $transaksi->tanggal_kunjungan_label = $transaksi->tanggal_kunjungan
            ? Carbon::parse($transaksi->tanggal_kunjungan)->translatedFormat('d M Y')
            : '-';

        $transaksi->status_class = $this->statusPembayaranClass($transaksi->status);
        $transaksi->status_kunjungan_class = $this->statusKunjunganClass($transaksi->status_kunjungan);

        $transaksi->diskon_label = '-';
        if (!is_null($transaksi->diskon_nilai) && (float) $transaksi->diskon_nilai > 0) {
            $transaksi->diskon_label = $transaksi->diskon_tipe === 'persen'
                ? rtrim(rtrim(number_format((float) $transaksi->diskon_nilai, 2, ',', '.'), '0'), ',') . '%'
                : 'Rp ' . number_format((float) $transaksi->diskon_nilai, 0, ',', '.');
        }

        $transaksi->final_total = !is_null($transaksi->total_setelah_diskon)
            ? (float) $transaksi->total_setelah_diskon
            : (float) ($transaksi->total_tagihan ?? 0);

        $details = DB::table('pembayaran_detail as pd')
            ->leftJoin('layanan as l', 'l.id', '=', 'pd.layanan_id')
            ->leftJoin('resep_obat as ro', 'ro.id', '=', 'pd.resep_obat_id')
            ->leftJoin('order_lab_detail as old', 'old.id', '=', 'pd.order_lab_detail_id')
            ->leftJoin('order_lab as ol', 'ol.id', '=', 'old.order_lab_id')
            ->leftJoin('order_radiologi_detail as ord', 'ord.id', '=', 'pd.order_radiologi_detail_id')
            ->where('pd.pembayaran_id', $id)
            ->select(
                'pd.*',
                'l.nama_layanan',

                'ro.resep_id',
                'ro.obat_id',
                'ro.jumlah as resep_jumlah',
                'ro.dosis as resep_dosis',
                'ro.keterangan as resep_keterangan',

                'old.order_lab_id',
                'old.jenis_pemeriksaan_lab_id',
                'old.status_pemeriksaan as lab_status_pemeriksaan',

                'ol.no_order_lab',
                'ol.tanggal_order as lab_tanggal_order',
                'ol.tanggal_pemeriksaan as lab_tanggal_pemeriksaan',
                'ol.jam_pemeriksaan as lab_jam_pemeriksaan',
                'ol.status as lab_status_order',

                'ord.order_radiologi_id',
                'ord.jenis_pemeriksaan_radiologi_id',
                'ord.status_pemeriksaan as radiologi_status_pemeriksaan'
            )
            ->orderBy('pd.id')
            ->get()
            ->map(function ($item) {
                $item->jenis_item = 'Lainnya';
                $item->nama_item_final = $item->nama_item ?: '-';
                $item->referensi_label = '-';
                $item->informasi_tambahan = '-';

                if (!is_null($item->layanan_id)) {
                    $item->jenis_item = 'Layanan';
                    $item->nama_item_final = $item->nama_layanan ?: ($item->nama_item ?: 'Layanan #' . $item->layanan_id);
                    $item->referensi_label = 'layanan_id: ' . $item->layanan_id;
                    $item->informasi_tambahan = 'Item layanan dari kunjungan pasien';
                } elseif (!is_null($item->resep_obat_id)) {
                    $item->jenis_item = 'Resep Obat';
                    $item->nama_item_final = $item->nama_item ?: 'Resep Obat #' . $item->resep_obat_id;
                    $item->referensi_label = 'resep_obat_id: ' . $item->resep_obat_id;

                    $parts = [];
                    if (!is_null($item->obat_id)) {
                        $parts[] = 'obat_id: ' . $item->obat_id;
                    }
                    if (!is_null($item->resep_jumlah)) {
                        $parts[] = 'jumlah resep: ' . $item->resep_jumlah;
                    }
                    if (!is_null($item->resep_dosis)) {
                        $parts[] = 'dosis: ' . rtrim(rtrim(number_format((float) $item->resep_dosis, 2, ',', '.'), '0'), ',');
                    }
                    if (!empty($item->resep_keterangan)) {
                        $parts[] = 'keterangan: ' . $item->resep_keterangan;
                    }

                    $item->informasi_tambahan = count($parts) ? implode(' | ', $parts) : '-';
                } elseif (!is_null($item->order_lab_detail_id)) {
                    $item->jenis_item = 'Laboratorium';
                    $item->nama_item_final = $item->nama_item ?: 'Pemeriksaan Lab #' . ($item->jenis_pemeriksaan_lab_id ?: $item->order_lab_detail_id);
                    $item->referensi_label = 'order_lab_detail_id: ' . $item->order_lab_detail_id;

                    $parts = [];
                    if (!empty($item->no_order_lab)) {
                        $parts[] = 'no order: ' . $item->no_order_lab;
                    } elseif (!is_null($item->order_lab_id)) {
                        $parts[] = 'order_lab_id: ' . $item->order_lab_id;
                    }

                    if (!is_null($item->jenis_pemeriksaan_lab_id)) {
                        $parts[] = 'jenis_pemeriksaan_lab_id: ' . $item->jenis_pemeriksaan_lab_id;
                    }

                    if (!empty($item->lab_status_order)) {
                        $parts[] = 'status order: ' . $item->lab_status_order;
                    }

                    if (!empty($item->lab_status_pemeriksaan)) {
                        $parts[] = 'status pemeriksaan: ' . $item->lab_status_pemeriksaan;
                    }

                    if (!empty($item->lab_tanggal_pemeriksaan)) {
                        $parts[] = 'tgl periksa: ' . Carbon::parse($item->lab_tanggal_pemeriksaan)->translatedFormat('d M Y');
                    }

                    if (!empty($item->lab_jam_pemeriksaan)) {
                        $parts[] = 'jam: ' . substr($item->lab_jam_pemeriksaan, 0, 5);
                    }

                    $item->informasi_tambahan = count($parts) ? implode(' | ', $parts) : '-';
                } elseif (!is_null($item->order_radiologi_detail_id)) {
                    $item->jenis_item = 'Radiologi';
                    $item->nama_item_final = $item->nama_item ?: 'Pemeriksaan Radiologi #' . ($item->jenis_pemeriksaan_radiologi_id ?: $item->order_radiologi_detail_id);
                    $item->referensi_label = 'order_radiologi_detail_id: ' . $item->order_radiologi_detail_id;

                    $parts = [];
                    if (!is_null($item->order_radiologi_id)) {
                        $parts[] = 'order_radiologi_id: ' . $item->order_radiologi_id;
                    }

                    if (!is_null($item->jenis_pemeriksaan_radiologi_id)) {
                        $parts[] = 'jenis_pemeriksaan_radiologi_id: ' . $item->jenis_pemeriksaan_radiologi_id;
                    }

                    if (!empty($item->radiologi_status_pemeriksaan)) {
                        $parts[] = 'status pemeriksaan: ' . $item->radiologi_status_pemeriksaan;
                    }

                    $item->informasi_tambahan = count($parts) ? implode(' | ', $parts) : '-';
                }

                return $item;
            });

        $layananKunjungan = collect();

        if (!is_null($transaksi->kunjungan_id)) {
            $layananKunjungan = DB::table('kunjungan_layanan as kl')
                ->join('layanan as l', 'l.id', '=', 'kl.layanan_id')
                ->where('kl.kunjungan_id', $transaksi->kunjungan_id)
                ->select(
                    'kl.id',
                    'kl.jumlah',
                    'l.nama_layanan',
                    'l.harga_sebelum_diskon',
                    'l.harga_setelah_diskon'
                )
                ->orderBy('kl.id')
                ->get()
                ->map(function ($item) {
                    $harga = !is_null($item->harga_setelah_diskon)
                        ? (float) $item->harga_setelah_diskon
                        : (float) ($item->harga_sebelum_diskon ?? 0);

                    $item->harga_aktif = $harga;
                    $item->estimasi_total = $harga * (int) $item->jumlah;

                    return $item;
                });
        }

        $stats = [
            'totalItem'            => $details->count(),
            'totalQty'             => (int) $details->sum('qty'),
            'subtotalDetail'       => (float) $details->sum('subtotal'),
            'totalBayar'           => (float) $transaksi->final_total,
            'totalLayananVisit'    => $layananKunjungan->count(),
            'totalQtyLayananVisit' => (int) $layananKunjungan->sum('jumlah'),
            'itemLayanan'          => $details->where('jenis_item', 'Layanan')->count(),
            'itemResep'            => $details->where('jenis_item', 'Resep Obat')->count(),
            'itemLab'              => $details->where('jenis_item', 'Laboratorium')->count(),
            'itemRadiologi'        => $details->where('jenis_item', 'Radiologi')->count(),
        ];

        $chartJenis = $details->groupBy('jenis_item')->map->count();

        return view('super-admin.transaksi-insight.show', [
            'transaksi'         => $transaksi,
            'details'           => $details,
            'layananKunjungan'  => $layananKunjungan,
            'stats'             => $stats,
            'chartJenisLabels'  => $chartJenis->keys()->values(),
            'chartJenisValues'  => $chartJenis->values()->map(fn ($v) => (int) $v)->values(),
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
                $q->where('p.kode_transaksi', 'like', '%' . $search . '%')
                    ->orWhere('ps.nama_pasien', 'like', '%' . $search . '%')
                    ->orWhere('ps.no_emr', 'like', '%' . $search . '%')
                    ->orWhere('ps.no_bpjs', 'like', '%' . $search . '%')
                    ->orWhere('mp.nama_metode', 'like', '%' . $search . '%')
                    ->orWhere('pl_emr.nama_poli', 'like', '%' . $search . '%')
                    ->orWhere('pl_kunj.nama_poli', 'like', '%' . $search . '%')
                    ->orWhere('dr_emr.nama_dokter', 'like', '%' . $search . '%')
                    ->orWhere('dr_kunj.nama_dokter', 'like', '%' . $search . '%')
                    ->orWhere('p.catatan', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('p.status', $request->status);
        }

        if ($request->filled('poli_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('e.poli_id', $request->poli_id)
                    ->orWhere('k.poli_id', $request->poli_id);
            });
        }

        if ($request->filled('dokter_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('e.dokter_id', $request->dokter_id)
                    ->orWhere('k.dokter_id', $request->dokter_id);
            });
        }

        if ($request->filled('metode_pembayaran_id')) {
            $query->where('p.metode_pembayaran_id', $request->metode_pembayaran_id);
        }
    }

    private function applyPeriodFilter($query, Request $request): void
    {
        $periode = $this->resolvePeriode($request);

        if ($periode === 'harian') {
            $selectedDate = $this->resolveSelectedDate($request);
            $query->whereDate(DB::raw('COALESCE(p.tanggal_pembayaran, p.created_at)'), $selectedDate->toDateString());
            return;
        }

        if ($periode === 'bulanan') {
            $selectedMonth = $this->resolveSelectedMonth($request);
            $query->whereYear(DB::raw('COALESCE(p.tanggal_pembayaran, p.created_at)'), $selectedMonth->year)
                ->whereMonth(DB::raw('COALESCE(p.tanggal_pembayaran, p.created_at)'), $selectedMonth->month);
            return;
        }

        if ($periode === 'tahunan') {
            $selectedYear = $this->resolveSelectedYear($request);
            $query->whereYear(DB::raw('COALESCE(p.tanggal_pembayaran, p.created_at)'), $selectedYear);
        }
    }

    private function buildTrendChart(Request $request): array
    {
        $periode = $this->resolvePeriode($request);
        $selectedDate = $this->resolveSelectedDate($request);
        $selectedMonth = $this->resolveSelectedMonth($request);
        $selectedYear = $this->resolveSelectedYear($request);

        $baseQuery = DB::table('pembayaran as p')
            ->leftJoin('emr as e', 'e.id', '=', 'p.emr_id')
            ->leftJoin('pasien as ps', 'ps.id', '=', 'e.pasien_id')
            ->leftJoin('kunjungan as k', 'k.id', '=', 'e.kunjungan_id')
            ->leftJoin('poli as pl_emr', 'pl_emr.id', '=', 'e.poli_id')
            ->leftJoin('poli as pl_kunj', 'pl_kunj.id', '=', 'k.poli_id')
            ->leftJoin('dokter as dr_emr', 'dr_emr.id', '=', 'e.dokter_id')
            ->leftJoin('dokter as dr_kunj', 'dr_kunj.id', '=', 'k.dokter_id')
            ->leftJoin('metode_pembayaran as mp', 'mp.id', '=', 'p.metode_pembayaran_id');

        $this->applyCommonFilters($baseQuery, $request);

        if ($periode === 'harian') {
            $start = $selectedDate->copy()->subDays(6);
            $end = $selectedDate->copy();

            $raw = (clone $baseQuery)
                ->whereBetween(DB::raw('DATE(COALESCE(p.tanggal_pembayaran, p.created_at))'), [$start->toDateString(), $end->toDateString()])
                ->selectRaw("DATE(COALESCE(p.tanggal_pembayaran, p.created_at)) as label_key, COUNT(p.id) as total")
                ->groupBy('label_key')
                ->orderBy('label_key')
                ->pluck('total', 'label_key');

            $labels = [];
            $values = [];

            $cursor = $start->copy();
            while ($cursor <= $end) {
                $key = $cursor->format('Y-m-d');
                $labels[] = $cursor->translatedFormat('d M');
                $values[] = (int) ($raw[$key] ?? 0);
                $cursor->addDay();
            }

            return [
                'labels' => $labels,
                'values' => $values,
            ];
        }

        if ($periode === 'bulanan') {
            $start = $selectedMonth->copy()->startOfMonth();
            $end = $selectedMonth->copy()->endOfMonth();

            $raw = (clone $baseQuery)
                ->whereYear(DB::raw('COALESCE(p.tanggal_pembayaran, p.created_at)'), $selectedMonth->year)
                ->whereMonth(DB::raw('COALESCE(p.tanggal_pembayaran, p.created_at)'), $selectedMonth->month)
                ->selectRaw("DATE(COALESCE(p.tanggal_pembayaran, p.created_at)) as label_key, COUNT(p.id) as total")
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
            ->whereYear(DB::raw('COALESCE(p.tanggal_pembayaran, p.created_at)'), $selectedYear)
            ->selectRaw("DATE_FORMAT(COALESCE(p.tanggal_pembayaran, p.created_at), '%Y-%m') as label_key, COUNT(p.id) as total")
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
                'title' => 'Tren 7 Hari Terakhir',
                'subtitle' => 'Grafik transaksi 7 hari terakhir sampai tanggal ' . $selectedDate->translatedFormat('d F Y'),
                'dataset_label' => 'Transaksi Harian',
            ];
        }

        if ($periode === 'bulanan') {
            return [
                'title' => 'Tren Harian Dalam Bulan',
                'subtitle' => 'Grafik jumlah transaksi per hari pada ' . $selectedMonth->translatedFormat('F Y'),
                'dataset_label' => 'Transaksi Bulanan',
            ];
        }

        return [
            'title' => 'Tren Bulanan Dalam Tahun',
            'subtitle' => 'Grafik jumlah transaksi per bulan pada tahun ' . $selectedYear,
            'dataset_label' => 'Transaksi Tahunan',
        ];
    }

    private function statusPembayaranClass(?string $status): string
    {
        return match ($status) {
            'Sudah Bayar' => 'bg-emerald-100 text-emerald-700',
            'Belum Bayar' => 'bg-amber-100 text-amber-700',
            default       => 'bg-slate-100 text-slate-700',
        };
    }

    private function statusKunjunganClass(?string $status): string
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