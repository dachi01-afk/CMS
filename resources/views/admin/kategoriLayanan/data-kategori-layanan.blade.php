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
                    Data Kategori Layanan
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Atur kategori layanan untuk mengelompokkan tindakan medis, pemeriksaan, dan jasa lain di klinik /
                    rumah sakit Anda.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-3">
            <button type="button"
                class="hidden md:inline-flex items-center gap-2 px-3 py-2 text-xs md:text-sm rounded-xl border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-600">
                <i class="fa-regular fa-circle-question text-sm"></i>
                <span>Panduan Kategori</span>
            </button>

            <!-- Tombol Tambah -->
            <button id="buttonModalCreateLayanan" type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-xl shadow-md
                       bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                       focus:outline-none focus:ring-2 focus:ring-sky-400">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Kategori Layanan</span>
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
                <select id="layanan-pageLength"
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
                    <input type="text" id="layanan-searchInput"
                        class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm text-slate-800 dark:text-slate-100
                               border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700
                               focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari kategori / deskripsi layanan...">
                </div>
                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Contoh: <span class="italic">Layanan Dokter Umum, Laboratorium, Tindakan Medis</span>.
                </p>
            </div>
        </div>

        <!-- Tabel -->
        <div class="overflow-x-auto">
            <table id="layananTable"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100 border-t border-slate-100 dark:border-slate-700">
                <thead
                    class="text-xs font-semibold uppercase bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600 text-white tracking-wide">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Nama Kategori Layanan</th>
                        <th class="px-6 py-3">Deskripsi Kategori Layanan</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
            </table>
        </div>

        <!-- Footer -->
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">
            <div id="layanan-customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>

            <ul id="layanan-customPagination"
                class="inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
            </ul>
        </div>
    </div>

</section>


<!-- Modal Create Kategori Layanan -->
<div id="modalCreateKategoriLayanan" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">

    <div class="w-full max-w-2xl">
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden border border-slate-100 dark:border-slate-700">

            <!-- HEADER -->
            <div class="px-6 py-4 bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-layer-group"></i>
                    Tambah Data Kategori Layanan
                </h3>
                <p class="text-xs text-sky-50/90 mt-1">
                    Kategori membantu pengelompokan tarif dan laporan layanan di klinik.
                </p>
            </div>

            <!-- FORM -->
            <form id="formCreateKategoriLayanan" data-url="{{ route('kategori.layanan.create.data.kategori.layanan') }}"
                method="POST" class="px-6 pb-4 space-y-5">
                @csrf

                <!-- Nama Kategori -->
                <div class="space-y-1.5">
                    <label for="nama_kategori_create"
                        class="block mb-1 text-sm font-medium text-slate-800 dark:text-slate-100">
                        Nama Kategori
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-tag absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="nama_kategori_create" name="nama_kategori"
                            class="w-full pl-9 p-3 rounded-xl border border-slate-300 bg-slate-50 text-slate-800
                                       focus:ring-teal-500 focus:border-teal-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                            placeholder="Contoh: Layanan Dokter Umum, Laboratorium, Tindakan Medis">
                    </div>
                    <div id="nama_kategori-error" class="text-red-600 text-xs mt-1"></div>
                </div>

                <!-- Deskripsi Kategori -->
                <div class="space-y-1.5">
                    <label for="deskripsi_kategori_create"
                        class="block mb-1 text-sm font-medium text-slate-800 dark:text-slate-100">
                        Deskripsi Kategori
                    </label>
                    <textarea name="deskripsi_kategori" id="deskripsi_kategori_create" rows="3"
                        class="w-full p-3 rounded-xl border border-slate-300 bg-slate-50 text-slate-800
                                   focus:ring-teal-500 focus:border-teal-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                        placeholder="Tuliskan deskripsi singkat, contoh: berisi semua layanan konsultasi dokter umum dan spesialis."></textarea>
                    <div id="deskripsi_kategori-error" class="text-red-600 text-xs mt-1"></div>
                </div>

                <!-- Status Kategori -->
                <div class="space-y-1.5">
                    <label
                        class="block mb-1 text-sm font-semibold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fa-solid fa-toggle-on mr-2 text-teal-400"></i>
                        Status Kategori
                    </label>
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="status_kategori" value="Aktif"
                                class="mr-2 text-teal-500 focus:ring-teal-500" checked>
                            <span class="text-sm text-slate-700 dark:text-slate-100">Aktif</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="status_kategori" value="Tidak Aktif"
                                class="mr-2 text-red-500 focus:ring-red-500">
                            <span class="text-sm text-slate-700 dark:text-slate-100">Tidak Aktif</span>
                        </label>
                    </div>
                    <div id="status_kategori-error" class="text-red-500 text-xs mt-1 hidden"></div>
                </div>

            </form>

            <!-- FOOTER BUTTON -->
            <div
                class="flex justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-slate-800/70 border-t border-slate-200 dark:border-slate-700">
                <button id="buttonCloseModalCreateLayanan"
                    class="px-5 py-2.5 rounded-xl bg-slate-200 text-slate-800 text-sm font-medium hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100">
                    Batal
                </button>

                <button type="submit" form="formCreateKategoriLayanan"
                    class="px-6 py-2.5 rounded-xl bg-teal-600 text-white text-sm font-semibold hover:bg-teal-700 shadow">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Update Kategori Layanan -->
