<?php

/**
 * ============================================================
 *  EKSTRAKSI DATA TINDAKAN - SISTEM INFORMASI ROYAL KLINIK
 * ============================================================
 * Menarik semua data tindakan/layanan sejak sistem berjalan
 * (17 Oktober 2025) sampai hari ini (22 September 2026).
 *
 * Output:
 *   - storage/app/exports/tindakan_lengkap_YYYY-MM-DD.csv
 *   - storage/app/exports/ringkasan_tindakan_YYYY-MM-DD.md
 *
 * Jalankan:
 *   "C:/laragon/bin/php/php-8.3.11-Win32-vs16-x64/php.exe" extract-tindakan.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$outDir = storage_path('app/exports');
if (!is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}
$today = now()->format('Y-m-d');
$csvPath = $outDir . "/tindakan_lengkap_{$today}.csv";
$mdPath  = $outDir . "/ringkasan_tindakan_{$today}.md";

echo "=== EKSTRAKSI DATA TINDAKAN ===\n";
echo "Database: " . config('database.connections.' . config('database.default') . '.database') . "\n\n";

/* ============================================================
 * BAGIAN 1: Order Layanan (transaksi tindakan di kasir)
 * ============================================================ */
$orderLayanan = DB::table('order_layanan as ol')
    ->join('pasien as p', 'p.id', '=', 'ol.pasien_id')
    ->leftJoin('metode_pembayaran as mp', 'mp.id', '=', 'ol.metode_pembayaran_id')
    ->select(
        'ol.id', 'ol.kode_transaksi', 'ol.tanggal_order', 'ol.tanggal_pembayaran',
        'p.no_emr', 'p.nama_pasien',
        'ol.subtotal', 'ol.diskon_tipe', 'ol.diskon_nilai', 'ol.potongan_pesanan',
        'ol.total_bayar', 'ol.status_order_layanan', 'mp.nama_metode',
        'ol.created_at'
    )
    ->orderBy('ol.created_at')
    ->get();

/* ============================================================
 * BAGIAN 2: Detail item tindakan per order
 * (leftJoin agar baris dengan layanan_id NULL/tidak ada di
 *  master tetap ikut terekstrak - tidak ada data yang hilang)
 * ============================================================ */
$orderDetail = DB::table('order_layanan_detail as old')
    ->join('order_layanan as ol', 'ol.id', '=', 'old.order_layanan_id')
    ->join('pasien as p', 'p.id', '=', 'ol.pasien_id')
    ->leftJoin('layanan as l', 'l.id', '=', 'old.layanan_id')
    ->leftJoin('kategori_layanan as kl', 'kl.id', '=', 'l.kategori_layanan_id')
    ->select(
        'ol.kode_transaksi', 'ol.tanggal_order', 'ol.status_order_layanan',
        'p.no_emr', 'p.nama_pasien',
        DB::raw("COALESCE(l.nama_layanan, CONCAT('[layanan tidak ditemukan: id=', COALESCE(old.layanan_id, 'NULL'), ']')) as nama_layanan"),
        'kl.nama_kategori', 'old.qty', 'old.harga_satuan',
        'old.total_harga_item', 'old.created_at'
    )
    ->orderBy('old.created_at')
    ->get();

/* ============================================================
 * BAGIAN 3: Penjualan Layanan (tindakan terjual langsung)
 * ============================================================ */
$penjualan = DB::table('penjualan_layanan as pl')
    ->join('pasien as p', 'p.id', '=', 'pl.pasien_id')
    ->join('layanan as l', 'l.id', '=', 'pl.layanan_id')
    ->leftJoin('kategori_layanan as kl', 'kl.id', '=', 'pl.kategori_layanan_id')
    ->leftJoin('metode_pembayaran as mp', 'mp.id', '=', 'pl.metode_pembayaran_id')
    ->select(
        'pl.id', 'pl.kode_transaksi', 'pl.tanggal_transaksi',
        'p.no_emr', 'p.nama_pasien',
        'l.nama_layanan', 'kl.nama_kategori', 'pl.jumlah', 'pl.sub_total',
        'pl.diskon_tipe', 'pl.diskon_nilai', 'pl.total_setelah_diskon',
        'pl.status', 'mp.nama_metode', 'pl.created_at'
    )
    ->orderBy('pl.created_at')
    ->get();

/* ============================================================
 * BAGIAN 4: Tindakan per Kunjungan (kunjungan_layanan)
 * ============================================================ */
