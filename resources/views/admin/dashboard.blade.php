<x-mycomponents.layout>
    <div class="space-y-6">

        {{-- HERO --}}
        <section
            class="relative overflow-hidden rounded-[28px] bg-gradient-to-r from-slate-900 via-blue-900 to-blue-600 p-6 md:p-8 shadow-xl">
            <div class="absolute -top-20 -right-10 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-10 h-48 w-48 rounded-full bg-cyan-300/10 blur-3xl"></div>

            <div class="relative z-10 flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
                <div class="max-w-4xl text-white">
                    <div class="mb-4 flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-wide text-blue-50">
                            <i class="fa-solid fa-user-shield"></i>
                            Dashboard Admin
                        </span>

                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-wide text-blue-50">
                            <i class="fa-solid fa-chart-line"></i>
                            Operational Overview
                        </span>

                        <span id="heroPeriodeBadge"
                            class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-wide text-blue-50">
                            <i class="fa-solid fa-calendar-days"></i>
                            Tahun {{ date('Y') }}
                        </span>
                    </div>

                    <h1 class="text-3xl font-extrabold tracking-tight md:text-4xl">
                        Selamat Datang, {{ $namaAdmin }}
                    </h1>

                    <p class="mt-3 max-w-3xl text-sm leading-relaxed text-blue-50/90 md:text-base">
                        Pantau statistik utama klinik, distribusi data master, dan grafik kunjungan pasien
                        dalam satu dashboard admin yang lebih rapi, cepat dibaca, dan konsisten dengan tampilan
                        Super Admin.
                    </p>

                    <div class="mt-5 flex flex-wrap gap-2">
                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-xs font-semibold text-white">
                            <i class="fa-solid fa-calendar-day"></i>
                            {{ date('d M Y') }}
                        </span>

                        <span
                            class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-xs font-semibold text-white">
                            <i class="fa-solid fa-database"></i>
                            Data Dashboard Admin
                        </span>

                        <span id="dashboardStatus"
                            class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-4 py-2 text-xs font-semibold text-amber-700">
                            <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                            Memuat data...
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 xl:min-w-[360px]">
                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur">
                        <p class="text-xs uppercase tracking-wider text-blue-100">Tanggal</p>
                        <h3 class="mt-2 text-lg font-bold">{{ date('d M Y') }}</h3>
                        <p class="mt-1 text-xs text-blue-100">Update dashboard hari ini</p>
                    </div>

                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur">
                        <p class="text-xs uppercase tracking-wider text-blue-100">Periode Chart</p>
                        <h3 id="chartYear" class="mt-2 text-lg font-bold">{{ date('Y') }}</h3>
                        <p class="mt-1 text-xs text-blue-100">Visual kunjungan aktif</p>
                    </div>

                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur">
                        <p class="text-xs uppercase tracking-wider text-blue-100">Mode</p>
                        <h3 id="chartModeHero" class="mt-2 text-lg font-bold">Tahunan</h3>
                        <p class="mt-1 text-xs text-blue-100">Siap ganti filter periode</p>
                    </div>

                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur">
                        <p class="text-xs uppercase tracking-wider text-blue-100">Statistik</p>
                        <h3 class="mt-2 text-lg font-bold">4 KPI Utama</h3>
                        <p class="mt-1 text-xs text-blue-100">Pasien hari ini, dokter, pasien, farmasi</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- FILTER PANEL --}}
        <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                        <i class="fa-solid fa-filter"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900">Filter Grafik Kunjungan</h2>
                        <p class="text-sm text-slate-500">
                            Atur tampilan grafik kunjungan pasien berdasarkan periode harian, mingguan, bulanan, dan
                            tahunan.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-blue-50 px-4 py-2 text-xs font-bold text-blue-700">
                        <i class="fa-solid fa-chart-column"></i>
                        Bar Chart
                    </span>
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-bold text-slate-700">
                        <i class="fa-solid fa-sliders"></i>
                        Period Based
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
                <div class="xl:col-span-3">
                    <label for="filterPeriodeChart" class="mb-2 block text-sm font-semibold text-slate-700">
                        Mode Periode
                    </label>
                    <select id="filterPeriodeChart"
                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                        <option value="harian">Harian</option>
                        <option value="mingguan">Mingguan</option>
                        <option value="bulanan">Bulanan</option>
                        <option value="tahunan" selected>Tahunan</option>
                    </select>
                </div>

                <div id="filterHarianWrap" class="hidden xl:col-span-3">
                    <label for="filterTanggalChart" class="mb-2 block text-sm font-semibold text-slate-700">
                        Pilih Tanggal
                    </label>
                    <input type="date" id="filterTanggalChart" value="{{ date('Y-m-d') }}"
                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div id="filterMingguanWrap" class="hidden xl:col-span-3">
                    <label for="filterMingguChart" class="mb-2 block text-sm font-semibold text-slate-700">
                        Pilih Minggu
                    </label>
                    <input type="week" id="filterMingguChart"
                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div id="filterBulananWrap" class="hidden xl:col-span-3">
                    <label for="filterBulanChart" class="mb-2 block text-sm font-semibold text-slate-700">
                        Pilih Bulan
                    </label>
                    <input type="month" id="filterBulanChart" value="{{ date('Y-m') }}"
                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div id="filterTahunanWrap" class="xl:col-span-3">
                    <label for="filterTahunChart" class="mb-2 block text-sm font-semibold text-slate-700">
                        Pilih Tahun
                    </label>
                    <input type="number" id="filterTahunChart" min="2020" max="2100"
                        value="{{ date('Y') }}"
                        class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100">
                </div>

                <div class="xl:col-span-3 flex items-end gap-3">
                    <button id="btnApplyChartFilter" type="button"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-md transition hover:-translate-y-0.5 hover:bg-blue-700">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        Terapkan Filter
                    </button>

                    <button id="btnResetDashboardFilter" type="button" title="Reset Filter Chart"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                        <i class="fa-solid fa-rotate-left"></i>
                    </button>
                </div>
            </div>
        </section>

        {{-- KPI --}}
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <a href="{{ route('admin.pasien.hari.ini') }}"
                class="group relative block overflow-hidden rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-amber-100">
                <div
                    class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-amber-100/60 transition group-hover:scale-110">
                </div>
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Pasien Hari Ini</p>
                        <h3 id="totalKunjunganHariIni" class="mt-3 text-3xl font-extrabold text-slate-900">0</h3>
                        <p class="mt-2 text-xs text-slate-400">
                            Klik untuk buka proses kunjungan hari ini
                        </p>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-amber-600">
                        <i class="fa-solid fa-calendar-check text-xl"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('manajemen_pengguna.index', ['tab' => 'dokter']) }}"
                class="group relative block overflow-hidden rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-100">
                <div
                    class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-blue-100/60 transition group-hover:scale-110">
                </div>
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Jumlah Dokter</p>
                        <h3 id="totalDokter" class="mt-3 text-3xl font-extrabold text-slate-900">0</h3>
                        <p class="mt-2 text-xs text-slate-400">Klik untuk buka data dokter</p>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                        <i class="fa-solid fa-user-doctor text-xl"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('manajemen_pengguna.index', ['tab' => 'pasien']) }}"
                class="group relative block overflow-hidden rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-emerald-100">
                <div
                    class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-emerald-100/60 transition group-hover:scale-110">
                </div>
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Jumlah Pasien</p>
                        <h3 id="totalPasien" class="mt-3 text-3xl font-extrabold text-slate-900">0</h3>
                        <p class="mt-2 text-xs text-slate-400">Klik untuk buka data pasien</p>
                    </div>
                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600">
                        <i class="fa-solid fa-users text-xl"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('manajemen_pengguna.index', ['tab' => 'farmasi']) }}"
                class="group relative block overflow-hidden rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-cyan-100">
                <div
                    class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-cyan-100/60 transition group-hover:scale-110">
                </div>
                <div class="relative z-10 flex items-start justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-500">Jumlah Apoteker</p>
                        <h3 id="totalFarmasi" class="mt-3 text-3xl font-extrabold text-slate-900">0</h3>
                        <p class="mt-2 text-xs text-slate-400">Klik untuk buka data apoteker</p>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-600">
                        <i class="fa-solid fa-prescription-bottle-medical text-xl"></i>
                    </div>
                </div>
            </a>
        </section>

        {{-- MAIN --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">

            {{-- CHART --}}
            <div
                class="xl:col-span-8 flex h-full flex-col rounded-[28px] border border-slate-200 bg-white p-4 md:p-5 shadow-sm">
                <div class="mb-5 flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-900">Grafik Kunjungan Pasien</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Visualisasi jumlah kunjungan pasien berdasarkan filter periode yang aktif.
                        </p>
                    </div>

                    <span
                        class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-4 py-2 text-xs font-bold text-blue-700">
                        <i class="fa-solid fa-chart-column"></i>
                        Bar Chart
                    </span>
                </div>

                <div class="relative min-h-[460px] flex-1 w-full overflow-hidden rounded-2xl">
                    <canvas id="kunjunganChart" class="!h-full !w-full"></canvas>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Mode Chart</p>
                        <p id="chartModeText" class="mt-2 text-sm font-semibold text-slate-700">Tahunan</p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Filter Aktif</p>
                        <p id="chartActiveFilter" class="mt-2 text-sm font-semibold text-slate-700">
                            Tahun {{ date('Y') }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wide text-slate-400">Ringkasan</p>
                        <p id="chartSummary" class="mt-2 text-sm font-semibold text-slate-700">
                            Menyiapkan ringkasan data kunjungan...
                        </p>
                    </div>
                </div>
            </div>

            {{-- SIDE --}}
            <div class="xl:col-span-4 space-y-6">
                <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-4">
                        <h3 class="text-lg font-extrabold text-slate-900">Distribusi Data</h3>
                        <p id="distributionNote" class="mt-1 text-sm text-slate-500">
                            Perbandingan relatif antara pasien hari ini dan statistik utama saat ini.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-600">Pasien Hari Ini</span>
                                <span id="totalKunjunganHariIniMini" class="font-bold text-slate-800">0</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div id="barKunjunganHariIni"
                                    class="h-2 rounded-full bg-gradient-to-r from-amber-500 to-orange-600"
                                    style="width: 0%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-600">Dokter</span>
                                <span id="totalDokterMini" class="font-bold text-slate-800">0</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div id="barDokter"
                                    class="h-2 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600"
                                    style="width: 0%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-600">Pasien</span>
                                <span id="totalPasienMini" class="font-bold text-slate-800">0</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div id="barPasien"
                                    class="h-2 rounded-full bg-gradient-to-r from-emerald-500 to-green-600"
                                    style="width: 0%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-600">Apoteker</span>
                                <span id="totalFarmasiMini" class="font-bold text-slate-800">0</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div id="barFarmasi"
                                    class="h-2 rounded-full bg-gradient-to-r from-cyan-500 to-sky-600"
                                    style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-4">
                        <h3 class="text-lg font-extrabold text-slate-900">Informasi Dashboard</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Tampilan admin ini disamakan secara visual dengan dashboard Super Admin.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                            <div class="mt-1 text-blue-600">
                                <i class="fa-solid fa-chart-column"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">Chart Dinamis</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Grafik mendukung filter harian, mingguan, bulanan, dan tahunan untuk membaca tren
                                    kunjungan.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                            <div class="mt-1 text-emerald-600">
                                <i class="fa-solid fa-database"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">KPI Ringkas</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    KPI menampilkan data master utama supaya admin lebih cepat membaca kondisi sistem.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                            <div class="mt-1 text-amber-600">
                                <i class="fa-solid fa-circle-info"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">Catatan Scope</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Card Pasien Hari Ini terhubung ke Proses Kunjungan Hari Ini, card Jumlah Dokter
                                    ke Data Dokter, card Jumlah Pasien ke Data Pasien, dan card Jumlah Farmasi ke
                                    Data Farmasi.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @vite(['resources/js/admin/dashboard.js'])
</x-mycomponents.layout>