<div id="modalUpdateKategoriLayanan" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">

    <div class="w-full max-w-2xl">
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden border border-slate-100 dark:border-slate-700">

            <!-- HEADER -->
            <div class="px-6 py-4 bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600">
                <h3 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-pen-to-square"></i>
                    Edit Data Kategori Layanan
                </h3>
                <p class="text-xs text-sky-50/90 mt-1">
                    Pastikan perubahan sesuai dengan struktur layanan yang berjalan.
                </p>
            </div>

            <!-- FORM -->
            <form id="formUpdateKategoriLayanan"
                data-url="{{ route('kategori.layanan.update.data.kategori.layanan') }}" method="POST"
                class="px-6 pb-4 space-y-5">
                @csrf
                <input type="hidden" id="id_update" name="id">

                <!-- Nama Kategori -->
                <div class="space-y-1.5">
                    <label class="block mb-1 text-sm font-medium text-slate-800 dark:text-slate-100">
                        Nama Kategori
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-tag absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="nama_kategori_update" name="nama_kategori"
                            class="w-full pl-9 p-3 rounded-xl border border-slate-300 bg-slate-50 text-slate-800
                                       focus:ring-teal-500 focus:border-teal-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                            placeholder="Masukkan nama kategori layanan">
                    </div>
                    <div id="nama_kategori-error" class="text-red-600 text-xs mt-1"></div>
                </div>

                <!-- Deskripsi Kategori -->
                <div class="space-y-1.5">
                    <label class="block mb-1 text-sm font-medium text-slate-800 dark:text-slate-100">
                        Deskripsi Kategori
                    </label>
                    <textarea id="deskripsi_kategori_update" name="deskripsi_kategori" rows="3"
                        class="w-full p-3 rounded-xl border border-slate-300 bg-slate-50 text-slate-800
                                   focus:ring-teal-500 focus:border-teal-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                        placeholder="Tuliskan deskripsi kategori..."></textarea>
                    <div id="deskripsi_kategori-error" class="text-red-600 text-xs mt-1"></div>
                </div>

                <!-- Status Kategori -->
                <div class="space-y-1.5">
                    <label
                        class="block mb-1 text-sm font-semibold text-slate-800 dark:text-slate-100 flex items-center">
                        <i class="fa-solid fa-toggle-on mr-2 text-teal-400"></i>
                        Status Kategori
                    </label>
                    <div class="flex flex-wrap gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="status_kategori" value="Aktif"
                                class="mr-2 text-teal-500 focus:ring-teal-500">
                            <span class="text-sm text-slate-700 dark:text-slate-100">Aktif</span>
                        </label>

                        <label class="flex items-center cursor-pointer">
                            <input type="radio" name="status_kategori" value="Tidak Aktif"
                                class="mr-2 text-red-500 focus:ring-red-500">
                            <span class="text-sm text-slate-700 dark:text-slate-100">Tidak Aktif</span>
                        </label>
                    </div>
                    <div id="status_kategori-error" class="text-red-500 text-xs mt-1 hidden"></div>
                </div>

            </form>

            <!-- FOOTER -->
            <div
                class="flex justify-end gap-3 px-6 py-4 bg-slate-50 dark:bg-slate-800/70 border-t border-slate-200 dark:border-slate-700">
                <button id="buttonCloseModalUpdateLayanan"
                    class="px-5 py-2.5 rounded-xl bg-slate-200 text-slate-800 text-sm font-medium hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100">
                    Batal
                </button>

                <button type="submit" form="formUpdateKategoriLayanan"
                    class="px-6 py-2.5 rounded-xl bg-teal-600 text-white text-sm font-semibold hover:bg-teal-700 shadow">
                    Update
                </button>
            </div>

        </div>
    </div>
</div>

@vite(['resources/js/admin/kategoriLayanan/data-kategori-layanan.js'])
