<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Jenis Tes Lab</h2>
    <button id="btnAdd"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none">
        + Tambah Data
    </button>
</div>

<!-- Tabel -->
<div class="relative overflow-x-auto shadow-md sm:rounded-lg">
    <table id="pasienTable" class="w-full text-sm text-left text-gray-600">
        <thead class="text-xs uppercase bg-sky-500 text-white">
            <tr>
                <th class="px-6 py-3">No</th>
                <th class="px-6 py-3">Jenis Kunjungan</th>
                <th class="px-6 py-3">Jenis Tes</th>
                <th class="px-6 py-3">Hasil Tes</th>
                <th class="px-6 py-3">Tanggal Tes</th>
                <th class="px-6 py-3 text-center">Aksi</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
