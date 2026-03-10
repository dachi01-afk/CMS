<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="App Clinic">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CMS-Royal-Klinik | Detail Transaksi</title>
    <link href="{{ asset('storage/assets/royal_klinik.svg') }}" rel="shortcut icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

    <style>
        :root {
            --border: #e2e8f0;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.10), transparent 22%),
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 18%),
                linear-gradient(to bottom, #f8fbff, #f8fafc);
            color: #0f172a;
        }

        .page-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 28px 20px 42px;
        }

        .hero-card,
        .panel-card,
        .table-card,
        .chart-card {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid var(--border);
            border-radius: 28px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
        }

        .hero-card {
            background: linear-gradient(135deg, #0f172a 0%, #14532d 55%, #10b981 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .hero-card::before {
            content: "";
            position: absolute;
            width: 320px;
            height: 320px;
            border-radius: 999px;
            top: -120px;
            right: -90px;
            background: rgba(255, 255, 255, 0.08);
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.15);
            color: #fff;
        }

        .glass-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 16px;
            padding: 12px 18px;
            font-size: 14px;
            font-weight: 700;
            transition: all .25s ease;
        }

        .glass-btn-light {
            background: rgba(255, 255, 255, 0.14);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .glass-btn-light:hover {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, 0.20);
        }

        .info-card {
            border: 1px solid var(--border);
            border-radius: 20px;
            background: #f8fafc;
            padding: 16px;
            min-height: 92px;
        }

        .label-mini {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #64748b;
        }

        .value-main {
            margin-top: 6px;
            font-size: 14px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.5;
        }

        .section-title {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .section-subtitle {
            font-size: 13px;
            color: #64748b;
        }

        .stat-card {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
        }

        .stat-card::after {
            content: "";
            position: absolute;
            right: -16px;
            top: -18px;
            width: 86px;
            height: 86px;
            border-radius: 999px;
            background: rgba(16, 185, 129, 0.08);
        }

        .stat-icon {
            position: relative;
            z-index: 2;
            width: 52px;
            height: 52px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 800;
        }

        .chart-area {
            position: relative;
            height: 320px;
        }

        .table-scroll {
            overflow-x: auto;
        }

        table.dataTable thead th {
            border-bottom: 1px solid #e2e8f0 !important;
            color: #334155 !important;
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
            background: #f8fafc;
        }

        table.dataTable tbody td {
            padding-top: 15px !important;
            padding-bottom: 15px !important;
            border-bottom: 1px solid #f1f5f9 !important;
            color: #334155;
            vertical-align: middle;
        }

        table.dataTable.no-footer {
            border-bottom: 1px solid #e2e8f0 !important;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter {
            margin-bottom: 18px;
            color: #475569;
            font-size: 14px;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin-top: 18px;
            color: #64748b !important;
            font-size: 14px;
        }

        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 7px 10px;
            background: #fff;
            margin-left: 8px;
        }

        @media (max-width: 1024px) {
            .chart-area {
                height: 300px;
            }
        }

        @media (max-width: 768px) {
            .page-container {
                padding: 16px 12px 28px;
            }

            .chart-area {
                height: 280px;
            }
        }
    </style>
</head>

