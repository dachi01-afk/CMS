{{-- resources/views/kasir/metode-pembayaran/metode-pembayaran.blade.php --}}

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
                <i class="fa-solid fa-wallet text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Metode Pembayaran
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola daftar metode pembayaran yang tersedia di kasir, seperti tunai, transfer, dan lainnya.
                </p>
            </div>
        </div>

        {{-- CTA: Tambah Data --}}
        <div class="flex items-center gap-2 md:gap-3">
            <button id="buttonOpenModalCreateMetodePembayaran" type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-xl shadow-md
                       bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                       focus:outline-none focus:ring-2 focus:ring-sky-400">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Metode</span>
            </button>
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
                <select id="metode-pembayaran-page-length"
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
                    <input type="text" id="metode-pembayaran-search-input"
                        class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm
                                  text-slate-800 dark:text-slate-100
                                  border border-slate-300 dark:border-slate-600 rounded-lg
                                  bg-slate-50 dark:bg-slate-700
                                  focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari nama metode pembayaran...">
                </div>
                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Contoh: <span class="italic">Cash, Transfer, QRIS</span>.
                </p>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table id="metodePembayaran"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100
                          border-t border-slate-100 dark:border-slate-700">
                <thead
                    class="text-[11px] md:text-xs font-semibold uppercase
                           bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600
                           text-white tracking-wide">
                    <tr>
                        <th class="px-4 md:px-6 py-3">No</th>
                        <th class="px-4 md:px-6 py-3">Metode Pembayaran</th>
                        <th class="px-4 md:px-6 py-3">Action</th>
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
            <div id="metode-pembayaran-custom-info" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>
            <ul id="metode-pembayaran-custom-pagination"
                class="inline-flex items-center gap-0 text-sm isolate rounded-lg
                       border border-slate-200 dark:border-slate-600 overflow-hidden">
            </ul>
        </div>
    </div>

</section>

{{-- ============== MODAL CREATE METODE PEMBAYARAN ============== --}}
<div id="modalCreateMetodePembayaran" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center 
               w-full h-full p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm
               overflow-y-auto overflow-x-hidden">

    <div class="relative w-full max-w-xl">
        <div
            class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl
                       border border-slate-100 dark:border-slate-700 overflow-hidden flex flex-col max-h-[90vh]">

            {{-- Header --}}
            <div
                class="bg-gradient-to-r from-sky-500 to-teal-600
                           px-5 md:px-6 pt-4 pb-3 flex items-start justify-between gap-3 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-xl bg-sky-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-plus text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-white">
                            Tambah Metode Pembayaran
                        </h3>
                        <p class="text-[11px] text-sky-50/90 mt-0.5">
                            Masukkan nama metode pembayaran baru yang dapat digunakan di kasir.
                        </p>
                    </div>
                </div>
                <button type="button" id="buttonCloseModalCreateMetodePembayaranHeader"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full
                               text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Form --}}
            <form id="formCreateMetodePembayaran"
                class="px-5 md:px-6 pb-5 pt-4 flex flex-col gap-5 bg-slate-50/60 dark:bg-slate-800"
                data-url="{{ route('kasir.crate.data.metode.pembayaran') }}" method="POST">
                @csrf

                <div>
                    <label for="nama_metode_create"
                        class="block mb-2 text-sm font-medium text-slate-800 dark:text-slate-100">
                        Metode Pembayaran
                    </label>
                    <input type="text" name="nama_metode" id="nama_metode_create"
                        class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg
                                      focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                      dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                        placeholder="Contoh: Cash, Transfer BCA, QRIS" required>
                    <div id="nama_metode-error" class="text-red-600 text-xs md:text-sm mt-1"></div>
                </div>

                {{-- Buttons --}}
                <div
                    class="flex justify-end gap-3 md:gap-4 mt-4 pt-4
                               border-t border-slate-200 dark:border-slate-700">
                    <button type="button" id="buttonCloseModalCreateMetodePembayaran"
                        class="px-5 md:px-6 py-2.5 text-sm font-medium
                                       text-slate-700 bg-slate-200 rounded-lg hover:bg-slate-300
                                       dark:bg-slate-600 dark:text-white dark:hover:bg-slate-500
                                       transition-all duration-200 hover:shadow-md inline-flex items-center gap-2">
                        <i class="fa-solid fa-xmark text-xs"></i>
                        <span>Tutup</span>
                    </button>
                    <button type="submit" id="saveJadwalButton"
                        class="px-5 md:px-6 py-2.5 text-sm font-semibold text-white
                                       bg-gradient-to-r from-sky-500 to-teal-600 rounded-lg
                                       hover:from-sky-600 hover:to-teal-700
                                       focus:ring-2 focus:ring-sky-400 focus:outline-none
                                       transition-all duration-200 hover:shadow-md inline-flex items-center gap-2">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span>Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============== MODAL UPDATE METODE PEMBAYARAN ============== --}}
