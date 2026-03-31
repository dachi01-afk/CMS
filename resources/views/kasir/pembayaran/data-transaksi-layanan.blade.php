<section class="space-y-4 sm:space-y-5">

    {{-- Header --}}
    <div
        class="flex flex-col gap-3 sm:gap-4 md:flex-row md:items-center md:justify-between
               bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl px-4 sm:px-5 md:px-6 py-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                       bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-briefcase-medical text-base sm:text-lg"></i>
            </div>

            <div class="min-w-0">
                <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50 leading-tight">
                    Transaksi Layanan
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Rekap order layanan pasien, termasuk total pembayaran, metode pembayaran, dan status transaksi.
                </p>
            </div>
        </div>
    </div>

    {{-- Card Table --}}
    <div
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl shadow-sm overflow-hidden">

        {{-- Filter / Search --}}
        <div
            class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between
                   px-4 sm:px-5 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">

            <div class="flex flex-wrap items-center gap-2 text-sm">
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>

                <select id="transaksi-layanan-page-length"
                    class="border border-slate-300 dark:border-slate-600 text-sm rounded-lg
                           focus:ring-sky-500 focus:border-sky-500
                           bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100
                           px-3 py-2 w-full sm:w-32">
                    <option value="10">10 baris</option>
                    <option value="25">25 baris</option>
                    <option value="50">50 baris</option>
                    <option value="100">100 baris</option>
                </select>

                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">per halaman</span>
            </div>

            <div class="w-full lg:w-auto">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                    </span>

                    <input type="text" id="transaksi-layanan-search-input"
                        class="block w-full lg:w-80 xl:w-96 pl-9 pr-3 py-2.5 text-sm
                               text-slate-800 dark:text-slate-100
                               border border-slate-300 dark:border-slate-600 rounded-lg
                               bg-slate-50 dark:bg-slate-700
                               focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari pasien, metode, atau kode transaksi...">
                </div>

                <p class="mt-1 text-[11px] sm:text-xs text-slate-400 dark:text-slate-500 leading-relaxed">
                    Contoh: <span class="italic">Nama pasien, metode pembayaran, kode transaksi</span>
                </p>
            </div>
        </div>

        {{-- Table --}}
        <div class="w-full overflow-x-auto">
            <div class="min-w-full inline-block align-middle">
                <table id="transaksiLayananTable"
                    class="w-full min-w-[900px] lg:min-w-[1100px] text-xs sm:text-sm text-left
                           text-slate-700 dark:text-slate-100 border-t border-slate-100 dark:border-slate-700">
                    <thead
                        class="text-[10px] sm:text-xs font-semibold uppercase
                               bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600
                               text-white tracking-wide">
                        <tr>
                            <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">No</th>
                            <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Kode Transaksi</th>
                            <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Nama Pasien</th>
                            <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Total Bayar</th>
                            <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Metode Pembayaran</th>
                            <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Tanggal Order</th>
                            <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Status</th>
                            <th class="px-3 sm:px-4 md:px-6 py-3 text-center whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
                </table>
            </div>
        </div>

        {{-- Footer Pagination --}}
        <div
            class="flex flex-col gap-3 sm:gap-4 lg:flex-row lg:items-center lg:justify-between
                   px-4 sm:px-5 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700
                   bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">

            <div id="transaksi-layanan-custom-info"
                class="text-xs sm:text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
            </div>

            <div class="w-full lg:w-auto overflow-x-auto">
                <ul id="transaksi-layanan-custom-paginate"
                    class="inline-flex min-w-max items-center text-xs sm:text-sm isolate rounded-lg
                           border border-slate-200 dark:border-slate-600 overflow-hidden bg-white dark:bg-slate-700">
                </ul>
            </div>
        </div>
    </div>

</section>

@vite(['resources/js/kasir/pembayaran/data-transaksi-layanan.js'])
