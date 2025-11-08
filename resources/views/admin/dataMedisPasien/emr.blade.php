<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Electronic Medical Record</h2>
</div>

<!-- Tabel -->
<div class="overflow-hidden rounded-lg shadow-md">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200">
        <div>
            <select id="emr-pageLength"
                class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-24 p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="relative">
            <input type="text" id="emr-searchInput"
                class="block w-60 p-2 pl-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Cari data...">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="emrTable" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs uppercase bg-sky-500 text-white">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">Nama Pasien</th>
                    <th class="px-6 py-3">Nama Dokter</th>
                    <th class="px-6 py-3">Tanggal Kunjungan</th>
                    <th class="px-6 py-3">Keluhan Awal</th>
                    <th class="px-6 py-3">Keluhan Utama</th>
                    <th class="px-6 py-3">Riwayat Penyakit Dahulu</th>
                    <th class="px-6 py-3">Riwayat Penyakit Keluarga</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-4 py-3 border-t border-gray-200 gap-3">
        <div id="emr-customInfo" class="text-sm text-gray-700 dark:text-gray-300"></div>
        <ul id="emr-customPagination" class="inline-flex -space-x-px text-sm"></ul>
    </div>
</div>


<!-- Modal Lihat Detail EMR -->
{{-- <div id="modalDetailEMR" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 bg-black bg-opacity-50">
    <div class="relative w-full max-w-2xl max-h-full">
        <div class="relative bg-white rounded-2xl shadow-xl dark:bg-gray-800 dark:text-white">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                    Detail Rekam Medis Pasien
                </h3>
                <button id="closeDetailEMR" class="text-gray-400 hover:text-gray-900 dark:hover:text-white">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-y-3">
                    <div class="font-medium text-gray-700 dark:text-gray-300">Nama Pasien:</div>
                    <div id="detail_nama_pasien" class="text-gray-900 dark:text-white">-</div>

                    <div class="font-medium text-gray-700 dark:text-gray-300">Nama Dokter:</div>
                    <div id="detail_nama_dokter" class="text-gray-900 dark:text-white">-</div>

                    <div class="font-medium text-gray-700 dark:text-gray-300">Tanggal Kunjungan:</div>
                    <div id="detail_tanggal_kunjungan" class="text-gray-900 dark:text-white">-</div>

                    <div class="font-medium text-gray-700 dark:text-gray-300">Keluhan Awal:</div>
                    <div id="detail_keluhan_awal" class="text-gray-900 dark:text-white">-</div>

                    <div class="font-medium text-gray-700 dark:text-gray-300">Keluhan Utama:</div>
                    <div id="detail_keluhan_utama" class="text-gray-900 dark:text-white">-</div>

                    <div class="font-medium text-gray-700 dark:text-gray-300">Riwayat Penyakit Dahulu:</div>
                    <div id="detail_riwayat_penyakit_dahulu" class="text-gray-900 dark:text-white">-</div>

                    <div class="font-medium text-gray-700 dark:text-gray-300">Riwayat Penyakit Keluarga:</div>
                    <div id="detail_riwayat_penyakit_keluarga" class="text-gray-900 dark:text-white">-</div>

                    <div class="font-medium text-gray-700 dark:text-gray-300">Tekanan Darah:</div>
                    <div id="tekanan_darah" class="text-gray-900 dark:text-white">-</div>

                    <div class="font-medium text-gray-700 dark:text-gray-300">Suhu Tubuh:</div>
                    <div id="suhu_tubuh" class="text-gray-900 dark:text-white">-</div>

                    <div class="font-medium text-gray-700 dark:text-gray-300">Detak Nadi:</div>
                    <div id="nadi" class="text-gray-900 dark:text-white">-</div>

                    <div class="font-medium text-gray-700 dark:text-gray-300">Pernapasan:</div>
                    <div id="pernapasan" class="text-gray-900 dark:text-white">-</div>

                    <div class="font-medium text-gray-700 dark:text-gray-300">Saturasi Oksigen:</div>
                    <div id="saturasi_oksigen" class="text-gray-900 dark:text-white">-</div>

                    <div class="font-medium text-gray-700 dark:text-gray-300">Diagnosis:</div>
                    <div id="diagnosis" class="text-gray-900 dark:text-white">-</div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                <button type="button" id="buttonCloseModalDetailEMR"
                    class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg 
                    hover:bg-gray-300 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div> --}}



@vite(['resources/js/admin/dataMedisPasien/rekam_medis_elektronik.js'])
