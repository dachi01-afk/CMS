{{-- CARD PENGGUNAAN BHP --}}
<div class="space-y-4">

    {{-- Header + Filter --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-gradient-to-r from-sky-500/10 via-blue-500/5 to-emerald-500/5 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3">
        <div class="space-y-1">
            <h2 class="text-base md:text-lg font-semibold text-slate-800 dark:text-slate-50">
                Penggunaan Bahan Habis Pakai
            </h2>
            <p class="text-[11px] md:text-xs text-slate-500 dark:text-slate-300">
                Rekap penggunaan BHP. Atur rentang tanggal & cari nama barang untuk melihat detail pemakaian.
            </p>
            <p class="text-[11px] md:text-[10px] text-sky-600 dark:text-sky-300 flex items-center gap-1">
                <i class="fa-regular fa-clock text-[10px]"></i>
                <span>Last refresh:
                    <span id="text-last-refresh" class="font-medium">-</span>
                </span>
            </p>
        </div>

        {{-- Filter area --}}
        <div class="flex flex-col md:flex-row gap-2 md:items-end">
            <div class="flex flex-col">
                <label for="filter_start_date"
                    class="text-[11px] font-medium text-slate-600 dark:text-slate-200 mb-1">Dari Tanggal</label>
                <input type="date" id="filter_start_date"
                    class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700
                           bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-100 focus:ring-2
                           focus:ring-sky-500 focus:border-sky-500" />
            </div>

            <div class="flex flex-col">
                <label for="filter_end_date"
                    class="text-[11px] font-medium text-slate-600 dark:text-slate-200 mb-1">Sampai Tanggal</label>
                <input type="date" id="filter_end_date"
                    class="px-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700
                           bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-100 focus:ring-2
                           focus:ring-sky-500 focus:border-sky-500" />
            </div>

            <div class="flex flex-col md:w-56">
                <label for="filter_nama_barang"
                    class="text-[11px] font-medium text-slate-600 dark:text-slate-200 mb-1">Cari Nama Barang</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-2 text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-[10px]"></i>
                    </span>
                    <input type="text" id="filter_nama_barang"
                        class="pl-7 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700
                               bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-100
                               focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Ketik minimal 2 huruf..." />
                </div>
            </div>

            <div class="flex gap-2">
                <button id="btn-filter-penggunaan-barang" type="button"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg
                           bg-emerald-500 text-white hover:bg-emerald-600 focus:ring-2 focus:ring-emerald-400">
                    <i class="fa-solid fa-filter text-[10px]"></i>
                    <span>Filter</span>
                </button>

                <button id="btn-reset-penggunaan-barang" type="button"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg
                           bg-slate-100 text-slate-700 hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-100
                           focus:ring-2 focus:ring-slate-300">
                    <i class="fa-solid fa-rotate-left text-[10px]"></i>
                    <span>Reset</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Toolbar bawah header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">

        <!-- Page length -->
        <div class="flex items-center gap-2 text-sm">
            <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>
            <select id="penggunaan-bhp-pageLength"
                class="border border-slate-300 dark:border-slate-600 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500
                           bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-1 w-28">
                <option value="10">10 baris</option>
                <option value="25">25 baris</option>
                <option value="50">50 baris</option>
                <option value="100">100 baris</option>
            </select>
            <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">per halaman</span>
        </div>

        <div class="flex items-center gap-2 text-[11px] text-slate-500 dark:text-slate-300">
            <span
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-sky-50 dark:bg-sky-900/40
                         text-sky-700 dark:text-sky-200 border border-sky-100 dark:border-sky-800">
                <i class="fa-solid fa-pills text-[9px]"></i>
                <span>Rekap per barang</span>
            </span>
            <span class="hidden md:inline">•</span>
            <span>Data otomatis mengikuti filter di atas</span>
        </div>

        <div class="flex gap-2 justify-end">
            <button id="btn-export-penggunaan-barang" type="button"
                data-url-export="{{ route('export.data.penggunaan.obat') }}"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg
           border border-emerald-500 text-emerald-600 hover:bg-emerald-50
           dark:border-emerald-400 dark:text-emerald-200 dark:hover:bg-emerald-900/20">
                <i class="fa-solid fa-file-excel text-[10px]"></i>
                <span>Export</span>
            </button>

            <button id="btn-print-penggunaan-barang" type="button"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg
                       border border-sky-500 text-sky-600 hover:bg-sky-50
                       dark:border-sky-400 dark:text-sky-200 dark:hover:bg-sky-900/20">
                <i class="fa-solid fa-print text-[10px]"></i>
                <span>Cetak</span>
            </button>
        </div>
    </div>

    {{-- TABLE WRAPPER --}}
    <div
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table id="table-penggunaan-barang" class="min-w-full text-xs text-left text-slate-600 dark:text-slate-100"
                data-url-export="{{ route('export.data.penggunaan.bhp') }}"
                data-url-print="{{ route('print.pdf.data.penggunaan.bhp') }}">
                <thead
                    class="text-[11px] uppercase bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-200">
                    <tr>
                        <th class="px-3 py-2 !text-center">#</th>
                        <th class="px-3 py-2 text-left">Nama Barang</th>
                        <th class="px-3 py-2 !text-center">Penggunaan Umum</th>
                        <th class="px-3 py-2 !text-center">Nominal BHP Umum</th>
                        <th class="px-3 py-2 !text-center">Sisa Stok BHP</th>
                    </tr>
                </thead>
                <tbody class="text-[11px]">
                    {{-- DataTables akan isi sendiri --}}
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">
            <div id="penggunaan-bhp-customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300">
            </div>

            <ul id="penggunaan-bhp-customPagination"
                class="inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
            </ul>
        </div>
    </div>
</div>

@vite(['resources/js/farmasi/penggunaan-bhp/data-penggunaan-bhp.js'])
