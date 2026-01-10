{{-- CARD DATA STOK OBAT --}}
<div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
        <div>
            <h2 class="text-base md:text-lg font-semibold text-gray-900 dark:text-white">
                Data Stok Obat
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
                    placeholder="Cari kode, nama obat atau kategori">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
            </div>

            {{-- BUTTONS --}}
            <div class="flex items-center gap-2 justify-end">
                {{-- Tambah Data Obat --}}
                <button type="button" id="btn-open-modal-create-obat"
                    class="inline-flex items-center justify-center px-3 md:px-4 py-2 rounded-xl text-[11px] md:text-xs 
               font-semibold bg-emerald-600 text-white shadow-sm hover:bg-emerald-700">
                    <i class="fa-solid fa-plus mr-1.5 text-[10px]"></i>
                    Tambah Data Obat
                </button>

                {{-- Export CSV --}}
                <a href="{{ route('export.data.obat') }}"
                    class="inline-flex items-center justify-center px-3 md:px-4 py-2 rounded-xl border text-[11px] md:text-xs
               font-medium bg-white text-gray-700 border-gray-200 hover:bg-gray-50
               dark:bg-gray-900 dark:text-gray-100 dark:border-gray-700">
                    <i class="fa-solid fa-file-csv mr-1.5 text-[10px]"></i>
                    Export CSV
                </a>

                <!-- Import Excel -->
                <form id="import-form" action="{{ route('import.data.obat') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="file" id="file-input" accept=".xlsx,.xls,.csv" style="display: none;"
                        required>

                    <button type="button" id="btn-import"
                        class="inline-flex items-center justify-center px-3 md:px-4 py-2 rounded-xl text-[11px] md:text-xs 
               font-medium bg-white text-emerald-700 border border-emerald-500 hover:bg-emerald-50
               dark:bg-gray-900 dark:border-emerald-500 dark:text-emerald-300">
                        <i class="fa-solid fa-upload mr-1.5 text-[10px]"></i>
                        Import
                    </button>
                </form>
            </div>

        </div>
    </div>

    {{-- TABLE WRAPPER --}}
    <div class="rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <!-- Toolbar -->
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">
            <!-- Page length -->
            <div class="flex items-center gap-2 text-sm">
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>
                <select id="obat-pageLength"
                    class="border border-slate-300 dark:border-slate-600 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500
                           bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-1 w-28">
                    <option value="10">10 baris</option>
                    <option value="25">25 baris</option>
                    <option value="50">50 baris</option>
                    <option value="100">100 baris</option>
                </select>
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">per halaman</span>
            </div>
        </div>
        <table id="dataObatTable" class="min-w-full text-xs md:text-sm">
            <thead
                class="bg-gray-50 dark:bg-gray-800/80 text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-300">
                <tr>
                    <th class="px-3 py-2.5 text-left">No</th>
                    <th class="px-3 py-2.5 text-left">Kode</th>
                    <th class="px-3 py-2.5 text-left">Nama Obat</th>
                    <th class="px-3 py-2.5 text-left">Farmasi</th>
                    <th class="px-3 py-2.5 text-left">Jenis</th>
                    <th class="px-3 py-2.5 text-left">Kategori</th>
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

    <!-- Footer -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">
        <div id="obat-customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300">
        </div>

        <ul id="obat-customPagination"
            class="inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
        </ul>
    </div>
</div>


