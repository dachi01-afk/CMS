<?php

/**
 * ============================================================
 *  EXPORT EXCEL - DATA TINDAKAN PASIEN
 *  Sistem Informasi Klinik Royal Klinik
 * ============================================================
 * Mengambil semua data tindakan/layanan yang diberikan kepada
 * pasien (4 sumber) dan menuliskannya ke file .xlsx rapi.
 *
 * CATATAN PENTING: SEMUA kolom tanggal ditulis sebagai STRING
 * eksplisit (bukan date/datetime) sesuai permintaan.
 *
 * Jalankan:
 *   C:/laragon/bin/php/php-8.3.11-Win32-vs16-x64/php.exe export-tindakan-excel.php
 */

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

echo "=== EXPORT EXCEL DATA TINDAKAN ===\n";
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
 * BAGIAN 2: Penjualan Layanan (tindakan terjual langsung)
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
 * BAGIAN 3: Tindakan per Kunjungan (kunjungan_layanan)
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
 * BAGIAN 4: Tindakan dari kasir global (pembayaran_detail)
 * ============================================================ */
$pembayaranDetail = DB::table('pembayaran_detail as pd')
    ->join('pembayaran as pb', 'pb.id', '=', 'pd.pembayaran_id')
    ->leftJoin('layanan as l', 'l.id', '=', 'pd.layanan_id')
    ->select(
        'pb.kode_transaksi',
        'pb.tanggal_pembayaran',
        DB::raw('pd.created_at as pd_created_at'),
        'pb.status as status_pembayaran',
        'pd.nama_item', 'l.nama_layanan', 'pd.qty', 'pd.harga', 'pd.subtotal'
    )
    ->whereNotNull('pd.layanan_id')
    ->orderBy('pd.created_at')
    ->get();

echo "Data terkumpul:\n";
echo "  - Order layanan     : {$orderLayanan->count()} transaksi / " . $orderDetail->count() . " item\n";
echo "  - Penjualan layanan : {$penjualan->count()} transaksi\n";
echo "  - Kunjungan-layanan : {$kunjunganLayanan->count()} baris\n";
echo "  - Pembayaran detail : {$pembayaranDetail->count()} baris\n\n";

/* ============================================================
 * SUSUN BARIS GABUNGAN (urut kronologis)
 * ============================================================ */
$rows = collect();

foreach ($orderDetail as $r) {
    $rows->push([
        'sumber'      => 'ORDER_LAYANAN',
        'tanggal'     => (string) $r->tanggal_order,
        'kode'        => (string) $r->kode_transaksi,
        'no_emr'      => (string) ($r->no_emr ?? ''),
        'pasien'      => (string) ($r->nama_pasien ?? ''),
        'tindakan'    => (string) $r->nama_layanan,
        'kategori'    => (string) ($r->nama_kategori ?? ''),
        'poli'        => '',
        'dokter'      => '',
        'qty'         => (int) $r->qty,
        'nilai'       => (float) $r->total_harga_item,
        'status'      => (string) ($r->status_order_layanan ?? ''),
        'catatan'     => '',
    ]);
}

foreach ($penjualan as $r) {
    $rows->push([
        'sumber'      => 'PENJUALAN_LAYANAN',
        'tanggal'     => (string) $r->tanggal_transaksi,
        'kode'        => (string) $r->kode_transaksi,
        'no_emr'      => (string) ($r->no_emr ?? ''),
        'pasien'      => (string) ($r->nama_pasien ?? ''),
        'tindakan'    => (string) $r->nama_layanan,
        'kategori'    => (string) ($r->nama_kategori ?? ''),
        'poli'        => '',
        'dokter'      => '',
        'qty'         => (int) $r->jumlah,
        'nilai'       => (float) $r->total_setelah_diskon,
        'status'      => (string) ($r->status ?? ''),
        'catatan'     => '',
    ]);
}

foreach ($kunjunganLayanan as $r) {
    $rows->push([
        'sumber'      => 'KUNJUNGAN_LAYANAN',
        'tanggal'     => (string) $r->tanggal_kunjungan,
        'kode'        => 'KJ-' . $r->kunjungan_id,
        'no_emr'      => (string) ($r->no_emr ?? ''),
        'pasien'      => (string) ($r->nama_pasien ?? ''),
        'tindakan'    => (string) $r->nama_layanan,
        'kategori'    => '',
        'poli'        => (string) ($r->nama_poli ?? ''),
        'dokter'      => (string) ($r->nama_dokter ?? ''),
        'qty'         => (int) $r->jumlah,
        'nilai'       => null,
        'status'      => (string) ($r->status_kunjungan ?? ''),
        'catatan'     => $r->keluhan_awal ? 'keluhan: ' . $r->keluhan_awal : '',
    ]);
}

