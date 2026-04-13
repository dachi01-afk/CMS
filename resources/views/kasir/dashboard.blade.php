<x-mycomponents.layout>
    <div class="space-y-6">

        {{-- HERO / HEADER --}}
        <section
            class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-900 to-blue-700 px-6 py-7 shadow-xl">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute -top-10 -right-10 h-40 w-40 rounded-full bg-white blur-2xl"></div>
                <div class="absolute bottom-0 left-0 h-32 w-32 rounded-full bg-blue-300 blur-2xl"></div>
            </div>

            <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-blue-100 backdrop-blur">
                        <i class="fa-solid fa-cash-register"></i>
                        Kasir Panel
                    </div>

                    <h1 class="mt-4 text-3xl font-black tracking-tight text-white md:text-4xl">
                        Selamat Datang, {{ $namaKasir }}
                    </h1>

                    <p class="mt-3 max-w-xl text-sm leading-6 text-blue-50/90 md:text-base">
                        Dashboard transaksi kasir untuk memantau pembayaran alur default, transaksi obat,
                        dan transaksi layanan non-pemeriksaan secara cepat dan terpusat.
                    </p>

                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <div
                            class="inline-flex items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur">
                            <i class="fa-regular fa-calendar-check text-blue-200"></i>
                            <span>{{ now()->format('d F Y') }}</span>
                        </div>

                        <div
                            class="inline-flex items-center gap-2 rounded-xl border border-blue-300/20 bg-blue-400/10 px-4 py-2 text-sm font-medium text-blue-100">
                            <span class="h-2.5 w-2.5 rounded-full bg-lime-300"></span>
                            Sistem {{ $serverStatus }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:w-[520px]">
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-xs uppercase tracking-wider text-blue-100/80">Transaksi Hari Ini</p>
                        <h3 class="mt-2 text-2xl font-bold text-white">
                            {{ number_format($summary['total_transaksi_hari_ini'], 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-xs uppercase tracking-wider text-blue-100/80">Total Transaksi</p>
                        <h3 class="mt-2 text-2xl font-bold text-white">
                            {{ number_format($summary['total_transaksi'], 0, ',', '.') }}
                        </h3>
                    </div>
                </div>
            </div>
        </section>

        {{-- KPI UTAMA --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div
                class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-[40px] bg-blue-50"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Transaksi Hari Ini</p>
                        <h3 class="mt-2 text-3xl font-extrabold text-slate-900">
                            {{ number_format($summary['total_transaksi_hari_ini'], 0, ',', '.') }}
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Gabungan pembayaran, obat, dan layanan</p>
                    </div>
                    <div class="rounded-2xl bg-blue-100 p-3 text-blue-600">
                        <i class="fa-solid fa-receipt text-xl"></i>
                    </div>
                </div>
            </div>

            <div
                class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-[40px] bg-emerald-50"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Seluruh Transaksi</p>
                        <h3 class="mt-2 text-3xl font-extrabold text-slate-900">
                            {{ number_format($summary['total_transaksi'], 0, ',', '.') }}
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Akumulasi semua transaksi kasir</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-100 p-3 text-emerald-600">
                        <i class="fa-solid fa-chart-column text-xl"></i>
                    </div>
                </div>
            </div>

            <div
                class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-[40px] bg-amber-50"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Pendapatan Hari Ini</p>
                        <h3 class="mt-2 text-3xl font-extrabold text-slate-900">
                            Rp {{ number_format($summary['pendapatan_hari_ini'], 0, ',', '.') }}
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Hanya transaksi berstatus berhasil dibayar</p>
                    </div>
                    <div class="rounded-2xl bg-amber-100 p-3 text-amber-600">
                        <i class="fa-solid fa-sack-dollar text-xl"></i>
                    </div>
                </div>
            </div>

            <div
                class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-[40px] bg-violet-50"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Pendapatan</p>
                        <h3 class="mt-2 text-3xl font-extrabold text-slate-900">
                            Rp {{ number_format($summary['pendapatan_total'], 0, ',', '.') }}
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Total omset semua transaksi berhasil</p>
                    </div>
                    <div class="rounded-2xl bg-violet-100 p-3 text-violet-600">
                        <i class="fa-solid fa-wallet text-xl"></i>
                    </div>
                </div>
            </div>
        </section>

        {{-- KPI 3 TRANSAKSI --}}
        <section class="grid grid-cols-1 gap-4 xl:grid-cols-3">
            <a href="{{ route('kasir.insight.pembayaran') }}"
                class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-blue-300 hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Pembayaran Alur Default</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-900">
                            {{ number_format($summary['pembayaran_total'], 0, ',', '.') }}
                        </h3>
                        <p class="mt-1 text-xs text-slate-400">
                            Hari ini: {{ number_format($summary['pembayaran_hari_ini'], 0, ',', '.') }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl bg-blue-100 p-3 text-blue-600 transition duration-300 group-hover:scale-110">
                        <i class="fa-solid fa-file-invoice-dollar text-xl"></i>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Pendapatan</p>
                    <p class="mt-2 text-lg font-bold text-slate-900">
                        Rp {{ number_format($summary['pembayaran_pendapatan_total'], 0, ',', '.') }}
                    </p>
                    <p class="mt-2 text-xs text-blue-600 font-medium">Klik untuk melihat detail transaksi</p>
                </div>
            </a>

            <a href="{{ route('kasir.insight.obat') }}"
                class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-emerald-300 hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Transaksi Obat</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-900">
                            {{ number_format($summary['obat_total'], 0, ',', '.') }}
                        </h3>
                        <p class="mt-1 text-xs text-slate-400">
                            Hari ini: {{ number_format($summary['obat_hari_ini'], 0, ',', '.') }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl bg-emerald-100 p-3 text-emerald-600 transition duration-300 group-hover:scale-110">
                        <i class="fa-solid fa-pills text-xl"></i>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Pendapatan</p>
                    <p class="mt-2 text-lg font-bold text-slate-900">
                        Rp {{ number_format($summary['obat_pendapatan_total'], 0, ',', '.') }}
                    </p>
                    <p class="mt-2 text-xs text-emerald-600 font-medium">Klik untuk melihat detail transaksi</p>
                </div>
            </a>

            <a href="{{ route('kasir.insight.layanan') }}"
                class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-amber-300 hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Transaksi Layanan</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-900">
                            {{ number_format($summary['layanan_total'], 0, ',', '.') }}
                        </h3>
                        <p class="mt-1 text-xs text-slate-400">
                            Hari ini: {{ number_format($summary['layanan_hari_ini'], 0, ',', '.') }}
                        </p>
                    </div>
                    <div
                        class="rounded-2xl bg-amber-100 p-3 text-amber-600 transition duration-300 group-hover:scale-110">
                        <i class="fa-solid fa-stethoscope text-xl"></i>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl bg-slate-50 p-4">
                    <p class="text-xs uppercase tracking-wide text-slate-500">Pendapatan</p>
                    <p class="mt-2 text-lg font-bold text-slate-900">
                        Rp {{ number_format($summary['layanan_pendapatan_total'], 0, ',', '.') }}
                    </p>
                    <p class="mt-2 text-xs text-amber-600 font-medium">Klik untuk melihat detail transaksi</p>
                </div>
            </a>
        </section>

        {{-- MAIN CONTENT --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            {{-- GRAFIK --}}
            <div id="transaksiChartSection" data-chart-url="{{ route('kasir.chart.transaksi') }}"
                class="xl:col-span-2 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="flex items-center gap-2 text-lg font-bold text-slate-900">
                            <i class="fa-solid fa-chart-simple text-blue-500"></i>
                            Analitik Transaksi Kasir
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Grafik menampilkan komposisi transaksi pembayaran, obat, dan layanan non-pemeriksaan.
                        </p>
                    </div>

                    <select id="filterTransaksiChart"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="harian" {{ $chartFilter === 'harian' ? 'selected' : '' }}>Harian</option>
                        <option value="mingguan" {{ $chartFilter === 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                        <option value="bulanan" {{ $chartFilter === 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                        <option value="tahunan" {{ $chartFilter === 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                    </select>
                </div>

                <div class="mb-4 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 font-medium text-blue-700">
                        Periode:
                        <span id="transaksiChartRange" class="ml-1">{{ $chartData['range_text'] }}</span>
                    </span>
                </div>

                <div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total Transaksi</p>
                        <h3 id="summaryTotalTransaksi" class="mt-2 text-2xl font-bold text-slate-900">
                            {{ number_format($chartData['summary_total'], 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-blue-700">Pembayaran</p>
                        <h3 id="summaryPembayaran" class="mt-2 text-2xl font-bold text-blue-600">
                            {{ number_format($chartData['summary_pembayaran'], 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">Obat</p>
                        <h3 id="summaryObat" class="mt-2 text-2xl font-bold text-emerald-600">
                            {{ number_format($chartData['summary_obat'], 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-amber-700">Layanan</p>
                        <h3 id="summaryLayanan" class="mt-2 text-2xl font-bold text-amber-600">
                            {{ number_format($chartData['summary_layanan'], 0, ',', '.') }}
                        </h3>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl bg-slate-50 p-4">
                    <div class="relative h-[430px] w-full">
                        <canvas id="kasirTransactionChart" class="block h-full w-full"></canvas>
                    </div>
                </div>
            </div>

            <script id="transaksiChartInitialData" type="application/json">
                @json($chartData)
            </script>

            {{-- CONTROL / INFO --}}
            <div class="space-y-6">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="flex items-center gap-2 text-lg font-bold text-slate-900">
                        <i class="fa-solid fa-bolt text-slate-700"></i>
                        Akses Cepat
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Shortcut menuju transaksi utama kasir.
                    </p>

                    <div class="mt-5 grid grid-cols-1 gap-3">
                        <a href="{{ route('kasir.pembayaran', ['tab' => 'transaksi-menunggu']) }}"
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left transition hover:border-blue-300 hover:bg-blue-50">
                            <i class="fa-solid fa-file-invoice-dollar mb-3 text-lg text-blue-600"></i>
                            <p class="text-sm font-semibold text-slate-800">Pembayaran</p>
                            <p class="mt-1 text-xs text-slate-500">Transaksi alur default pasien</p>
                        </a>

                        <a href="{{ route('kasir.pembayaran', ['tab' => 'transaksi-obat']) }}"
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left transition hover:border-emerald-300 hover:bg-emerald-50">
                            <i class="fa-solid fa-pills mb-3 text-lg text-emerald-600"></i>
                            <p class="text-sm font-semibold text-slate-800">Penjualan Obat</p>
                            <p class="mt-1 text-xs text-slate-500">Pasien beli obat tanpa poli</p>
                        </a>

                        <a href="{{ route('kasir.pembayaran', ['tab' => 'transaksi-layanan']) }}"
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-left transition hover:border-amber-300 hover:bg-amber-50">
                            <i class="fa-solid fa-stethoscope mb-3 text-lg text-amber-600"></i>
                            <p class="text-sm font-semibold text-slate-800">Order Layanan</p>
                            <p class="mt-1 text-xs text-slate-500">Hanya layanan non kategori pemeriksaan</p>
                        </a>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="flex items-center gap-2 text-lg font-bold text-slate-900">
                        <i class="fa-solid fa-circle-info text-blue-600"></i>
                        Ringkasan Hari Ini
                    </h2>

                    <div class="mt-5 space-y-4">
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Pembayaran Hari Ini</p>
                                <p class="text-xs text-slate-500">Alur default kunjungan</p>
                            </div>
                            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700">
                                {{ number_format($summary['pembayaran_hari_ini'], 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Obat Hari Ini</p>
                                <p class="text-xs text-slate-500">Penjualan obat langsung</p>
                            </div>
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
                                {{ number_format($summary['obat_hari_ini'], 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Layanan Hari Ini</p>
                                <p class="text-xs text-slate-500">Tanpa kategori pemeriksaan</p>
                            </div>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                                {{ number_format($summary['layanan_hari_ini'], 0, ',', '.') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Pendapatan Hari Ini</p>
                                <p class="text-xs text-slate-500">Total transaksi berhasil dibayar</p>
                            </div>
                            <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-bold text-violet-700">
                                Rp {{ number_format($summary['pendapatan_hari_ini'], 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </div>

</x-mycomponents.layout>
@vite(['resources/js/kasir/dashboard.js'])
