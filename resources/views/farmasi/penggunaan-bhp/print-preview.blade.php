<!DOCTYPE html>
<html>

<head>
    <title>Laporan Penggunaan BHP</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table,
        th,
        td {
            border: 1px solid #333;
        }

        th {
            background-color: #f2f2f2;
            padding: 8px;
        }

        td {
            padding: 6px;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>LAPORAN PENGGUNAAN BAHAN HABIS PAKAI</h2>
        <p>Periode: {{ $startDate }} s/d {{ $endDate }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Barang</th>
                <th width="15%">Penggunaan</th>
                <th width="20%">Nominal</th>
                <th width="15%">Sisa Stok</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $index => $row)
                @php
                    $nominal = ($row->total_pakai_umum ?? 0) * ($row->harga_jual_umum_bhp ?? 0);
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row->nama_barang }}</td>
                    <td class="text-center">{{ number_format($row->total_pakai_umum ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">Rp {{ number_format($nominal, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($row->stok_barang, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
