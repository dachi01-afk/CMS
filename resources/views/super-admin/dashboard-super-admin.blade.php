<x-mycomponents.layout>
    <div class="space-y-6">

        {{-- HERO / HEADER --}}
        <section
            class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-emerald-900 to-emerald-700 px-6 py-7 shadow-xl">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute -top-10 -right-10 h-40 w-40 rounded-full bg-white blur-2xl"></div>
                <div class="absolute bottom-0 left-0 h-32 w-32 rounded-full bg-emerald-300 blur-2xl"></div>
            </div>

            <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-100 backdrop-blur">
                        <i class="fa-solid fa-shield-halved"></i>
                        Super Admin Panel
                    </div>

                    <h1 class="mt-4 text-3xl font-black tracking-tight text-white md:text-4xl">
                        Selamat Datang, {{ $namaSuperAdmin }}
                    </h1>

                    <p class="mt-3 max-w-xl text-sm leading-6 text-emerald-50/90 md:text-base">
                        Pusat kendali utama untuk memantau performa bisnis, aktivitas pengguna, transaksi,
                        serta stabilitas sistem secara menyeluruh.
                    </p>

                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <div
                            class="inline-flex items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur">
                            <i class="fa-regular fa-calendar-check text-emerald-200"></i>
                            <span>{{ now()->format('d F Y') }}</span>
                        </div>

                        <div
                            class="inline-flex items-center gap-2 rounded-xl border border-emerald-300/20 bg-emerald-400/10 px-4 py-2 text-sm font-medium text-emerald-100">
                            <span class="h-2.5 w-2.5 rounded-full bg-lime-300"></span>
                            Sistem {{ $serverStatus }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:w-[520px]">
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-xs uppercase tracking-wider text-emerald-100/80">Total User</p>
                        <h3 class="mt-2 text-2xl font-bold text-white">{{ $totalUser }}</h3>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-xs uppercase tracking-wider text-emerald-100/80">Admin Online</p>
                        <h3 class="mt-2 text-2xl font-bold text-white">{{ $totalAdminOnline }}</h3>
                    </div>
                </div>
            </div>
        </section>

        {{-- KPI UTAMA --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            {{-- Total Pasien --}}
            <a href="{{ route('super.admin.pasien.insight.index') }}" class="block">
                <div
                    class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-[40px] bg-blue-50"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Pasien</p>
                            <h3 id="totalPasien" class="mt-2 text-3xl font-extrabold text-slate-900">
                                {{ $totalPasien }}
                            </h3>
                            <p class="mt-2 text-xs text-slate-400">Klik untuk melihat insight pasien</p>
                        </div>
                        <div
                            class="rounded-2xl bg-blue-100 p-3 text-blue-600 transition duration-300 group-hover:scale-110">
                            <i class="fa-solid fa-users text-xl"></i>
                        </div>
                    </div>
                </div>
            </a>

            {{-- Total Transaksi --}}
            <a href="{{ route('super.admin.transaksi.insight.index') }}" class="block">
                <div
                    class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-[40px] bg-emerald-50"></div>
                    <div class="relative flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Transaksi</p>
                            <h3 class="mt-2 text-3xl font-extrabold text-slate-900">
                                {{ $totalTransaksi }}
                            </h3>
                            <p class="mt-2 text-xs text-slate-400">Klik untuk melihat detail transaksi</p>
                        </div>
                        <div
                            class="rounded-2xl bg-emerald-100 p-3 text-emerald-600 transition duration-300 group-hover:scale-110">
                            <i class="fa-solid fa-file-invoice-dollar text-xl"></i>
                        </div>
                    </div>
                </div>
            </a>

            {{-- Pendapatan --}}
            <div
                class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-[40px] bg-amber-50"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Pendapatan</p>
                        <h3 id="totalPendapatan" class="mt-2 text-3xl font-extrabold text-slate-900">
                            {{ $pendapatanRupiah }}
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Omset seluruh transaksi berhasil</p>
                    </div>
                    <div class="rounded-2xl bg-amber-100 p-3 text-amber-600">
                        <i class="fa-solid fa-sack-dollar text-xl"></i>
                    </div>
                </div>
            </div>

            {{-- Pasien Hari Ini --}}
            <a href="{{ route('super.admin.pasien.hari.ini.index') }}"
                class="group block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-violet-300 hover:shadow-lg">
                <div>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Pasien Hari Ini</p>
                            <h3 class="mt-2 text-3xl font-bold text-slate-800">{{ $pasienHariIni }}</h3>
                            <p class="mt-2 text-xs text-slate-500 group-hover:text-violet-600">
                                Kunjungan operasional hari ini • klik untuk melihat detail
                            </p>
                        </div>

                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-violet-100 text-violet-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 8v6M23 11h-6" />
                            </svg>
                        </div>
                    </div>
                </div>
            </a>
        </section>

        {{-- KPI TAMBAHAN --}}
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Admin Online</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-900">{{ $totalAdminOnline }}</h3>
                    </div>
                    <div class="rounded-xl bg-slate-100 p-3 text-slate-700">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Antrian Aktif</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-900">{{ $antrianAktif }}</h3>
                        <p class="mt-2 text-xs text-slate-400">Status: Pending, Waiting, Engaged, Payment</p>
                    </div>
                    <div class="rounded-xl bg-rose-100 p-3 text-rose-700">
                        <i class="fa-solid fa-wave-square"></i>
                    </div>
                </div>
            </div>
        </section>

        {{-- MAIN CONTENT --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            {{-- GRAFIK --}}
            <div id="kunjunganChartSection" data-chart-url="{{ route('super.admin.chart.kunjungan') }}"
                class="xl:col-span-2 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="flex items-center gap-2 text-lg font-bold text-slate-900">
                            <i class="fa-solid fa-chart-simple text-emerald-500"></i>
                            Analitik Sistem & Bisnis
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Grafik menampilkan komposisi kunjungan aktif, selesai, dan dibatalkan pada setiap periode.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div class="relative" id="reportDropdownWrapper">
                            <button type="button" id="btnToggleReportDropdown"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                                <i class="fa-solid fa-file-arrow-down"></i>
                                Report
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </button>

                            <div id="reportDropdownMenu"
                                class="absolute right-0 z-20 mt-2 hidden min-w-[190px] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                                <a id="btnReportPdfKunjungan"
                                    href="{{ route('super.admin.report.kunjungan.pdf', ['filter' => $chartFilter]) }}"
                                    data-base-url="{{ route('super.admin.report.kunjungan.pdf') }}"
                                    class="flex items-center gap-3 border-b border-slate-100 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                    <i class="fa-solid fa-file-pdf text-rose-500"></i>
                                    Download PDF
                                </a>

                                <a id="btnReportExcelKunjungan"
                                    href="{{ route('super.admin.report.kunjungan.excel', ['filter' => $chartFilter]) }}"
                                    data-base-url="{{ route('super.admin.report.kunjungan.excel') }}"
                                    class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                    <i class="fa-solid fa-file-excel text-emerald-600"></i>
                                    Download Excel
                                </a>
                            </div>
                        </div>

                        <select id="filterKunjunganChart"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                            {{-- <option value="harian" {{ $chartFilter === 'harian' ? 'selected' : '' }}>Harian</option>
                            <option value="mingguan" {{ $chartFilter === 'mingguan' ? 'selected' : '' }}>Mingguan
                            </option> --}}
                            <option value="bulanan" {{ $chartFilter === 'bulanan' ? 'selected' : '' }}>Bulanan
                            </option>
                            <option value="tahunan" {{ $chartFilter === 'tahunan' ? 'selected' : '' }}>Tahunan
                            </option>
                        </select>
                    </div>
                </div>

                <div class="mb-4 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                    <span
                        class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 font-medium text-emerald-700">
                        Periode:
                        <span id="kunjunganChartRange" class="ml-1">{{ $chartData['range_text'] }}</span>
                    </span>
                </div>

                <div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total Kunjungan</p>
                        <h3 id="summaryTotalKunjungan" class="mt-2 text-2xl font-bold text-slate-900">
                            {{ number_format($chartData['summary_total'], 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-amber-700">Aktif</p>
                        <h3 id="summaryKunjunganAktif" class="mt-2 text-2xl font-bold text-amber-600">
                            {{ number_format($chartData['summary_aktif'], 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">Selesai</p>
                        <h3 id="summaryKunjunganSelesai" class="mt-2 text-2xl font-bold text-emerald-600">
                            {{ number_format($chartData['summary_selesai'], 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-rose-700">Dibatalkan</p>
                        <h3 id="summaryKunjunganDibatalkan" class="mt-2 text-2xl font-bold text-rose-600">
                            {{ number_format($chartData['summary_dibatalkan'], 0, ',', '.') }}
                        </h3>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl bg-slate-50 p-4">
                    <div class="relative h-[430px] w-full">
                        <canvas id="managerChart" class="block h-full w-full"></canvas>
                    </div>
                </div>
            </div>

            <script id="kunjunganChartInitialData" type="application/json">
                @json($chartData)
            </script>

            {{-- CONTROL CENTER --}}
            <div class="space-y-6">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="flex items-center gap-2 text-lg font-bold text-slate-900">
                        <i class="fa-solid fa-sliders text-slate-700"></i>
                        Kontrol Cepat
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Shortcut area untuk operasi yang sering dipakai super admin.
                    </p>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <button type="button"
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left transition hover:border-emerald-300 hover:bg-emerald-50">
                            <i class="fa-solid fa-user-gear mb-3 text-lg text-emerald-600"></i>
                            <p class="text-sm font-semibold text-slate-800">Kelola User</p>
                            <p class="mt-1 text-xs text-slate-500">Hak akses & role</p>
                        </button>

                        <button type="button"
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left transition hover:border-rose-300 hover:bg-rose-50">
                            <i class="fa-solid fa-wave-square mb-3 text-lg text-rose-600"></i>
                            <p class="text-sm font-semibold text-slate-800">Monitoring Antrian</p>
                            <p class="mt-1 text-xs text-slate-500">Pantau pasien aktif hari ini</p>
                        </button>

                        <button type="button"
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left transition hover:border-amber-300 hover:bg-amber-50">
                            <i class="fa-solid fa-file-shield mb-3 text-lg text-amber-600"></i>
                            <p class="text-sm font-semibold text-slate-800">Audit Log</p>
                            <p class="mt-1 text-xs text-slate-500">Riwayat aktivitas</p>
                        </button>

                        <button type="button"
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left transition hover:border-purple-300 hover:bg-purple-50">
                            <i class="fa-solid fa-gear mb-3 text-lg text-purple-600"></i>
                            <p class="text-sm font-semibold text-slate-800">Pengaturan</p>
                            <p class="mt-1 text-xs text-slate-500">Konfigurasi sistem</p>
                        </button>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="flex items-center gap-2 text-lg font-bold text-slate-900">
                        <i class="fa-solid fa-server text-emerald-600"></i>
                        Status Sistem
                    </h2>

                    <div class="mt-5 space-y-4">
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Server Utama</p>
                                <p class="text-xs text-slate-500">Koneksi dan respons sistem</p>
                            </div>
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
                                {{ $serverStatus }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Antrian Hari Ini</p>
                                <p class="text-xs text-slate-500">Pasien yang masih aktif diproses</p>
                            </div>
                            <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-700">
                                {{ $antrianAktif }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- FOOTER INFO PANEL --}}
        <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Ringkasan Operasional</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Snapshot cepat kondisi sistem dan performa bisnis.
                        </p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 p-3 text-emerald-600">
                        <i class="fa-solid fa-chart-pie text-xl"></i>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-4">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wider text-slate-400">User Management</p>
                        <p class="mt-2 text-xl font-bold text-slate-900">{{ $totalUser }}</p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wider text-slate-400">Admin Online</p>
                        <p class="mt-2 text-xl font-bold text-slate-900">{{ $totalAdminOnline }}</p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wider text-slate-400">Antrian Live</p>
                        <p class="mt-2 text-xl font-bold text-slate-900">{{ $antrianAktif }}</p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs uppercase tracking-wider text-slate-400">Pasien Hari Ini</p>
                        <p class="mt-2 text-xl font-bold text-slate-900">{{ $pasienHariIni }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Aktivitas Super Admin</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Panel ini bisa kamu isi nanti dengan audit trail atau notifikasi sistem.
                        </p>
                    </div>
                    <div class="rounded-2xl bg-slate-100 p-3 text-slate-700">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                </div>

                <div class="mt-5 space-y-3">
                    <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                        <div class="mt-1 h-2.5 w-2.5 rounded-full bg-emerald-500"></div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Sistem berjalan normal</p>
                            <p class="text-xs text-slate-500">Seluruh modul utama terdeteksi aktif.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                        <div class="mt-1 h-2.5 w-2.5 rounded-full bg-rose-500"></div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Antrian aktif terpantau</p>
                            <p class="text-xs text-slate-500">Saat ini ada {{ $antrianAktif }} pasien aktif dalam
                                proses kunjungan.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4">
                        <div class="mt-1 h-2.5 w-2.5 rounded-full bg-blue-500"></div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Admin online</p>
                            <p class="text-xs text-slate-500">Saat ini ada {{ $totalAdminOnline }} admin yang sedang
                                aktif.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>
</x-mycomponents.layout>
@vite(['resources/js/super-admin/data-dashboard-super-admin.js'])
