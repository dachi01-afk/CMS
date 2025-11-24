<section class="space-y-5">

    {{-- HEADER + DESKRIPSI --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
               bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl px-4 md:px-6 py-4 shadow-sm">

        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl
                       bg-gradient-to-br from-sky-500 to-cyan-500 text-white shadow-md">
                <i class="fa-solid fa-notes-medical text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Daftar Pasien dengan EMR
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola dan akses ringkasan rekam medis elektronik setiap pasien di klinik.
                </p>
            </div>
        </div>

        <div class="flex flex-col items-start md:items-end gap-1 text-xs text-slate-500 dark:text-slate-400">
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full
                         bg-sky-50 text-sky-700 border border-sky-100
                         dark:bg-sky-900/30 dark:text-sky-100 dark:border-sky-800">
                <i class="fa-solid fa-shield-heart text-[11px]"></i>
                <span>Data EMR terlindungi & rahasia</span>
            </span>
        </div>
    </div>

    {{-- CARD TABEL EMR --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm">

        {{-- Toolbar --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                   px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">

            {{-- Page length --}}
            <div class="flex items-center gap-2 text-sm">
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>
                <select id="emr-pageLength"
                    class="border border-slate-300 dark:border-slate-600 text-sm rounded-lg
                           focus:ring-sky-500 focus:border-sky-500
                           bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100
                           px-2 py-1 w-28">
                    <option value="10">10 baris</option>
                    <option value="25">25 baris</option>
                    <option value="50">50 baris</option>
                    <option value="100">100 baris</option>
                </select>
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">per halaman</span>
            </div>

            {{-- Search --}}
            <div class="w-full md:w-auto">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                    </span>
                    <input type="text" id="emr-searchInput"
                        class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm
                               text-slate-800 dark:text-slate-100
                               border border-slate-300 dark:border-slate-600 rounded-lg
                               bg-slate-50 dark:bg-slate-700
                               focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari nama pasien / No EMR...">
                </div>
                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Contoh: <span class="italic">Nama pasien, EMR-00123, dsb.</span>
                </p>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table id="emrTable"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100
                       border-t border-slate-100 dark:border-slate-700">
                <thead
                    class="text-xs font-semibold uppercase
                           bg-gradient-to-r from-sky-500 via-cyan-500 to-sky-600
                           text-white tracking-wide">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">No EMR</th>
                        <th class="px-6 py-3">Nama Pasien</th>
                        <th class="px-6 py-3">Total EMR</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between
                   px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700
                   bg-slate-50/70 dark:bg-slate-800/60 gap-3 rounded-b-2xl">

            <div id="emr-customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300">
                {{-- info datatables akan di-inject via JS --}}
            </div>

            <ul id="emr-customPagination"
                class="inline-flex items-center gap-0 text-sm isolate rounded-lg
                       border border-slate-200 dark:border-slate-600 overflow-hidden">
                {{-- pagination datatables akan di-inject via JS --}}
            </ul>
        </div>
    </div>
</section>

@vite(['resources/js/admin/dataMedisPasien/rekam_medis_elektronik.js'])
