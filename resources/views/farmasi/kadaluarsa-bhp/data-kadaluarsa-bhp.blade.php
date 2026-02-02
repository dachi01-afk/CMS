<div class="px-2 md:px-4 lg:px-6 py-4 md:py-6 space-y-6">

    {{-- TITLE + DESCRIPTION --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h1 class="text-lg md:text-xl font-semibold text-slate-900 dark:text-slate-50">
                Kadaluarsa BHP
            </h1>
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">
                Pantau BHP yang sudah mendekati tanggal kadaluarsa agar stok dapat diputar tepat waktu.
            </p>
        </div>
    </div>

    {{-- CARD WARNING KADALUARSA --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm">
        <div class="flex items-center justify-between px-4 md:px-6 py-3 border-b border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-100 text-amber-600 text-xs">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>
                <div>
                    <h2 class="text-sm md:text-base font-semibold text-slate-900 dark:text-slate-50">
                        Warning Kadaluarsa BHP
                    </h2>
                    <p class="text-[11px] md:text-xs text-slate-500 dark:text-slate-400">
                        Menampilkan BHP yang akan kadaluarsa ≤ <span id="warningThresholdText">7</span> hari ke depan.
                    </p>
                </div>
            </div>
            <span
                class="inline-flex items-center rounded-full bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 text-[11px] md:text-xs">
                <i class="fa-regular fa-clock mr-1"></i> Update otomatis saat halaman dibuka
            </span>
        </div>

        <div class="px-4 md:px-6 py-4 overflow-x-auto">
            <table
                class="min-w-full text-xs md:text-sm border border-slate-100 dark:border-slate-800 rounded-xl overflow-hidden">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr class="text-[11px] md:text-xs text-slate-600 dark:text-slate-300">
                        <th class="px-3 py-2 text-left font-semibold w-[25%]">Nama Obat</th>
                        <th class="px-3 py-2 text-left font-semibold w-[25%]">Tanggal Kadaluarsa</th>
                        <th class="px-3 py-2 text-left font-semibold w-[25%]">No Batch</th>
                        <th class="px-3 py-2 text-left font-semibold w-[25%]">Stok</th>
                    </tr>
                </thead>
                <tbody id="warningKadaluarsaBody" class="divide-y divide-slate-100 dark:divide-slate-800">
                    {{-- diisi via axios --}}
                    <tr>
                        <td colspan="3" class="px-3 py-3 text-center text-[11px] md:text-xs text-slate-400">
                            Memuat data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- CARD DATA KADALUARSA OBAT (DATATABLES) --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm">
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-b border-slate-100 dark:border-slate-700">
            <div>
                <h2 class="text-sm md:text-base font-semibold text-slate-900 dark:text-slate-50">
                    Data Kadaluarsa BHP
                </h2>
                <p class="text-[11px] md:text-xs text-slate-500 dark:text-slate-400 mt-1">
                    Last update:
                    <span id="lastUpdateKadaluarsa" class="font-medium text-slate-700 dark:text-slate-200">-</span>
                    WIB
                </p>
            </div>
        </div>

        <div class="px-2 md:px-4 lg:px-6 py-4">
            <div class="overflow-x-auto">
                <!-- Toolbar -->
                <div
                    class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">

                    <!-- Page length -->
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>
                        <select id="poli-pageLength"
                            class="border border-slate-300 dark:border-slate-600 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500
                           bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-1 w-28">
                            <option value="10">10 baris</option>
                            <option value="25">25 baris</option>
                            <option value="50">50 baris</option>
                            <option value="100">100 baris</option>
                        </select>
                        <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">per halaman</span>
                    </div>

                    <!-- Search -->
                    <div class="w-full md:w-auto">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                            </span>
                            <input type="text" id="searchKadaluarsaObat"
                                class="block w-full md:w-72 pl-9 pr-3 py-2 text-sm text-slate-800 dark:text-slate-100
                               border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700
                               focus:ring-sky-500 focus:border-sky-500"
                                placeholder="Cari nama obat...">
                        </div>
                        <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                            Contoh: <span class="italic">Paracetamol dll</span>.
                        </p>
                    </div>
                </div>

                <table id="tableKadaluarsaBHP"
                    class="min-w-full text-xs md:text-sm border border-slate-100 dark:border-slate-800 rounded-xl overflow-hidden">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                        <tr class="text-[11px] md:text-xs text-slate-600 dark:text-slate-300">
                            <th class="px-3 py-2 text-left font-semibold w-[40px]">No</th>
                            <th class="px-3 py-2 text-left font-semibold">Kode</th>
                            <th class="px-3 py-2 text-left font-semibold">Nama Barang</th>
                            <th class="px-3 py-2 text-left font-semibold">Tanggal Kadaluarsa</th>
                            <th class="px-3 py-2 text-left font-semibold">Stok</th>
                            <th class="px-3 py-2 text-left font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-[11px] md:text-xs text-slate-700 dark:text-slate-200">
                        {{-- DataTables yang isi --}}
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div
                class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">
                <div id="data-kadaluarsa-obat-customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300">
                </div>

                <ul id="data-kadaluarsa-obat-customPagination"
                    class="inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
                </ul>
            </div>
        </div>
    </div>
</div>

@vite(['resources/js/farmasi/kadaluarsa-bhp/data-kadaluarsa-bhp.js'])
