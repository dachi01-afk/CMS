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
                                data-poli-id="{{ $jadwal->poli->id }}"
                                data-nama-poli="{{ $jadwal->poli->nama_poli }}"
                                data-spesialis="{{ $jadwal->dokter->jenisSpesialis->nama_spesialis ?? '-' }}"
                                data-jadwal-id="{{ $jadwal->id }}">
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
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-2xl max-h-full">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    Tambah Kunjungan Pasien
                </h3>
                <button type="button" id="closeModalBtn"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                    ✕
                </button>
            </div>

            <!-- Form -->
            <form action="{{ route('jadwal_kunjungan.create') }}" method="POST" class="p-6 space-y-4">
                @csrf

                <!-- Info Dokter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Dokter</label>
                    <input type="text" id="dokter_nama" name="dokter_nama" readonly
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <input type="hidden" id="dokter_id" name="dokter_id">
                </div>

                {{-- Info Poli --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700">Poli</label>
                    <input type="text" id="nama_poli" readonly 
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <input type="hidden" id="poli_id" name="poli_id">
                </div>

                <!-- Search Pasien -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Cari Pasien</label>
                    <input type="text" id="search_pasien" name="search_pasien" placeholder="Ketik nama pasien..."
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <div id="search_results"
                        class="mt-2 bg-white border border-gray-200 rounded-lg shadow max-h-40 overflow-y-auto hidden">
                        <!-- Hasil pencarian akan muncul di sini -->
                    </div>
                </div>

                <!-- Data Pasien -->
                <div id="pasien_data" class="hidden">
                    <input type="hidden" name="pasien_id" id="pasien_id">
                    <p class="text-sm text-gray-600"><strong>Nama:</strong> <span id="nama_pasien"></span></p>
                    <p class="text-sm text-gray-600"><strong>Alamat:</strong> <span id="alamat_pasien"></span></p>
                    <p class="text-sm text-gray-600"><strong>Jenis Kelamin:</strong> <span id="jk_pasien"></span>
                    </p>
                </div>

                <!-- Tanggal Kunjungan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Kunjungan</label>
                    <input type="date" name="tanggal_kunjungan" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <!-- Keluhan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Keluhan Awal</label>
                    <textarea name="keluhan_awal" rows="3" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                </div>

                <!-- Footer -->
                <div class="flex justify-end space-x-2 border-t pt-4">
                    <button type="button" class="px-4 py-2 text-sm bg-gray-200 rounded-lg hover:bg-gray-300">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@vite(['resources/js/admin/jadwalKunjungan/jadwal_kunjungan.js'])
