<section class="space-y-5">

    {{-- ============== HEADER ============== --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">

        {{-- KIRI: ICON + TITLE --}}
        <div class="flex items-start gap-3 md:flex-1">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl
           bg-gradient-to-br from-sky-500 to-cyan-500 text-white shadow-md shrink-0">
                <i class="fa-solid fa-pills text-lg"></i>
            </div>

            <div class="min-w-0">
                <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Monitoring Restock Obat
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Pantau obat dengan stok menipis dan proses pengajuan restock secara teratur.
                    <span class="hidden sm:inline">
                        Gunakan pencarian untuk menemukan data obat lebih cepat.
                    </span>
                </p>
            </div>
        </div>

        {{-- KANAN: TOMBOL --}}
        <div class="flex justify-center md:justify-end">
            <button id="button-open-modal-create-restock-obat" type="button"
                class="inline-flex items-center gap-2 px-5 py-2.5
           text-sm font-semibold text-white rounded-xl shadow-md
           bg-gradient-to-r from-emerald-500 to-teal-600
           hover:from-emerald-600 hover:to-teal-700
           focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Pemesanan Restock</span>
            </button>
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

                    <select id="restock-obat-page-length"
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

                        <input type="text" id="restock-obat-search-input"
                            class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm
                                   text-slate-800 dark:text-slate-100
                                   border border-slate-300 dark:border-slate-600 rounded-lg
                                   bg-slate-50 dark:bg-slate-700
                                   focus:ring-sky-500 focus:border-sky-500"
                            placeholder="Cari dokter, pasien, antrian, obat...">
                    </div>

                    <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500 hidden sm:block">
                        Contoh: <span class="italic">Nama pasien, nomor antrian, nama obat</span>.
                    </p>
                </div>

            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table id="table-restock-obat"
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
                        {{-- Sticky Action --}}
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

            <div id="restock-obat-custom-info" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>

            {{-- Pagination aman di HP --}}
            <div class="w-full md:w-auto overflow-x-auto">
                <ul id="restock-obat-custom-pagination"
                    class="min-w-max inline-flex items-center gap-0 text-sm isolate rounded-lg
                           border border-slate-200 dark:border-slate-600 overflow-hidden">
                </ul>
            </div>
        </div>
    </div>

</section>

<div id="restock-obat-page" data-batch-url="{{ route('farmasi.get.data.batch.obat.by.obat.id', ':obatId') }}"
    data-get-supplier-url="{{ route('get.data.supplier') }}"
    data-create-supplier-url="{{ route('create.data.supplier') }}"
    data-create-restock-obat-url="{{ route('farmasi.create.data.restock.obat') }}"
    data-get-data-restock-obat-detail-url="{{ route('farmasi.get.data.restock.obat.by.id', ':id') }}">
</div>

{{-- MODAL CREATE RESTOCK OBAT --}}
<div id="modal-create-restock-obat"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">

    <div class="w-full max-w-7xl max-h-[95vh] overflow-hidden rounded-3xl bg-white shadow-2xl">
        {{-- HEADER --}}
        <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5">
            <div class="flex items-start gap-4">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md">
                    <i class="fa-solid fa-boxes-stacked text-lg"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-800">
                        Form Pemesanan Restock Obat
                    </h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Lengkapi data transaksi restock dan detail item obat.
                    </p>
                </div>
            </div>

            <button type="button" id="button-close-modal-create-restock-obat"
                class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- BODY --}}
        <div class="max-h-[calc(95vh-88px)] overflow-y-auto">
            <form id="form-create-restock-obat" action="#" method="POST" class="p-6 space-y-6">
                @csrf

                {{-- SECTION: DATA TRANSAKSI --}}
                <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                    <div class="mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice text-emerald-600"></i>
                        <h4 class="text-sm font-bold uppercase tracking-wide text-slate-700">
                            Data Transaksi Restock
                        </h4>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Supplier</label>

                            <div class="flex gap-2">
                                <select name="supplier_id" id="supplier_id"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                                    <option value="">Pilih supplier</option>
                                </select>

                                <button type="button" id="button-open-modal-create-supplier"
                                    class="inline-flex shrink-0 items-center justify-center rounded-xl bg-sky-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-sky-600">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Depot</label>
                            <select name="depot_id" id="depot_id"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                                <option value="">Pilih depot</option>
                                @foreach ($dataDepot as $depot)
                                    <option value="{{ $depot->id }}">{{ $depot->nama_depot }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-2 my-4">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">No Faktur</label>
                            <input type="text" name="no_faktur"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                                placeholder="Contoh: INV-RST-0001">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal Jatuh Tempo</label>
                            <input type="date" name="tanggal_jatuh_tempo"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                        </div>
                    </div>

                    <div class="xl:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Total Tagihan</label>
                        <div class="relative">
                            <span
                                class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-500">Rp</span>
                            <input type="text" id="grand-total-display"
                                class="w-full rounded-xl border border-slate-300 bg-slate-100 pl-10 pr-4 py-2.5 text-sm font-semibold text-slate-700"
                                value="0" readonly>
                            <input type="hidden" name="total_tagihan" id="grand-total-input" value="0">
                        </div>
                    </div>
                </div>

                {{-- SECTION: DETAIL OBAT --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-capsules text-sky-600"></i>
                            <h4 class="text-sm font-bold uppercase tracking-wide text-slate-700">
                                Detail Item Restock
                            </h4>
                        </div>

                        <button type="button" id="button-add-detail-row"
                            class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-600">
                            <i class="fa-solid fa-plus text-xs"></i>
                            Tambah Item
                        </button>
                    </div>

                    <div id="restock-detail-container" class="space-y-4">
                        {{-- ROW DETAIL --}}
                        <div class="detail-row rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <div class="grid grid-cols-1 gap-4 xl:grid-cols-4">
                                <div class="xl:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Obat</label>
                                    <select name="details[0][obat_id]"
                                        class="obat-select w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
                                        <option value="">Pilih obat</option>
                                        @foreach ($dataObat as $obat)
                                            <option value="{{ $obat->id }}">{{ $obat->nama_obat }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="xl:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Batch Obat</label>
                                    <select
                                        class="batch-obat-select w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
                                        <option value="">Pilih / ketik batch baru</option>
                                    </select>

                                    <input type="hidden" name="details[0][batch_obat_id]"
                                        class="batch-obat-id-input" value="">
                                    <input type="hidden" name="details[0][batch_nama]" class="batch-obat-nama-input"
                                        value="">

                                    <p class="mt-1 text-xs text-slate-500">
                                        Pilih batch existing jika sama. Jika belum ada, ketik nama batch baru lalu tekan
                                        enter.
                                    </p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 xl:grid-cols-3 my-2.5">
                                <div class="xl:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal
                                        Kadaluarsa</label>
                                    <input type="date" name="details[0][tanggal_kadaluarsa_obat]"
                                        class="tanggal-kadaluarsa-input w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
                                </div>

                                <div class="xl:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Qty</label>
                                    <input type="number" name="details[0][qty]" min="1" value="1"
                                        class="detail-qty w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
                                </div>

                                <div class="xl:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Harga Beli</label>
                                    <div class="relative">
                                        <span
                                            class="detail-harga-prefix pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-500">
                                            Rp
                                        </span>
                                        <input type="text"
                                            class="detail-harga-beli-display w-full rounded-xl border border-slate-300 bg-white pl-10 pr-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100"
                                            value="0" placeholder="0">
                                        <input type="hidden" name="details[0][harga_beli]" class="detail-harga-beli"
                                            value="0">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 xl:grid-cols-3 my-2.5">
                                <div class="xl:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Subtotal</label>
                                    <div class="relative">
                                        <span
                                            class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-500">Rp</span>
                                        <input type="text"
                                            class="detail-subtotal-display w-full rounded-xl border border-slate-300 bg-slate-100 pl-10 pr-4 py-2.5 text-sm font-semibold text-slate-700"
                                            value="0" readonly>
                                        <input type="hidden" name="details[0][subtotal]"
                                            class="detail-subtotal-input" value="0">
                                    </div>
                                </div>

                                <div class="xl:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Tipe Diskon</label>
                                    <select name="details[0][diskon_type]"
                                        class="detail-diskon-type w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
                                        <option value="">Tanpa diskon</option>
                                        <option value="nominal">Nominal</option>
                                        <option value="persen">Persen</option>
                                    </select>
                                </div>

                                <div class="xl:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Nilai Diskon</label>
                                    <div class="relative">
                                        <span
                                            class="detail-diskon-prefix pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-500">
                                            Rp
                                        </span>
                                        <input type="text"
                                            class="detail-diskon-value-display w-full rounded-xl border border-slate-300 bg-white pl-10 pr-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100"
                                            value="0" placeholder="0">
                                        <input type="hidden" name="details[0][diskon_value]"
                                            class="detail-diskon-value" value="0">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2 my-2.5">
                                <div class="xl:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Amount
                                        Diskon</label>
                                    <div class="relative">
                                        <span
                                            class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-500">Rp</span>
                                        <input type="text"
                                            class="detail-diskon-amount-display w-full rounded-xl border border-slate-300 bg-slate-100 pl-10 pr-4 py-2.5 text-sm font-semibold text-slate-700"
                                            value="0" readonly>
                                        <input type="hidden" name="details[0][diskon_amount]"
                                            class="detail-diskon-amount-input" value="0">
                                    </div>
                                </div>

                                <div class="xl:col-span-1">
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Total Setelah
                                        Diskon</label>
                                    <div class="relative">
                                        <span
                                            class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-emerald-600">Rp</span>
                                        <input type="text"
                                            class="detail-total-display w-full rounded-xl border border-emerald-200 bg-emerald-50 pl-10 pr-4 py-2.5 text-sm font-bold text-emerald-700"
                                            value="0" readonly>
                                        <input type="hidden" name="details[0][total_setelah_diskon]"
                                            class="detail-total-input" value="0">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4 flex justify-end">
                                <button type="button"
                                    class="button-remove-detail-row inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-600">
                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                    Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div
                    class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm text-slate-500">
                        Pastikan data supplier, depot, batch, qty, dan harga beli sudah sesuai.
                    </div>

                    <div class="flex flex-col-reverse gap-2 sm:flex-row">
                        <button type="button" id="button-cancel-modal-create-restock-obat"
                            class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Batal
                        </button>

                        <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-emerald-600 hover:to-teal-700">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            Simpan Restock
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<template id="template-detail-row-restock-obat">
    <div class="detail-row rounded-2xl border border-slate-200 bg-slate-50 p-4">
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-4">
            <div class="xl:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Obat</label>
                <select name="details[__INDEX__][obat_id]"
                    class="obat-select w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
                    <option value="">Pilih obat</option>
                    @foreach ($dataObat as $obat)
                        <option value="{{ $obat->id }}">{{ $obat->nama_obat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="xl:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Batch Obat</label>
                <select
                    class="batch-obat-select w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
                    <option value="">Pilih / ketik batch baru</option>
                </select>

                <input type="hidden" name="details[__INDEX__][batch_obat_id]" class="batch-obat-id-input"
                    value="">
                <input type="hidden" name="details[__INDEX__][batch_nama]" class="batch-obat-nama-input"
                    value="">

                <p class="mt-1 text-xs text-slate-500">
                    Pilih batch existing jika sama. Jika belum ada, ketik nama batch baru lalu tekan enter.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3 my-2.5">
            <div class="xl:col-span-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal Kadaluarsa</label>
                <input type="date" name="details[__INDEX__][tanggal_kadaluarsa_obat]"
                    class="tanggal-kadaluarsa-input w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
            </div>

            <div class="xl:col-span-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Qty</label>
                <input type="number" name="details[__INDEX__][qty]" min="1" value="1"
                    class="detail-qty w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
            </div>

            <div class="xl:col-span-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Harga Beli</label>
                <div class="relative">
                    <span
                        class="detail-harga-prefix pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-500">
                        Rp
                    </span>
                    <input type="text"
                        class="detail-harga-beli-display w-full rounded-xl border border-slate-300 bg-white pl-10 pr-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100"
                        value="0" placeholder="0">
                    <input type="hidden" name="details[__INDEX__][harga_beli]" class="detail-harga-beli"
                        value="0">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3 my-2.5">
            <div class="xl:col-span-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Subtotal</label>
                <div class="relative">
                    <span
                        class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-500">Rp</span>
                    <input type="text"
                        class="detail-subtotal-display w-full rounded-xl border border-slate-300 bg-slate-100 pl-10 pr-4 py-2.5 text-sm font-semibold text-slate-700"
                        value="0" readonly>
                    <input type="hidden" name="details[__INDEX__][subtotal]" class="detail-subtotal-input"
                        value="0">
                </div>
            </div>

            <div class="xl:col-span-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Tipe Diskon</label>
                <select name="details[__INDEX__][diskon_type]"
                    class="detail-diskon-type w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
                    <option value="">Tanpa diskon</option>
                    <option value="nominal">Nominal</option>
                    <option value="persen">Persen</option>
                </select>
            </div>

            <div class="xl:col-span-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Nilai Diskon</label>
                <div class="relative">
                    <span
                        class="detail-diskon-prefix pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-500">
                        Rp
                    </span>
                    <input type="text"
                        class="detail-diskon-value-display w-full rounded-xl border border-slate-300 bg-white pl-10 pr-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100"
                        value="0" placeholder="0">
                    <input type="hidden" name="details[__INDEX__][diskon_value]" class="detail-diskon-value"
                        value="0">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2 my-2.5">
            <div class="xl:col-span-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Amount Diskon</label>
                <div class="relative">
                    <span
                        class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-500">Rp</span>
                    <input type="text"
                        class="detail-diskon-amount-display w-full rounded-xl border border-slate-300 bg-slate-100 pl-10 pr-4 py-2.5 text-sm font-semibold text-slate-700"
                        value="0" readonly>
                    <input type="hidden" name="details[__INDEX__][diskon_amount]" class="detail-diskon-amount-input"
                        value="0">
                </div>
            </div>

            <div class="xl:col-span-1">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Total Setelah Diskon</label>
                <div class="relative">
                    <span
                        class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-emerald-600">Rp</span>
                    <input type="text"
                        class="detail-total-display w-full rounded-xl border border-emerald-200 bg-emerald-50 pl-10 pr-4 py-2.5 text-sm font-bold text-emerald-700"
                        value="0" readonly>
                    <input type="hidden" name="details[__INDEX__][total_setelah_diskon]" class="detail-total-input"
                        value="0">
                </div>
            </div>
        </div>

        <div class="mb-4 flex justify-end">
            <button type="button"
                class="button-remove-detail-row inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-600">
                <i class="fa-solid fa-trash-can text-xs"></i>
                Hapus
            </button>
        </div>
    </div>
</template>

<div id="modal-create-supplier"
    class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">

    <div class="w-full max-w-2xl rounded-3xl bg-white shadow-2xl overflow-hidden">
        <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5">
            <div class="flex items-start gap-4">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-500 text-white shadow-md">
                    <i class="fa-solid fa-truck-field text-lg"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Tambah Supplier</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Lengkapi data supplier baru untuk kebutuhan restock obat.
                    </p>
                </div>
            </div>

            <button type="button" id="button-close-modal-create-supplier"
                class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <form id="form-create-supplier" class="space-y-5 p-6">
            @csrf

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="create_supplier_nama_supplier"
                        class="mb-1.5 block text-sm font-medium text-slate-700">
                        Nama Supplier <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="create_supplier_nama_supplier" name="nama_supplier"
                        class="placeholder:text-slate-400 w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100"
                        placeholder="Masukkan nama supplier">
                    <p class="mt-1 text-xs text-rose-500 hidden error-text" data-error-for="nama_supplier"></p>
                </div>

                <div>
                    <label for="create_supplier_kontak_person"
                        class="mb-1.5 block text-sm font-medium text-slate-700">
                        Kontak Person
                    </label>
                    <input type="text" id="create_supplier_kontak_person" name="kontak_person"
                        class="placeholder:text-slate-400 w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100"
                        placeholder="Masukkan nama kontak person">
                    <p class="mt-1 text-xs text-rose-500 hidden error-text" data-error-for="kontak_person"></p>
                </div>

                <div>
                    <label for="create_supplier_no_hp" class="mb-1.5 block text-sm font-medium text-slate-700">
                        No HP
                    </label>
                    <input type="text" id="create_supplier_no_hp" name="no_hp"
                        class="placeholder:text-slate-400 w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100"
                        placeholder="Masukkan nomor HP">
                    <p class="mt-1 text-xs text-rose-500 hidden error-text" data-error-for="no_hp"></p>
                </div>

                <div class="md:col-span-2">
                    <label for="create_supplier_email" class="mb-1.5 block text-sm font-medium text-slate-700">
                        Email
                    </label>
                    <input type="email" id="create_supplier_email" name="email"
                        class="placeholder:text-slate-400 w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100"
                        placeholder="Masukkan email supplier">
                    <p class="mt-1 text-xs text-rose-500 hidden error-text" data-error-for="email"></p>
                </div>

                <div class="md:col-span-2">
                    <label for="create_supplier_alamat" class="mb-1.5 block text-sm font-medium text-slate-700">
                        Alamat
                    </label>
                    <textarea id="create_supplier_alamat" name="alamat" rows="3"
                        class="placeholder:text-slate-400 w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100"
                        placeholder="Masukkan alamat supplier"></textarea>
                    <p class="mt-1 text-xs text-rose-500 hidden error-text" data-error-for="alamat"></p>
                </div>

                <div class="md:col-span-2">
                    <label for="create_supplier_keterangan" class="mb-1.5 block text-sm font-medium text-slate-700">
                        Keterangan
                    </label>
                    <textarea id="create_supplier_keterangan" name="keterangan" rows="3"
                        class="placeholder:text-slate-400 w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100"
                        placeholder="Tambahkan keterangan supplier"></textarea>
                    <p class="mt-1 text-xs text-rose-500 hidden error-text" data-error-for="keterangan"></p>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end border-t border-slate-200 pt-4">
                <button type="button" id="button-cancel-modal-create-supplier"
                    class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </button>

                <button type="submit" id="button-submit-create-supplier"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-cyan-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-sky-600 hover:to-cyan-700">
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    Simpan Supplier
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modal-detail-restock-obat"
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

            <button type="button" id="button-close-modal-detail-restock-obat"
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

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3 text-sm">
                    <div>
                        <p class="text-slate-500">Supplier</p>
                        <p id="detail_supplier" class="font-semibold text-slate-800">-</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Depot</p>
                        <p id="detail_depot" class="font-semibold text-slate-800">-</p>
                    </div>
                    <div>
                        <p class="text-slate-500">No Faktur</p>
                        <p id="detail_no_faktur" class="font-semibold text-slate-800">-</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Tanggal Jatuh Tempo</p>
                        <p id="detail_tanggal_jatuh_tempo" class="font-semibold text-slate-800">-</p>
                    </div>
                    <div>
                        <p class="text-slate-500">Status Restock</p>
                        <p id="detail_status_transaksi" class="font-semibold text-slate-800">-</p>
                    </div>
                    <div class="md:col-span-2 xl:col-span-3">
                        <p class="text-slate-500">Total Tagihan</p>
                        <p id="detail_total_tagihan" class="font-bold text-emerald-600 text-base">Rp 0</p>
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
                        <tbody id="detail-restock-obat-tbody" class="divide-y divide-slate-200">
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
            <button type="button" id="button-close-footer-modal-detail-restock-obat"
                class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Tutup
            </button>
        </div>
    </div>
</div>

@vite(['resources/js/farmasi/restock-obat/data-restock-obat.js'])