<!-- Modal Create Obat -->
<div id="modalCreateObat" aria-hidden="true"
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
                        Tambah Data Obat Baru
                    </h3>
                    <p class="text-[11px] md:text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Lengkapi informasi obat dengan benar untuk mendukung stok dan penjualan di klinik.
                    </p>
                </div>
                <button type="button" id="btn-close-modal-create-obat"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-gray-200 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Body (scrollable) -->
            <form id="formModalCreate" class="px-6 py-5 space-y-7 overflow-y-auto" data-url="{{ route('obat.create') }}"
                method="POST">
                @csrf

                <!-- Section: Identitas Obat -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                            <i class="fa-solid fa-capsules text-xs"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                                Identitas Obat
                            </h4>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                Data dasar obat yang tampil di rekam medis dan transaksi.
                            </p>
                        </div>
                    </div>

                    <!-- Barcode -->
                    <div class="grid grid-cols-1">
                        <div>
                            <label for="barcode" class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Barcode
                            </label>
                            <div class="mt-1 flex gap-2">
                                <input type="text" name="barcode" id="barcode"
                                    class="block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="Scan / masukkan barcode" autocomplete="off">
                                <button type="button"
                                    class="hidden md:inline-flex items-center px-3 py-2 text-[11px] font-medium rounded-lg border border-dashed border-gray-300 text-gray-500 hover:border-blue-400 hover:text-blue-600 dark:border-gray-600 dark:text-gray-300 dark:hover:border-blue-500">
                                    <i class="fa-solid fa-barcode text-xs mr-1"></i> Scan
                                </button>
                            </div>
                            <div id="barcode-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>
                    </div>

                    <!-- Nama / Brand / Kategori -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="nama_obat" class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Nama Obat <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-2 text-gray-400">
                                    <i class="fa-solid fa-magnifying-glass text-[11px]"></i>
                                </span>
                                <input type="text" name="nama_obat" id="nama_obat"
                                    class="block w-full pl-7 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="Cari / ketik nama obat" required autocomplete="off">
                            </div>
                            <div id="nama_obat-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="brand_farmasi_id"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Nama Brand Farmasi
                            </label>

                            <div class="relative mt-1">
                                <select name="brand_farmasi_id" id="brand_farmasi_id"
                                    data-url-index="{{ route('get.data.brand.farmasi') }}"
                                    data-url-store="{{ route('create.data.brand.farmasi') }}"
                                    data-url-delete="{{ route('delete.data.brand.farmasi') }}"
                                    class="block w-full pr-9 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="">Ketik untuk mencari / tambah baru</option>
                                </select>

                                <!-- Tombol X (clear & delete brand) -->
                                <button type="button" id="btn-clear-brand"
                                    class="hidden absolute inset-y-0 right-2 my-auto w-5 h-5 rounded-full flex items-center justify-center text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/40">
                                    <i class="fa-solid fa-xmark text-[10px]"></i>
                                </button>
                            </div>

                            <div id="brand_farmasi_id-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="kategori_obat"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Kategori Obat <span class="text-red-500">*</span>
                            </label>

                            <select name="kategori_obat" id="kategori_obat"
                                data-url-index="{{ route('obat.get.data.kategori.obat') }}"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 
               focus:border-blue-500 focus:ring-1 focus:ring-blue-500 
               dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                required>
                            </select>

                            <!-- Tombol X di pojok -->
                            <button id="btn-clear-kategori" type="button"
                                class="hidden absolute right-3 top-[38px] text-gray-400 hover:text-red-500">
                                ✕
                            </button>

                            <div id="kategori_obat-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                    </div>

                    <!-- Jenis / Satuan / Dosis -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- JENIS -->
                        <div>
                            <label for="jenis_id" class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Jenis
                            </label>

                            <!-- wrapper khusus select + tombol X -->
                            <div class="relative mt-1">
                                <select name="jenis_id" id="jenis_id"
                                    data-url-index="{{ route('get.data.jenis.obat') }}"
                                    data-url-store="{{ route('create.data.jenis.obat') }}"
                                    data-url-delete="{{ route('delete.data.jenis.obat') }}"
                                    class="block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 
                       focus:border-blue-500 focus:ring-1 focus:ring-blue-500 
                       dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                </select>

                                <!-- Tombol X custom (di luar TomSelect, sejajar di kanan tengah) -->
                                <button id="btn-clear-jenis" type="button"
                                    class="hidden absolute inset-y-0 right-5 z-20 flex items-center
                       text-gray-400 hover:text-red-500 text-sm font-bold">
                                    ×
                                </button>
                            </div>

                            <div id="jenis-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <!-- SATUAN -->
                        <div>
                            <label for="satuan_id" class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Satuan <span class="text-red-500">*</span>
                            </label>

                            <!-- wrapper select + tombol X -->
                            <div class="relative mt-1">
                                <select name="satuan_id" id="satuan_id"
                                    data-url-index="{{ route('get.data.satuan.obat') }}"
                                    data-url-store="{{ route('create.data.satuan.obat') }}"
                                    data-url-delete="{{ route('delete.data.satuan.obat') }}"
                                    class="block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 
                   focus:border-blue-500 focus:ring-1 focus:ring-blue-500 
                   dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    required>
                                </select>

                                <!-- Tombol X custom -->
                                <button id="btn-clear-satuan" type="button"
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


                    <!--
                     -->
                    <div class="grid grid-cols-1">
                        <div>
                            <label for="kandungan" class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Kandungan (pisahkan dengan koma (,))
                            </label>
                            <input type="text" name="kandungan" id="kandungan"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="Paracetamol, Caffeine, dll" autocomplete="off">
                            <div id="kandungan-error" class="text-red-600 text-[11px] mt-1"></div>
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
                                Pantau stok awal dan tanggal kedaluwarsa untuk mencegah obat kadaluarsa.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="stok_obat" class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Stok Obat (global)
                            </label>
                            <input name="stok_obat" id="stok_obat"
                                class="mt-1 block w-full text-sm bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                                readonly>
                            <p class="text-[10px] text-gray-400 mt-1">
                                Stok ini otomatis bertambah dari per depot.
                            </p>
                            <div id="stok_obat-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="expired_date"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Expired Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="expired_date" id="expired_date"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                required>
                            <div id="expired_date-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="nomor_batch"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Nomor Batch <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nomor_batch" id="nomor_batch"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="Nomor batch produksi" required autocomplete="off">
                            <div id="nomor_batch-error" class="text-red-600 text-[11px] mt-1"></div>
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
                            <label for="harga_beli_satuan"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Harga Beli Satuan (Rp)
                            </label>
                            <div class="relative mt-1">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-[11px] text-gray-400">Rp</span>
                                <input type="text" name="harga_beli_satuan" id="harga_beli_satuan"
                                    class="block w-full pl-8 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="0" autocomplete="off">
                            </div>
                            <div id="harga_beli_satuan-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="harga_jual_umum"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Harga Jual Umum (Rp)
                            </label>
                            <div class="relative mt-1">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-[11px] text-gray-400">Rp</span>
                                <input type="text" name="harga_jual_umum" id="harga_jual_umum"
                                    class="block w-full pl-8 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="0" autocomplete="off">
                            </div>
                            <div id="harga_jual_umum-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="harga_otc" class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Harga OTC (Rp)
                            </label>
                            <div class="relative mt-1">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-[11px] text-gray-400">Rp</span>
                                <input type="text" name="harga_otc" id="harga_otc"
                                    class="block w-full pl-8 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="0" autocomplete="off">
                            </div>
                            <div id="harga_otc-error" class="text-red-600 text-[11px] mt-1"></div>
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
                    <div id="depot-container" class="space-y-3">

                        <!-- ROW DEPOT (TEMPLATE PERTAMA) -->
                        <div
                            class="depot-row grid grid-cols-12 gap-4 items-center bg-gray-50/60 dark:bg-gray-800/60 
        rounded-xl px-4 py-4 border border-dashed border-gray-200 dark:border-gray-700">

                            <!-- NAMA DEPOT -->
                            <div class="col-span-12 md:col-span-4">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                    Nama Depot <span class="text-red-500">*</span>
                                </label>

                                <div class="relative mt-1">
                                    <select name="depot_id[]"
                                        class="select-nama-depot block w-full text-sm bg-transparent dark:bg-gray-900
                    border border-gray-200 rounded-lg pl-3 pr-7 py-2 focus:border-blue-500 
                    focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:text-white"
                                        data-url-index="{{ route('get.data.depot') }}"
                                        data-url-store="{{ route('create.data.depot') }}"
                                        data-url-delete="{{ route('delete.data.depot') }}">
                                        <option value="">Pilih / ketik nama depot</option>
                                    </select>

                                    <!-- tombol X -->
                                    <button type="button"
                                        class="btn-clear-depot hidden absolute right-2 top-1/2 -translate-y-1/2 
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
                                        class="select-tipe-depot block w-full text-sm bg-transparent dark:bg-gray-900
                    border border-gray-200 rounded-lg pl-3 pr-7 py-2 focus:border-blue-500 
                    focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:text-white"
                                        data-url-index="{{ route('get.data.tipe.depot') }}"
                                        data-url-store="{{ route('create.data.tipe.depot') }}"
                                        data-url-delete="{{ route('delete.data.tipe.depot') }}">
                                        <option value="">Pilih / ketik tipe depot</option>
                                    </select>

                                    <!-- tombol X -->
                                    <button type="button"
                                        class="btn-clear-tipe-depot hidden absolute right-2 top-1/2 -translate-y-1/2 
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
                                    class="input-stok-depot mt-1 block w-full text-sm bg-transparent dark:bg-gray-900
                border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 
                focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:text-white"
                                    value="0">
                            </div>

                            <!-- BUTTON HAPUS -->
                            <div class="col-span-12 md:col-span-2 flex md:justify-center justify-end">
                                <button type="button"
                                    class="btn-remove-depot w-full md:w-9 h-9 flex items-center justify-center 
                rounded-lg bg-red-50 text-red-600 text-xs hover:bg-red-100 border border-red-100">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>


                    <div class="flex justify-start">
                        <button type="button" id="btn-add-depot"
                            class="inline-flex items-center text-[11px] font-medium text-blue-600 hover:text-blue-700 hover:underline">
                            <i class="fa-solid fa-plus-circle mr-1 text-xs"></i>
                            Tambah Depot
                        </button>
                    </div>
                </div>

                <!-- Footer Buttons (sticky dengan background gradient halus) -->
                <div
                    class="sticky bottom-0 -mx-6 pt-3 pb-4 px-6 bg-gradient-to-t from-white via-white/95 to-white/40 dark:from-gray-900 dark:via-gray-900/95 dark:to-gray-900/40 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                    <button type="button" id="btn-cancel-modal-create-obat"
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

