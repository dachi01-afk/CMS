<section class="space-y-5">

    <!-- HEADER ATAS + CTA -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-heart-pulse text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Detail Layanan Klinik
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola daftar <span class="font-medium">layanan medis</span>, <span class="font-medium">tarif</span>,
                    dan <span class="font-medium">kategori layanan</span> yang akan digunakan di pendaftaran, kasir, dan
                    laporan keuangan.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-3">
            <!-- Tombol Info -->
            <button type="button" id="btnInfoLayanan"
                class="hidden md:inline-flex items-center gap-2 px-3 py-2 text-xs md:text-sm rounded-xl border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-600">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>Penting</span>
            </button>

            <!-- Tombol Tambah -->
            <button id="buttonModalCreateLayanan" type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-xl shadow-md
                       bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                       focus:outline-none focus:ring-2 focus:ring-sky-400">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Data Layanan</span>
            </button>
        </div>
    </div>

    <!-- CARD TABEL -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm">

        <!-- Toolbar atas: page length + search -->
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
                        placeholder="Cari nama layanan, kategori, atau tarif...">
                </div>
                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Contoh: <span class="italic">Konsultasi Dokter Umum, Laboratorium, Tindakan Luka</span>.
                </p>
            </div>
        </div>

        <!-- Tabel -->
        <div class="overflow-x-auto">
            <table id="layananTable"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100 border-t border-slate-100 dark:border-slate-700">
                <thead
                    class="text-xs font-semibold uppercase bg-gradient-to-r from-sky-500 to-teal-500 text-white tracking-wide">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Nama Layanan</th>
                        <th class="px-6 py-3">Harga Layanan</th>
                        <th class="px-6 py-3">Diskon</th>
                        <th class="px-6 py-3">Tarif Layanan</th>
                        <th class="px-6 py-3">Kategori Layanan</th>
                        <th class="px-6 py-3 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
            </table>
        </div>

        <!-- Footer: info + pagination -->
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">
            <div id="layanan-customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>

            <ul id="layanan-customPagination"
                class="inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
            </ul>
        </div>
    </div>

</section>

