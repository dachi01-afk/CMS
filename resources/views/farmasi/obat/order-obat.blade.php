<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Order Obat</h2>

    <!-- Modal toggle -->
    <button id="btn-open-modal-penjualan-obat"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none">
        + Tambah Data
    </button>
</div>

<!-- Tabel -->
<div class="overflow-hidden rounded-lg shadow-md">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200">
        <div>
            <select id="penjualan-obat-page-length"
                class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-24 p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="relative">
            <input type="text" id="penjualan-obat-search-input"
                class="block w-60 p-2 pl-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Cari data...">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="penjualanObatTable" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs uppercase bg-sky-500 text-white">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">Nama Pasien</th>
                    <th class="px-6 py-3">Nama Obat</th>
                    <th class="px-6 py-3">Kode Transaksi</th>
                    <th class="px-6 py-3">Jumlah Obat</th>
                    <th class="px-6 py-3">Sub Total</th>
                    <th class="px-6 py-3">Tanggal Transaksi</th>
                    {{-- <th class="px-6 py-3 text-center">Aksi</th> --}}
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-4 py-3 border-t border-gray-200 gap-3">
        <div id="penjualan-obat-custom-info" class="text-sm text-gray-700 dark:text-gray-300"></div>
        <ul id="penjualan-obat-custom-paginate" class="inline-flex -space-x-px text-sm"></ul>
    </div>
</div>


<div id="modalJualObat" tabindex="-1" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 bg-black bg-opacity-50 overflow-y-auto overflow-x-hidden">
    <div class="relative w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">

            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Form Penjualan Obat
                </h3>
                <button type="button" id="closeModalBtn"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center">
                    ✕
                </button>
            </div>

            <!-- Form -->
            <form method="POST" class="px-6 py-2 space-y-4"
                id="form-penjualan-obat">
                @csrf
                <input type="hidden" name="resep_id" id="resep_id">
                <input type="hidden" name="tanggal_kunjungan" id="tanggal_kunjungan">

                <!-- Cari Obat -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Cari Obat</label>
                    <input type="text" id="search_obat" placeholder="Ketik nama obat..."
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">

                    <div id="obat_results"
                        class="mt-2 bg-white border border-gray-200 rounded-lg shadow max-h-40 overflow-y-auto hidden">
                        <!-- hasil pencarian obat -->
                    </div>
                </div>

                <!-- Tabel Daftar Obat yang Dipilih -->
                <div class="mt-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Daftar Obat</label>
                    <table
                        class="w-full text-sm text-left text-gray-700 dark:text-gray-300 border border-gray-300 rounded-lg">
                        <thead class="bg-gray-100 dark:bg-gray-600">
                            <tr>
                                <th class="px-3 py-2">Nama Obat</th>
                                <th class="px-3 py-2">Stok</th>
                                <th class="px-3 py-2">Jumlah</th>
                                <th class="px-3 py-2">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="selected_obat_list">
                            <!-- Obat yang ditambahkan akan muncul di sini -->
                        </tbody>
                    </table>
                </div>


                <!-- Cari Pasien -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Cari Pasien</label>
                    <input type="text" id="search_pasien" name="search_pasien" placeholder="Ketik nama pasien..."
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                    <div id="search_results"
                        class="mt-2 bg-white border border-gray-200 rounded-lg shadow max-h-40 overflow-y-auto hidden">
                        <!-- hasil pencarian -->
                    </div>
                </div>

                <!-- Data Pasien -->
                <div id="pasien_data" class="hidden space-y-1 text-sm text-gray-700 dark:text-gray-300">
                    <input type="hidden" name="pasien_id" id="pasien_id">
                    <p><strong>Nama:</strong> <span id="nama_pasien"></span></p>
                    <p><strong>Alamat:</strong> <span id="alamat_pasien"></span></p>
                    <p><strong>Jenis Kelamin:</strong> <span id="jk_pasien"></span></p>
                </div>

                <!-- Footer -->
                <div class="flex justify-end space-x-2 border-t border-gray-200 dark:border-gray-600 py-4">
                    <button type="button" id="btn-close-modal-penjualan-obat"
                        class="px-4 py-2 text-sm bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-white">
                        Close
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-400">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Obat -->
{{-- <div id="editObatModal" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 bg-black bg-opacity-50">
    <div class="relative w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">

            <!-- Header -->
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Data Obat</h3>
            </div>

            <!-- Form -->
            <form id="formEditObat" class="p-5 flex flex-col gap-4"
                data-url="{{ route('pengaturan_klinik.update_obat', ['id' => 0]) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="obat_id" id="obat_id_edit">

                <!-- Nama Obat -->
                <div>
                    <label for="nama_obat_edit"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama
                        Obat</label>
                    <input type="text" name="nama_obat_edit" id="nama_obat_edit"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Nama Obat" required>
                    <div id="nama_obat_edit-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Jumlah -->
                <div>
                    <label for="jumlah_edit"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jumlah</label>
                    <input type="number" name="jumlah_edit" id="jumlah_edit"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Jumlah Obat" required>
                    <div id="jumlah_edit-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Dosis -->
                <div>
                    <label for="dosis_edit" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Dosis
                        (mg/ml)</label>
                    <input type="number" step="0.01" name="dosis_edit" id="dosis_edit"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Masukkan Dosis" required>
                    <div id="dosis_edit-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Harga -->
                <div>
                    <label for="total_harga_edit"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Harga</label>
                    <div class="relative mt-1">
                        <span
                            class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-300">Rp</span>
                        <input type="text" name="total_harga_edit" id="total_harga_edit"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white pl-10"
                            placeholder="Masukkan Harga" required>
                        <div id="total_harga_edit-error" class="text-red-600 text-sm mt-1"></div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-3 mt-5 border-t border-gray-200 pt-4 dark:border-gray-600">
                    <button type="button" id="closeEditObatModal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500">
                        Close
                    </button>
                    <button type="submit" id="updateObatButton"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> --}}



@vite(['resources/js/farmasi/obat/order-obat.js'])
