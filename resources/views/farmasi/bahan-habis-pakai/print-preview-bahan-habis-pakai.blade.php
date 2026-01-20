<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>{{ $meta['title'] ?? 'Laporan BHP' }}</title>
    <link href="{{ asset('storage/assets/royal_klinik.svg') }}" rel="shortcut icon">

    <style>
        /* ===== DomPDF Page ===== */
        @page {
            size: A4 landscape;
            margin: 10mm 10mm 14mm 10mm;
            /* bawah agak lega untuk footer */
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.7px;
            color: #111827;
            margin: 0;
        }

        /* ===== Layout helpers ===== */
        .row {
            width: 100%;
        }

        .flex {
            display: flex;
        }

        .items-center {
            align-items: center;
        }

        .justify-between {
            justify-content: space-between;
        }

        .gap-10 {
            gap: 10px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .muted {
            color: #6B7280;
        }

        .bold {
            font-weight: 700;
        }

        .semibold {
            font-weight: 600;
        }

        .title {
            font-size: 15px;
            font-weight: 800;
            letter-spacing: .2px;
            margin: 0;
        }

        .sub {
            font-size: 10.3px;
            color: #374151;
        }

        .hr {
            border: 0;
            border-top: 1px solid #E5E7EB;
            margin: 8px 0 10px;
        }

        /* ===== Header block ===== */
        .header {
            padding: 2mm 0 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .logo {
            width: 22px;
            height: 22px;
        }

        .brand-name {
            font-size: 11.5px;
            font-weight: 800;
            line-height: 1;
        }

        .brand-tag {
            font-size: 9.5px;
            color: #6B7280;
            margin-top: 2px;
        }

        .meta-box {
            border: 1px solid #E5E7EB;
            background: #F9FAFB;
            border-radius: 8px;
            padding: 6px 8px;
            font-size: 9.8px;
            line-height: 1.35;
            min-width: 92mm;
        }

        .meta-box b {
            color: #111827;
        }

        .pill {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 999px;
            border: 1px solid #E5E7EB;
            background: #FFFFFF;
            font-size: 9.3px;
            color: #374151;
            vertical-align: middle;
        }

        /* ===== Table ===== */
        .table-wrap {
            border: 1px solid #D1D5DB;
            border-radius: 10px;
            overflow: hidden;
            /* dompdf kadang hit/miss, tapi aman */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        thead th {
            background: #F3F4F6;
            color: #111827;
            font-weight: 800;
            font-size: 9.8px;
            padding: 7px 6px;
            border-bottom: 1px solid #D1D5DB;
            border-right: 1px solid #E5E7EB;
        }

        thead th:last-child {
            border-right: 0;
        }

        tbody td {
            font-size: 9.9px;
            padding: 7px 6px;
            border-top: 1px solid #E5E7EB;
            border-right: 1px solid #F1F5F9;
            vertical-align: middle;
        }

        tbody td:last-child {
            border-right: 0;
        }

        /* Zebra */
        tbody tr:nth-child(even) td {
            background: #FCFCFD;
        }

        .nowrap {
            white-space: nowrap;
        }

        .wrap {
            white-space: normal;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        /* Kode cell: anti tembus garis */
        .kode-cell {
            font-family: DejaVu Sans Mono, DejaVu Sans, sans-serif;
            padding: 0;
            /* padding dipindah ke span */
        }

        .kode-wrap {
            display: block;
            padding: 7px 10px;
            /* jarak aman dari border */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: clip;
            line-height: 1.15;
        }

        /* Money */
        .money {
            font-variant-numeric: tabular-nums;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: -8mm;
            left: 0;
            right: 0;
            font-size: 9.3px;
            color: #6B7280;
        }

        .footer .line {
            border-top: 1px solid #E5E7EB;
            margin-bottom: 4px;
        }
    </style>
</head>

<body>

    @php
        $title = $meta['title'] ?? 'Laporan Data Stok Bahan Habis Pakai';
        $printedAt = $meta['printed_at'] ?? '-';
        $keyword = !empty($meta['keyword']) ? '"' . $meta['keyword'] . '"' : 'Semua Data';
        $total = $meta['total'] ?? 0;
    @endphp

    {{-- ===== HEADER ===== --}}
    <div class="header">
        <div class="row flex justify-between items-center gap-10">
            <div class="brand">
                {{-- Kalau svg kadang tidak kebaca di dompdf, ganti ke png --}}
                <img class="logo" src="{{ asset('storage/assets/royal_klinik.svg') }}" alt="logo">
                <div>
                    <div class="brand-name">Royal Klinik</div>
                    <div class="brand-tag">royalklinik.id</div>
                </div>
            </div>

            <div class="text-center" style="flex:1;">
                <div class="title">{{ $title }}</div>
                <div class="sub muted" style="margin-top:2px;">
                    Laporan dicetak otomatis dari sistem
                </div>
            </div>

            <div class="meta-box">
                <div><b>Dicetak</b>: <span class="pill">{{ $printedAt }}</span></div>
                <div style="margin-top:2px;"><b>Keyword</b>: <span class="pill">{{ $keyword }}</span></div>
                <div style="margin-top:2px;"><b>Total</b>: <span class="pill">{{ $total }} data</span></div>
            </div>
        </div>

        <hr class="hr">
    </div>

    {{-- ===== TABLE (pakai colgroup mm agar stabil di dompdf) ===== --}}
    <div class="table-wrap">
        <table>
            <colgroup>
                <col style="width:10mm;"> {{-- No --}}
                <col style="width:32mm;"> {{-- Kode --}}
                <col style="width:60mm;"> {{-- Nama Barang --}}
                <col style="width:34mm;"> {{-- Brand --}}
                <col style="width:22mm;"> {{-- Stok --}}
                <col style="width:22mm;"> {{-- Harga Umum --}}
                <col style="width:22mm;"> {{-- Harga Beli --}}
                <col style="width:22mm;"> {{-- Avg HPP --}}
                <col style="width:22mm;"> {{-- Harga OTC --}}
                <col style="width:22mm;"> {{-- Margin --}}
            </colgroup>

            <thead>
                <tr>
                    <th class="">No</th>
                    <th class="text-center">Kode</th>
                    <th class="text-center">Nama Barang</th>
                    <th class="text-center">Brand Farmasi</th>
                    <th class="text-center">Stok</th>
                    <th class="text-center">Harga Umum</th>
                    <th class="text-center">Harga Beli</th>
                    <th class="text-center">Avg HPP</th>
                    <th class="text-center">Harga OTC</th>
                    <th class="text-center">Margin Profit</th>
                </tr>
            </thead>

            <tbody>
                @forelse($rows as $i => $bhp)
                    @php
                        $stok = is_null($bhp->stok_barang) ? 0 : (int) $bhp->stok_barang;
                        $satuan =
                            optional($bhp->satuanBHP)->nama_satuan_obat ??
                            (optional($bhp->satuanBHP)->nama_satuan ?? 'pcs');

                        $hargaJual = (float) ($bhp->harga_jual_umum_bhp ?? 0);
                        $hargaBeli = (float) ($bhp->harga_beli_satuan_bhp ?? 0);
                        $hpp = (float) ($bhp->avg_hpp_bhp ?? 0);
                        $otc = (float) ($bhp->harga_otc_bhp ?? 0);
                        $margin = $hargaJual - $hpp;

                        $rp2 = fn($n) => 'Rp ' . number_format($n, 2, ',', '.');
                        $rp0 = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');
                    @endphp

                    <tr>
                        <td class="text-center nowrap">{{ $i + 1 }}</td>

                        <td class="text-center kode-cell">
                            <span class="kode-wrap">{{ $bhp->kode ?? '-' }}</span>
                        </td>

                        <td class="text-left wrap">{{ $bhp->nama_barang ?? '-' }}</td>
                        <td class="text-center wrap">{{ optional($bhp->brandFarmasi)->nama_brand ?? '-' }}</td>
                        <td class="text-center nowrap">{{ $stok }} {{ $satuan }}</td>

                        <td class="text-right nowrap money">{{ $rp2($hargaJual) }}</td>
                        <td class="text-right nowrap money">{{ $rp2($hargaBeli) }}</td>
                        <td class="text-right nowrap money">{{ $rp2($hpp) }}</td>
                        <td class="text-right nowrap money">{{ $rp2($otc) }}</td>
                        <td class="text-right nowrap money">{{ $rp0($margin) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center muted" style="padding:12px;">
                            Tidak ada data.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ===== FOOTER + PAGE NUMBER (DOMPDF) ===== --}}
    <div class="footer">
        <div class="line"></div>
        <div class="row flex justify-between">
            <div>Dokumen ini dihasilkan otomatis dari sistem Royal Klinik.</div>
            <div>
                Halaman <span class="bold">{PAGE_NUM}</span> / <span class="bold">{PAGE_COUNT}</span>
            </div>
        </div>
    </div>

    {{-- DomPDF page number (lebih pasti) --}}
    <script type="text/php">
if (isset($pdf)) {
    $text = "Halaman {PAGE_NUM} / {PAGE_COUNT}";
    $size = 9;
    $font = $fontMetrics->getFont("DejaVu Sans", "normal");
    $width = $fontMetrics->get_text_width($text, $font, $size);
    $x = $pdf->get_width() - $width - 28; // kanan
    $y = $pdf->get_height() - 24; // bawah
    $pdf->page_text($x, $y, $text, $font, $size, [0.42, 0.45, 0.50]);
}
</script>

</body>

</html>