<body>
    <div class="page-container space-y-6">

        <div class="hero-card p-6 md:p-8">
            <div class="relative z-10 flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
                <div class="max-w-4xl">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <span class="hero-badge">
                            <i class="fa-solid fa-shield-halved"></i>
                            Super Admin
                        </span>
                        <span class="hero-badge">
                            <i class="fa-solid fa-receipt"></i>
                            Detail Transaksi
                        </span>
                    </div>

                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                        {{ $transaksi->kode_transaksi ?: 'Transaksi' }}
                    </h1>

                    <p class="mt-3 max-w-3xl text-sm md:text-base text-emerald-50/90 leading-relaxed">
                        Detail transaksi sudah terhubung ke pasien, kunjungan, poli, dokter, metode pembayaran, layanan,
                        resep obat, laboratorium, dan radiologi.
                    </p>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <span class="hero-badge">
                            <i class="fa-solid fa-calendar-days"></i>
                            {{ $transaksi->tanggal_transaksi_label }}
                        </span>
                        <span class="hero-badge">
                            <i class="fa-solid fa-wallet"></i>
                            {{ $transaksi->status ?: '-' }}
                        </span>
                        <span class="hero-badge">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            Rp {{ number_format($transaksi->final_total, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('super.admin.transaksi.insight.index') }}" class="glass-btn glass-btn-light">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali ke Insight
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="panel-card p-6 xl:col-span-2">
                <div class="mb-5">
                    <h3 class="section-title">Informasi Transaksi</h3>
                    <p class="section-subtitle mt-1">Data utama pembayaran dan keterkaitan transaksi.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="info-card">
                        <p class="label-mini">Kode Transaksi</p>
                        <p class="value-main">{{ $transaksi->kode_transaksi ?: '-' }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Metode Pembayaran</p>
                        <p class="value-main">{{ $transaksi->nama_metode ?: '-' }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Tanggal Pembayaran</p>
                        <p class="value-main">{{ $transaksi->tanggal_transaksi_label }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Status Pembayaran</p>
                        <div class="mt-2">
                            <span class="status-pill {{ $transaksi->status_class }}">
                                {{ $transaksi->status ?: '-' }}
                            </span>
                        </div>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Total Tagihan</p>
                        <p class="value-main">Rp {{ number_format((float) $transaksi->total_tagihan, 0, ',', '.') }}
                        </p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Diskon</p>
                        <p class="value-main">{{ $transaksi->diskon_label }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Total Setelah Diskon</p>
                        <p class="value-main">Rp {{ number_format((float) $transaksi->final_total, 0, ',', '.') }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Uang Diterima</p>
                        <p class="value-main">Rp
                            {{ number_format((float) ($transaksi->uang_yang_diterima ?? 0), 0, ',', '.') }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Kembalian</p>
                        <p class="value-main">Rp {{ number_format((float) ($transaksi->kembalian ?? 0), 0, ',', '.') }}
                        </p>
                    </div>

                    <div class="info-card md:col-span-2">
                        <p class="label-mini">Catatan</p>
                        <p class="value-main">{{ $transaksi->catatan ?: '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="panel-card p-6">
                <div class="mb-5">
                    <h3 class="section-title">Konteks Pasien & Kunjungan</h3>
                    <p class="section-subtitle mt-1">Siapa pasiennya dan kunjungan yang terkait.</p>
                </div>

                <div class="space-y-4">
                    <div class="info-card">
                        <p class="label-mini">Nama Pasien</p>
                        <p class="value-main">{{ $transaksi->nama_pasien ?: '-' }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">No EMR</p>
                        <p class="value-main">{{ $transaksi->no_emr ?: '-' }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">No BPJS</p>
                        <p class="value-main">{{ $transaksi->no_bpjs ?: '-' }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Tanggal Kunjungan</p>
                        <p class="value-main">{{ $transaksi->tanggal_kunjungan_label }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Poli</p>
                        <p class="value-main">{{ $transaksi->nama_poli ?: '-' }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Dokter</p>
                        <p class="value-main">{{ $transaksi->nama_dokter ?: '-' }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">No Antrian</p>
                        <p class="value-main">{{ $transaksi->no_antrian ?: '-' }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Status Kunjungan</p>
                        <div class="mt-2">
                            <span class="status-pill {{ $transaksi->status_kunjungan_class }}">
                                {{ $transaksi->status_kunjungan ?: '-' }}
                            </span>
                        </div>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Keluhan Awal</p>
                        <p class="value-main">{{ $transaksi->keluhan_awal ?: '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Total Item</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            {{ number_format($stats['totalItem']) }}</h3>
                    </div>
                    <div class="stat-icon bg-blue-100 text-blue-600">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Total Qty</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            {{ number_format($stats['totalQty']) }}</h3>
                    </div>
                    <div class="stat-icon bg-violet-100 text-violet-600">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Subtotal Detail</p>
                        <h3 class="mt-3 text-2xl font-extrabold text-slate-900">Rp
                            {{ number_format($stats['subtotalDetail'], 0, ',', '.') }}</h3>
                    </div>
                    <div class="stat-icon bg-cyan-100 text-cyan-600">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Total Bayar</p>
                        <h3 class="mt-3 text-2xl font-extrabold text-slate-900">Rp
                            {{ number_format($stats['totalBayar'], 0, ',', '.') }}</h3>
                    </div>
                    <div class="stat-icon bg-emerald-100 text-emerald-600">
                        <i class="fa-solid fa-sack-dollar"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Layanan Kunjungan</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            {{ number_format($stats['totalLayananVisit']) }}</h3>
                        <p class="mt-2 text-xs text-slate-400">Total layanan dari kunjungan terkait</p>
                    </div>
                    <div class="stat-icon bg-amber-100 text-amber-600">
                        <i class="fa-solid fa-stethoscope"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Item Layanan</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            {{ number_format($stats['itemLayanan']) }}</h3>
                        <p class="mt-2 text-xs text-slate-400">Item layanan yang masuk transaksi</p>
                    </div>
                    <div class="stat-icon bg-blue-100 text-blue-600">
                        <i class="fa-solid fa-stethoscope"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Item Resep</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            {{ number_format($stats['itemResep']) }}</h3>
                        <p class="mt-2 text-xs text-slate-400">Item resep obat yang tertagih</p>
                    </div>
                    <div class="stat-icon bg-emerald-100 text-emerald-600">
                        <i class="fa-solid fa-pills"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Item Lab</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">{{ number_format($stats['itemLab']) }}
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Item pemeriksaan laboratorium</p>
                    </div>
                    <div class="stat-icon bg-violet-100 text-violet-600">
                        <i class="fa-solid fa-flask-vial"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Item Radiologi</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            {{ number_format($stats['itemRadiologi']) }}</h3>
                        <p class="mt-2 text-xs text-slate-400">Item pemeriksaan radiologi</p>
                    </div>
                    <div class="stat-icon bg-amber-100 text-amber-600">
                        <i class="fa-solid fa-x-ray"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <div class="chart-card p-6">
                <div class="mb-5">
                    <h3 class="section-title">Komposisi Jenis Item</h3>
                    <p class="section-subtitle mt-1">Distribusi item transaksi berdasarkan jenisnya.</p>
                </div>
                <div class="chart-area">
                    <canvas id="chartJenisItem"></canvas>
                </div>
            </div>

            <div class="table-card p-6">
                <div class="mb-5">
                    <h3 class="section-title">Layanan Kunjungan Terkait</h3>
                    <p class="section-subtitle mt-1">Data dari pivot `kunjungan_layanan` dan master `layanan`.</p>
                </div>

                <div class="table-scroll">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-left text-slate-700">
                                <th class="px-4 py-3 font-bold">No</th>
                                <th class="px-4 py-3 font-bold">Nama Layanan</th>
                                <th class="px-4 py-3 font-bold">Jumlah</th>
                                <th class="px-4 py-3 font-bold">Harga Aktif</th>
                                <th class="px-4 py-3 font-bold">Estimasi Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($layananKunjungan as $index => $item)
                                <tr class="border-b border-slate-100">
                                    <td class="px-4 py-3">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 font-semibold text-slate-900">{{ $item->nama_layanan }}</td>
                                    <td class="px-4 py-3">{{ number_format((int) $item->jumlah) }}</td>
                                    <td class="px-4 py-3">Rp
                                        {{ number_format((float) $item->harga_aktif, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3">Rp
                                        {{ number_format((float) $item->estimasi_total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-5 text-center text-slate-500">
                                        Tidak ada layanan kunjungan terkait.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="table-card p-6">
            <div class="mb-5">
                <h3 class="text-xl font-extrabold text-slate-900">Detail Item Transaksi</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Tahap 3 sudah aktif. Item transaksi sekarang membaca layanan, resep obat, laboratorium, dan
                    radiologi
                    berdasarkan referensi yang tersedia.
                </p>
                <p class="mt-2 text-xs text-amber-600">
                    Catatan: nama master obat, nama pemeriksaan lab, dan nama pemeriksaan radiologi masih akan tampil
                    berbasis ID
                    sampai tabel master tahap berikutnya disambungkan.
                </p>
            </div>

            <div class="table-scroll">
                <table id="tableDetailTransaksi" class="display w-full text-sm">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Jenis Item</th>
                            <th>Nama Item</th>
                            <th>Referensi</th>
                            <th>Informasi Tambahan</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($details as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <span class="status-pill bg-slate-100 text-slate-700">
                                        {{ $item->jenis_item }}
                                    </span>
                                </td>
                                <td class="font-semibold text-slate-900">
                                    {{ $item->nama_item_final }}
                                </td>
                                <td class="text-xs text-slate-600">
                                    {{ $item->referensi_label }}
                                </td>
                                <td class="text-xs text-slate-600">
                                    {{ $item->informasi_tambahan }}
                                </td>
                                <td>{{ number_format((int) $item->qty) }}</td>
                                <td>Rp {{ number_format((float) $item->harga, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    <script>
        $(function() {
            $('#tableDetailTransaksi').DataTable({
                pageLength: 10,
                order: [
                    [0, 'asc']
                ],
                language: {
                    search: 'Cari cepat:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ item',
                    zeroRecords: 'Detail item tidak ditemukan',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(difilter dari _MAX_ total data)',
                    paginate: {
                        previous: 'Sebelumnya',
                        next: 'Berikutnya'
                    }
                }
            });

            const chartJenisLabels = @json($chartJenisLabels);
            const chartJenisValues = @json($chartJenisValues);

            if ($('#chartJenisItem').length) {
                new Chart($('#chartJenisItem').get(0), {
                    type: 'doughnut',
                    data: {
                        labels: chartJenisLabels,
                        datasets: [{
                            data: chartJenisValues,
                            backgroundColor: ['#10b981', '#3b82f6', '#8b5cf6', '#f59e0b',
                                '#ef4444'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        cutout: '68%',
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>

</html>
