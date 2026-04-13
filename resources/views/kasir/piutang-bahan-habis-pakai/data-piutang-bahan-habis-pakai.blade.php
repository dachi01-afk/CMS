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
                    Data Piutang Bahan Habis Pakai
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Kelola Piutang Bahan Habis Pakai dari supplier secara rapi dan mudah dipantau.
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

<div id="modal-detail-piutang-bhp" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50"></div>

    <div class="relative flex min-h-screen items-center justify-center p-4">
        <div class="w-full max-w-6xl rounded-2xl bg-white shadow-2xl dark:bg-slate-800 overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 px-6 py-4">
                <div>
                    <h3 class="text-xl font-bold text-slate-800 dark:text-slate-100">
                        Detail Piutang Bahan Habis Pakai
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Informasi lengkap data piutang bahan habis pakai
                    </p>
                </div>

                <button type="button" id="close-modal-detail-piutang-bhp"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="max-h-[80vh] overflow-y-auto p-6 space-y-6">
                <div id="detail-piutang-bhp-loading" class="hidden">
                    <div
                        class="rounded-xl border border-slate-200 dark:border-slate-700 p-6 text-center text-slate-500">
                        Memuat detail data...
                    </div>
                </div>

                <div id="detail-piutang-bhp-content" class="hidden space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                            <h4 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-4">Informasi Piutang
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-slate-500">No Referensi</p>
                                    <p id="detail-bhp-no-referensi"
                                        class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Status Piutang</p>
                                    <div id="detail-bhp-status-piutang">-</div>
                                </div>
                                <div>
                                    <p class="text-slate-500">Tanggal Piutang</p>
                                    <p id="detail-bhp-tanggal-piutang"
                                        class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Tanggal Jatuh Tempo</p>
                                    <p id="detail-bhp-tanggal-jatuh-tempo"
                                        class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Tanggal Pelunasan</p>
                                    <p id="detail-bhp-tanggal-pelunasan"
                                        class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Total Piutang</p>
                                    <p id="detail-bhp-total-piutang" class="font-bold text-emerald-600">-</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Metode Penerimaan</p>
                                    <p id="detail-bhp-metode-penerimaan"
                                        class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Bukti Penerimaan</p>
                                    <p id="detail-bhp-bukti-penerimaan"
                                        class="font-semibold text-slate-800 dark:text-slate-100 break-all">-</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                            <h4 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-4">Informasi Supplier &
                                User</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-slate-500">Nama Supplier</p>
                                    <p id="detail-bhp-nama-supplier"
                                        class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Kontak Person</p>
                                    <p id="detail-bhp-kontak-person"
                                        class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">No HP</p>
                                    <p id="detail-bhp-no-hp" class="font-semibold text-slate-800 dark:text-slate-100">
                                        -</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Email</p>
                                    <p id="detail-bhp-email" class="font-semibold text-slate-800 dark:text-slate-100">
                                        -</p>
                                </div>
                                <div class="sm:col-span-2">
                                    <p class="text-slate-500">Alamat</p>
                                    <p id="detail-bhp-alamat"
                                        class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Dibuat Oleh</p>
                                    <p id="detail-bhp-dibuat-oleh"
                                        class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">Diupdate Oleh</p>
                                    <p id="detail-bhp-diupdate-oleh"
                                        class="font-semibold text-slate-800 dark:text-slate-100">-</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700">
                            <h4 class="text-base font-bold text-slate-800 dark:text-slate-100">Item Detail Piutang</h4>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-emerald-500 text-white">
                                    <tr>
                                        <th class="px-4 py-3 text-left">No</th>
                                        <th class="px-4 py-3 text-left">Kode</th>
                                        <th class="px-4 py-3 text-left">Nama Item</th>
                                        <th class="px-4 py-3 text-right">Qty</th>
                                        <th class="px-4 py-3 text-right">Harga</th>
                                        <th class="px-4 py-3 text-right">Subtotal</th>
                                        <th class="px-4 py-3 text-left">Diskon</th>
                                        <th class="px-4 py-3 text-right">Total Akhir</th>
                                    </tr>
                                </thead>
                                <tbody id="detail-bhp-items-body"
                                    class="divide-y divide-slate-200 dark:divide-slate-700"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="detail-piutang-bhp-empty" class="hidden">
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        Data detail tidak ditemukan.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@vite(['resources/js/kasir/piutang-bahan-habis-pakai/data-piutang-bahan-habis-pakai.js'])
