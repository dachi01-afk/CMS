<section class="space-y-5">

    <!-- HEADER ATAS + CTA -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-hospital text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Data Poli
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola daftar <span class="font-medium">Poli</span> yang digunakan pada modul
                    <span class="font-medium">Jadwal Dokter</span>, <span class="font-medium">Kunjungan</span>, dan
                    layanan lainnya.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-3">
            <button type="button"
                class="hidden md:inline-flex items-center gap-2 px-3 py-2 text-xs md:text-sm rounded-xl border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-600">
                <i class="fa-regular fa-circle-question text-sm"></i>
                <span>Panduan Poli</span>
            </button>

            <!-- Tombol Tambah -->
            <button id="buttonModalCreatePoli" type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-xl shadow-md
                       bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                       focus:outline-none focus:ring-2 focus:ring-sky-400">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Data Poli</span>
            </button>
        </div>
    </div>

    <!-- CARD TABEL -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm">

        <!-- Toolbar -->
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">

            <!-- Page length -->
            <div class="flex items-center gap-2 text-sm">
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>
                <select id="poli-pageLength"
                    class="border border-slate-300 dark:border-slate-600 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500
                           bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-1 w-28">
                    <option value="10">10 baris</option>
                    <option value="25">25 baris</option>
                    <option value="50">50 baris</option>
                    <option value="100">100 baris</option>
                </select>
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">per halaman</span>
            </div>

            <!-- Search -->
            <div class="w-full md:w-auto">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                    </span>
                    <input type="text" id="poli-searchInput"
                        class="block w-full md:w-72 pl-9 pr-3 py-2 text-sm text-slate-800 dark:text-slate-100
                               border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700
                               focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari nama poli...">
                </div>
                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Contoh: <span class="italic">Poli Umum, Poli Gigi, Poli Anak</span>.
                </p>
            </div>
        </div>

        <!-- Tabel -->
        <div class="overflow-x-auto">
            <table id="poliTable"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100 border-t border-slate-100 dark:border-slate-700">
                <thead
                    class="text-xs font-semibold uppercase bg-gradient-to-r from-sky-500 to-teal-500 text-white tracking-wide">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Nama Poli</th>
                        <th class="px-6 py-3 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
            </table>
        </div>

        <!-- Footer -->
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">
            <div id="poli-customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300">
            </div>

            <ul id="poli-customPagination"
                class="inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
            </ul>
        </div>
    </div>

</section>


<!-- Modal Create Poli -->
<div id="modalCreatePoli" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto overflow-x-hidden">
    <div class="relative w-full max-w-xl">
        <div
            class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 dark:bg-slate-800 dark:border-slate-700">

            <!-- HEADER -->
            <div
                class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-sky-500 to-teal-500 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-sky-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-hospital-user text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-slate-50">
                            Tambah Data Poli
                        </h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">
                            Isi nama poli sesuai layanan yang tersedia di klinik / rumah sakit.
                        </p>
                    </div>
                </div>

                <button type="button" id="buttonCloseModalCreatePoli"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- FORM -->
            <form id="formCreatePoli" class="px-6 pb-4 space-y-5"
                data-url="{{ route('poli.create.data') }}" method="POST">
                @csrf

                {{-- Nama Poli --}}
                <div class="space-y-1.5">
                    <label for="nama_poli_create" class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                        Nama Poli <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_poli" id="nama_poli_create"
                        class="bg-slate-50/70 border border-slate-200 text-slate-900 text-sm rounded-xl w-full px-3 py-2.5
                                   focus:ring-sky-500 focus:border-sky-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                        placeholder="Contoh: Poli Umum, Poli Gigi, Poli Anak" required>
                    <div id="nama_poli-error" class="text-red-600 text-xs mt-1"></div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 mt-5 border-t border-slate-100 pt-4 dark:border-slate-700">
                    <button type="button" id="buttonCloseModalCreatePoli_footer"
                        class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit" id="saveJadwalButton"
                        class="px-5 py-2.5 inline-flex items-center gap-2 text-sm font-semibold text-white rounded-xl
                                   bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700 focus:ring-2 focus:ring-sky-400">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Update Poli --}}
<div id="modalUpdatePoli" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto overflow-x-hidden">
    <div class="relative w-full max-w-xl">
        <div
            class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 dark:bg-slate-800 dark:border-slate-700">

            <!-- HEADER -->
            <div
                class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-sky-500 to-teal-500 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-teal-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-pen-to-square text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-slate-50">
                            Edit Data Poli
                        </h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">
                            Perbarui nama poli sesuai perubahan layanan yang tersedia.
                        </p>
                    </div>
                </div>

                <button type="button" id="buttonCloseModalUpdatePoli"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- FORM -->
            <form id="formUpdatePoli" class="px-6 pb-4 space-y-5"
                data-url="{{ route('poli.update.data') }}" method="POST">
                @csrf
                <input type="hidden" id="id_update" name="id">

                {{-- Nama Poli --}}
                <div class="space-y-1.5">
                    <label for="nama_poli_update"
                        class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                        Nama Poli <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_poli" id="nama_poli_update"
                        class="bg-slate-50 border border-slate-200 text-slate-900 text-sm rounded-xl w-full px-3 py-2.5
                                   focus:ring-teal-500 focus:border-teal-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                        placeholder="Contoh: Poli Umum, Poli Gigi, Poli Anak" required>
                    <div id="nama_poli-error" class="text-red-600 text-xs mt-1"></div>
                </div>

                <div class="flex justify-end gap-3 mt-5 border-t border-slate-100 pt-4 dark:border-slate-700">
                    <button type="button" id="buttonCloseModalUpdatePoli_footer"
                        class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:border-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:border-slate-600 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit" id="updateJadwalButton"
                        class="px-5 py-2.5 inline-flex items-center gap-2 text-sm font-semibold text-white rounded-xl
                                   bg-gradient-to-r from-teal-500 to-sky-600 hover:from-teal-600 hover:to-sky-700 focus:ring-2 focus:ring-teal-400">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@vite(['resources/js/admin/poli/data-poli.js'])
