<section class="space-y-5">

    {{-- HEADER + FILTER / EXPORT --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
               bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl px-4 md:px-6 py-4 shadow-sm">

        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl
                       bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-chart-line text-lg"></i>
            </div>

            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Laporan Kunjungan
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Pantau dan ekspor laporan kunjungan pasien berdasarkan periode waktu yang diinginkan.
                </p>
            </div>
        </div>

        {{-- FILTER PERIODE + EXPORT --}}
        <form action="{{ route('laporan.export') }}" method="GET"
            class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 md:gap-3 w-full md:w-auto">

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <label for="periode" class="text-sm font-medium text-slate-600 dark:text-slate-300 whitespace-nowrap">
                    Periode
                </label>
                <select name="periode" id="periode"
                    class="w-full sm:w-40 border border-slate-300 dark:border-slate-600 text-sm rounded-lg
                               bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200
                               focus:ring-sky-500 focus:border-sky-500 px-3 py-2">
                    <option value="">Semua</option>
                    <option value="minggu">Per Minggu</option>
                    <option value="bulan">Per Bulan</option>
                    <option value="tahun">Per Tahun</option>
                </select>
            </div>

            <button type="submit"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium
                           rounded-lg shadow-sm
                           bg-gradient-to-r from-sky-500 to-cyan-500 text-white
                           hover:from-sky-600 hover:to-cyan-600
                           focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-1">
                <i class="fa-solid fa-file-export text-xs"></i>
                <span>Export Data</span>
            </button>
        </form>
    </div>

    {{-- CARD TABEL KUNJUNGAN --}}
    <div
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl shadow-sm overflow-hidden">

        {{-- CONTROL BAR (PAGE LENGTH + SEARCH) --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                   px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">

            <div class="flex items-center gap-2">
                <label for="kunjungan_pageLength" class="text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">
                    Tampilkan
                </label>
                <select id="kunjungan_pageLength"
                    class="border border-slate-300 dark:border-slate-600 text-sm rounded-lg
                               bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200
                               focus:ring-sky-500 focus:border-sky-500 px-2 py-1 w-24">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-sm text-slate-500 dark:text-slate-400 hidden sm:inline">
                    entri per halaman
                </span>
            </div>

            <div class="relative w-full md:w-60">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" id="kunjungan_searchInput"
                    class="block w-full pl-8 pr-3 py-2 text-sm
                              border border-slate-300 dark:border-slate-600 rounded-lg
                              bg-slate-50 dark:bg-slate-900
                              text-slate-800 dark:text-slate-100
                              placeholder-slate-400
                              focus:ring-sky-500 focus:border-sky-500"
                    placeholder="Cari kunjungan, dokter, pasien...">
            </div>
        </div>

        {{-- TABEL --}}
        <div class="overflow-x-auto">
            <table id="kunjunganTable" class="min-w-full text-sm text-left text-slate-700 dark:text-slate-100">
                <thead class="text-xs uppercase bg-sky-500 dark:bg-sky-600 text-white tracking-wide">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">No. Antrian</th>
                        <th class="px-6 py-3">Nama Dokter</th>
                        <th class="px-6 py-3">Nama Pasien</th>
                        <th class="px-6 py-3">Tanggal Kunjungan</th>
                        <th class="px-6 py-3">Keluhan Awal</th>
                        <th class="px-6 py-3">Status</th>
                        {{-- <th class="px-6 py-3 text-center">Aksi</th> --}}
                    </tr>
                </thead>
                <tbody>
                    {{-- Diisi via DataTable / JS --}}
                </tbody>
            </table>
        </div>

        {{-- FOOTER INFO + PAGINATION --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                   px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700">

            <div id="kunjungan_customInfo" class="text-sm text-slate-600 dark:text-slate-300">
                {{-- Info custom datatable (ex: Menampilkan 1–10 dari 120 data) --}}
            </div>

            <ul id="kunjungan_customPagination" class="inline-flex items-center gap-px text-sm">
                {{-- Pagination custom --}}
            </ul>
        </div>
    </div>

</section>
@vite(['resources/js/admin/laporan/laporan_kunjungan.js'])
