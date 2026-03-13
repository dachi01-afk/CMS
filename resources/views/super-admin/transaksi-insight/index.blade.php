<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="App Clinic">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CMS-Royal-Klinik | Insight Transaksi</title>
    <link href="{{ asset('storage/assets/royal_klinik.svg') }}" rel="shortcut icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.1/flowbite.min.js" defer></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

    <style>
        :root {
            --border: #e2e8f0;
        }

        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, 0.10), transparent 20%),
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.08), transparent 18%),
                linear-gradient(to bottom, #f8fbff, #f8fafc);
            color: #0f172a;
        }

        .page-container {
            max-width: 1650px;
            margin: 0 auto;
            padding: 28px 20px 42px;
        }

        .hero-card {
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #0f172a 0%, #14532d 55%, #10b981 100%);
            color: white;
            border-radius: 28px;
            box-shadow: 0 20px 45px rgba(16, 185, 129, 0.18);
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

        .panel-card,
        .chart-card,
        .table-card {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid var(--border);
            border-radius: 28px;
            box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
        }

        .period-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 14px;
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 800;
            border: 1px solid #d1fae5;
            background: #ecfdf5;
            color: #047857;
            cursor: pointer;
            transition: all .2s ease;
        }

        .period-chip:hover {
            transform: translateY(-1px);
            background: #d1fae5;
        }

        .period-info {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 999px;
            padding: 10px 16px;
            background: #ecfdf5;
            color: #047857;
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

        .input-modern,
        .select-modern {
            width: 100%;
            border: 1px solid #cbd5e1;
            background: #fff;
            border-radius: 16px;
            padding: 12px 14px;
            font-size: 14px;
            color: #0f172a;
        }

        .input-modern:focus,
        .select-modern:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12);
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

        .info-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 5px 12px;
            background: #ecfdf5;
            color: #059669;
            font-size: 12px;
            font-weight: 700;
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
            height: 330px;
        }

        .table-scroll {
            overflow-x: auto;
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
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                            Insight Transaksi
                        </span>
                        <span class="hero-badge">
                            <i class="fa-solid fa-calendar-days"></i>
                            {{ $periodeLabel }}
                        </span>
                    </div>

                    <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight">
                        Detail Total Transaksi
                    </h1>

                    <p class="mt-3 max-w-3xl text-sm md:text-base text-emerald-50/90 leading-relaxed">
                        Pantau transaksi pembayaran berdasarkan periode harian, bulanan, dan tahunan dalam satu panel
                        super admin.
                    </p>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <span class="hero-badge">
                            <i class="fa-solid fa-receipt"></i>
                            {{ number_format($stats['totalTransaksi']) }} Transaksi
                        </span>
                        <span class="hero-badge">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            Rp {{ number_format($stats['totalPendapatan'], 0, ',', '.') }}
                        </span>
                        <span class="hero-badge">
                            <i class="fa-solid fa-users"></i>
                            {{ number_format($stats['pasienTerlibat']) }} Pasien
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('super.admin.index') }}" class="glass-btn glass-btn-light">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('super.admin.transaksi.insight.index') }}" class="panel-card p-6 md:p-7"
            id="filterForm">
            <div class="mb-5">
                <h2 class="section-title">Filter Transaksi</h2>
                <p class="section-subtitle mt-1">Atur tampilan data transaksi berdasarkan periode, status, poli, dokter,
                    metode, dan pencarian umum.</p>
            </div>

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

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-8">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Mode Periode</label>
                    <select name="periode" id="periode" class="select-modern">
                        <option value="harian" {{ ($filters['periode'] ?? 'bulanan') == 'harian' ? 'selected' : '' }}>
                            Harian</option>
                        <option value="bulanan"
                            {{ ($filters['periode'] ?? 'bulanan') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                        <option value="tahunan" {{ ($filters['periode'] ?? '') == 'tahunan' ? 'selected' : '' }}>
                            Tahunan</option>
                    </select>
                </div>

                <div class="period-field" id="fieldTanggal">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Pilih Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal"
                        value="{{ $filters['tanggal'] ?? now()->toDateString() }}" class="input-modern">
                </div>

                <div class="period-field" id="fieldBulan">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Pilih Bulan</label>
                    <input type="month" name="bulan_tahun" id="bulan_tahun"
                        value="{{ $filters['bulan_tahun'] ?? now()->format('Y-m') }}" class="input-modern">
                </div>

                <div class="period-field" id="fieldTahun">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Pilih Tahun</label>
                    <input type="number" name="tahun" id="tahun" min="2000" max="2100"
                        value="{{ $filters['tahun'] ?? now()->format('Y') }}" class="input-modern">
                </div>

                <div class="xl:col-span-2">
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Cari Transaksi</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                        placeholder="Kode transaksi / pasien / no EMR / BPJS / poli / dokter" class="input-modern">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Status</label>
                    <select name="status" class="select-modern">
                        <option value="">Semua Status</option>
                        @foreach ($statusOptions as $status)
                            <option value="{{ $status }}"
                                {{ ($filters['status'] ?? '') == $status ? 'selected' : '' }}>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Poli</label>
                    <select name="poli_id" class="select-modern">
                        <option value="">Semua Poli</option>
                        @foreach ($poliList as $poli)
                            <option value="{{ $poli->id }}"
                                {{ ($filters['poli_id'] ?? '') == $poli->id ? 'selected' : '' }}>
                                {{ $poli->nama_poli }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Dokter</label>
                    <select name="dokter_id" class="select-modern">
                        <option value="">Semua Dokter</option>
                        @foreach ($dokterList as $dokter)
                            <option value="{{ $dokter->id }}"
                                {{ ($filters['dokter_id'] ?? '') == $dokter->id ? 'selected' : '' }}>
                                {{ $dokter->nama_dokter }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Metode</label>
                    <select name="metode_pembayaran_id" class="select-modern">
                        <option value="">Semua Metode</option>
                        @foreach ($metodePembayaranList as $metode)
                            <option value="{{ $metode->id }}"
                                {{ ($filters['metode_pembayaran_id'] ?? '') == $metode->id ? 'selected' : '' }}>
                                {{ $metode->nama_metode }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-bold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-emerald-700">
                    <i class="fa-solid fa-magnifying-glass mr-2"></i>
                    Terapkan Filter
                </button>

                <button type="button" id="btnResetFilter"
                    class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                    <i class="fa-solid fa-rotate-left mr-2"></i>
                    Reset
                </button>
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
                        <p class="text-sm font-semibold text-slate-500">Total Transaksi</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            {{ number_format($stats['totalTransaksi']) }}</h3>
                        <p class="mt-2 text-xs text-slate-400">Semua transaksi pada periode aktif</p>
                    </div>
                    <div class="stat-icon bg-emerald-100 text-emerald-600">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Transaksi Lunas</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            {{ number_format($stats['transaksiSukses']) }}</h3>
                        <p class="mt-2 text-xs text-slate-400">Status sudah bayar</p>
                    </div>
                    <div class="stat-icon bg-blue-100 text-blue-600">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Belum Bayar</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            {{ number_format($stats['transaksiPending']) }}</h3>
                        <p class="mt-2 text-xs text-slate-400">Transaksi belum lunas</p>
                    </div>
                    <div class="stat-icon bg-amber-100 text-amber-600">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Total Pendapatan</p>
                        <h3 class="mt-3 text-2xl font-extrabold text-slate-900">Rp
                            {{ number_format($stats['totalPendapatan'], 0, ',', '.') }}</h3>
                        <p class="mt-2 text-xs text-slate-400">Akumulasi transaksi berhasil</p>
                    </div>
                    <div class="stat-icon bg-violet-100 text-violet-600">
                        <i class="fa-solid fa-sack-dollar"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card p-5">
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Pasien Terlibat</p>
                        <h3 class="mt-3 text-3xl font-extrabold text-slate-900">
                            {{ number_format($stats['pasienTerlibat']) }}</h3>
                        <p class="mt-2 text-xs text-slate-400">Pasien dengan transaksi pada periode aktif</p>
                    </div>
                    <div class="stat-icon bg-cyan-100 text-cyan-600">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="chart-card p-6">
                <div class="mb-5 flex items-start justify-between gap-3">
                    <div>
                        <h3 class="section-title">Distribusi Status Pembayaran</h3>
                        <p class="section-subtitle mt-1">Komposisi status transaksi pada periode aktif.</p>
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
                        <h3 class="section-title">Distribusi Metode Pembayaran</h3>
                        <p class="section-subtitle mt-1">Cash, transfer, dan metode lain pada periode aktif.</p>
                    </div>
                    <span class="info-badge">
                        <i class="fa-solid fa-credit-card"></i>
                        Bar
                    </span>
                </div>
                <div class="chart-area">
                    <canvas id="chartMetode"></canvas>
                </div>
            </div>

            <div class="chart-card p-6">
                <div class="mb-5 flex items-start justify-between gap-3">
                    <div>
                        <h3 class="section-title">{{ $trendTitle }}</h3>
                        <p class="section-subtitle mt-1">{{ $trendSubtitle }}</p>
                    </div>
                    <span class="info-badge">
                        <i class="fa-solid fa-chart-line"></i>
                        Trend
                    </span>
                </div>
                <div class="chart-area">
                    <canvas id="chartTrend"></canvas>
                </div>
            </div>
        </div>

        <div class="table-card p-6">
            <div class="mb-5">
                <h3 class="text-xl font-extrabold text-slate-900">Daftar Transaksi</h3>
                <p class="mt-1 text-sm text-slate-500">Daftar transaksi pada periode aktif yang sudah terhubung ke
                    pasien, kunjungan, poli, dokter, dan metode pembayaran.</p>
            </div>

            <div class="table-scroll">
                <table id="tableTransaksi" class="display w-full text-sm">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Transaksi</th>
                            <th>Tanggal Bayar</th>
                            <th>Pasien</th>
                            <th>No EMR</th>
                            <th>Tanggal Kunjungan</th>
                            <th>Poli</th>
                            <th>Dokter</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Total Bayar</th>
                            <th>Total Item</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transaksis as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="font-bold text-slate-900">{{ $item->kode_transaksi ?: '-' }}</td>
                                <td>{{ $item->tanggal_transaksi_label }}</td>
                                <td>{{ $item->nama_pasien }}</td>
                                <td>{{ $item->no_emr }}</td>
                                <td>{{ $item->tanggal_kunjungan_label }}</td>
                                <td>{{ $item->nama_poli }}</td>
                                <td>{{ $item->nama_dokter }}</td>
                                <td>{{ $item->nama_metode }}</td>
                                <td>
                                    <span class="status-pill {{ $item->status_class }}">
                                        {{ $item->status ?: '-' }}
                                    </span>
                                </td>
                                <td>Rp {{ number_format((float) $item->final_total, 0, ',', '.') }}</td>
                                <td>{{ number_format((int) $item->total_item) }}</td>
                                <td>
                                    <a href="{{ route('super.admin.transaksi.insight.show', $item->id) }}"
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

            $('#btnResetFilter').on('click', function() {
                window.location.href = '{{ route('super.admin.transaksi.insight.index') }}';
            });

            $('#tableTransaksi').DataTable({
                pageLength: 10,
                order: [
                    [2, 'desc']
                ],
                language: {
                    search: 'Cari cepat:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ transaksi',
                    zeroRecords: 'Data transaksi tidak ditemukan',
                    infoEmpty: 'Tidak ada data',
                    infoFiltered: '(difilter dari _MAX_ total data)',
                    paginate: {
                        previous: 'Sebelumnya',
                        next: 'Berikutnya'
                    }
                }
            });

            const chartStatusLabels = @json($chartStatusLabels);
            const chartStatusValues = @json($chartStatusValues);
            const chartMetodeLabels = @json($chartMetodeLabels);
            const chartMetodeValues = @json($chartMetodeValues);
            const chartTrendLabels = @json($chartTrendLabels);
            const chartTrendValues = @json($chartTrendValues);
            const trendDatasetLabel = @json($trendDatasetLabel);

            if ($('#chartStatus').length) {
                new Chart($('#chartStatus').get(0), {
                    type: 'doughnut',
                    data: {
                        labels: chartStatusLabels,
                        datasets: [{
                            data: chartStatusValues,
                            backgroundColor: ['#10b981', '#f59e0b'],
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
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            if ($('#chartMetode').length) {
                new Chart($('#chartMetode').get(0), {
                    type: 'bar',
                    data: {
                        labels: chartMetodeLabels,
                        datasets: [{
                            label: 'Jumlah Transaksi',
                            data: chartMetodeValues,
                            backgroundColor: 'rgba(16, 185, 129, 0.80)',
                            borderRadius: 10,
                            borderWidth: 0,
                            maxBarThickness: 48
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
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }

            if ($('#chartTrend').length) {
                new Chart($('#chartTrend').get(0), {
                    type: 'line',
                    data: {
                        labels: chartTrendLabels,
                        datasets: [{
                            label: trendDatasetLabel,
                            data: chartTrendValues,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.12)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 3,
                            pointRadius: 4
                        }]
                    },
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
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
