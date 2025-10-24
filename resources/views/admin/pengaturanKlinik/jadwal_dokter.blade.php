<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Jadwal Dokter</h2>

    <!-- Modal toggle -->
    <button id="btnAddJadwalDokter"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none">
        + Tambah Data
    </button>
</div>

<!-- Tabel -->
<div class="overflow-hidden rounded-lg shadow-md">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200">
        <div>
            <select id="jadwal_pageLength"
                class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-24 p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="relative">
            <input type="text" id="jadwal_searchInput"
                class="block w-60 p-2 pl-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Cari data...">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="jadwalTable" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs uppercase bg-sky-500 text-white">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">Nama Dokter</th>
                    <th class="px-6 py-3">Nama Poli</th>
                    <th class="px-6 py-3">Hari</th>
                    <th class="px-6 py-3">Jam Mulai</th>
                    <th class="px-6 py-3">Jam Selesai</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-4 py-3 border-t border-gray-200 gap-3">
        <div id="jadwal_customInfo" class="text-sm text-gray-700 dark:text-gray-300"></div>
        <ul id="jadwal_customPagination" class="inline-flex -space-x-px text-sm"></ul>
    </div>
</div>


<!-- Modal Add Jadwal Dokter -->
<div id="addJadwalModal" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 bg-black bg-opacity-50">
    <div class="relative w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Tambah Jadwal Dokter</h3>
            </div>

            <form id="formAddJadwalDokter" class="p-5 flex flex-col gap-4"
                data-url="{{ route('pengaturan_klinik.add_jadwal_dokter') }}" method="POST">
                @csrf

                <!-- Search Dokter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cari Dokter</label>
                    <input type="text" id="search_dokter_create" name="search_dokter_create"
                        placeholder="Ketik nama dokter..."
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <div id="search_results_create"
                        class="mt-2 bg-white border border-gray-200 rounded-lg shadow max-h-40 overflow-y-auto hidden">
                        <!-- Hasil pencarian akan muncul di sini -->
                    </div>
                </div>

                {{-- Data Dokter --}}
                <div id="dokter_data_create" class="hidden">
                    <input type="hidden" name="dokter_id" id="dokter_id_create">
                    <input type="hidden" name="poli_id" id="poli_id_create">
                    <p class="text-sm text-gray-600"><strong>Nama Dokter:</strong> <span id="nama_dokter_create"></span>
                    </p>
                    <p class="text-sm text-gray-600"><strong>Poli Dokter:</strong> <span id="nama_poli_create"></span>
                    </p>
                    </p>
                </div>

                {{-- Hari --}}
                <div>
                    <label for="hari" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Hari
                        Praktik</label>
                    <select id="hari" name="hari" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        <option value="" disabled selected>-</option>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                        <option value="Minggu">Minggu</option>
                    </select>
                    <div id="hari-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Jam Awal --}}
                <div>
                    <label for="jam_awal" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jam
                        Mulai</label>
                    <input type="time" id="jam_awal" name="jam_awal" required step="1"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                    <div id="jam_awal-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Jam Selesai --}}
                <div>
                    <label for="jam_selesai" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jam
                        Selesai</label>
                    <input type="time" id="jam_selesai" name="jam_selesai" required step="1"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                    <div id="jam_selesai-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 mt-5 border-t border-gray-200 pt-4 dark:border-gray-600">
                    <button type="button" id="closeAddJadwalModal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-white">
                        Close
                    </button>
                    <button type="submit" id="saveJadwalButton"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- edit jadwal --}}
<div id="editJadwalModal" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 bg-black bg-opacity-50">
    <div class="relative w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Jadwal Dokter</h3>
            </div>

            <form id="formEditJadwalDokter" class="p-5 flex flex-col gap-4"
                data-url="{{ route('pengaturan_klinik.update_jadwal_dokter', ['id' => 0]) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="jadwal_id_update" name="id">

                <!-- Search Dokter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cari Dokter</label>
                    <input type="text" id="search_dokter_update" name="search_dokter_update"
                        placeholder="Ketik nama dokter..."
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <div id="search_results_update"
                        class="mt-2 bg-white border border-gray-200 rounded-lg shadow max-h-40 overflow-y-auto hidden">
                        <!-- Hasil pencarian akan muncul di sini -->
                    </div>
                </div>

                {{-- Data Dokter --}}
                <div id="dokter_data_update" class="hidden">
                    <input type="hidden" name="dokter_id" id="dokter_id_update">
                    <input type="hidden" name="poli_id" id="poli_id_update">
                    <p class="text-sm text-gray-600"><strong>Nama Dokter:</strong> <span
                            id="nama_dokter_update"></span></p>
                    <p class="text-sm text-gray-600"><strong>Poli Dokter:</strong> <span id="nama_poli_update"></span>
                    </p>
                    {{-- <p class="text-sm text-gray-600"><strong>Jenis Kelamin:</strong> <span id="deskripsi_dokter"></span> --}}
                    </p>
                </div>

                {{-- Hari --}}
                <div>
                    <label for="hari_edit" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Hari
                        Praktik</label>
                    <select id="hari_edit" name="hari_edit" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5">
                        <option value="" disabled selected>-</option>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                        <option value="Minggu">Minggu</option>
                    </select>
                    <div id="hari_edit-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Jam --}}
                <div>
                    <label for="jam_awal_edit"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jam Mulai</label>
                    <input type="time" id="jam_awal_edit" name="jam_awal_edit" required step="1"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5">
                    <div id="jam_awal_edit-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <div>
                    <label for="jam_selesai_edit"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jam Selesai</label>
                    <input type="time" id="jam_selesai_edit" name="jam_selesai_edit" required step="1"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5">
                    <div id="jam_selesai_edit-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <div class="flex justify-end gap-3 mt-5 border-t border-gray-200 pt-4 dark:border-gray-600">
                    <button type="button" id="closeEditJadwalModal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-white">
                        Close
                    </button>
                    <button type="submit" id="updateJadwalButton"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



@vite(['resources/js/admin/pengaturanKlinik/jadwal_dokter.js'])
