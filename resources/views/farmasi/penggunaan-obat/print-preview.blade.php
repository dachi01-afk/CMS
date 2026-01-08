<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cetak Penggunaan Obat</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #111;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .title {
            font-size: 16px;
            font-weight: 700;
            margin: 0;
        }

        .meta {
            font-size: 11px;
            color: #444;
            margin-top: 4px;
            line-height: 1.4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px 8px;
        }

        th {
            background: #f3f4f6;
            text-transform: uppercase;
            font-size: 11px;
        }

        td.num {
            text-align: right;
            white-space: nowrap;
        }

        td.center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            background: #e5f7ed;
            border: 1px solid #b7ebc9;
            font-size: 11px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <div class="header">
        <div>
            <p class="title">Laporan Penggunaan Obat</p>
            <div class="meta">
                <div><b>Periode:</b>
                    {{ $startDate ? $startDate : '-' }} s/d {{ $endDate ? $endDate : '-' }}
                </div>
                <div><b>Filter Nama Obat:</b> {{ $namaObat ? $namaObat : '-' }}</div>
                <div><b>Dicetak:</b> {{ $printedAt }}</div>
            </div>
        </div>

        <div class="no-print">
            <button onclick="window.print()"
                style="padding:8px 12px;border:1px solid #ddd;border-radius:8px;background:#fff;cursor:pointer;">
                Print
            </button>
            <button onclick="window.close()"
                style="padding:8px 12px;border:1px solid #ddd;border-radius:8px;background:#fff;cursor:pointer;">
                Tutup
            </button>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:40px">#</th>
                <th>Nama Obat</th>
                <th class="center">Penggunaan Umum</th>
                <th class="center">Nominal Obat Umum</th>
                <th class="center">Penggunaan BPJS</th>
                <th class="center">Nominal Obat BPJS</th>
                <th class="center">Sisa Obat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
                <tr>
                    <td class="center">{{ $i + 1 }}</td>
                    <td>
                        <div style="font-weight:600">{{ $row->nama_obat }}</div>
                        @if (!empty($row->kandungan_obat))
                            <div style="font-size:11px;color:#666">{{ $row->kandungan_obat }}</div>
                        @endif
                    </td>
                    <td class="center">
                        {{ (int) $row->penggunaan_umum }} {{ $row->satuan ?? '' }}
                    </td>
                    <td class="num">
                        Rp {{ number_format((int) $row->nominal_umum, 0, ',', '.') }}
                    </td>
                    <td class="center">
                        {{ (int) $row->penggunaan_bpjs }} {{ $row->satuan ?? '' }}
                    </td>
                    <td class="num">
                        Rp {{ number_format((int) $row->nominal_bpjs, 0, ',', '.') }}
                    </td>
                    <td class="center">
                        <span class="badge">{{ (int) $row->sisa_obat }} {{ $row->satuan ?? '' }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="center" style="padding:16px;color:#666">
                        Data tidak ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        // auto print saat tab print dibuka
        window.addEventListener("load", () => {
            setTimeout(() => window.print(), 200);
        });
    </script>

</body>

</html>
