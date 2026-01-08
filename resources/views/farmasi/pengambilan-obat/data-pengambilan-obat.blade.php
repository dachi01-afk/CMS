{{-- =========================
   PENGAMBILAN RESEP OBAT (REDESIGN)
   ========================= --}}

<section class="space-y-5">

    {{-- ============== HEADER ============== --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">

        {{-- KIRI: ICON + TITLE --}}
        <div class="flex items-start gap-3 md:flex-1">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl
                   bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md shrink-0">
                <i class="fa-solid fa-prescription-bottle-medical text-lg"></i>
            </div>

            <div class="min-w-0">
                <h2 class="text-lg sm:text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Pengambilan Resep Obat
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                    Daftar resep pasien yang perlu diproses pengambilan obat.
                    <span class="hidden sm:inline">
                        Gunakan pencarian untuk mempercepat monitoring.
                    </span>
                </p>
            </div>
        </div>

        {{-- KANAN: TOMBOL --}}
        <div class="flex justify-center md:justify-end">
            <button id="buttonModalCreateResep" type="button"
                class="inline-flex items-center gap-2 px-5 py-2.5
                   text-sm font-semibold text-white rounded-xl shadow-md
                   bg-gradient-to-r from-sky-500 to-teal-600
                   hover:from-sky-600 hover:to-teal-700
                   focus:outline-none focus:ring-2 focus:ring-sky-400">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Resep</span>
            </button>
        </div>
    </div>


    {{-- ============== CARD TABEL ============== --}}
    <div
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl shadow-sm overflow-hidden">

        {{-- Toolbar --}}
        <div class="px-3 sm:px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">
            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">

                {{-- Page length --}}
                <div class="flex items-center gap-2 text-sm w-full md:w-auto">
                    <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>

                    <select id="custom_pageLength"
                        class="w-full sm:w-40 md:w-32 border border-slate-300 dark:border-slate-600 text-sm rounded-lg
                               focus:ring-sky-500 focus:border-sky-500
                               bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100
                               px-2 py-2">
                        <option value="10">10 baris</option>
                        <option value="25">25 baris</option>
                        <option value="50">50 baris</option>
                        <option value="100">100 baris</option>
                    </select>

                    <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">/ halaman</span>
                </div>

                {{-- Search --}}
                <div class="w-full md:w-auto">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                        </span>

                        <input type="text" id="obat_searchInput"
                            class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm
                                   text-slate-800 dark:text-slate-100
                                   border border-slate-300 dark:border-slate-600 rounded-lg
                                   bg-slate-50 dark:bg-slate-700
                                   focus:ring-sky-500 focus:border-sky-500"
                            placeholder="Cari dokter, pasien, antrian, obat...">
                    </div>

                    <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500 hidden sm:block">
                        Contoh: <span class="italic">Nama pasien, nomor antrian, nama obat</span>.
                    </p>
                </div>

            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table id="pengambilanResepObat"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100
                       border-t border-slate-100 dark:border-slate-700 whitespace-nowrap">
                <thead
                    class="text-[11px] md:text-xs font-semibold uppercase
                           bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600
                           text-white tracking-wide">
                    <tr>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">No</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Nama Pasien</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Nama Poli</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Nama Dokter</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Nomor Antrian</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Tanggal Kunjungan</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Nama Obat</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Jumlah Obat</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Keterangan</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Status</th>

                        {{-- Sticky Action --}}
                        <th
                            class="px-3 sm:px-4 md:px-6 py-3 text-center whitespace-nowrap
                                   sticky right-0 z-10
                                   bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600">
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                   px-3 sm:px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700
                   bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">

            <div id="obat_customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>

            {{-- Pagination aman di HP --}}
            <div class="w-full md:w-auto overflow-x-auto">
                <ul id="obat_customPagination"
                    class="min-w-max inline-flex items-center gap-0 text-sm isolate rounded-lg
                           border border-slate-200 dark:border-slate-600 overflow-hidden">
                </ul>
            </div>
        </div>
    </div>

</section>

{{-- =========================
   MODAL CREATE RESEP
   ========================= --}}
<div id="modalCreateResep" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center
           w-full h-full p-3 sm:p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto overflow-x-hidden">

    <div class="relative w-full max-w-none md:max-w-4xl lg:max-w-6xl">
        <div
            class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl
                   border border-slate-100 dark:border-slate-700 overflow-hidden flex flex-col max-h-[92vh]">

            {{-- Header --}}
            <div
                class="bg-gradient-to-r from-sky-500 to-teal-600 px-4 sm:px-5 md:px-6 py-4
                        flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-white/15 flex items-center justify-center text-white">
                        <i class="fa-solid fa-file-prescription text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base md:text-lg font-semibold text-white">
                            Tambah Resep Obat
                        </h3>
                        <p class="text-[11px] text-white/90 mt-0.5">
                            Isi data resep & tambahkan daftar obat. Bisa tambah baris obat lebih dari 1.
                        </p>
                    </div>
                </div>

                <button type="button" id="btnCloseModalCreateResepTop"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-full
                           text-white/90 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Body --}}
            <form id="formCreateResep" class="px-4 sm:px-5 md:px-6 py-5 overflow-y-auto">
                @csrf

                {{-- Info umum (Assist-like) --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <div>
                        <label class="block text-sm text-slate-600 dark:text-slate-200 mb-1">Tanggal Resep</label>
                        <input type="date" id="tanggal_resep" name="tanggal_resep"
                            value="{{ now()->format('Y-m-d') }}"
                            class="w-full border-0 border-b border-slate-300 dark:border-slate-600
                   bg-transparent px-0 py-2 text-sm focus:ring-0 focus:border-sky-500">
                    </div>

                    <div>
                        <label class="block text-sm text-slate-600 dark:text-slate-200 mb-1">Pilih Dokter</label>
                        <select id="dokter_id" name="dokter_id"
                            class="tom-dokter w-full border-0 border-b border-slate-300 dark:border-slate-600
                   bg-transparent px-0 py-2 text-sm focus:ring-0 focus:border-sky-500">
                            <option value="">-- Pilih Dokter --</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm text-slate-600 dark:text-slate-200 mb-1">Cari Pasien</label>
                        <select id="pasien_id" name="pasien_id"
                            class="tom-pasien w-full border-0 border-b border-slate-300 dark:border-slate-600
                   bg-transparent px-0 py-2 text-sm focus:ring-0 focus:border-sky-500">
                            <option value="">-- Cari Pasien --</option>
                        </select>
                    </div>

                    {{-- ✅ sesuai permintaan: kirim kunjungan_id kosong --}}
                    <input type="hidden" name="kunjungan_id" value="">
                </div>


                {{-- Divider --}}
                <div class="my-5 border-t border-slate-200 dark:border-slate-700"></div>

                {{-- Section Obat --}}
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h4 class="text-sm md:text-base font-semibold text-slate-800 dark:text-slate-100">
                            Daftar Obat
                        </h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Tambahkan 1 atau lebih obat. Klik “Tambah Obat” untuk menambah baris.
                        </p>
                    </div>

                    <button type="button" id="btnTambahObat"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold
                               text-white rounded-xl shadow-md
                               bg-gradient-to-r from-sky-500 to-teal-600
                               hover:from-sky-600 hover:to-teal-700
                               focus:outline-none focus:ring-2 focus:ring-sky-400">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Tambah Obat</span>
                    </button>
                </div>

                {{-- Container dynamic rows --}}
                <div id="obatRows" class="mt-4 space-y-3"></div>

                {{-- Footer Buttons --}}
                <div
                    class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-700
                            flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
                    <button type="button" id="btnCloseModalCreateResepBottom"
                        class="w-full sm:w-auto px-5 py-2.5 text-sm font-medium
                               text-slate-700 bg-slate-200 rounded-xl hover:bg-slate-300
                               dark:bg-slate-600 dark:text-white dark:hover:bg-slate-500
                               transition inline-flex items-center justify-center gap-2">
                        <i class="fa-solid fa-xmark text-xs"></i>
                        <span>Tutup</span>
                    </button>

                    <button type="submit" id="btnSubmitCreateResep"
                        class="w-full sm:w-auto px-5 py-2.5 text-sm font-semibold text-white rounded-xl shadow-md
                               bg-gradient-to-r from-sky-500 to-teal-600
                               hover:from-sky-600 hover:to-teal-700
                               focus:outline-none focus:ring-2 focus:ring-sky-400
                               inline-flex items-center justify-center gap-2">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span>Simpan Resep</span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- =========================
   MODAL UPDATE RESEP
   ========================= --}}
