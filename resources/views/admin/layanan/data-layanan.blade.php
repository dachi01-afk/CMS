<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Detail Layanan</h2>

    <!-- Modal toggle -->
    <button id="buttonModalCreateLayanan"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none">
        + Tambah Data
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
                    <th class="px-6 py-3">Nama Poli</th>
                    <th class="px-6 py-3">Nama Layanan</th>
                    <th class="px-6 py-3">Tarif Layanan</th>
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


<!-- Modal Create Layanan -->
<div id="modalCreateLayanan" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 bg-black bg-opacity-50">
    <div class="relative w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Tambah Data Layanan</h3>
            </div>

            <form id="formCreateLayanan" class="p-5 flex flex-col gap-4" data-url="{{ route('layanan.create.data') }}"
                method="POST">
                @csrf

                {{-- Nama Poli --}}
                <div>
                    <label for="nama_poli"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Poli</label>
                    <select id="poli_id_create" name="poli_id" required
                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                            focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        <option value="" disabled selected>Pilih Poli</option>
                        @foreach ($dataPoli as $poli)
                            <option value="{{ $poli->id }}">{{ $poli->nama_poli }}</option>
                        @endforeach
                    </select>
                    <div id="nama_poli-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Nama Layanan --}}
                <div>
                    <label for="nama_layanan" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama
                        Layanan</label>
                    <input type="text" name="nama_layanan" id="nama_layanan_create"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5
                            focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Nama Layanan" required>
                    <div id="nama_layanan-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Harga -->
                <div>
                    <label for="harga_layanan"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Harga Layanan</label>
                    <div class="relative mt-1">
                        <span
                            class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-300">Rp</span>
                        <input type="text" name="harga_layanan" id="harga_layanan_create"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white pl-10"
                            placeholder="Masukkan Harga" required>
                        <div id="harga_layanan-error" class="text-red-600 text-sm mt-1"></div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 mt-5 border-t border-gray-200 pt-4 dark:border-gray-600">
                    <button type="button" id="buttonCloseModalCreateLayanan"
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
<div id="modalUpdateLayanan" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 bg-black bg-opacity-50">
    <div class="relative w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Data Layanan</h3>
            </div>

            <form id="formUpdateLayanan" class="p-5 flex flex-col gap-4" data-url="{{ route('layanan.update.data') }}"
                method="POST">
                @csrf
                <input type="hidden" id="id_update" name="id">

                {{-- Nama Poli --}}
                <div>
                    <label for="nama_poli"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Poli</label>
                    <select id="poli_id_update" name="poli_id" required
                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg
                            focus:ring-blue-500 focus:border-blue-500 p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        <option value="" disabled selected>Pilih Poli</option>
                        @foreach ($dataPoli as $poli)
                            <option value="{{ $poli->id }}">{{ $poli->nama_poli }}</option>
                        @endforeach
                    </select>
                    <div id="nama_poli-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Nama Layanan --}}
                <div>
                    <label for="nama_layanan"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama
                        Layanan</label>
                    <input type="text" name="nama_layanan" id="nama_layanan_update"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5
                            focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Nama Layanan" required>
                    <div id="nama_layanan-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Harga -->
                <div>
                    <label for="harga_layanan"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Harga Layanan</label>
                    <div class="relative mt-1">
                        <span
                            class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-300">Rp</span>
                        <input type="text" name="harga_layanan" id="harga_layanan_update"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white pl-10"
                            placeholder="Masukkan Harga" required>
                        <div id="harga_layanan-error" class="text-red-600 text-sm mt-1"></div>
                    </div>
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

@vite(['resources/js/admin/layanan/data-layanan.js'])