$kunjunganLayanan = DB::table('kunjungan_layanan as kln')
    ->join('kunjungan as k', 'k.id', '=', 'kln.kunjungan_id')
    ->join('pasien as p', 'p.id', '=', 'k.pasien_id')
    ->leftJoin('layanan as l', 'l.id', '=', 'kln.layanan_id')
    ->leftJoin('poli as po', 'po.id', '=', 'k.poli_id')
    ->leftJoin('dokter as d', 'd.id', '=', 'k.dokter_id')
    ->select(
        'k.id as kunjungan_id', 'k.tanggal_kunjungan', 'k.keluhan_awal', 'k.status as status_kunjungan',
        'p.no_emr', 'p.nama_pasien',
        'po.nama_poli', 'd.nama_dokter',
        DB::raw("COALESCE(l.nama_layanan, CONCAT('[layanan tidak ditemukan: id=', COALESCE(kln.layanan_id, 'NULL'), ']')) as nama_layanan"),
        'kln.jumlah', 'kln.created_at'
    )
    ->orderBy('k.tanggal_kunjungan')
    ->get();

/* ============================================================
 * BAGIAN 5: Tindakan dari kasir global (pembayaran_detail)
 * ============================================================ */
$pembayaranDetail = DB::table('pembayaran_detail as pd')
    ->join('pembayaran as pb', 'pb.id', '=', 'pd.pembayaran_id')
    ->leftJoin('layanan as l', 'l.id', '=', 'pd.layanan_id')
    ->select(
        'pb.kode_transaksi', 'pb.tanggal_pembayaran', 'pb.status as status_pembayaran',
        'pd.nama_item', 'l.nama_layanan', 'pd.qty', 'pd.harga', 'pd.subtotal',
        'pd.created_at'
    )
    ->whereNotNull('pd.layanan_id')
    ->orderBy('pd.created_at')
    ->get();

/* ============================================================
 * TULIS CSV GABUNGAN
 * ============================================================ */
