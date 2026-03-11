<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Kunjungan</title>
    <style>
        @page {
            margin: 20px 24px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #0f172a;
            font-size: 12px;
        }

        .header-table,
        .meta-table,
        .summary-table,
        .detail-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .subtitle {
            font-size: 12px;
            color: #475569;
        }

        .badge {
            display: inline-block;
            padding: 6px 10px;
            border: 1px solid #a7f3d0;
            background: #ecfdf5;
            color: #047857;
            font-size: 11px;
            font-weight: 700;
        }

        .divider {
            margin: 14px 0 10px;
            border-top: 2px solid #10b981;
        }

        .meta-table td {
            padding: 4px 0;
            font-size: 12px;
        }

        .section-title {
            margin: 16px 0 8px;
            font-size: 13px;
            font-weight: 700;
        }

        .summary-table td {
            width: 25%;
            border: 1px solid #cbd5e1;
            padding: 10px;
            vertical-align: top;
        }

        .summary-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 6px;
        }

        .summary-value {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            font-size: 11px;
        }

        .detail-table th {
            background: #f8fafc;
            text-align: center;
            font-weight: 700;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .note {
            margin-top: 12px;
            font-size: 10px;
            color: #64748b;
        }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td>
                <div class="title">Laporan Analitik Sistem & Bisnis</div>
                <div class="subtitle">
                    Laporan komposisi kunjungan aktif, selesai, dan dibatalkan berdasarkan filter periode.
                </div>
            </td>
            <td class="text-right">
                <span class="badge">Filter: {{ $chartData['filter_label'] }}</span>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="meta-table">
        <tr>
            <td width="20%">Nama Super Admin</td>
            <td width="2%">:</td>
            <td>{{ $namaSuperAdmin }}</td>
        </tr>
        <tr>
            <td>Periode Laporan</td>
            <td>:</td>
            <td>{{ $chartData['range_text'] }}</td>
        </tr>
        <tr>
            <td>Dicetak Pada</td>
            <td>:</td>
            <td>{{ $generatedAt->locale('id')->translatedFormat('d F Y H:i') }}</td>
        </tr>
        <tr>
            <td>Keterangan</td>
            <td>:</td>
            <td>Status aktif dihitung dari Pending, Waiting, Engaged, dan Payment.</td>
        </tr>
    </table>

    <div class="section-title">Ringkasan</div>

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">Total Kunjungan</div>
                <div class="summary-value">{{ number_format($chartData['summary_total'], 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Aktif</div>
                <div class="summary-value">{{ number_format($chartData['summary_aktif'], 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Selesai</div>
                <div class="summary-value">{{ number_format($chartData['summary_selesai'], 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Dibatalkan</div>
                <div class="summary-value">{{ number_format($chartData['summary_dibatalkan'], 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Detail Per Periode</div>

    <table class="detail-table">
        <thead>
            <tr>
                <th width="6%">No</th>
                <th>Periode</th>
                <th width="14%">Total</th>
                <th width="14%">Aktif</th>
                <th width="14%">Selesai</th>
                <th width="14%">Dibatalkan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($chartData['rows'] as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $row['label'] }}</td>
                    <td class="text-right">{{ number_format($row['total'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['aktif'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['selesai'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($row['dibatalkan'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada data kunjungan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="note">
        Dokumen ini dibuat otomatis dari Dashboard Super Admin Royal Klinik.id.
    </div>
</body>

</html>
     