foreach ($pembayaranDetail as $r) {
    // Fallback: item yang belum dibayar tidak punya tanggal_pembayaran;
    // pakai tanggal pencatatan item agar tanggal tidak kosong.
    $tanggalPd = $r->tanggal_pembayaran ?? $r->pd_created_at;
    $catatanPd = $r->tanggal_pembayaran
        ? ''
        : 'tanggal pembayaran kosong (belum dibayar) — memakai tanggal pencatatan item';

    $rows->push([
        'sumber'      => 'PEMBAYARAN_DETAIL',
        'tanggal'     => (string) $tanggalPd,
        'kode'        => (string) $r->kode_transaksi,
        'no_emr'      => '',
        'pasien'      => '',
        'tindakan'    => (string) ($r->nama_layanan ?: $r->nama_item),
        'kategori'    => '',
        'poli'        => '',
        'dokter'      => '',
        'qty'         => (int) $r->qty,
        'nilai'       => (float) $r->subtotal,
        'status'      => (string) ($r->status_pembayaran ?? ''),
        'catatan'     => $catatanPd,
    ]);
}

// Urutkan kronologis berdasarkan tanggal (ISO sortable secara leksikal)
$rows = $rows->sortBy(fn ($row) => $row['tanggal'] !== '' ? $row['tanggal'] : '9999-12-31')->values();

/* ============================================================
 * STATISTIK RINGKASAN
 * ============================================================ */
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

$populer = collect()
    ->merge($orderDetail->filter(fn ($r) => ! str_starts_with($r->nama_layanan, '[layanan tidak ditemukan'))
        ->map(fn ($r) => ['nama' => $r->nama_layanan, 'qty' => (int) $r->qty]))
    ->merge($penjualan->map(fn ($r) => ['nama' => $r->nama_layanan, 'qty' => (int) $r->jumlah]))
    ->groupBy('nama')
    ->map(fn ($g) => $g->sum('qty'))
    ->sortDesc();

$firstDate = DB::table('kunjungan')->min('tanggal_kunjungan') ?? DB::table('kunjungan')->value('created_at');
$today = now()->format('Y-m-d');

/* ============================================================
 * BANGUN FILE EXCEL
 * ============================================================ */
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Data Tindakan');

$headers = [
    'SUMBER_DATA', 'TANGGAL', 'KODE_TRANSAKSI', 'NO_EMR', 'NAMA_PASIEN',
    'NAMA_TINDAKAN', 'KATEGORI', 'POLI', 'DOKTER', 'QTY', 'NILAI (Rp)', 'STATUS', 'CATATAN',
];
$colCount = count($headers);
$lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colCount); // 'M'

// --- Header ---
for ($c = 1; $c <= $colCount; $c++) {
    $sheet->getCellByColumnAndRow($c, 1)->setValueExplicit($headers[$c - 1], DataType::TYPE_STRING);
}

$sheet->getStyle('A1:' . $lastColLetter . '1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2F5597']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
]);

// --- Data (semua tanggal = STRING eksplisit) ---
$thinBorder = [
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D9D9D9']],
    ],
];

$rIdx = 2;
foreach ($rows as $row) {
    // Kolom teks (A-J, L, M) => string eksplisit
    $stringCols = [
        1 => $row['sumber'],
        2 => $row['tanggal'],      // <- TANGGAL sebagai STRING, bukan date
        3 => $row['kode'],
        4 => $row['no_emr'],
        5 => $row['pasien'],
        6 => $row['tindakan'],
        7 => $row['kategori'],
        8 => $row['poli'],
        9 => $row['dokter'],
        12 => $row['status'],
        13 => $row['catatan'],
    ];
    foreach ($stringCols as $c => $val) {
        $sheet->getCellByColumnAndRow($c, $rIdx)->setValueExplicit((string) $val, DataType::TYPE_STRING);
    }

    // Kolom angka
    $sheet->getCellByColumnAndRow(10, $rIdx)->setValueExplicit((int) $row['qty'], DataType::TYPE_NUMERIC);
    if ($row['nilai'] !== null) {
        $sheet->getCellByColumnAndRow(11, $rIdx)->setValueExplicit((float) $row['nilai'], DataType::TYPE_NUMERIC);
        $sheet->getStyleByColumnAndRow(11, $rIdx)->getNumberFormat()->setFormatCode('#,##0');
    }

    $rIdx++;
}

