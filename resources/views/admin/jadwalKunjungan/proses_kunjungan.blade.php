{{-- Proses Kunjungan Hari Ini --}}
<div id="prosesKunjungan" class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl shadow-lg border border-indigo-50">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-indigo-100 flex items-center justify-center">
                <i class="fa-solid fa-stethoscope text-indigo-600 text-xl"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-gray-800">
                    Proses Kunjungan Hari Ini
                </h2>
                <p class="text-xs md:text-sm text-gray-500 mt-1">
                    Daftar pasien yang sedang menunggu proses kunjungan pada hari ini.
                </p>
            </div>
        </div>
    </div>

    {{-- Wrapper tabel --}}
    <div class="relative border border-gray-100 rounded-xl overflow-visible">
        {{-- Header strip --}}
        <div
            class="bg-gradient-to-r from-indigo-50 via-sky-50 to-white px-4 py-2 border-b border-gray-100
                   flex items-center justify-between text-[11px] text-gray-500">
            <div class="flex items-center gap-2">
                <i class="fa-regular fa-clock text-indigo-500 text-xs"></i>
                <span>Hanya menampilkan kunjungan berstatus pending / hari ini</span>
            </div>
        </div>

        <div class="relative overflow-visible">
            <table class="min-w-full text-sm text-gray-700 align-middle" id="tabelProses">
                <thead class="bg-indigo-50 text-indigo-700 uppercase text-[11px]">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold">No Antrian</th>
                        <th class="px-5 py-3 text-center font-semibold">Nama Pasien</th>
                        <th class="px-5 py-3 text-center font-semibold">Dokter</th>
                        <th class="px-5 py-3 text-center font-semibold">Poli</th>
                        <th class="px-5 py-3 text-center font-semibold">Keluhan</th>
                        <th class="px-5 py-3 text-center font-semibold">Status Kunjungan</th>
                        <th class="px-5 py-3 text-center font-semibold w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody id="waitingBody" class="divide-y divide-gray-100">
                    <tr>
                        <td colspan="6" class="text-center text-gray-500 py-8 italic text-sm">
                            Memuat data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL: Edit Kunjungan (tanpa SweetAlert) --}}
<div id="editKunjunganModal" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 bg-black bg-opacity-40 backdrop-blur-sm">
    <div class="relative w-full max-w-3xl max-h-full">
        <div class="relative bg-white rounded-2xl shadow-xl border border-gray-100">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div>
                    <h3 class="text-base md:text-lg font-semibold text-gray-900">
                        Edit Kunjungan Pasien
                    </h3>
                    <p class="text-xs text-gray-500 mt-1">
                        Ubah dokter, poli, keluhan.
                    </p>
                </div>
                <button type="button"
                    class="close-edit-kunjungan text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-full
                               w-8 h-8 inline-flex items-center justify-center">
                    ✕
                </button>
            </div>

            {{-- Form --}}
            <form id="editKunjunganForm" class="px-5 py-5 space-y-4" method="POST" action="">
                @csrf
                {{-- Info dasar (No Antrian & Pasien) --}}
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-gray-700">
                            No Antrian
                        </label>
                        <input type="text" id="edit_no_antrian" readonly
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-gray-700">
                            Status Kunjungan
                        </label>
                        <input type="text" id="edit_status" readonly
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3 py-2.5 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-700">
                        Nama Pasien
                    </label>
                    <input type="text" id="edit_nama_pasien" readonly
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 rounded-lg px-3 py-2.5 text-sm">
                </div>


                {{-- Dokter & Poli (search seperti di perawat) --}}
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    {{-- Dokter (TomSelect) --}}
                    <div>
                        <label for="edit_dokter_select" class="block mb-1 text-xs font-semibold text-gray-700">
                            Dokter
                        </label>
                        <select id="edit_dokter_select" name="dokter_id"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg
                                       px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400"
                            placeholder="Cari & pilih dokter…">
                            {{-- TomSelect isi async --}}
                        </select>
                        <div id="edit_dokter_id-error" class="text-[11px] text-red-600 mt-1"></div>
                    </div>

                    {{-- Poli (muncul setelah dokter dipilih) --}}
                    <div id="group_poli_edit" class="hidden">
                        <label for="edit_poli_select" class="block mb-1 text-xs font-semibold text-gray-700">
                            Poli
                        </label>
                        <select id="edit_poli_select" name="poli_id"
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg
                                       px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400"
                            placeholder="Cari & pilih poli…">
                            {{-- TomSelect isi async sesuai dokter --}}
                        </select>
                        <div id="edit_poli_id-error" class="text-[11px] text-red-600 mt-1"></div>
                    </div>
                </div>

                {{-- Keluhan --}}
                <div>
                    <label class="block mb-1 text-xs font-semibold text-gray-700">
                        Keluhan Awal
                    </label>
                    <textarea id="edit_keluhan_awal" name="keluhan_awal" rows="3" required
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg px-3 py-2.5
                                     focus:outline-none focus:ring-2 focus:ring-indigo-200 focus:border-indigo-400"></textarea>
                </div>


                {{-- Global error box --}}
                <div id="edit_error_box"
                    class="hidden text-[11px] text-red-600 bg-red-50 border border-red-100 rounded-lg px-3 py-2"></div>

                {{-- Footer --}}
                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100 mt-2">
                    <button type="button"
                        class="close-edit-kunjungan px-4 py-2 text-xs md:text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2 text-xs md:text-sm bg-indigo-600 text-white rounded-lg font-medium
                                   hover:bg-indigo-700 shadow-sm hover:shadow focus:ring-2 focus:ring-indigo-400">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- SweetAlert2 untuk aksi mulai/batalkan (modal edit tidak pakai Swal) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@vite(['resources/js/admin/jadwalKunjungan/proses_kunjungan.js'])
