<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Daftar Obat </h2>

    <!-- Modal toggle -->
    <button id="btnAddObat"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none">
        + Tambah Data
    </button>
</div>

<!-- Tabel -->
<div class="overflow-hidden rounded-lg shadow-md">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200">
        <div>
            <select id="obat_pageLength"
                class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-24 p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="relative">
            <input type="text" id="obat_searchInput"
                class="block w-60 p-2 pl-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Cari data...">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="obatTable" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs uppercase bg-sky-500 text-white">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">Nama Obat</th>
                    <th class="px-6 py-3">Jumlah</th>
                    <th class="px-6 py-3">Dosis</th>
                    <th class="px-6 py-3">Harga</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-4 py-3 border-t border-gray-200 gap-3">
        <div id="obat_customInfo" class="text-sm text-gray-700 dark:text-gray-300"></div>
        <ul id="obat_customPagination" class="inline-flex -space-x-px text-sm"></ul>
    </div>
</div>

<!-- Modal Add Obat -->
<div id="addObatModal" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 bg-black bg-opacity-50">
    <div class="relative w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">

            <!-- Header -->
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Tambah Data Obat</h3>
            </div>

            <!-- Form -->
            <form id="formAddObat" class="p-5 flex flex-col gap-4" data-url="{{ route('pengaturan_klinik.add_obat') }}"
                method="POST">
                @csrf

                <!-- Nama Obat -->
                <div>
                    <label for="nama_obat" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama
                        Obat</label>
                    <input type="text" name="nama_obat" id="nama_obat"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Nama Obat" required>
                    <div id="nama_obat-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Jumlah -->
                <div>
                    <label for="jumlah"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jumlah</label>
                    <input type="number" name="jumlah" id="jumlah"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Jumlah Obat" required>
                    <div id="jumlah-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Dosis -->
                <div>
                    <label for="dosis" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Dosis
                        (mg/ml)</label>
                    <input type="number" step="0.01" name="dosis" id="dosis"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Masukkan Dosis" required>
                    <div id="dosis-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Harga -->
                <div>
                    <label for="total_harga"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Harga</label>
                    <div class="relative mt-1">
                        <span
                            class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-300">Rp</span>
                        <input type="number" step="0.01" name="total_harga" id="total_harga"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white pl-10"
                            placeholder="Masukkan Harga" required>
                        <div id="total_harga-error" class="text-red-600 text-sm mt-1"></div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-3 mt-5 border-t border-gray-200 pt-4 dark:border-gray-600">
                    <button type="button" id="closeAddObatModal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500">
                        Close
                    </button>
                    <button type="submit" id="saveObatButton"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Obat -->
<div id="editObatModal" aria-hidden="true"
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
                        <input type="number" step="0.01" name="total_harga_edit" id="total_harga_edit"
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
</div>



@vite(['resources/js/admin/pengaturanKlinik/daftar_obat.js'])
