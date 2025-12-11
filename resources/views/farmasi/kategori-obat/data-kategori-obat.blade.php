<section class="space-y-5">

    <!-- HEADER ATAS + CTA -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-layer-group text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Data Kategori Obat
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola data kategori Obat.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-3">

            <!-- Tombol Info -->
            {{-- <button type="button" id="btnInfoKategoriLayanan"
                class="hidden md:inline-flex items-center gap-2 px-3 py-2 text-xs md:text-sm rounded-xl border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-600">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>Penting</span>
            </button> --}}

            <!-- Tombol Tambah -->
            <button id="buttonModalCreateKategoriObat" type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-xl shadow-md
                       bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                       focus:outline-none focus:ring-2 focus:ring-sky-400">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Kategori Obat</span>
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
                <select id="kategori-obat-pageLength"
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
                    <input type="text" id="kategori-layanan-searchInput"
                        class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm text-slate-800 dark:text-slate-100
                               border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700
                               focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari Kategori Obat">
                </div>
                {{-- <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Contoh: <span class="italic">Layanan Dokter Umum, Laboratorium, Tindakan Medis</span>.
                </p> --}}
            </div>
        </div>

        <!-- Tabel -->
        <div class="overflow-x-auto">
            <table id="tabelKategoriLayanan"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100 border-t border-slate-100 dark:border-slate-700">
                <thead
                    class="text-xs font-semibold uppercase bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600 text-white tracking-wide">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Nama Kategori Obat</th>
                        <th class="px-6 py-3 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
            </table>
        </div>

        <!-- Footer -->
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">
            <div id="kategori-layanan-customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>

            <ul id="kategori-layanan-customPagination"
                class="inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
            </ul>
        </div>
    </div>

</section>


<!-- Modal Create Kategori Obat -->
<div id="modalCreateKategoriObat" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">

    <div class="w-full max-w-2xl">
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden border border-slate-100 dark:border-slate-700">

            <!-- HEADER -->
            <div
                class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-sky-500 to-teal-500 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-sky-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-slate-50">
                            Tambah Data Kategori Obat
                        </h3>
                        {{-- <p class="text-xs text-sky-50/90 mt-0.5">
                            Kategori membantu pengelompokan tarif dan laporan Obat di klinik.
                        </p> --}}
                    </div>
                </div>

                <button type="button" id="buttonCloseModalCreateKategoriObat"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- FORM -->
            <form id="formCreateKategoriObat" data-url="{{ route('kategori.obat.create.data.kategori.obat') }}"
                method="POST" class="px-6 pb-4 space-y-5">
                @csrf

                <!-- Nama Kategori -->
                <div class="space-y-1.5">
                    <label for="nama_kategori_obat_create"
                        class="block mb-1 text-sm font-medium text-slate-800 dark:text-slate-100">
                        Nama Kategori
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-tag absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="nama_kategori_obat_create" name="nama_kategori_obat"
                            class="w-full pl-9 p-3 rounded-xl border border-slate-300 bg-slate-50 text-slate-800
                                       focus:ring-teal-500 focus:border-teal-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                            placeholder="Obat Bebas dll">
                    </div>
                    <div id="nama_kategori_obat-error" class="text-red-600 text-xs mt-1"></div>
                </div>
            </form>

            <!-- FOOTER BUTTON -->
            <div
                class="flex justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-slate-800/70 border-t border-slate-200 dark:border-slate-700">
                <button id="buttonCloseModalCreateKategoriObat_footer"
                    class="px-5 py-2.5 rounded-xl bg-slate-200 text-slate-800 text-sm font-medium hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100">
                    Batal
                </button>

                <button type="submit" form="formCreateKategoriObat"
                    class="px-6 py-2.5 rounded-xl bg-teal-600 text-white text-sm font-semibold hover:bg-teal-700 shadow">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Update Kategori Layanan -->
<div id="modalUpdateKategoriObat" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">

    <div class="w-full max-w-2xl">
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden border border-slate-100 dark:border-slate-700">

            <!-- HEADER -->
            <div
                class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-sky-500 to-teal-500 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-sky-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-slate-50">
                            Edit Data Kategori Obat
                        </h3>
                        {{-- <p class="text-xs text-sky-50/90 mt-0.5">
                            Pastikan perubahan sesuai dengan struktur layanan yang berjalan.
                        </p> --}}
                    </div>
                </div>

                <button type="button" id="buttonCloseModalUpdateKategoriObat"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>


            <!-- FORM -->
            <form id="formUpdateKategoriObat" data-url="{{ route('kategori.obat.update.data.kategori.obat') }}"
                method="POST" class="px-6 pb-4 space-y-5">
                @csrf
                <input type="hidden" id="id_update" name="id">

                <!-- Nama Kategori -->
                <div class="space-y-1.5">
                    <label class="block mb-1 text-sm font-medium text-slate-800 dark:text-slate-100">
                        Nama Kategori
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-tag absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="nama_kategori_obat_update"
                            class="w-full pl-9 p-3 rounded-xl border border-slate-300 bg-slate-50 text-slate-800
                                       focus:ring-teal-500 focus:border-teal-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                            placeholder="Masukkan nama kategori obat">
                    </div>
                    <div id="nama_kategori_obat-error" class="text-red-600 text-xs mt-1"></div>
                </div>
            </form>

            <!-- FOOTER -->
            <div
                class="flex justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-slate-800/70 border-t border-slate-200 dark:border-slate-700">
                <button id="buttonCloseModalUpdateKategoriObat_footer"
                    class="px-5 py-2.5 rounded-xl bg-slate-200 text-slate-800 text-sm font-medium hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100">
                    Batal
                </button>

                <button type="submit" form="formUpdateKategoriObat"
                    class="px-6 py-2.5 rounded-xl bg-teal-600 text-white text-sm font-semibold hover:bg-teal-700 shadow">
                    Update
                </button>
            </div>

        </div>
    </div>
</div>

@vite(['resources/js/farmasi/kategori-obat/data-kategori-obat.js'])
