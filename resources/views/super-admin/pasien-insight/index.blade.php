<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="App Clinic">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">
    <title>CMS-Royal-Klinik | Insight Pasien</title>
    <link href="{{ asset('storage/assets/royal_klinik.svg') }}" rel="shortcut icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.1/flowbite.min.js" defer></script>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

    <style>
        :root {
            --primary: #2563eb;
            --primary-soft: rgba(37, 99, 235, 0.10);
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
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.10), transparent 20%),
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 18%),
                linear-gradient(to bottom, #f8fbff, #f8fafc);
            color: var(--dark);
        }

        .page-container {
            max-width: 1650px;
            margin: 0 auto;
            padding: 28px 20px 42px;
        }

        .panel-card {
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid var(--border);
            border-radius: 28px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
        }

        .hero-card {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 45%, #2563eb 100%);
            color: white;
            border-radius: 28px;
            box-shadow: 0 20px 45px rgba(37, 99, 235, 0.22);
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
            cursor: pointer;
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

        .input-modern,
        .select-modern {
            width: 100%;
            border: 1px solid #cbd5e1;
            background: #fff;
            border-radius: 16px;
            padding: 12px 14px;
            font-size: 14px;
            color: #0f172a;
            transition: all .2s ease;
        }

        .input-modern:focus,
        .select-modern:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12);
        }

        .filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .period-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 14px;
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 800;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            color: #1d4ed8;
            cursor: pointer;
            transition: all .2s ease;
        }

        .period-chip:hover {
            transform: translateY(-1px);
            background: #dbeafe;
        }

        .period-info {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 10px 16px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 800;
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

        .chart-card {
            border: 1px solid var(--border);
            border-radius: 26px;
            background: white;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
        }

        .chart-area {
            position: relative;
            height: 340px;
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

        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 14px;
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 800;
            color: white;
            background: #0f172a;
            transition: all .2s ease;
        }

        .action-btn:hover {
            background: #1e293b;
            transform: translateY(-1px);
        }

        .info-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 5px 12px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 12px;
            font-weight: 700;
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

        #tablePasien thead th {
            position: sticky;
            top: 0;
            z-index: 5;
        }

        .table-scroll {
            overflow-x: auto;
        }

        .filter-body {
            display: block;
        }

        .summary-note {
            font-size: 12px;
            color: #94a3b8;
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
                            <i class="fa-solid fa-hospital-user"></i>
                            Patient Analytics
                        </span>
                        <span class="hero-badge">
                            <i class="fa-solid fa-calendar-days"></i>
                            {{ $periodeLabel }}
                        </span>
                    </div>

                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                        Insight Pasien
                    </h1>

                    <p class="mt-3 max-w-3xl text-sm md:text-base text-blue-50/90 leading-relaxed">
                        Pantau pasien yang berkunjung berdasarkan periode harian, bulanan, dan tahunan dalam satu
                        dashboard super admin yang lebih informatif.
                    </p>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <span class="hero-badge">
                            <i class="fa-solid fa-users"></i>
                            {{ number_format($stats['totalPasien']) }} Pasien
                        </span>
                        <span class="hero-badge">
                            <i class="fa-solid fa-notes-medical"></i>
                            {{ number_format($stats['totalKunjungan']) }} Kunjungan
                        </span>
                        <span class="hero-badge">
                            <i class="fa-solid fa-stethoscope"></i>
                            {{ number_format($stats['poliAktif']) }} Poli Aktif
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="button" id="btnToggleFilter" class="glass-btn glass-btn-light">
                        <i class="fa-solid fa-sliders"></i>
                        Toggle Filter
                    </button>

                    <a href="{{ route('super.admin.index') }}" class="glass-btn glass-btn-light">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('super.admin.pasien.insight.index') }}" class="panel-card p-6 md:p-7"
            id="filterForm">
            <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                        <i class="fa-solid fa-filter"></i>
                    </div>
                    <div>
                        <h2 class="section-title">Filter Data Insight</h2>
                        <p class="section-subtitle">Atur tampilan data pasien berdasarkan periode, poli, dokter, dan
                            status kunjungan.</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="filter-chip">
                        <i class="fa-solid fa-layer-group text-blue-500"></i>
                        Period Based
                    </span>
                    <span class="filter-chip">
                        <i class="fa-solid fa-magnifying-glass text-blue-500"></i>
                        Live Search Table
                    </span>
                </div>
            </div>

            <div class="filter-body" id="filterBody">
                <div class="mb-5 flex flex-wrap gap-2">
                    <button type="button" class="period-chip shortcut-period" data-periode="harian"
                        data-tanggal="{{ now()->toDateString() }}">
                        <i class="fa-solid fa-calendar-day"></i>
                        Hari Ini
                    </button>

                    <button type="button" class="period-chip shortcut-period" data-periode="bulanan"
                        data-bulan="{{ now()->format('Y-m') }}">
                        <i class="fa-solid fa-calendar-alt"></i>
                        Bulan Ini
                    </button>

                    <button type="button" class="period-chip shortcut-period" data-periode="tahunan"
                        data-tahun="{{ now()->format('Y') }}">
                        <i class="fa-solid fa-calendar"></i>
                        Tahun Ini
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-12">
                    <div class="xl:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Mode Periode</label>
                        <select name="periode" id="periode" class="select-modern">
                            <option value="harian"
                                {{ ($filters['periode'] ?? 'bulanan') == 'harian' ? 'selected' : '' }}>Harian</option>
                            <option value="bulanan"
                                {{ ($filters['periode'] ?? 'bulanan') == 'bulanan' ? 'selected' : '' }}>Bulanan
                            </option>
                            <option value="tahunan" {{ ($filters['periode'] ?? '') == 'tahunan' ? 'selected' : '' }}>
                                Tahunan</option>
                        </select>
                    </div>

                    <div class="xl:col-span-2 period-field" id="fieldTanggal">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Pilih Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal"
                            value="{{ $filters['tanggal'] ?? now()->toDateString() }}" class="input-modern">
                    </div>

                    <div class="xl:col-span-2 period-field" id="fieldBulan">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Pilih Bulan</label>
                        <input type="month" name="bulan_tahun" id="bulan_tahun"
                            value="{{ $filters['bulan_tahun'] ?? now()->format('Y-m') }}" class="input-modern">
                    </div>

                    <div class="xl:col-span-2 period-field" id="fieldTahun">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Pilih Tahun</label>
                        <input type="number" name="tahun" id="tahun" min="2000" max="2100"
                            value="{{ $filters['tahun'] ?? now()->format('Y') }}" class="input-modern">
                    </div>

                    <div class="xl:col-span-4">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Cari Pasien</label>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                            placeholder="Nama / No EMR / NIK / BPJS / No HP" class="input-modern">
                    </div>

                    <div class="xl:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Poli</label>
                        <select name="poli_id" id="poli_id" class="select-modern">
                            <option value="">Semua Poli</option>
                            @foreach ($poliList as $poli)
                                <option value="{{ $poli->id }}"
                                    {{ ($filters['poli_id'] ?? '') == $poli->id ? 'selected' : '' }}>
                                    {{ $poli->nama_poli }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="xl:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Dokter</label>
                        <select name="dokter_id" id="dokter_id" class="select-modern">
                            <option value="">Semua Dokter</option>
                            @foreach ($dokterList as $dokter)
                                <option value="{{ $dokter->id }}"
                                    {{ ($filters['dokter_id'] ?? '') == $dokter->id ? 'selected' : '' }}>
                                    {{ $dokter->nama_dokter }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="xl:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                        <select name="status" id="status" class="select-modern">
                            <option value="">Semua Status</option>
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status }}"
                                    {{ ($filters['status'] ?? '') == $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <button type="submit"
                        class="inline-flex items-center justify-center rounded-2xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-blue-700">
                        <i class="fa-solid fa-magnifying-glass mr-2"></i>
                        Terapkan Filter
                    </button>

                    <button type="button" id="btnResetFilter"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <i class="fa-solid fa-rotate-left mr-2"></i>
                        Reset
                    </button>

                    <span class="summary-note">
                        Perubahan periode akan memengaruhi statistik, grafik, dan daftar pasien.
                    </span>
                </div>
            </div>
        </form>

        <div>
            <span class="period-info">
                <i class="fa-solid fa-chart-simple"></i>
                Menampilkan data periode: {{ $periodeLabel }}
            </span>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Pasien Berkunjung</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            {{ number_format($stats['totalPasien']) }}
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Jumlah pasien pada periode aktif</p>
                    </div>
                    <div class="stat-icon bg-blue-100 text-blue-600">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Total Kunjungan</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            {{ number_format($stats['totalKunjungan']) }}
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Semua visit pada periode aktif</p>
                    </div>
                    <div class="stat-icon bg-violet-100 text-violet-600">
                        <i class="fa-solid fa-notes-medical"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Kunjungan Selesai</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            {{ number_format($stats['kunjunganSelesai']) }}
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Status kunjungan berhasil selesai</p>
                    </div>
                    <div class="stat-icon bg-emerald-100 text-emerald-600">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Poli Aktif</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            {{ number_format($stats['poliAktif']) }}
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Poli yang menerima kunjungan</p>
                    </div>
                    <div class="stat-icon bg-cyan-100 text-cyan-600">
                        <i class="fa-solid fa-stethoscope"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Dokter Aktif</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            {{ number_format($stats['dokterAktif']) }}
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Dokter yang menangani kunjungan</p>
                    </div>
                    <div class="stat-icon bg-amber-100 text-amber-600">
                        <i class="fa-solid fa-user-doctor"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="chart-card p-6">
                <div class="mb-5 flex items-start justify-between gap-3">
                    <div>
                        <h3 class="section-title">Distribusi Kunjungan per Poli</h3>
                        <p class="section-subtitle mt-1">Lihat poli yang paling aktif menerima pasien.</p>
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
                        <p class="section-subtitle mt-1">Komposisi status kunjungan pasien secara visual.</p>
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
                        <h3 class="section-title">{{ $trendTitle }}</h3>
                        <p class="section-subtitle mt-1">{{ $trendSubtitle }}</p>
                    </div>
                    <span class="info-badge">
                        <i class="fa-solid fa-chart-column"></i>
                        Bar Chart
                    </span>
                </div>
                <div class="chart-area">
                    <canvas id="chartTrend"></canvas>
                </div>
            </div>
        </div>

        <div class="table-card p-6">
            <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-xl font-extrabold text-slate-900">Daftar Pasien</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Daftar pasien yang berkunjung pada periode aktif. Klik detail untuk melihat histori pasien
                        secara lengkap.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="table-toolbar-badge">
                        <i class="fa-solid fa-database"></i>
                        {{ count($patients) }} data
                    </span>
                    <span class="table-toolbar-badge" style="background:#f8fafc; color:#334155;">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        Searchable
                    </span>
                </div>
            </div>

            <div class="table-scroll">
                <table id="tablePasien" class="display w-full text-sm">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No EMR</th>
                            <th>Nama Pasien</th>
                            <th>NIK</th>
                            <th>No BPJS</th>
                            <th>No HP</th>
                            <th>JK</th>
                            <th>Umur</th>
                            <th>Total Kunjungan</th>
                            <th>Total Poli</th>
                            <th>Kunjungan Terakhir</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($patients as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->no_emr ?: '-' }}</td>
                                <td>
                                    <div class="font-bold text-slate-900">{{ $item->nama_pasien ?: '-' }}</div>
                                    <div class="mt-1 text-xs text-slate-500">
                                        Lahir: {{ $item->tanggal_lahir_label }}
                                    </div>
                                </td>
                                <td>{{ $item->nik ?: '-' }}</td>
                                <td>{{ $item->no_bpjs ?: '-' }}</td>
                                <td>{{ $item->no_hp_pasien ?: '-' }}</td>
                                <td>{{ $item->jenis_kelamin ?: '-' }}</td>
                                <td>{{ $item->umur }}</td>
                                <td>
                                    <span class="info-badge">
                                        <i class="fa-solid fa-notes-medical"></i>
                                        {{ number_format($item->total_kunjungan) }} visit
                                    </span>
                                </td>
                                <td>{{ number_format($item->total_poli) }}</td>
                                <td>{{ $item->terakhir_kunjungan_label }}</td>
                                <td>
                                    <a href="{{ route('super.admin.pasien.insight.show', $item->id) }}"
                                        class="action-btn">
                                        <i class="fa-solid fa-eye"></i>
                                        Detail
                                    </a>
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
            const perPoliLabels = @json($chartPerPoliLabels);
            const perPoliValues = @json($chartPerPoliValues);

            const statusLabels = @json($chartStatusLabels);
            const statusValues = @json($chartStatusValues);

            const trendLabels = @json($chartTrendLabels);
            const trendValues = @json($chartTrendValues);
            const trendDatasetLabel = @json($trendDatasetLabel);

            function togglePeriodeFields() {
                const periode = $('#periode').val();

                $('#fieldTanggal').hide();
                $('#fieldBulan').hide();
                $('#fieldTahun').hide();

                if (periode === 'harian') {
                    $('#fieldTanggal').show();
                }

                if (periode === 'bulanan') {
                    $('#fieldBulan').show();
                }

                if (periode === 'tahunan') {
                    $('#fieldTahun').show();
                }
            }

            togglePeriodeFields();

            $('#periode').on('change', function() {
                togglePeriodeFields();
            });

            $('#btnResetFilter').on('click', function() {
                window.location.href = '{{ route('super.admin.pasien.insight.index') }}';
            });

            $('#btnToggleFilter').on('click', function() {
                $('#filterBody').stop(true, true).slideToggle(220);
            });

            $('.shortcut-period').on('click', function() {
                const periode = $(this).data('periode');

                $('#periode').val(periode);

                if (periode === 'harian') {
                    $('#tanggal').val($(this).data('tanggal'));
                }

                if (periode === 'bulanan') {
                    $('#bulan_tahun').val($(this).data('bulan'));
                }

                if (periode === 'tahunan') {
                    $('#tahun').val($(this).data('tahun'));
                }

                togglePeriodeFields();
                $('#filterForm').trigger('submit');
            });

            $('#tablePasien').DataTable({
                pageLength: 10,
                order: [
                    [8, 'desc']
                ],
                language: {
                    search: 'Cari cepat:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ pasien',
                    zeroRecords: 'Data pasien tidak ditemukan',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(difilter dari _MAX_ total data)',
                    paginate: {
                        previous: 'Sebelumnya',
                        next: 'Berikutnya'
                    }
                }
            });

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

            if ($('#chartTrend').length) {
                new Chart($('#chartTrend').get(0), {
                    type: 'bar',
                    data: {
                        labels: trendLabels,
                        datasets: [{
                            label: trendDatasetLabel,
                            data: trendValues,
                            backgroundColor: 'rgba(37, 99, 235, 0.82)',
                            borderColor: '#2563eb',
                            borderWidth: 1,
                            borderRadius: 12,
                            maxBarThickness: trendLabels.length === 1 ? 90 : 36
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
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `${context.dataset.label}: ${context.parsed.y} kunjungan`;
                                    }
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