<!-- ===================== -->
<!-- Modal Create Layanan  -->
<!-- ===================== -->
<div id="modalCreateLayanan" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-center justify-center p-4
           bg-slate-900/60 backdrop-blur-sm overflow-y-auto overflow-x-hidden">

    <div class="w-full max-w-2xl">
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700
         max-h-[90vh] flex flex-col overflow-hidden">

            <!-- HEADER -->
            <div
                class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-sky-500 to-teal-500 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-sky-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-heart-pulse text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-slate-50">Tambah Data Layanan Klinik</h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">Lengkapi informasi layanan medis dan tarifnya dengan
                            benar.</p>
                    </div>
                </div>

                <button type="button" id="buttonCloseModalCreateLayanan"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- FORM -->
            <form id="formCreateLayanan"
                class="px-6 py-4 space-y-5 bg-slate-50/60 dark:bg-slate-800
         flex-1 overflow-y-auto"
                data-url="{{ route('layanan.create.data') }}" method="POST">
                @csrf

                <div
                    class="flex items-center gap-2 text-xs rounded-xl px-3 py-2 bg-emerald-50 text-emerald-700 border border-emerald-100
                            dark:bg-emerald-900/40 dark:text-emerald-100 dark:border-emerald-800">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Pastikan nama layanan, kategori, dan tarif sesuai dengan aturan klinik.</span>
                </div>

                <!-- Kategori -->
                <div class="space-y-1.5">
                    <label for="kategori_layanan_id_create"
                        class="block mb-1 text-sm font-semibold text-slate-800 dark:text-slate-100">
                        Kategori Layanan
                    </label>
                    <div class="relative">
                        <i
                            class="fa-solid fa-layer-group absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <select id="kategori_layanan_id_create" name="kategori_layanan_id"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-800 text-sm
                                   focus:ring-teal-500 focus:border-teal-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100">
                            <option value="">Pilih kategori layanan</option>
                            @foreach ($dataKategoriLayanan as $kategori)
                                <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="kategori_layanan_id_create-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Nama -->
                <div class="space-y-1.5">
                    <label for="nama_layanan_create"
                        class="block mb-1 text-sm font-semibold text-slate-800 dark:text-slate-100">
                        Nama Layanan
                    </label>
                    <div class="relative">
                        <i
                            class="fa-solid fa-stethoscope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="nama_layanan" id="nama_layanan_create"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-800 text-sm
                                   focus:ring-teal-500 focus:border-teal-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100"
                            placeholder="Contoh: Konsultasi Dokter Umum">
                    </div>
                    <div id="nama_layanan_create-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Pengaturan Akses Poli -->
                <div class="space-y-3">

                    <!-- Toggle Global -->
                    <div
                        class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-4 py-3
                dark:bg-slate-700 dark:border-slate-600">
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Layanan Global</p>
                            <p class="text-xs text-slate-500 dark:text-slate-300">Jika aktif, layanan dapat diakses
                                oleh semua poli.</p>
                        </div>

                        <label class="inline-flex items-center cursor-pointer select-none">
                            <input type="checkbox" id="is_global_create" name="is_global" class="sr-only peer">
                            <div
                                class="relative w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer
                        peer-checked:bg-emerald-600 dark:bg-slate-600
                        after:content-[''] after:absolute after:top-0.5 after:left-[2px]
                        after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all
                        peer-checked:after:translate-x-full">
                            </div>
                        </label>
                    </div>
                    <div id="is_global_create-error" class="text-red-600 text-sm -mt-2"></div>

                    <!-- Pilih Poli (muncul kalau tidak global) -->
                    <div id="poli_section_create" class="space-y-1.5">
                        <label for="poli_ids_create"
                            class="block text-sm font-semibold text-slate-800 dark:text-slate-100">
                            Poli yang Bisa Mengakses Layanan
                        </label>
                        <select id="poli_ids_create" name="poli_ids[]" multiple
                            class="w-full rounded-lg border border-slate-300 bg-white text-slate-800 text-sm
         dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100">
                        </select>
                        <p class="text-[11px] text-slate-500 dark:text-slate-300">
                            Pilih satu atau beberapa poli. Jika layanan global, bagian ini akan disembunyikan.
                        </p>
                        <div id="poli_ids_create-error" class="text-red-600 text-sm mt-1"></div>
                    </div>

                </div>

                <!-- Tarif & Diskon -->
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- Harga sebelum diskon -->
                        <div class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-800 dark:text-slate-100">Harga</label>
                            <div class="relative">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500 dark:text-slate-300 text-sm">Rp</span>
                                <input type="text" name="harga_sebelum_diskon" id="harga_sebelum_diskon_create"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-800 text-sm
                                           focus:ring-teal-500 focus:border-teal-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100"
                                    placeholder="150.000">
                            </div>
                            <div id="harga_sebelum_diskon_create-error" class="text-red-600 text-sm mt-1"></div>
                        </div>

                        <!-- Jenis diskon -->
                        <div class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-800 dark:text-slate-100">Jenis
                                Diskon</label>
                            <select name="diskon_tipe" id="diskon_tipe_create"
                                class="w-full py-2.5 px-3 rounded-lg border border-slate-300 bg-white text-slate-800 text-sm
                                       focus:ring-teal-500 focus:border-teal-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100">
                                <option value="nominal">Nominal (Rp)</option>
                                <option value="persen">Persen (%)</option>
                            </select>
                        </div>

                        <!-- Diskon -->
                        <div class="space-y-1.5">
                            <label
                                class="block text-sm font-semibold text-slate-800 dark:text-slate-100">Diskon</label>
                            <div class="relative">
                                <span id="diskon_prefix_rp_create"
                                    class="hidden absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500 dark:text-slate-300 text-sm">Rp</span>

                                <input type="text" name="diskon" id="diskon_create"
                                    class="w-full pr-3 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-800 text-sm
                                           focus:ring-teal-500 focus:border-teal-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100"
                                    placeholder="0">
                            </div>
                            <p id="diskon_helper_create" class="text-[11px] text-slate-500">Isi 0 jika tidak ada
                                diskon.</p>
                            <div id="diskon_create-error" class="text-red-600 text-sm mt-1"></div>
                        </div>

                        <!-- Harga setelah diskon -->
                        <div class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-800 dark:text-slate-100">Harga Setelah
                                Diskon</label>
                            <div class="relative">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500 dark:text-slate-300 text-sm">Rp</span>
                                <input type="text" name="harga_setelah_diskon" id="harga_setelah_diskon_create"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-slate-300 bg-slate-100 text-slate-700 text-sm cursor-not-allowed
                                           dark:bg-slate-600 dark:text-slate-200"
                                    readonly>
                            </div>
                            <div id="harga_setelah_diskon_create-error" class="text-red-600 text-sm mt-1"></div>
                        </div>

                    </div>
                </div>

                <!-- FOOTER -->
                <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <button type="button" id="buttonCloseModalCreateLayanan_footer"
                        class="px-5 py-2.5 text-sm font-medium rounded-lg bg-slate-200 text-slate-800 hover:bg-slate-300
                               dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold rounded-lg bg-teal-600 text-white shadow-md hover:bg-teal-700
                               focus:ring-4 focus:ring-teal-200 dark:focus:ring-teal-800">
                        Simpan Layanan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- ===================== -->
