<div id="prosesKunjungan" class="bg-white p-6 rounded-xl shadow-lg">
    <div class="flex items-center space-x-3 mb-4">
        <i class="fa-solid fa-stethoscope text-indigo-600 text-2xl"></i>
        <h2 class="text-2xl font-bold text-gray-800">Proses Kunjungan Hari Ini</h2>
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
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody id="waitingBody">
                <tr>
                    <td colspan="5" class="text-center text-gray-500 py-6 italic">Memuat data...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@vite(['resources/js/admin/jadwalKunjungan/proses_kunjungan.js'])
