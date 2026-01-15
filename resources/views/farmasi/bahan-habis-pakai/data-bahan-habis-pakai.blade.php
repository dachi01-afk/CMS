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
                    placeholder="Cari kode, nama Barang">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
            </div>

            {{-- BUTTONS --}}
            <div class="flex items-center gap-2 justify-end">
                {{-- Tambah Data Obat --}}
                <button type="button" id="btn-open-modal-create-bhp"
                    class="inline-flex items-center justify-center px-3 md:px-4 py-2 rounded-xl text-[11px] md:text-xs 
                           font-semibold bg-emerald-600 text-white shadow-sm hover:bg-emerald-700">
                    <i class="fa-solid fa-plus mr-1.5 text-[10px]"></i>
                    Tambah Data Bahan Habis Pakai
                </button>

                <a href="{{ route('export.excel.data.bahan.habis.pakai') }}" id="btn-export-bhp-excel"
                    class="inline-flex items-center justify-center px-3 md:px-4 py-2 rounded-xl border text-[11px] md:text-xs
               font-medium bg-white text-gray-700 border-gray-200 hover:bg-gray-50
               dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
                    <i class="fa-solid fa-file-csv mr-1.5 text-[10px]"></i>
                    Export Excel
                </a>

                <button type="button" id="btn-print-bhp"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-xl border text-[11px] md:text-xs
           font-medium bg-white text-gray-700 border-gray-200 hover:bg-gray-50
           dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
                    <i class="fa-solid fa-print mr-1.5 text-[10px]"></i>
                    Print PDF
                </button>

                <button type="button" id="btn-import-bhp"
                    class="inline-flex items-center justify-center px-4 py-2 rounded-xl border text-[11px] md:text-xs
           font-medium bg-white text-gray-700 border-gray-200 hover:bg-gray-50">
                    <i class="fa-solid fa-file-import mr-1.5 text-[10px]"></i>
                    Import Excel
                </button>

                <form id="form-import-bhp" action="{{ route('import.excel.data.bahan.habis.pakai') }}" method="POST"
                    enctype="multipart/form-data" class="hidden">
                    @csrf
                    <input type="file" id="input-file-import-bhp" name="file" accept=".xlsx,.xls">
                </form>

            </div>
        </div>
    </div>

    {{-- TABLE WRAPPER --}}
    <div class="rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <table id="tabelBahanHabisPakai" class="min-w-full text-xs md:text-sm">
            <thead
                class="bg-gray-50 dark:bg-gray-800/80 text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-300">
                <tr>
                    <th class="px-3 py-2.5 text-left">No</th>
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

