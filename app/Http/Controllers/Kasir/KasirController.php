<?php

namespace App\Http\Controllers\Kasir;

use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\Kasir;
use App\Models\MetodePembayaran;
use App\Models\Pembayaran;
use App\Models\User;
use Carbon\CarbonPeriod;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Laravel\Facades\Image;
use Yajra\DataTables\DataTables;

class KasirController extends Controller
{
    public function dashboard()
    {
        return view('kasir.dashboard');
    }

    public function index()
    {
        return view('kasir.pembayaran.kasir');
    }

    public function getDataTransaksiLayanan(Request $request)
    {
        // ====== SUBQUERY TRANSAKSI LAYANAN ======
        $subLayanan = DB::table('penjualan_layanan as pl')
            ->leftJoin('pasien', 'pasien.id', '=', 'pl.pasien_id')
            ->leftJoin('layanan', 'layanan.id', '=', 'pl.layanan_id')
            ->leftJoin('kategori_layanan', 'kategori_layanan.id', '=', 'pl.kategori_layanan_id')
            ->leftJoin('metode_pembayaran', 'metode_pembayaran.id', '=', 'pl.metode_pembayaran_id')
            ->selectRaw("
            pl.kode_transaksi,
            pasien.nama_pasien,

            -- nanti di-outer query digabung lagi
            GROUP_CONCAT(DISTINCT layanan.nama_layanan SEPARATOR ', ') AS nama_item,
            GROUP_CONCAT(DISTINCT kategori_layanan.nama_kategori SEPARATOR ', ') AS kategori_item,

            SUM(
                COALESCE(pl.total_setelah_diskon, pl.total_tagihan, 0)
            ) AS total_tagihan,

            MAX(metode_pembayaran.nama_metode) AS metode_pembayaran,
            MIN(pl.tanggal_transaksi) AS tanggal_transaksi,

            -- flag status numerik biar gampang di-merge
            CASE
                WHEN MIN(pl.status) = 'Sudah Bayar'
                     AND MAX(pl.status) = 'Sudah Bayar'
                THEN 1
                ELSE 0
            END AS status_flag,

            MAX(pl.bukti_pembayaran) AS bukti_pembayaran
        ")
            ->groupBy(
                'pl.kode_transaksi',
                'pasien.nama_pasien'
            );

        // ====== SUBQUERY TRANSAKSI OBAT ======
        $subObat = DB::table('penjualan_obat as po')
            ->leftJoin('pasien', 'pasien.id', '=', 'po.pasien_id')
            ->leftJoin('obat', 'obat.id', '=', 'po.obat_id')
            ->leftJoin('metode_pembayaran', 'metode_pembayaran.id', '=', 'po.metode_pembayaran_id')
            ->selectRaw("
            po.kode_transaksi,
            pasien.nama_pasien,

            GROUP_CONCAT(DISTINCT obat.nama_obat SEPARATOR ', ') AS nama_item,
            GROUP_CONCAT(DISTINCT 'Obat' SEPARATOR ', ') AS kategori_item,

            SUM(
                COALESCE(po.total_setelah_diskon, po.total_tagihan, 0)
            ) AS total_tagihan,

            MAX(metode_pembayaran.nama_metode) AS metode_pembayaran,
            MIN(po.tanggal_transaksi) AS tanggal_transaksi,

            CASE
                WHEN MIN(po.status) = 'Sudah Bayar'
                     AND MAX(po.status) = 'Sudah Bayar'
                THEN 1
                ELSE 0
            END AS status_flag,

            MAX(po.bukti_pembayaran) AS bukti_pembayaran
        ")
            ->groupBy(
                'po.kode_transaksi',
                'pasien.nama_pasien'
            );

        // ====== UNION + GROUP ULANG BERDASARKAN KODE TRANSAKSI ======
        $query = DB::query()
            ->fromSub(
                $subLayanan->unionAll($subObat),
                't'
            )
            ->selectRaw("
            t.kode_transaksi,
            t.nama_pasien,

            GROUP_CONCAT(DISTINCT t.nama_item SEPARATOR ', ') AS nama_layanan,
            GROUP_CONCAT(DISTINCT t.kategori_item SEPARATOR ', ') AS kategori_layanan,

            SUM(t.total_tagihan) AS total_tagihan,

            MAX(t.metode_pembayaran) AS metode_pembayaran,
            MIN(t.tanggal_transaksi) AS tanggal_transaksi,

            CASE
                WHEN MIN(t.status_flag) = 1 AND MAX(t.status_flag) = 1
                THEN 'Sudah Bayar'
                ELSE 'Belum Bayar'
            END AS status,

            MAX(t.bukti_pembayaran) AS bukti_pembayaran
        ")
            ->groupBy(
                't.kode_transaksi',
                't.nama_pasien'
            );

        // ====== DATATABLES ======
        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('total_tagihan', function ($row) {
                $n = (float) $row->total_tagihan;
                return $n > 0
                    ? 'Rp ' . number_format($n, 0, ',', '.')
                    : '-';
            })
            ->editColumn('tanggal_transaksi', function ($row) {
                if (!$row->tanggal_transaksi) return '-';

                return \Carbon\Carbon::parse($row->tanggal_transaksi)
                    ->locale('id')
                    ->translatedFormat('d F Y H:i');
            })
            ->addColumn('bukti_pembayaran', function ($row) {
                if (!$row->bukti_pembayaran) return '-';

                $url = asset('storage/' . $row->bukti_pembayaran);
                return '<a href="' . $url . '" target="_blank" class="text-sky-600 underline">Lihat</a>';
            })
            ->addColumn('action', function ($row) {
                $url = route('kasir.show.kwitansi.transaksi.layanan', [
                    'kodeTransaksi' => $row->kode_transaksi
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
        $hariIni = Carbon::today();

        $dataPembayaran = Pembayaran::with([
            'emr.kunjungan.pasien',
            'pembayaranDetail', // relasi ke pembayaran_detail
            'metodePembayaran',
        ])
            ->where('status', 'Belum Bayar')
            ->whereHas('emr.kunjungan', function ($q) use ($hariIni) {
                $q->whereDate('tanggal_kunjungan', $hariIni);
            })
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

            // ✅ SEMUA ITEM DARI pembayaran_detail
            ->addColumn('items', function ($p) {

                if ($p->details->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada item</span>';
                }

                $output = '<ul class="list-disc pl-4">';
                foreach ($p->details as $d) {
                    $output .= '<li>'
                        . e($d->nama_item)
                        . ' (x' . $d->qty . ') - Rp '
                        . number_format($d->subtotal, 0, ',', '.')
                        . '</li>';
                }
                $output .= '</ul>';

                return $output;
            })

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

            ->addColumn(
                'status',
                fn($p) =>
                $p->status ?? '-'
            )

            ->addColumn('action', function ($p) {

                $urlBayar = route('kasir.transaksi', [
                    'kode_transaksi' => $p->kode_transaksi
                ]);

                $urlDelete = route('kasir.pembayaran.delete', [
                    'id' => $p->id
                ]);

                return '
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
            'emr.resep.obat',
            'emr.kunjungan.layanan',
            'metodePembayaran',
        ])->where('status', 'Sudah Bayar')
            ->latest()
            ->get();

        return DataTables::of($dataPembayaran)
            ->addIndexColumn()
            ->addColumn('nama_pasien', function ($p) {
                return $p->emr?->kunjungan?->pasien?->nama_pasien ?? '-';
            })
            ->addColumn('tanggal_kunjungan', function ($p) {
                $tgl = $p->emr?->kunjungan?->tanggal_kunjungan ?? null;

                return $tgl ? Carbon::parse($tgl)->toIso8601String() : '-';
            })
            ->addColumn('no_antrian', function ($p) {
                return $p->emr?->kunjungan?->no_antrian ?? '-';
            })
            ->addColumn('nama_obat', function ($p) {
                $resep = $p->emr?->resep;
                if (!$resep || $resep->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
                $items = [];
                foreach ($resep->obat as $obat) {
                    $items[] = '<li>' . e($obat->nama_obat) . '</li>';
                }

                return '<ul class="list-disc pl-4">' . implode('', $items) . '</ul>';
            })
            ->addColumn('dosis', function ($p) {
                $resep = $p->emr?->resep;
                if (!$resep || $resep->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
                $items = [];
                foreach ($resep->obat as $obat) {
                    $val = $obat->pivot->dosis ?? null;
                    $val = is_numeric($val) ? number_format((float) $val, 2) . ' mg' : e($val ?? '-');
                    $items[] = '<li>' . $val . '</li>';
                }

                return '<ul class="list-disc pl-4">' . implode('', $items) . '</ul>';
            })
            ->addColumn('jumlah', function ($p) {
                $resep = $p->emr?->resep;
                if (!$resep || $resep->obat->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
                $items = [];
                foreach ($resep->obat as $obat) {
                    $qty = $obat->pivot->jumlah ?? null;
                    $items[] = '<li>' . (($qty !== null && $qty !== '') ? e($qty) . ' capsul' : '-') . '</li>';
                }

                return '<ul class="list-disc pl-4">' . implode('', $items) . '</ul>';
            })
            ->addColumn('nama_layanan', function ($p) {
                $layanan = $p->emr?->kunjungan?->layanan ?? collect();
                if ($layanan->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
                $items = [];
                foreach ($layanan as $l) {
                    $items[] = '<li>' . e($l->nama_layanan ?? '-') . '</li>';
                }

                return '<ul class="list-disc pl-4">' . implode('', $items) . '</ul>';
            })
            ->addColumn('jumlah_layanan', function ($p) {
                $layanan = $p->emr?->kunjungan?->layanan ?? collect();
                if ($layanan->isEmpty()) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
                $items = [];
                foreach ($layanan as $l) {
                    $items[] = '<li>' . e($l->pivot->jumlah ?? '-') . '</li>';
                }

                return '<ul class="list-disc pl-4">' . implode('', $items) . '</ul>';
            })
            ->addColumn('total_tagihan', function ($p) {
                return 'Rp ' . number_format((int) ($p->total_tagihan ?? 0), 0, ',', '.');
            })
            ->addColumn('metode_pembayaran', function ($p) {
                return $p->metodePembayaran->nama_metode ?? '-';
            })
            ->addColumn('bukti_pembayaran', function ($p) {
                if (!$p->bukti_pembayaran) {
                    return '<span class="text-gray-400 italic">Tidak ada</span>';
                }
                $url = asset('storage/' . $p->bukti_pembayaran);
                $urlEsc = e($url);

                $html = <<<HTML
<div class="flex flex-col items-center text-center space-y-2">
    <img src="{$urlEsc}" alt="Bukti Pembayaran"
         class="w-24 h-24 object-cover rounded-lg border border-gray-300 shadow-sm hover:scale-105 transition-transform duration-200 cursor-pointer"
         onclick="window.open('{$urlEsc}', '_blank')" />
    <a href="{$urlEsc}" target="_blank" class="text-sky-600 underline text-sm font-medium">
        Lihat Bukti Pembayaran
    </a>
</div>
HTML;

                return $html;
            })
            ->addColumn('status', function ($p) {
                return $p->status ?? '-';
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
            'emr.kunjungan.layanan',
            'emr.resep.obat',
        ])->where('kode_transaksi', $kodeTransaksi)
            ->firstOrFail();

        $dataMetodePembayaran = MetodePembayaran::all();

        return view('kasir.pembayaran.transaksi', compact('dataPembayaran', 'dataMetodePembayaran'));
    }

    public function transaksiCash(Request $request)
    {
        // normalize diskon_tipe '' -> null biar lulus nullable|in
        $request->merge([
            'diskon_tipe' => $request->diskon_tipe ?: null,
        ]);

        $request->validate([
            'id' => ['required', 'exists:pembayaran,id'],
            'uang_yang_diterima' => ['required', 'numeric', 'min:0'],
            'kembalian' => ['required', 'numeric'],
            'metode_pembayaran_id' => ['required', 'exists:metode_pembayaran,id'],
            'total_tagihan' => ['nullable', 'numeric', 'min:0'],
            'total_setelah_diskon' => ['nullable', 'numeric', 'min:0'],
            'diskon_tipe' => ['nullable', 'in:persen,nominal'],
            'diskon_nilai' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $pembayaranId = $request->id;
            $pembayaran = null;

            Log::info('=== TRANSAKSI CASH START ===', [
                'pembayaran_id' => $pembayaranId,
                'timestamp' => now()->toDateTimeString(),
            ]);

            DB::transaction(function () use ($request, $pembayaranId, &$pembayaran) {
                $pemb = Pembayaran::with(['emr.resep.obat', 'emr.kunjungan.pasien.user'])
                    ->lockForUpdate()
                    ->findOrFail($pembayaranId);

                // total awal ambil dari DB
                $totalAwal = (float) ($pemb->total_tagihan ?? 0);

                if ($totalAwal <= 0 && $request->filled('total_tagihan')) {
                    $totalAwal = (float) $request->total_tagihan;
                }

                if ($totalAwal <= 0) {
                    throw ValidationException::withMessages([
                        'total_tagihan' => 'Total tagihan tidak valid.',
                    ]);
                }

                // hitung diskon di server
                $diskonTipe = $request->diskon_tipe ?: null;
                $diskonNilai = (float) ($request->diskon_nilai ?? 0);

                $potongan = 0.0;
                if ($diskonTipe === 'persen' && $diskonNilai > 0) {
                    $potongan = $totalAwal * ($diskonNilai / 100);
                } elseif ($diskonTipe === 'nominal' && $diskonNilai > 0) {
                    $potongan = $diskonNilai;
                }

                if ($potongan > $totalAwal) {
                    $potongan = $totalAwal;
                }

                $totalSetelahDiskon = $totalAwal - $potongan;

                // validasi uang diterima >= total setelah diskon
                $uangDiterima = (float) $request->uang_yang_diterima;
                if ($uangDiterima < $totalSetelahDiskon) {
                    throw ValidationException::withMessages([
                        'uang_yang_diterima' => 'Nominal uang yang diterima belum cukup.',
                    ]);
                }

                // hitung kembalian di server
                $kembalian = $uangDiterima - $totalSetelahDiskon;

                Log::info('Payment calculation', [
                    'total_awal' => $totalAwal,
                    'diskon_tipe' => $diskonTipe,
                    'diskon_nilai' => $diskonNilai,
                    'potongan' => $potongan,
                    'total_setelah_diskon' => $totalSetelahDiskon,
                    'uang_diterima' => $uangDiterima,
                    'kembalian' => $kembalian,
                ]);

                // update pembayaran
                $pemb->update([
                    'total_tagihan' => $totalAwal,
                    'diskon_tipe' => $diskonTipe,
                    'diskon_nilai' => $diskonNilai,
                    'total_setelah_diskon' => $totalSetelahDiskon,
                    'uang_yang_diterima' => $uangDiterima,
                    'kembalian' => $kembalian,
                    'tanggal_pembayaran' => now(),
                    'status' => 'Sudah Bayar',
                    'metode_pembayaran_id' => $request->metode_pembayaran_id,
                ]);

                Log::info('Pembayaran updated to Sudah Bayar', [
                    'pembayaran_id' => $pemb->id,
                    'kode_transaksi' => $pemb->kode_transaksi,
                ]);

                $pembayaran = $pemb->fresh([
                    'emr.resep.obat',
                    'emr.kunjungan.pasien.user',
                    'emr.kunjungan.layanan',
                    'metodePembayaran',
                ]);

                // ✅✅✅ KIRIM NOTIF SETELAH COMMIT ✅✅✅
                DB::afterCommit(function () use ($pembayaranId) {
                    try {
                        Log::info('🔔 Preparing to send notification for pembayaran cash', [
                            'pembayaran_id' => $pembayaranId,
                        ]);

                        $pembFresh = Pembayaran::with(['emr.kunjungan.pasien.user'])->find($pembayaranId);

                        if ($pembFresh) {
                            Log::info('Pembayaran loaded for notification', [
                                'pembayaran_id' => $pembFresh->id,
                                'pasien_id' => $pembFresh->emr->kunjungan->pasien_id ?? null,
                                'pasien_user_id' => $pembFresh->emr->kunjungan->pasien->user_id ?? null,
                            ]);

                            NotificationHelper::kirimNotifikasiPembayaranSelesai($pembFresh);

                            Log::info('✅ Notification helper called successfully');
                        } else {
                            Log::warning('⚠️ Pembayaran not found for notification', [
                                'pembayaran_id' => $pembayaranId,
                            ]);
                        }
                    } catch (\Throwable $e) {
                        Log::error('❌ Error sending notification for pembayaran cash', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'pembayaran_id' => $pembayaranId,
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
                'message' => 'Uang Kembalian Rp' . number_format($pembayaran->kembalian, 0, ',', '.') . '. Terimakasih 😊😊😊',
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
            throw $e;
        }
    }

    public function transaksiTransfer(Request $request)
    {
        // normalize diskon_tipe '' -> null
        $request->merge([
            'diskon_tipe' => $request->diskon_tipe ?: null,
        ]);

        $request->validate([
            'id' => ['required', 'exists:pembayaran,id'],
            'bukti_pembayaran' => ['required', 'file', 'mimes:jpeg,jpg,png,gif,webp,svg,jfif', 'max:5120'],
            'metode_pembayaran_id' => ['required', 'exists:metode_pembayaran,id'],
            'total_tagihan' => ['nullable', 'numeric', 'min:0'],
            'total_setelah_diskon' => ['nullable', 'numeric', 'min:0'],
            'diskon_tipe' => ['nullable', 'in:persen,nominal'],
            'diskon_nilai' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $pembayaranId = $request->id;
            $pembayaran = null;

            Log::info('=== TRANSAKSI TRANSFER START ===', [
                'pembayaran_id' => $pembayaranId,
                'timestamp' => now()->toDateTimeString(),
            ]);

            DB::transaction(function () use ($request, $pembayaranId, &$pembayaran) {
                $pemb = Pembayaran::with(['emr.resep.obat', 'emr.kunjungan.pasien.user'])
                    ->lockForUpdate()
                    ->findOrFail($pembayaranId);

                // total awal dari DB
                $totalAwal = (float) ($pemb->total_tagihan ?? 0);
                if ($totalAwal <= 0 && $request->filled('total_tagihan')) {
                    $totalAwal = (float) $request->total_tagihan;
                }

                if ($totalAwal <= 0) {
                    throw ValidationException::withMessages([
                        'total_tagihan' => 'Total tagihan tidak valid.',
                    ]);
                }

                // hitung diskon di server
                $diskonTipe = $request->diskon_tipe ?: null;
                $diskonNilai = (float) ($request->diskon_nilai ?? 0);

                $potongan = 0.0;
                if ($diskonTipe === 'persen' && $diskonNilai > 0) {
                    $potongan = $totalAwal * ($diskonNilai / 100);
                } elseif ($diskonTipe === 'nominal' && $diskonNilai > 0) {
                    $potongan = $diskonNilai;
                }

                if ($potongan > $totalAwal) {
                    $potongan = $totalAwal;
                }

                $totalSetelahDiskon = $totalAwal - $potongan;

                if ($totalSetelahDiskon <= 0) {
                    throw ValidationException::withMessages([
                        'total_setelah_diskon' => 'Total setelah diskon tidak valid.',
                    ]);
                }

                Log::info('Payment calculation for transfer', [
                    'total_awal' => $totalAwal,
                    'diskon_tipe' => $diskonTipe,
                    'diskon_nilai' => $diskonNilai,
                    'total_setelah_diskon' => $totalSetelahDiskon,
                ]);

                // upload bukti transfer
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
                        Storage::disk('public')->put($path, (string) $image->encodeByExtension($ext, quality: 80));
                    }
                    $fotoPath = $path;

                    Log::info('Bukti transfer uploaded', [
                        'path' => $fotoPath,
                    ]);
                }

                // untuk transfer, uang_yang_diterima = total setelah diskon, kembalian = 0
                $pemb->update([
                    'total_tagihan' => $totalAwal,
                    'diskon_tipe' => $diskonTipe,
                    'diskon_nilai' => $diskonNilai,
                    'total_setelah_diskon' => $totalSetelahDiskon,
                    'bukti_pembayaran' => $fotoPath,
                    'uang_yang_diterima' => $totalSetelahDiskon,
                    'kembalian' => 0,
                    'tanggal_pembayaran' => now(),
                    'status' => 'Sudah Bayar',
                    'metode_pembayaran_id' => $request->metode_pembayaran_id,
                ]);

                Log::info('Pembayaran updated to Sudah Bayar (Transfer)', [
                    'pembayaran_id' => $pemb->id,
                    'kode_transaksi' => $pemb->kode_transaksi,
                ]);

                $pembayaran = $pemb->fresh([
                    'emr.resep.obat',
                    'emr.kunjungan.pasien.user',
                    'emr.kunjungan.layanan',
                    'metodePembayaran',
                ]);

                // ✅✅✅ KIRIM NOTIF SETELAH COMMIT ✅✅✅
                DB::afterCommit(function () use ($pembayaranId) {
                    try {
                        Log::info('🔔 Preparing to send notification for pembayaran transfer', [
                            'pembayaran_id' => $pembayaranId,
                        ]);

                        $pembFresh = Pembayaran::with(['emr.kunjungan.pasien.user'])->find($pembayaranId);

                        if ($pembFresh) {
                            Log::info('Pembayaran loaded for notification', [
                                'pembayaran_id' => $pembFresh->id,
                                'pasien_id' => $pembFresh->emr->kunjungan->pasien_id ?? null,
                                'pasien_user_id' => $pembFresh->emr->kunjungan->pasien->user_id ?? null,
                            ]);

                            NotificationHelper::kirimNotifikasiPembayaranSelesai($pembFresh);

                            Log::info('✅ Notification helper called successfully');
                        } else {
                            Log::warning('⚠️ Pembayaran not found for notification', [
                                'pembayaran_id' => $pembayaranId,
                            ]);
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
                    number_format($pembayaran->uang_yang_diterima, 0, ',', '.') . '. Terimakasih 😊😊😊',
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
            throw $e;
        }
    }

    public function showKwitansi($kodeTransaksi)
    {
        $dataPembayaran = Pembayaran::with([
            'emr.kunjungan.pasien',
            'emr.resep.obat',
            'emr.kunjungan.layanan',
            'metodePembayaran',
        ])->where('kode_transaksi', $kodeTransaksi)->firstOrFail();

        $resep = optional($dataPembayaran->emr)->resep;

        if ($resep instanceof Collection) {
            $obatCollection = $resep->flatMap(function ($item) {
                return $item->obat ?? collect();
            });
        } elseif ($resep) {
            $obatCollection = $resep->obat ?? collect();
        } else {
            $obatCollection = collect();
        }

        $totalObat = $obatCollection->sum(function ($obat) {
            $jumlah = $obat->pivot->jumlah ?? 0;
            $harga  = $obat->total_harga ?? 0;

            return $jumlah * $harga;
        });

        $layananCollection = optional(optional($dataPembayaran->emr)->kunjungan)->layanan ?? collect();

        if (!$layananCollection instanceof Collection) {
            $layananCollection = $layananCollection
                ? collect([$layananCollection])
                : collect();
        }

        $totalLayanan = $layananCollection->sum(function ($layanan) {
            $jumlah = $layanan->pivot->jumlah ?? 0;
            $harga  = $layanan->harga_setelah_diskon ?? 0;

            return $jumlah * $harga;
        });

        $grandTotal = $totalObat + $totalLayanan;

        $namaPT = 'Royal Klinik';

        return view('kasir.pembayaran.kwitansi', compact(
            'dataPembayaran',
            'totalObat',
            'totalLayanan',
            'grandTotal',
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
        } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            return response()->json([
                'message' => 'Ukuran file terlalu besar! Maksimal 5 MB.',
            ], 413);
        } catch (\Illuminate\Validation\ValidationException $e) {
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
