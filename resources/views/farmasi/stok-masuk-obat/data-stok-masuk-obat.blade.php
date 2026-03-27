<section class="space-y-5">

    {{-- ============== HEADER ============== --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">

        <div class="flex items-start gap-3 md:flex-1">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl
                bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-md shrink-0">
                <i class="fa-solid fa-box-open text-lg"></i>
            </div>

            <div class="min-w-0">
                <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Data Stok Masuk Obat
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Menampilkan daftar restock obat yang belum dikonfirmasi sebagai stok masuk.
                    <span class="hidden sm:inline">
                        Gunakan pencarian untuk menemukan data lebih cepat.
                    </span>
                </p>
            </div>
        </div>

        <div class="flex justify-center md:justify-end">
            <button type="button"
                class="inline-flex items-center gap-2 px-5 py-2.5
                text-sm font-semibold text-white rounded-xl shadow-md
                bg-gradient-to-r from-emerald-500 to-teal-600
                hover:from-emerald-600 hover:to-teal-700
                focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <i class="fa-solid fa-rotate text-xs"></i>
                <span>Refresh Data</span>
            </button>
        </div>
    </div>

    {{-- ============== CARD TABEL ============== --}}
    <div
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl shadow-sm overflow-hidden">

        <div class="px-3 sm:px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">

                <div class="flex items-center gap-2 text-sm w-full md:w-auto">
                    <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>

                    <select id="stok-masuk-obat-page-length"
                        class="w-full sm:w-40 md:w-32 border border-slate-300 dark:border-slate-600 text-sm rounded-lg
                               focus:ring-sky-500 focus:border-sky-500
                               bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100
                               px-2 py-2">
                        <option value="10">10 baris</option>
                        <option value="25">25 baris</option>
                        <option value="50">50 baris</option>
                        <option value="100">100 baris</option>
                    </select>

                    <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">/ halaman</span>
                </div>

                <div class="w-full md:w-auto">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                        </span>

                        <input type="text" id="stok-masuk-obat-search-input"
                            class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm
                                   text-slate-800 dark:text-slate-100
                                   border border-slate-300 dark:border-slate-600 rounded-lg
                                   bg-slate-50 dark:bg-slate-700
                                   focus:ring-sky-500 focus:border-sky-500"
                            placeholder="Cari supplier, depot, no faktur...">
                    </div>

                    <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500 hidden sm:block">
                        Contoh: <span class="italic">Nama supplier, nama depot, nomor faktur</span>.
                    </p>
                </div>

            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="table-stok-masuk-obat"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100
                       border-t border-slate-100 dark:border-slate-700 whitespace-nowrap">
                <thead
                    class="text-[11px] md:text-xs font-semibold uppercase
                           bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600
                           text-white tracking-wide">
                    <tr>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">No</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Nama Supplier</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Nama Depot</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">No Faktur</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Tanggal Jatuh Tempo</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Total Tagihan</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Status</th>
                        <th
                            class="px-3 sm:px-4 md:px-6 py-3 text-center whitespace-nowrap
                                   sticky right-0 z-10
                                   bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                   px-3 sm:px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700
                   bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">

            <div id="stok-masuk-obat-custom-info" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>

            {{-- Pagination aman di HP --}}
            <div class="w-full md:w-auto overflow-x-auto">
                <ul id="stok-masuk-obat-custom-pagination"
                    class="min-w-max inline-flex items-center gap-0 text-sm isolate rounded-lg
                           border border-slate-200 dark:border-slate-600 overflow-hidden">
                </ul>
            </div>
        </div>
    </div>

</section>

<div id="modal-detail-stok-masuk-obat"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 px-4 py-6">

    <div
        class="w-full max-w-6xl bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">

        {{-- Header Modal --}}
        <div
            class="flex items-center justify-between px-4 md:px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40">
            <div>
                <h3 class="text-lg md:text-xl font-bold text-slate-800 dark:text-slate-100">
                    Detail Stok Masuk Obat
                </h3>
                <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Informasi transaksi restock dan detail item obat.
                </p>
            </div>

            <button type="button" id="btn-close-modal-detail-stok-masuk-obat"
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 dark:text-slate-300">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Body Modal --}}
        <div class="p-4 md:p-6 space-y-6 max-h-[85vh] overflow-y-auto">

            {{-- Loading --}}
            <div id="detail-stok-masuk-loading" class="hidden">
                <div
                    class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 text-sm text-slate-500 dark:text-slate-300">
                    Memuat data detail...
                </div>
            </div>

            {{-- Content --}}
            <div id="detail-stok-masuk-content" class="space-y-6 hidden">

                {{-- Info Header --}}
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Nama Supplier</p>
                        <p id="detail-nama-supplier"
                            class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">-</p>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Nama Depot</p>
                        <p id="detail-nama-depot" class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">
                            -</p>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                        <p class="text-xs text-slate-500 dark:text-slate-400">No Faktur</p>
                        <p id="detail-no-faktur" class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">-
                        </p>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Status Hutang</p>
                        <p id="detail-status-hutang"
                            class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">-</p>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Tanggal Diterima</p>
                        <p id="detail-tanggal-terima"
                            class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">-</p>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Tanggal Jatuh Tempo</p>
                        <p id="detail-tanggal-jatuh-tempo"
                            class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">-</p>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Total Tagihan</p>
                        <p id="detail-total-tagihan"
                            class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">-</p>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                        <p class="text-xs text-slate-500 dark:text-slate-400">Status Restock</p>
                        <p id="detail-status-restock"
                            class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">-</p>
                    </div>
                </div>

                {{-- Tabel Detail Item --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div
                        class="px-4 py-3 bg-slate-50 dark:bg-slate-900/30 border-b border-slate-200 dark:border-slate-700">
                        <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                            Detail Item Obat
                        </h4>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left whitespace-nowrap">
                            <thead class="bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-100">
                                <tr>
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Kode Obat</th>
                                    <th class="px-4 py-3">Nama Obat</th>
                                    <th class="px-4 py-3">Batch</th>
                                    <th class="px-4 py-3">Expired</th>
                                    <th class="px-4 py-3">Qty</th>
                                    <th class="px-4 py-3">Harga Beli</th>
                                    <th class="px-4 py-3">Diskon</th>
                                    <th class="px-4 py-3">Total</th>
                                </tr>
                            </thead>
                            <tbody id="detail-stok-masuk-items-body"
                                class="divide-y divide-slate-100 dark:divide-slate-700 text-slate-700 dark:text-slate-100">
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        {{-- Footer --}}
        <div
            class="flex justify-end px-4 md:px-6 py-4 border-t border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40">
            <button type="button" id="btn-close-footer-modal-detail-stok-masuk-obat"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-xl border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700">
                Tutup
            </button>
        </div>
    </div>
</div>

@vite(['resources/js/farmasi/stok-masuk-obat/data-stok-masuk-obat.js'])
