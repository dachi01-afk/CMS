<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Data Obat</h2>

    <!-- Modal toggle -->
    <button id="btn-open-modal-create-obat"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none">
        + Tambah Data
    </button>
</div>

<!-- Tabel -->
<div class="overflow-hidden rounded-lg shadow-md">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200">
        <div>
            <select id="data-obat-page-length"
                class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-24 p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="relative">
            <input type="text" id="data-obat-search-input"
                class="block w-60 p-2 pl-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Cari data...">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="dataObatTable" class="w-full text-sm text-left text-gray-600">
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
        <div id="data-obat-custom-info" class="text-sm text-gray-700 dark:text-gray-300"></div>
        <ul id="data-obat-custom-paginate" class="inline-flex -space-x-px text-sm"></ul>
    </div>
</div>

<!-- Modal Create Obat -->
<div id="modalCreateObat" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 bg-black bg-opacity-50">
    <div class="relative w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">

            <!-- Header -->
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Tambah Data Obat</h3>
            </div>

            <!-- Form -->
            <form id="formModalCreate" class="p-5 flex flex-col gap-4" data-url="{{ route('obat.create') }}"
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
                        <input type="text" name="total_harga" id="total_harga"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white pl-10"
                            placeholder="Masukkan Harga" required>
                        <div id="total_harga-error" class="text-red-600 text-sm mt-1"></div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-3 mt-5 border-t border-gray-200 pt-4 dark:border-gray-600">
                    <button type="button" id="btn-close-modal-create-obat"
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

<!-- Modal Update Obat -->
<div id="modalUpdateObat" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 bg-black bg-opacity-50">
    <div class="relative w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">

            <!-- Header -->
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Data Obat</h3>
            </div>

            <!-- Form -->
            <form id="formModalUpdate" class="p-5 flex flex-col gap-4"
                data-url="{{ route('obat.update', ['id' => 0]) }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="id_update">

                <!-- Nama Obat -->
                <div>
                    <label for="nama-obat-update"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama
                        Obat</label>
                    <input type="text" name="nama_obat" id="nama-obat-update" readonly
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Nama Obat" required>
                    <div id="nama-obat-update-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Jumlah -->
                <div>
                    <label for="jumlah-update"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jumlah</label>
                    <input type="number" name="jumlah" id="jumlah-update"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Jumlah Obat" required>
                    <div id="jumlah-update-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Dosis -->
                <div>
                    <label for="dosis_edit" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Dosis
                        (mg/ml)</label>
                    <input type="number" step="0.01" name="dosis" id="dosis-update"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Masukkan Dosis" required>
                    <div id="dosis_update-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Harga -->
                <div>
                    <label for="total-harga-update"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Harga</label>
                    <div class="relative mt-1">
                        <span
                            class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-300">Rp</span>
                        <input type="text" name="total_harga" id="total-harga-update"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white pl-10"
                            placeholder="Masukkan Harga" required>
                        <div id="total-harga-update-error" class="text-red-600 text-sm mt-1"></div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end gap-3 mt-5 border-t border-gray-200 pt-4 dark:border-gray-600">
                    <button type="button" id="btn-close-modal-update-obat"
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



@vite(['resources/js/farmasi/obat/data-obat.js'])
