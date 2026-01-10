<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>{{ $meta['judul'] }}</title>
    <style>
        @page {
            margin: 18px 18px 28px 18px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111;
        }

        h1 {
            font-size: 16px;
            margin: 0 0 4px 0;
        }

        .meta {
            margin: 0 0 10px 0;
        }

        .meta div {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #d9d9d9;
            padding: 6px 8px;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            text-align: center;
        }

        td.num {
            text-align: right;
            white-space: nowrap;
        }

        td.center {
            text-align: center;
        }

        .sub {
            font-size: 10px;
            color: #555;
            margin-top: 2px;
        }
    </style>
</head>

<body>

    <h1>{{ $meta['judul'] }}</h1>
    <div class="meta">
        <div><strong>Periode:</strong> {{ $meta['periode'] }}</div>
        <div>
            <strong>Filter Nama Obat:</strong>
            {{ ($meta['filterNama'] ?? '-') !== '-' ? $meta['filterNama'] : 'Semua Obat' }}
        </div>
        <div><strong>Dicetak:</strong> {{ $meta['printedAt'] }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:38px;">#</th>
                <th>Nama Obat</th>
                <th style="width:90px;">Satuan</th>
                <th style="width:120px;">Penggunaan Umum</th>
                <th style="width:120px;">Nominal Umum</th>
                <th style="width:120px;">Penggunaan BPJS</th>
                <th style="width:120px;">Nominal BPJS</th>
                <th style="width:110px;">Sisa Obat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
                @php
                    $satuan = $row->satuan ?? 'Unit';
                    $nama = $row->nama_obat ?? '-';
                    $kandungan = $row->kandungan_obat ?? null;

                    // paksa tampil 0 sama seperti excel terakhir kamu
                    $pu = (int) ($row->penggunaan_umum ?? 0);
                    $nu = (int) ($row->nominal_umum ?? 0);
                    $pb = (int) ($row->penggunaan_bpjs ?? 0);
                    $nb = (int) ($row->nominal_bpjs ?? 0);
                    $sisa = (int) ($row->sisa_obat ?? 0);
                @endphp
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>
                        <div><strong>{{ $nama }}</strong></div>
                        @if ($kandungan)
                            <div class="sub">{{ $kandungan }}</div>
                        @endif
                    </td>
                    <td class="center">{{ $satuan }}</td>
                    <td class="center">{{ $pu }} {{ $satuan }}</td>
                    <td class="num">Rp {{ number_format($nu, 0, ',', '.') }}</td>
                    <td class="center">{{ $pb }} {{ $satuan }}</td>
                    <td class="num">Rp {{ number_format($nb, 0, ',', '.') }}</td>
                    <td class="center">{{ $sisa }} {{ $satuan }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="center">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Page number (DOMPDF) --}}
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_text(740, 565, "Halaman {PAGE_NUM} / {PAGE_COUNT}", null, 9, [0,0,0]);
        }
    </script>
</body>

</html>
