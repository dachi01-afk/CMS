<div id="prosesKunjungan" class="bg-white p-6 rounded-xl shadow-lg">
    <div class="flex items-center space-x-3 mb-4">
        <i class="fa-solid fa-stethoscope text-indigo-600 text-2xl"></i>
        <h2 class="text-2xl font-bold text-gray-800">Daftar List Kunjungan Yang Akan Datang</h2>
    </div>

    <div class="relative overflow-x-auto border border-gray-100 rounded-lg">
        <table class="min-w-full text-sm text-gray-700" id="tabelProses">
            <thead class="bg-indigo-50 text-indigo-700 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">No Antrian</th>
                    <th class="px-6 py-3 text-left">Nama Pasien</th>
                    <th class="px-6 py-3 text-left">Dokter</th>
                    <th class="px-6 py-3 text-left">Poli</th>
                    <th class="px-6 py-3 text-left">Keluhan</th>
                    <th class="px-6 py-3 text-left">Tanggal Kunjungan</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="waitingBodyMasaDepan">
                <tr>
                    <td colspan="5" class="text-center text-gray-500 py-6 italic">Memuat data...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Detail KYAD -->
<div id="modalDetailKYAD" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-lg shadow-xl">
        <h2 class="text-xl font-semibold mb-4">Detail Kunjungan</h2>

        <div id="detailKYADContent" class="space-y-2 text-gray-700">
            <!-- detailnya muncul di sini -->
        </div>

        <div class="text-right mt-4">
            <button id="closeModalKYAD" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                Tutup
            </button>
        </div>
    </div>
</div>

@vite(['resources/js/admin/jadwalKunjungan/kunjungan-masa-depan.js'])
