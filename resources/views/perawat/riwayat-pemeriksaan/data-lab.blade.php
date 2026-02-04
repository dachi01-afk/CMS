<section class="space-y-5">
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
               bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl px-4 md:px-6 py-4 shadow-sm">

        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl  bg-gradient-to-r from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-vial text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Riwayat Hasil Lab
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Menampilkan hasil lab yang sudah tersimpan untuk perawat yang login.
                </p>
            </div>
        </div>
    </div>

    <div
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                    px-4 md:px-6 py-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/40">
            <div class="flex items-center gap-2">
                <span class="text-xs md:text-sm text-slate-600 dark:text-slate-300">Tampilkan</span>
                <select id="lab_pageLength"
                    class="border border-slate-300 dark:border-slate-600 text-xs md:text-sm rounded-xl
                           focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-slate-800 px-2 py-1.5 w-24">
                    <option value="10">10 data</option>
                    <option value="25">25 data</option>
                    <option value="50">50 data</option>
                    <option value="100">100 data</option>
                </select>
                <span class="text-xs md:text-sm text-slate-600 dark:text-slate-300 hidden md:inline">per halaman</span>
            </div>

            <div class="w-full md:w-auto">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" id="lab_searchInput"
                        class="block w-full md:w-64 lg:w-72 pl-8 pr-3 py-2 text-sm rounded-xl border border-slate-300 dark:border-slate-600
                               bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-50
                               placeholder:text-slate-400 dark:placeholder:text-slate-500
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Cari pasien, dokter, tanggal, pemeriksaan...">
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="tabelRiwayatLab"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100 min-w-[900px]">
                <thead class="text-xs uppercase  bg-gradient-to-r from-sky-500 to-teal-500 text-white">
                    <tr>
                        <th class="px-5 py-3 text-[11px] font-semibold tracking-wide">No</th>
                        <th class="px-5 py-3 text-[11px] font-semibold tracking-wide">No Order</th>
                        <th class="px-5 py-3 text-[11px] font-semibold tracking-wide">Nama Pasien</th>
                        <th class="px-5 py-3 text-[11px] font-semibold tracking-wide">Dokter</th>
                        <th class="px-5 py-3 text-[11px] font-semibold tracking-wide">Tanggal</th>
                        <th class="px-5 py-3 text-[11px] font-semibold tracking-wide">Ringkasan</th>
                        <th class="px-5 py-3 text-[11px] font-semibold tracking-wide text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
            </table>
        </div>

        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                    px-4 md:px-6 py-3 border-t border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/40">
            <div id="lab_customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>
            <ul id="lab_customPagination" class="inline-flex items-center gap-1 text-xs md:text-sm"></ul>
        </div>
    </div>
</section>

@vite(['resources/js/perawat/riwayat-pemeriksaan/data-lab.js'])
