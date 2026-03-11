<x-mycomponents.layout>
    <div class="space-y-6">

        {{-- HERO HEADER --}}
        <section
            class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-900 to-blue-700 p-6 sm:p-8 shadow-xl">
            <div class="absolute -top-16 -right-10 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute -bottom-16 -left-10 h-40 w-40 rounded-full bg-cyan-300/10 blur-2xl"></div>

            <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl text-white">
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-blue-100">
                        <i class="fa-solid fa-gauge-high"></i>
                        Dashboard Admin
                    </span>

                    <h1 class="mt-4 text-3xl font-bold leading-tight sm:text-4xl">
                        Selamat Datang Kembali, {{ $namaAdmin }}
                    </h1>

                    <p class="mt-3 text-sm leading-6 text-blue-100 sm:text-base">
                        Pantau statistik utama klinik, distribusi data master, dan tren kunjungan pasien
                        dalam satu tampilan yang lebih rapi dan informatif.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:min-w-[340px]">
                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur">
                        <p class="text-xs uppercase tracking-wider text-blue-100">Tanggal</p>
                        <h3 class="mt-2 text-lg font-bold">{{ date('d M Y') }}</h3>
                        <p class="mt-1 text-xs text-blue-100">Update dashboard hari ini</p>
                    </div>

                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur">
                        <p class="text-xs uppercase tracking-wider text-blue-100">Periode Chart</p>
                        <h3 id="chartYear" class="mt-2 text-lg font-bold">{{ date('Y') }}</h3>
                        <p class="mt-1 text-xs text-blue-100">Kunjungan per bulan</p>
                    </div>

                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur">
                        <p class="text-xs uppercase tracking-wider text-blue-100">Sumber Data</p>
                        <h3 class="mt-2 text-lg font-bold">Realtime API</h3>
                        <p class="mt-1 text-xs text-blue-100">Diambil dari endpoint dashboard</p>
                    </div>

                    <div class="rounded-2xl border border-white/15 bg-white/10 p-4 text-white backdrop-blur">
                        <p class="text-xs uppercase tracking-wider text-blue-100">Statistik</p>
                        <h3 class="mt-2 text-lg font-bold">4 KPI Utama</h3>
                        <p class="mt-1 text-xs text-blue-100">Dokter, pasien, farmasi, obat</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- KPI CARDS --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            {{-- Dokter --}}
            <div
                class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Jumlah Dokter</p>
                        <h3 id="totalDokter" class="mt-3 text-3xl font-extrabold text-slate-900">0</h3>
                        <p class="mt-2 text-xs text-slate-400">Data master tenaga medis aktif</p>
                    </div>

                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-md">
                        <i class="fa-solid fa-user-doctor text-xl"></i>
                    </div>
                </div>

                <div class="mt-4 h-2 rounded-full bg-blue-50">
                    <div class="h-2 w-3/4 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600"></div>
                </div>
            </div>

            {{-- Pasien --}}
            <div
                class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Jumlah Pasien</p>
                        <h3 id="totalPasien" class="mt-3 text-3xl font-extrabold text-slate-900">0</h3>
                        <p class="mt-2 text-xs text-slate-400">Total pasien yang terdaftar</p>
                    </div>

                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-md">
                        <i class="fa-solid fa-users text-xl"></i>
                    </div>
                </div>

                <div class="mt-4 h-2 rounded-full bg-emerald-50">
                    <div class="h-2 w-4/5 rounded-full bg-gradient-to-r from-emerald-500 to-green-600"></div>
                </div>
            </div>

            {{-- Farmasi --}}
            <div
                class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Jumlah Farmasi</p>
                        <h3 id="totalFarmasi" class="mt-3 text-3xl font-extrabold text-slate-900">0</h3>
                        <p class="mt-2 text-xs text-slate-400">Tenaga farmasi yang tersedia</p>
                    </div>

                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-500 to-sky-600 text-white shadow-md">
                        <i class="fa-solid fa-prescription-bottle-medical text-xl"></i>
                    </div>
                </div>

                <div class="mt-4 h-2 rounded-full bg-cyan-50">
                    <div class="h-2 w-2/3 rounded-full bg-gradient-to-r from-cyan-500 to-sky-600"></div>
                </div>
            </div>

            {{-- Obat --}}
            <div
                class="group rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Obat Terdaftar</p>
                        <h3 id="totalObat" class="mt-3 text-3xl font-extrabold text-slate-900">0</h3>
                        <p class="mt-2 text-xs text-slate-400">Jumlah item obat di sistem</p>
                    </div>

                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 text-white shadow-md">
                        <i class="fa-solid fa-pills text-xl"></i>
                    </div>
                </div>

                <div class="mt-4 h-2 rounded-full bg-violet-50">
                    <div class="h-2 w-3/5 rounded-full bg-gradient-to-r from-violet-500 to-purple-600"></div>
                </div>
            </div>
        </section>

        {{-- MAIN CONTENT --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">

            {{-- CHART --}}
<div class="xl:col-span-8 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
    <div
        class="mb-5 flex flex-col gap-4 border-b border-slate-100 pb-4">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Grafik Kunjungan Pasien</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Visualisasi jumlah kunjungan pasien berdasarkan filter harian, mingguan, bulanan, dan tahunan.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span id="dashboardStatus"
                    class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                    <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                    Memuat data...
                </span>
            </div>
        </div>

        {{-- FILTER CHART --}}
        <div class="grid grid-cols-1 gap-3 xl:grid-cols-12">
            <div class="xl:col-span-3">
                <label for="filterPeriodeChart" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Periode
                </label>
                <select id="filterPeriodeChart"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                    <option value="harian">Harian</option>
                    <option value="mingguan">Mingguan</option>
                    <option value="bulanan">Bulanan</option>
                    <option value="tahunan" selected>Tahunan</option>
                </select>
            </div>

            <div id="filterHarianWrap" class="xl:col-span-3 hidden">
                <label for="filterTanggalChart" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Tanggal
                </label>
                <input type="date" id="filterTanggalChart" value="{{ date('Y-m-d') }}"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>

            <div id="filterMingguanWrap" class="xl:col-span-3 hidden">
                <label for="filterMingguChart" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Minggu
                </label>
                <input type="week" id="filterMingguChart"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>

            <div id="filterBulananWrap" class="xl:col-span-3 hidden">
                <label for="filterBulanChart" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Bulan
                </label>
                <input type="month" id="filterBulanChart" value="{{ date('Y-m') }}"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>

            <div id="filterTahunanWrap" class="xl:col-span-3">
                <label for="filterTahunChart" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                    Tahun
                </label>
                <input type="number" id="filterTahunChart" value="{{ date('Y') }}" min="2020" max="2100"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-700 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
            </div>

            <div class="xl:col-span-3 flex items-end">
                <button id="btnApplyChartFilter"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    <i class="fa-solid fa-filter"></i>
                    Terapkan Filter
                </button>
            </div>
        </div>
    </div>

    <div class="h-[350px] w-full">
        <canvas id="kunjunganChart"></canvas>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-3">
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

            {{-- SIDE PANEL --}}
            <div class="xl:col-span-4 space-y-6">

                {{-- DISTRIBUSI --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-slate-800">Distribusi Data</h3>
                        <p id="distributionNote" class="mt-1 text-sm text-slate-500">
                            Perbandingan relatif antar statistik utama saat ini.
                        </p>
                    </div>

                    <div class="space-y-4">
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
                                <span class="font-medium text-slate-600">Farmasi</span>
                                <span id="totalFarmasiMini" class="font-bold text-slate-800">0</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div id="barFarmasi"
                                    class="h-2 rounded-full bg-gradient-to-r from-cyan-500 to-sky-600"
                                    style="width: 0%"></div>
                            </div>
                        </div>

                        <div>
                            <div class="mb-2 flex items-center justify-between text-sm">
                                <span class="font-medium text-slate-600">Obat</span>
                                <span id="totalObatMini" class="font-bold text-slate-800">0</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                                <div id="barObat"
                                    class="h-2 rounded-full bg-gradient-to-r from-violet-500 to-purple-600"
                                    style="width: 0%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- INFO CARD --}}
                <div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-slate-800">Informasi Dashboard</h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Panel ini membantu admin membaca data dengan lebih cepat.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                            <div class="mt-1 text-indigo-600">
                                <i class="fa-solid fa-chart-column"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">Grafik Tahunan</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Data kunjungan ditampilkan per bulan agar lebih mudah melihat pola naik dan turun.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                            <div class="mt-1 text-emerald-600">
                                <i class="fa-solid fa-database"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">Statistik Master</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    KPI di atas mengambil data langsung dari tabel master yang sudah tersedia di sistem.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                            <div class="mt-1 text-amber-600">
                                <i class="fa-solid fa-circle-info"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">Catatan</p>
                                <p class="mt-1 text-xs leading-5 text-slate-500">
                                    Saat ini card obat menampilkan jumlah item obat. Kalau mau total stok fisik,
                                    endpoint-nya perlu diubah ke sum stok.
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
