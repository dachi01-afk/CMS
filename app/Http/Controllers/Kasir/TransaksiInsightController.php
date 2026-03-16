<?php

namespace App\Http\Controllers\Kasir;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiInsightController extends Controller
{
    public function pembayaran(Request $request)
    {
        $search = trim((string) $request->get('search'));
        $filter = $this->normalizeChartFilter($request->get('filter', 'bulanan'));

        $query = DB::table('pembayaran as p')
            ->leftJoin('metode_pembayaran as mp', 'mp.id', '=', 'p.metode_pembayaran_id')
            ->select([
                'p.id',
                'p.kode_transaksi',
                'p.tanggal_pembayaran',
                'p.total_tagihan',
                'p.diskon_nilai',
                'p.total_setelah_diskon',
                'p.uang_yang_diterima',
                'p.kembalian',
                'p.status',
                'mp.nama_metode',
            ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('p.kode_transaksi', 'like', "%{$search}%")
                    ->orWhere('p.status', 'like', "%{$search}%")
                    ->orWhere('mp.nama_metode', 'like', "%{$search}%");
            });
        }

        $rows = $query->orderByDesc('p.tanggal_pembayaran')->paginate(10)->withQueryString();

        $summary = [
            'total' => DB::table('pembayaran')->count(),
            'hari_ini' => DB::table('pembayaran')->whereDate('tanggal_pembayaran', today())->count(),
            'pendapatan' => DB::table('pembayaran')->where('status', 'Sudah Bayar')->sum('total_setelah_diskon'),
        ];

        $chartData = $this->buildPembayaranChart($filter);

