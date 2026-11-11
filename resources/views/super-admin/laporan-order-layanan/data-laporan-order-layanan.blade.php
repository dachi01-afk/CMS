<section class="space-y-5">

    @if (session('error'))
        <div
            class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700
               dark:border-red-700/60 dark:bg-red-900/40 dark:text-red-100">
            <div class="flex items-start gap-2">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <p>{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- HEADER + FILTER / EXPORT --}}
    <div
        class="flex flex-col gap-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl px-4 md:px-6 py-4 shadow-sm">

        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl
                       bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-file-invoice-dollar text-lg"></i>
            </div>

            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Transaksi Order Layanan
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Rekap pembelian layanan yang dilakukan pasien langsung ke klinik dan diinput ke sistem
                    tanpa membuat kunjungan terlebih dahulu.
                </p>
            </div>
        </div>

        {{-- Filter Layanan + Periode + Status + Export --}}
        <form action="{{ route('super.admin.laporan.order.layanan.export') }}" method="GET"
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-12 gap-2 md:gap-3 items-end w-full">

            <div class="sm:col-span-2 xl:col-span-3 remote-filter-select">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">
                    Layanan
                </label>
                <select name="layanan_id" id="layananOrderLayanan"
                    data-search-url="{{ route('super.admin.laporan.order.layanan.search.layanan') }}"
                    data-placeholder="Semua Layanan">
                    <option value=""></option>
                </select>
            </div>

            <div class="sm:col-span-1 xl:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">
                    Periode
                </label>
                <select name="periode" id="periodeOrderLayanan"
                    class="w-full border border-slate-300 dark:border-slate-600 rounded-lg
                       bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100
                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2 text-sm">
                    <option value="">Semua</option>
                    <option value="minggu">Minggu Ini</option>
                    <option value="bulan">Per Bulan</option>
                    <option value="tahun">Per Tahun</option>
                </select>
            </div>

            {{-- Label + input Bulan dibungkus satu wrapper agar bisa disembunyikan bersama --}}
            <div id="bulanOrderLayananWrapper" class="hidden sm:col-span-1 xl:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">
                    Bulan
                </label>
                <select name="bulan" id="bulanOrderLayanan"
                    class="w-full border border-slate-300 dark:border-slate-600 rounded-lg
                       bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100
                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2 text-sm">
                    <option value="">Pilih Bulan</option>
                    <option value="01">Januari</option>
                    <option value="02">Februari</option>
                    <option value="03">Maret</option>
                    <option value="04">April</option>
                    <option value="05">Mei</option>
                    <option value="06">Juni</option>
                    <option value="07">Juli</option>
                    <option value="08">Agustus</option>
                    <option value="09">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>
            </div>

            {{-- Label + input Tahun dibungkus satu wrapper agar bisa disembunyikan bersama --}}
            <div id="tahunOrderLayananWrapper" class="hidden sm:col-span-1 xl:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">
                    Tahun
                </label>
                <select name="tahun" id="tahunOrderLayanan"
                    class="w-full border border-slate-300 dark:border-slate-600 rounded-lg
                       bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100
                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2 text-sm">
                    @for ($year = now()->year; $year >= now()->year - 4; $year--)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endfor
                </select>
            </div>

            <div class="sm:col-span-2 xl:col-span-3">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">
                    Status
                </label>
                <select name="status" id="statusOrderLayanan"
                    class="w-full border border-slate-300 dark:border-slate-600 rounded-lg
                       bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100
                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2 text-sm">
                    <option value="">Semua Status</option>
                    <option value="Sudah Bayar">Sudah Bayar</option>
                    <option value="Belum Bayar">Belum Bayar</option>
                </select>
            </div>

            <div class="sm:col-span-2 xl:col-span-12 flex justify-end">
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-xl shadow-md
                           bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700
                           focus:outline-none focus:ring-2 focus:ring-sky-400">
                    <i class="fa-solid fa-file-excel text-xs"></i>
                    <span>Export Excel</span>
                </button>
            </div>
        </form>

    </div>

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">

        <div
            class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Transaksi</p>
            <p id="laporanOrderLayananTotalTransaksi" class="mt-2 text-2xl font-bold text-slate-800 dark:text-slate-50">
                0
            </p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Transaksi order layanan</p>
        </div>

        <div
            class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Pasien</p>
            <p id="laporanOrderLayananTotalPasien" class="mt-2 text-2xl font-bold text-slate-800 dark:text-slate-50">
                0
            </p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Pasien unik pada periode ini</p>
        </div>

        <div
            class="rounded-2xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/30 px-4 py-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">
                Sudah Bayar
            </p>
            <p id="laporanOrderLayananSudahBayar" class="mt-2 text-2xl font-bold text-emerald-700 dark:text-emerald-200">
                Rp 0
            </p>
            <p class="mt-1 text-xs text-emerald-600/80 dark:text-emerald-300/80">Total nilai transaksi lunas</p>
        </div>

        <div
            class="rounded-2xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/30 px-4 py-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-300">
                Belum Bayar
            </p>
            <p id="laporanOrderLayananBelumBayar" class="mt-2 text-2xl font-bold text-amber-700 dark:text-amber-200">
                Rp 0
            </p>
            <p class="mt-1 text-xs text-amber-600/80 dark:text-amber-300/80">Total nilai transaksi tertunda</p>
        </div>

    </div>

    {{-- CARD TABEL --}}
    <div
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl shadow-sm overflow-hidden">

        {{-- CONTROL BAR (PAGE LENGTH + SEARCH) --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                   px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">

            <div class="flex items-center gap-2">
                <label for="orderLayanan_pageLength"
                    class="text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">
                    Tampilkan
                </label>
                <select id="orderLayanan_pageLength"
                    class="border border-slate-300 dark:border-slate-600 text-sm rounded-lg
                               bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200
                               focus:ring-sky-500 focus:border-sky-500 px-2 py-1 w-24">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-sm text-slate-500 dark:text-slate-400 hidden sm:inline">
                    entri per halaman
                </span>
            </div>

            <div class="relative w-full md:w-72">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" id="orderLayanan_searchInput"
                    class="block w-full pl-8 pr-3 py-2 text-sm
                              border border-slate-300 dark:border-slate-600 rounded-lg
                              bg-slate-50 dark:bg-slate-900
                              text-slate-800 dark:text-slate-100
                              placeholder-slate-400
                              focus:ring-sky-500 focus:border-sky-500"
                    placeholder="Cari kode transaksi, pasien, no. EMR...">
            </div>
        </div>

        {{-- TABEL --}}
        <div class="overflow-x-auto">
            <table id="tabelLaporanOrderLayanan" class="min-w-full text-sm text-left text-slate-700 dark:text-slate-100">
                <thead class="text-xs uppercase bg-sky-500 dark:bg-sky-600 text-white tracking-wide">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Kode Transaksi</th>
                        <th class="px-6 py-3">Tanggal Order</th>
                        <th class="px-6 py-3">Nama Pasien</th>
                        <th class="px-6 py-3">No. EMR</th>
                        <th class="px-6 py-3">Metode Pembayaran</th>
                        <th class="px-6 py-3">Jumlah Item</th>
                        <th class="px-6 py-3">Subtotal</th>
                        <th class="px-6 py-3">Potongan</th>
                        <th class="px-6 py-3">Total Bayar</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Tanggal Pembayaran</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Diisi via DataTable --}}
                </tbody>
            </table>
        </div>

        {{-- FOOTER INFO + PAGINATION --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                   px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700">

            <div id="orderLayanan_customInfo" class="text-sm text-slate-600 dark:text-slate-300">
                {{-- Info custom datatable --}}
            </div>

            <ul id="orderLayanan_customPagination" class="inline-flex items-center gap-px text-sm">
                {{-- Pagination custom --}}
            </ul>
        </div>
    </div>

</section>

<!-- ✅ MODAL DETAIL TRANSAKSI ORDER LAYANAN -->
<div id="modalDetailOrderLayanan" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50 px-4">
    <div class="relative w-full max-w-4xl">
        <div class="relative overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-slate-800">

            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-200 p-5 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-700 ring-1 ring-sky-200 dark:bg-sky-900/30 dark:text-sky-200 dark:ring-sky-800">
                        <i class="fa-solid fa-file-invoice"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                            Detail Transaksi Order Layanan
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Informasi transaksi dan rincian layanan yang dibeli pasien.
                        </p>
                    </div>
                </div>

                <button type="button" data-close-modal="detail-order-layanan"
                    class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-700 dark:hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-5 space-y-4">

                <div id="detailOrderLayananError"
                    class="hidden rounded-xl bg-rose-50 p-3 text-sm text-rose-800 ring-1 ring-rose-200 dark:bg-rose-900/30 dark:text-rose-200 dark:ring-rose-800">
                </div>

                <!-- Info transaksi -->
                <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200 dark:bg-slate-900/30 dark:ring-slate-700">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3 text-sm">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kode Transaksi</div>
                            <div id="detailKodeTransaksi" class="mt-1 font-semibold text-slate-800 dark:text-slate-100">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tanggal Order</div>
                            <div id="detailTanggalOrder" class="mt-1 text-slate-700 dark:text-slate-200">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nama Pasien</div>
                            <div id="detailNamaPasien" class="mt-1 font-semibold text-slate-800 dark:text-slate-100">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">No. EMR</div>
                            <div id="detailNoEmr" class="mt-1 text-slate-700 dark:text-slate-200">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Metode Pembayaran</div>
                            <div id="detailMetode" class="mt-1 text-slate-700 dark:text-slate-200">-</div>
                        </div>
                        <div class="flex flex-wrap items-start gap-6">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Status</div>
                                <div id="detailStatus"
                                    class="mt-1 inline-flex rounded-full px-3 py-1 text-xs font-semibold bg-slate-100 text-slate-700">
                                    -
                                </div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tanggal Pembayaran</div>
                                <div id="detailTanggalPembayaran" class="mt-1 text-slate-700 dark:text-slate-200">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel rincian -->
                <div class="rounded-2xl ring-1 ring-slate-200 overflow-hidden dark:ring-slate-700">
                    <div id="detailOrderLayananLoading"
                        class="hidden p-4 text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-900/30">
                        Memuat rincian layanan...
                    </div>

                    <div class="max-h-[45vh] overflow-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="sticky top-0 bg-slate-50 text-xs uppercase text-slate-600 dark:bg-slate-900/30 dark:text-slate-300">
                                <tr>
                                    <th class="px-4 py-3 text-center w-16">No</th>
                                    <th class="px-4 py-3 text-left">Nama Layanan</th>
                                    <th class="px-4 py-3 text-right">Harga Satuan</th>
                                    <th class="px-4 py-3 text-right">Qty</th>
                                    <th class="px-4 py-3 text-right">Total per Item</th>
                                </tr>
                            </thead>

                            <tbody id="detailItemsBody"
                                class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-800">
                                <tr>
                                    <td colspan="5"
                                        class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                                        Belum ada data.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Ringkasan nilai -->
                <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200 dark:bg-slate-900/30 dark:ring-slate-700">
                    <div class="flex flex-col items-end gap-2 text-sm">
                        <div class="flex w-full max-w-sm items-center justify-between">
                            <span class="text-slate-600 dark:text-slate-300">Subtotal</span>
                            <span id="detailSubtotal" class="font-semibold text-slate-800 dark:text-slate-100">Rp 0</span>
                        </div>
                        <div class="flex w-full max-w-sm items-center justify-between">
                            <span class="text-slate-600 dark:text-slate-300">Potongan</span>
                            <span id="detailPotongan" class="font-semibold text-rose-600 dark:text-rose-300">Rp 0</span>
                        </div>
                        <div class="flex w-full max-w-sm items-center justify-between border-t border-slate-200 pt-2 dark:border-slate-700">
                            <span class="font-semibold text-slate-700 dark:text-slate-200">Total Bayar</span>
                            <span id="detailTotalBayar" class="text-base font-bold text-slate-900 dark:text-white">Rp 0</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-2 border-t border-slate-200 p-5 dark:border-slate-700">
                <button type="button" data-close-modal="detail-order-layanan"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>

@vite(['resources/js/super-admin/laporan-order-layanan/data-laporan-order-layanan.js'])
