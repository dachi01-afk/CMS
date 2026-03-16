<x-mycomponents.layout>
    <div class="space-y-6">

        {{-- HERO / HEADER --}}
        <section
            class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-sky-900 to-cyan-700 px-6 py-7 shadow-xl">
            <div class="absolute inset-0 opacity-10">
                <div class="absolute -top-10 -right-10 h-40 w-40 rounded-full bg-white blur-2xl"></div>
                <div class="absolute bottom-0 left-0 h-32 w-32 rounded-full bg-cyan-300 blur-2xl"></div>
            </div>

            <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-2xl">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-cyan-100 backdrop-blur">
                        <i class="fa-solid fa-user-nurse"></i>
                        Dashboard Perawat
                    </div>

                    <h1 class="mt-4 text-3xl font-black tracking-tight text-white md:text-4xl">
                        Selamat Datang, {{ $namaPerawat }}
                    </h1>

                    <p class="mt-3 max-w-xl text-sm leading-6 text-cyan-50/90 md:text-base">
                        Dashboard ini menampilkan beban pasien otomatis berdasarkan area tugas dokter dan poli,
                        sekaligus hasil kerja nyata perawat dari data EMR yang sudah diinput.
                    </p>

                    <div class="mt-5 flex flex-wrap items-center gap-3">
                        <div
                            class="inline-flex items-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-medium text-white backdrop-blur">
                            <i class="fa-regular fa-calendar-check text-cyan-200"></i>
                            <span>{{ now()->format('d F Y') }}</span>
                        </div>

                        <div
                            class="inline-flex items-center gap-2 rounded-xl border border-cyan-300/20 bg-cyan-400/10 px-4 py-2 text-sm font-medium text-cyan-100">
                            <span class="h-2.5 w-2.5 rounded-full bg-lime-300"></span>
                            Sistem {{ $serverStatus }}
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 lg:w-[520px]">
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-xs uppercase tracking-wider text-cyan-100/80">Pasien Area Tugas</p>
                        <h3 class="mt-2 text-2xl font-bold text-white">{{ $statPasienAreaTugasHariIni }}</h3>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-xs uppercase tracking-wider text-cyan-100/80">Sudah Ditangani Hari Ini</p>
                        <h3 class="mt-2 text-2xl font-bold text-white">{{ $statSudahDitanganiHariIni }}</h3>
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
                        <p class="text-sm font-medium text-slate-500">Pasien Area Tugas Hari Ini</p>
                        <h3 class="mt-2 text-3xl font-extrabold text-slate-900">
                            {{ $statPasienAreaTugasHariIni }}
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Pasien otomatis dari dokter & poli penugasan</p>
                    </div>
                    <div
                        class="rounded-2xl bg-blue-100 p-3 text-blue-600 transition duration-300 group-hover:scale-110">
                        <i class="fa-solid fa-users text-xl"></i>
                    </div>
                </div>
            </div>

            <div
                class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-[40px] bg-amber-50"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Menunggu Tindakan</p>
                        <h3 class="mt-2 text-3xl font-extrabold text-slate-900">
                            {{ $statMenungguTindakan }}
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Pasien yang masih waiting di area tugas Anda</p>
                    </div>
                    <div
                        class="rounded-2xl bg-amber-100 p-3 text-amber-600 transition duration-300 group-hover:scale-110">
                        <i class="fa-solid fa-hourglass-half text-xl"></i>
                    </div>
                </div>
            </div>

            <div
                class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-[40px] bg-violet-50"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Sedang Konsultasi</p>
                        <h3 class="mt-2 text-3xl font-extrabold text-slate-900">
                            {{ $statSedangKonsultasi }}
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Pasien engaged di dokter area tugas Anda</p>
                    </div>
                    <div
                        class="rounded-2xl bg-violet-100 p-3 text-violet-600 transition duration-300 group-hover:scale-110">
                        <i class="fa-solid fa-stethoscope text-xl"></i>
                    </div>
                </div>
            </div>

            <div
                class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-[40px] bg-emerald-50"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Sudah Ditangani Hari Ini</p>
                        <h3 class="mt-2 text-3xl font-extrabold text-slate-900">
                            {{ $statSudahDitanganiHariIni }}
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Pasien unik yang sudah punya input EMR hari ini</p>
                    </div>
                    <div
                        class="rounded-2xl bg-emerald-100 p-3 text-emerald-600 transition duration-300 group-hover:scale-110">
                        <i class="fa-solid fa-file-medical text-xl"></i>
                    </div>
                </div>
            </div>
        </section>

        {{-- KPI TAMBAHAN --}}
        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Kunjungan Area Tugas</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-900">{{ $statTotalKunjunganAreaTugas }}</h3>
                    </div>
                    <div class="rounded-xl bg-slate-100 p-3 text-slate-700">
                        <i class="fa-solid fa-hospital-user"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total EMR Saya</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-900">{{ $statTotalEmrSaya }}</h3>
                    </div>
                    <div class="rounded-xl bg-cyan-100 p-3 text-cyan-700">
                        <i class="fa-solid fa-notes-medical"></i>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Total Pasien Unik Saya</p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-900">{{ $statTotalPasienUnikSaya }}</h3>
                    </div>
                    <div class="rounded-xl bg-emerald-100 p-3 text-emerald-700">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
            </div>
        </section>

        {{-- MAIN CONTENT --}}
        <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            {{-- GRAFIK --}}
            <div id="perawatChartSection" data-chart-url="{{ route('perawat.chart') }}"
                class="xl:col-span-2 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="flex items-center gap-2 text-lg font-bold text-slate-900">
                            <i class="fa-solid fa-chart-simple text-cyan-500"></i>
                            Analitik Beban Kerja vs Penanganan
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Membandingkan jumlah kunjungan area tugas perawat dengan jumlah pasien yang benar-benar
                            sudah ditangani berdasarkan EMR.
                        </p>
                    </div>

                    <div>
                        <select id="filterPerawatChart"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
                            <option value="harian" {{ $chartFilter === 'harian' ? 'selected' : '' }}>Harian</option>
                            <option value="mingguan" {{ $chartFilter === 'mingguan' ? 'selected' : '' }}>Mingguan
                            </option>
                            <option value="bulanan" {{ $chartFilter === 'bulanan' ? 'selected' : '' }}>Bulanan
                            </option>
                            <option value="tahunan" {{ $chartFilter === 'tahunan' ? 'selected' : '' }}>Tahunan
                            </option>
                        </select>
                    </div>
                </div>

                <div class="mb-4 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                    <span class="inline-flex items-center rounded-full bg-cyan-50 px-3 py-1 font-medium text-cyan-700">
                        Periode:
                        <span id="perawatChartRange" class="ml-1">{{ $chartData['range_text'] }}</span>
                    </span>
                </div>

                <div class="mb-5 grid grid-cols-1 gap-3 lg:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total Area Tugas</p>
                        <h3 id="summaryAssignedTotal" class="mt-2 text-2xl font-bold text-slate-900">
                            {{ number_format($chartData['summary_assigned_total'], 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">Total Sudah Ditangani
                        </p>
                        <h3 id="summaryHandledTotal" class="mt-2 text-2xl font-bold text-emerald-600">
                            {{ number_format($chartData['summary_handled_total'], 0, ',', '.') }}
                        </h3>
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl bg-slate-50 p-4">
                    <div class="relative h-[430px] w-full">
                        <canvas id="perawatDashboardChart" class="block h-full w-full"></canvas>
                    </div>
                </div>
            </div>

            <script id="perawatChartInitialData" type="application/json">
                @json($chartData)
            </script>

            {{-- SIDEBAR --}}
            <div class="space-y-6">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="flex items-center gap-2 text-lg font-bold text-slate-900">
                        <i class="fa-solid fa-sliders text-slate-700"></i>
                        Ringkasan Kinerja
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Perbandingan beban kerja otomatis dan hasil kerja nyata.
                    </p>

                    <div class="mt-5 space-y-4">
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Pasien Area Tugas</p>
                                <p class="text-xs text-slate-500">Pasien otomatis hari ini</p>
                            </div>
                            <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700">
                                {{ $statPasienAreaTugasHariIni }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Sudah Ditangani</p>
                                <p class="text-xs text-slate-500">Sudah memiliki EMR hari ini</p>
                            </div>
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
                                {{ $statSudahDitanganiHariIni }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Progress Penanganan</p>
                                <p class="text-xs text-slate-500">Persentase pasien area tugas yang sudah ditangani</p>
                            </div>
                            <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-bold text-violet-700">
                                {{ $persenPenangananHariIni }}%
                            </span>
                        </div>

                        <div class="mt-2">
                            <div class="mb-2 flex items-center justify-between text-xs text-slate-500">
                                <span>Progres hari ini</span>
                                <span>{{ $persenPenangananHariIni }}%</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200">
                                <div class="h-2 rounded-full bg-gradient-to-r from-cyan-500 to-emerald-500"
                                    style="width: {{ $persenPenangananHariIni }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                    <h2 class="flex items-center gap-2 text-lg font-bold text-slate-900">
                        <i class="fa-solid fa-hospital text-cyan-600"></i>
                        Poli Teratas
                    </h2>

                    <div class="mt-5 space-y-3">
                        @forelse ($poliTeratas as $item)
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-800">{{ $item->nama_poli ?? '-' }}
                                        </p>
                                        <p class="text-xs text-slate-500">Pasien unik: {{ $item->total_pasien }}</p>
                                    </div>
                                    <span class="rounded-full bg-cyan-100 px-3 py-1 text-xs font-bold text-cyan-700">
                                        {{ $item->total_emr }} EMR
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-500">
                                Belum ada data poli.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        {{-- FOOTER TABLE --}}
        <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- PASIEN AREA TUGAS --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Pasien Menunggu Tindakan</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            5 pasien teratas dari area dokter & poli penugasan Anda.
                        </p>
                    </div>
                    <div class="rounded-2xl bg-blue-50 p-3 text-blue-600">
                        <i class="fa-solid fa-user-clock text-xl"></i>
                    </div>
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left">No Antrian</th>
                                <th class="px-4 py-3 text-left">Pasien</th>
                                <th class="px-4 py-3 text-left">Poli</th>
                                <th class="px-4 py-3 text-left">Dokter</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listSiapTriage as $item)
                                <tr class="border-b border-slate-100">
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $item->no_antrian }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $item->nama_pasien }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $item->nama_poli }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $item->nama_dokter }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-4 text-center text-sm text-slate-500">
                                        Tidak ada pasien waiting di area tugas Anda.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- EMR TERBARU --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">EMR Terbaru Yang Saya Input</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            5 data EMR terbaru yang benar-benar diinput oleh Anda.
                        </p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 p-3 text-emerald-600">
                        <i class="fa-solid fa-file-waveform text-xl"></i>
                    </div>
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Pasien</th>
                                <th class="px-4 py-3 text-left">Poli</th>
                                <th class="px-4 py-3 text-left">Dokter</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listPasienTerbaruDitangani as $item)
                                <tr class="border-b border-slate-100">
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-slate-800">
                                        {{ $item->nama_pasien ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ $item->nama_poli ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ $item->nama_dokter ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-4 text-center text-sm text-slate-500">
                                        Belum ada input EMR.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</x-mycomponents.layout>

@vite(['resources/js/perawat/dashboard.js'])
