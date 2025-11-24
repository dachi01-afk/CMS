<section class="space-y-5">

    <!-- HEADER ATAS + CTA -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-user-doctor text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Data Jenis Spesialis Dokter
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola daftar jenis spesialis dokter yang akan digunakan pada modul
                    <span class="font-medium">Jadwal Dokter</span>, <span class="font-medium">Kunjungan</span>, dan modul
                    lainnya.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-3">
            <button type="button"
                class="hidden md:inline-flex items-center gap-2 px-3 py-2 text-xs md:text-sm rounded-xl border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-600">
                <i class="fa-regular fa-circle-question text-sm"></i>
                <span>Panduan Spesialis</span>
            </button>

            <!-- Tombol Tambah -->
            <button id="btnAddJenisSpesialisDokter" type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-xl shadow-md
                       bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                       focus:outline-none focus:ring-2 focus:ring-sky-400">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Jenis Spesialis</span>
            </button>
        </div>
    </div>

    <!-- CARD TABEL -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm">

        <!-- Toolbar atas -->
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">

            <!-- Page length -->
            <div class="flex items-center gap-2 text-sm">
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>
                <select id="jenis-spesialis-dokter_pageLength"
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
                    <input type="text" id="jenis-spesialis-dokter_searchInput"
                        class="block w-full md:w-72 pl-9 pr-3 py-2 text-sm text-slate-800 dark:text-slate-100
                               border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700
                               focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari nama spesialis dokter...">
                </div>
                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Contoh: <span class="italic">Spesialis Anak, Spesialis Penyakit Dalam, Spesialis Saraf</span>.
                </p>
            </div>
        </div>

        <!-- Tabel -->
        <div class="overflow-x-auto">
            <table id="jenisSpesialisDokter"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100 border-t border-slate-100 dark:border-slate-700">
                <thead
                    class="text-xs font-semibold uppercase bg-gradient-to-r from-sky-500 to-teal-500 text-white tracking-wide">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Nama Spesialis</th>
                        <th class="px-6 py-3 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
            </table>
        </div>

        <!-- Footer -->
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">
            <div id="jenis-spesialis-dokter_customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300">
            </div>

            <ul id="jenis-spesialis-dokter_customPagination"
                class="inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
            </ul>
        </div>
    </div>

</section>

{{-- Modal Add Data Jenis Spesialis Dokter --}}
<div id="addJenisSpesialisDokterModal" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto overflow-x-hidden">

    <div class="relative w-full max-w-lg">
        <div
            class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 dark:bg-slate-800 dark:border-slate-700">

            {{-- HEADER --}}
            <div
                class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-sky-500 to-teal-500 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-sky-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-user-doctor text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-slate-50">
                            Tambah Data Jenis Spesialis Dokter
                        </h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">
                            Lengkapi informasi jenis spesialis dokter untuk digunakan di seluruh modul klinik.
                        </p>
                    </div>
                </div>

                <button type="button" id="closeAddJenisSpesialisDokterModal"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- FORM --}}
            <form id="formAddJenisSpesialisDokter" class="px-6 pb-5 space-y-5"
                data-url="{{ route('create.data.jenis.spesialis.dokter') }}" method="POST">
                @csrf

                {{-- Nama Spesialis --}}
                <div class="space-y-1.5">
                    <label for="nama_spesialis" class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                        Nama Spesialis <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_spesialis" id="nama_spesialis"
                        class="block w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2.5 text-sm text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500
                               dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                        placeholder="Contoh: Spesialis Penyakit Dalam" required>
                    <p class="text-[11px] text-slate-500">
                        Gunakan nama spesialis yang jelas, misalnya:
                        <span class="italic">Spesialis Anak, Spesialis Gigi, Spesialis Saraf</span>.
                    </p>
                    <div id="nama_spesialis-error" class="text-red-600 text-xs mt-1"></div>
                </div>

                {{-- FOOTER BUTTONS --}}
                <div
                    class="flex items-center justify-end gap-3 pt-4 mt-2 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" id="closeAddJenisSpesialisDokterModal_footer"
                        class="px-4 py-2.5 text-sm font-medium rounded-xl border border-slate-200 bg-white text-slate-700
                               hover:bg-slate-50 hover:border-slate-300 transition
                               dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-xl
                               bg-gradient-to-r from-sky-500 to-teal-600 text-white shadow-md
                               hover:from-sky-600 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-sky-400">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Simpan Data</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Update Jenis Spesialis --}}
<div id="modalUpdateJenisSpesialis" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">

    <div class="relative w-full max-w-lg">
        <div
            class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 dark:bg-slate-800 dark:border-slate-700">

            {{-- HEADER --}}
            <div
                class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-sky-500 to-teal-500 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-teal-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-pen-to-square text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-slate-50">
                            Edit Jenis Spesialis Dokter
                        </h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">
                            Perbarui informasi jenis spesialis dokter dengan benar.
                        </p>
                    </div>
                </div>

                <button type="button" id="buttonCloseModalUpdateJenisSpesialis_Header"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- FORM --}}
            <form id="formUpdateModalJenisSpesialis" method="POST"
                data-url="{{ route('update.data.jenis.spesialis.dokter') }}" class="px-6 pb-4 space-y-5">
                @csrf
                <input type="hidden" id="id_update" name="id">

                {{-- Nama Spesialis --}}
                <div class="space-y-1.5">
                    <label for="nama_spesialis_update"
                        class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                        Nama Spesialis <span class="text-red-500">*</span>
                    </label>

                    <input type="text" name="nama_spesialis" id="nama_spesialis_update"
                        class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500
                               dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                        placeholder="Contoh: Spesialis Penyakit Dalam" required>

                    <div id="nama_spesialis_update-error" class="text-red-600 text-xs mt-1"></div>
                </div>

                {{-- FOOTER BUTTONS --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button type="button" id="buttonCloseModalUpdateJenisSpesialis"
                        class="px-4 py-2.5 text-sm font-medium rounded-xl border border-slate-200 bg-white text-slate-700
                               hover:bg-slate-50 hover:border-slate-300 transition
                               dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-600">
                        Batal
                    </button>

                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-xl
                               bg-gradient-to-r from-teal-500 to-sky-600 text-white shadow-md
                               hover:from-teal-600 hover:to-sky-700 focus:outline-none focus:ring-2 focus:ring-teal-400">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@vite(['resources/js/admin/jenisSpesialisDokter/data-jenis-spesialis-dokter.js'])
