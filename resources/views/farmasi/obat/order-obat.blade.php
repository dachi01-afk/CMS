<!-- Header -->
<div class="mb-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Order Obat</h2>
            <p class="text-sm text-slate-500 mt-1">Kelola transaksi order obat pasien dengan tampilan yang lebih rapi dan
                cepat.</p>
        </div>

        <div class="flex items-center gap-2">
            <button id="btn-open-modal-penjualan-obat"
                class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-300">
                <span class="text-base">+</span>
                Tambah Order
            </button>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 bg-slate-50/70 px-5 py-4">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <div>
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">
                        Tampilkan
                    </label>
                    <select id="penjualan-obat-page-length"
                        class="rounded-xl border border-slate-300 px-3 py-2 text-sm text-slate-700 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <div class="w-full lg:w-80">
                <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-slate-500">
                    Cari transaksi
                </label>
                <input type="text" id="penjualan-obat-search-input"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100"
                    placeholder="Cari kode transaksi / pasien / obat...">
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="penjualanObatTable" class="w-full text-sm text-left text-slate-700">
            <thead class="bg-slate-800 text-xs uppercase tracking-wider text-white">
                <tr>
                    <th class="px-5 py-4">No</th>
                    <th class="px-5 py-4">Kode Transaksi</th>
                    <th class="px-5 py-4">Pasien</th>
                    <th class="px-5 py-4">Jumlah Item</th>
                    <th class="px-5 py-4">Total Tagihan</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4">Tanggal Transaksi</th>
                    <th class="px-5 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="flex flex-col gap-3 border-t border-slate-200 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
        <div id="penjualan-obat-custom-info" class="text-sm text-slate-600"></div>
        <ul id="penjualan-obat-custom-paginate" class="inline-flex -space-x-px text-sm"></ul>
    </div>
</div>

<!-- Modal -->
<div id="modalJualObat" tabindex="-1" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 overflow-y-auto">
    <div class="relative w-full max-w-6xl">
        <div class="overflow-hidden rounded-2xl bg-white shadow-2xl">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <h3 id="modal-title-penjualan-obat" class="text-xl font-bold text-slate-800">Tambah Order Obat</h3>
                    <p class="text-sm text-slate-500">Pilih pasien, tambahkan obat, lalu simpan transaksi.</p>
                </div>

                <button type="button" id="closeModalBtn"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">
                    ✕
                </button>
            </div>

            <form id="form-penjualan-obat" class="p-6">
                @csrf
                <input type="hidden" id="penjualan_obat_id" name="penjualan_obat_id">
                <input type="hidden" id="pasien_id" name="pasien_id">
                <input type="hidden" id="resep_id" name="resep_id">
                <input type="hidden" id="tanggal_kunjungan" name="tanggal_kunjungan">

                <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                    <!-- LEFT -->
                    <div class="xl:col-span-2 space-y-6">
                        <!-- Cari Pasien -->
                        <div class="rounded-2xl border border-slate-200 p-5">
                            <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-600">Data Pasien
                            </h4>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700">Cari Pasien</label>
                                <input type="text" id="search_pasien" name="search_pasien"
                                    placeholder="Ketik nama pasien..."
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                                <div id="search_results"
                                    class="mt-2 hidden max-h-48 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg">
                                </div>
                            </div>

                            <div id="pasien_data" class="mt-4 hidden rounded-2xl bg-slate-50 p-4">
                                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-slate-500">Nama Pasien</p>
                                        <p id="nama_pasien" class="mt-1 font-semibold text-slate-800"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-slate-500">Jenis Kelamin</p>
                                        <p id="jk_pasien" class="mt-1 font-semibold text-slate-800"></p>
                                    </div>
                                    <div>
                                        <p class="text-xs uppercase tracking-wide text-slate-500">No EMR</p>
                                        <p id="no_emr_pasien" class="mt-1 font-semibold text-slate-800"></p>
                                    </div>
                                    <div class="md:col-span-2">
                                        <p class="text-xs uppercase tracking-wide text-slate-500">Alamat</p>
                                        <p id="alamat_pasien" class="mt-1 font-semibold text-slate-800"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cari Obat -->
                        <div class="rounded-2xl border border-slate-200 p-5">
                            <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-600">Daftar Obat
                            </h4>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700">Cari Obat</label>
                                <input type="text" id="search_obat" placeholder="Ketik nama obat..."
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-700 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100">
                                <div id="obat_results"
                                    class="mt-2 hidden max-h-48 overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-lg">
                                </div>
                            </div>

                            <div class="mt-5 overflow-x-auto">
                                <table class="min-w-full overflow-hidden rounded-2xl border border-slate-200">
                                    <thead class="bg-slate-100 text-xs uppercase tracking-wide text-slate-600">
                                        <tr>
                                            <th class="px-4 py-3 text-left">Obat</th>
                                            <th class="px-4 py-3 text-left">Harga</th>
                                            <th class="px-4 py-3 text-left">Stok</th>
                                            <th class="px-4 py-3 text-left">Qty</th>
                                            <th class="px-4 py-3 text-left">Subtotal</th>
                                            <th class="px-4 py-3 text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="selected_obat_list" class="bg-white"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT -->
                    <div class="space-y-6">
                        <div class="rounded-2xl border border-slate-200 p-5">
                            <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-600">Ringkasan
                                Transaksi</h4>

                            <div class="space-y-4">
                                <div class="rounded-xl bg-slate-50 p-4">
                                    <p class="text-xs uppercase tracking-wide text-slate-500">Jumlah Item</p>
                                    <p id="summary-total-item" class="mt-1 text-2xl font-bold text-slate-800">0</p>
                                </div>

                                <div class="rounded-xl bg-sky-50 p-4">
                                    <p class="text-xs uppercase tracking-wide text-sky-700">Grand Total</p>
                                    <p id="summary-grand-total" class="mt-1 text-2xl font-bold text-sky-700">Rp 0</p>
                                </div>

                                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                                    <p class="text-sm text-amber-800">
                                        Status transaksi otomatis dibuat sebagai
                                        <span class="font-semibold">Belum Bayar</span>.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-200 p-5">
                            <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-600">Aksi</h4>

                            <div class="flex flex-col gap-3">
                                <button type="button" id="btn-close-modal-penjualan-obat"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                                    Batal
                                </button>

                                <button type="submit" id="btn-submit-penjualan-obat"
                                    class="w-full rounded-xl bg-sky-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-300">
                                    Simpan Order
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@vite(['resources/js/farmasi/obat/order-obat.js'])
