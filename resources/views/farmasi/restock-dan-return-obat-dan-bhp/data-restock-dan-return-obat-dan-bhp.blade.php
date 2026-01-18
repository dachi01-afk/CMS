{{-- Header --}}
<div class="mb-4 flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Restock dan Return</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Kelola transaksi restock dan return obat & BHP
        </p>
    </div>

    <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto md:items-start">
        {{-- Search --}}
        <div class="w-full md:w-[360px]">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                </span>

                <input type="text" id="customSearch"
                    class="block w-full pl-9 pr-3 py-2 text-sm
                       text-slate-800 dark:text-slate-100
                       border border-slate-200 dark:border-slate-700 rounded-xl
                       bg-white dark:bg-slate-800
                       focus:ring-sky-500 focus:border-sky-500"
                    placeholder="Cari kode / supplier / nama item...">
            </div>

            <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500 hidden sm:block">
                Contoh: <span class="italic">STK-0001, Kimia Farma, Paracetamol</span>.
            </p>
        </div>

        {{-- Button open modal --}}
        <button type="button" id="btn-open-modal-create"
            class="inline-flex items-center justify-center gap-2
               px-4 py-2 h-[42px]
               bg-emerald-600 text-white rounded-xl hover:bg-emerald-700
               shadow-sm whitespace-nowrap">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Restock & Return Obat / Barang</span>
        </button>
    </div>
</div>

