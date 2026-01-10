<!-- resources/views/kasir/order-layanan/order-layanan.blade.php -->

<section class="space-y-5">

    <!-- ================= HEADER + CTA ================= -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-stethoscope text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Order Layanan
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola pemesanan layanan klinik, mulai dari pemeriksaan hingga tindakan penunjang untuk pasien.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-3">
            <button id="buttonOpenModalCreateOrderLayanan" type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-xl shadow-md
                       bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                       focus:outline-none focus:ring-2 focus:ring-sky-400">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Order</span>
            </button>
        </div>
    </div>

    <!-- ================= CARD TABEL ================= -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm">

        <!-- Toolbar -->
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">

            <!-- Page length -->
            <div class="flex items-center gap-2 text-sm">
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>
                <select id="order-layanan-page-length"
                    class="border border-slate-300 dark:border-slate-600 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500
                           bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-1 w-28">
                    <option value="10">10 baris</option>
                    <option value="25">25 baris</option>
                    <option value="50">50 baris</option>
                    <option value="100">100 baris</option>
                </select>
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">per halaman</span>
            </div>

            <!-- Search -->
            <div class="w-full md:w-auto">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                    </span>
                    <input type="text" id="order-layanan-search-input"
                        class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm text-slate-800 dark:text-slate-100
                               border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700
                               focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari kode transaksi, nama pasien, atau layanan...">
                </div>
                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Contoh: <span class="italic">TRX-2025..., Nama pasien, jenis layanan</span>.
                </p>
            </div>
        </div>

        <!-- Tabel -->
        <div class="overflow-x-auto">
            <table id="orderLayanan"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100 border-t border-slate-100 dark:border-slate-700">
                <thead
                    class="text-[11px] md:text-xs font-semibold uppercase bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600 text-white tracking-wide">
                    <tr>
                        <th class="px-4 md:px-6 py-3">No</th>
                        <th class="px-4 md:px-6 py-3">Kode Transaksi</th>
                        <th class="px-4 md:px-6 py-3">Nama Pasien</th>
                        <th class="px-4 md:px-6 py-3">Layanan</th>
                        <th class="px-4 md:px-6 py-3">Kategori</th>
                        <th class="px-4 md:px-6 py-3">Jumlah</th>
                        <th class="px-4 md:px-6 py-3">Total Tagihan</th>
                        <th class="px-4 md:px-6 py-3">Status</th>
                        <th class="px-4 md:px-6 py-3">Tanggal</th>
                        <th class="px-4 md:px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
            </table>
        </div>

        <!-- Footer -->
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 gap-3 rounded-b-2xl">
            <div id="order-layanan-custom-info" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>
            <ul id="order-layanan-custom-pagination"
                class="inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
            </ul>
        </div>
    </div>
</section>

