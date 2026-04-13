<section class="space-y-5">

    {{-- ============== HEADER ============== --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">

        {{-- KIRI: ICON + TITLE --}}
        <div class="flex items-start gap-3 md:flex-1">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl
                bg-gradient-to-br from-sky-500 to-cyan-500 text-white shadow-md shrink-0">
                <i class="fa-solid fa-clock-rotate-left text-lg"></i>
            </div>

            <div class="min-w-0">
                <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Riwayat Restock Bahan Habis Pakai
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Lihat dan telusuri seluruh data riwayat restock bahan habis pakai, mulai dari supplier, depot,
                    nomor faktur, hingga status restock.
                    <span class="hidden sm:inline">
                        Gunakan pencarian untuk menemukan data restock dengan lebih cepat dan akurat.
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

                    <select id="riwayat-restock-bhp-page-length"
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

                {{-- Search --}}
                <div class="w-full md:w-auto">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                        </span>

                        <input type="text" id="riwayat-restock-bhp-search-input"
                            class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm
                                   text-slate-800 dark:text-slate-100
                                   border border-slate-300 dark:border-slate-600 rounded-lg
                                   bg-slate-50 dark:bg-slate-700
                                   focus:ring-sky-500 focus:border-sky-500"
                            placeholder="Cari supplier, depot, no faktur, atau status restock...">
                    </div>

                    <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500 hidden sm:block">
                        Contoh: <span class="italic">Nama supplier, no faktur, nama depot, pending</span>.
                    </p>
                </div>

            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table id="table-riwayat-restock-bahan-habis-pakai"
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
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Tanggal Diterima</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Tanggal Jatuh Tempo</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Total Tagihan</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Status Restock</th>
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

            <div id="riwayat-restock-bhp-custom-info" class="text-xs md:text-sm text-slate-600 dark:text-slate-300">
            </div>

            <div class="w-full md:w-auto overflow-x-auto">
                <ul id="riwayat-restock-bhp-custom-pagination"
                    class="min-w-max inline-flex items-center gap-0 text-sm isolate rounded-lg
                           border border-slate-200 dark:border-slate-600 overflow-hidden">
                </ul>
            </div>
        </div>
    </div>

</section>

<div id="riwayat-restock-bhp-page"
    data-get-data-restock-bhp-detail-url="{{ route('farmasi.get.data.restock.bahan.habis.pakai.by.id', ':id') }}">
</div>

<div id="riwayat-modal-detail-restock-bhp"
    class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">

    <div class="w-full max-w-6xl max-h-[95vh] overflow-hidden rounded-3xl bg-white shadow-2xl">
        {{-- HEADER --}}
        <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5">
            <div class="flex items-start gap-4">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-cyan-600 text-white shadow-md">
                    <i class="fa-solid fa-file-invoice text-lg"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-800">
                        Detail Restock Obat
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Informasi lengkap transaksi restock obat.
                    </p>
                </div>
            </div>

            <button type="button" id="button-close-modal-detail-riwayat-restock-bhp"
                class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="max-h-[calc(95vh-88px)] overflow-y-auto p-6 space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                <div class="mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-blue-600"></i>
                    <h4 class="text-sm font-bold uppercase tracking-wide text-slate-700">
                        Data Transaksi
                    </h4>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4 text-sm">
                    <div>
                        <p class="text-slate-500">Supplier</p>
                        <p id="riwayat-restock-bhp-detail_supplier" class="font-semibold text-slate-800">-</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Depot</p>
                        <p id="riwayat-restock-bhp-detail_depot" class="font-semibold text-slate-800">-</p>
                    </div>
                    <div>
                        <p class="text-slate-500">No Faktur</p>
                        <p id="riwayat-restock-bhp-detail_no_faktur" class="font-semibold text-slate-800">-</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Dibuat Oleh</p>
                        <p id="riwayat-restock-bhp-detail_dibuat_oleh" class="font-semibold text-slate-800">-</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Tanggal Terima</p>
                        <p id="riwayat-restock-bhp-detail_tanggal_terima" class="font-semibold text-slate-800">-</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Tanggal Jatuh Tempo</p>
                        <p id="riwayat-restock-bhp-detail_tanggal_jatuh_tempo" class="font-semibold text-slate-800">-
                        </p>
                    </div>
                    <div>
                        <p class="text-slate-500">Status Restock</p>
                        <p id="riwayat-restock-bhp-detail_status_transaksi" class="font-semibold text-slate-800">-</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Dikonfirmasi Oleh</p>
                        <p id="riwayat-restock-bhp-detail_dikonfirmasi_oleh" class="font-semibold text-slate-800">-</p>
                    </div>
                    <div class="md:col-span-2 xl:col-span-3">
                        <p class="text-slate-500">Total Tagihan</p>
                        <p id="riwayat-restock-bhp-detail_total_tagihan" class="font-bold text-emerald-600 text-base">
                            Rp 0</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5">
                <div class="mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-capsules text-sky-600"></i>
                    <h4 class="text-sm font-bold uppercase tracking-wide text-slate-700">
                        Detail Item Restock
                    </h4>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm border border-slate-200 rounded-xl overflow-hidden">
                        <thead class="bg-slate-100 text-slate-700">
                            <tr>
                                <th class="px-4 py-3 text-left">No</th>
                                <th class="px-4 py-3 text-left">Obat</th>
                                <th class="px-4 py-3 text-left">Batch</th>
                                <th class="px-4 py-3 text-left">Kadaluarsa</th>
                                <th class="px-4 py-3 text-left">Qty</th>
                                <th class="px-4 py-3 text-left">Harga Beli</th>
                                <th class="px-4 py-3 text-left">Subtotal</th>
                                <th class="px-4 py-3 text-left">Diskon</th>
                                <th class="px-4 py-3 text-left">Amount Diskon</th>
                                <th class="px-4 py-3 text-left">Total</th>
                            </tr>
                        </thead>
                        <tbody id="detail-riwayat-restock-bhp-tbody" class="divide-y divide-slate-200">
                            <tr>
                                <td colspan="10" class="px-4 py-4 text-center text-slate-500">
                                    Belum ada data
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="border-t border-slate-200 px-6 py-4 flex justify-end">
            <button type="button" id="button-close-footer-modal-detail-riwayat-restock-bhp"
                class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Tutup
            </button>
        </div>
    </div>
</div>

@vite(['resources/js/farmasi/restock-bahan-habis-pakai/data-riwayat-restock-bahan-habis-pakai.js'])
