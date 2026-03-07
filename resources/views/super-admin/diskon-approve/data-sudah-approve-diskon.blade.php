<section class="space-y-5">

    <div
        class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-4 shadow-sm dark:border-slate-700 dark:bg-slate-800 md:flex-row md:items-center md:justify-between md:px-6">
        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-badge-check text-lg"></i>
            </div>

            <div>
                <h2 class="text-xl font-bold text-slate-800 dark:text-slate-50 md:text-2xl">
                    Data Diskon Sudah Diapprove
                </h2>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Menampilkan riwayat permintaan diskon yang sudah disetujui oleh
                    <span class="font-medium">Manager / Super Admin</span>.
                    Anda dapat melihat detail item, alasan pengajuan, dan total potongan yang telah diapprove.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-3">
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-200 dark:ring-emerald-800">
                    <i class="fa-solid fa-circle-check"></i>
                    Sudah Diapprove
                </span>
            </div>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">

        <div
            class="flex flex-col gap-3 border-b border-slate-200 px-4 py-3 dark:border-slate-700 md:flex-row md:items-center md:justify-between md:px-6">

            <div class="flex items-center gap-2 text-sm">
                <span class="hidden text-slate-600 dark:text-slate-300 sm:inline">Tampil</span>
                <select id="sudahApprove-pageLength"
                    class="w-28 rounded-lg border border-slate-300 bg-white px-2 py-1 text-sm text-slate-800 focus:border-sky-500 focus:ring-sky-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100">
                    <option value="10">10 baris</option>
                    <option value="25">25 baris</option>
                    <option value="50">50 baris</option>
                    <option value="100">100 baris</option>
                </select>
                <span class="hidden text-slate-600 dark:text-slate-300 sm:inline">per halaman</span>
            </div>

            <div class="w-full md:w-auto">
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <i class="fa-solid fa-magnifying-glass text-xs text-slate-400"></i>
                    </span>
                    <input type="text" id="sudahApprove-searchInput"
                        class="block w-full rounded-lg border border-slate-300 bg-slate-50 py-2 pl-9 pr-3 text-sm text-slate-800 focus:border-sky-500 focus:ring-sky-500 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 md:w-80"
                        placeholder="Cari nama pasien, kode transaksi, requester, approver, atau alasan...">
                </div>
                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Contoh: <span class="italic">Budi, TRX-0001, Kasir A, Manager, Diskon member</span>.
                </p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table id="tabel-sudah-approve-diskon" data-url="{{ route('super.admin.data.sudah.approve') }}"
                class="w-full border-t border-slate-100 text-left text-sm text-slate-700 dark:border-slate-700 dark:text-slate-100">
                <thead
                    class="bg-gradient-to-r from-sky-500 to-teal-500 text-xs font-semibold uppercase tracking-wide text-white">
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

        <div
            class="flex flex-col gap-3 rounded-b-2xl border-t border-slate-200 bg-slate-50/70 px-4 py-3 dark:border-slate-700 dark:bg-slate-800/60 md:flex-row md:items-center md:justify-between md:px-6">
            <div id="sudahApprove-customInfo" class="text-xs text-slate-600 dark:text-slate-300 md:text-sm"></div>

            <ul id="sudahApprove-customPagination"
                class="isolate inline-flex items-center gap-0 overflow-hidden rounded-lg border border-slate-200 text-sm dark:border-slate-600">
            </ul>
        </div>
    </div>

</section>

<div id="modalDetailDiskonSudahApprove" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
    <div class="relative w-full max-w-5xl">
        <div class="relative overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-slate-800">

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
                            Data item diskon yang sudah diapprove.
                        </p>
                    </div>
                </div>

                <button type="button" data-close-modal="detail-diskon-sudah-approve"
                    class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-700 dark:hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="space-y-4 p-5">
                <div id="modalDetailDiskonErrorSudahApprove"
                    class="hidden rounded-xl bg-rose-50 p-3 text-sm text-rose-800 ring-1 ring-rose-200 dark:bg-rose-900/30 dark:text-rose-200 dark:ring-rose-800">
                    Error
                </div>

                <div class="rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-200 dark:bg-slate-900/30 dark:ring-slate-700">
                    <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between">
                        <div class="space-y-1">
                            <div class="text-sm text-slate-700 dark:text-slate-200">
                                Pasien: <b id="modalNamaPasienSudahApprove">-</b>
                            </div>
                            <div class="text-sm text-slate-700 dark:text-slate-200">
                                Kode Transaksi: <b id="modalKodeTransaksiSudahApprove">-</b>
                            </div>
                            <div class="text-sm text-slate-700 dark:text-slate-200">
                                Requester: <b id="modalRequesterSudahApprove">-</b>
                            </div>
                        </div>
                    </div>

                    <div id="modalReasonWrapSudahApprove" class="mt-3 hidden">
                        <div class="mb-1 text-xs font-semibold text-slate-700 dark:text-slate-200">Alasan</div>
                        <div id="modalReasonSudahApprove"
                            class="whitespace-pre-line rounded-xl bg-white p-3 text-sm text-slate-600 ring-1 ring-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700">
                            -
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span id="modalBadgeCountSudahApprove"
                        class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-700 dark:text-slate-100 dark:ring-slate-600">
                        0 item
                    </span>

                    <span id="modalBadgeTotalSudahApprove"
                        class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-indigo-200 dark:bg-indigo-900/30 dark:text-indigo-200 dark:ring-indigo-800">
                        Total: Rp 0
                    </span>

                    <span id="modalBadgePotonganSudahApprove"
                        class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-200 dark:ring-emerald-800">
                        Potongan: Rp 0
                    </span>

                    <span id="modalBadgeAfterSudahApprove"
                        class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold text-sky-700 ring-1 ring-sky-200 dark:bg-sky-900/30 dark:text-sky-200 dark:ring-sky-800">
                        Setelah: Rp 0
                    </span>
                </div>

                <div class="overflow-hidden rounded-2xl ring-1 ring-slate-200 dark:ring-slate-700">
                    <div id="modalDetailDiskonLoadingSudahApprove"
                        class="hidden bg-slate-50 p-4 text-sm text-slate-600 dark:bg-slate-900/30 dark:text-slate-300">
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

                            <tbody id="modalItemsBodySudahApprove"
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

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 p-5 dark:border-slate-700">
                <button type="button" data-close-modal="detail-diskon-sudah-approve"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>

@vite(['resources/js/super-admin/data-sudah-approve-diskon.js'])
