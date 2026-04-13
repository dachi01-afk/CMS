<section class="space-y-5">

    {{-- HEADER --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
               bg-white dark:bg-slate-800 border border-emerald-200 dark:border-slate-700
               rounded-2xl px-4 md:px-6 py-4 shadow-sm">

        <div class="flex items-start gap-3 md:flex-1">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl
                       bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md shrink-0">
                <i class="fa-solid fa-wallet text-lg"></i>
            </div>

            <div class="min-w-0">
                <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Data Hutang
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Kelola hutang pembelian dan pemesanan obat dari supplier secara rapi dan mudah dipantau.
                </p>
            </div>
        </div>
    </div>

    {{-- CARD TABEL --}}
    <div
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl shadow-sm overflow-hidden">

        <div class="px-3 sm:px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">

                <div class="flex items-center gap-2 text-sm w-full md:w-auto">
                    <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>
                    <select id="hutang-page-length"
                        class="w-full sm:w-40 md:w-32 border border-slate-300 dark:border-slate-600 text-sm rounded-lg
                               focus:ring-emerald-500 focus:border-emerald-500
                               bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-2">
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
                        <input type="text" id="hutang-search-input"
                            class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm
                                   text-slate-800 dark:text-slate-100
                                   border border-slate-300 dark:border-slate-600 rounded-lg
                                   bg-slate-50 dark:bg-slate-700
                                   focus:ring-emerald-500 focus:border-emerald-500"
                            placeholder="Cari supplier, no faktur, tanggal hutang...">
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="table-hutang"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100
                       border-t border-slate-100 dark:border-slate-700 whitespace-nowrap">
                <thead
                    class="text-[11px] md:text-xs font-semibold uppercase
                           bg-gradient-to-r from-emerald-500 to-teal-600
                           text-white tracking-wide">
                    <tr>
                        <th class="px-3 sm:px-4 md:px-6 py-3">No</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3">Nama Supplier</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3">No Faktur</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3">Tanggal Hutang</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3">Jatuh Tempo</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3">Total Hutang</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3">Status</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 text-center sticky right-0 z-10 bg-teal-600">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
            </table>
        </div>

        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                   px-3 sm:px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700
                   bg-emerald-50/40 dark:bg-slate-800/60 rounded-b-2xl">
            <div id="hutang-custom-info" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>
            <div class="w-full md:w-auto overflow-x-auto">
                <ul id="hutang-custom-pagination"
                    class="min-w-max inline-flex items-center gap-0 text-sm isolate rounded-lg
                           border border-slate-200 dark:border-slate-600 overflow-hidden">
                </ul>
            </div>
        </div>
    </div>

</section>

<div id="modal-detail-hutang" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="w-full max-w-7xl rounded-2xl bg-white shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between border-b px-6 py-4 bg-slate-50">
            <div>
                <h3 class="text-lg font-bold text-slate-800">Detail Hutang</h3>
                <p class="text-sm text-slate-500">Informasi lengkap data hutang.</p>
            </div>
            <button type="button" id="button-close-modal-detail-hutang"
                class="rounded-lg px-3 py-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="max-h-[85vh] overflow-y-auto p-6 space-y-6">

            {{-- Loading / message --}}
            <div id="detail-hutang-alert"
                class="hidden rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
            </div>

            {{-- INFORMASI HUTANG + SUPPLIER --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="rounded-2xl border border-slate-200 p-5">
                    <h4 class="text-base font-bold text-slate-800 mb-4">Informasi Hutang</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-slate-500">No Faktur</p>
                            <p id="detail-no-faktur" class="font-semibold text-slate-800">-</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Status Hutang</p>
                            <p id="detail-status-hutang" class="font-semibold text-slate-800">-</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Tanggal Hutang</p>
                            <p id="detail-tanggal-hutang" class="font-semibold text-slate-800">-</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Tanggal Jatuh Tempo</p>
                            <p id="detail-tanggal-jatuh-tempo" class="font-semibold text-slate-800">-</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Tanggal Pelunasan</p>
                            <p id="detail-tanggal-pelunasan" class="font-semibold text-slate-800">-</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Metode Pembayaran</p>
                            <p id="detail-metode-pembayaran" class="font-semibold text-slate-800">-</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-slate-500">Total Hutang</p>
                            <p id="detail-total-hutang" class="text-lg font-bold text-rose-600">-</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 p-5">
                    <h4 class="text-base font-bold text-slate-800 mb-4">Informasi Supplier</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-slate-500">Nama Supplier</p>
                            <p id="detail-supplier-nama" class="font-semibold text-slate-800">-</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Kontak Person</p>
                            <p id="detail-supplier-kontak" class="font-semibold text-slate-800">-</p>
                        </div>
                        <div>
                            <p class="text-slate-500">No HP</p>
                            <p id="detail-supplier-nohp" class="font-semibold text-slate-800">-</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Email</p>
                            <p id="detail-supplier-email" class="font-semibold text-slate-800">-</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-slate-500">Alamat</p>
                            <p id="detail-supplier-alamat" class="font-semibold text-slate-800">-</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- INFORMASI RESTOCK + AUDIT --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="rounded-2xl border border-slate-200 p-5">
                    <h4 class="text-base font-bold text-slate-800 mb-4">Informasi Restock</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-slate-500">No Faktur Restock</p>
                            <p id="detail-restock-no-faktur" class="font-semibold text-slate-800">-</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Tanggal Terima</p>
                            <p id="detail-restock-tanggal-terima" class="font-semibold text-slate-800">-</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Tanggal Jatuh Tempo</p>
                            <p id="detail-restock-jatuh-tempo" class="font-semibold text-slate-800">-</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Status Restock</p>
                            <p id="detail-restock-status" class="font-semibold text-slate-800">-</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Depot</p>
                            <p id="detail-restock-depot" class="font-semibold text-slate-800">-</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Total Tagihan</p>
                            <p id="detail-restock-total-tagihan" class="font-semibold text-slate-800">-</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 p-5">
                    <h4 class="text-base font-bold text-slate-800 mb-4">Audit Data</h4>
                    <div class="grid grid-cols-1 gap-4 text-sm">
                        <div>
                            <p class="text-slate-500">Dibuat Oleh</p>
                            <p id="detail-dibuat-oleh" class="font-semibold text-slate-800">-</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Diupdate Oleh</p>
                            <p id="detail-diupdate-oleh" class="font-semibold text-slate-800">-</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Created At</p>
                            <p id="detail-created-at" class="font-semibold text-slate-800">-</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Updated At</p>
                            <p id="detail-updated-at" class="font-semibold text-slate-800">-</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TABEL ITEM --}}
            <div class="rounded-2xl border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 border-b px-5 py-3">
                    <h4 class="text-base font-bold text-slate-800">Detail Item Restock</h4>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left text-slate-700">
                        <thead class="bg-slate-100 uppercase text-xs text-slate-700">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Kode Obat</th>
                                <th class="px-4 py-3">Nama Obat</th>
                                <th class="px-4 py-3 text-right">Qty</th>
                                <th class="px-4 py-3 text-right">Harga Beli</th>
                                <th class="px-4 py-3 text-right">Subtotal</th>
                                <th class="px-4 py-3">Diskon Type</th>
                                <th class="px-4 py-3 text-right">Diskon Value</th>
                                <th class="px-4 py-3 text-right">Diskon Amount</th>
                                <th class="px-4 py-3 text-right">Total Setelah Diskon</th>
                            </tr>
                        </thead>
                        <tbody id="detail-hutang-items-body">
                            <tr>
                                <td colspan="10" class="px-4 py-6 text-center text-slate-500">
                                    Belum ada data.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

@vite(['resources/js/kasir/hutang/data-hutang.js'])