<div id="modalUpdateResep" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center
           w-full h-full p-3 sm:p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto overflow-x-hidden">

    <div class="relative w-full max-w-none md:max-w-4xl lg:max-w-6xl">
        <div
            class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl
                   border border-slate-100 dark:border-slate-700 overflow-hidden flex flex-col max-h-[92vh]">

            {{-- Header --}}
            <div class="bg-gradient-to-r from-sky-500 to-teal-600 px-4 sm:px-5 md:px-6 py-4
                        flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-white/15 flex items-center justify-center text-white">
                        <i class="fa-solid fa-pen-to-square text-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base md:text-lg font-semibold text-white">Update Resep Obat</h3>
                        <p class="text-[11px] text-white/90 mt-0.5">
                            Edit daftar obat, jumlah, dosis, dan keterangan.
                        </p>
                    </div>
                </div>

                <button type="button" id="btnCloseModalUpdateResepTop"
                    class="inline-flex items-center justify-center h-9 w-9 rounded-full
                           text-white/90 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Body --}}
            <form id="formUpdateResep" class="px-4 sm:px-5 md:px-6 py-5 overflow-y-auto">
                @csrf
                

                <input type="hidden" id="update_resep_id" name="resep_id" value="">

                {{-- Info readonly --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm text-slate-600 dark:text-slate-200 mb-1">Tanggal</label>
                        <input type="date" id="update_tanggal_resep" readonly
                            class="w-full border-0 border-b border-slate-300 dark:border-slate-600 bg-transparent
                                   px-0 py-2 text-sm focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-600 dark:text-slate-200 mb-1">Pasien</label>
                        <input type="text" id="update_nama_pasien" readonly
                            class="w-full border-0 border-b border-slate-300 dark:border-slate-600 bg-transparent
                                   px-0 py-2 text-sm focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-600 dark:text-slate-200 mb-1">Poli</label>
                        <input type="text" id="update_nama_poli" readonly
                            class="w-full border-0 border-b border-slate-300 dark:border-slate-600 bg-transparent
                                   px-0 py-2 text-sm focus:ring-0">
                    </div>
                    <div>
                        <label class="block text-sm text-slate-600 dark:text-slate-200 mb-1">Dokter</label>
                        <input type="text" id="update_nama_dokter" readonly
                            class="w-full border-0 border-b border-slate-300 dark:border-slate-600 bg-transparent
                                   px-0 py-2 text-sm focus:ring-0">
                    </div>
                </div>

                <div class="my-5 border-t border-slate-200 dark:border-slate-700"></div>

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h4 class="text-sm md:text-base font-semibold text-slate-800 dark:text-slate-100">
                            Daftar Obat
                        </h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400">
                            Anda bisa menambah / menghapus baris obat.
                        </p>
                    </div>

                    <button type="button" id="btnTambahObatUpdate"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold
                               text-white rounded-xl shadow-md bg-gradient-to-r from-sky-500 to-teal-600
                               hover:from-sky-600 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-sky-400">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Tambah Obat</span>
                    </button>
                </div>

                <div id="obatRowsUpdate" class="mt-4 space-y-3"></div>

                <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-700
                            flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
                    <button type="button" id="btnCloseModalUpdateResepBottom"
                        class="w-full sm:w-auto px-5 py-2.5 text-sm font-medium
                               text-slate-700 bg-slate-200 rounded-xl hover:bg-slate-300
                               dark:bg-slate-600 dark:text-white dark:hover:bg-slate-500 transition">
                        <i class="fa-solid fa-xmark text-xs"></i> Tutup
                    </button>

                    <button type="submit" id="btnSubmitUpdateResep"
                        class="w-full sm:w-auto px-5 py-2.5 text-sm font-semibold text-white rounded-xl shadow-md
                               bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                               focus:outline-none focus:ring-2 focus:ring-sky-400">
                        <i class="fa-solid fa-check text-xs"></i> Simpan Perubahan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@vite(['resources/js/farmasi/pengambilan-obat/data-pengambilan-obat.js'])
