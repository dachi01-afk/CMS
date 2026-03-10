<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="App Clinic">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">
    <title>CMS-Royal-Klinik | Detail Pasien</title>
    <link href="{{ asset('storage/assets/royal_klinik.svg') }}" rel="shortcut icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.1/flowbite.min.js" defer></script>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>

    {{-- JQUERY WAJIB --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

    <style>
        :root {
            --primary: #2563eb;
            --dark: #0f172a;
            --muted: #64748b;
            --border: #e2e8f0;
            --surface: #ffffff;
            --bg: #f8fafc;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.10), transparent 22%),
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 18%),
                linear-gradient(to bottom, #f8fbff, #f8fafc);
            color: var(--dark);
        }

        .page-container {
            max-width: 1650px;
            margin: 0 auto;
            padding: 28px 20px 42px;
        }

        .hero-card {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 45%, #2563eb 100%);
            color: white;
            border-radius: 30px;
            box-shadow: 0 20px 45px rgba(37, 99, 235, 0.20);
        }

        .hero-card::before {
            content: "";
            position: absolute;
            width: 340px;
            height: 340px;
            border-radius: 999px;
            top: -120px;
            right: -90px;
            background: rgba(255, 255, 255, 0.08);
        }

        .hero-card::after {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            border-radius: 999px;
            bottom: -90px;
            left: -60px;
            background: rgba(255, 255, 255, 0.06);
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
            backdrop-filter: blur(4px);
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

        .panel-card {
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid var(--border);
            border-radius: 28px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
        }

        .info-card {
            border: 1px solid var(--border);
            border-radius: 20px;
            background: #f8fafc;
            padding: 16px;
            min-height: 92px;
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

        .profile-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 6px 12px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 12px;
            font-weight: 700;
        }

        .stat-card {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 24px;
            background: #fff;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.05);
            transition: all .25s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 34px rgba(15, 23, 42, 0.08);
        }

        .stat-card::after {
            content: "";
            position: absolute;
            right: -16px;
            top: -18px;
            width: 86px;
            height: 86px;
            border-radius: 999px;
            background: rgba(37, 99, 235, 0.08);
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

        .chart-card {
            border: 1px solid var(--border);
            border-radius: 26px;
            background: white;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
        }

        .chart-area {
            position: relative;
            height: 330px;
        }

        .info-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 6px 12px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 12px;
            font-weight: 700;
        }

        .table-card {
            border: 1px solid var(--border);
            border-radius: 28px;
            background: white;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.05);
        }

        .table-toolbar-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 8px 14px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 700;
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

        #tableRiwayat thead th {
            position: sticky;
            top: 0;
            z-index: 5;
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

        {{-- HERO --}}
        <div class="hero-card p-6 md:p-8">
            <div class="relative z-10 flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
                <div class="max-w-4xl">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <span class="hero-badge">
                            <i class="fa-solid fa-shield-halved"></i>
                            Super Admin
                        </span>
                        <span class="hero-badge">
                            <i class="fa-solid fa-user-injured"></i>
                            Detail Pasien
                        </span>
                    </div>

                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                        {{ $pasien->nama_pasien ?: '-' }}
                    </h1>

                    <p class="mt-3 max-w-3xl text-sm md:text-base text-blue-50/90 leading-relaxed">
                        Riwayat lengkap kunjungan pasien, poli, dokter, status, dan tren layanan pasien secara
                        menyeluruh.
                    </p>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <span class="hero-badge">
                            <i class="fa-solid fa-id-card"></i>
                            No EMR: {{ $pasien->no_emr ?: '-' }}
                        </span>
                        <span class="hero-badge">
                            <i class="fa-solid fa-cake-candles"></i>
                            Umur: {{ $pasien->umur }}
                        </span>
                        <span class="hero-badge">
                            <i class="fa-solid fa-venus-mars"></i>
                            {{ $pasien->jenis_kelamin ?: '-' }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('super.admin.pasien.insight.index') }}" class="glass-btn glass-btn-light">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali ke Insight
                    </a>
                </div>
            </div>
        </div>

        {{-- BIODATA + LAST VISIT --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="panel-card p-6 xl:col-span-2">
                <div class="mb-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                            <i class="fa-solid fa-user-injured text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-extrabold text-slate-900">{{ $pasien->nama_pasien ?: '-' }}</h2>
                            <p class="text-sm text-slate-500">No EMR: {{ $pasien->no_emr ?: '-' }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <span class="profile-badge">
                            <i class="fa-solid fa-envelope"></i>
                            {{ $pasien->email ?: 'Email tidak tersedia' }}
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="info-card">
                        <p class="label-mini">NIK</p>
                        <p class="value-main">{{ $pasien->nik ?: '-' }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">No BPJS</p>
                        <p class="value-main">{{ $pasien->no_bpjs ?: '-' }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">No HP</p>
                        <p class="value-main">{{ $pasien->no_hp_pasien ?: '-' }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Email</p>
                        <p class="value-main break-all">{{ $pasien->email ?: '-' }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Tanggal Lahir</p>
                        <p class="value-main">{{ $pasien->tanggal_lahir_label }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Umur</p>
                        <p class="value-main">{{ $pasien->umur }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Jenis Kelamin</p>
                        <p class="value-main">{{ $pasien->jenis_kelamin ?: '-' }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Golongan Darah</p>
                        <p class="value-main">{{ $pasien->golongan_darah ?: '-' }}</p>
                    </div>

                    <div class="info-card md:col-span-2">
                        <p class="label-mini">Alamat</p>
                        <p class="value-main">{{ $pasien->alamat ?: '-' }}</p>
                    </div>

                    <div class="info-card md:col-span-2">
                        <p class="label-mini">Alergi</p>
                        <p class="value-main">{{ $pasien->alergi ?: '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="panel-card p-6">
                <div class="mb-5">
                    <h3 class="section-title">Ringkasan Kunjungan Terakhir</h3>
                    <p class="section-subtitle mt-1">Snapshot kunjungan terakhir pasien.</p>
                </div>

                <div class="space-y-4">
                    <div class="info-card">
                        <p class="label-mini">Tanggal</p>
                        <p class="value-main">{{ $stats['kunjunganTerakhir'] }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Poli Terakhir</p>
                        <p class="value-main">{{ $stats['poliTerakhir'] }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Dokter Terakhir</p>
                        <p class="value-main">{{ $stats['dokterTerakhir'] }}</p>
                    </div>

                    <div class="info-card">
                        <p class="label-mini">Status Terakhir</p>
                        <div class="mt-2">
                            <span class="status-pill {{ $stats['statusTerakhirClass'] }}">
                                {{ $stats['statusTerakhir'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- STATS --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Total Kunjungan</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            {{ number_format($stats['totalKunjungan']) }}</h3>
                        <p class="mt-2 text-xs text-slate-400">Semua histori kunjungan pasien</p>
                    </div>
                    <div class="stat-icon bg-blue-100 text-blue-600">
                        <i class="fa-solid fa-notes-medical"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Total Poli Dikunjungi</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            {{ number_format($stats['totalPoli']) }}</h3>
                        <p class="mt-2 text-xs text-slate-400">Jumlah poli yang pernah dikunjungi</p>
                    </div>
                    <div class="stat-icon bg-cyan-100 text-cyan-600">
                        <i class="fa-solid fa-stethoscope"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Total Dokter Dikunjungi</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            {{ number_format($stats['totalDokter']) }}</h3>
                        <p class="mt-2 text-xs text-slate-400">Dokter yang pernah menangani pasien</p>
                    </div>
                    <div class="stat-icon bg-violet-100 text-violet-600">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Last Visit</p>
                        <h3 class="mt-3 text-xl font-extrabold text-slate-900">{{ $stats['kunjunganTerakhir'] }}</h3>
                        <p class="mt-2 text-xs text-slate-400">Tanggal kunjungan terakhir pasien</p>
                    </div>
                    <div class="stat-icon bg-amber-100 text-amber-600">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- CHARTS --}}
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="chart-card p-6">
                <div class="mb-5 flex items-start justify-between gap-3">
                    <div>
                        <h3 class="section-title">Kunjungan per Poli</h3>
                        <p class="section-subtitle mt-1">Poli yang paling sering dikunjungi pasien ini.</p>
                    </div>
                    <span class="info-badge">
                        <i class="fa-solid fa-chart-column"></i>
                        Bar Chart
                    </span>
                </div>
                <div class="chart-area">
                    <canvas id="chartPerPoli"></canvas>
                </div>
            </div>

            <div class="chart-card p-6">
                <div class="mb-5 flex items-start justify-between gap-3">
                    <div>
                        <h3 class="section-title">Distribusi Status</h3>
                        <p class="section-subtitle mt-1">Status kunjungan pasien secara visual.</p>
                    </div>
                    <span class="info-badge">
                        <i class="fa-solid fa-chart-pie"></i>
                        Doughnut
                    </span>
                </div>
                <div class="chart-area">
                    <canvas id="chartStatus"></canvas>
                </div>
            </div>

            <div class="chart-card p-6">
                <div class="mb-5 flex items-start justify-between gap-3">
                    <div>
                        <h3 class="section-title">Tren 12 Bulan</h3>
                        <p class="section-subtitle mt-1">Pergerakan jumlah kunjungan per bulan.</p>
                    </div>
                    <span class="info-badge">
                        <i class="fa-solid fa-chart-line"></i>
                        Line Chart
                    </span>
                </div>
                <div class="chart-area">
                    <canvas id="chartMonthly"></canvas>
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="table-card p-6">
            <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900">Riwayat Kunjungan Pasien</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Semua histori kunjungan pasien, lengkap dengan poli, dokter, status, dan keluhan awal.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="table-toolbar-badge">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        {{ count($riwayat) }} riwayat
                    </span>
                    <span class="table-toolbar-badge" style="background:#f8fafc; color:#334155;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        Searchable
                    </span>
                </div>
            </div>

            <div class="table-scroll">
                <table id="tableRiwayat" class="display w-full text-sm">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Poli</th>
                            <th>Dokter</th>
                            <th>No Antrian</th>
                            <th>Keluhan Awal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($riwayat as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->tanggal_kunjungan_label }}</td>
                                <td>{{ $item->nama_poli }}</td>
                                <td>{{ $item->nama_dokter }}</td>
                                <td>{{ $item->no_antrian }}</td>
                                <td>{{ $item->keluhan_awal }}</td>
                                <td>
                                    <span class="status-pill {{ $item->status_class }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
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
            $('#tableRiwayat').DataTable({
                pageLength: 10,
                order: [
                    [1, 'desc']
                ],
                language: {
                    search: 'Cari cepat:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ riwayat',
                    zeroRecords: 'Riwayat kunjungan tidak ditemukan',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(difilter dari _MAX_ total data)',
                    paginate: {
                        previous: 'Sebelumnya',
                        next: 'Berikutnya'
                    }
                }
            });

            const perPoliLabels = @json($chartPerPoliLabels);
            const perPoliValues = @json($chartPerPoliValues);

            const statusLabels = @json($chartStatusLabels);
            const statusValues = @json($chartStatusValues);

            const monthlyLabels = @json($chartMonthlyLabels);
            const monthlyValues = @json($chartMonthlyValues);

            if ($('#chartPerPoli').length) {
                new Chart($('#chartPerPoli').get(0), {
                    type: 'bar',
                    data: {
                        labels: perPoliLabels,
                        datasets: [{
                            label: 'Jumlah Kunjungan',
                            data: perPoliValues,
                            backgroundColor: [
                                'rgba(37, 99, 235, 0.80)',
                                'rgba(14, 165, 233, 0.80)',
                                'rgba(99, 102, 241, 0.80)',
                                'rgba(16, 185, 129, 0.80)',
                                'rgba(245, 158, 11, 0.80)',
                                'rgba(139, 92, 246, 0.80)',
                                'rgba(239, 68, 68, 0.80)'
                            ],
                            borderRadius: 12,
                            borderWidth: 0,
                            maxBarThickness: 44
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#475569'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    color: '#475569'
                                },
                                grid: {
                                    color: 'rgba(148, 163, 184, 0.15)'
                                }
                            }
                        }
                    }
                });
            }

            if ($('#chartStatus').length) {
                new Chart($('#chartStatus').get(0), {
                    type: 'doughnut',
                    data: {
                        labels: statusLabels,
                        datasets: [{
                            data: statusValues,
                            backgroundColor: [
                                '#f59e0b',
                                '#0ea5e9',
                                '#6366f1',
                                '#8b5cf6',
                                '#10b981',
                                '#ef4444'
                            ],
                            borderWidth: 0,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        cutout: '68%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    color: '#475569',
                                    padding: 16
                                }
                            }
                        }
                    }
                });
            }

            if ($('#chartMonthly').length) {
                new Chart($('#chartMonthly').get(0), {
                    type: 'line',
                    data: {
                        labels: monthlyLabels,
                        datasets: [{
                            label: 'Kunjungan',
                            data: monthlyValues,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.12)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 3,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: '#2563eb'
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true,
                                labels: {
                                    color: '#334155'
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: '#475569'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0,
                                    color: '#475569'
                                },
                                grid: {
                                    color: 'rgba(148, 163, 184, 0.15)'
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
</body>

</html>