<!-- Modal Update Obat -->
<div id="modalUpdateObat" aria-hidden="true"
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
                        Edit Data Obat
                    </h3>
                    <p class="text-[11px] md:text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Perbarui informasi obat dengan benar untuk mendukung stok dan penjualan di klinik.
                    </p>
                </div>
                <button type="button" id="btn-close-modal-update-obat"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-gray-200 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Body (scrollable) -->
            <form id="formModalUpdate" class="px-6 py-5 space-y-7 overflow-y-auto" data-url="" method="POST">
                @csrf
                <input type="hidden" name="obat_id" id="edit_obat_id">

                <!-- Section: Identitas Obat -->
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <div
                                class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                                <i class="fa-solid fa-capsules text-xs"></i>
                            </div>
                            <div>
                                <h4
                                    class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                                    Identitas Obat
                                </h4>
                                <p class="text-[13px] text-gray-500 dark:text-gray-400">
                                    Data dasar obat yang tampil di rekam medis dan transaksi.
                                </p>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                                Kode Obat
                            </h4>
                            <p class="text-[13px] text-gray-500 dark:text-gray-400" id="kode_obat"></p>
                        </div>
                    </div>

                    <!-- Barcode -->
                    <div class="grid grid-cols-1">
                        <div>
                            <label for="edit_barcode"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Barcode
                            </label>
                            <div class="mt-1 flex gap-2">
                                <input type="text" name="barcode" id="edit_barcode"
                                    class="block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="Scan / masukkan barcode" autocomplete="off">
                                <button type="button"
                                    class="hidden md:inline-flex items-center px-3 py-2 text-[11px] font-medium rounded-lg border border-dashed border-gray-300 text-gray-500 hover:border-blue-400 hover:text-blue-600 dark:border-gray-600 dark:text-gray-300 dark:hover:border-blue-500">
                                    <i class="fa-solid fa-barcode text-xs mr-1"></i> Scan
                                </button>
                            </div>
                            <div id="edit_barcode-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>
                    </div>

                    <!-- Nama / Brand / Kategori -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="edit_nama_obat"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Nama Obat <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-2 text-gray-400">
                                    <i class="fa-solid fa-magnifying-glass text-[11px]"></i>
                                </span>
                                <input type="text" name="nama_obat" id="edit_nama_obat"
                                    class="block w-full pl-7 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="Cari / ketik nama obat" required autocomplete="off">
                            </div>
                            <div id="edit_nama_obat-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="edit_brand_farmasi_id"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Nama Brand Farmasi
                            </label>

                            <div class="relative mt-1">
                                <select name="brand_farmasi_id" id="edit_brand_farmasi_id"
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

                            <div id="edit_brand_farmasi_id-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div class="relative">
                            <label for="edit_kategori_obat"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Kategori Obat <span class="text-red-500">*</span>
                            </label>

                            <select name="kategori_obat" id="edit_kategori_obat"
                                data-url-index="{{ route('obat.get.data.kategori.obat') }}"
                                data-url-delete="{{ route('kategori.obat.delete.data.kategori.obat') }}"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 
                                    focus:border-blue-500 focus:ring-1 focus:ring-blue-500 
                                    dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                required>
                            </select>

                            <!-- Tombol X di pojok -->
                            <button id="btn-clear-kategori-update" type="button"
                                class="hidden absolute right-3 top-[38px] text-gray-400 hover:text-red-500">
                                ✕
                            </button>

                            <div id="edit_kategori_obat-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                    </div>

                    <!-- Jenis / Satuan / Dosis -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- JENIS -->
                        <div>
                            <label for="edit_jenis_id"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Jenis
                            </label>

                            <div class="relative mt-1">
                                <select name="jenis" id="edit_jenis_id"
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

                            <div id="edit_jenis-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <!-- SATUAN -->
                        <div>
                            <label for="edit_satuan_id"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Satuan <span class="text-red-500">*</span>
                            </label>

                            <div class="relative mt-1">
                                <select name="satuan" id="edit_satuan_id"
                                    data-url-index="{{ route('get.data.satuan.obat') }}"
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

                            <div id="edit_satuan-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <!-- DOSIS -->
                        <div>
                            <label for="edit_dosis"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Dosis (mg/ml) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" step="0.01" name="dosis" id="edit_dosis"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 
                                    focus:border-blue-500 focus:ring-1 focus:ring-blue-500 
                                    dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="Contoh: 500" required>
                            <div id="edit_dosis-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>
                    </div>

                    <!-- KANDUNGAN -->
                    <div class="grid grid-cols-1">
                        <div>
                            <label for="edit_kandungan"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Kandungan (pisahkan dengan koma (,))
                            </label>
                            <input type="text" name="kandungan" id="edit_kandungan"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="Paracetamol, Caffeine, dll" autocomplete="off">
                            <div id="edit_kandungan-error" class="text-red-600 text-[11px] mt-1"></div>
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
                                Pantau stok dan tanggal kedaluwarsa untuk mencegah obat kadaluarsa.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label for="edit_stok_obat"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Stok Obat (global)
                            </label>
                            <input name="stok_obat" id="edit_stok_obat"
                                class="mt-1 block w-full text-sm bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-700 dark:text-white"
                                readonly>
                            <p class="text-[10px] text-gray-400 mt-1">
                                Stok ini otomatis bertambah dari per depot.
                            </p>
                            <div id="edit_stok_obat-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="edit_expired_date"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Expired Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="expired_date" id="edit_expired_date"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                required>
                            <div id="edit_expired_date-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="edit_nomor_batch"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Nomor Batch <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nomor_batch" id="edit_nomor_batch"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="Nomor batch produksi" required autocomplete="off">
                            <div id="edit_nomor_batch-error" class="text-red-600 text-[11px] mt-1"></div>
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
                            <label for="edit_harga_beli_satuan"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Harga Beli Satuan (Rp)
                            </label>
                            <div class="relative mt-1">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-[11px] text-gray-400">Rp</span>
                                <input type="text" name="harga_beli_satuan" id="edit_harga_beli_satuan"
                                    class="block w-full pl-8 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="0" autocomplete="off">
                            </div>
                            <div id="edit_harga_beli_satuan-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="edit_harga_jual_umum"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Harga Jual Umum (Rp)
                            </label>
                            <div class="relative mt-1">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-[11px] text-gray-400">Rp</span>
                                <input type="text" name="harga_jual_umum" id="edit_harga_jual_umum"
                                    class="block w-full pl-8 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="0" autocomplete="off">
                            </div>
                            <div id="edit_harga_jual_umum-error" class="text-red-600 text-[11px] mt-1"></div>
                        </div>

                        <div>
                            <label for="edit_harga_otc"
                                class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Harga OTC (Rp)
                            </label>
                            <div class="relative mt-1">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-[11px] text-gray-400">Rp</span>
                                <input type="text" name="harga_otc" id="edit_harga_otc"
                                    class="block w-full pl-8 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                    placeholder="0" autocomplete="off">
                            </div>
                            <div id="edit_harga_otc-error" class="text-red-600 text-[11px] mt-1"></div>
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
                    <div id="depot-container-update" class="space-y-3">
                        <!-- ROW DEPOT TEMPLATE UPDATE -->
                        <div
                            class="depot-row grid grid-cols-12 gap-4 items-center bg-gray-50/60 dark:bg-gray-800/60 
                                rounded-xl px-4 py-4 border border-dashed border-gray-200 dark:border-gray-700">

                            <!-- NAMA DEPOT -->
                            <div class="col-span-12 md:col-span-4">
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                    Nama Depot <span class="text-red-500">*</span>
                                </label>

                                <div class="relative mt-1">
                                    <select name="depot_id[]"
                                        class="select-nama-depot block w-full text-sm bg-transparent dark:bg-gray-900
                                            border border-gray-200 rounded-lg pl-3 pr-7 py-2 focus:border-blue-500 
                                            focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:text-white"
                                        data-url-index="{{ route('get.data.depot') }}"
                                        data-url-store="{{ route('create.data.depot') }}"
                                        data-url-delete="{{ route('delete.data.depot') }}">
                                        <option value="">Pilih / ketik nama depot</option>
                                    </select>

                                    <!-- tombol X -->
                                    <button type="button"
                                        class="btn-clear-depot hidden absolute right-2 top-1/2 -translate-y-1/2 
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
                                        class="select-tipe-depot block w-full text-sm bg-transparent dark:bg-gray-900
                                            border border-gray-200 rounded-lg pl-3 pr-7 py-2 focus:border-blue-500 
                                            focus:ring-1 focus:ring-blue-500 dark:border-gray-700 dark:text-white"
                                        data-url-index="{{ route('get.data.tipe.depot') }}"
                                        data-url-store="{{ route('create.data.tipe.depot') }}"
                                        data-url-delete="{{ route('delete.data.tipe.depot') }}">
                                        <option value="">Pilih / ketik tipe depot</option>
                                    </select>

                                    <!-- tombol X -->
                                    <button type="button"
                                        class="btn-clear-tipe-depot hidden absolute right-2 top-1/2 -translate-y-1/2 
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
                                    class="input-stok-depot mt-1 block w-full text-sm bg-transparent dark:bg-gray-900
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
                    <button type="button" id="btn-cancel-modal-update-obat"
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

@vite(['resources/js/farmasi/obat/data-obat.js'])
