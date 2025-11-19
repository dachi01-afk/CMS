{{-- resources/views/kasir/order-layanan/order-layanan.blade.php --}}

<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Order Layanan</h2>

    <!-- Modal toggle -->
    <button id="buttonOpenModalCreateOrderLayanan"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none">
        + Tambah Order
    </button>
</div>

<!-- Tabel Order Layanan -->
<div class="overflow-hidden rounded-lg shadow-md">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200">
        <div>
            <select id="order-layanan-page-length"
                class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-24 p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="relative">
            <input type="text" id="order-layanan-search-input"
                class="block w-60 p-2 pl-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Cari order layanan...">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="orderLayanan" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs uppercase bg-sky-500 text-white">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">Kode Transaksi</th>
                    <th class="px-6 py-3">Nama Pasien</th>
                    <th class="px-6 py-3">Layanan</th>
                    <th class="px-6 py-3">Kategori</th>
                    <th class="px-6 py-3">Jumlah</th>
                    <th class="px-6 py-3">Total Tagihan</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Tanggal</th>
                    <th class="px-6 py-3">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-4 py-3 border-t border-gray-200 gap-3">
        <div id="order-layanan-custom-info" class="text-sm text-gray-700"></div>
        <ul id="order-layanan-custom-pagination" class="inline-flex -space-x-px text-sm"></ul>
    </div>
</div>

