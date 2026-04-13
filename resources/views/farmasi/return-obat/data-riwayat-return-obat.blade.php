<section class="space-y-5">
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
        <div class="flex items-start gap-3 md:flex-1">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-md shrink-0">
                <i class="fa-solid fa-rotate-left text-lg"></i>
            </div>

            <div class="min-w-0">
                <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Riwayat Return Obat
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Lihat histori transaksi return obat ke supplier, pantau status proses, dan buka detail setiap
                    transaksi.
                </p>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-3 sm:px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div class="flex items-center gap-2 text-sm w-full md:w-auto">
                    <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>

                    <select id="riwayat-return-obat-page-length"
                        class="w-full sm:w-40 md:w-32 border border-slate-300 dark:border-slate-600 text-sm rounded-lg focus:ring-amber-500 focus:border-amber-500 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-2">
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

                        <input type="text" id="riwayat-return-obat-search-input"
                            class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm text-slate-800 dark:text-slate-100 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700 focus:ring-amber-500 focus:border-amber-500"
                            placeholder="Cari kode return, supplier, depot...">
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="table-riwayat-return-obat"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100 border-t border-slate-100 dark:border-slate-700 whitespace-nowrap">
                <thead
                    class="text-[11px] md:text-xs font-semibold uppercase bg-gradient-to-r from-amber-500 via-orange-500 to-orange-600 text-white tracking-wide">
                    <tr>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">No</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Kode Return</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Tanggal Return</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Supplier</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Depot</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Status</th>
                        <th
                            class="px-3 sm:px-4 md:px-6 py-3 text-center whitespace-nowrap sticky right-0 z-10 bg-gradient-to-r from-amber-500 via-orange-500 to-orange-600">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
            </table>
        </div>

        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-3 sm:px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">
            <div id="riwayat-return-obat-custom-info" class="text-xs md:text-sm text-slate-600 dark:text-slate-300">
            </div>

            <div class="w-full md:w-auto overflow-x-auto">
                <ul id="riwayat-return-obat-custom-pagination"
                    class="min-w-max inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
                </ul>
            </div>
        </div>
    </div>
</section>

<div id="modal-detail-riwayat-return-obat"
    class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">

    <div class="w-full max-w-6xl max-h-[95vh] overflow-hidden rounded-3xl bg-white shadow-2xl">
        <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5">
            <div class="flex items-start gap-4">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-md">
                    <i class="fa-solid fa-circle-info text-lg"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Detail Return Obat</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Informasi lengkap transaksi return obat beserta item detail.
                    </p>
                </div>
            </div>

            <button type="button" id="button-close-modal-detail-riwayat-return-obat"
                class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="max-h-[calc(95vh-88px)] overflow-y-auto p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kode Return</p>
                    <p id="detail-riwayat-kode-return" class="mt-2 text-sm font-bold text-slate-800">-</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal Return</p>
                    <p id="detail-riayat-tanggal-return" class="mt-2 text-sm font-bold text-slate-800">-</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status Return</p>
                    <p id="detail-riwayat-status-return"
                        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-700">
                        -
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Return</p>
                    <p id="detail-riwayat-total-tagihan" class="mt-2 text-sm font-bold text-emerald-700">Rp 0</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h4 class="text-sm font-bold uppercase tracking-wide text-slate-700 mb-4">Informasi Supplier &
                        Depot</h4>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500">Supplier</span>
                            <span id="detail-riwayat-supplier" class="font-semibold text-slate-800 text-right">-</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500">Kontak Person</span>
                            <span id="detail-riwayat-kontak-person"
                                class="font-semibold text-slate-800 text-right">-</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500">Depot</span>
                            <span id="detail-riwayat-depot" class="font-semibold text-slate-800 text-right">-</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h4 class="text-sm font-bold uppercase tracking-wide text-slate-700 mb-4">Informasi Piutang
                    </h4>

                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500">No Referensi</span>
                            <span id="detail-riwayat-no-referensi"
                                class="font-semibold text-slate-800 text-right">-</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500">Status Piutang</span>
                            <span id="detail-riwayat-status-piutang"
                                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-700">
                                -
                            </span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500">Total Piutang</span>
                            <span id="detail-riwayat-total-piutang" class="font-semibold text-slate-800 text-right">Rp
                                0</span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500">Tanggal Piutang</span>
                            <span id="detail-riwayat-tanggal-piutang"
                                class="font-semibold text-slate-800 text-right">-</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <h4 class="text-sm font-bold uppercase tracking-wide text-slate-700 mb-4">Keterangan</h4>
                <p id="detail-riwayat-keterangan" class="text-sm text-slate-700 leading-relaxed">-</p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200">
                    <h4 class="text-sm font-bold uppercase tracking-wide text-slate-700">Detail Item Return</h4>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-700 whitespace-nowrap">
                        <thead class="bg-slate-100 text-slate-700">
                            <tr>
                                <th class="px-4 py-3">No</th>
                                <th class="px-4 py-3">Nama Obat</th>
                                <th class="px-4 py-3">Kode Obat</th>
                                <th class="px-4 py-3">Batch</th>
                                <th class="px-4 py-3">Kadaluarsa</th>
                                <th class="px-4 py-3 text-right">Qty</th>
                                <th class="px-4 py-3 text-right">Harga Beli</th>
                                <th class="px-4 py-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody id="detail-riwayat-return-obat-items" class="divide-y divide-slate-200">
                            <tr>
                                <td colspan="8" class="px-4 py-6 text-center text-slate-500">
                                    Belum ada data.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end border-t border-slate-200 pt-5">
                <button type="button" id="button-close-footer-modal-detail-return-obat"
                    class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@vite(['resources/js/farmasi/return-obat/data-riwayat-return-obat.js'])
