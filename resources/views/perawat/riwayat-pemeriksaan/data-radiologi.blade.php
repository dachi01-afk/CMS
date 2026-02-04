<section class="space-y-5">
    {{-- HEADER CARD --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
                rounded-2xl px-4 md:px-6 py-4 shadow-sm">

        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl
                        bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-x-ray text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Riwayat Radiologi
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Hanya menampilkan hasil radiologi yang <span class="font-semibold">diinput oleh kamu</span>.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 md:gap-3">
            <div
                class="inline-flex items-center gap-2 rounded-full bg-sky-50 text-sky-700 px-3 py-1
                        dark:bg-sky-900/40 dark:text-sky-100 border border-sky-100 dark:border-sky-700">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-semibold tracking-wide uppercase">Status: Tersimpan</span>
            </div>
        </div>
    </div>

    {{-- CARD TABLE --}}
    <div
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
                rounded-2xl shadow-sm overflow-hidden">

        {{-- TOP TOOLBAR --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                    px-4 md:px-6 py-3 border-b border-slate-100 dark:border-slate-700
                    bg-slate-50/60 dark:bg-slate-900/40">

            <div class="flex items-center gap-2">
                <span class="text-xs md:text-sm text-slate-600 dark:text-slate-300">Tampilkan</span>
                <select id="riwayatRadiologi_pageLength"
                    class="border border-slate-300 dark:border-slate-600 text-xs md:text-sm rounded-xl
                               focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-slate-800
                               px-2 py-1.5 w-24">
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
                    <input type="text" id="riwayatRadiologi_searchInput"
                        class="block w-full md:w-64 lg:w-72 pl-8 pr-3 py-2 text-sm
                                  rounded-xl border border-slate-300 dark:border-slate-600
                                  bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-50
                                  placeholder:text-slate-400 dark:placeholder:text-slate-500
                                  focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari pasien, dokter, no order...">
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="overflow-x-auto">
            <table id="tabelRiwayatRadiologi"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100 min-w-[900px]">
                <thead class="text-xs uppercase bg-gradient-to-r from-sky-500 to-teal-500 text-white">
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

        {{-- FOOTER --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                    px-4 md:px-6 py-3 border-t border-slate-100 dark:border-slate-700
                    bg-slate-50/60 dark:bg-slate-900/40">
            <div id="riwayatRadiologi_customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>
            <ul id="riwayatRadiologi_customPagination" class="inline-flex items-center gap-1 text-xs md:text-sm"></ul>
        </div>
    </div>
</section>
