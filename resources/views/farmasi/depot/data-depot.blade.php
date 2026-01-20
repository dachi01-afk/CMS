{{-- CARD DATA DEPOT --}}
<div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">

    {{-- HEADER (Depot) --}}
    <div class="mb-4">
        <h2 class="text-lg md:text-xl font-bold text-sky-600 dark:text-sky-400 tracking-tight">
            Depot
        </h2>

        <div class="mt-2 space-y-1.5">
            <p class="text-[12px] md:text-sm text-gray-700 dark:text-gray-200 leading-relaxed">
                Depot merupakan fitur untuk maintenance jumlah obat yang tersebar di Klinik.
            </p>
            <p class="text-[12px] md:text-sm text-gray-700 dark:text-gray-200 leading-relaxed">
                Pemilik Klinik atau Apoteker bisa mengetahui jumlah obat yang terdapat di Apotek, Ruang Dokter, Gudang,
                dan lain-lain.
            </p>
        </div>

        {{-- Optional: info update kecil (kalau mau tetap ada) --}}
        <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
            Last Update:
            <span class="font-medium text-gray-700 dark:text-gray-200">
                {{ $lastUpdate ?? now()->format('d/m/Y') }}
            </span>
        </p>
    </div>

    {{-- TABLE WRAPPER --}}
    <div class="rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <table id="table-depot" class="min-w-full text-xs md:text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-[11px] md:text-xs uppercase tracking-wider text-slate-500">
                    <th class="px-4 py-3 w-[70px]">No</th>
                    <th class="px-4 py-3">Depot</th>
                    <th class="px-4 py-3 w-[180px]">Tipe</th>
                    <th class="px-4 py-3 w-[140px]">Stok</th>
                    <th class="px-4 py-3 w-[420px] text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 text-[11px] md:text-xs">
                {{-- server-side DataTables --}}
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Show Obat -->
<div id="modal-show-obat" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/20">

    <!-- Modal Box -->
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl mx-4">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-slate-800">
                List Obat Pada Depot
            </h2>

            <button id="btn-close-show-modal-obat" class="text-slate-400 hover:text-slate-600 text-xl">
                &times;
            </button>
        </div>

        <!-- Content -->
        <div class="px-6 py-4">
            <div class="overflow-x-auto">
                <table class="w-full text-sm border border-slate-200 rounded-lg">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-4 py-2 text-left border">
                                Nama Obat / Bahan Habis Pakai
                            </th>
                            <th class="px-4 py-2 text-left border">
                                Stok Obat
                            </th>
                        </tr>
                    </thead>

                    <tbody id="modal-obat-body">
                        <!-- Diisi via jQuery -->
                        <tr>
                            <td colspan="2" class="px-4 py-6 text-center text-slate-400">
                                Memuat data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end px-6 py-4 border-t">
            <button id="btn-close-footer"
                class="px-4 py-2 rounded-lg bg-slate-200 hover:bg-slate-300 text-sm font-medium">
                Tutup
            </button>
        </div>

    </div>
</div>

<!-- Modal Repair Obat -->
<div id="modal-repair-obat"
    class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto h-[calc(100%-1rem)] max-h-full items-center justify-center bg-black/20">
    <div class="relative w-full max-w-5xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-sm overflow-hidden">

            <div class="pt-8 pb-4 text-center">
                <h3 class="text-2xl font-semibold text-blue-600">
                    Repair Stock Obat Depot Apotek
                </h3>
                <button type="button"
                    class="absolute top-4 right-6 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center"
                    onclick="closeModalRepair()">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>

            <div
                class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 px-6 py-4 border-b border-slate-200 dark:border-slate-700">

                <div class="flex items-center gap-2 text-sm">
                    <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>
                    <select id="obat-pageLength"
                        class="border border-slate-300 dark:border-slate-600 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500
                        bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-1 w-28">
                        <option value="10">10 baris</option>
                        <option value="25">25 baris</option>
                        <option value="50">50 baris</option>
                        <option value="100">100 baris</option>
                    </select>
                    <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">per halaman</span>
                </div>

                <div class="relative w-full md:w-80">
                    <input id="globalSearchObat" type="text"
                        class="w-full text-xs md:text-sm pl-9 pr-3 py-2.5 rounded-xl border border-gray-200
                        bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                        dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                        placeholder="Cari Obat">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                </div>
            </div>

            <!-- Body (scrollable) -->
            <form id="form-modal-repair-obat" class="px-6 py-5 space-y-7 overflow-y-auto"
                data-url="{{ route('repair.stok.obat') }}" method="POST">
                @csrf
                <div class="p-6 overflow-x-auto">
                    <table id="table-repair-obat" class="w-full border-collapse border border-gray-200 text-sm">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="border border-gray-300 px-3 py-2 text-left">Kode Obat</th>
                                <th class="border border-gray-300 px-3 py-2 text-left">Nama Obat</th>
                                <th class="border border-gray-300 px-3 py-2">Qty Akhir</th>
                                <th class="border border-gray-300 px-3 py-2">Qty Akhir (Fisik)</th>
                                <th class="border border-gray-300 px-3 py-2">Selisih</th>
                                <th class="border border-gray-300 px-3 py-2">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="modal-repair-obat-body">
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-400">
                                    <i class="fa-solid fa-circle-notch animate-spin mr-2"></i> Memuat data...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60">
                    <div id="obat-customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300">
                    </div>

                    <ul id="obat-customPagination"
                        class="inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
                    </ul>
                </div>

                <div class="p-6 flex justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeModalRepair()"
                        class="px-6 py-2 rounded text-sm font-medium text-gray-600 hover:bg-gray-100 transition-all">
                        Batal
                    </button>
                    <button type="button" id="btn-save-repair-obat"
                        class="bg-[#0070BA] hover:bg-blue-700 text-white px-10 py-2 rounded shadow-md text-sm font-medium transition-all">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Repair Obat -->
