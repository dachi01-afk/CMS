<x-mycomponents.layout>
    {{-- resources/views/perawat/dashboard.blade.php --}}

    <section class="space-y-6">

        {{-- ================= HEADER ================= --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 
               bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl px-4 md:px-6 py-4 shadow-sm">

            <div class="flex items-start gap-3">
                <div
                    class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl
                       bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                    <i class="fa-solid fa-user-nurse text-lg"></i>
                </div>
                <div>
                    <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                        Dashboard Perawat
                    </h2>
                    <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Ringkasan aktivitas triage, kunjungan pasien, dan jadwal dokter untuk hari ini.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div
                    class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-700 border border-slate-200 dark:border-slate-600">
                    <i class="fa-regular fa-calendar text-slate-500 text-sm"></i>
                    <span class="text-xs md:text-sm font-medium text-slate-700 dark:text-slate-100">
                        {{ now()->translatedFormat('d F Y') }}
                    </span>
                </div>
                <div class="inline-flex items-center gap-1 bg-slate-100 dark:bg-slate-700 rounded-xl p-1 text-xs">
                    <button
                        class="px-3 py-1 rounded-lg bg-white dark:bg-slate-800 shadow-sm text-sky-600 font-semibold">
                        Hari ini
                    </button>
                    <button class="px-3 py-1 rounded-lg text-slate-500 dark:text-slate-300 hover:text-sky-600">
                        Minggu ini
                    </button>
                    <button class="px-3 py-1 rounded-lg text-slate-500 dark:text-slate-300 hover:text-sky-600">
                        Bulan ini
                    </button>
                </div>
            </div>
        </div>

        {{-- ================= TOP STATS CARDS ================= --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            {{-- Pasien Menunggu Triage --}}
            <div
                class="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                <div class="flex items-center justify-between px-4 pt-4">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        Menunggu Triage
                    </p>
                    <span
                        class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-sky-100 text-sky-600 dark:bg-sky-900/40">
                        <i class="fa-solid fa-notes-medical text-sm"></i>
                    </span>
                </div>
                <div class="px-4 pb-4 pt-2">
                    <p class="text-2xl font-bold text-slate-800 dark:text-slate-50">
                        {{ $statMenungguTriage ?? 0 }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Pasien yang sudah terdaftar dan siap di-triage.
                    </p>
                </div>
            </div>

            {{-- Pasien Sedang Konsultasi --}}
            <div
                class="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-800 border border-amber-100 dark:border-amber-900/40 shadow-sm">
                <div class="flex items-center justify-between px-4 pt-4">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        Sedang Konsultasi
                    </p>
                    <span
                        class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/40">
                        <i class="fa-solid fa-stethoscope text-sm"></i>
                    </span>
                </div>
                <div class="px-4 pb-4 pt-2">
                    <p class="text-2xl font-bold text-slate-800 dark:text-slate-50">
                        {{ $statSedangKonsultasi ?? 0 }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Pasien yang sedang diperiksa di ruang dokter.
                    </p>
                </div>
            </div>

            {{-- Total Triage Hari Ini --}}
            <div
                class="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-800 border border-emerald-100 dark:border-emerald-900/40 shadow-sm">
                <div class="flex items-center justify-between px-4 pt-4">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        Triage Hari Ini
                    </p>
                    <span
                        class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40">
                        <i class="fa-solid fa-heart-pulse text-sm"></i>
                    </span>
                </div>
                <div class="px-4 pb-4 pt-2">
                    <p class="text-2xl font-bold text-slate-800 dark:text-slate-50">
                        {{ $statTotalTriageHariIni ?? 0 }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Total pasien yang sudah di-triage oleh perawat.
                    </p>
                </div>
            </div>

            {{-- Dokter Aktif Hari Ini --}}
            <div
                class="relative overflow-hidden rounded-2xl bg-white dark:bg-slate-800 border border-indigo-100 dark:border-indigo-900/40 shadow-sm">
                <div class="flex items-center justify-between px-4 pt-4">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">
                        Dokter Aktif Hari Ini
                    </p>
                    <span
                        class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40">
                        <i class="fa-solid fa-user-doctor text-sm"></i>
                    </span>
                </div>
                <div class="px-4 pb-4 pt-2">
                    <p class="text-2xl font-bold text-slate-800 dark:text-slate-50">
                        {{ $statDokterAktif ?? 0 }}
                    </p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                        Dokter yang memiliki jadwal praktik hari ini.
                    </p>
                </div>
            </div>
        </div>

        {{-- ================= MIDDLE ROW: GRAFIK & ANTRIAN ================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

            {{-- Grafik Line + Progress --}}
            <div
                class="lg:col-span-2 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm">
                <div
                    class="flex items-center justify-between px-4 md:px-5 py-3 border-b border-slate-100 dark:border-slate-700">
                    <div>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                            Grafik Triage 7 Hari Terakhir
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Pantau tren jumlah pasien yang di-triage oleh perawat.
                        </p>
                    </div>
                    <select
                        class="text-xs border border-slate-200 dark:border-slate-600 rounded-lg px-2 py-1 bg-slate-50 dark:bg-slate-700 text-slate-600 dark:text-slate-100">
                        <option>7 Hari</option>
                        <option>30 Hari</option>
                    </select>
                </div>
                <div class="p-4 md:p-5">
                    {{-- Grafik Line --}}
                    <div class="h-64">
                        <canvas id="triageChart" data-labels='@json($grafikTanggal)'
                            data-values='@json($grafikJumlah)'></canvas>
                    </div>

                    {{-- Progress animasi --}}
                    <div class="mt-3">
                        <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
                            <span>Persentase Triage Selesai</span>
                            <span>{{ $persenTriageSelesai }}%</span>
                        </div>
                        <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                            <div id="triageProgressInner"
                                class="h-2 rounded-full bg-gradient-to-r from-sky-500 to-emerald-500"
                                data-percent="{{ $persenTriageSelesai }}"></div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Ringkasan Antrian + Donut Chart --}}
            <div
                class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col">
                <div
                    class="px-4 md:px-5 py-3 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                        Ringkasan Antrian
                    </p>
                    <span
                        class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-900/40">
                        Realtime
                    </span>
                </div>

                <div class="p-4 md:p-5 space-y-4 text-sm">
                    {{-- Donut & angka di samping --}}
                    <div class="flex flex-col md:flex-row items-center gap-4">
                        <div class="w-full md:w-1/2 h-40">
                            <canvas id="chartStatusTriage" data-menunggu="{{ $statMenungguTriage ?? 0 }}"
                                data-triage="{{ $statTotalTriageHariIni ?? 0 }}"
                                data-konsultasi="{{ $statSedangKonsultasi ?? 0 }}"></canvas>
                        </div>

                        <div class="flex-1 space-y-2 text-xs md:text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Menunggu Triage</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-50">
                                    {{ $statMenungguTriage ?? 0 }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Selesai Triage</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-50">
                                    {{ $statTotalTriageHariIni ?? 0 }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Sedang Konsultasi</span>
                                <span class="font-semibold text-slate-800 dark:text-slate-50">
                                    {{ $statSedangKonsultasi ?? 0 }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Progress ringkas (non animasi) --}}
                    <div class="mt-2">
                        <p class="text-xs text-slate-500 mb-2">
                            Persentase pasien yang selesai di-triage dari total kunjungan hari ini
                        </p>
                        <div class="flex items-center gap-3">
                            <div class="flex-1 h-2 rounded-full bg-slate-100 dark:bg-slate-700 overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-emerald-400 to-sky-500"
                                    style="width: {{ $persenTriageSelesai ?? 0 }}%"></div>
                            </div>
                            <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                {{ $persenTriageSelesai ?? 0 }}%
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= BOTTOM ROW: TABLES ================= --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">

            {{-- Tabel Pasien Siap Triage (mini) --}}
            <div
                class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div
                    class="flex items-center justify-between px-4 md:px-5 py-3 border-b border-slate-100 dark:border-slate-700">
                    <div>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                            Pasien Siap Triage
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            5 pasien teratas yang menunggu penilaian triage.
                        </p>
                    </div>
                    <a href="{{ route('perawat.dashboard') }}"
                        class="text-xs font-semibold text-sky-600 hover:text-sky-700">
                        Lihat semua
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs md:text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/60 text-slate-500 dark:text-slate-200">
                            <tr>
                                <th class="px-4 py-2 text-left">No Antrian</th>
                                <th class="px-4 py-2 text-left">Nama Pasien</th>
                                <th class="px-4 py-2 text-left">Poli</th>
                                <th class="px-4 py-2 text-left">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listSiapTriage as $row)
                                <tr
                                    class="border-b border-slate-100 dark:border-slate-700/60 hover:bg-slate-50 dark:hover:bg-slate-700">
                                    <td class="px-4 py-2 font-semibold text-slate-800 dark:text-slate-50">
                                        {{ $row->no_antrian }}
                                    </td>
                                    <td class="px-4 py-2 text-slate-700 dark:text-slate-100">
                                        {{ $row->nama_pasien }}
                                    </td>
                                    <td class="px-4 py-2 text-slate-600 dark:text-slate-200">
                                        {{ $row->nama_poli }}
                                    </td>
                                    <td class="px-4 py-2">
                                        <button data-id="{{ $row->id }}"
                                            class="btn-start-triage inline-flex items-center gap-1 px-3 py-1.5 rounded-lg
                                               text-xs font-semibold bg-sky-500 hover:bg-sky-600 text-white">
                                            <i class="fa-solid fa-play text-[10px]"></i>
                                            Mulai
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4"
                                        class="px-4 py-4 text-center text-xs text-slate-500 dark:text-slate-400">
                                        Tidak ada pasien yang menunggu triage.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tabel Pasien Dalam Konsultasi --}}
            <div
                class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div
                    class="flex items-center justify-between px-4 md:px-5 py-3 border-b border-slate-100 dark:border-slate-700">
                    <div>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                            Pasien Dalam Konsultasi
                        </p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Monitoring pasien yang sedang berada di ruang dokter.
                        </p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-xs md:text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/60 text-slate-500 dark:text-slate-200">
                            <tr>
                                <th class="px-4 py-2 text-left">Pasien</th>
                                <th class="px-4 py-2 text-left">Dokter</th>
                                <th class="px-4 py-2 text-left">Poli</th>
                                {{-- <th class="px-4 py-2 text-left">Mulai</th> --}}
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($listSedangKonsultasi as $row)
                                <tr
                                    class="border-b border-slate-100 dark:border-slate-700/60 hover:bg-slate-50 dark:hover:bg-slate-700">
                                    <td class="px-4 py-2 text-slate-700 dark:text-slate-100">
                                        {{ $row->nama_pasien }}
                                    </td>
                                    <td class="px-4 py-2 text-slate-700 dark:text-slate-100">
                                        {{ $row->nama_dokter }}
                                    </td>
                                    <td class="px-4 py-2 text-slate-600 dark:text-slate-200">
                                        {{ $row->nama_poli }}
                                    </td>
                                    {{-- <td class="px-4 py-2 text-slate-500 dark:text-slate-300 text-xs">
                                        {{ $row->jam_mulai_konsultasi?->format('H:i') ?? '-' }}
                                    </td> --}}
                                    <td class="px-4 py-2 text-slate-500 dark:text-slate-300 text-xs">
                                        -
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4"
                                        class="px-4 py-4 text-center text-xs text-slate-500 dark:text-slate-400">
                                        Belum ada pasien yang sedang konsultasi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>
</x-mycomponents.layout>

@vite(['resources/js/perawat/dashboard.js'])
