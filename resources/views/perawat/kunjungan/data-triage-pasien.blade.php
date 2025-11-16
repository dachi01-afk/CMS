<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Daftar Siap Triage</h2>
</div>

<!-- Table Wrapper -->
<div class="overflow-hidden rounded-lg shadow-md">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200">
        <div>
            <select id="triage_pageLength"
                class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-24 p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="relative">
            <input type="text" id="triage_searchInput"
                class="block w-60 p-2 pl-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Cari Data">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="tabelTriage" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs uppercase bg-sky-500 text-white">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">No Antrian</th>
                    <th class="px-6 py-3">Nama Pasien</th>
                    <th class="px-6 py-3">Dokter</th>
                    <th class="px-6 py-3">Poli</th>
                    <th class="px-6 py-3">Keluhan</th>
                    <th class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-4 py-3 border-t border-gray-200 gap-3">
        <div id="triage_customInfo" class="text-sm text-gray-700"></div>
        <ul id="triage_customPagination" class="inline-flex -space-x-px text-sm"></ul>
    </div>
</div>

@vite(['resources/js/perawat/kunjungan/data-triage-pasien.js'])
