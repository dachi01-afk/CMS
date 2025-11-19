<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Data Kategori Layanan</h2>

    <!-- Modal toggle -->
    <button id="buttonModalCreateLayanan"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none">
        + Tambah Data Kategori Layanan
    </button>
</div>

<!-- Tabel -->
<div class="overflow-hidden rounded-lg shadow-md">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200">
        <div>
            <select id="layanan-pageLength"
                class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-24 p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="relative">
            <input type="text" id="layanan-searchInput"
                class="block w-60 p-2 pl-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Cari data...">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="layananTable" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs uppercase bg-sky-500 text-white">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">Nama Kategori Layanan</th>
                    <th class="px-6 py-3">Deskripsi Kategori Layanan</th>
                    <th class="px-6 py-3">Status Kategori Layanan</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-4 py-3 border-t border-gray-200 gap-3">
        <div id="layanan-customInfo" class="text-sm text-gray-700 dark:text-gray-300"></div>
        <ul id="layanan-customPagination" class="inline-flex -space-x-px text-sm"></ul>
    </div>
</div>


<!-- Modal Create Kategori Layanan -->
<div id="modalCreateKategoriLayanan" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">

    <div class="w-full max-w-2xl">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">

            <!-- HEADER -->
            <div class="px-6 py-4 bg-gradient-to-r from-sky-600 to-teal-600">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-layer-group"></i>
                    Tambah Data Kategori Layanan
                </h3>
            </div>

            <!-- FORM -->
            <form id="formCreateKategoriLayanan" data-url="{{ route('kategori.layanan.create.data.kategori.layanan') }}"
                method="POST" class="px-6 py-5 space-y-5">
                @csrf

                <!-- Nama Kategori -->
                <div>
                    <label for="nama_kategori" class="block mb-1.5 font-medium text-gray-700 dark:text-gray-200">
                        Nama Kategori
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-tag absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="nama_kategori_create" name="nama_kategori"
                            class="w-full pl-10 p-3 rounded-lg border border-gray-300 bg-gray-50 text-gray-800
                                   focus:ring-teal-500 focus:border-teal-500"
                            placeholder="Masukkan nama kategori layanan">
                    </div>
                    <div id="nama_kategori-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Deskripsi Kategori -->
                <div>
                    <label for="deskripsi_kategori" class="block mb-1.5 font-medium text-gray-700 dark:text-gray-200">
                        Deskripsi Kategori
                    </label>
                    <textarea name="deskripsi_kategori" id="deskripsi_kategori_create" rows="3"
                        class="w-full p-3 rounded-lg border border-gray-300 bg-gray-50 text-gray-800
                               focus:ring-teal-500 focus:border-teal-500"
                        placeholder="Tuliskan deskripsi kategori..."></textarea>
                    <div id="deskripsi_kategori-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Status Kategori -->
                <div class="relative">
                    <label class="block mb-2 text-sm font-semibold text-gray-800 dark:text-white flex items-center">
                        <i class="fa-solid fa-toggle-on mr-2 text-green-500"></i> Status Kategori
                    </label>
                    <div class="flex gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="status_kategori" value="Aktif"
                                class="mr-2 text-green-500 focus:ring-green-500" checked>
                            <span class="text-sm text-gray-700 dark:text-white">Aktif</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="status_kategori" value="Tidak Aktif"
                                class="mr-2 text-red-500 focus:ring-red-500">
                            <span class="text-sm text-gray-700 dark:text-white">Tidak Aktif</span>
                        </label>
                    </div>
                    <div id="status_kategori-error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>

            </form>

            <!-- FOOTER BUTTON -->
            <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200">
                <button id="buttonCloseModalCreateLayanan"
                    class="px-6 py-2.5 rounded-lg bg-gray-200 text-gray-800 font-medium hover:bg-gray-300">
                    Cancel
                </button>

                <button type="submit" form="formCreateKategoriLayanan"
                    class="px-6 py-2.5 rounded-lg bg-teal-600 text-white font-medium hover:bg-teal-700 shadow">
                    Simpan
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Modal Update Kategori Layanan -->
<div id="modalUpdateKategoriLayanan" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">

    <div class="w-full max-w-2xl">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">

            <!-- HEADER (SAMA DENGAN CREATE) -->
            <div class="px-6 py-4 bg-gradient-to-r from-sky-600 to-teal-600">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit Data Kategori Layanan
                </h3>
            </div>

            <!-- FORM -->
            <form id="formUpdateKategoriLayanan" data-url="{{ route('kategori.layanan.update.data.kategori.layanan') }}"
                method="POST" class="px-6 py-5 space-y-5">
                @csrf
                <input type="hidden" id="id_update" name="id">

                <!-- Nama Kategori -->
                <div>
                    <label class="block mb-1.5 font-medium text-gray-700 dark:text-gray-200">
                        Nama Kategori
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-tag absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" id="nama_kategori_update" name="nama_kategori"
                            class="w-full pl-10 p-3 rounded-lg border border-gray-300 bg-gray-50 text-gray-800
                                   focus:ring-teal-500 focus:border-teal-500"
                            placeholder="Masukkan nama kategori layanan">
                    </div>
                    <div id="nama_kategori-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Deskripsi Kategori -->
                <div>
                    <label class="block mb-1.5 font-medium text-gray-700 dark:text-gray-200">
                        Deskripsi Kategori
                    </label>
                    <textarea id="deskripsi_kategori_update" name="deskripsi_kategori" rows="3"
                        class="w-full p-3 rounded-lg border border-gray-300 bg-gray-50 text-gray-800
                               focus:ring-teal-500 focus:border-teal-500"
                        placeholder="Tuliskan deskripsi kategori..."></textarea>
                    <div id="deskripsi_kategori-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Status Kategori -->
                <div class="relative">
                    <label class="block mb-2 text-sm font-semibold text-gray-800 dark:text-white flex items-center">
                        <i class="fa-solid fa-toggle-on mr-2 text-green-500"></i> Status Kategori
                    </label>
                    <div class="flex gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="status_kategori" value="Aktif"
                                class="mr-2 text-green-500 focus:ring-green-500">
                            <span class="text-sm text-gray-700 dark:text-white">Aktif</span>
                        </label>

                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="status_kategori" value="Tidak Aktif"
                                class="mr-2 text-red-500 focus:ring-red-500">
                            <span class="text-sm text-gray-700 dark:text-white">Tidak Aktif</span>
                        </label>
                    </div>
                    <div id="status_kategori-error" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>

            </form>

            <!-- FOOTER -->
            <div class="flex justify-end gap-3 px-6 py-4 bg-gray-50 dark:bg-gray-700 border-t border-gray-200">

                <button id="buttonCloseModalUpdateLayanan"
                    class="px-6 py-2.5 rounded-lg bg-gray-200 text-gray-800 font-medium hover:bg-gray-300">
                    Cancel
                </button>

                <button type="submit" form="formUpdateKategoriLayanan"
                    class="px-6 py-2.5 rounded-lg bg-teal-600 text-white font-medium hover:bg-teal-700 shadow">
                    Update
                </button>

            </div>

        </div>
    </div>
</div>


@vite(['resources/js/admin/kategoriLayanan/data-kategori-layanan.js'])