<!-- ================= MODAL CREATE ORDER LAYANAN ================= -->
<div id="modalCreateOrderLayanan" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center 
           w-full h-full p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto overflow-x-auto">

    <div class="relative w-full max-w-4xl">
        <div
            class="relative bg-white rounded-2xl shadow-2xl dark:bg-gray-800 border border-slate-100 dark:border-slate-700
           flex flex-col max-h-[90vh] overflow-y-auto overflow-x-hidden">

            <!-- Header -->
            <div
                class="bg-gradient-to-r from-sky-500 to-teal-600 px-5 md:px-6 pt-4 pb-3 flex items-start justify-between gap-3 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-xl bg-sky-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-file-medical text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-white">Tambah Order Layanan</h3>
                        <p class="text-[11px] text-sky-50/90 mt-0.5">
                            Pilih pasien, layanan, dan (jika perlu) poli & jadwal dokter untuk membuat order baru.
                        </p>
                    </div>
                </div>
                <button type="button" id="buttonCloseModalCreateOrderLayanan"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Form -->
            <form id="formCreateOrderLayanan"
                class="px-5 md:px-6 pb-5 pt-4 flex flex-col gap-6 bg-slate-50/60 dark:bg-slate-800" method="POST"
                data-url="{{ route('order.layanan.create.data.order.layanan') }}">
                @csrf

                <!-- PASIEN -->
                <div
                    class="bg-gray-50 dark:bg-gray-700 p-4 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-600">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <label for="pasien_search_create" class="text-sm font-semibold text-gray-900 dark:text-white">
                            Cari Pasien
                        </label>
                    </div>

                    <div class="relative">
                        <input type="text" id="pasien_search_create"
                            class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg
                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-3 py-2.5
                                   dark:bg-gray-600 dark:border-gray-500 dark:text-white
                                   transition-all duration-200 hover:shadow-md"
                            placeholder="Ketik nama / No EMR / No RM / NIK pasien...">

                        <input type="hidden" name="pasien_id" id="pasien_id_create">

                        <div id="pasien_search_results_create"
                            class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 
                                   rounded-lg shadow-lg max-h-60 overflow-y-auto hidden text-sm">
                        </div>
                    </div>

                    <div id="pasien_id_create-error"
                        class="text-red-600 text-xs md:text-sm mt-2 opacity-100 transition-opacity duration-200">
                    </div>

                    <!-- Info pasien -->
                    <div id="pasien_info_create"
                        class="mt-4 hidden rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 
                               dark:bg-gray-800 dark:border-blue-900/60">
                        <div class="flex items-start gap-3">
                            <div
                                class="h-9 w-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 dark:bg-blue-900/40 dark:text-blue-200">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div class="flex-1">
                                <p
                                    class="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-1 dark:text-blue-300">
                                    Data Pasien Terpilih
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-1 text-sm">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400 text-xs">Nama Pasien</span>
                                        <p id="pasien_nama_info_create"
                                            class="font-semibold text-gray-800 dark:text-gray-100">
                                            -
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400 text-xs">No EMR</span>
                                        <p id="pasien_no_emr_info_create"
                                            class="font-semibold text-gray-800 dark:text-gray-100">
                                            -
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400 text-xs">Jenis Kelamin</span>
                                        <p id="pasien_jk_info_create"
                                            class="font-semibold text-gray-800 dark:text-gray-100">
                                            -
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DAFTAR LAYANAN (MULTI ITEM) -->
                <div
                    class="bg-gray-50 dark:bg-gray-700 p-4 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-600">

                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    Daftar Layanan
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-300">
                                    Tambahkan satu atau lebih layanan dalam satu transaksi.
                                </p>
                            </div>
                        </div>

                        <button type="button" id="btnAddLayananRow"
                            class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg
                       bg-gradient-to-r from-sky-500 to-teal-600 text-white hover:from-sky-600 hover:to-teal-700
                       shadow-sm">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Tambah Layanan</span>
                        </button>
                    </div>

                    <!-- WRAPPER BARIS LAYANAN -->
                    <div id="orderItemsWrapper" class="mt-3 space-y-3">

                        <!-- TEMPLATE BARIS (SATU LAYANAN) -->
                        <div id="orderItemTemplate"
                            class="order-item flex flex-col md:flex-row items-start gap-3
                    bg-white/90 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-600
                    px-3 py-3">

                            <!-- LAYANAN -->
                            <div class="w-full md:w-4/12">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">
                                    Layanan
                                </label>
                                <select
                                    class="layanan-select w-full bg-white border border-gray-300 text-gray-900 text-xs md:text-sm rounded-lg
                           focus:ring-2 focus:ring-green-500 focus:border-green-500 px-2.5 py-2
                           dark:bg-gray-700 dark:border-gray-500 dark:text-white">
                                    @foreach ($dataLayanan as $layanan)
                                        <option value="{{ $layanan->id }}"
                                            data-kategori-id="{{ $layanan->kategori_layanan_id }}"
                                            data-kategori-nama="{{ $layanan->kategoriLayanan->nama_kategori ?? '' }}"
                                            data-harga="{{ $layanan->harga_setelah_diskon }}"
                                            data-is-global="{{ (int) ($layanan->is_global ?? 0) }}"
                                            data-poli-ids="{{ $layanan->polis?->pluck('id')->implode(',') ?? '' }}">
                                            {{ $layanan->nama_layanan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- KATEGORI -->
                            <div class="w-full md:w-3/12">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">
                                    Kategori
                                </label>
                                <input type="text"
                                    class="kategori-nama-input w-full bg-gray-100 border border-gray-300 text-gray-900 text-xs md:text-sm rounded-lg
                              px-2.5 py-2 dark:bg-gray-700 dark:border-gray-500 dark:text-white"
                                    placeholder="Otomatis" readonly>
                                <input type="hidden" class="kategori-id-input">
                            </div>

                            <!-- JUMLAH -->
                            <div class="w-full md:w-2/12">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">
                                    Jumlah
                                </label>
                                <input type="number" min="1" value="1"
                                    class="jumlah-input w-full bg-white border border-gray-300 text-gray-900 text-xs md:text-sm rounded-lg
                              px-2.5 py-2 dark:bg-gray-700 dark:border-gray-500 dark:text-white">
                            </div>

                            <!-- SUBTOTAL -->
                            <div class="w-full md:w-3/12">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">
                                    Subtotal
                                </label>
                                <input type="text"
                                    class="subtotal-input w-full bg-gray-100 border border-gray-300 text-gray-900 text-xs md:text-sm rounded-lg
                              px-2.5 py-2 dark:bg-gray-700 dark:border-gray-500 dark:text-white"
                                    placeholder="Rp 0" readonly>
                            </div>

                            <!-- HAPUS BARIS -->
                            <div class="self-stretch flex items-center md:items-start pt-5">
                                <button type="button"
                                    class="btn-remove-item inline-flex items-center justify-center h-8 w-8 rounded-full
                               text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- RINGKASAN TOTAL -->
                    <div
                        class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-600 flex items-center justify-between">
                        <p class="text-xs md:text-sm text-gray-600 dark:text-gray-300 font-medium">
                            Total Tagihan
                        </p>
                        <input type="text" id="total_tagihan_create" name="total_tagihan" readonly
                            class="w-40 md:w-52 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg
                      px-3 py-2.5 dark:bg-gray-700 dark:border-gray-500 dark:text-white"
                            placeholder="Rp 0">
                    </div>

                    <div id="total_tagihan_create-error"
                        class="text-red-600 text-xs md:text-sm mt-2 opacity-0 transition-opacity duration-200">
                    </div>
                </div>


                <!-- POLI & JADWAL (Hanya PEMERIKSAAN) -->
                <div id="section_poli_jadwal_create" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2 hidden">

                    <!-- POLI -->
                    <div
                        class="bg-gray-50 dark:bg-gray-700 p-4 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7h18M5 7v10a2 2 0 002 2h10a2 2 0 002-2V7"></path>
                            </svg>
                            <label for="poli_id_select_create"
                                class="text-sm font-semibold text-gray-900 dark:text-white">
                                Poli Tujuan
                            </label>
                        </div>

                        <select id="poli_id_select_create" name="poli_id"
                            class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg
                                       focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2.5
                                       dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                            <option value="">-- Pilih Poli --</option>
                            @foreach ($dataPoli as $poli)
                                <option value="{{ $poli->id }}">{{ $poli->nama_poli }}</option>
                            @endforeach
                        </select>
                        <div id="poli_id_create-error"
                            class="text-red-600 text-xs md:text-sm mt-2 opacity-0 transition-opacity duration-200">
                        </div>
                    </div>

                    <!-- JADWAL DOKTER HARI INI -->
                    <div
                        class="bg-gray-50 dark:bg-gray-700 p-4 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <label for="jadwal_dokter_id_create"
                                class="text-sm font-semibold text-gray-900 dark:text-white">
                                Jadwal Dokter (Hari Ini)
                            </label>
                        </div>

                        <select id="jadwal_dokter_id_create" name="jadwal_dokter_id"
                            class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg
                                       focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-3 py-2.5
                                       dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                            disabled>
                            <option value="">-- Pilih Jadwal Dokter --</option>
                        </select>

                        <input type="hidden" name="dokter_id" id="dokter_id_create">

                        <div id="jadwal_dokter_id_create-error"
                            class="text-red-600 text-xs md:text-sm mt-2 opacity-0 transition-opacity duration-200">
                        </div>

                        <p id="info_jadwal_dokter_create"
                            class="mt-2 text-xs text-gray-500 dark:text-gray-300 hidden"></p>
                    </div>
                </div>

                <!-- BUTTONS -->
                <div class="flex justify-end gap-3 md:gap-4 mt-6 pt-4 border-t border-gray-200 dark:border-gray-600">
                    <button type="button" id="buttonCancaleModalCreateOrderLayanan"
                        class="px-5 md:px-6 py-2.5 md:py-3 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-white transition-all duration-200 hover:shadow-lg inline-flex items-center gap-2">
                        <i class="fa-solid fa-xmark text-xs"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit" id="saveOrderLayananButton"
                        class="px-5 md:px-6 py-2.5 md:py-3 text-sm font-semibold text-white bg-gradient-to-r from-sky-500 to-teal-600 rounded-lg hover:from-sky-600 hover:to-teal-700 focus:ring-2 focus:ring-sky-400 transition-all duration-200 hover:shadow-lg inline-flex items-center gap-2">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span>Simpan Order</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL UPDATE ORDER LAYANAN ================= -->
<div id="modalUpdateOrderLayanan" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center 
           w-full h-full p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto overflow-x-auto">

    <div class="relative w-full max-w-4xl">
        <div
            class="relative bg-white rounded-2xl shadow-2xl dark:bg-gray-800 border border-slate-100 dark:border-slate-700
           flex flex-col max-h-[90vh] overflow-y-auto overflow-x-hidden">

            <!-- Header -->
            <div
                class="bg-gradient-to-r from-sky-500 to-teal-600 px-5 md:px-6 pt-4 pb-3 flex items-start justify-between gap-3 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-xl bg-sky-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-file-medical text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-white">Edit Order Layanan</h3>
                        <p class="text-[11px] text-sky-50/90 mt-0.5">
                            Ubah detail pasien, layanan, dan (jika perlu) poli & jadwal dokter untuk order ini.
                        </p>
                    </div>
                </div>
                <button type="button" id="buttonCloseModalUpdateOrderLayanan"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Form -->
            <form id="formUpdateOrderLayanan"
                class="px-5 md:px-6 pb-5 pt-4 flex flex-col gap-6 bg-slate-50/60 dark:bg-slate-800" method="POST"
                data-url="{{ route('order.layanan.update.data.order.layanan') }}">
                @csrf

                <!-- ID ORDER (HIDDEN) -->
                <input type="hidden" name="order_layanan_id" id="order_layanan_id_update">

                <!-- PASIEN -->
                <div
                    class="bg-gray-50 dark:bg-gray-700 p-4 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-600">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <label for="pasien_search_update" class="text-sm font-semibold text-gray-900 dark:text-white">
                            Cari / Ubah Pasien
                        </label>
                    </div>

                    <div class="relative">
                        <input type="text" id="pasien_search_update"
                            class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg
                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500 px-3 py-2.5
                                   dark:bg-gray-600 dark:border-gray-500 dark:text-white
                                   transition-all duration-200 hover:shadow-md"
                            placeholder="Ketik nama / No EMR / No RM / NIK pasien...">

                        <input type="hidden" name="pasien_id" id="pasien_id_update">

                        <div id="pasien_search_results_update"
                            class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 
                                   rounded-lg shadow-lg max-h-60 overflow-y-auto hidden text-sm">
                        </div>
                    </div>

                    <div id="pasien_id_update-error"
                        class="text-red-600 text-xs md:text-sm mt-2 opacity-100 transition-opacity duration-200">
                    </div>

                    <!-- Info pasien -->
                    <div id="pasien_info_update"
                        class="mt-4 hidden rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 
                               dark:bg-gray-800 dark:border-blue-900/60">
                        <div class="flex items-start gap-3">
                            <div
                                class="h-9 w-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 dark:bg-blue-900/40 dark:text-blue-200">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div class="flex-1">
                                <p
                                    class="text-xs font-semibold text-blue-700 uppercase tracking-wide mb-1 dark:text-blue-300">
                                    Data Pasien Terpilih
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-1 text-sm">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400 text-xs">Nama Pasien</span>
                                        <p id="pasien_nama_info_update"
                                            class="font-semibold text-gray-800 dark:text-gray-100">
                                            -
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400 text-xs">No EMR</span>
                                        <p id="pasien_no_emr_info_update"
                                            class="font-semibold text-gray-800 dark:text-gray-100">
                                            -
                                        </p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400 text-xs">Jenis Kelamin</span>
                                        <p id="pasien_jk_info_update"
                                            class="font-semibold text-gray-800 dark:text-gray-100">
                                            -
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DAFTAR LAYANAN (MULTI ITEM) -->
                <div
                    class="bg-gray-50 dark:bg-gray-700 p-4 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-600">

                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                    Daftar Layanan
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-300">
                                    Ubah, tambah, atau hapus layanan dalam satu transaksi.
                                </p>
                            </div>
                        </div>

                        <button type="button" id="btnAddLayananRowUpdate"
                            class="inline-flex items-center gap-2 px-3 py-1.5 text-xs font-medium rounded-lg
                       bg-gradient-to-r from-sky-500 to-teal-600 text-white hover:from-sky-600 hover:to-teal-700
                       shadow-sm">
                            <i class="fa-solid fa-plus text-[10px]"></i>
                            <span>Tambah Layanan</span>
                        </button>
                    </div>

                    <!-- WRAPPER BARIS LAYANAN -->
                    <div id="orderItemsWrapperUpdate" class="mt-3 space-y-3">

                        <!-- TEMPLATE BARIS (SATU LAYANAN) -->
                        <div id="orderItemTemplateUpdate"
                            class="order-item flex flex-col md:flex-row items-start gap-3
                    bg-white/90 dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-600
                    px-3 py-3">

                            <!-- LAYANAN -->
                            <div class="w-full md:w-4/12">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">
                                    Layanan
                                </label>
                                <select
                                    class="layanan-select w-full bg-white border border-gray-300 text-gray-900 text-xs md:text-sm rounded-lg
                           focus:ring-2 focus:ring-green-500 focus:border-green-500 px-2.5 py-2
                           dark:bg-gray-700 dark:border-gray-500 dark:text-white">
                                    @foreach ($dataLayanan as $layanan)
                                        <option value="{{ $layanan->id }}"
                                            data-kategori-id="{{ $layanan->kategori_layanan_id }}"
                                            data-kategori-nama="{{ $layanan->kategoriLayanan->nama_kategori ?? '' }}"
                                            data-harga="{{ $layanan->getRawOriginal('harga_setelah_diskon') ?? 0 }}">
                                            {{ $layanan->nama_layanan }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            <!-- KATEGORI -->
                            <div class="w-full md:w-3/12">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">
                                    Kategori
                                </label>
                                <input type="text"
                                    class="kategori-nama-input w-full bg-gray-100 border border-gray-300 text-gray-900 text-xs md:text-sm rounded-lg
                              px-2.5 py-2 dark:bg-gray-700 dark:border-gray-500 dark:text-white"
                                    placeholder="Otomatis" readonly>
                                <input type="hidden" class="kategori-id-input">
                            </div>

                            <!-- JUMLAH -->
                            <div class="w-full md:w-2/12">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">
                                    Jumlah
                                </label>
                                <input type="number" min="1" value="1"
                                    class="jumlah-input w-full bg-white border border-gray-300 text-gray-900 text-xs md:text-sm rounded-lg
                              px-2.5 py-2 dark:bg-gray-700 dark:border-gray-500 dark:text-white">
                            </div>

                            <!-- SUBTOTAL -->
                            <div class="w-full md:w-3/12">
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-200 mb-1">
                                    Subtotal
                                </label>
                                <input type="text"
                                    class="subtotal-input w-full bg-gray-100 border border-gray-300 text-gray-900 text-xs md:text-sm rounded-lg
                              px-2.5 py-2 dark:bg-gray-700 dark:border-gray-500 dark:text-white"
                                    placeholder="Rp 0" readonly>
                            </div>

                            <!-- HAPUS BARIS -->
                            <div class="self-stretch flex items-center md:items-start pt-5">
                                <button type="button"
                                    class="btn-remove-item inline-flex items-center justify-center h-8 w-8 rounded-full
                               text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- RINGKASAN TOTAL -->
                    <div
                        class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-600 flex items-center justify-between">
                        <p class="text-xs md:text-sm text-gray-600 dark:text-gray-300 font-medium">
                            Total Tagihan
                        </p>
                        <input type="text" id="total_tagihan_update" name="total_tagihan" readonly
                            class="w-40 md:w-52 bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg
                      px-3 py-2.5 dark:bg-gray-700 dark:border-gray-500 dark:text-white"
                            placeholder="Rp 0">
                    </div>

                    <div id="total_tagihan_update-error"
                        class="text-red-600 text-xs md:text-sm mt-2 opacity-0 transition-opacity duration-200">
                    </div>
                </div>


                <!-- POLI & JADWAL (Hanya PEMERIKSAAN) -->
                <div id="section_poli_jadwal_update" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2 hidden">

                    <!-- POLI -->
                    <div
                        class="bg-gray-50 dark:bg-gray-700 p-4 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 7h18M5 7v10a2 2 0 002 2h10a2 2 0 002-2V7"></path>
                            </svg>
                            <label for="poli_id_select_update"
                                class="text-sm font-semibold text-gray-900 dark:text-white">
                                Poli Tujuan
                            </label>
                        </div>

                        <select id="poli_id_select_update" name="poli_id"
                            class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg
                                       focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2.5
                                       dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                            <option value="">-- Pilih Poli --</option>
                            @foreach ($dataPoli as $poli)
                                <option value="{{ $poli->id }}">{{ $poli->nama_poli }}</option>
                            @endforeach
                        </select>
                        <div id="poli_id_update-error"
                            class="text-red-600 text-xs md:text-sm mt-2 opacity-0 transition-opacity duration-200">
                        </div>
                    </div>

                    <!-- JADWAL DOKTER HARI INI -->
                    <div
                        class="bg-gray-50 dark:bg-gray-700 p-4 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <label for="jadwal_dokter_id_update"
                                class="text-sm font-semibold text-gray-900 dark:text-white">
                                Jadwal Dokter (Hari Ini)
                            </label>
                        </div>

                        <select id="jadwal_dokter_id_update" name="jadwal_dokter_id"
                            class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg
                                       focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 px-3 py-2.5
                                       dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                            disabled>
                            <option value="">-- Pilih Jadwal Dokter --</option>
                        </select>

                        <input type="hidden" name="dokter_id" id="dokter_id_update">

                        <div id="jadwal_dokter_id_update-error"
                            class="text-red-600 text-xs md:text-sm mt-2 opacity-0 transition-opacity duration-200">
                        </div>

                        <p id="info_jadwal_dokter_update"
                            class="mt-2 text-xs text-gray-500 dark:text-gray-300 hidden"></p>
                    </div>
                </div>

                <!-- BUTTONS -->
                <div class="flex justify-end gap-3 md:gap-4 mt-6 pt-4 border-t border-gray-200 dark:border-gray-600">
                    <button type="button" id="buttonCancaleModalUpdateOrderLayanan"
                        class="px-5 md:px-6 py-2.5 md:py-3 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-white transition-all duration-200 hover:shadow-lg inline-flex items-center gap-2">
                        <i class="fa-solid fa-xmark text-xs"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit" id="updateOrderLayananButton"
                        class="px-5 md:px-6 py-2.5 md:py-3 text-sm font-semibold text-white bg-gradient-to-r from-sky-500 to-teal-600 rounded-lg hover:from-sky-600 hover:to-teal-700 focus:ring-2 focus:ring-sky-400 transition-all duration-200 hover:shadow-lg inline-flex items-center gap-2">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span>Update Order</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@vite(['resources/js/admin/order-layanan/data-order-layanan.js'])