<!-- Modal Create Order Layanan -->
<div id="modalCreateOrderLayanan" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-auto fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">

    <div class="relative w-full max-w-3xl max-h-full">
        <div class="relative bg-white rounded-2xl shadow-2xl dark:bg-gray-800 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-2xl font-bold text-white">Tambah Order Layanan</h3>
                </div>
                <button type="button" id="buttonCloseModalCreateOrderLayanan"
                    class="text-white hover:text-gray-200 transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            <form id="formCreateOrderLayanan" class="p-6 flex flex-col gap-6" method="POST"
                data-url="{{ route('order.layanan.create.data.order.layanan') }}">
                @csrf

                {{-- ================= PASIEN SECTION ================= --}}
                <div
                    class="bg-gray-50 dark:bg-gray-700 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
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
                                   focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3
                                   dark:bg-gray-600 dark:border-gray-500 dark:text-white
                                   transition-all duration-200 hover:shadow-md"
                            placeholder="Ketik nama / No EMR / No RM / NIK pasien...">

                        <input type="hidden" name="pasien_id" id="pasien_id_create">

                        <div id="pasien_search_results_create"
                            class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 
                                   rounded-lg shadow-lg max-h-60 overflow-y-auto hidden">
                        </div>
                    </div>

                    <div id="pasien_id_create-error"
                        class="text-red-600 text-sm mt-2 opacity-100 transition-opacity duration-200">
                    </div>

                    {{-- Info pasien --}}
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

                {{-- ================= LAYANAN & KATEGORI ================= --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div
                        class="bg-gray-50 dark:bg-gray-700 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <label for="layanan_id_create"
                                class="text-sm font-semibold text-gray-900 dark:text-white">Layanan</label>
                        </div>
                        <select name="layanan_id" id="layanan_id_create"
                            class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg
                                   focus:ring-2 focus:ring-green-500 focus:border-green-500 p-3
                                   dark:bg-gray-600 dark:border-gray-500 dark:text-white
                                   transition-all duration-200 hover:shadow-md">
                            <option value="">-- Pilih Layanan --</option>
                            @foreach ($dataLayanan as $layanan)
                                <option value="{{ $layanan->id }}"
                                    data-kategori-id="{{ $layanan->kategori_layanan_id }}"
                                    data-kategori-nama="{{ $layanan->kategoriLayanan->nama_kategori ?? '' }}"
                                    data-harga="{{ $layanan->harga_layanan }}">
                                    {{ $layanan->nama_layanan }}
                                </option>
                            @endforeach
                        </select>
                        <div id="layanan_id_create-error"
                            class="text-red-600 text-sm mt-2 opacity-0 transition-opacity duration-200 hover:opacity-100">
                        </div>
                    </div>

                    <div
                        class="bg-gray-50 dark:bg-gray-700 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                </path>
                            </svg>
                            <label for="kategori_layanan_nama_create"
                                class="text-sm font-semibold text-gray-900 dark:text-white">Kategori Layanan</label>
                        </div>
                        <input type="text" id="kategori_layanan_nama_create" readonly
                            class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 p-3 dark:bg-gray-600 dark:border-gray-500 dark:text-white transition-all duration-200"
                            placeholder="Akan terisi otomatis">
                        <input type="hidden" name="kategori_layanan_id" id="kategori_layanan_id_create">
                    </div>
                </div>

                {{-- ================= JUMLAH & TOTAL ================= --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div
                        class="bg-gray-50 dark:bg-gray-700 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 20l4-16m2 16l4-16M6 9h14M4 15h16"></path>
                            </svg>
                            <label for="jumlah_create"
                                class="text-sm font-semibold text-gray-900 dark:text-white">Jumlah</label>
                        </div>
                        <input type="number" name="jumlah" id="jumlah_create" min="1" value="1"
                            class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 p-3 dark:bg-gray-600 dark:border-gray-500 dark:text-white transition-all duration-200 hover:shadow-md">
                        <div id="jumlah_create-error"
                            class="text-red-600 text-sm mt-2 opacity-0 transition-opacity duration-200 hover:opacity-100">
                        </div>
                    </div>

                    <div
                        class="bg-gray-50 dark:bg-gray-700 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                </path>
                            </svg>
                            <label for="total_tagihan_create"
                                class="text-sm font-semibold text-gray-900 dark:text-white">Total Tagihan</label>
                        </div>
                        <input type="text" id="total_tagihan_create" name="total_tagihan" readonly
                            class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 p-3 dark:bg-gray-600 dark:border-gray-500 dark:text-white transition-all duration-200"
                            placeholder="Rp 0">
                        <div id="total_tagihan_create-error"
                            class="text-red-600 text-sm mt-2 opacity-0 transition-opacity duration-200 hover:opacity-100">
                        </div>
                    </div>
                </div>

                {{-- ================= POLI & JADWAL (Hanya PEMERIKSAAN) ================= --}}
                <div id="section_poli_jadwal_create" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2 hidden">

                    {{-- POLI --}}
                    <div
                        class="bg-gray-50 dark:bg-gray-700 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
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
                                       focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 p-3
                                       dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                            <option value="">-- Pilih Poli --</option>
                            @foreach ($dataPoli as $poli)
                                <option value="{{ $poli->id }}">{{ $poli->nama_poli }}</option>
                            @endforeach
                        </select>
                        <div id="poli_id_create-error"
                            class="text-red-600 text-sm mt-2 opacity-0 transition-opacity duration-200"></div>
                    </div>

                    {{-- JADWAL DOKTER HARI INI --}}
                    <div
                        class="bg-gray-50 dark:bg-gray-700 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
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
                                       focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 p-3
                                       dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                            disabled>
                            <option value="">-- Pilih Jadwal Dokter --</option>
                        </select>

                        <input type="hidden" name="dokter_id" id="dokter_id_create">

                        <div id="jadwal_dokter_id_create-error"
                            class="text-red-600 text-sm mt-2 opacity-0 transition-opacity duration-200"></div>

                        <p id="info_jadwal_dokter_create"
                            class="mt-2 text-xs text-gray-500 dark:text-gray-300 hidden"></p>
                    </div>
                </div>

                {{-- ================= BUTTONS ================= --}}
                <div class="flex justify-end gap-4 mt-6 pt-4 border-t border-gray-200 dark:border-gray-600">
                    <button type="button" id="buttonCancaleModalCreateOrderLayanan"
                        class="px-6 py-3 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-white transition-all duration-200 hover:shadow-lg flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Batal
                    </button>
                    <button type="submit" id="saveOrderLayananButton"
                        class="px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg hover:from-blue-600 hover:to-purple-700 focus:ring-4 focus:ring-blue-300 transition-all duration-200 hover:shadow-lg flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Simpan Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Update Order Layanan -->
<div id="modalUpdateOrderLayanan" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">

    <div class="relative w-full max-w-3xl max-h-full">
        <div class="relative bg-white rounded-2xl shadow-2xl dark:bg-gray-800 overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="text-2xl font-bold text-white">Edit Order Layanan</h3>
                </div>
                <button type="button" id="buttonCloseModalUpdateOrderLayanan"
                    class="text-white hover:text-gray-200 transition-colors duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="formUpdateOrderLayanan" class="p-6 flex flex-col gap-6"
                data-url="{{ route('order.layanan.update.data.order.layanan') }}" method="POST">
                @csrf
                <input type="hidden" id="id_update" name="id">

                {{-- ================= PASIEN ================= --}}
                <div
                    class="bg-gray-50 dark:bg-gray-700 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <label for="pasien_id_update" class="text-sm font-semibold text-gray-900 dark:text-white">
                            Pasien
                        </label>
                    </div>

                    <select name="pasien_id" id="pasien_id_update"
                        class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500 p-3
                               dark:bg-gray-600 dark:border-gray-500 dark:text-white
                               transition-all duration-200 hover:shadow-md">
                    </select>

                    <div id="pasien_id_update-error"
                        class="text-red-600 text-sm mt-2 opacity-0 transition-opacity duration-200"></div>

                    {{-- Info pasien --}}
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
                                            class="font-semibold text-gray-800 dark:text-gray-100">-</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400 text-xs">No EMR</span>
                                        <p id="pasien_no_emr_info_update"
                                            class="font-semibold text-gray-800 dark:text-gray-100">-</p>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400 text-xs">Jenis Kelamin</span>
                                        <p id="pasien_jk_info_update"
                                            class="font-semibold text-gray-800 dark:text-gray-100">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================= LAYANAN & KATEGORI ================= --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div
                        class="bg-gray-50 dark:bg-gray-700 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <label for="layanan_id_update"
                                class="text-sm font-semibold text-gray-900 dark:text-white">Layanan</label>
                        </div>
                        <select name="layanan_id" id="layanan_id_update"
                            class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg
                                   focus:ring-2 focus:ring-green-500 focus:border-green-500 p-3
                                   dark:bg-gray-600 dark:border-gray-500 dark:text-white
                                   transition-all duration-200 hover:shadow-md">
                            <option value="">-- Pilih Layanan --</option>
                            @foreach ($dataLayanan as $layanan)
                                <option value="{{ $layanan->id }}"
                                    data-kategori-id="{{ $layanan->kategori_layanan_id }}"
                                    data-kategori-nama="{{ $layanan->kategoriLayanan->nama_kategori ?? '' }}"
                                    data-harga="{{ $layanan->harga_layanan }}">
                                    {{ $layanan->nama_layanan }}
                                </option>
                            @endforeach
                        </select>
                        <div id="layanan_id_update-error"
                            class="text-red-600 text-sm mt-2 opacity-0 transition-opacity duration-200"></div>
                    </div>

                    <div
                        class="bg-gray-50 dark:bg-gray-700 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                                </path>
                            </svg>
                            <label for="kategori_layanan_nama_update"
                                class="text-sm font-semibold text-gray-900 dark:text-white">Kategori Layanan</label>
                        </div>
                        <input type="text" id="kategori_layanan_nama_update" readonly
                            class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg
                                   focus:ring-2 focus:ring-purple-500 focus:border-purple-500 p-3
                                   dark:bg-gray-600 dark:border-gray-500 dark:text-white
                                   transition-all duration-200"
                            placeholder="Akan terisi otomatis">
                        <input type="hidden" name="kategori_layanan_id" id="kategori_layanan_id_update">
                    </div>
                </div>

                {{-- ================= JUMLAH & TOTAL ================= --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div
                        class="bg-gray-50 dark:bg-gray-700 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 20l4-16m2 16l4-16M6 9h14M4 15h16"></path>
                            </svg>
                            <label for="jumlah_update"
                                class="text-sm font-semibold text-gray-900 dark:text-white">Jumlah</label>
                        </div>
                        <input type="number" name="jumlah" id="jumlah_update" min="1"
                            class="w-full bg-white border border-gray-300 text-gray-900 text-sm rounded-lg
                                   focus:ring-2 focus:ring-orange-500 focus:border-orange-500 p-3
                                   dark:bg-gray-600 dark:border-gray-500 dark:text-white
                                   transition-all duration-200 hover:shadow-md">
                        <div id="jumlah_update-error"
                            class="text-red-600 text-sm mt-2 opacity-0 transition-opacity duration-200"></div>
                    </div>

                    <div
                        class="bg-gray-50 dark:bg-gray-700 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-teal-500" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                </path>
                            </svg>
                            <label for="total_tagihan_update"
                                class="text-sm font-semibold text-gray-900 dark:text-white">Total Tagihan</label>
                        </div>
                        {{-- tampil Rp di front-end --}}
                        <input type="text" id="total_tagihan_update" name="total_tagihan" readonly
                            class="w-full bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg
                                   focus:ring-2 focus:ring-teal-500 focus:border-teal-500 p-3
                                   dark:bg-gray-600 dark:border-gray-500 dark:text-white
                                   transition-all duration-200"
                            placeholder="Rp 0">
                        <div id="total_tagihan_update-error"
                            class="text-red-600 text-sm mt-2 opacity-0 transition-opacity duration-200"></div>
                    </div>
                </div>

                {{-- ================= POLI & JADWAL (Hanya PEMERIKSAAN) ================= --}}
                <div id="section_poli_jadwal_update"
                     class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-2 hidden">

                    {{-- POLI --}}
                    <div
                        class="bg-gray-50 dark:bg-gray-700 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
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
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 p-3
                                   dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                            <option value="">-- Pilih Poli --</option>
                            @foreach ($dataPoli as $poli)
                                <option value="{{ $poli->id }}">{{ $poli->nama_poli }}</option>
                            @endforeach
                        </select>
                        <div id="poli_id_update-error"
                            class="text-red-600 text-sm mt-2 opacity-0 transition-opacity duration-200"></div>
                    </div>

                    {{-- JADWAL DOKTER HARI INI --}}
                    <div
                        class="bg-gray-50 dark:bg-gray-700 p-4 rounded-xl shadow-sm border border-gray-200 dark:border-gray-600">
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
                                   focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 p-3
                                   dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                            disabled>
                            <option value="">-- Pilih Jadwal Dokter --</option>
                        </select>

                        <input type="hidden" name="dokter_id" id="dokter_id_update">

                        <div id="jadwal_dokter_id_update-error"
                            class="text-red-600 text-sm mt-2 opacity-0 transition-opacity duration-200"></div>

                        <p id="info_jadwal_dokter_update"
                           class="mt-2 text-xs text-gray-500 dark:text-gray-300 hidden"></p>
                    </div>
                </div>

                {{-- ================= BUTTONS ================= --}}
                <div class="flex justify-end gap-4 mt-6 pt-4 border-t border-gray-200 dark:border-gray-600">
                    <button type="button" id="buttonCancleModalUpdateOrderLayanan"
                        class="px-6 py-3 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-white transition-all duration-200 hover:shadow-lg flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Batal
                    </button>
                    <button type="submit" id="updateOrderLayananButton"
                        class="px-6 py-3 text-sm font-medium text-white bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg hover:from-blue-600 hover:to-purple-700 focus:ring-4 focus:ring-blue-300 transition-all duration-200 hover:shadow-lg flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@vite(['resources/js/admin/order-layanan/data-order-layanan.js'])
