<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Data Poli</h2>

    <!-- Modal toggle -->
    <button id="buttonModalCreatePoli"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none">
        + Tambah Data
    </button>
</div>

<!-- Tabel -->
<div class="overflow-hidden rounded-lg shadow-md">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200">
        <div>
            <select id="poli-pageLength"
                class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-24 p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="relative">
            <input type="text" id="poli-searchInput"
                class="block w-60 p-2 pl-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Cari data...">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="poliTable" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs uppercase bg-sky-500 text-white">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">Nama Poli</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-4 py-3 border-t border-gray-200 gap-3">
        <div id="poli-customInfo" class="text-sm text-gray-700 dark:text-gray-300"></div>
        <ul id="poli-customPagination" class="inline-flex -space-x-px text-sm"></ul>
    </div>
</div>


<!-- Modal Add Jadwal Dokter -->
<div id="modalCreatePoli" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4">
    <div class="relative w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Tambah Jadwal Dokter</h3>
            </div>

            <form id="formCreatePoli" class="p-5 flex flex-col gap-4" data-url="{{ route('poli.create.data') }}"
                method="POST">
                @csrf

                {{-- Nama Poli --}}
                <div>
                    <label for="nama_poli" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama
                        Poli</label>
                    <input type="text" name="nama_poli" id="nama_poli_create"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5
                            focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Nama Spesialis" required>
                    <div id="nama_poli-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 mt-5 border-t border-gray-200 pt-4 dark:border-gray-600">
                    <button type="button" id="buttonCloseModalCreatePoli"
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
<div id="modalUpdatePoli" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4">
    <div class="relative w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Jadwal Dokter</h3>
            </div>

            <form id="formUpdatePoli" class="p-5 flex flex-col gap-4" data-url="{{ route('poli.update.data') }}"
                method="POST">
                @csrf
                <input type="hidden" id="id_update" name="id">

                {{-- Nama Poli --}}
                <div>
                    <label for="nama_poli" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama
                        Poli</label>
                    <input type="text" name="nama_poli" id="nama_poli_update"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5
                            focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Nama Spesialis" required>
                    <div id="nama_poli-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <div class="flex justify-end gap-3 mt-5 border-t border-gray-200 pt-4 dark:border-gray-600">
                    <button type="button" id="buttonCloseModalUpdatePoli"
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

@vite(['resources/js/admin/poli/data-poli.js'])
