<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Detail Layanan</h2>

    <!-- Modal toggle -->
    <button id="buttonModalCreateLayanan"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none">
        + Tambah Data
    </button>
</div>

<!-- Tabel -->
<div class="overflow-hidden rounded-lg shadow-md">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200">
        <div>
            <select id="layanan-pageLength"
                class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-24 p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="relative">
            <input type="text" id="layanan-searchInput"
                class="block w-60 p-2 pl-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Cari data...">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="layananTable" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs uppercase bg-sky-500 text-white">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">Nama Layanan</th>
                    <th class="px-6 py-3">Tarif Layanan</th>
                    <th class="px-6 py-3">Kategori Layanan</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-4 py-3 border-t border-gray-200 gap-3">
        <div id="layanan-customInfo" class="text-sm text-gray-700 dark:text-gray-300"></div>
        <ul id="layanan-customPagination" class="inline-flex -space-x-px text-sm"></ul>
    </div>
</div>

<!-- Modal Create Layanan -->
<div id="modalCreateLayanan" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center p-4
           bg-black/40 backdrop-blur-sm">

    <div class="w-full max-w-2xl">
        <div class="bg-white/95 dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">

            <!-- HEADER -->
            <div class="px-6 py-4 bg-gradient-to-r from-sky-600 via-cyan-500 to-emerald-500">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-heart-pulse text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">
                            Tambah Data Layanan Klinik
                        </h3>
                        <p class="text-xs text-sky-50/90">
                            Lengkapi informasi layanan medis dan tarifnya dengan benar.
                        </p>
                    </div>
                </div>
            </div>

            <!-- FORM -->
            <form id="formCreateLayanan" class="px-6 py-5 space-y-5 bg-slate-50/60 dark:bg-gray-800"
                data-url="{{ route('layanan.create.data') }}" method="POST">
                @csrf

                <!-- Info strip -->
                <div
                    class="flex items-center gap-2 text-xs rounded-xl px-3 py-2
                            bg-emerald-50 text-emerald-700 border border-emerald-100
                            dark:bg-emerald-900/40 dark:text-emerald-100 dark:border-emerald-800">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Pastikan nama layanan dan tarif sesuai dengan aturan klinik.</span>
                </div>

                {{-- Kategori Layanan --}}
                <div>
                    <label for="kategori_layanan_id_create"
                        class="block mb-1.5 text-sm font-semibold text-gray-800 dark:text-gray-100">
                        Kategori Layanan
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-layer-group absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <select id="kategori_layanan_id_create" name="kategori_layanan_id"
                            class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300
                                       bg-white text-gray-800 text-sm
                                       focus:ring-teal-500 focus:border-teal-500
                                       dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                            <option value="">Pilih kategori layanan</option>
                            @foreach ($dataKategoriLayanan as $kategori)
                                <option value="{{ $kategori->id }}">
                                    {{ $kategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div id="kategori_layanan_id-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Nama Layanan --}}
                <div>
                    <label for="nama_layanan_create"
                        class="block mb-1.5 text-sm font-semibold text-gray-800 dark:text-gray-100">
                        Nama Layanan
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-stethoscope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="nama_layanan" id="nama_layanan_create"
                            class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300
                                      bg-white text-gray-800 text-sm
                                      focus:ring-teal-500 focus:border-teal-500
                                      dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                            placeholder="Contoh: Konsultasi Dokter Umum">
                    </div>
                    <div id="nama_layanan-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Harga Layanan -->
                <div>
                    <label for="harga_layanan_create"
                        class="block mb-1.5 text-sm font-semibold text-gray-800 dark:text-gray-100">
                        Harga Layanan
                    </label>
                    <div class="relative">
                        <span
                            class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-300 text-sm">
                            Rp
                        </span>
                        <input type="text" name="harga_layanan" id="harga_layanan_create"
                            class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300
                                      bg-white text-gray-800 text-sm
                                      focus:ring-teal-500 focus:border-teal-500
                                      dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                            placeholder="Contoh: 150.000">
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Tulis angka saja, sistem akan mengonversi sesuai format penyimpanan.
                    </p>
                    <div id="harga_layanan-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- FOOTER --}}
                <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" id="buttonCloseModalCreateLayanan"
                        class="px-5 py-2.5 text-sm font-medium rounded-lg
                                   bg-gray-200 text-gray-800 hover:bg-gray-300
                                   dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">
                        Batal
                    </button>
                    <button type="submit" id="saveJadwalButton"
                        class="px-5 py-2.5 text-sm font-semibold rounded-lg
                                   bg-teal-600 text-white shadow-md
                                   hover:bg-teal-700 focus:ring-4 focus:ring-teal-200
                                   dark:focus:ring-teal-800">
                        Simpan Layanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Update Layanan -->
<div id="modalUpdateLayanan" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center p-4
           bg-black/40 backdrop-blur-sm">

    <div class="w-full max-w-2xl">
        <div class="bg-white/95 dark:bg-gray-800 rounded-2xl shadow-2xl overflow-hidden">

            <!-- HEADER -->
            <div class="px-6 py-4 bg-gradient-to-r from-sky-600 via-cyan-500 to-emerald-500">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-pen-to-square text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-white">
                            Edit Data Layanan Klinik
                        </h3>
                        <p class="text-xs text-sky-50/90">
                            Perbarui informasi layanan dengan benar.
                        </p>
                    </div>
                </div>
            </div>

            <!-- FORM -->
            <form id="formUpdateLayanan" class="px-6 py-5 space-y-5 bg-slate-50/60 dark:bg-gray-800"
                data-url="{{ route('layanan.update.data') }}" method="POST">

                <!-- Info strip -->
                <div
                    class="flex items-center gap-2 text-xs rounded-xl px-3 py-2
                            bg-emerald-50 text-emerald-700 border border-emerald-100
                            dark:bg-emerald-900/40 dark:text-emerald-100 dark:border-emerald-800">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Pastikan nama layanan dan tarif sesuai dengan aturan klinik.</span>
                </div>

                @csrf
                <input type="hidden" id="id_update" name="id">

                {{-- Kategori Layanan --}}
                <div>
                    <label for="kategori_layanan_id_update"
                        class="block mb-1.5 text-sm font-semibold text-gray-800 dark:text-gray-100">
                        Kategori Layanan
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-layer-group absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>

                        <select id="kategori_layanan_id_update" name="kategori_layanan_id"
                            class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300
                                   bg-white text-gray-800 text-sm
                                   focus:ring-teal-500 focus:border-teal-500
                                   dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100">
                            <option value="">Memuat kategori…</option>
                        </select>
                    </div>
                    <div id="kategori_layanan_id-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Nama Layanan --}}
                <div>
                    <label class="block mb-1.5 text-sm font-semibold text-gray-800 dark:text-gray-100">
                        Nama Layanan
                    </label>
                    <div class="relative">
                        <i
                            class="fa-solid fa-stethoscope absolute left-3 top-1/2 -translate-y-1/2 
                                   text-gray-400"></i>

                        <input type="text" id="nama_layanan_update" name="nama_layanan"
                            class="w-full pl-10 py-2.5 rounded-lg border border-gray-300 bg-white text-gray-800
                                   focus:ring-teal-500 focus:border-teal-500
                                   dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                            placeholder="Masukkan nama layanan">
                    </div>
                    <div id="nama_layanan-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Harga --}}
                <div>
                    <label class="block mb-1.5 text-sm font-semibold text-gray-800 dark:text-gray-100">
                        Harga Layanan
                    </label>
                    <div class="relative">
                        <span
                            class="absolute inset-y-0 left-0 flex items-center pl-3 
                                     text-gray-500 dark:text-gray-300">Rp</span>

                        <input type="text" id="harga_layanan_update" name="harga_layanan"
                            class="w-full pl-10 py-2.5 rounded-lg border border-gray-300 bg-white text-gray-800
                                      focus:ring-teal-500 focus:border-teal-500
                                      dark:bg-gray-700 dark:border-gray-600 dark:text-gray-100"
                            placeholder="Contoh: 150.000">
                    </div>
                    <div id="harga_layanan-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="button" id="buttonCloseModalUpdateLayanan"
                        class="px-5 py-2.5 text-sm font-medium rounded-lg
                               bg-gray-200 text-gray-800 hover:bg-gray-300
                               dark:bg-gray-700 dark:text-gray-100 dark:hover:bg-gray-600">
                        Batal
                    </button>

                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold rounded-lg
                               bg-teal-600 text-white hover:bg-teal-700 shadow-md
                               focus:ring-4 focus:ring-teal-200 dark:focus:ring-teal-800">
                        Simpan Perubahan
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>


@vite(['resources/js/admin/layanan/data-layanan.js'])