<!-- Modal Update Layanan  -->
<!-- ===================== -->
<div id="modalUpdateLayanan" aria-hidden="true" 
    class="hidden fixed inset-0 z-50 flex items-center justify-center p-4
           bg-slate-900/60 backdrop-blur-sm overflow-y-auto overflow-x-hidden">

    <div class="w-full max-w-2xl">
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden border border-slate-100 dark:border-slate-700">

            <!-- HEADER -->
            <div
                class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-sky-500 to-teal-500 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-sky-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-pen-to-square text-white text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-slate-50">Edit Data Layanan Klinik</h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">Perbarui informasi layanan dengan benar.</p>
                    </div>
                </div>

                <button type="button" id="buttonCloseModalUpdateLayanan"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- FORM -->
            <form id="formUpdateLayanan" class="px-6 pb-4 space-y-5 bg-slate-50/60 dark:bg-slate-800"
                data-url="{{ route('layanan.update.data') }}" method="POST">
                @csrf
                <input type="hidden" id="id_update" name="id">

                <!-- Kategori -->
                <div class="space-y-1.5">
                    <label for="kategori_layanan_id_update"
                        class="block mb-1 text-sm font-semibold text-slate-800 dark:text-slate-100">Kategori
                        Layanan</label>
                    <div class="relative">
                        <i
                            class="fa-solid fa-layer-group absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <select id="kategori_layanan_id_update" name="kategori_layanan_id"
                            class="w-full pl-9 pr-3 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-800 text-sm
                                   focus:ring-teal-500 focus:border-teal-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100">
                            <option value="">Memuat kategori…</option>
                        </select>
                    </div>
                    <div id="kategori_layanan_id_update-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Nama -->
                <div class="space-y-1.5">
                    <label class="block mb-1 text-sm font-semibold text-slate-800 dark:text-slate-100">Nama
                        Layanan</label>
                    <div class="relative">
                        <i
                            class="fa-solid fa-stethoscope absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" id="nama_layanan_update" name="nama_layanan"
                            class="w-full pl-9 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-800
                                   focus:ring-teal-500 focus:border-teal-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100"
                            placeholder="Masukkan nama layanan">
                    </div>
                    <div id="nama_layanan_update-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <!-- Tarif & Diskon -->
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <!-- Harga sebelum diskon -->
                        <div class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-800 dark:text-slate-100">Harga</label>
                            <div class="relative">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500 dark:text-slate-300 text-sm">Rp</span>
                                <input type="text" name="harga_sebelum_diskon" id="harga_sebelum_diskon_update"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-800 text-sm
                                           focus:ring-teal-500 focus:border-teal-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100"
                                    placeholder="150.000">
                            </div>
                            <div id="harga_sebelum_diskon_update-error" class="text-red-600 text-sm mt-1"></div>
                        </div>

                        <!-- Jenis diskon -->
                        <div class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-800 dark:text-slate-100">Jenis
                                Diskon</label>
                            <select name="diskon_tipe" id="diskon_tipe_update"
                                class="w-full py-2.5 px-3 rounded-lg border border-slate-300 bg-white text-slate-800 text-sm
                                       focus:ring-teal-500 focus:border-teal-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100">
                                <option value="nominal">Nominal (Rp)</option>
                                <option value="persen">Persen (%)</option>
                            </select>
                        </div>

                        <!-- Diskon -->
                        <div class="space-y-1.5">
                            <label
                                class="block text-sm font-semibold text-slate-800 dark:text-slate-100">Diskon</label>
                            <div class="relative">
                                <span id="diskon_prefix_rp_update"
                                    class="hidden absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500 dark:text-slate-300 text-sm">Rp</span>

                                <input type="text" name="diskon" id="diskon_update"
                                    class="w-full pr-3 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-800 text-sm
                                           focus:ring-teal-500 focus:border-teal-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100"
                                    placeholder="0">
                            </div>
                            <p id="diskon_helper_update" class="text-[11px] text-slate-500">Isi 0 jika tidak ada
                                diskon.</p>
                            <div id="diskon_update-error" class="text-red-600 text-sm mt-1"></div>
                        </div>

                        <!-- Harga setelah diskon -->
                        <div class="space-y-1.5">
                            <label class="block text-sm font-semibold text-slate-800 dark:text-slate-100">Harga Setelah
                                Diskon</label>
                            <div class="relative">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-500 dark:text-slate-300 text-sm">Rp</span>
                                <input type="text" name="harga_setelah_diskon" id="harga_setelah_diskon_update"
                                    class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-slate-300 bg-slate-100 text-slate-700 text-sm cursor-not-allowed
                                           dark:bg-slate-600 dark:text-slate-200"
                                    readonly>
                            </div>
                            <div id="harga_setelah_diskon_update-error" class="text-red-600 text-sm mt-1"></div>
                        </div>

                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-4 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <button type="button" id="buttonCloseModalUpdateLayanan_footer"
                        class="px-5 py-2.5 text-sm font-medium rounded-lg bg-slate-200 text-slate-800 hover:bg-slate-300
                               dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold rounded-lg bg-teal-600 text-white hover:bg-teal-700 shadow-md
                               focus:ring-4 focus:ring-teal-200 dark:focus:ring-teal-800">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@vite(['resources/js/admin/layanan/data-layanan.js'])