$fh = fopen($csvPath, 'w');
fprintf($fh, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM (Excel-friendly)
fputcsv($fh, ['SUMBER_DATA', 'TANGGAL', 'KODE_TRANSAKSI', 'NO_EMR', 'NAMA_PASIEN',
    'NAMA_TINDAKAN', 'KATEGORI', 'POLI', 'DOKTER', 'QTY', 'NILAI', 'STATUS', 'CATATAN']);

foreach ($orderDetail as $r) {
    fputcsv($fh, ['ORDER_LAYANAN', $r->tanggal_order, $r->kode_transaksi, $r->no_emr, $r->nama_pasien,
        $r->nama_layanan, $r->nama_kategori, '', '', $r->qty, $r->total_harga_item,
        $r->status_order_layanan, '']);
}
foreach ($penjualan as $r) {
    fputcsv($fh, ['PENJUALAN_LAYANAN', $r->tanggal_transaksi, $r->kode_transaksi, $r->no_emr, $r->nama_pasien,
        $r->nama_layanan, $r->nama_kategori, '', '', $r->jumlah, $r->total_setelah_diskon,
        $r->status, '']);
}
foreach ($kunjunganLayanan as $r) {
    fputcsv($fh, ['KUNJUNGAN_LAYANAN', $r->tanggal_kunjungan, 'KJ-' . $r->kunjungan_id, $r->no_emr, $r->nama_pasien,
        $r->nama_layanan, '', $r->nama_poli, $r->nama_dokter, $r->jumlah, '',
        $r->status_kunjungan, 'keluhan: ' . $r->keluhan_awal]);
}
foreach ($pembayaranDetail as $r) {
    fputcsv($fh, ['PEMBAYARAN_DETAIL', $r->tanggal_pembayaran, $r->kode_transaksi, '', '',
        $r->nama_layanan ?: $r->nama_item, '', '', '', $r->qty, $r->subtotal,
        $r->status_pembayaran, '']);
}
fclose($fh);

/* ============================================================
 * RINGKASAN STATISTIK
 * ============================================================ */
$firstDate = DB::table('kunjungan')->min('tanggal_kunjungan') ?? DB::table('kunjungan')->value('created_at');

$stats = [
    'total_order'        => $orderLayanan->count(),
    'order_sudah_bayar'  => $orderLayanan->where('status_order_layanan', 'Sudah Bayar')->count(),
    'order_belum_bayar'  => $orderLayanan->where('status_order_layanan', 'Belum Bayar')->count(),
    'order_nilai'        => $orderLayanan->sum('total_bayar'),
    'total_penjualan'    => $penjualan->count(),
    'penjualan_sudah'    => $penjualan->where('status', 'Sudah Bayar')->count(),
    'penjualan_belum'    => $penjualan->where('status', 'Belum Bayar')->count(),
    'penjualan_nilai'    => $penjualan->sum('total_setelah_diskon'),
    'total_kunjungan'    => $kunjunganLayanan->count(),
];

// Tindakan terpopuler (dari order detail + penjualan)
// Abaikan placeholder '[layanan tidak ditemukan]' agar tidak merusak peringkat
$populer = collect()
    ->merge($orderDetail->filter(fn($r) => !str_starts_with($r->nama_layanan, '[layanan tidak ditemukan'))
        ->map(fn($r) => ['nama' => $r->nama_layanan, 'qty' => (int) $r->qty]))
    ->merge($penjualan->map(fn($r) => ['nama' => $r->nama_layanan, 'qty' => (int) $r->jumlah]))
    ->groupBy('nama')
    ->map(fn($g) => $g->sum('qty'))
    ->sortDesc();

// Per tahun
$perTahun = $orderLayanan->groupBy(fn($r) => substr($r->created_at, 0, 4))
    ->map(fn($g, $th) => ['tahun' => $th, 'order' => $g->count(), 'nilai' => $g->sum('total_bayar')]);

// Per bulan (12 bulan terakhir)
$perBulan = $orderLayanan
    ->filter(fn($r) => $r->created_at >= now()->subMonths(12))
    ->groupBy(fn($r) => substr($r->created_at, 0, 7))
    ->map(fn($g, $bl) => ['bulan' => $bl, 'order' => $g->count(), 'nilai' => $g->sum('total_bayar')]);

/* ============================================================
 * TULIS RINGKASAN MD
 * ============================================================ */
$md = "# Ringkasan Data Tindakan — Sistem Informasi Royal Klinik\n\n";
$md .= "**Rentang data:** " . ($firstDate ?? '-') . " s/d " . $today . "\n";
$md .= "**Diekstrak:** " . now()->format('Y-m-d H:i:s') . "\n\n";

$md .= "## 1. Statistik Umum\n\n| Metrik | Nilai |\n|---|---:|\n";
$md .= "| Total order layanan | {$stats['total_order']} |\n";
$md .= "| — Sudah bayar | {$stats['order_sudah_bayar']} |\n";
$md .= "| — Belum bayar | {$stats['order_belum_bayar']} |\n";
$md .= "| Nilai total order | Rp " . number_format($stats['order_nilai'], 0, ',', '.') . " |\n";
$md .= "| Total transaksi penjualan layanan | {$stats['total_penjualan']} |\n";
$md .= "| — Sudah bayar | {$stats['penjualan_sudah']} |\n";
$md .= "| — Belum bayar | {$stats['penjualan_belum']} |\n";
$md .= "| Nilai total penjualan layanan | Rp " . number_format($stats['penjualan_nilai'], 0, ',', '.') . " |\n";
$md .= "| Baris tindakan per kunjungan | {$stats['total_kunjungan']} |\n\n";

$md .= "## 2. 15 Tindakan Terpopuler (berdasarkan qty)\n\n| # | Tindakan | Total Qty |\n|---|---|---:|\n";
$i = 0;
foreach ($populer->take(15) as $nama => $qty) {
    $md .= "| " . (++$i) . " | {$nama} | {$qty} |\n";
}

$md .= "\n## 3. Order Layanan per Tahun\n\n| Tahun | Jumlah Order | Nilai (Rp) |\n|---|---:|---:|\n";
foreach ($perTahun as $t) {
    $md .= "| {$t['tahun']} | {$t['order']} | " . number_format($t['nilai'], 0, ',', '.') . " |\n";
}

$md .= "\n## 4. Tren 12 Bulan Terakhir (Order Layanan)\n\n| Bulan | Order | Nilai (Rp) |\n|---|---:|---:|\n";
foreach ($perBulan as $t) {
    $md .= "| {$t['bulan']} | {$t['order']} | " . number_format($t['nilai'], 0, ',', '.') . " |\n";
}

file_put_contents($mdPath, $md);

/* ============================================================
 * OUTPUT KONSOL
 * ============================================================ */
echo "Data diekstrak:\n";
echo "  - Order layanan        : " . $orderLayanan->count() . " transaksi / " . $orderDetail->count() . " item\n";
echo "  - Penjualan layanan    : " . $penjualan->count() . " transaksi\n";
echo "  - Kunjungan-layanan    : " . $kunjunganLayanan->count() . " baris\n";
echo "  - Pembayaran detail    : " . $pembayaranDetail->count() . " baris\n\n";
echo "Output:\n  - {$csvPath}\n  - {$mdPath}\n";
