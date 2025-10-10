<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Laporan Kunjungan</h2>
    {{-- <button id="btnAddDokter"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none">
        + Tambah Data
    </button> --}}
</div>

<!-- Tabel Kunjungan -->
<div class="overflow-hidden rounded-lg shadow-md">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200">
        <div>
            <select id="kunjungan_pageLength"
                class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-24 p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="relative">
            <input type="text" id="kunjungan_searchInput"
                class="block w-60 p-2 pl-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Cari data...">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="kunjunganTable" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs uppercase bg-sky-500 text-white">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">No. Antrian</th>
                    <th class="px-6 py-3">Nama Dokter</th>
                    <th class="px-6 py-3">Nama Pasien</th>
                    <th class="px-6 py-3">Tanggal Kunjungan</th>
                    <th class="px-6 py-3">Keluhan Awal</th>
                    <th class="px-6 py-3">Status</th>
                    {{-- <th class="px-6 py-3 text-center">Aksi</th> --}}
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-4 py-3 border-t border-gray-200 gap-3">
        <div id="kunjungan_customInfo" class="text-sm text-gray-700 dark:text-gray-300"></div>
        <ul id="kunjungan_customPagination" class="inline-flex -space-x-px text-sm"></ul>
    </div>
</div>


@vite(['resources/js/admin/laporan/laporan_kunjungan.js'])
