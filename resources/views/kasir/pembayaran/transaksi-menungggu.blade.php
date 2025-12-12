<section class="space-y-5">

    {{-- ============== HEADER ============== --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
               bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl px-4 md:px-6 py-4 shadow-sm">

        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl
                       bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-clock-rotate-left text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Transaksi Menunggu
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Daftar transaksi pasien yang masih menunggu pembayaran atau pengambilan obat.
                    Gunakan pencarian dan pengaturan jumlah baris untuk mempermudah monitoring.
                </p>
            </div>
        </div>
    </div>

    {{-- ============== CARD TABEL ============== --}}
    <div
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl shadow-sm overflow-hidden">

        {{-- Toolbar --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                   px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">

            {{-- Page length --}}
            <div class="flex items-center gap-2 text-sm">
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>
                <select id="transaksi-menunggu-page-length"
                    class="border border-slate-300 dark:border-slate-600 text-sm rounded-lg
                               focus:ring-sky-500 focus:border-sky-500
                               bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100
                               px-2 py-1 w-28">
                    <option value="10">10 baris</option>
                    <option value="25">25 baris</option>
                    <option value="50">50 baris</option>
                    <option value="100">100 baris</option>
                </select>
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">per halaman</span>
            </div>

            {{-- Search --}}
            <div class="w-full md:w-auto">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                    </span>
                    <input type="text" id="transaksi-menunggu-search-input"
                        class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm
                                  text-slate-800 dark:text-slate-100
                                  border border-slate-300 dark:border-slate-600 rounded-lg
                                  bg-slate-50 dark:bg-slate-700
                                  focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari nama pasien, kode transaksi, atau metode pembayaran...">
                </div>
                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Contoh: <span class="italic">Nama pasien, nomor antrian, kode transaksi</span>.
                </p>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table id="transaksiMenunggu"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100
                          border-t border-slate-100 dark:border-slate-700">
                <thead
                    class="text-[11px] md:text-xs font-semibold uppercase
                           bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600
                           text-white tracking-wide">
                    <tr>
                        <th class="px-4 md:px-6 py-3">No</th>
                        <th class="px-4 md:px-6 py-3">Nama Pasien</th>
                        <th class="px-4 md:px-6 py-3">Tanggal Kunjungan</th>
                        <th class="px-4 md:px-6 py-3">Nomor Antrian</th>
                        <th class="px-4 md:px-6 py-3">Nama Obat</th>
                        <th class="px-4 md:px-6 py-3">Dosis</th>
                        <th class="px-4 md:px-6 py-3">Jumlah Obat</th>
                        <th class="px-4 md:px-6 py-3">Nama Layanan</th>
                        <th class="px-4 md:px-6 py-3">Jumlah Layanan</th>
                        <th class="px-4 md:px-6 py-3">Total</th>
                        <th class="px-4 md:px-6 py-3">Metode Pembayaran</th>
                        <th class="px-4 md:px-6 py-3">Status</th>
                        <th class="px-4 md:px-6 py-3">Kode Transaksi</th>
                        <th class="px-4 md:px-6 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    {{-- Diisi via DataTables --}}
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                   px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700
                   bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">
            <div id="transaksi-menunggu-custom-info" class="text-xs md:text-sm text-slate-600 dark:text-slate-300">
            </div>
            <ul id="transaksi-menunggu-custom-pagination"
                class="inline-flex items-center gap-0 text-sm isolate rounded-lg
                       border border-slate-200 dark:border-slate-600 overflow-hidden">
            </ul>
        </div>
    </div>

    {{-- ============== MODAL UPDATE STATUS RESEP OBAT ============== --}}
    <div id="modalBayarSekarang" aria-hidden="true"
        class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center
               w-full h-full p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto overflow-x-hidden">

        <div class="relative w-full max-w-lg">
            <div
                class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl
                       border border-slate-100 dark:border-slate-700 overflow-hidden flex flex-col max-h-[90vh]">

                {{-- Header --}}
                <div
                    class="bg-gradient-to-r from-sky-500 to-teal-600
                           px-5 md:px-6 pt-4 pb-3 flex items-start justify-between gap-3 rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <div
                            class="h-9 w-9 rounded-xl bg-sky-500 flex items-center justify-center shadow-md text-white">
                            <i class="fa-solid fa-prescription-bottle-medical text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-base md:text-lg font-semibold text-white">
                                Update Status Resep Obat
                            </h3>
                            <p class="text-[11px] text-sky-50/90 mt-0.5">
                                Atur status pengambilan obat pasien sesuai kondisi di farmasi.
                            </p>
                        </div>
                    </div>
                    <button type="button" id="buttonCloseModalUpdateStatus"
                        class="inline-flex items-center justify-center h-8 w-8 rounded-full
                               text-slate-100 hover:text-white hover:bg-white/10 transition">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                {{-- Form --}}
                <form id="formBayarSekarang"
                    class="px-5 md:px-6 pb-5 pt-4 flex flex-col gap-5 bg-slate-50/60 dark:bg-slate-800" method="POST"
                    action="{{ route('update.status.resep') }}">
                    @csrf

                    <input type="hidden" name="resep_id" id="resep_id">
                    <input type="hidden" name="obat_id" id="obat_id">

                    <div>
                        <label for="status"
                            class="block text-sm font-medium text-slate-800 dark:text-slate-100 mb-1.5">
                            Status Pengambilan Obat
                        </label>
                        <select name="status" id="status"
                            class="w-full border border-slate-300 dark:border-slate-600 text-sm rounded-lg
                                       focus:ring-sky-500 focus:border-sky-500
                                       bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100
                                       px-3 py-2">
                            <option value="Belum Diambil">Belum Diambil</option>
                            <option value="Sudah Diambil">Sudah Diambil</option>
                        </select>
                        <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                            Pastikan status sesuai dengan kondisi aktual di farmasi.
                        </p>
                    </div>

                    {{-- Footer Buttons --}}
                    <div
                        class="flex justify-end gap-3 md:gap-4 mt-4 pt-4
                               border-t border-slate-200 dark:border-slate-700">
                        <button type="button" id="closeButtonModalUpdateStatus"
                            class="px-5 md:px-6 py-2.5 text-sm font-medium
                                       text-slate-700 bg-slate-200 rounded-lg hover:bg-slate-300
                                       dark:bg-slate-600 dark:text-white dark:hover:bg-slate-500
                                       transition-all duration-200 hover:shadow-md inline-flex items-center gap-2">
                            <i class="fa-solid fa-xmark text-xs"></i>
                            <span>Tutup</span>
                        </button>
                        <button type="submit" id="updateStatusButton"
                            class="px-5 md:px-6 py-2.5 text-sm font-semibold text-white
                                       bg-gradient-to-r from-sky-500 to-teal-600 rounded-lg
                                       hover:from-sky-600 hover:to-teal-700
                                       focus:ring-2 focus:ring-sky-400 focus:outline-none
                                       transition-all duration-200 hover:shadow-md inline-flex items-center gap-2">
                            <i class="fa-solid fa-check text-xs"></i>
                            <span>Update</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@vite(['resources/js/kasir/pembayaran/transaksi-menunggu.js'])
