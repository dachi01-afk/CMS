<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Pengambilan Resep Obat</h2>
</div>

<!-- Tabel Laboratorium -->
<div class="overflow-hidden rounded-lg shadow-md">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200">
        <div>
            <select id="lab_pageLength"
                class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-24 p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="relative">
            <input type="text" id="lab_searchInput"
                class="block w-60 p-2 pl-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Cari data...">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="pengambilanResepObat" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs uppercase bg-sky-500 text-white">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">Nama Dokter</th>
                    <th class="px-6 py-3">Nama Pasien</th>
                    <th class="px-6 py-3">Nomor Antrian</th>
                    <th class="px-6 py-3">Tanggal Kunjungan</th>
                    <th class="px-6 py-3">Nama Obat</th>
                    <th class="px-6 py-3">Jumlah Obat</th>
                    <th class="px-6 py-3">Keterangan</th>
                    <th class="px-6 py-3">Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-4 py-3 border-t border-gray-200 gap-3">
        <div id="lab_customInfo" class="text-sm text-gray-700 dark:text-gray-300"></div>
        <ul id="lab_customPagination" class="inline-flex -space-x-px text-sm"></ul>
    </div>
</div>

{{-- <!-- Modal Update Status Resep Obat -->
<div id="updateStatusModal" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 bg-black bg-opacity-50">
    <div class="relative w-full max-w-lg max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">

            <!-- Header -->
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Update Status Resep Obat</h3>
                <button type="button" id="buttonCloseModalUpdateStatus" class="text-gray-400 hover:text-gray-600">
                    ✕
                </button>
            </div>

            <!-- Form -->
            <form id="formUpdateStatus" class="p-5 flex flex-col gap-4" method="POST"
                action="{{ route('update.status.resep.obat') }}">
                @csrf
                <input type="hidden" name="resep_id" id="resep_id">
                <input type="hidden" name="obat_id" id="obat_id">

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Status Pengambilan Obat
                    </label>
                    <select name="status" id="status"
                        class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-full p-2">
                        <option value="Belum Diambil">Belum Diambil</option>
                        <option value="Sudah Diambil">Sudah Diambil</option>
                    </select>
                </div>

                <!-- Footer Buttons -->
                <div class="flex justify-end gap-3 mt-6 border-t border-gray-200 pt-4 dark:border-gray-600">
                    <button type="button" id="closeButtonModalUpdateStatus"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg 
                        hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500">
                        Close
                    </button>
                    <button type="submit" id="updateStatusButton"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-blue-700 rounded-lg 
                        hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 
                        dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> --}}


@vite(['resources/js/admin/pengambilanObat/pengambilan_obat.js'])
