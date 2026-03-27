<section class="space-y-5">

    {{-- ============== HEADER ============== --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">

        {{-- KIRI: ICON + TITLE --}}
        <div class="flex items-start gap-3 md:flex-1">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl
                bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-md shrink-0">
                <i class="fa-solid fa-file-invoice-dollar text-lg"></i>
            </div>

            <div class="min-w-0">
                <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Data Riwayat Hutang Bahan Habis Pakai
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Lihat dan telusuri seluruh data riwayat hutang Bahan Habis Pakai, mulai dari supplier, depot,
                    nomor faktur, total tagihan, hingga status pembayaran.
                    <span class="hidden sm:inline">
                        Gunakan pencarian untuk menemukan data hutang dengan lebih cepat dan akurat.
                    </span>
                </p>
            </div>
        </div>
    </div>

    {{-- ============== CARD TABEL ============== --}}
    <div
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl shadow-sm overflow-hidden">

        {{-- Toolbar --}}
        <div class="px-3 sm:px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">

                {{-- Page length --}}
                <div class="flex items-center gap-2 text-sm w-full md:w-auto">
                    <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>

                    <select id="data-riwayat-hutang-page-length"
                        class="w-full sm:w-40 md:w-32 border border-slate-300 dark:border-slate-600 text-sm rounded-lg
                               focus:ring-amber-500 focus:border-amber-500
                               bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100
                               px-2 py-2">
                        <option value="10">10 baris</option>
                        <option value="25">25 baris</option>
                        <option value="50">50 baris</option>
                        <option value="100">100 baris</option>
                    </select>

                    <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">/ halaman</span>
                </div>

                {{-- Search --}}
                <div class="w-full md:w-auto">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                        </span>

                        <input type="text" id="data-riwayat-hutang-search-input"
                            class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm
                                   text-slate-800 dark:text-slate-100
                                   border border-slate-300 dark:border-slate-600 rounded-lg
                                   bg-slate-50 dark:bg-slate-700
                                   focus:ring-amber-500 focus:border-amber-500"
                            placeholder="Cari supplier, depot, no faktur, atau status pembayaran...">
                    </div>

                    <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500 hidden sm:block">
                        Contoh: <span class="italic">Nama supplier, no faktur, nama depot, lunas, belum lunas</span>.
                    </p>
                </div>

            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table id="table-data-riwayat-hutang-bhp"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100
                       border-t border-slate-100 dark:border-slate-700 whitespace-nowrap">
                <thead
                    class="text-[11px] md:text-xs font-semibold uppercase
                           bg-gradient-to-r from-amber-500 via-orange-500 to-orange-600
                           text-white tracking-wide">
                    <tr>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">No</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Nama Supplier</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">No Faktur</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Tanggal Hutang</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Tanggal Jatuh Tempo</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Tanggal Pelunasan</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Total Tagihan</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Status Pembayaran</th>
                        <th
                            class="px-3 sm:px-4 md:px-6 py-3 text-center whitespace-nowrap
                                   sticky right-0 z-10
                                   bg-gradient-to-r from-amber-500 via-orange-500 to-orange-600">
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

            <div id="data-riwayat-hutang-custom-info" class="text-xs md:text-sm text-slate-600 dark:text-slate-300">
            </div>

            <div class="w-full md:w-auto overflow-x-auto">
                <ul id="data-riwayat-hutang-custom-pagination"
                    class="min-w-max inline-flex items-center gap-0 text-sm isolate rounded-lg
                           border border-slate-200 dark:border-slate-600 overflow-hidden">
                </ul>
            </div>
        </div>
    </div>

</section>

{{-- ============== MODAL DETAIL RIWAYAT HUTANG BHP ============== --}}
<div id="modal-detail-riwayat-hutang-bhp"
    class="fixed inset-0 z-[999] hidden items-center justify-center bg-black/50 px-4 py-6">

    <div
        class="w-full max-w-6xl bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">

        {{-- Header --}}
        <div
            class="flex items-center justify-between px-4 md:px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <div>
                <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100">Detail Hutang Bahan Habis Pakai</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Informasi lengkap data hutang bahan habis pakai.
                </p>
            </div>

            <button type="button" id="close-modal-detail-riwayat-hutang-bhp"
                class="inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="p-4 md:p-6 max-h-[80vh] overflow-y-auto">
            <div id="detail-riwayat-hutang-bhp-loading" class="hidden">
                <div class="flex items-center gap-3 text-slate-600 dark:text-slate-300">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <span>Memuat detail data hutang bahan habis pakai...</span>
                </div>
            </div>

            <div id="detail-riwayat-hutang-bhp-error"
                class="hidden rounded-xl border border-red-200 bg-red-50 text-red-600 px-4 py-3 text-sm">
            </div>

            <div id="detail-riwayat-hutang-bhp-content" class="hidden space-y-5">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    {{-- Informasi Hutang --}}
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                            <h4 class="font-bold text-slate-800 dark:text-slate-100">Informasi Hutang</h4>
                        </div>
                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-slate-500">No Faktur</p>
                                <p id="detail-riwayat-hutang-bhp-no-faktur"
                                    class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Status Hutang</p>
                                <div id="detail-riwayat-hutang-bhp-status-hutang">-</div>
                            </div>
                            <div>
                                <p class="text-slate-500">Tanggal Hutang</p>
                                <p id="detail-riwayat-hutang-bhp-tanggal-hutang"
                                    class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Tanggal Jatuh Tempo</p>
                                <p id="detail-riwayat-hutang-bhp-tanggal-jatuh-tempo"
                                    class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Tanggal Pelunasan</p>
                                <p id="detail-riwayat-hutang-bhp-tanggal-pelunasan"
                                    class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Metode Pembayaran</p>
                                <p id="detail-riwayat-hutang-bhp-metode-pembayaran"
                                    class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-slate-500">Total Hutang</p>
                                <p id="detail-riwayat-hutang-bhp-total-hutang" class="font-bold text-lg text-rose-600">
                                    Rp 0</p>
                            </div>
                        </div>
                    </div>

                    {{-- Informasi Supplier --}}
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                            <h4 class="font-bold text-slate-800 dark:text-slate-100">Informasi Supplier</h4>
                        </div>
                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-slate-500">Nama Supplier</p>
                                <p id="detail-riwayat-hutang-bhp-nama-supplier"
                                    class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Kontak Person</p>
                                <p id="detail-riwayat-hutang-bhp-kontak-person"
                                    class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                            </div>
                            <div>
                                <p class="text-slate-500">No HP</p>
                                <p id="detail-riwayat-hutang-bhp-no-hp-supplier"
                                    class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Email</p>
                                <p id="detail-riwayat-hutang-bhp-email-supplier"
                                    class="font-semibold text-slate-800 dark:text-slate-100 break-all">-</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-slate-500">Alamat</p>
                                <p id="detail-riwayat-hutang-bhp-alamat-supplier"
                                    class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                            </div>
                        </div>
                    </div>

                    {{-- Informasi Restock --}}
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                            <h4 class="font-bold text-slate-800 dark:text-slate-100">Informasi Restock Bahan Habis
                                Pakai</h4>
                        </div>
                        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <p class="text-slate-500">No Faktur Restock</p>
                                <p id="detail-riwayat-hutang-bhp-no-faktur-restock"
                                    class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Tanggal Terima</p>
                                <p id="detail-riwayat-hutang-bhp-tanggal-terima"
                                    class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Tanggal Jatuh Tempo Restock</p>
                                <p id="detail-riwayat-hutang-bhp-tanggal-jatuh-tempo-restock"
                                    class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Status Restock</p>
                                <div id="detail-riwayat-hutang-bhp-status-restock">-</div>
                            </div>
                            <div>
                                <p class="text-slate-500">Depot</p>
                                <p id="detail-riwayat-hutang-bhp-depot"
                                    class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Total Tagihan</p>
                                <p id="detail-riwayat-hutang-bhp-total-tagihan-restock"
                                    class="font-semibold text-slate-800 dark:text-slate-100">Rp 0</p>
                            </div>
                        </div>
                    </div>

                    {{-- Audit Data --}}
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                            <h4 class="font-bold text-slate-800 dark:text-slate-100">Audit Data</h4>
                        </div>
                        <div class="p-4 grid grid-cols-1 gap-4 text-sm">
                            <div>
                                <p class="text-slate-500">Dibuat Oleh</p>
                                <p id="detail-riwayat-hutang-bhp-dibuat-oleh"
                                    class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Diupdate Oleh</p>
                                <p id="detail-riwayat-hutang-bhp-diupdate-oleh"
                                    class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Created At</p>
                                <p id="detail-riwayat-hutang-bhp-created-at"
                                    class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                            </div>
                            <div>
                                <p class="text-slate-500">Updated At</p>
                                <p id="detail-riwayat-hutang-bhp-updated-at"
                                    class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Detail Item --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-200 dark:border-slate-700">
                        <h4 class="font-bold text-slate-800 dark:text-slate-100">Detail Item Bahan Habis Pakai</h4>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-100">
                                <tr>
                                    <th class="px-4 py-3 text-left">No</th>
                                    <th class="px-4 py-3 text-left">Kode BHP</th>
                                    <th class="px-4 py-3 text-left">Nama BHP</th>
                                    <th class="px-4 py-3 text-left">Qty</th>
                                    <th class="px-4 py-3 text-left">Harga Beli</th>
                                    <th class="px-4 py-3 text-left">Subtotal</th>
                                    <th class="px-4 py-3 text-left">Diskon Type</th>
                                    <th class="px-4 py-3 text-left">Diskon Value</th>
                                    <th class="px-4 py-3 text-left">Diskon Amount</th>
                                    <th class="px-4 py-3 text-left">Total Setelah Diskon</th>
                                </tr>
                            </thead>
                            <tbody id="detail-riwayat-hutang-bhp-detail-item-body"
                                class="divide-y divide-slate-200 dark:divide-slate-700">
                                <tr>
                                    <td colspan="10" class="px-4 py-4 text-center text-slate-500">Belum ada data
                                        item.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Bukti Pembayaran --}}
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200 mb-3">Bukti Pembayaran</p>
                    <div id="detail-riwayat-hutang-bhp-bukti-pembayaran-wrapper">
                        <p class="text-sm text-slate-500">-</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@vite(['resources/js/kasir/hutang-bahan-habis-pakai/data-riwayat-hutang-bahan-habis-pakai.js'])
