<x-mycomponents.layout>

    <div class="space-y-5">

        {{-- Header --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
            <div class="flex items-start gap-3">
                <div
                    class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white shadow-md shrink-0">
                    <i class="fa-solid fa-clipboard-check text-lg"></i>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                        Pengajuan Perubahan Data
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Semua pengajuan edit dan hapus dari <span class="font-medium">Admin</span> (layanan) dan
                        <span class="font-medium">Farmasi</span> (obat). Perubahan baru diterapkan setelah disetujui.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800 ring-1 ring-amber-200 dark:bg-amber-900/30 dark:text-amber-200 dark:ring-amber-800">
                    <i class="fa-solid fa-clock"></i>
                    {{ $totalMenunggu }} menunggu
                </span>
            </div>
        </div>

        {{-- Ringkasan: kartu juga bisa diklik sebagai filter cepat --}}
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
            <button type="button" data-status-filter="menunggu" aria-pressed="false"
                title="Tampilkan pengajuan yang menunggu"
                class="status-card group relative cursor-pointer overflow-hidden rounded-2xl border border-amber-200/80 bg-white p-4 text-left shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-400 focus-visible:ring-offset-2 dark:border-amber-500/25 dark:bg-slate-800 dark:hover:border-amber-500/50 dark:focus-visible:ring-offset-slate-900 [&.is-active]:border-amber-400 [&.is-active]:shadow-[0_0_0_3px_rgba(251,191,36,0.16)] [&.is-active]:after:absolute [&.is-active]:after:inset-y-0 [&.is-active]:after:left-0 [&.is-active]:after:w-[3px] [&.is-active]:after:bg-amber-500 [&.is-active]:after:content-[''] dark:[&.is-active]:bg-slate-800">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p
                            class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">
                            Menunggu</p>
                        <p
                            class="mt-1.5 text-3xl font-bold leading-none tracking-tight text-slate-800 tabular-nums dark:text-slate-50">
                            {{ number_format($totalMenunggu, 0, ',', '.') }}</p>
                    </div>
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-500 ring-1 ring-inset ring-amber-100 transition group-hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/20">
                        <i class="fa-solid fa-hourglass-half text-sm"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-center justify-between gap-2">
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Belum diproses</p>
                    <span
                        class="inline-flex items-center gap-1 text-[10px] font-semibold text-amber-600 opacity-60 transition group-hover:opacity-100 dark:text-amber-300">
                        Filter <i class="fa-solid fa-arrow-right text-[8px]"></i>
                    </span>
                </div>
            </button>

            <button type="button" data-status-filter="disetujui" aria-pressed="false"
                title="Tampilkan pengajuan yang disetujui"
                class="status-card group relative cursor-pointer overflow-hidden rounded-2xl border border-emerald-200/80 bg-white p-4 text-left shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-emerald-300 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400 focus-visible:ring-offset-2 dark:border-emerald-500/25 dark:bg-slate-800 dark:hover:border-emerald-500/50 dark:focus-visible:ring-offset-slate-900 [&.is-active]:border-emerald-400 [&.is-active]:shadow-[0_0_0_3px_rgba(52,211,153,0.16)] [&.is-active]:after:absolute [&.is-active]:after:inset-y-0 [&.is-active]:after:left-0 [&.is-active]:after:w-[3px] [&.is-active]:after:bg-emerald-500 [&.is-active]:after:content-[''] dark:[&.is-active]:bg-slate-800">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p
                            class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">
                            Disetujui</p>
                        <p
                            class="mt-1.5 text-3xl font-bold leading-none tracking-tight text-slate-800 tabular-nums dark:text-slate-50">
                            {{ number_format($totalDisetujui, 0, ',', '.') }}</p>
                    </div>
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-500 ring-1 ring-inset ring-emerald-100 transition group-hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/20">
                        <i class="fa-solid fa-circle-check text-sm"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-center justify-between gap-2">
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Perubahan sudah diterapkan</p>
                    <span
                        class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-600 opacity-60 transition group-hover:opacity-100 dark:text-emerald-300">
                        Filter <i class="fa-solid fa-arrow-right text-[8px]"></i>
                    </span>
                </div>
            </button>

            <button type="button" data-status-filter="ditolak" aria-pressed="false"
                title="Tampilkan pengajuan yang ditolak"
                class="status-card group relative cursor-pointer overflow-hidden rounded-2xl border border-rose-200/80 bg-white p-4 text-left shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-rose-300 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-400 focus-visible:ring-offset-2 dark:border-rose-500/25 dark:bg-slate-800 dark:hover:border-rose-500/50 dark:focus-visible:ring-offset-slate-900 [&.is-active]:border-rose-400 [&.is-active]:shadow-[0_0_0_3px_rgba(251,113,133,0.16)] [&.is-active]:after:absolute [&.is-active]:after:inset-y-0 [&.is-active]:after:left-0 [&.is-active]:after:w-[3px] [&.is-active]:after:bg-rose-500 [&.is-active]:after:content-[''] dark:[&.is-active]:bg-slate-800">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p
                            class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400">
                            Ditolak</p>
                        <p
                            class="mt-1.5 text-3xl font-bold leading-none tracking-tight text-slate-800 tabular-nums dark:text-slate-50">
                            {{ number_format($totalDitolak, 0, ',', '.') }}</p>
                    </div>
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-500 ring-1 ring-inset ring-rose-100 transition group-hover:bg-rose-100 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/20">
                        <i class="fa-solid fa-circle-xmark text-sm"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-center justify-between gap-2">
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Data asli tidak diubah</p>
                    <span
                        class="inline-flex items-center gap-1 text-[10px] font-semibold text-rose-600 opacity-60 transition group-hover:opacity-100 dark:text-rose-300">
                        Filter <i class="fa-solid fa-arrow-right text-[8px]"></i>
                    </span>
                </div>
            </button>
        </div>

        {{-- Tabel pengajuan --}}
        <div id="daftar-pengajuan"
            class="scroll-mt-24 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800 [&_.dt-processing]:rounded-xl [&_.dt-processing]:border [&_.dt-processing]:border-slate-200 [&_.dt-processing]:bg-white [&_.dt-processing]:px-4 [&_.dt-processing]:py-2 [&_.dt-processing]:text-xs [&_.dt-processing]:font-semibold [&_.dt-processing]:text-slate-600 [&_.dt-processing]:shadow-[0_10px_25px_-12px_rgba(15,23,42,0.3)] dark:[&_.dt-processing]:border-slate-700 dark:[&_.dt-processing]:bg-slate-800 dark:[&_.dt-processing]:text-slate-300">
            <div
                class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 dark:border-slate-700 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-800 dark:text-slate-50">Daftar Pengajuan</h2>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Pantau dan proses pengajuan perubahan
                        data dari Admin dan Farmasi.</p>
                </div>

                <div
                    class="inline-flex items-center gap-2 self-start rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-[11px] font-medium text-slate-500 dark:border-slate-700 dark:bg-slate-900/40 dark:text-slate-400">
                    <i class="fa-solid fa-clipboard-list text-[11px] text-slate-400"></i>
                    <span>{{ number_format($totalMenunggu + $totalDisetujui + $totalDitolak, 0, ',', '.') }}
                        pengajuan</span>
                </div>
            </div>

            <div
                class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 dark:border-slate-700 lg:flex-row lg:items-center lg:justify-between">
                <div class="relative w-full lg:max-w-sm">
                    <i
                        class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                    <input id="pengajuanSearch" type="search" autocomplete="off"
                        placeholder="Cari nama layanan atau obat..." aria-label="Cari pengajuan"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/70 py-2.5 pl-10 pr-10 text-sm text-slate-700 placeholder:text-slate-400 transition [&::-webkit-search-cancel-button]:appearance-none [&::-webkit-search-cancel-button]:hidden focus:border-indigo-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100 dark:border-slate-600 dark:bg-slate-900/40 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-indigo-500 dark:focus:bg-slate-900">
                    <button type="button" id="pengajuanSearchClear" aria-label="Hapus pencarian"
                        class="absolute right-2.5 top-1/2 hidden h-6 w-6 -translate-y-1/2 inline-flex items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-200/70 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-indigo-200 dark:hover:bg-slate-700 dark:hover:text-slate-200">
                        <i class="fa-solid fa-xmark text-[10px]"></i>
                    </button>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <div class="relative">
                        <i
                            class="fa-solid fa-circle-dot pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[10px] text-slate-400"></i>
                        <select id="filterStatus" aria-label="Filter status"
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-9 text-xs font-semibold text-slate-600 transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100 dark:border-slate-600 dark:bg-slate-900/40 dark:text-slate-200">
                            <option value="">Semua Status</option>
                            <option value="menunggu">Menunggu</option>
                            <option value="disetujui">Disetujui</option>
                            <option value="ditolak">Ditolak</option>
                        </select>
                        <i
                            class="fa-solid fa-chevron-down pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-[9px] text-slate-400"></i>
                    </div>

                    <div class="relative">
                        <i
                            class="fa-solid fa-layer-group pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[10px] text-slate-400"></i>
                        <select id="filterJenis" aria-label="Filter jenis data"
                            class="w-full appearance-none rounded-xl border border-slate-200 bg-white py-2.5 pl-9 pr-9 text-xs font-semibold text-slate-600 transition focus:border-indigo-400 focus:outline-none focus:ring-4 focus:ring-indigo-100 dark:border-slate-600 dark:bg-slate-900/40 dark:text-slate-200">
                            <option value="">Semua Jenis</option>
                            <option value="layanan">Layanan</option>
                            <option value="obat">Obat</option>
                        </select>
                        <i
                            class="fa-solid fa-chevron-down pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-[9px] text-slate-400"></i>
                    </div>

                    <button type="button" id="resetFilter" title="Reset pencarian dan filter" disabled
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-100 disabled:pointer-events-none disabled:opacity-50 dark:border-slate-600 dark:bg-slate-900/40 dark:text-slate-300 dark:hover:bg-slate-700">
                        <i class="fa-solid fa-rotate-left text-[10px]"></i>
                        Reset
                    </button>
                </div>
            </div>

            <div class="max-h-[600px] overflow-auto overscroll-contain">
                <table id="pengajuanTable"
                    class="w-full text-left text-sm text-slate-700 dark:text-slate-100
                        [&[id=pengajuanTable].dataTable>thead]:bg-slate-50 dark:[&[id=pengajuanTable].dataTable>thead]:bg-slate-900
                        [&[id=pengajuanTable].dataTable>thead>tr>th]:sticky [&[id=pengajuanTable].dataTable>thead>tr>th]:top-0 [&[id=pengajuanTable].dataTable>thead>tr>th]:z-[2]
                        [&[id=pengajuanTable].dataTable>thead>tr>th]:whitespace-nowrap [&[id=pengajuanTable].dataTable>thead>tr>th]:border-b [&[id=pengajuanTable].dataTable>thead>tr>th]:border-slate-200
                        [&[id=pengajuanTable].dataTable>thead>tr>th]:bg-slate-50 [&[id=pengajuanTable].dataTable>thead>tr>th]:px-4 [&[id=pengajuanTable].dataTable>thead>tr>th]:py-[13px]
                        [&[id=pengajuanTable].dataTable>thead>tr>th]:align-middle [&[id=pengajuanTable].dataTable>thead>tr>th]:text-[10px] [&[id=pengajuanTable].dataTable>thead>tr>th]:font-bold
                        [&[id=pengajuanTable].dataTable>thead>tr>th]:uppercase [&[id=pengajuanTable].dataTable>thead>tr>th]:leading-[1.2] [&[id=pengajuanTable].dataTable>thead>tr>th]:tracking-[0.11em]
                        [&[id=pengajuanTable].dataTable>thead>tr>th]:text-slate-500
                        dark:[&[id=pengajuanTable].dataTable>thead>tr>th]:border-slate-700 dark:[&[id=pengajuanTable].dataTable>thead>tr>th]:bg-slate-900 dark:[&[id=pengajuanTable].dataTable>thead>tr>th]:text-slate-400
                        [&[id=pengajuanTable].dataTable>thead>tr>th.dt-orderable-asc:hover]:bg-slate-100 [&[id=pengajuanTable].dataTable>thead>tr>th.dt-orderable-asc:hover]:text-indigo-700
                        [&[id=pengajuanTable].dataTable>thead>tr>th.dt-orderable-desc:hover]:bg-slate-100 [&[id=pengajuanTable].dataTable>thead>tr>th.dt-orderable-desc:hover]:text-indigo-700
                        dark:[&[id=pengajuanTable].dataTable>thead>tr>th.dt-orderable-asc:hover]:bg-slate-800 dark:[&[id=pengajuanTable].dataTable>thead>tr>th.dt-orderable-asc:hover]:text-indigo-300
                        dark:[&[id=pengajuanTable].dataTable>thead>tr>th.dt-orderable-desc:hover]:bg-slate-800 dark:[&[id=pengajuanTable].dataTable>thead>tr>th.dt-orderable-desc:hover]:text-indigo-300
                        [&[id=pengajuanTable].dataTable_thead_.dt-column-header]:flex [&[id=pengajuanTable].dataTable_thead_.dt-column-header]:items-center [&[id=pengajuanTable].dataTable_thead_.dt-column-header]:justify-start [&[id=pengajuanTable].dataTable_thead_.dt-column-header]:gap-1.5
                        [&[id=pengajuanTable].dataTable_thead_th:last-child_.dt-column-header]:justify-end
                        [&[id=pengajuanTable].dataTable_thead_th.dt-orderable-none_.dt-column-order]:hidden
                        [&[id=pengajuanTable].dataTable_thead_.dt-column-order]:relative [&[id=pengajuanTable].dataTable_thead_.dt-column-order]:inline-flex [&[id=pengajuanTable].dataTable_thead_.dt-column-order]:h-4 [&[id=pengajuanTable].dataTable_thead_.dt-column-order]:w-2.5 [&[id=pengajuanTable].dataTable_thead_.dt-column-order]:text-slate-300
                        [&[id=pengajuanTable].dataTable_thead_.dt-column-order::before]:left-1/2 [&[id=pengajuanTable].dataTable_thead_.dt-column-order::before]:top-0.5 [&[id=pengajuanTable].dataTable_thead_.dt-column-order::before]:-translate-x-1/2 [&[id=pengajuanTable].dataTable_thead_.dt-column-order::before]:text-[6px] [&[id=pengajuanTable].dataTable_thead_.dt-column-order::before]:leading-none
                        [&[id=pengajuanTable].dataTable_thead_.dt-column-order::after]:left-1/2 [&[id=pengajuanTable].dataTable_thead_.dt-column-order::after]:top-[9px] [&[id=pengajuanTable].dataTable_thead_.dt-column-order::after]:-translate-x-1/2 [&[id=pengajuanTable].dataTable_thead_.dt-column-order::after]:text-[6px] [&[id=pengajuanTable].dataTable_thead_.dt-column-order::after]:leading-none
                        [&[id=pengajuanTable].dataTable_thead_th.dt-ordering-asc_.dt-column-order::before]:text-indigo-600
                        [&[id=pengajuanTable].dataTable_thead_th.dt-ordering-desc_.dt-column-order::after]:text-indigo-600
                        [&[id=pengajuanTable].dataTable_thead_th.dt-ordering-asc_.dt-column-order::after]:text-slate-200
                        [&[id=pengajuanTable].dataTable_thead_th.dt-ordering-desc_.dt-column-order::before]:text-slate-200
                        dark:[&[id=pengajuanTable].dataTable_thead_.dt-column-order::before]:text-slate-600 dark:[&[id=pengajuanTable].dataTable_thead_.dt-column-order::after]:text-slate-600
                        dark:[&[id=pengajuanTable].dataTable_thead_th.dt-ordering-asc_.dt-column-order::before]:text-indigo-400
                        dark:[&[id=pengajuanTable].dataTable_thead_th.dt-ordering-desc_.dt-column-order::after]:text-indigo-400
                        dark:[&[id=pengajuanTable].dataTable_thead_th.dt-ordering-asc_.dt-column-order::after]:text-slate-700
                        dark:[&[id=pengajuanTable].dataTable_thead_th.dt-ordering-desc_.dt-column-order::before]:text-slate-700
                        [&[id=pengajuanTable].dataTable>tbody>tr>td]:border-b [&[id=pengajuanTable].dataTable>tbody>tr>td]:!border-slate-100 [&[id=pengajuanTable].dataTable>tbody>tr>td]:px-4 [&[id=pengajuanTable].dataTable>tbody>tr>td]:py-3.5 [&[id=pengajuanTable].dataTable>tbody>tr>td]:align-middle
                        dark:[&[id=pengajuanTable].dataTable>tbody>tr>td]:!border-slate-700
                        [&[id=pengajuanTable].dataTable>thead>tr>th:first-child]:text-center [&[id=pengajuanTable].dataTable>tbody>tr>td:first-child]:text-center
                        [&[id=pengajuanTable].dataTable>tbody>tr:last-child>td]:!border-b-0
                        [&[id=pengajuanTable].dataTable>tbody>tr:hover>td]:bg-[#f8faff] dark:[&[id=pengajuanTable].dataTable>tbody>tr:hover>td]:bg-slate-800
                        [&[id=pengajuanTable].dataTable>tbody>tr.row-pending>td:first-child]:shadow-[inset_3px_0_0_#fbbf24]
                        [&[id=pengajuanTable].dataTable>tbody>tr.row-pending:hover>td:first-child]:shadow-[inset_3px_0_0_#f59e0b]
                        [&[id=pengajuanTable].dataTable_tbody_td.dtr-control::before]:mr-2 [&[id=pengajuanTable].dataTable_tbody_td.dtr-control::before]:inline-flex [&[id=pengajuanTable].dataTable_tbody_td.dtr-control::before]:h-6 [&[id=pengajuanTable].dataTable_tbody_td.dtr-control::before]:w-6 [&[id=pengajuanTable].dataTable_tbody_td.dtr-control::before]:items-center [&[id=pengajuanTable].dataTable_tbody_td.dtr-control::before]:justify-center [&[id=pengajuanTable].dataTable_tbody_td.dtr-control::before]:rounded-lg [&[id=pengajuanTable].dataTable_tbody_td.dtr-control::before]:border [&[id=pengajuanTable].dataTable_tbody_td.dtr-control::before]:border-indigo-200 [&[id=pengajuanTable].dataTable_tbody_td.dtr-control::before]:bg-indigo-50 [&[id=pengajuanTable].dataTable_tbody_td.dtr-control::before]:text-[0.7rem] [&[id=pengajuanTable].dataTable_tbody_td.dtr-control::before]:leading-none [&[id=pengajuanTable].dataTable_tbody_td.dtr-control::before]:text-indigo-600 [&[id=pengajuanTable].dataTable_tbody_td.dtr-control::before]:shadow-none">
                    <caption class="sr-only">Daftar pengajuan perubahan data layanan dan obat</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="text-center">No</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Jenis</th>
                            <th scope="col">Data</th>
                            <th scope="col">Aksi</th>
                            <th scope="col">Diajukan Oleh</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="text-right">Detail</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <div
                class="flex flex-col gap-3 border-t border-slate-200 px-5 py-3.5 dark:border-slate-700 md:flex-row md:items-center md:justify-between">
                <div
                    class="flex flex-col gap-2 text-[11px] text-slate-500 dark:text-slate-400 sm:flex-row sm:items-center sm:gap-3">
                    <label for="pengajuanPageLength" class="flex items-center gap-2">
                        <span>Tampilkan</span>
                        <select id="pengajuanPageLength"
                            class="rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-[11px] font-semibold text-slate-600 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-slate-600 dark:bg-slate-900/40 dark:text-slate-200">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span>data</span>
                    </label>
                    <span class="hidden h-3 w-px bg-slate-200 dark:bg-slate-600 sm:inline-block"></span>
                    <span id="pengajuanInfo" aria-live="polite">Memuat data...</span>
                </div>

                <nav aria-label="Navigasi halaman pengajuan" aria-live="polite">
                    <ul id="pengajuanPagination" class="flex flex-wrap items-center gap-1"></ul>
                </nav>
            </div>
        </div>

    </div>

    {{-- Modal detail pengajuan --}}
    <div id="modalDetailPengajuan" class="hidden fixed inset-0 z-[1040] items-center justify-center bg-black/50 px-4">
        <div class="relative w-full max-w-3xl">
            <div class="max-h-[90vh] overflow-y-auto rounded-2xl bg-white shadow-xl dark:bg-slate-800">

                <div
                    class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-700">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-50">Detail Pengajuan</h3>
                        <p class="text-xs text-slate-500 mt-1" id="detailTarget">-</p>
                    </div>
                    <button type="button" id="btnCloseDetailPengajuan"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div class="px-5 py-4 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900/40">
                            <p class="text-[11px] uppercase tracking-wide text-slate-500">Jenis Data</p>
                            <p class="mt-1 font-semibold text-slate-800 dark:text-slate-100" id="detailJenis">-</p>
                        </div>
                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900/40">
                            <p class="text-[11px] uppercase tracking-wide text-slate-500">Aksi</p>
                            <p class="mt-1 font-semibold text-slate-800 dark:text-slate-100" id="detailAksi">-</p>
                        </div>
                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900/40">
                            <p class="text-[11px] uppercase tracking-wide text-slate-500">Diajukan Oleh</p>
                            <p class="mt-1 font-semibold text-slate-800 dark:text-slate-100" id="detailPengaju">-</p>
                        </div>
                        <div
                            class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900/40">
                            <p class="text-[11px] uppercase tracking-wide text-slate-500">Tanggal Pengajuan</p>
                            <p class="mt-1 font-semibold text-slate-800 dark:text-slate-100" id="detailTanggal">-</p>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-50">Perbandingan Data</h4>
                            <span id="detailStatusBadge"
                                class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold">-</span>
                        </div>

                        <div class="overflow-hidden rounded-xl border border-slate-200 dark:border-slate-700">
                            <table class="w-full text-sm">
                                <thead
                                    class="bg-slate-100 text-xs uppercase text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                    <tr>
                                        <th class="px-4 py-3 text-left">Field</th>
                                        <th class="px-4 py-3 text-left">Data Lama</th>
                                        <th class="px-4 py-3 text-left">Data Diajukan</th>
                                    </tr>
                                </thead>
                                <tbody id="detailRows" class="divide-y divide-slate-100 dark:divide-slate-700">
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-slate-500">Memuat data...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div id="catatanWrapper">
                        <label for="catatanVerifikator"
                            class="block mb-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
                            Catatan Verifikator <span class="font-normal text-slate-400">(opsional)</span>
                        </label>
                        <textarea id="catatanVerifikator" rows="3"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100"
                            placeholder="Tulis alasan persetujuan atau penolakan..."></textarea>
                    </div>

                    <div id="catatanTersimpan"
                        class="hidden rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm dark:border-slate-700 dark:bg-slate-900/40">
                        <p class="text-[11px] uppercase tracking-wide text-slate-500">Catatan Verifikator</p>
                        <p class="mt-1 text-slate-700 dark:text-slate-200" id="catatanTersimpanText">-</p>
                    </div>
                </div>

                <div
                    class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-t border-slate-200 px-5 py-4 dark:border-slate-700">
                    <p class="text-xs text-slate-500" id="detailDiverifikasi">-</p>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" id="btnTutupPengajuan"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                            Tutup
                        </button>
                        <button type="button" id="btnTolakPengajuan"
                            class="hidden rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                            <i class="fa-solid fa-xmark mr-1"></i> Tolak
                        </button>
                        <button type="button" id="btnSetujuiPengajuan"
                            class="hidden rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            <i class="fa-solid fa-check mr-1"></i> Setujui
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/js/verifikator/pengajuan-perubahan.js'])

</x-mycomponents.layout>