{{-- Card: Toolbar + Table --}}
<div
    class="bg-white dark:bg-slate-900 rounded-2xl shadow border border-slate-100 dark:border-slate-800 overflow-hidden">

    {{-- Toolbar --}}
    <div class="px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-800">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">

            <div class="flex items-center gap-2 text-sm">
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>

                <select id="restock_pageLength"
                    class="w-36 border border-slate-200 dark:border-slate-700 text-sm rounded-xl
                           focus:ring-sky-500 focus:border-sky-500
                           bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100
                           px-3 py-2">
                    <option value="10">10 baris</option>
                    <option value="25">25 baris</option>
                    <option value="50">50 baris</option>
                    <option value="100">100 baris</option>
                </select>

                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">/ halaman</span>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table id="table-restock-return" class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                <tr>
                    <th class="px-4 py-3">Kode</th>
                    <th class="px-4 py-3">No Faktur</th>
                    <th class="px-4 py-3">Jenis</th>
                    <th class="px-4 py-3">Tgl Pengiriman</th>
                    <th class="px-4 py-3">Tgl Pembuatan</th>
                    <th class="px-4 py-3">Supplier</th>
                    <th class="px-4 py-3">Nama Item</th>
                    <th class="px-4 py-3">Jumlah</th>
                    <th class="px-4 py-3">Diapprove</th>
                    <th class="px-4 py-3">Total Harga</th>
                    <th class="px-4 py-3">Tempo</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 text-[11px] md:text-xs">
                {{-- server-side DataTables --}}
            </tbody>
        </table>
    </div>
    {{-- Footer --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                   px-3 sm:px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700
                   bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">

        <div id="custom_customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>

        {{-- Pagination aman di HP --}}
        <div class="w-full md:w-auto overflow-x-auto">
            <ul id="custom_Pagination"
                class="min-w-max inline-flex items-center gap-0 text-sm isolate rounded-lg
                           border border-slate-200 dark:border-slate-600 overflow-hidden">
            </ul>
        </div>
    </div>
</div>

{{-- MODAL CREATE RESTOCK/RETURN (STYLE DISAMAKAN DENGAN MODAL OBAT) --}}
<div id="modalCreateRestockReturn" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full bg-black/40 px-4">

    <div class="relative w-full max-w-4xl">
        {{-- Card --}}
        <div
            class="relative flex flex-col bg-white rounded-2xl shadow-2xl dark:bg-gray-900 border border-gray-100 dark:border-gray-700 max-h-[90vh]">

            {{-- Header (sticky) --}}
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 sticky top-0 bg-white dark:bg-gray-900 z-10 rounded-t-2xl">
                <div>
                    <h3 class="text-base md:text-lg font-semibold text-gray-900 dark:text-white">
                        Buat Transaksi Restock / Return
                    </h3>
                    <p class="text-[11px] md:text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Lengkapi header transaksi. Setelah tersimpan, lanjut input detail item.
                    </p>
                </div>

                <button type="button" id="btn-close-modal-create"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-gray-200 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Body (scrollable) --}}
            <form id="formCreateRestockReturn" class="px-6 py-5 space-y-7 overflow-y-auto" data-url="#"
                method="POST">
                @csrf

                {{-- Section: Header Transaksi --}}
                <div class="space-y-4">
                    <div class="flex items-center gap-2">
                        <div
                            class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                            <i class="fa-solid fa-clipboard-list text-xs"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                                Header Transaksi
                            </h4>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                Data utama transaksi restock/return.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- Tanggal transaksi --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Tanggal Transaksi <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="tanggal_transaksi"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                                       focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                                       dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                required>
                            <div class="text-red-600 text-[11px] mt-1" data-error="tanggal_transaksi"></div>
                        </div>

                        {{-- Jenis transaksi --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Jenis Transaksi <span class="text-red-500">*</span>
                            </label>
                            <select name="jenis_transaksi"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                                       focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                                       dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                required>
                                <option value="">-- Pilih --</option>
                                <option value="restock">Restock</option>
                                <option value="return">Return</option>
                            </select>
                            <div class="text-red-600 text-[11px] mt-1" data-error="jenis_transaksi"></div>
                        </div>

                        {{-- SUPPLIER (TomSelect style seperti modal obat) --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Supplier
                            </label>

                            <div class="relative mt-1">
                                <select name="supplier_id" id="supplier_id"
                                    data-url-index="{{ route('get.data.supplier') }}"
                                    data-url-store="{{ route('create.data.supplier') }}"
                                    data-url-delete="{{ route('delete.data.supplier') }}"
                                    data-url-update="{{ route('update.data.supplier') }}"
                                    data-url-show="{{ route('get.data.supplier.by.id', ['id' => '__ID__']) }}"
                                    class="block w-full pr-9 text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                                           focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                                           dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                    <option value="">Ketik untuk cari / tambah supplier</option>
                                </select>

                                {{-- Tombol X (clear & delete supplier) --}}
                                <button type="button" id="btn-clear-supplier"
                                    class="hidden absolute inset-y-0 right-2 my-auto w-5 h-5 rounded-full flex items-center justify-center
                                           text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/40">
                                    <i class="fa-solid fa-xmark text-[10px]"></i>
                                </button>
                            </div>

                            <div class="text-red-600 text-[11px] mt-1" data-error="supplier_id"></div>
                        </div>

                        {{-- Nomor faktur --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Nomor Faktur
                            </label>
                            <input type="text" name="nomor_faktur"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                                       focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                                       dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="Opsional" autocomplete="off">
                            <div class="text-red-600 text-[11px] mt-1" data-error="nomor_faktur"></div>
                        </div>

                        {{-- Keterangan --}}
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Keterangan
                            </label>
                            <textarea name="keterangan" rows="2"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                                       focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                                       dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                placeholder="Opsional"></textarea>
                            <div class="text-red-600 text-[11px] mt-1" data-error="keterangan"></div>
                        </div>

                    </div>
                </div>

                {{-- Section: Detail Supplier (muncul saat create/pilih) --}}
                <div id="supplier-detail" class="space-y-4 hidden">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                            <i class="fa-solid fa-truck-field text-xs"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold tracking-wide text-gray-800 uppercase dark:text-gray-200">
                                Detail Supplier
                            </h4>
                            <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                Otomatis terisi jika memilih supplier yang sudah ada. Editable jika supplier baru
                                dibuat.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Kontak Person
                            </label>
                            <input type="text" id="supplier_kontak_person"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                                       focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                                       dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                autocomplete="off">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                No HP
                            </label>
                            <input type="text" id="supplier_no_hp"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                                       focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                                       dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                autocomplete="off">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Email
                            </label>
                            <input type="email" id="supplier_email"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                                       focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                                       dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                autocomplete="off">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Alamat
                            </label>
                            <textarea id="supplier_alamat" rows="2"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                                       focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                                       dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                Keterangan Supplier
                            </label>
                            <textarea id="supplier_keterangan" rows="2"
                                class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                                       focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                                       dark:border-gray-700 dark:bg-gray-900 dark:text-white"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Section: Rincian Item (OBAT / BHP) --}}
                <div class="mt-5 border-t border-gray-200 dark:border-gray-700 pt-4">

                    {{-- Tabs --}}
                    <div class="flex items-center gap-6 text-xs font-semibold uppercase tracking-wide">
                        <button type="button" id="tab-obat" data-tab="obat"
                            class="pb-2 border-b-2 border-pink-500 text-gray-900 dark:text-white">
                            Obat
                        </button>

                        <button type="button" id="tab-bhp" data-tab="bhp"
                            class="pb-2 border-b-2 border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                            Bahan Habis Pakai
                        </button>
                    </div>

                    <div class="mt-4">

                        {{-- ========================= PANEL: OBAT (sesuai foto) ========================= --}}
                        <div id="panel-obat" data-panel="obat">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                {{-- Nama Obat --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Nama Obat <span class="text-red-500">*</span>
                                    </label>
                                    <select name="obat_id" id="obat_id" required class="mt-1">
                                        <option value="">Pilih obat...</option>
                                    </select>
                                    <div class="text-red-600 text-[11px] mt-1" data-error="obat_id"></div>
                                </div>

                                {{-- Kategori Obat --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Kategori Obat <span class="text-red-500">*</span>
                                    </label>
                                    <input name="kategori_obat_id" id="kategori_obat_id"
                                        class="mt-1 block w-full text-sm bg-gray-100 border border-gray-200 rounded-lg px-3 py-2
               dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                        disabled placeholder="Otomatis">
                                    <div class="text-red-600 text-[11px] mt-1" data-error="kategori_obat_id"></div>
                                </div>

                                {{-- Transaksi --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Transaksi <span class="text-red-500">*</span>
                                    </label>
                                    <select name="transaksi_obat" id="transaksi_obat"
                                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                               focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                               dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        required>
                                        <option value="">Pilih transaksi...</option>
                                        <option value="restock">Restock</option>
                                        <option value="return">Return</option>
                                    </select>
                                    <div class="text-red-600 text-[11px] mt-1" data-error="transaksi_obat"></div>
                                </div>

                                {{-- Satuan --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Satuan <span class="text-red-500">*</span>
                                    </label>
                                    <select name="satuan_obat_id" id="satuan_obat_id"
                                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                               focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                               dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        required>
                                        <option value="">Pilih satuan...</option>
                                    </select>
                                    <div class="text-red-600 text-[11px] mt-1" data-error="satuan_obat_id"></div>
                                </div>

                                {{-- Expired Date --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Expired Date <span class="text-red-500">*</span>
                                    </label>
                                    <select name="expired_date_obat" id="expired_date_obat"
                                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                               focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                               dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        required>
                                        <option value="">Pilih expired...</option>
                                    </select>
                                    <div class="text-red-600 text-[11px] mt-1" data-error="expired_date_obat"></div>
                                </div>

                                {{-- Batch --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Batch <span class="text-red-500">*</span>
                                    </label>
                                    <select name="batch_obat" id="batch_obat"
                                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                               focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                               dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        required>
                                        <option value="">Pilih batch...</option>
                                    </select>
                                    <div class="text-red-600 text-[11px] mt-1" data-error="batch_obat"></div>
                                </div>

                                {{-- Jumlah Obat --}}
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Jumlah Obat <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" min="0" name="jumlah_obat" id="jumlah_obat"
                                        value="0"
                                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                               focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                               dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        required>
                                    <div class="text-red-600 text-[11px] mt-1" data-error="jumlah_obat"></div>
                                </div>

                                {{-- Harga beli lama (kiri) --}}
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                        Harga Beli Satuan Obat Lama
                                    </label>
                                    <input type="text" name="harga_beli_lama_obat" id="harga_beli_lama_obat"
                                        readonly
                                        class="mt-1 block w-full text-sm bg-transparent border-b border-dashed border-gray-300
                               px-1 py-2 text-gray-500 dark:text-gray-400 dark:border-gray-700">
                                </div>

                                {{-- Harga satuan obat (kanan) --}}
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                        Harga Satuan Obat <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="harga_satuan_obat" id="harga_satuan_obat"
                                        class="mt-1 block w-full text-sm bg-transparent border-b border-dashed border-gray-300
                               px-1 py-2 focus:outline-none focus:border-blue-500
                               dark:text-white dark:border-gray-700"
                                        required>
                                    <div class="text-red-600 text-[11px] mt-1" data-error="harga_satuan_obat"></div>
                                </div>

                                {{-- Harga jual satuan obat lama (kiri) --}}
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                        Harga Jual Satuan Obat Lama
                                    </label>
                                    <input type="text" name="harga_jual_lama_obat" id="harga_jual_lama_obat"
                                        readonly
                                        class="mt-1 block w-full text-sm bg-transparent border-b border-dashed border-gray-300
                               px-1 py-2 text-gray-500 dark:text-gray-400 dark:border-gray-700">
                                </div>

                                {{-- (opsional) Harga jual OTC lama (kiri) --}}
                                <div>
                                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                        Harga Jual OTC Lama
                                    </label>
                                    <input type="text" name="harga_jual_otc_lama_obat"
                                        id="harga_jual_otc_lama_obat" readonly
                                        class="mt-1 block w-full text-sm bg-transparent border-b border-dashed border-gray-300
                               px-1 py-2 text-gray-500 dark:text-gray-400 dark:border-gray-700">
                                </div>

                                {{-- Harga Total Awal --}}
                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                        Harga Total Awal <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="harga_total_awal_obat" id="harga_total_awal_obat"
                                        class="mt-1 block w-full text-sm bg-transparent border-b border-dashed border-gray-300
                               px-1 py-2 focus:outline-none focus:border-blue-500
                               dark:text-white dark:border-gray-700"
                                        required>
                                    <div class="text-red-600 text-[11px] mt-1" data-error="harga_total_awal_obat">
                                    </div>
                                </div>

                                {{-- Depot Tujuan --}}
                                <div class="md:col-span-2">
                                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                        Depot Tujuan
                                    </label>
                                    <input type="text" name="depot_tujuan_obat" id="depot_tujuan_obat"
                                        value="Apotek" readonly
                                        class="mt-1 block w-full text-sm bg-transparent border-b border-dashed border-gray-300
                               px-1 py-2 text-gray-500 dark:text-gray-400 dark:border-gray-700">
                                </div>

                                {{-- Keterangan --}}
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Keterangan
                                    </label>
                                    <textarea name="keterangan_item_obat" id="keterangan_item_obat" rows="2"
                                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                               focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                               dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        placeholder="Opsional"></textarea>
                                </div>

                            </div>

                            {{-- Button: Tambah Rincian (kanan bawah seperti foto) --}}
                            <div class="mt-4 flex justify-end">
                                <button type="button" id="btn-tambah-rincian-obat"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold
                           border border-blue-500 text-blue-600 rounded-md hover:bg-blue-50
                           dark:hover:bg-blue-900/30">
                                    Tambah Rincian <i class="fa-solid fa-angle-right text-[10px]"></i>
                                </button>
                            </div>
                        </div>

                        {{-- =========================
           PANEL: BHP (sesuai foto)
           ========================= --}}
                        <div id="panel-bhp" data-panel="bhp" class="hidden">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                                {{-- Bahan Habis Pakai --}}
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Bahan Habis Pakai <span class="text-red-500">*</span>
                                    </label>
                                    <select name="bhp_id" id="bhp_id"
                                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                               focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                               dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        required>
                                        <option value="">Pilih BHP...</option>
                                    </select>
                                    <div class="text-red-600 text-[11px] mt-1" data-error="bhp_id"></div>
                                </div>

                                {{-- Kategori Bahan --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Kategori Bahan <span class="text-red-500">*</span>
                                    </label>
                                    <select name="kategori_bhp_id" id="kategori_bhp_id"
                                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                               focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                               dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        required>
                                        <option value="">Pilih kategori...</option>
                                    </select>
                                    <div class="text-red-600 text-[11px] mt-1" data-error="kategori_bhp_id"></div>
                                </div>

                                {{-- Transaksi --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Transaksi <span class="text-red-500">*</span>
                                    </label>
                                    <select name="transaksi_bhp" id="transaksi_bhp"
                                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                               focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                               dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        required>
                                        <option value="">Pilih transaksi...</option>
                                        <option value="restock">Restock</option>
                                        <option value="return">Return</option>
                                    </select>
                                    <div class="text-red-600 text-[11px] mt-1" data-error="transaksi_bhp"></div>
                                </div>

                                {{-- Expired Date --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Expired Date <span class="text-red-500">*</span>
                                    </label>
                                    <select name="expired_date_bhp" id="expired_date_bhp"
                                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                               focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                               dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        required>
                                        <option value="">Pilih expired...</option>
                                    </select>
                                    <div class="text-red-600 text-[11px] mt-1" data-error="expired_date_bhp"></div>
                                </div>

                                {{-- Batch --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Batch <span class="text-red-500">*</span>
                                    </label>
                                    <select name="batch_bhp" id="batch_bhp"
                                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                               focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                               dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        required>
                                        <option value="">Pilih batch...</option>
                                    </select>
                                    <div class="text-red-600 text-[11px] mt-1" data-error="batch_bhp"></div>
                                </div>

                                {{-- Jumlah Bahan --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Jumlah Bahan <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" min="0" name="jumlah_bhp" id="jumlah_bhp"
                                        value="0"
                                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                               focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                               dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        required>
                                    <div class="text-red-600 text-[11px] mt-1" data-error="jumlah_bhp"></div>
                                </div>

                                {{-- Satuan (di foto ada 1 field satuan) --}}
                                <div class="md:col-span-1">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Satuan <span class="text-red-500">*</span>
                                    </label>
                                    <select name="satuan_bhp_id" id="satuan_bhp_id"
                                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                               focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                               dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        required>
                                        <option value="">Pilih satuan...</option>
                                    </select>
                                    <div class="text-red-600 text-[11px] mt-1" data-error="satuan_bhp_id"></div>
                                </div>

                                {{-- Harga Beli Satuan BHP Lama --}}
                                <div class="md:col-span-1">
                                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                        Harga Beli Satuan BHP Lama
                                    </label>
                                    <input type="text" name="harga_beli_lama_bhp" id="harga_beli_lama_bhp"
                                        readonly
                                        class="mt-1 block w-full text-sm bg-transparent border-b border-dashed border-gray-300
                               px-1 py-2 text-gray-500 dark:text-gray-400 dark:border-gray-700">
                                </div>

                                {{-- Harga Satuan BHP --}}
                                <div class="md:col-span-1">
                                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                        Harga Satuan BHP <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="harga_satuan_bhp" id="harga_satuan_bhp"
                                        class="mt-1 block w-full text-sm bg-transparent border-b border-dashed border-gray-300
                               px-1 py-2 focus:outline-none focus:border-blue-500
                               dark:text-white dark:border-gray-700"
                                        required>
                                    <div class="text-red-600 text-[11px] mt-1" data-error="harga_satuan_bhp"></div>
                                </div>

                                {{-- Harga Total Awal --}}
                                <div class="md:col-span-3">
                                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                        Harga Total Awal <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="harga_total_awal_bhp" id="harga_total_awal_bhp"
                                        class="mt-1 block w-full text-sm bg-transparent border-b border-dashed border-gray-300
                               px-1 py-2 focus:outline-none focus:border-blue-500
                               dark:text-white dark:border-gray-700"
                                        required>
                                    <div class="text-red-600 text-[11px] mt-1" data-error="harga_total_awal_bhp">
                                    </div>
                                </div>

                                {{-- Depot Tujuan --}}
                                <div class="md:col-span-3">
                                    <label class="block text-[11px] font-medium text-gray-500 dark:text-gray-400">
                                        Depot Tujuan
                                    </label>
                                    <input type="text" name="depot_tujuan_bhp" id="depot_tujuan_bhp"
                                        value="Apotek" readonly
                                        class="mt-1 block w-full text-sm bg-transparent border-b border-dashed border-gray-300
                               px-1 py-2 text-gray-500 dark:text-gray-400 dark:border-gray-700">
                                </div>

                                {{-- Keterangan --}}
                                <div class="md:col-span-3">
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-300">
                                        Keterangan
                                    </label>
                                    <textarea name="keterangan_item_bhp" id="keterangan_item_bhp" rows="2"
                                        class="mt-1 block w-full text-sm bg-transparent border border-gray-200 rounded-lg px-3 py-2
                               focus:border-blue-500 focus:ring-1 focus:ring-blue-500
                               dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                        placeholder="Opsional"></textarea>
                                </div>

                            </div>

                            {{-- Button: Tambah Rincian --}}
                            <div class="mt-4 flex justify-end">
                                <button type="button" id="btn-tambah-rincian-bhp"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold
                           border border-blue-500 text-blue-600 rounded-md hover:bg-blue-50
                           dark:hover:bg-blue-900/30">
                                    Tambah Rincian <i class="fa-solid fa-angle-right text-[10px]"></i>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Footer Buttons (sticky gradient) --}}
                <div
                    class="sticky bottom-0 -mx-6 pt-3 pb-4 px-6 bg-gradient-to-t from-white via-white/95 to-white/40
                           dark:from-gray-900 dark:via-gray-900/95 dark:to-gray-900/40
                           border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                    <button type="button" id="btn-cancel-modal-create"
                        class="px-4 md:px-5 py-2.5 text-xs font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200
                               border border-gray-200 dark:bg-gray-800 dark:text-gray-100 dark:border-gray-700 dark:hover:bg-gray-700">
                        Batal
                    </button>

                    <button type="submit" id="btn-submit-create"
                        class="px-4 md:px-5 py-2.5 text-xs font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700
                               shadow-sm shadow-emerald-300/60">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@vite(['resources/js/farmasi/restock-dan-return-obat-dan-bhp/data-restock-dan-return-obat-dan-bhp.js'])