<div id="modalUpdateMetodePembayaran" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center 
               w-full h-full p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm
               overflow-y-auto overflow-x-hidden">

    <div class="relative w-full max-w-xl">
        <div
            class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl
                       border border-slate-100 dark:border-slate-700 overflow-hidden flex flex-col max-h-[90vh]">

            {{-- Header --}}
            <div
                class="bg-gradient-to-r from-teal-500 to-sky-600
                           px-5 md:px-6 pt-4 pb-3 flex items-start justify-between gap-3 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-xl bg-teal-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-white">
                            Edit Metode Pembayaran
                        </h3>
                        <p class="text-[11px] text-sky-50/90 mt-0.5">
                            Perbarui nama metode pembayaran yang sudah tersedia.
                        </p>
                    </div>
                </div>
                <button type="button" id="buttonCloseModalUpdateMetodePembayaranHeader"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full
                               text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Form --}}
            <form id="formUpdateMetodePembayaran"
                class="px-5 md:px-6 pb-5 pt-4 flex flex-col gap-5 bg-slate-50/60 dark:bg-slate-800"
                data-url="{{ route('kasir.update.data.metode.pembayaran') }}" method="POST">
                @csrf
                <input type="hidden" id="id_update" name="id">

                <div>
                    <label for="nama_metode_update"
                        class="block mb-2 text-sm font-medium text-slate-800 dark:text-slate-100">
                        Metode Pembayaran
                    </label>
                    <input type="text" name="nama_metode" id="nama_metode_update"
                        class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-lg
                                      focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                      dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                        placeholder="Contoh: Cash, Transfer BCA, QRIS" required>
                    <div id="nama_metode_update-error" class="text-red-600 text-xs md:text-sm mt-1"></div>
                </div>

                <div
                    class="flex justify-end gap-3 md:gap-4 mt-4 pt-4
                               border-t border-slate-200 dark:border-slate-700">
                    <button type="button" id="buttonCloseModalUpdateMetodePembayaran"
                        class="px-5 md:px-6 py-2.5 text-sm font-medium
                                       text-slate-700 bg-slate-200 rounded-lg hover:bg-slate-300
                                       dark:bg-slate-600 dark:text-white dark:hover:bg-slate-500
                                       transition-all duration-200 hover:shadow-md inline-flex items-center gap-2">
                        <i class="fa-solid fa-xmark text-xs"></i>
                        <span>Tutup</span>
                    </button>
                    <button type="submit" id="updateJadwalButton"
                        class="px-5 md:px-6 py-2.5 text-sm font-semibold text-white
                                       bg-gradient-to-r from-teal-500 to-sky-600 rounded-lg
                                       hover:from-teal-600 hover:to-sky-700
                                       focus:ring-2 focus:ring-teal-400 focus:outline-none
                                       transition-all duration-200 hover:shadow-md inline-flex items-center gap-2">
                        <i class="fa-solid fa-check text-xs"></i>
                        <span>Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@vite(['resources/js/kasir/metode-pembayaran/metode-pembayaran.js'])