<!-- Modal Create Bhp -->
<div id="modalCreateBhp" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full px-4 bg-black/40">
    <div class="relative w-full max-w-4xl">
        <!-- Card -->
        <div
            class="relative flex flex-col bg-white rounded-2xl shadow-2xl dark:bg-gray-900 border border-gray-100 dark:border-gray-700 max-h-[90vh]">

            <!-- Header (sticky) -->
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-900 z-10 rounded-t-2xl">
                <div>
                    <h3 class="text-base md:text-lg font-semibold text-gray-900 dark:text-white">
                        Tambah Data Bahan Habis Pakai
                    </h3>
                    <p class="text-[11px] md:text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Lengkapi informasi bahan habis pakai dengan benar untuk mendukung stok dan penjualan di klinik.
                    </p>
                </div>
                <button type="button" id="btn-close-modal-create-bhp"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-gray-200 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Body (scrollable) -->
            <form id="formModalCreateBhp" class="px-6 py-5 space-y-7 overflow-y-auto"
                data-url="{{ route('create.data.bahan.habis.pakai') }}" method="POST">
                @csrf

                <!-- Section: Identitas Obat -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                            <i class="fa-solid fa-capsules text-xs"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                                Identitas Barang Habis Pakai
                            </h4>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                Data dasar bahan habis pakai yang tampil di rekam medis dan transaksi.
                            </p>
                        </div>
                    </div>

                    <!-- Barcode -->
                    <div class="grid grid-cols-1">
                        <div>
                            <label for="kode_create" class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Kode Barang <span class="text-gray-400">(opsional)</span>
                            </label>

                            <div class="mt-1 flex gap-2">
                                <input type="text" name="kode" id="kode_create"
                                    class="block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="Contoh: BHP-0001 (kosongkan jika auto)" autocomplete="off">
                            </div>

                            <div id="kode_create-error" class="text-red-600 text-[11px] mt-1"></div>

                        </div>
                    </div>

                    <!-- Nama / Brand / Kategori -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nama_barang" class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Nama Barang <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-2 text-gray-400">
                                    <i class="fa-solid fa-magnifying-glass text-[11px]"></i>
                                </span>
                                <input type="text" name="nama_barang" id="nama_barang"
                                    class="block w-full pl-7 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="Cari / ketik nama barang" required autocomplete="off">
                            </div>
                            <div id="nama_barang-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="brand_farmasi_id_create"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Nama Brand Farmasi
                            </label>

                            <div class="relative mt-1">
                                <select name="brand_farmasi_id_create" id="brand_farmasi_id_create"
                                    data-url-index="{{ route('get.data.brand.farmasi') }}"
                                    data-url-store="{{ route('create.data.brand.farmasi') }}"
                                    data-url-delete="{{ route('delete.data.brand.farmasi') }}"
                                    class="block w-full pr-9 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="">Ketik untuk mencari / tambah baru</option>
                                </select>

                                <!-- Tombol X (clear & delete brand) -->
                                <button type="button" id="btn-clear-brand-create"
                                    class="hidden absolute inset-y-0 right-2 my-auto w-5 h-5 rounded-full flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/40">
                                    <i class="fa-solid fa-xmark text-[10px]"></i>
                                </button>
                            </div>

                            <div id="brand_farmasi_id_create-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>
                    </div>

                    <!-- Jenis / Satuan / Dosis -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- JENIS -->
                        <div>
                            <label for="jenis_id_create"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Jenis
                            </label>

                            <!-- wrapper khusus select + tombol X -->
                            <div class="relative mt-1">
                                <select name="jenis_id_create" id="jenis_id_create"
                                    data-url-index="{{ route('get.data.jenis.obat') }}"
                                    data-url-store="{{ route('create.data.jenis.obat') }}"
                                    data-url-delete="{{ route('delete.data.jenis.obat') }}"
                                    class="block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 
                       focus:border-blue-500 focus:ring-1 focus:ring-blue-500 
                       dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                </select>

                                <!-- Tombol X custom (di luar TomSelect, sejajar di kanan tengah) -->
                                <button id="btn-clear-jenis-create" type="button"
                                    class="hidden absolute inset-y-0 right-5 z-20 flex items-center
                       text-gray-400 hover:text-red-500 text-sm font-bold">
                                    ×
                                </button>
                            </div>

                            <div id="jenis_id_create-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <!-- SATUAN -->
                        <div>
                            <label for="satuan_id_create"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Satuan <span class="text-red-500">*</span>
                            </label>

                            <!-- wrapper select + tombol X -->
                            <div class="relative mt-1">
                                <select name="satuan_id_create" id="satuan_id_create"
                                    data-url-index="{{ route('get.data.satuan.obat') }}"
                                    data-url-store="{{ route('create.data.satuan.obat') }}"
                                    data-url-delete="{{ route('delete.data.satuan.obat') }}"
                                    class="block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 
                   focus:border-blue-500 focus:ring-1 focus:ring-blue-500 
                   dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    required>
                                </select>

                                <!-- Tombol X custom -->
                                <button id="btn-clear-satuan-create" type="button"
                                    class="hidden absolute inset-y-0 right-5 z-20 flex items-center
                   text-gray-400 hover:text-red-500 text-sm font-bold">
                                    ×
                                </button>
                            </div>

                            <div id="satuan-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>


                        <!-- DOSIS -->
                        <div>
                            <label for="dosis" class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Dosis (mg/ml) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.01" name="dosis" id="dosis"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 
                   focus:border-blue-500 focus:ring-1 focus:ring-blue-500 
                   dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="Contoh: 500" required>
                            <div id="dosis-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-dashed border-gray-200 dark:border-gray-700"></div>

                <!-- Section: Stok & Kedaluwarsa -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div
                            class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <i class="fa-solid fa-boxes-stacked text-xs"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                                Stok & Kedaluwarsa
                            </h4>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                Pantau stok awal dan tanggal kedaluwarsa untuk mencegah BHP kadaluarsa.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="stok_barang"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Stok BHP (global)
                            </label>
                            <input type="text" name="stok_barang" id="stok_barang" value="0" readonly
                                tabindex="-1" inputmode="numeric"
                                class="mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2
           cursor-not-allowed pointer-events-none
           dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                            <p class="text-[10px] text-gray-400 mt-1">
                                Stok ini otomatis bertambah dari per depot.
                            </p>
                            <div id="stok_barang-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="tanggal_kadaluarsa_bhp_create"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Expired Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_kadaluarsa_bhp_create"
                                id="tanggal_kadaluarsa_bhp_create"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                required>
                            <div id="tanggal_kadaluarsa_bhp_create-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="no_batch_create"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Nomor Batch <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="no_batch_create" id="no_batch_create"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="Nomor batch produksi" required autocomplete="off">
                            <div id="no_batch_create-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-dashed border-gray-200 dark:border-gray-700"></div>

                <!-- Section: Harga -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-8 h-8 rounded-full bg-amber-50 flex items-center justify-center text-amber-600">
                                <i class="fa-solid fa-money-bill-wave text-xs"></i>
                            </div>
                            <div>
                                <h4
                                    class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                                    Pengaturan Harga
                                </h4>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                    Atur harga beli dan jual untuk umum dan OTC.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="harga_beli_satuan_bhp_create"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Harga Beli Satuan (Rp)
                            </label>
                            <div class="relative mt-1">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-[11px] text-gray-400">Rp</span>
                                <input type="text" name="harga_beli_satuan_bhp_create"
                                    id="harga_beli_satuan_bhp_create"
                                    class="block w-full pl-8 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="0" autocomplete="off">
                            </div>
                            <div id="harga_beli_satuan_bhp_create-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="harga_jual_umum_bhp_create"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Harga Jual Umum (Rp)
                            </label>
                            <div class="relative mt-1">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-[11px] text-gray-400">Rp</span>
                                <input type="text" name="harga_jual_umum_bhp_create"
                                    id="harga_jual_umum_bhp_create"
                                    class="block w-full pl-8 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="0" autocomplete="off">
                            </div>
                            <div id="harga_jual_umum_bhp_create-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="harga_otc_bhp_create"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Harga OTC (Rp)
                            </label>
                            <div class="relative mt-1">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-[11px] text-gray-400">Rp</span>
                                <input type="text" name="harga_otc_bhp_create" id="harga_otc_bhp_create"
                                    class="block w-full pl-8 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="0" autocomplete="off">
                            </div>
                            <div id="harga_otc_bhp_create-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-dashed border-gray-200 dark:border-gray-700"></div>

                <!-- Section: Depot -->
                <div class="space-y-4 pb-1">
                    <div class="text-center">
                        <p class="text-[11px] font-semibold text-blue-600 uppercase tracking-[0.16em]">
                            Set Ketersediaan Obat Pada Depot
                        </p>
                        <p class="text-[11px] text-gray-500 mt-1 dark:text-gray-400">
                            Atur distribusi stok ke Apotek / Gudang yang berbeda.
                        </p>
                    </div>

                    <!-- WRAPPER DEPOT -->
                    <div id="depot-container-create-bhp" class="space-y-3">

                        <!-- ROW DEPOT (TEMPLATE PERTAMA) -->
                        <div
                            class="depot-row-template-create-bhp grid grid-cols-12 gap-4 items-center bg-gray-50/60 dark:bg-gray-800/60 
        rounded-xl px-4 py-4 border border-dashed border-gray-200 dark:border-gray-700">

                            <!-- NAMA DEPOT -->
                            <div class="col-span-12 md:col-span-4">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                    Nama Depot <span class="text-red-500">*</span>
                                </label>

                                <div class="relative mt-1">
                                    <select name="depot_id[]"
                                        class="select-nama-depot-create block w-full text-sm bg-transparent dark:bg-gray-900
                    border border-gray-200 rounded-lg pl-3 pr-7 py-2 focus:border-blue-500 
                    focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:text-white"
                                        data-url-index="{{ route('get.data.depot') }}"
                                        data-url-store="{{ route('create.data.depot') }}"
                                        data-url-delete="{{ route('delete.data.depot') }}">
                                        <option value="">Pilih / ketik nama depot</option>
                                    </select>

                                    <!-- tombol X -->
                                    <button type="button"
                                        class="btn-clear-depot-create hidden absolute right-2 top-1/2 -translate-y-1/2 
                    text-gray-400 hover:text-red-500 text-base font-bold">
                                        ×
                                    </button>
                                </div>

                                <div class="depot_id-error text-red-600 text-[11px] mt-1"></div>
                            </div>

                            <!-- TIPE DEPOT -->
                            <div class="col-span-12 md:col-span-3">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                    Tipe Depot <span class="text-red-500">*</span>
                                </label>

                                <div class="relative mt-1">
                                    <select name="tipe_depot[]"
                                        class="select-tipe-depot-create block w-full text-sm bg-transparent dark:bg-gray-900
                    border border-gray-200 rounded-lg pl-3 pr-7 py-2 focus:border-blue-500 
                    focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:text-white"
                                        data-url-index="{{ route('get.data.tipe.depot') }}"
                                        data-url-store="{{ route('create.data.tipe.depot') }}"
                                        data-url-delete="{{ route('delete.data.tipe.depot') }}">
                                        <option value="">Pilih / ketik tipe depot</option>
                                    </select>

                                    <!-- tombol X -->
                                    <button type="button"
                                        class="btn-clear-tipe-depot-create hidden absolute right-2 top-1/2 -translate-y-1/2 
                    flex items-center justify-center text-gray-400 hover:text-red-500">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                </div>

                                <div class="tipe_depot-error text-red-600 text-[11px]"></div>
                            </div>

                            <!-- STOK DEPOT -->
                            <div class="col-span-12 md:col-span-3">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                    Stok Depot
                                </label>
                                <input type="number" name="stok_depot[]"
                                    class="input-stok-depot-create mt-1 block w-full text-sm bg-transparent dark:bg-gray-900
                border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 
                focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:text-white"
                                    value="0">
                            </div>

                            <!-- BUTTON HAPUS -->
                            <div class="col-span-12 md:col-span-2 flex md:justify-center justify-end">
                                <button type="button"
                                    class="btn-remove-depot-create w-full md:w-9 h-9 flex items-center justify-center 
                rounded-lg bg-red-50 text-red-600 text-xs hover:bg-red-100 border border-red-100">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>


                    <div class="flex justify-start">
                        <button type="button" id="btn-add-depot-create-bhp"
                            class="inline-flex items-center text-[11px] font-medium text-blue-600 hover:text-blue-700 hover:underline">
                            <i class="fa-solid fa-plus-circle mr-1 text-xs"></i>
                            Tambah Depot
                        </button>
                    </div>
                </div>

                <!-- Footer Buttons (sticky dengan background gradient halus) -->
                <div
                    class="sticky bottom-0 -mx-6 pt-3 pb-4 px-6 bg-gradient-to-t from-white via-white/95 to-white/40 dark:from-gray-900 dark:via-gray-900/95 dark:to-gray-900/40 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                    <button type="button" id="btn-cancel-modal-create-bhp"
                        class="px-4 md:px-5 py-2.5 text-xs font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 border border-gray-200 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700 dark:hover:bg-gray-700">
                        Batal
                    </button>
                    <button type="submit" id="saveObatButton"
                        class="px-4 md:px-5 py-2.5 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm shadow-blue-300/60">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Update Bhp --}}