        return view('kasir.transaksi-insight.index', compact(
            'rows',
            'search',
            'summary',
            'chartData',
            'filter'
        ));
    }

    public function obat(Request $request)
    {
        $search = trim((string) $request->get('search'));
        $filter = $this->normalizeChartFilter($request->get('filter', 'bulanan'));

        $query = DB::table('penjualan_obat as po')
            ->leftJoin('metode_pembayaran as mp', 'mp.id', '=', 'po.metode_pembayaran_id')
            ->leftJoin('pasien as ps', 'ps.id', '=', 'po.pasien_id')
            ->select([
                'po.id',
                'po.kode_transaksi',
                'po.tanggal_transaksi',
                'po.total_tagihan',
                'po.diskon_nilai',
                'po.total_setelah_diskon',
                'po.uang_yang_diterima',
                'po.kembalian',
                'po.status',
                'ps.nama_pasien',
                'mp.nama_metode',
            ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('po.kode_transaksi', 'like', "%{$search}%")
                    ->orWhere('po.status', 'like', "%{$search}%")
                    ->orWhere('ps.nama_pasien', 'like', "%{$search}%")
                    ->orWhere('mp.nama_metode', 'like', "%{$search}%");
            });
        }

        $rows = $query->orderByDesc('po.tanggal_transaksi')->paginate(10)->withQueryString();

        $summary = [
            'total' => DB::table('penjualan_obat')->count(),
            'hari_ini' => DB::table('penjualan_obat')->whereDate('tanggal_transaksi', today())->count(),
            'pendapatan' => DB::table('penjualan_obat')->where('status', 'Sudah Bayar')->sum('total_setelah_diskon'),
        ];

        $chartData = $this->buildObatChart($filter);

        return view('kasir.transaksi-obat-insight.index', compact(
            'rows',
            'search',
            'summary',
            'chartData',
            'filter'
        ));
    }

    public function layanan(Request $request)
    {
        $search = trim((string) $request->get('search'));
        $filter = $this->normalizeChartFilter($request->get('filter', 'bulanan'));

        $query = DB::table('order_layanan as ol')
            ->join('order_layanan_detail as old', 'old.order_layanan_id', '=', 'ol.id')
            ->join('layanan as l', 'l.id', '=', 'old.layanan_id')
            ->join('kategori_layanan as kl', 'kl.id', '=', 'l.kategori_layanan_id')
            ->whereRaw('LOWER(kl.nama_kategori) NOT LIKE ?', ['%pemeriksaan%'])
            ->select([
                'ol.id',
                'ol.kode_transaksi',
                'ol.tanggal_order',
                'ol.subtotal',
                'ol.potongan_pesanan',
                'ol.total_bayar',
                'ol.status_order_layanan',
            ])
            ->distinct();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('ol.kode_transaksi', 'like', "%{$search}%")
                    ->orWhere('ol.status_order_layanan', 'like', "%{$search}%");
            });
        }

        $rows = $query->orderByDesc('ol.tanggal_order')->paginate(10)->withQueryString();

        $summary = [
            'total' => DB::table('order_layanan as ol')
                ->join('order_layanan_detail as old', 'old.order_layanan_id', '=', 'ol.id')
                ->join('layanan as l', 'l.id', '=', 'old.layanan_id')
                ->join('kategori_layanan as kl', 'kl.id', '=', 'l.kategori_layanan_id')
                ->whereRaw('LOWER(kl.nama_kategori) NOT LIKE ?', ['%pemeriksaan%'])
                ->distinct('ol.id')
                ->count('ol.id'),

            'hari_ini' => DB::table('order_layanan as ol')
                ->join('order_layanan_detail as old', 'old.order_layanan_id', '=', 'ol.id')
                ->join('layanan as l', 'l.id', '=', 'old.layanan_id')
                ->join('kategori_layanan as kl', 'kl.id', '=', 'l.kategori_layanan_id')
                ->whereRaw('LOWER(kl.nama_kategori) NOT LIKE ?', ['%pemeriksaan%'])
                ->whereDate('ol.tanggal_order', today())
                ->distinct('ol.id')
                ->count('ol.id'),

            'pendapatan' => DB::table('order_layanan as ol')
                ->join('order_layanan_detail as old', 'old.order_layanan_id', '=', 'ol.id')
                ->join('layanan as l', 'l.id', '=', 'old.layanan_id')
                ->join('kategori_layanan as kl', 'kl.id', '=', 'l.kategori_layanan_id')
                ->whereRaw('LOWER(kl.nama_kategori) NOT LIKE ?', ['%pemeriksaan%'])
                ->where('ol.status_order_layanan', 'Sudah Bayar')
                ->distinct()
                ->sum('ol.total_bayar'),
        ];

        $chartData = $this->buildLayananChart($filter);

        return view('kasir.transaksi-layanan-insight.index', compact(
            'rows',
            'search',
            'summary',
            'chartData',
            'filter'
        ));
    }

    public function chartPembayaran(Request $request)
    {
        return response()->json(
            $this->buildPembayaranChart($this->normalizeChartFilter($request->get('filter', 'bulanan')))
        );
    }

    public function chartObat(Request $request)
    {
        return response()->json(
            $this->buildObatChart($this->normalizeChartFilter($request->get('filter', 'bulanan')))
        );
    }

    public function chartLayanan(Request $request)
    {
        return response()->json(
            $this->buildLayananChart($this->normalizeChartFilter($request->get('filter', 'bulanan')))
        );
    }

    private function normalizeChartFilter(?string $filter): string
    {
        return in_array($filter, ['harian', 'mingguan', 'bulanan', 'tahunan'], true)
            ? $filter
            : 'bulanan';
    }

    private function getDateRange(string $filter): array
    {
        $now = now();

        return match ($filter) {
            'harian' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'day'],
            'mingguan' => [$now->copy()->subWeeks(11)->startOfWeek(Carbon::MONDAY), $now->copy()->endOfWeek(Carbon::SUNDAY), 'week'],
            'tahunan' => [$now->copy()->subYears(4)->startOfYear(), $now->copy()->endOfYear(), 'year'],
            default => [$now->copy()->startOfYear(), $now->copy()->endOfYear(), 'month'],
        };
    }

    private function buildBuckets(string $filter): array
    {
        [$start, $end, $bucketType] = $this->getDateRange($filter);

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

        return [$start, $end, $bucketType, $keys, $labels];
    }

    private function resolveBucketKey(Carbon $date, string $bucketType): string
    {
        return match ($bucketType) {
            'day' => $date->format('Y-m-d'),
            'week' => $date->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'),
            'year' => $date->format('Y'),
            default => $date->format('Y-m'),
        };
    }

    private function buildPembayaranChart(string $filter): array
    {
        [$start, $end, $bucketType, $keys, $labels] = $this->buildBuckets($filter);

        $jumlahMap = array_fill_keys($keys, 0);
        $pendapatanMap = array_fill_keys($keys, 0);

        $rows = DB::table('pembayaran')
            ->select('tanggal_pembayaran', 'total_setelah_diskon', 'status')
            ->whereDate('tanggal_pembayaran', '>=', $start->toDateString())
            ->whereDate('tanggal_pembayaran', '<=', $end->toDateString())
            ->get();

        foreach ($rows as $row) {
            $date = Carbon::parse($row->tanggal_pembayaran);
            $key = $this->resolveBucketKey($date, $bucketType);

            if (!array_key_exists($key, $jumlahMap)) {
                continue;
            }

            $jumlahMap[$key]++;

            if ($row->status === 'Sudah Bayar') {
                $pendapatanMap[$key] += (float) $row->total_setelah_diskon;
            }
        }

        return [
            'filter' => $filter,
            'labels' => $labels,
            'range_text' => $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y'),
            'jumlah' => array_values($jumlahMap),
            'pendapatan' => array_values($pendapatanMap),
        ];
    }

    private function buildObatChart(string $filter): array
    {
        [$start, $end, $bucketType, $keys, $labels] = $this->buildBuckets($filter);

        $jumlahMap = array_fill_keys($keys, 0);
        $pendapatanMap = array_fill_keys($keys, 0);

        $rows = DB::table('penjualan_obat')
            ->select('tanggal_transaksi', 'total_setelah_diskon', 'status')
            ->whereDate('tanggal_transaksi', '>=', $start->toDateString())
            ->whereDate('tanggal_transaksi', '<=', $end->toDateString())
            ->get();

        foreach ($rows as $row) {
            $date = Carbon::parse($row->tanggal_transaksi);
            $key = $this->resolveBucketKey($date, $bucketType);

            if (!array_key_exists($key, $jumlahMap)) {
                continue;
            }

            $jumlahMap[$key]++;

            if ($row->status === 'Sudah Bayar') {
                $pendapatanMap[$key] += (float) $row->total_setelah_diskon;
            }
        }

        return [
            'filter' => $filter,
            'labels' => $labels,
            'range_text' => $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y'),
            'jumlah' => array_values($jumlahMap),
            'pendapatan' => array_values($pendapatanMap),
        ];
    }

    private function buildLayananChart(string $filter): array
    {
        [$start, $end, $bucketType, $keys, $labels] = $this->buildBuckets($filter);

        $jumlahMap = array_fill_keys($keys, 0);
        $pendapatanMap = array_fill_keys($keys, 0);

        $rows = DB::table('order_layanan as ol')
            ->join('order_layanan_detail as old', 'old.order_layanan_id', '=', 'ol.id')
            ->join('layanan as l', 'l.id', '=', 'old.layanan_id')
            ->join('kategori_layanan as kl', 'kl.id', '=', 'l.kategori_layanan_id')
            ->whereRaw('LOWER(kl.nama_kategori) NOT LIKE ?', ['%pemeriksaan%'])
            ->whereDate('ol.tanggal_order', '>=', $start->toDateString())
            ->whereDate('ol.tanggal_order', '<=', $end->toDateString())
            ->select('ol.id', 'ol.tanggal_order', 'ol.total_bayar', 'ol.status_order_layanan')
            ->distinct()
            ->get();

        foreach ($rows as $row) {
            $date = Carbon::parse($row->tanggal_order);
            $key = $this->resolveBucketKey($date, $bucketType);

            if (!array_key_exists($key, $jumlahMap)) {
                continue;
            }

            $jumlahMap[$key]++;

            if ($row->status_order_layanan === 'Sudah Bayar') {
                $pendapatanMap[$key] += (float) $row->total_bayar;
            }
        }

        return [
            'filter' => $filter,
            'labels' => $labels,
            'range_text' => $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y'),
            'jumlah' => array_values($jumlahMap),
            'pendapatan' => array_values($pendapatanMap),
        ];
    }
}
