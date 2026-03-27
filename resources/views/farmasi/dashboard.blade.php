<x-mycomponents.layout>
    <div class="space-y-5">

        {{-- HERO HEADER --}}
        <div
            class="relative overflow-hidden rounded-[24px] bg-gradient-to-r from-slate-950 via-emerald-900 to-emerald-600 px-5 py-6 shadow-lg lg:px-6 lg:py-7">
            <div class="grid grid-cols-1 gap-5 xl:grid-cols-12 xl:items-center">
                <div class="xl:col-span-8">
                    <div
                        class="mb-4 inline-flex items-center rounded-full border border-white/20 bg-white/10 px-4 py-1 text-[11px] font-bold uppercase tracking-[0.2em] text-white/90">
                        Apotek Panel
                    </div>

                    <h1 class="text-3xl font-extrabold leading-tight text-white lg:text-4xl">
                        Selamat Datang, {{ $namaFarmasi }}
                    </h1>

                    <p class="mt-3 max-w-2xl text-sm leading-7 text-emerald-50/95 lg:text-base">
                        Pusat kendali operasional apotek untuk memantau stok obat, penjualan, dan alert stok kritis
                        secara menyeluruh.
                    </p>

                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <div
                            class="inline-flex items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold text-white shadow-sm">
                            <i class="fa-regular fa-calendar-days"></i>
                            <span>{{ date('d M Y') }}</span>
                        </div>

                        <div
                            class="inline-flex items-center gap-2 rounded-xl border border-lime-300/20 bg-lime-400/10 px-4 py-2 text-sm font-semibold text-lime-100 shadow-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-lime-300"></span>
                            <span>Data Dashboard Apotek</span>
                        </div>

                        <div
                            class="inline-flex items-center gap-2 rounded-xl border border-emerald-300/20 bg-emerald-400/10 px-4 py-2 text-sm font-semibold text-emerald-100 shadow-sm">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-300"></span>
                            <span id="heroFilterBadge">Grafik berhasil dimuat</span>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-4">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-white/60">
                                Mode Aktif
                            </p>
                            <h3 id="heroModePeriode" class="mt-2 text-2xl font-extrabold text-white">Harian</h3>
                            <p class="mt-1 text-xs text-white/70">Siap ganti filter periode</p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-sm">
                            <p class="text-[11px] font-bold uppercase tracking-wide text-white/60">
                                5 KPI Utama
                            </p>
                            <h3 class="mt-2 text-2xl font-extrabold text-white">Apotek</h3>
                            <p class="mt-1 text-xs text-white/70">Stok, transaksi, alert, pemasukan, total penjualan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- FILTER GRAFIK --}}
        <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                        <i class="fa-solid fa-filter"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900">Filter Grafik Penjualan Obat</h2>
                        <p class="text-sm text-slate-500">
                            Atur tampilan grafik penjualan obat berdasarkan periode harian, mingguan, bulanan, dan
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
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <a href="{{ route('farmasi.stok.obat.index') }}"
                class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Stok Obat</p>
                        <h3 id="totalStokObat" class="mt-2 text-4xl font-extrabold tracking-tight text-slate-900">0</h3>
                        <p class="mt-2 text-xs text-slate-400 group-hover:text-indigo-600">
                            Klik untuk lihat data stok obat
                        </p>
                    </div>
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-[22px] bg-indigo-50 text-indigo-500 transition group-hover:bg-indigo-100">
                        <i class="fa-solid fa-boxes-stacked text-2xl"></i>
                    </div>
                </div>
            </a>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Stok Menipis</p>
                        <h3 id="stokMenipis" class="mt-2 text-4xl font-extrabold tracking-tight text-amber-500">0</h3>
                        <p id="stokMenipisInfo" class="mt-2 text-xs text-slate-400">Perlu restock segera</p>
                    </div>
                    <div class="flex h-16 w-16 items-center justify-center rounded-[22px] bg-amber-50 text-amber-500">
                        <i class="fa-solid fa-triangle-exclamation text-2xl"></i>
                    </div>
                </div>
            </div>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Stok Habis</p>
                        <h3 id="stokHabis" class="mt-2 text-4xl font-extrabold tracking-tight text-rose-500">0</h3>
                        <p class="mt-2 text-xs text-slate-400">Harus ditindaklanjuti</p>
                    </div>
                    <div class="flex h-16 w-16 items-center justify-center rounded-[22px] bg-rose-50 text-rose-500">
                        <i class="fa-solid fa-circle-xmark text-2xl"></i>
                    </div>
                </div>
            </div>

            <a href="{{ route('farmasi.penjualan.obat.hari.ini.index') }}"
                class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Pemasukan Hari Ini</p>
                        <h3 id="pemasukanHariIni" class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">
                            Rp 0
                        </h3>
                        <p id="totalKeseluruhanTransaksiObat"
                            class="mt-2 text-xs text-slate-400 group-hover:text-emerald-600">
                            Klik untuk lihat penjualan obat hari ini
                        </p>
                    </div>
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-[22px] bg-emerald-50 text-emerald-500 transition group-hover:bg-emerald-100">
                        <i class="fa-solid fa-wallet text-2xl"></i>
                    </div>
                </div>
            </a>

            <a href="{{ route('farmasi.penjualan.obat.index') }}"
                class="group rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-md">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Penjualan Obat</p>
                        <h3 id="totalPenjualanObat" class="mt-2 text-4xl font-extrabold tracking-tight text-blue-600">
                            0</h3>
                        <p class="mt-2 text-xs text-slate-400 group-hover:text-blue-600">
                            Klik untuk lihat semua data penjualan obat
                        </p>
                    </div>
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-[22px] bg-blue-50 text-blue-500 transition group-hover:bg-blue-100">
                        <i class="fa-solid fa-cash-register text-2xl"></i>
                    </div>
                </div>
            </a>
        </div>

        {{-- GRAFIK + PANEL SAMPING --}}
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            <div class="xl:col-span-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-3xl font-extrabold text-slate-900">Grafik Penjualan Obat</h2>
                        <p id="chartTitle" class="mt-1 text-sm text-slate-500">
                            Visualisasi jumlah transaksi penjualan obat berdasarkan filter periode yang aktif.
                        </p>
                    </div>

                    <span
                        class="inline-flex items-center rounded-full bg-blue-50 px-4 py-2 text-xs font-bold text-blue-600">
                        <i class="fa-solid fa-chart-column mr-2"></i> Bar Chart
                    </span>
                </div>

                <div class="relative w-full" style="height: 380px;">
                    <canvas id="chartPenjualanObat"></canvas>
                </div>
            </div>

            <div class="xl:col-span-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4">
                    <h2 class="text-2xl font-extrabold text-slate-900">Distribusi Apotek</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Perbandingan cepat antara transaksi harian dan alert stok utama.
                    </p>
                </div>

                <div class="space-y-4">
                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm font-semibold text-slate-700">
                            <span>Transaksi Hari Ini</span>
                            <span id="quickTransaksiHariIni">0</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-slate-100">
                            <div id="barTransaksiHariIni" class="h-2 rounded-full bg-blue-500" style="width: 0%;">
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm font-semibold text-slate-700">
                            <span>Stok Menipis</span>
                            <span id="quickStokMenipis">0</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-slate-100">
                            <div id="barStokMenipis" class="h-2 rounded-full bg-amber-400" style="width: 0%;"></div>
                        </div>
                    </div>

                    <div>
                        <div class="mb-1 flex items-center justify-between text-sm font-semibold text-slate-700">
                            <span>Stok Habis</span>
                            <span id="quickStokHabis">0</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-slate-100">
                            <div id="barStokHabis" class="h-2 rounded-full bg-rose-500" style="width: 0%;"></div>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="text-sm font-bold text-slate-700">Alert Stok Obat</h3>
                            <span class="rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-bold text-rose-600">
                                Prioritas
                            </span>
                        </div>

                        <div id="quickCriticalStockList" class="space-y-3">
                            <div class="text-sm text-slate-400">Memuat data stok kritis...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABEL --}}
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
            <div class="xl:col-span-7 rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900">Transaksi Obat Terbaru</h2>
                        <p class="mt-1 text-sm text-slate-500">Aktivitas penjualan terakhir</p>
                    </div>

                    <div id="dashboardUpdatedAt" class="text-xs font-medium text-slate-400">
                        Update terakhir: -
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                                <th class="px-5 py-3">Kode</th>
                                <th class="px-5 py-3">Pasien</th>
                                <th class="px-5 py-3">Tanggal</th>
                                <th class="px-5 py-3">Status</th>
                                <th class="px-5 py-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody id="recentTransactionTableBody" class="divide-y divide-slate-100">
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-slate-400">
                                    Memuat transaksi terbaru...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="xl:col-span-5 rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-100 px-5 py-4">
                    <h2 class="text-lg font-extrabold text-slate-900">Daftar Stok Kritis</h2>
                    <p class="mt-1 text-sm text-slate-500">Obat dengan stok menipis atau habis</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-slate-50">
                            <tr class="text-left text-xs font-bold uppercase tracking-wide text-slate-500">
                                <th class="px-5 py-3">Nama Obat</th>
                                <th class="px-5 py-3">Stok</th>
                                <th class="px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody id="criticalStockTableBody" class="divide-y divide-slate-100">
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-center text-sm text-slate-400">
                                    Memuat data stok kritis...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</x-mycomponents.layout>
@vite(['resources/js/farmasi/dashboard.js'])
