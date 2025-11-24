<section class="space-y-5">

    {{-- HEADER + CTA --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-calendar-check text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold tracking-[0.18em] text-slate-400 uppercase">
                    Pengaturan Klinik
                </p>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800">
                    Jadwal Dokter
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    Atur hari dan jam praktik dokter untuk setiap poli agar alur pendaftaran pasien lebih terstruktur.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-3">
            <button type="button"
                class="hidden md:inline-flex items-center gap-2 px-3 py-2 text-xs md:text-sm rounded-xl border border-slate-200 text-slate-600 bg-white hover:bg-slate-50">
                <i class="fa-regular fa-circle-question text-sm"></i>
                <span>Panduan Jadwal Dokter</span>
            </button>

            <button id="btnAddJadwalDokter" type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-xl shadow-md
                       bg-gradient-to-r from-sky-500 to-teal-500 hover:from-sky-600 hover:from-sky-600 hover:to-teal-700
                       focus:outline-none focus:ring-2 focus:ring-sky-400">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Jadwal</span>
            </button>
        </div>
    </div>

    {{-- CARD TABEL --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">

        {{-- Toolbar atas --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-b border-slate-200">

            {{-- Page length --}}
            <div class="flex items-center gap-2 text-sm">
                <span class="text-slate-600 hidden sm:inline">Tampil</span>
                <select id="jadwal_pageLength"
                    class="border border-slate-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500
                           bg-white text-slate-800 px-2 py-1 w-28">
                    <option value="10">10 baris</option>
                    <option value="25">25 baris</option>
                    <option value="50">50 baris</option>
                    <option value="100">100 baris</option>
                </select>
                <span class="text-slate-600 hidden sm:inline">per halaman</span>
            </div>

            {{-- Search --}}
            <div class="w-full md:w-auto">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                    </span>
                    <input type="text" id="jadwal_searchInput"
                        class="block w-full md:w-72 pl-9 pr-3 py-2 text-sm text-slate-800
                               border border-slate-300 rounded-lg bg-slate-50
                               focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari dokter, poli, hari, atau jam...">
                </div>
                <p class="mt-1 text-[11px] text-slate-400">
                    Contoh: <span class="italic">dr. Andi, Poli Umum, Senin...</span>
                </p>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table id="jadwalTable" class="w-full text-sm text-left text-slate-700 border-t border-slate-100">
                <thead
                    class="text-xs font-semibold uppercase bg-gradient-to-r from-sky-500 to-teal-500 text-white tracking-wide">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Nama Dokter</th>
                        <th class="px-6 py-3">Nama Poli</th>
                        <th class="px-6 py-3">Hari</th>
                        <th class="px-6 py-3">Jam Mulai</th>
                        <th class="px-6 py-3">Jam Selesai</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100"></tbody>
            </table>
        </div>

        {{-- Footer tabel --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between px-4 md:px-6 py-3 border-t border-slate-200 bg-slate-50/70 gap-3 rounded-b-2xl">
            <div id="jadwal_customInfo" class="text-xs md:text-sm text-slate-600"></div>
            <ul id="jadwal_customPagination"
                class="inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 overflow-hidden">
            </ul>
        </div>
    </div>
</section>

{{-- Modal Add Jadwal Dokter --}}
<div id="addJadwalModal" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center w-full h-full p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="relative w-full max-w-xl">
        <div
            class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 flex flex-col max-h-[90vh] overflow-y-auto">

            {{-- Header --}}
            <div
                class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 border-b border-slate-100 bg-gradient-to-r from-sky-500 to-teal-500 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-sky-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-user-clock text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-slate-50">
                            Tambah Jadwal Dokter
                        </h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">
                            Tentukan dokter, poli, hari, dan jam praktik dengan jelas agar tidak terjadi bentrok jadwal.
                        </p>
                    </div>
                </div>

                <button type="button" id="closeAddJadwalModal_header"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Form --}}
            <form id="formAddJadwalDokter" class="px-6 pb-5 pt-4 flex flex-col gap-4 bg-slate-50/60"
                data-url="{{ route('pengaturan_klinik.add_jadwal_dokter') }}" method="POST">
                @csrf

                {{-- Info strip --}}
                <div
                    class="flex items-center gap-2 text-xs rounded-xl px-3 py-2
                           bg-emerald-50 text-emerald-700 border border-emerald-100">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Setiap jadwal akan muncul di modul pendaftaran kunjungan pasien.</span>
                </div>

                {{-- Search Dokter --}}
                <div class="space-y-1">
                    <label class="block text-sm font-medium text-slate-800">Cari Dokter</label>
                    <input type="text" id="search_dokter_create" name="search_dokter_create"
                        placeholder="Ketik nama dokter..."
                        class="mt-1 block w-full rounded-lg border border-slate-300 bg-white text-sm text-slate-900
                               focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5">

                    <div id="search_loader_create" class="text-[11px] text-slate-500 mt-1 hidden">
                        Memuat…
                    </div>

                    <div id="search_results_create"
                        class="mt-2 bg-white border border-slate-200 rounded-lg shadow max-h-40 overflow-y-auto hidden text-sm">
                        {{-- hasil pencarian by JS --}}
                    </div>

                    <div id="dokter_chip_create"
                        class="mt-2 hidden inline-flex items-center px-3 py-1 rounded-full bg-sky-50 border border-sky-200">
                        <i class="fa-solid fa-user-doctor mr-2 text-sky-600 text-xs"></i>
                        <span id="dokter_chip_name" class="text-sm text-sky-800 font-medium"></span>
                        <button type="button" id="dokter_chip_clear"
                            class="ml-2 text-[11px] text-sky-700 hover:underline">Ganti</button>
                    </div>
                </div>

                {{-- hidden --}}
                <input type="hidden" name="dokter_id" id="dokter_id_create">
                <input type="hidden" name="poli_id" id="poli_id_create">

                {{-- Poli --}}
                <div id="group_poli_create" class="hidden">
                    <label for="poli_select_create" class="block mb-1 text-sm font-medium text-slate-800">
                        Pilih Poli
                    </label>
                    <select id="poli_select_create" disabled
                        class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg p-2.5
                               disabled:opacity-60 disabled:cursor-not-allowed focus:ring-sky-500 focus:border-sky-500">
                        <option value="">— pilih poli —</option>
                    </select>
                    <p id="poli_select_help" class="mt-1 text-xs text-slate-500">
                        Cari & pilih dokter terlebih dahulu.
                    </p>
                    <div id="poli_id-error" class="text-red-600 text-xs mt-1"></div>
                </div>

                {{-- Hari --}}
                <div>
                    <label for="hari" class="block mb-2 text-sm font-medium text-slate-800">Hari Praktik</label>
                    <select id="hari" name="hari" required
                        class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg w-full p-2.5 focus:ring-sky-500 focus:border-sky-500">
                        <option value="" disabled selected>-</option>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                        <option value="Minggu">Minggu</option>
                    </select>
                    <div id="hari-error" class="text-red-600 text-xs mt-1"></div>
                </div>

                {{-- Jam --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="jam_awal" class="block mb-2 text-sm font-medium text-slate-800">Jam Mulai</label>
                        <input type="time" id="jam_awal" name="jam_awal" required step="1"
                            class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg w-full p-2.5 focus:ring-sky-500 focus:border-sky-500">
                        <div id="jam_awal-error" class="text-red-600 text-xs mt-1"></div>
                    </div>
                    <div>
                        <label for="jam_selesai" class="block mb-2 text-sm font-medium text-slate-800">Jam
                            Selesai</label>
                        <input type="time" id="jam_selesai" name="jam_selesai" required step="1"
                            class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg w-full p-2.5 focus:ring-sky-500 focus:border-sky-500">
                        <div id="jam_selesai-error" class="text-red-600 text-xs mt-1"></div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 mt-6 border-t border-slate-200 pt-4">
                    <button type="button" id="closeAddJadwalModal"
                        class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-slate-200 rounded-xl 
                               hover:bg-slate-300">
                        Batal
                    </button>
                    <button type="submit" id="saveJadwalButton"
                        class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl 
                               bg-gradient-to-r from-sky-500 to-teal-500 hover:from-sky-600 hover:to-indigo-700
                               focus:ring-2 focus:ring-sky-400">
                        Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Jadwal Dokter --}}
<div id="editJadwalModal" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center w-full h-full p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="relative w-full max-w-xl">
        <div
            class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 flex flex-col max-h-[90vh] overflow-y-auto">

            {{-- Header --}}
            <div
                class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 border-b border-slate-100 bg-gradient-to-r from-sky-500 to-teal-500 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div
                        class="h-10 w-10 rounded-xl bg-indigo-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-pen-to-square text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-slate-50">
                            Edit Jadwal Dokter
                        </h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">
                            Sesuaikan poli, hari, dan jam praktik tanpa mengubah identitas dokter.
                        </p>
                    </div>
                </div>

            </div>

            {{-- Form --}}
            <form id="formEditJadwalDokter" class="px-6 pb-5 pt-4 flex flex-col gap-4 bg-slate-50/60"
                data-url-template="{{ route('pengaturan_klinik.update_jadwal_dokter', ['id' => '__ID__']) }}"
                method="POST">
                @csrf

                {{-- hidden key --}}
                <input type="hidden" id="jadwal_id_update" name="jadwal_id_update">
                <input type="hidden" id="dokter_id_update" name="dokter_id">
                <input type="hidden" id="poli_id_update" name="poli_id">

                {{-- Dokter (readonly) --}}
                <div>
                    <label class="block text-sm font-medium text-slate-800">Dokter</label>
                    <input id="search_dokter_update" name="search_dokter_update" type="text" readonly
                        aria-readonly="true"
                        class="block w-full rounded-lg border border-slate-300 py-2.5 px-3 text-sm bg-slate-100
                               text-slate-700 cursor-not-allowed select-none"
                        title="Nama dokter tidak dapat diubah pada mode edit">
                </div>

                {{-- Poli --}}
                <div id="group_poli_update">
                    <label for="poli_select_update" class="block mb-1 text-sm font-medium text-slate-800">
                        Pilih Poli
                    </label>
                    <select id="poli_select_update" disabled
                        class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg p-2.5
                               disabled:opacity-60 disabled:cursor-not-allowed focus:ring-sky-500 focus:border-sky-500">
                        <option value="">— pilih poli —</option>
                    </select>
                    <p id="poli_select_help_update" class="mt-1 text-xs text-slate-500">
                        Poli akan menyesuaikan dengan dokter yang dipilih.
                    </p>
                    <div id="poli_id_update-error" class="text-red-600 text-xs mt-1"></div>
                </div>

                {{-- Hari --}}
                <div>
                    <label for="hari_edit" class="block mb-1 text-sm font-medium text-slate-800">Hari Praktik</label>
                    <select id="hari_edit" name="hari" required
                        class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg w-full p-2.5 focus:ring-sky-500 focus:border-sky-500">
                        <option value="" disabled>—</option>
                        <option>Senin</option>
                        <option>Selasa</option>
                        <option>Rabu</option>
                        <option>Kamis</option>
                        <option>Jumat</option>
                        <option>Sabtu</option>
                        <option>Minggu</option>
                    </select>
                    <div id="hari_edit-error" class="text-red-600 text-xs mt-1"></div>
                </div>

                {{-- Jam --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="jam_awal_edit" class="block mb-1 text-sm font-medium text-slate-800">Jam
                            Mulai</label>
                        <input type="time" id="jam_awal_edit" name="jam_awal" required step="1"
                            class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg w-full p-2.5 focus:ring-sky-500 focus:border-sky-500">
                        <div id="jam_awal_edit-error" class="text-red-600 text-xs mt-1"></div>
                    </div>
                    <div>
                        <label for="jam_selesai_edit" class="block mb-1 text-sm font-medium text-slate-800">Jam
                            Selesai</label>
                        <input type="time" id="jam_selesai_edit" name="jam_selesai" required step="1"
                            class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg w-full p-2.5 focus:ring-sky-500 focus:border-sky-500">
                        <div id="jam_selesai_edit-error" class="text-red-600 text-xs mt-1"></div>
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-3 mt-6 border-t border-slate-200 pt-4">
                    <button type="button" id="closeEditJadwalModal"
                        class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-slate-200 rounded-xl 
                               hover:bg-slate-300">
                        Batal
                    </button>
                    <button type="submit" id="saveJadwalEditButton"
                        class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl 
                               bg-gradient-to-r from-indigo-500 to-sky-600 hover:from-indigo-600 hover:to-sky-700
                               focus:ring-2 focus:ring-indigo-400">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@vite(['resources/js/admin/pengaturanKlinik/jadwal_dokter.js'])