$lastDataRow = $rIdx - 1;

// Border halus untuk seluruh tabel
$sheet->getStyle('A1:' . $lastColLetter . $lastDataRow)->applyFromArray($thinBorder);

// QTY rata tengah
$sheet->getStyle('J2:J' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Lebar kolom
$widths = ['A' => 20, 'B' => 20, 'C' => 22, 'D' => 14, 'E' => 30, 'F' => 40, 'G' => 26, 'H' => 16, 'I' => 22, 'J' => 8, 'K' => 14, 'L' => 14, 'M' => 30];
foreach ($widths as $col => $w) {
    $sheet->getColumnDimension($col)->setWidth($w);
}

// Freeze header + autofilter
$sheet->freezePane('A2');
$sheet->setAutoFilter('A1:' . $lastColLetter . $lastDataRow);

/* ============================================================
 * SHEET 2: RINGKASAN
 * ============================================================ */
$rz = $spreadsheet->createSheet();
$rz->setTitle('Ringkasan');

$rz->setCellValueExplicit('A1', 'RINGKASAN DATA TINDAKAN — ROYAL KLINIK', DataType::TYPE_STRING);
$rz->getStyle('A1')->getFont()->setBold(true)->setSize(14);

$info = [
    ['Rentang data', (string) ($firstDate ?? '-') . ' s/d ' . $today],
    ['Diekstrak', now()->format('Y-m-d H:i:s')],
    ['Database', (string) config('database.connections.' . config('database.default') . '.database')],
    ['Total baris tindakan', (string) $rows->count()],
];
$r = 3;
$rz->setCellValueExplicit('A' . $r, 'INFORMASI', DataType::TYPE_STRING);
$rz->getStyle('A' . $r)->getFont()->setBold(true);
$r++;
foreach ($info as [$k, $v]) {
    $rz->getCellByColumnAndRow(1, $r)->setValueExplicit($k, DataType::TYPE_STRING);
    $rz->getCellByColumnAndRow(2, $r)->setValueExplicit($v, DataType::TYPE_STRING); // tanggal sebagai string
    $r++;
}

$r++;
$rz->setCellValueExplicit('A' . $r, 'STATISTIK UMUM', DataType::TYPE_STRING);
$rz->getStyle('A' . $r)->getFont()->setBold(true);
$r++;
$statLines = [
    ['Total order layanan', number_format($stats['total_order'], 0, ',', '.')],
    ['— Sudah bayar', number_format($stats['order_sudah_bayar'], 0, ',', '.')],
    ['— Belum bayar', number_format($stats['order_belum_bayar'], 0, ',', '.')],
    ['Nilai total order (Rp)', number_format($stats['order_nilai'], 0, ',', '.')],
    ['Total transaksi penjualan layanan', number_format($stats['total_penjualan'], 0, ',', '.')],
    ['— Sudah bayar', number_format($stats['penjualan_sudah'], 0, ',', '.')],
    ['— Belum bayar', number_format($stats['penjualan_belum'], 0, ',', '.')],
    ['Nilai total penjualan layanan (Rp)', number_format($stats['penjualan_nilai'], 0, ',', '.')],
    ['Baris tindakan per kunjungan', number_format($stats['total_kunjungan'], 0, ',', '.')],
];
foreach ($statLines as [$k, $v]) {
    $rz->getCellByColumnAndRow(1, $r)->setValueExplicit($k, DataType::TYPE_STRING);
    $rz->getCellByColumnAndRow(2, $r)->setValueExplicit($v, DataType::TYPE_STRING);
    $r++;
}

$r++;
$rz->setCellValueExplicit('A' . $r, '15 TINDAKAN TERPOPULER (QTY)', DataType::TYPE_STRING);
$rz->getStyle('A' . $r)->getFont()->setBold(true);
$r++;
$rz->getCellByColumnAndRow(1, $r)->setValueExplicit('Tindakan', DataType::TYPE_STRING);
$rz->getCellByColumnAndRow(2, $r)->setValueExplicit('Total Qty', DataType::TYPE_STRING);
$rz->getStyle('A' . $r . ':B' . $r)->getFont()->setBold(true);
$r++;
$i = 0;
foreach ($populer->take(15) as $nama => $qty) {
    $rz->getCellByColumnAndRow(1, $r)->setValueExplicit((++$i) . '. ' . $nama, DataType::TYPE_STRING);
    $rz->getCellByColumnAndRow(2, $r)->setValueExplicit((int) $qty, DataType::TYPE_NUMERIC);
    $r++;
}

$r++;
$rz->setCellValueExplicit('A' . $r, 'ORDER LAYANAN PER TAHUN', DataType::TYPE_STRING);
$rz->getStyle('A' . $r)->getFont()->setBold(true);
$r++;
$rz->getCellByColumnAndRow(1, $r)->setValueExplicit('Tahun', DataType::TYPE_STRING);
$rz->getCellByColumnAndRow(2, $r)->setValueExplicit('Jumlah Order', DataType::TYPE_STRING);
$rz->getCellByColumnAndRow(3, $r)->setValueExplicit('Nilai (Rp)', DataType::TYPE_STRING);
$rz->getStyle('A' . $r . ':C' . $r)->getFont()->setBold(true);
$r++;
$perTahun = $orderLayanan->groupBy(fn ($r0) => substr($r0->created_at, 0, 4))
    ->map(fn ($g, $th) => ['tahun' => $th, 'order' => $g->count(), 'nilai' => $g->sum('total_bayar')]);
foreach ($perTahun as $t) {
    $rz->getCellByColumnAndRow(1, $r)->setValueExplicit($t['tahun'], DataType::TYPE_STRING); // tahun sebagai string
    $rz->getCellByColumnAndRow(2, $r)->setValueExplicit($t['order'], DataType::TYPE_NUMERIC);
    $rz->getCellByColumnAndRow(3, $r)->setValueExplicit($t['nilai'], DataType::TYPE_NUMERIC);
    $rz->getStyleByColumnAndRow(3, $r)->getNumberFormat()->setFormatCode('#,##0');
    $r++;
}

$r++;
$rz->setCellValueExplicit('A' . $r, 'TREN 12 BULAN TERAKHIR (ORDER LAYANAN)', DataType::TYPE_STRING);
$rz->getStyle('A' . $r)->getFont()->setBold(true);
$r++;
$rz->getCellByColumnAndRow(1, $r)->setValueExplicit('Bulan', DataType::TYPE_STRING);
$rz->getCellByColumnAndRow(2, $r)->setValueExplicit('Order', DataType::TYPE_STRING);
$rz->getCellByColumnAndRow(3, $r)->setValueExplicit('Nilai (Rp)', DataType::TYPE_STRING);
$rz->getStyle('A' . $r . ':C' . $r)->getFont()->setBold(true);
$r++;
$perBulan = $orderLayanan
    ->filter(fn ($r0) => $r0->created_at >= now()->subMonths(12))
    ->groupBy(fn ($r0) => substr($r0->created_at, 0, 7))
    ->map(fn ($g, $bl) => ['bulan' => $bl, 'order' => $g->count(), 'nilai' => $g->sum('total_bayar')]);
foreach ($perBulan as $t) {
    $rz->getCellByColumnAndRow(1, $r)->setValueExplicit($t['bulan'], DataType::TYPE_STRING); // bulan (YYYY-MM) sebagai string
    $rz->getCellByColumnAndRow(2, $r)->setValueExplicit($t['order'], DataType::TYPE_NUMERIC);
    $rz->getCellByColumnAndRow(3, $r)->setValueExplicit($t['nilai'], DataType::TYPE_NUMERIC);
    $rz->getStyleByColumnAndRow(3, $r)->getNumberFormat()->setFormatCode('#,##0');
    $r++;
}

$rz->getColumnDimension('A')->setWidth(42);
$rz->getColumnDimension('B')->setWidth(24);
$rz->getColumnDimension('C')->setWidth(18);

/* ============================================================
 * SIMPAN
 * ============================================================ */
$outRoot = __DIR__ . '/Tindakan_Pasien_Royal_Klinik_' . $today . '.xlsx';
$outStorage = storage_path('app/exports/Tindakan_Pasien_Royal_Klinik_' . $today . '.xlsx');

$writer = new Xlsx($spreadsheet);
$writer->save($outRoot);
@copy($outRoot, $outStorage);

echo "Excel tersimpan:\n  - {$outRoot}\n  - {$outStorage}\n";
echo "Total baris data: " . ($lastDataRow - 1) . " (header di baris 1)\n";
