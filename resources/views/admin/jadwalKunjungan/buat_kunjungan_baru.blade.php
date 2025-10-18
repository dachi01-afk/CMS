<!-- Tabel Jadwal Dokter -->
<div class="bg-white p-6 rounded-xl shadow-lg mb-8">
    <!-- Header -->
    <div class="flex items-center space-x-3 mb-4">
        <i class="fa-solid fa-calendar-check text-indigo-600 text-2xl"></i>
        <h2 class="text-2xl font-bold text-gray-800">
            Jadwal Dokter Hari Ini <span class="text-primary-600">({{ $hariIni }})</span>
        </h2>
    </div>

    <!-- Table -->
    <div class="relative overflow-x-auto rounded-lg border border-gray-100 shadow-sm">
        <table class="min-w-full text-sm text-gray-700">
            <thead class="bg-indigo-50 text-indigo-700 text-xs uppercase tracking-wide">
                <tr>
                    <th class="px-6 py-3 font-semibold text-left">Dokter</th>
                    <th class="px-6 py-3 font-semibold text-left">Poli</th>
                    <th class="px-6 py-3 font-semibold text-left">Spesialis</th>
                    <th class="px-6 py-3 font-semibold text-center">Waktu</th>
                    <th class="px-6 py-3 font-semibold text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($jadwalHariIni as $jadwal)
                    <tr class="border-b border-gray-100 hover:bg-indigo-50/40 transition duration-150">
                        <!-- Kolom Dokter -->
                        <td class="px-6 py-4 font-medium text-gray-900 text-base">
                            {{ $jadwal->dokter->nama_dokter }}
                        </td>

                        <td class="px-6 py-4 font-medium text-gray-900 text-base">
                            {{ $jadwal->poli->nama_poli }}
                        </td>

                        <!-- Kolom Spesialis -->
                        <td class="px-6 py-4 font-medium text-gray-900 text-base">
                            {{ $jadwal->dokter->jenisSpesialis->nama_spesialis ?? '-' }}
                        </td>

                        <!-- Kolom Waktu -->
                        <td class="px-6 py-4 text-center">
                            <span
                                class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full font-medium text-sm whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($jadwal->jam_awal)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                            </span>
                        </td>

                        <!-- Kolom Aksi -->
                        <td class="px-6 py-4 text-center">
                            <button type="button"
                                class="pilih-jadwal-btn text-white bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-lg text-sm font-medium transition duration-150 shadow-sm"
                                data-dokter-id="{{ $jadwal->dokter->id }}"
                                data-dokter-nama="{{ $jadwal->dokter->nama_dokter }}"
                                data-poli-id="{{ $jadwal->poli->id }}" data-nama-poli="{{ $jadwal->poli->nama_poli }}"
                                data-spesialis="{{ $jadwal->dokter->jenisSpesialis->nama_spesialis ?? '-' }}"
                                data-tanggal-kunjungan="{{ $tanggalHariIni }}" data-jadwal-id="{{ $jadwal->id }}">
                                Pilih
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-gray-500 py-6 italic">
                            Tidak ada jadwal dokter untuk hari ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>



<!-- Modal Tambah Kunjungan -->
<div id="addKunjunganModal" tabindex="-1" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 bg-black bg-opacity-50 overflow-y-auto overflow-x-hidden">
    <div class="relative w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">

            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Tambah Kunjungan Pasien
                </h3>
                <button type="button" id="closeModalBtn"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 inline-flex justify-center items-center">
                    ✕
                </button>
            </div>

            <!-- Form -->
            <form action="{{ route('jadwal_kunjungan.create') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="tanggal_kunjungan" id="tanggal_kunjungan">

                <!-- Dokter -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Dokter</label>
                    <input type="text" id="dokter_nama" name="dokter_nama" readonly
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                    <input type="hidden" id="dokter_id" name="dokter_id">
                </div>

                <!-- Poli -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Poli</label>
                    <input type="text" id="nama_poli" readonly
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                    <input type="hidden" id="poli_id" name="poli_id">
                </div>

                <!-- Cari Pasien -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Cari Pasien</label>
                    <input type="text" id="search_pasien" name="search_pasien" placeholder="Ketik nama pasien..."
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                    <div id="search_results"
                        class="mt-2 bg-white border border-gray-200 rounded-lg shadow max-h-40 overflow-y-auto hidden">
                        <!-- hasil pencarian -->
                    </div>
                </div>

                <!-- Data Pasien -->
                <div id="pasien_data" class="hidden space-y-1 text-sm text-gray-700 dark:text-gray-300">
                    <input type="hidden" name="pasien_id" id="pasien_id">
                    <p><strong>Nama:</strong> <span id="nama_pasien"></span></p>
                    <p><strong>Alamat:</strong> <span id="alamat_pasien"></span></p>
                    <p><strong>Jenis Kelamin:</strong> <span id="jk_pasien"></span></p>
                </div>

                <!-- Tanggal -->
                {{-- <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal Kunjungan</label>
                    <input type="date" name="tanggal_kunjungan" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                </div> --}}

                <!-- Keluhan -->
                <div>
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Keluhan Awal</label>
                    <textarea name="keluhan_awal" rows="3" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"></textarea>
                </div>

                <!-- Footer -->
                <div class="flex justify-end space-x-2 border-t border-gray-200 dark:border-gray-600 pt-4">
                    <button type="button" id="closeModalBtn2"
                        class="px-4 py-2 text-sm bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-800 dark:text-white">
                        Close
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-400">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@vite(['resources/js/admin/jadwalKunjungan/jadwal_kunjungan.js'])
