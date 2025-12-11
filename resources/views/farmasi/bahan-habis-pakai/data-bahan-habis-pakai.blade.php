{{-- CARD DATA STOK Bahan Habis Pakai --}}
<div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
        <div>
            <h2 class="text-base md:text-lg font-semibold text-gray-900 dark:text-white">
                Data Stok Bahan Habis Pakai
            </h2>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                Last Update:
                <span class="font-medium text-gray-700 dark:text-gray-200">
                    {{ $lastUpdate ?? now()->format('d/m/Y') }}
                </span>
            </p>
        </div>

        <div class="flex flex-col md:flex-row gap-2 md:items-center">

            {{-- SEARCH GLOBAL --}}
            <div class="relative w-full md:w-72">
                <input id="globalSearchObat" type="text"
                    class="w-full text-xs md:text-sm pl-9 pr-3 py-2.5 rounded-xl border border-gray-200
                           bg-gray-50 focus:bg-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500
                           dark:bg-gray-900 dark:border-gray-700 dark:text-gray-100"
                    placeholder="Cari kode, nama obat atau kategori">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
            </div>

            {{-- BUTTONS --}}
            <div class="flex items-center gap-2 justify-end">
                {{-- Tambah Data Obat --}}
                <button type="button" id="btn-open-modal-create-obat"
                    class="inline-flex items-center justify-center px-3 md:px-4 py-2 rounded-xl text-[11px] md:text-xs 
                           font-semibold bg-emerald-600 text-white shadow-sm hover:bg-emerald-700">
                    <i class="fa-solid fa-plus mr-1.5 text-[10px]"></i>
                    Tambah Data Bahan Habis Pakai
                </button>

                {{-- Export dropdown (DataTables di JS) --}}
                <div class="relative" id="exportObatWrapper">
                    <button type="button" id="btn-export-trigger"
                        class="inline-flex items-center justify-center px-3 md:px-4 py-2 rounded-xl border text-[11px] md:text-xs
                               font-medium bg-white text-gray-700 border-gray-200 hover:bg-gray-50
                               dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
                        <i class="fa-solid fa-file-export mr-1.5 text-[10px]"></i>
                        Export
                        <i class="fa-solid fa-chevron-down ml-1 text-[8px]"></i>
                    </button>
                </div>

                {{-- Import --}}
                {{-- action="{{ route('farmasi.obat.import') }}" method="POST" --}}
                <form id="form-import-obat" enctype="multipart/form-data" class="hidden">
                    @csrf
                    <input type="file" name="file" id="input-file-import-obat" accept=".xlsx,.xls,.csv">
                </form>

                <button type="button" id="btn-import-obat"
                    class="inline-flex items-center justify-center px-3 md:px-4 py-2 rounded-xl text-[11px] md:text-xs 
                           font-medium bg-white text-emerald-700 border border-emerald-500 hover:bg-emerald-50
                           dark:bg-gray-900 dark:border-emerald-500 dark:text-emerald-300">
                    <i class="fa-solid fa-upload mr-1.5 text-[10px]"></i>
                    Import
                </button>
            </div>
        </div>
    </div>

    {{-- TABLE WRAPPER --}}
    <div class="rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <table id="tabelBahanHabisPakai" class="min-w-full text-xs md:text-sm">
            <thead
                class="bg-gray-50 dark:bg-gray-800/80 text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-300">
                <tr>
                    <th class="px-3 py-2.5 text-left">Kode</th>
                    <th class="px-3 py-2.5 text-left">Nama Barang</th>
                    <th class="px-3 py-2.5 text-left">Brand Farmasi</th>
                    <th class="px-3 py-2.5 text-left">Stok</th>
                    <th class="px-3 py-2.5 text-left">Harga Umum</th>
                    <th class="px-3 py-2.5 text-left">Harga Beli</th>
                    <th class="px-3 py-2.5 text-left">Avg HPP</th>
                    <th class="px-3 py-2.5 text-left">Harga OTC</th>
                    <th class="px-3 py-2.5 text-left">Margin Profit</th>
                    <th class="px-3 py-2.5 text-center w-10">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 text-[11px] md:text-xs">
                {{-- server-side DataTables --}}
            </tbody>
        </table>
    </div>
</div>

@vite(['resources/js/farmasi/bahan-habis-pakai/data-bahan-habis-pakai.js'])
