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
                <i class="fa-solid fa-prescription-bottle-medical text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Riwayat Transaksi Obat
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Lihat riwayat penjualan dan pembayaran obat pasien di kasir.
                </p>
            </div>
        </div>
    </div>

    {{-- ============== CARD TABEL ============== --}}
    <div
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl shadow-sm overflow-hidden">

        {{-- Toolbar: page length + search --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                   px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">

            {{-- Page length --}}
            <div class="flex items-center gap-2 text-sm">
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>
                <select id="transaksi-obat-page-length"
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
                    <input type="text" id="transaksi-obat-search-input"
                        class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm
                               text-slate-800 dark:text-slate-100
                               border border-slate-300 rounded-lg
                               bg-slate-50 dark:bg-slate-700
                               focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari nama pasien, obat, atau kode transaksi...">
                </div>
                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Contoh: <span class="italic">Nama pasien, nama obat, kode transaksi</span>.
                </p>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table id="riwayatTransaksiObatTable"
                class="w-full min-w-[1100px] text-sm text-left text-slate-700 dark:text-slate-100
                       border-t border-slate-100 dark:border-slate-700">
                <thead
                    class="text-[11px] md:text-xs font-semibold uppercase
                           bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600
                           text-white tracking-wide">
                    <tr>
                        <th class="px-4 md:px-6 py-3">No</th>
                        <th class="px-4 md:px-6 py-3 whitespace-nowrap">Nama Pasien</th>
                        <th class="px-4 md:px-6 py-3 whitespace-nowrap">Nama Obat</th>
                        <th class="px-4 md:px-6 py-3">Dosis</th>
                        <th class="px-4 md:px-6 py-3 whitespace-nowrap">Jumlah Obat</th>
                        <th class="px-4 md:px-6 py-3 whitespace-nowrap">Sub Total</th>
                        <th class="px-4 md:px-6 py-3 whitespace-nowrap">Metode Pembayaran</th>
                        <th class="px-4 md:px-6 py-3 whitespace-nowrap">Kode Transaksi</th>
                        <th class="px-4 md:px-6 py-3 whitespace-nowrap">Tanggal Transaksi</th>
                        <th class="px-4 md:px-6 py-3">Status</th>
                        <th class="px-4 md:px-6 py-3 whitespace-nowrap">Bukti Pembayaran</th>
                        <th class="px-4 md:px-6 py-3 text-center whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    {{-- Diisi via DataTables --}}
                </tbody>
            </table>
        </div>

        {{-- Footer: info + pagination --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                   px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700
                   bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">
            <div id="transaksi-obat-custom-info" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>
            <ul id="transaksi-obat-custom-paginate"
                class="inline-flex items-center gap-0 text-sm isolate rounded-lg
                       border border-slate-200 dark:border-slate-600 overflow-hidden">
            </ul>
        </div>
    </div>

</section>

{{-- ============== MODAL FORM TRANSAKSI OBAT ============== --}}
<div id="modalJualObat" tabindex="-1" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center 
           w-full h-full p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto overflow-x-hidden">
    <div class="relative w-full max-w-xl max-h-full">
        <div
            class="relative bg-white rounded-2xl shadow-2xl dark:bg-slate-800
                   border border-slate-100 dark:border-slate-700 flex flex-col overflow-hidden">

            {{-- Header --}}
            <div
                class="flex items-center justify-between px-5 md:px-6 pt-4 pb-3
                       bg-gradient-to-r from-sky-500 to-teal-600">
                <div>
                    <h3 class="text-base md:text-lg font-semibold text-white">
                        Form Transaksi Obat
                    </h3>
                    <p class="text-[11px] text-sky-50/90 mt-0.5">
                        Pilih obat dan pasien untuk membuat transaksi penjualan obat.
                    </p>
                </div>
                <button type="button" id="closeModalBtn"
                    class="text-slate-100 bg-transparent hover:bg-white/10 hover:text-white
                           rounded-full text-sm w-8 h-8 inline-flex justify-center items-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Form --}}
            <form action="{{ route('obat.pesan.obat') }}" method="POST" class="px-5 md:px-6 py-4 space-y-4"
                id="form-transaksi-obat">
                @csrf
                <input type="hidden" name="tanggal_kunjungan" id="tanggal_kunjungan">

                {{-- Cari Obat --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-slate-800 dark:text-slate-100">
                        Cari Obat
                    </label>
                    <input type="text" id="search_obat" placeholder="Ketik nama obat..."
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg
                               w-full px-3 py-2.5 focus:ring-sky-500 focus:border-sky-500
                               dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50">

                    <div id="obat_results"
                        class="mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600
                               rounded-lg shadow max-h-40 overflow-y-auto hidden text-sm">
                        {{-- hasil pencarian obat --}}
                    </div>
                </div>

                {{-- Tabel Daftar Obat yang Dipilih --}}
                <div class="mt-2">
                    <label class="block mb-2 text-sm font-medium text-slate-800 dark:text-slate-100">
                        Daftar Obat
                    </label>
                    <div class="border border-slate-200 dark:border-slate-600 rounded-xl overflow-hidden">
                        <table class="w-full text-sm text-left text-slate-700 dark:text-slate-300">
                            <thead class="bg-slate-100 dark:bg-slate-700/80">
                                <tr>
                                    <th class="px-3 py-2">Nama Obat</th>
                                    <th class="px-3 py-2">Stok</th>
                                    <th class="px-3 py-2">Jumlah</th>
                                    <th class="px-3 py-2 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="selected_obat_list">
                                {{-- Obat yang ditambahkan akan muncul di sini --}}
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Cari Pasien --}}
                <div>
                    <label class="block mb-2 text-sm font-medium text-slate-800 dark:text-slate-100">
                        Cari Pasien
                    </label>
                    <input type="text" id="search_pasien" name="search_pasien" placeholder="Ketik nama pasien..."
                        class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg
                               w-full px-3 py-2.5 focus:ring-sky-500 focus:border-sky-500
                               dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50">
                    <div id="search_results"
                        class="mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600
                               rounded-lg shadow max-h-40 overflow-y-auto hidden text-sm">
                        {{-- hasil pencarian --}}
                    </div>
                </div>

                {{-- Data Pasien --}}
                <div id="pasien_data"
                    class="hidden space-y-1 text-sm text-slate-700 dark:text-slate-300 border border-slate-100
                           dark:border-slate-600 rounded-xl px-3 py-3 bg-slate-50/70 dark:bg-slate-800/70">
                    <input type="hidden" name="pasien_id" id="pasien_id">
                    <p><span class="font-semibold">Nama:</span> <span id="nama_pasien"></span></p>
                    <p><span class="font-semibold">Alamat:</span> <span id="alamat_pasien"></span></p>
                    <p><span class="font-semibold">Jenis Kelamin:</span> <span id="jk_pasien"></span></p>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-3 border-t border-slate-200 dark:border-slate-700 pt-4 mt-4 pb-1">
                    <button type="button" id="btn-close-modal-transaksi-obat"
                        class="px-4 py-2 text-sm font-medium text-slate-700 bg-slate-200 rounded-lg
                               hover:bg-slate-300 dark:bg-slate-600 dark:hover:bg-slate-500
                               text-slate-800 dark:text-white inline-flex items-center gap-2 transition">
                        <i class="fa-solid fa-xmark text-xs"></i>
                        <span>Tutup</span>
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-semibold bg-gradient-to-r from-sky-500 to-teal-600
                               text-white rounded-lg hover:from-sky-600 hover:to-teal-700
                               focus:ring-2 focus:ring-sky-400 inline-flex items-center gap-2 transition">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span>Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@vite(['resources/js/kasir/pembayaran/riwayat-transaksi-obat.js'])
