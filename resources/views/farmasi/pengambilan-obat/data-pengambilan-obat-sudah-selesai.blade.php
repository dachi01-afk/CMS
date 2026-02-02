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
                    Daftar resep pasien yang telah mengambil resep obat.
                    <span class="hidden sm:inline">
                        Gunakan pencarian untuk mempercepat monitoring.
                    </span>
                </p>
            </div>
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

                    <select id="suda_selesai_pageLength"
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

                        <input type="text" id="resep_obat_searchInput"
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
            <table id="pengambilanResepObatSudahSelesai"
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
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Status</th>
                        <th class="px-3 sm:px-4 md:px-6 py-3 whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
            </table>
        </div>

        <!-- Footer -->
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">
            <div id="sudah_selesai_customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>

            <ul id="sudah_selesai_customPagination"
                class="inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
            </ul>
        </div>
    </div>

</section>

<div id="modal-detail-resep-selesai" class="fixed inset-0 z-50 hidden overflow-y-auto px-4"
    style="background: rgba(0, 0, 0, 0.5);">
    <div class="flex items-center justify-center min-h-screen">
        <div class="relative bg-white rounded-lg shadow-2xl max-w-2xl w-full overflow-hidden transform transition-all">

            <div class="bg-teal-600 px-5 py-3 flex justify-between items-center">
                <div class="flex items-center gap-2 text-white">
                    <i class="fa-solid fa-file-prescription text-lg"></i>
                    <h3 class="text-sm font-bold uppercase tracking-wider">
                        Detail Resep Obat #<span id="resep-id-selesai"></span>
                    </h3>
                </div>
                <button type="button" onclick="closeModalDetail()"
                    class="text-white hover:text-gray-200 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-6">
                <div class="overflow-hidden border border-gray-200 rounded-lg shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr class="bg-gray-50">
                                <th
                                    class="px-4 py-3 text-left text-[11px] font-bold text-gray-600 uppercase tracking-wider">
                                    Nama Obat</th>
                                <th
                                    class="px-4 py-3 text-center text-[11px] font-bold text-gray-600 uppercase tracking-wider">
                                    Jumlah</th>
                                <th
                                    class="px-4 py-3 text-left text-[11px] font-bold text-gray-600 uppercase tracking-wider">
                                    Dosis</th>
                                <th
                                    class="px-4 py-3 text-left text-[11px] font-bold text-gray-600 uppercase tracking-wider">
                                    Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="resep-obat-selesai" class="bg-white divide-y divide-gray-100 text-[12px]">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-3 flex justify-end">
                <button type="button" id="btn-close-modal-selesai"
                    class="px-5 py-2 bg-gray-500 hover:bg-gray-600 text-white text-xs font-bold rounded shadow-sm transition-all flex items-center gap-2">
                    <i class="fa-solid fa-circle-xmark"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@vite(['resources/js/farmasi/pengambilan-obat/data-pengambilan-obat-sudah-selesai.js'])
