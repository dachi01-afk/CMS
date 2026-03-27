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
                    Monitoring Return Obat
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Pantau data return obat supplier dan buat transaksi return baru.
                </p>
            </div>
        </div>

        <div class="flex justify-center md:justify-end">
            <button id="button-open-modal-create-return-obat" type="button"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-xl shadow-md bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-amber-400">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Return Obat</span>
            </button>
        </div>
    </div>

    <div
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-3 sm:px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                <div class="flex items-center gap-2 text-sm w-full md:w-auto">
                    <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>

                    <select id="return-obat-page-length"
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

                        <input type="text" id="return-obat-search-input"
                            class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm text-slate-800 dark:text-slate-100 border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700 focus:ring-amber-500 focus:border-amber-500"
                            placeholder="Cari kode return, supplier, depot...">
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="table-return-obat"
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
            <div id="return-obat-custom-info" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>

            <div class="w-full md:w-auto overflow-x-auto">
                <ul id="return-obat-custom-pagination"
                    class="min-w-max inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
                </ul>
            </div>
        </div>
    </div>
</section>

<div id="return-obat-page" data-batch-url="{{ route('farmasi.get.data.batch.by.obat.id', ':obatId') }}"
    data-stock-batch-url="{{ route('farmasi.get.stok.batch.obat.depot', [':batchObatId', ':depotId']) }}"
    data-get-supplier-url="{{ route('farmasi.get.data.supplier') }}"
    data-get-depot-by-supplier-url="{{ route('farmasi.get.depot.by.supplier') }}"
    data-get-obat-by-supplier-depot-url="{{ route('farmasi.get.obat.by.supplier.depot') }}"
    data-create-return-obat-url="{{ route('farmasi.create.data.return.obat') }}"
    data-detail-return-url="{{ route('farmasi.get.data.return.obat.by.no.return', ':kodeReturn') }}">

    <div id="modal-create-return-obat"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">

        <div class="w-full max-w-7xl max-h-[95vh] overflow-hidden rounded-3xl bg-white shadow-2xl">
            <div class="flex items-start justify-between border-b border-slate-200 px-6 py-5">
                <div class="flex items-start gap-4">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md">
                        <i class="fa-solid fa-boxes-stacked text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">
                            Form Return Obat
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">
                            Lengkapi data transaksi return dan detail item obat.
                        </p>
                    </div>
                </div>

                <button type="button" id="button-close-modal-create-return-obat"
                    class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="max-h-[calc(95vh-88px)] overflow-y-auto">
                <form id="form-create-return-obat" method="POST" class="p-6 space-y-6">
                    @csrf

                    <div class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                        <div class="mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-file-invoice text-emerald-600"></i>
                            <h4 class="text-sm font-bold uppercase tracking-wide text-slate-700">
                                Data Transaksi Return
                            </h4>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Kode Return</label>
                                <input type="text" name="kode_return"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-100 px-4 py-2.5 text-sm text-slate-700"
                                    placeholder="Otomatis dari sistem" readonly>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal Return</label>
                                <input type="date" name="tanggal_return" value="{{ now()->format('Y-m-d') }}"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-2 mt-4">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Supplier</label>
                                <select name="supplier_id" id="supplier_id"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                                    <option value="">Pilih supplier</option>
                                </select>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-slate-700">Depot</label>
                                <select name="depot_id" id="depot_id"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100">
                                    <option value="">Pilih depot</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Keterangan</label>
                            <textarea name="keterangan" rows="3"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100"
                                placeholder="Catatan return obat..."></textarea>
                        </div>

                        <div class="mt-4">
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Total Return</label>
                            <div class="relative">
                                <span
                                    class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-500">Rp</span>
                                <input type="text" id="grand-total-display" name="total_tagihan"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-100 pl-10 pr-4 py-2.5 text-sm font-semibold text-slate-700"
                                    value="0" readonly>
                                <input type="hidden" id="grand-total-input" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-capsules text-sky-600"></i>
                                <h4 class="text-sm font-bold uppercase tracking-wide text-slate-700">
                                    Detail Item Return
                                </h4>
                            </div>

                            <button type="button" id="button-add-detail-row"
                                class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-600">
                                <i class="fa-solid fa-plus text-xs"></i>
                                Tambah Item
                            </button>
                        </div>

                        <div id="return-detail-container" class="space-y-4">
                            <div class="detail-row rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="grid grid-cols-1 gap-4 xl:grid-cols-4">
                                    <div class="xl:col-span-2">
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Obat</label>
                                        <select name="details[0][obat_id]"
                                            class="obat-select w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
                                            <option value="">Pilih obat</option>
                                        </select>
                                    </div>

                                    <div class="xl:col-span-2">
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Batch
                                            Obat</label>
                                        <select
                                            class="batch-obat-select w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
                                            <option value="">Pilih batch obat</option>
                                        </select>

                                        <input type="hidden" name="details[0][batch_obat_id]"
                                            class="batch-obat-id-input" value="">
                                        <input type="hidden" name="details[0][batch_nama]"
                                            class="batch-obat-nama-input" value="">

                                        <p class="mt-1 text-xs text-slate-500">
                                            Pilih batch existing dari depot yang dipilih.
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4 xl:grid-cols-4 my-2.5">
                                    <div class="xl:col-span-1">
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal
                                            Kadaluarsa</label>
                                        <input type="date" name="details[0][tanggal_kadaluarsa_obat]"
                                            class="tanggal-kadaluarsa-input w-full rounded-xl border border-slate-300 bg-slate-100 px-3 py-2.5 text-sm"
                                            readonly>
                                    </div>

                                    <div class="xl:col-span-1">
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Stok
                                            Tersedia</label>
                                        <input type="text"
                                            class="stok-tersedia-display w-full rounded-xl border border-slate-300 bg-slate-100 px-3 py-2.5 text-sm font-semibold text-slate-700"
                                            value="0" readonly>
                                        <input type="hidden" name="details[0][stok_tersedia]"
                                            class="stok-tersedia-input" value="0">
                                    </div>

                                    <div class="xl:col-span-1">
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Qty</label>
                                        <input type="number" name="details[0][qty]" min="1" value="1"
                                            class="detail-qty w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
                                    </div>

                                    <div class="xl:col-span-1">
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Harga
                                            Beli</label>
                                        <div class="relative">
                                            <span
                                                class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-500">
                                                Rp
                                            </span>
                                            <input type="text"
                                                class="detail-harga-beli-display w-full rounded-xl border border-slate-300 bg-white pl-10 pr-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100"
                                                value="0" placeholder="0">
                                            <input type="hidden" name="details[0][harga_beli]"
                                                class="detail-harga-beli" value="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-4 xl:grid-cols-2 my-2.5">
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

                                    <div class="xl:col-span-1 flex items-end justify-end">
                                        <button type="button"
                                            class="button-remove-detail-row inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-600">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                            Hapus
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="text-sm text-slate-500">
                            Pastikan supplier, depot, batch, stok, qty, dan harga beli sudah sesuai.
                        </div>

                        <div class="flex flex-col-reverse gap-2 sm:flex-row">
                            <button type="button" id="button-cancel-modal-create-return-obat"
                                class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                Batal
                            </button>

                            <button type="submit"
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-emerald-600 hover:to-teal-700">
                                <i class="fa-solid fa-floppy-disk text-xs"></i>
                                Simpan Return
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <template id="template-detail-row-return-obat">
        <div class="detail-row rounded-2xl border border-slate-200 bg-slate-50 p-4">
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-4">
                <div class="xl:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Obat</label>
                    <select name="details[__INDEX__][obat_id]"
                        class="obat-select w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
                        <option value="">Pilih obat</option>
                    </select>
                </div>

                <div class="xl:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Batch Obat</label>
                    <select
                        class="batch-obat-select w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm focus:border-sky-500 focus:outline-none focus:ring-4 focus:ring-sky-100">
                        <option value="">Pilih batch obat</option>
                    </select>

                    <input type="hidden" name="details[__INDEX__][batch_obat_id]" class="batch-obat-id-input"
                        value="">
                    <input type="hidden" name="details[__INDEX__][batch_nama]" class="batch-obat-nama-input"
                        value="">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-4 my-2.5">
                <div class="xl:col-span-1">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Tanggal Kadaluarsa</label>
                    <input type="date" name="details[__INDEX__][tanggal_kadaluarsa_obat]"
                        class="tanggal-kadaluarsa-input w-full rounded-xl border border-slate-300 bg-slate-100 px-3 py-2.5 text-sm"
                        readonly>
                </div>

                <div class="xl:col-span-1">
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Stok Tersedia</label>
                    <input type="text"
                        class="stok-tersedia-display w-full rounded-xl border border-slate-300 bg-slate-100 px-3 py-2.5 text-sm font-semibold text-slate-700"
                        value="0" readonly>
                    <input type="hidden" name="details[__INDEX__][stok_tersedia]" class="stok-tersedia-input"
                        value="0">
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
                            class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-500">
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

            <div class="grid grid-cols-1 gap-4 xl:grid-cols-2 my-2.5">
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

                <div class="xl:col-span-1 flex items-end justify-end">
                    <button type="button"
                        class="button-remove-detail-row inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-600">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </template>

    <div id="modal-detail-return-obat"
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

                <button type="button" id="button-close-modal-detail-return-obat"
                    class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="max-h-[calc(95vh-88px)] overflow-y-auto p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kode Return</p>
                        <p id="detail-kode-return" class="mt-2 text-sm font-bold text-slate-800">-</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal Return</p>
                        <p id="detail-tanggal-return" class="mt-2 text-sm font-bold text-slate-800">-</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status Return</p>
                        <p id="detail-status-return" class="mt-2 text-sm font-bold text-slate-800">-</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Return</p>
                        <p id="detail-total-tagihan" class="mt-2 text-sm font-bold text-emerald-700">Rp 0</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <h4 class="text-sm font-bold uppercase tracking-wide text-slate-700 mb-4">Informasi Supplier &
                            Depot</h4>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">Supplier</span>
                                <span id="detail-supplier" class="font-semibold text-slate-800 text-right">-</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">Kontak Person</span>
                                <span id="detail-kontak-person"
                                    class="font-semibold text-slate-800 text-right">-</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">Depot</span>
                                <span id="detail-depot" class="font-semibold text-slate-800 text-right">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <h4 class="text-sm font-bold uppercase tracking-wide text-slate-700 mb-4">Informasi Piutang
                        </h4>

                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">No Referensi</span>
                                <span id="detail-no-referensi"
                                    class="font-semibold text-slate-800 text-right">-</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">Status Piutang</span>
                                <span id="detail-status-piutang"
                                    class="font-semibold text-slate-800 text-right">-</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">Total Piutang</span>
                                <span id="detail-total-piutang" class="font-semibold text-slate-800 text-right">Rp
                                    0</span>
                            </div>
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">Tanggal Piutang</span>
                                <span id="detail-tanggal-piutang"
                                    class="font-semibold text-slate-800 text-right">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                    <h4 class="text-sm font-bold uppercase tracking-wide text-slate-700 mb-4">Keterangan</h4>
                    <p id="detail-keterangan" class="text-sm text-slate-700 leading-relaxed">-</p>
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
                            <tbody id="detail-return-obat-items" class="divide-y divide-slate-200">
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
</div>

@vite(['resources/js/farmasi/return-obat/data-return-obat.js'])