<div id="modal-repair-bhp"
    class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto h-[calc(100%-1rem)] max-h-full items-center justify-center bg-black/20">
    <div class="relative w-full max-w-5xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-sm overflow-hidden">

            <div class="pt-8 pb-4 text-center">
                <h3 class="text-2xl font-semibold text-blue-600">
                    Repair Stock BHP Depot Apotek
                </h3>
                <button type="button"
                    class="absolute top-4 right-6 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ml-auto inline-flex justify-center items-center"
                    onclick="closeModalRepairBHP()">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>

            <div
                class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 px-6 py-4 border-b border-slate-200 dark:border-slate-700">

                <div class="flex items-center gap-2 text-sm">
                    <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>
                    <select id="bhp-pageLength"
                        class="border border-slate-300 dark:border-slate-600 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500
                        bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-1 w-28">
                        <option value="10">10 baris</option>
                        <option value="25">25 baris</option>
                        <option value="50">50 baris</option>
                        <option value="100">100 baris</option>
                    </select>
                    <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">per halaman</span>
                </div>

                <div class="relative w-full md:w-80">
                    <input id="globalSearchBHP" type="text"
                        class="w-full text-xs md:text-sm pl-9 pr-3 py-2.5 rounded-xl border border-gray-200
                        bg-gray-50 focus:bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                        dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                        placeholder="Cari Obat">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                </div>
            </div>

            <!-- Body (scrollable) -->
            <form id="form-modal-repair-bhp" class="px-6 py-5 space-y-7 overflow-y-auto"
                data-url="{{ route('repair.stok.bhp') }}" method="POST">
                @csrf
                <div class="p-6 overflow-x-auto">
                    <table id="table-repair-bhp" class="w-full border-collapse border border-gray-200 text-sm">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="border border-gray-300 px-3 py-2 text-left">Kode BHP</th>
                                <th class="border border-gray-300 px-3 py-2 text-left">Nama BHP</th>
                                <th class="border border-gray-300 px-3 py-2">Qty Akhir</th>
                                <th class="border border-gray-300 px-3 py-2">Qty Akhir (Fisik)</th>
                                <th class="border border-gray-300 px-3 py-2">Selisih</th>
                                <th class="border border-gray-300 px-3 py-2">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="modal-repair-bhp-body">
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-400">
                                    <i class="fa-solid fa-circle-notch animate-spin mr-2"></i> Memuat data...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60">
                    <div id="bhp-customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300">
                    </div>

                    <ul id="bhp-customPagination"
                        class="inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
                    </ul>
                </div>

                <div class="p-6 flex justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="closeModalRepairBHP()"
                        class="px-6 py-2 rounded text-sm font-medium text-gray-600 hover:bg-gray-100 transition-all">
                        Batal
                    </button>
                    <button type="button" id="btn-save-repair-bhp"
                        class="bg-[#0070BA] hover:bg-blue-700 text-white px-10 py-2 rounded shadow-md text-sm font-medium transition-all">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@vite(['resources/js/farmasi/depot/data-depot.js'])
