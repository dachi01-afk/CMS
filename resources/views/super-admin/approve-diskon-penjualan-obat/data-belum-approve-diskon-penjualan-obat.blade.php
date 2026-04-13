<section class="space-y-5">

    <!-- HEADER ATAS + CTA -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl
               bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-badge-check text-lg"></i>
            </div>

            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Approval Diskon Penjualan Obat
                </h2>

                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola permintaan diskon dari <span class="font-medium">Kasir</span>.
                    Tinjau item yang didiskon, alasan, dan total potongan sebelum
                    <span class="font-medium">Approve</span> atau <span class="font-medium">Reject</span>.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-3">
            {{-- optional badge status kecil kalau kamu mau --}}
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800 ring-1 ring-amber-200
                       dark:bg-amber-900/30 dark:text-amber-200 dark:ring-amber-800">
                    <i class="fa-solid fa-clock"></i>
                    Menunggu Approval
                </span>
            </div>
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
            <table id="tabel-belum-approve-diskon"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100 border-t border-slate-100 dark:border-slate-700">
                <thead
                    class="text-xs font-semibold uppercase bg-gradient-to-r from-sky-500 to-teal-500 text-white tracking-wide">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Nama Pasien</th>
                        <th class="px-6 py-3">Kode Transaksi</th>
                        <th class="px-6 py-3">Requested By</th>
                        <th class="px-6 py-3">Approved By</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3">Alasan</th>
                        <th class="px-6 py-3">Approved At</th>
                        <th class="px-6 py-3">Diskon Items</th>
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

<!-- ✅ MODAL DETAIL ITEM DISKON (HTML, bukan dari JS) -->
<div id="modalDetailDiskon" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50 px-4">
    <div class="relative w-full max-w-5xl">
        <div class="relative overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-slate-800">

            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-200 p-5 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-700 ring-1 ring-sky-200 dark:bg-sky-900/30 dark:text-sky-200 dark:ring-sky-800">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                            Detail Item Diskon
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Data item diskon yang diajukan kasir untuk approval.
                        </p>
                    </div>
                </div>

                <button type="button" data-close-modal="detail-diskon"
                    class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-700 dark:hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="p-5 space-y-4">

                <!-- Error box -->
                <div id="modalDetailDiskonError"
                    class="hidden rounded-xl bg-rose-50 p-3 text-sm text-rose-800 ring-1 ring-rose-200 dark:bg-rose-900/30 dark:text-rose-200 dark:ring-rose-800">
                    Error
                </div>

                <!-- Info transaksi -->
                <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200 dark:bg-slate-900/30 dark:ring-slate-700">
                    <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">

                        <div class="space-y-1">
                            <div class="text-sm text-slate-700 dark:text-slate-200">
                                Pasien: <b id="modalNamaPasien">-</b>
                            </div>
                            <div class="text-sm text-slate-700 dark:text-slate-200">
                                Kode Transaksi: <b id="modalKodeTransaksi">-</b>
                            </div>
                            <div class="text-sm text-slate-700 dark:text-slate-200">
                                Requester: <b id="modalRequester">-</b>
                            </div>
                        </div>
                    </div>

                    <div id="modalReasonWrap" class="mt-3 hidden">
                        <div class="text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1">Alasan</div>
                        <div id="modalReason"
                            class="text-sm text-slate-600 dark:text-slate-300 whitespace-pre-line rounded-xl bg-white p-3 ring-1 ring-slate-200 dark:bg-slate-800 dark:ring-slate-700">
                            -
                        </div>
                    </div>
                </div>

                <!-- Summary badges -->
                <div class="flex flex-wrap items-center gap-2">
                    <span id="modalBadgeCount"
                        class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-700 dark:text-slate-100 dark:ring-slate-600">
                        0 item
                    </span>

                    <span id="modalBadgeTotal"
                        class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-200 dark:ring-indigo-800">
                        Total: Rp 0
                    </span>

                    <span id="modalBadgePotongan"
                        class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-200 dark:ring-emerald-800">
                        Potongan: Rp 0
                    </span>

                    <span id="modalBadgeAfter"
                        class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700 ring-1 ring-sky-200 dark:bg-sky-900/30 dark:text-sky-200 dark:ring-sky-800">
                        Setelah: Rp 0
                    </span>
                </div>

                <!-- Table items -->
                <div class="rounded-2xl ring-1 ring-slate-200 overflow-hidden dark:ring-slate-700">
                    <div id="modalDetailDiskonLoading"
                        class="hidden p-4 text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-900/30">
                        Memuat data item...
                    </div>

                    <div class="max-h-[55vh] overflow-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="sticky top-0 bg-slate-50 text-xs uppercase text-slate-600 dark:bg-slate-900/30 dark:text-slate-300">
                                <tr>
                                    <th class="px-4 py-3 text-left">Jenis</th>
                                    <th class="px-4 py-3 text-left">Item</th>
                                    <th class="px-4 py-3 text-right">Qty</th>
                                    <th class="px-4 py-3 text-right">Harga</th>
                                    <th class="px-4 py-3 text-right">Subtotal</th>
                                    <th class="px-4 py-3 text-right">Diskon%</th>
                                    <th class="px-4 py-3 text-right">Potongan</th>
                                    <th class="px-4 py-3 text-right">Total</th>
                                </tr>
                            </thead>

                            <tbody id="modalItemsBody"
                                class="divide-y divide-slate-200 bg-white dark:divide-slate-700 dark:bg-slate-800">
                                <tr>
                                    <td colspan="8"
                                        class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                                        Belum ada data.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-2 border-t border-slate-200 p-5 dark:border-slate-700">
                <button type="button" data-close-modal="detail-diskon"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>

@vite(['resources/js/super-admin/approve-diskon-penjualan-obat/data-belum-approve-diskon-penjualan-obat.js'])