<div id="modalUpdateBhp" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full bg-black/40 px-4">
    <div class="relative w-full max-w-4xl">
        <!-- Card -->
        <div
            class="relative flex flex-col bg-white rounded-2xl shadow-2xl dark:bg-gray-900 border border-gray-100 dark:border-gray-700 max-h-[90vh]">

            <!-- Header (sticky) -->
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-900 z-10 rounded-t-2xl">
                <div>
                    <h3 class="text-base md:text-lg font-semibold text-gray-900 dark:text-white">
                        Update Data Bahan Habis Pakai
                    </h3>
                    <p class="text-[11px] md:text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Perbarui informasi bahan habis pakai dengan benar untuk mendukung stok dan penjualan di klinik.
                    </p>
                </div>
                <button type="button" id="btn-close-modal-update-bhp"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-gray-200 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Body (scrollable) -->
            <form id="formModalUpdateBhp" class="px-6 py-5 space-y-7 overflow-y-auto" data-url="" method="POST">
                @csrf
                <input type="hidden" name="id" id="id_update">

                <!-- Section: Identitas Obat -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                            <i class="fa-solid fa-capsules text-xs"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                                Identitas Bahan Habis Pakai
                            </h4>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                Data dasar bahan habis pakai yang tampil di rekam medis dan transaksi.
                            </p>
                        </div>
                    </div>

                    <!-- Nama / Brand / Kategori -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nama_barang_update"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Nama Bahan Habis Pakai <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-2 text-gray-400">
                                    <i class="fa-solid fa-magnifying-glass text-[11px]"></i>
                                </span>
                                <input type="text" name="nama_barang_update" id="nama_barang_update"
                                    class="block w-full pl-7 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="Cari / ketik nama obat" required autocomplete="off">
                            </div>
                            <div id="nama_barang_update-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="brand_farmasi_id_update"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Nama Brand Farmasi
                            </label>

                            <div class="relative mt-1">
                                <select name="brand_farmasi_id_update" id="brand_farmasi_id_update"
                                    data-url-index="{{ route('get.data.brand.farmasi') }}"
                                    data-url-store="{{ route('create.data.brand.farmasi') }}"
                                    data-url-delete="{{ route('delete.data.brand.farmasi') }}"
                                    class="block w-full pr-9 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="">Ketik untuk mencari / tambah baru</option>
                                </select>

                                <!-- Tombol X (clear & delete brand) -->
                                <button type="button" id="btn-clear-brand-update"
                                    class="hidden absolute inset-y-0 right-2 my-auto w-5 h-5 rounded-full flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/40">
                                    <i class="fa-solid fa-xmark text-[10px]"></i>
                                </button>
                            </div>

                            <div id="brand_farmasi_id_update-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>
                    </div>

                    <!-- Jenis / Satuan / Dosis -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- JENIS -->
                        <div>
                            <label for="jenis_id_update"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Jenis
                            </label>

                            <div class="relative mt-1">
                                <select name="jenis" id="jenis_id_update"
                                    data-url-index="{{ route('get.data.jenis.obat') }}"
                                    data-url-store="{{ route('create.data.jenis.obat') }}"
                                    data-url-delete="{{ route('delete.data.jenis.obat') }}"
                                    class="block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 
                                        focus:border-blue-500 focus:ring-1 focus:ring-blue-500 
                                        dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                </select>

                                <button id="btn-clear-jenis-update" type="button"
                                    class="hidden absolute inset-y-0 right-5 z-20 flex items-center
                                        text-gray-400 hover:text-red-500 text-sm font-bold">
                                    ×
                                </button>
                            </div>

                            <div id="jenis_id_update-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <!-- SATUAN -->
                        <div>
                            <label for="satuan_id_update"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Satuan <span class="text-red-500">*</span>
                            </label>

                            <div class="relative mt-1">
                                <select id="satuan_id_update" data-url-index="{{ route('get.data.satuan.obat') }}"
                                    data-url-store="{{ route('create.data.satuan.obat') }}"
                                    data-url-delete="{{ route('delete.data.satuan.obat') }}"
                                    class="block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 
                                        focus:border-blue-500 focus:ring-1 focus:ring-blue-500 
                                        dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    required>
                                </select>

                                <button id="btn-clear-satuan-update" type="button"
                                    class="hidden absolute inset-y-0 right-5 z-20 flex items-center
                                        text-gray-400 hover:text-red-500 text-sm font-bold">
                                    ×
                                </button>
                            </div>

                            <div id="satuan_id_update-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <!-- DOSIS -->
                        <div>
                            <label for="dosis_update"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Dosis (mg/ml) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.01" name="dosis_update" id="dosis_update"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 
                                    focus:border-blue-500 focus:ring-1 focus:ring-blue-500 
                                    dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="Contoh: 500" required>
                            <div id="dosis_update-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-dashed border-gray-200 dark:border-gray-700"></div>

                <!-- Section: Stok & Kedaluwarsa -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div
                            class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <i class="fa-solid fa-boxes-stacked text-xs"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                                Stok & Kedaluwarsa
                            </h4>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                Pantau stok dan tanggal kedaluwarsa untuk mencegah BHP kadaluarsa.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="stok_barang_update"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Stok BHP (global)
                            </label>
                            <input type="text" id="stok_barang_update" value="0" readonly tabindex="-1"
                                inputmode="numeric"
                                class="mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2
           cursor-not-allowed pointer-events-none
           dark:bg-gray-800 dark:border-gray-700 dark:text-white">

                            <p class="text-[10px] text-gray-400 mt-1">
                                Stok ini otomatis bertambah dari per depot.
                            </p>
                            <div id="stok_barang_update-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="tanggal_kadaluarsa_bhp_update"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Expired Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_kadaluarsa_bhp_update"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                required>
                            <div id="tanggal_kadaluarsa_bhp_update-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="no_batch_update"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Nomor Batch <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nomor_batch" id="no_batch_update"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="Nomor batch produksi" required autocomplete="off">
                            <div id="no_batch_update-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-dashed border-gray-200 dark:border-gray-700"></div>

                <!-- Section: Harga -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-8 h-8 rounded-full bg-amber-50 flex items-center justify-center text-amber-600">
                                <i class="fa-solid fa-money-bill-wave text-xs"></i>
                            </div>
                            <div>
                                <h4
                                    class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                                    Pengaturan Harga
                                </h4>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                    Atur harga beli dan jual untuk umum dan OTC.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="harga_beli_satuan_bhp_update"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Harga Beli Satuan (Rp)
                            </label>
                            <div class="relative mt-1">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-[11px] text-gray-400">Rp</span>
                                <input type="text" name="harga_beli_satuan" id="harga_beli_satuan_bhp_update"
                                    class="block w-full pl-8 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="0" autocomplete="off">
                            </div>
                            <div id="harga_beli_satuan_bhp_update-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="harga_jual_umum_bhp_update"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Harga Jual Umum (Rp)
                            </label>
                            <div class="relative mt-1">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-[11px] text-gray-400">Rp</span>
                                <input type="text" name="harga_jual_umum" id="harga_jual_umum_bhp_update"
                                    class="block w-full pl-8 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="0" autocomplete="off">
                            </div>
                            <div id="harga_jual_umum_bhp_update-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="harga_otc_bhp_update"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Harga OTC (Rp)
                            </label>
                            <div class="relative mt-1">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-[11px] text-gray-400">Rp</span>
                                <input type="text" name="harga_otc" id="harga_otc_bhp_update"
                                    class="block w-full pl-8 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="0" autocomplete="off">
                            </div>
                            <div id="harga_otc_bhp_update-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>
                    </div>
                </div>

                <!-- Divider -->
                <div class="border-t border-dashed border-gray-200 dark:border-gray-700"></div>

                <!-- Section: Depot -->
                <div class="space-y-4 pb-1">
                    <div class="text-center">
                        <p class="text-[11px] font-semibold text-blue-600 uppercase tracking-[0.16em]">
                            Set Ketersediaan Obat Pada Depot
                        </p>
                        <p class="text-[11px] text-gray-500 mt-1 dark:text-gray-400">
                            Atur distribusi stok ke Apotek / Gudang yang berbeda.
                        </p>
                    </div>

                    <!-- WRAPPER DEPOT UPDATE -->
                    <div id="depot-container-update-bhp" class="space-y-3">
                        <!-- ROW DEPOT TEMPLATE UPDATE -->
                        <div
                            class="depot-row-template-update-bhp grid grid-cols-12 gap-4 items-center bg-gray-50/60 dark:bg-gray-800/60 
                                rounded-xl px-4 py-4 border border-dashed border-gray-200 dark:border-gray-700">

                            <!-- NAMA DEPOT -->
                            <div class="col-span-12 md:col-span-4">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                    Nama Depot <span class="text-red-500">*</span>
                                </label>

                                <div class="relative mt-1">
                                    <select name="depot_id[]"
                                        class="select-nama-depot-update block w-full text-sm bg-transparent dark:bg-gray-900
                                            border border-gray-200 rounded-lg pl-3 pr-7 py-2 focus:border-blue-500 
                                            focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:text-white"
                                        data-url-index="{{ route('get.data.depot') }}"
                                        data-url-store="{{ route('create.data.depot') }}"
                                        data-url-delete="{{ route('delete.data.depot') }}">
                                        <option value="">Pilih / ketik nama depot</option>
                                    </select>

                                    <!-- tombol X -->
                                    <button type="button"
                                        class="btn-clear-depot-update hidden absolute right-2 top-1/2 -translate-y-1/2 
                                            text-gray-400 hover:text-red-500 text-base font-bold">
                                        ×
                                    </button>
                                </div>

                                <div class="depot_id-error text-red-600 text-[11px] mt-1"></div>
                            </div>

                            <!-- TIPE DEPOT -->
                            <div class="col-span-12 md:col-span-3">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                    Tipe Depot <span class="text-red-500">*</span>
                                </label>

                                <div class="relative mt-1">
                                    <select name="tipe_depot[]"
                                        class="select-tipe-depot-update block w-full text-sm bg-transparent dark:bg-gray-900
                                            border border-gray-200 rounded-lg pl-3 pr-7 py-2 focus:border-blue-500 
                                            focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:text-white"
                                        data-url-index="{{ route('get.data.tipe.depot') }}"
                                        data-url-store="{{ route('create.data.tipe.depot') }}"
                                        data-url-delete="{{ route('delete.data.tipe.depot') }}">
                                        <option value="">Pilih / ketik tipe depot</option>
                                    </select>

                                    <!-- tombol X -->
                                    <button type="button"
                                        class="btn-clear-tipe-depot-update hidden absolute right-2 top-1/2 -translate-y-1/2 
                                            flex items-center justify-center text-gray-400 hover:text-red-500">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                </div>

                                <div class="tipe_depot_update-error text-red-600 text-[11px]"></div>
                            </div>

                            <!-- STOK DEPOT -->
                            <div class="col-span-12 md:col-span-3">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                    Stok Depot
                                </label>
                                <input type="number" name="stok_depot[]"
                                    class="input-stok-depot-update mt-1 block w-full text-sm bg-transparent dark:bg-gray-900
                                        border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 
                                        focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:text-white"
                                    value="0">
                            </div>

                            <!-- BUTTON HAPUS -->
                            <div class="col-span-12 md:col-span-2 flex md:justify-center justify-end">
                                <button type="button"
                                    class="btn-remove-depot-update w-full md:w-9 h-9 flex items-center justify-center 
                                        rounded-lg bg-red-50 text-red-600 text-xs hover:bg-red-100 border border-red-100">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-start">
                        <button type="button" id="btn-add-depot-update"
                            class="inline-flex items-center text-[11px] font-medium text-blue-600 hover:text-blue-700 hover:underline">
                            <i class="fa-solid fa-plus-circle mr-1 text-xs"></i>
                            Tambah Depot
                        </button>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div
                    class="sticky bottom-0 -mx-6 pt-3 pb-4 px-6 bg-gradient-to-t from-white via-white/95 to-white/40 dark:from-gray-900 dark:via-gray-900/95 dark:to-gray-900/40 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                    <button type="button" id="btn-cancel-modal-update-bhp"
                        class="px-4 md:px-5 py-2.5 text-xs font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 border border-gray-200 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700 dark:hover:bg-gray-700">
                        Batal
                    </button>
                    <button type="submit" id="updateObatButton"
                        class="px-4 md:px-5 py-2.5 text-xs font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 shadow-sm shadow-emerald-300/60">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@vite(['resources/js/farmasi/bahan-habis-pakai/data-bahan-habis-pakai.js'])
