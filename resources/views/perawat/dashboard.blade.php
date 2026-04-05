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
                        Dashboard ini menampilkan grafik pasien yang sudah dilayani, ringkasan antrian,
                        jumlah pasien selesai dilayani, dan pasien yang sedang dalam konsultasi.
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
                        <p class="text-xs uppercase tracking-wider text-cyan-100/80">Ringkasan Antrian</p>
                        <h3 class="mt-2 text-2xl font-bold text-white">{{ $statMenungguTindakan }}</h3>
                    </div>

                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-xs uppercase tracking-wider text-cyan-100/80">Pasien Selesai Dilayani</p>
                        <h3 class="mt-2 text-2xl font-bold text-white">{{ $statSudahDitangani }}</h3>
                    </div>
                </div>
            </div>
        </section>

        {{-- KPI UTAMA --}}
        <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div
                class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-[40px] bg-sky-50"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Pasien Hari Ini</p>
                        <h3 class="mt-2 text-3xl font-extrabold text-slate-900">
                            {{ $statPasienHariIni }}
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Jumlah pasien yang masuk ke area tugas perawat hari ini
                        </p>
                    </div>
                    <div class="rounded-2xl bg-sky-100 p-3 text-sky-600 transition duration-300 group-hover:scale-110">
                        <i class="fa-solid fa-calendar-day text-xl"></i>
                    </div>
                </div>
            </div>

            <button type="button" id="btnOpenModalRingkasanAntrian"
                class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg text-left w-full">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-[40px] bg-amber-50"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Ringkasan Antrian</p>
                        <h3 class="mt-2 text-3xl font-extrabold text-slate-900">
                            {{ $statMenungguTindakan }}
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Jumlah pasien yang masih menunggu dilayani perawat</p>
                        <p class="mt-3 text-xs font-semibold text-amber-600">Klik untuk melihat detail</p>
                    </div>
                    <div
                        class="rounded-2xl bg-amber-100 p-3 text-amber-600 transition duration-300 group-hover:scale-110">
                        <i class="fa-solid fa-hourglass-half text-xl"></i>
                    </div>
                </div>
            </button>

            <div
                class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                <div class="absolute right-0 top-0 h-24 w-24 rounded-bl-[40px] bg-violet-50"></div>
                <div class="relative flex items-start justify-between">
                    <div>
                        <p class="text-sm font-medium text-slate-500">Pasien Dalam Konsultasi</p>
                        <h3 class="mt-2 text-3xl font-extrabold text-slate-900">
                            {{ $statSedangKonsultasi }}
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Jumlah pasien yang sedang dalam proses konsultasi</p>
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
                        <p class="text-sm font-medium text-slate-500">Pasien Selesai Dilayani</p>
                        <h3 class="mt-2 text-3xl font-extrabold text-slate-900">
                            {{ $statSudahDitangani }}
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Jumlah pasien yang sudah selesai dilayani oleh perawat
                        </p>
                    </div>
                    <div
                        class="rounded-2xl bg-emerald-100 p-3 text-emerald-600 transition duration-300 group-hover:scale-110">
                        <i class="fa-solid fa-file-medical text-xl"></i>
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
                            Grafik Pasien Yang Sudah Dilayani
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Menampilkan jumlah pasien yang sudah dilayani oleh perawat berdasarkan periode yang dipilih.
                        </p>
                    </div>

                    <div>
                        <select id="filterPerawatChart"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-100">
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
                    <span class="inline-flex items-center rounded-full bg-cyan-50 px-3 py-1 font-medium text-cyan-700">
                        Periode:
                        <span id="perawatChartRange" class="ml-1">{{ $chartData['range_text'] }}</span>
                    </span>
                </div>

                <div class="mb-5 grid grid-cols-1 gap-3 lg:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Total Ringkasan Antrian
                        </p>
                        <h3 class="mt-2 text-2xl font-bold text-slate-900">
                            {{ number_format($statMenungguTindakan, 0, ',', '.') }}
                        </h3>
                    </div>

                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                        <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">Total Pasien Selesai
                            Dilayani</p>
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
                        <i class="fa-solid fa-clipboard-list text-slate-700"></i>
                        Ringkasan Dashboard Perawat
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Ringkasan data utama pelayanan perawat pada hari ini.
                    </p>

                    <div class="mt-5 space-y-4">
                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Ringkasan Antrian</p>
                                <p class="text-xs text-slate-500">Pasien yang masih menunggu</p>
                            </div>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                                {{ $statMenungguTindakan }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Pasien Dalam Konsultasi</p>
                                <p class="text-xs text-slate-500">Pasien yang sedang dalam proses konsultasi</p>
                            </div>
                            <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-bold text-violet-700">
                                {{ $statSedangKonsultasi }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Pasien Selesai Dilayani</p>
                                <p class="text-xs text-slate-500">Pasien yang sudah selesai dilayani</p>
                            </div>
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
                                {{ $statSudahDitangani }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- FOOTER TABLE --}}
        <section class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- RINGKASAN ANTRIAN --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Ringkasan Antrian Pasien</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Daftar pasien yang masih berada dalam antrian pelayanan perawat.
                        </p>
                    </div>
                    <div class="rounded-2xl bg-amber-50 p-3 text-amber-600">
                        <i class="fa-solid fa-user-clock text-xl"></i>
                    </div>
                </div>

                <div class="mt-5 overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Tanggal Kunjungan</th>
                                <th class="px-4 py-3 text-left">No Antrian</th>
                                <th class="px-4 py-3 text-left">Pasien</th>
                                <th class="px-4 py-3 text-left">Poli</th>
                                <th class="px-4 py-3 text-left">Dokter</th>
                                @if ($isSuperAdmin)
                                    <th class="px-4 py-3 text-left">Perawat</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listSiapTriage as $item)
                                <tr class="border-b border-slate-100">
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                        {{ $item->tanggal_kunjungan_text ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-slate-800">{{ $item->no_antrian }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $item->nama_pasien }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $item->nama_poli }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ $item->nama_dokter }}</td>
                                    @if ($isSuperAdmin)
                                        <td class="px-4 py-3 text-slate-600">
                                            {{ $item->nama_perawat ?? 'Belum Dilayani' }}</td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isSuperAdmin ? 6 : 5 }}"
                                        class="px-4 py-4 text-center text-sm text-slate-500">
                                        Tidak ada pasien dalam antrian saat ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PASIEN SELESAI DILAYANI --}}
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Pasien Selesai Dilayani</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Daftar pasien yang sudah selesai dilayani oleh perawat.
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
                                <th class="px-4 py-3 text-left">Tanggal Kunjungan</th>
                                <th class="px-4 py-3 text-left">Pasien</th>
                                <th class="px-4 py-3 text-left">Poli</th>
                                <th class="px-4 py-3 text-left">Dokter</th>
                                @if ($isSuperAdmin)
                                    <th class="px-4 py-3 text-left">Perawat</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listPasienTerbaruDitangani as $item)
                                <tr class="border-b border-slate-100">
                                    <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                        {{ $item->tanggal_kunjungan_text ?? '-' }}
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
                                    @if ($isSuperAdmin)
                                        <td class="px-4 py-3 text-slate-600">
                                            {{ $item->nama_perawat ?? 'Belum Dilayani' }}
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isSuperAdmin ? 5 : 4 }}"
                                        class="px-4 py-4 text-center text-sm text-slate-500">
                                        Belum ada pasien yang selesai dilayani.
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

{{-- MODAL RINGKASAN ANTRIAN --}}
<div id="modalRingkasanAntrian"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4 py-6">
    <div class="w-full max-w-5xl rounded-3xl bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Detail Ringkasan Antrian</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Daftar pasien yang masih menunggu dilayani perawat berdasarkan area tugas dokter dan poli.
                </p>
            </div>

            <button type="button" id="btnCloseModalRingkasanAntrian"
                class="rounded-xl bg-slate-100 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-200">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="max-h-[70vh] overflow-y-auto px-6 py-5">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left">Tanggal Kunjungan</th>
                            <th class="px-4 py-3 text-left">No Antrian</th>
                            <th class="px-4 py-3 text-left">Pasien</th>
                            <th class="px-4 py-3 text-left">Poli</th>
                            <th class="px-4 py-3 text-left">Dokter</th>
                            @if ($isSuperAdmin)
                                <th class="px-4 py-3 text-left">Perawat</th>
                            @endif
                            <th class="px-4 py-3 text-left">Jadwal</th>
                            <th class="px-4 py-3 text-left">Keluhan</th>
                            <th class="px-4 py-3 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($detailRingkasanAntrian as $item)
                            <tr class="border-b border-slate-100">
                                <td class="px-4 py-3 text-slate-600 whitespace-nowrap">
                                    {{ $item->tanggal_kunjungan_text ?? '-' }}
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-800">
                                    {{ $item->no_antrian }}
                                </td>
                                <td class="px-4 py-3 text-slate-700">
                                    {{ $item->nama_pasien }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $item->nama_poli }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $item->nama_dokter }}
                                </td>
                                @if ($isSuperAdmin)
                                    <td class="px-4 py-3 text-slate-600">
                                        {{ $item->nama_perawat ?? 'Belum Dilayani' }}
                                    </td>
                                @endif
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $item->jadwal }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $item->keluhan }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700">
                                        {{ $item->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSuperAdmin ? 9 : 8 }}"
                                    class="px-4 py-5 text-center text-sm text-slate-500">
                                    Tidak ada data antrian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end border-t border-slate-200 px-6 py-4">
            <button type="button" id="btnCloseModalRingkasanAntrianFooter"
                class="rounded-xl bg-slate-800 px-4 py-2 text-sm font-medium text-white hover:bg-slate-900">
                Tutup
            </button>
        </div>
    </div>
</div>

@vite(['resources/js/perawat/dashboard.js'])
