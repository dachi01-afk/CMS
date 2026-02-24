{{-- Header --}}
<div class="mb-4 flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">
            Restock obat
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Kelola transaksi restock obat
        </p>
    </div>

    <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto md:items-start">
        {{-- Search --}}
        {{-- <div class="w-full md:w-[360px]">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                </span>

                <input type="text" id="customSearch"
                    class="block w-full pl-9 pr-3 py-2 text-sm text-slate-800 dark:text-slate-100 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 focus:ring-sky-500 focus:border-sky-500"
                    placeholder="Cari kode / supplier / nama item..." />
            </div>

            <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500 hidden sm:block">
                Contoh: <span class="italic">STK-0001, Kimia Farma, Paracetamol</span>.
            </p>
        </div> --}}

        {{-- Button open modal --}}
        <button type="button" id="btn-open-modal-create"
            class="inline-flex items-center justify-center gap-2 px-4 py-2 h-[42px] bg-emerald-600 text-white rounded-xl hover:bg-emerald-700 shadow-sm whitespace-nowrap">
            <i class="fa-solid fa-plus text-xs"></i>
            <span>Buat Data Restock Obat</span>
        </button>
    </div>
</div>

{{-- Card: Toolbar + Table --}}
<div
    class="bg-white dark:bg-slate-900 rounded-2xl shadow border border-slate-100 dark:border-slate-800 overflow-hidden">
    {{-- Toolbar --}}
    <div class="px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-800">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="flex items-center gap-2 text-sm">
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>

                <select id="restock_pageLength"
                    class="w-36 border border-slate-200 dark:border-slate-700 text-sm rounded-xl focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 px-3 py-2">
                    <option value="10">10 baris</option>
                    <option value="25">25 baris</option>
                    <option value="50">50 baris</option>
                    <option value="100">100 baris</option>
                </select>

                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">/ halaman</span>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table id="table-restock-return" class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                <tr>
                    <th class="px-4 py-3">Kode</th>
                    <th class="px-4 py-3">No Faktur</th>
                    <th class="px-4 py-3">Jenis</th>
                    <th class="px-4 py-3">Tgl Pengiriman</th>
                    <th class="px-4 py-3">Tgl Pembuatan</th>
                    <th class="px-4 py-3">Supplier</th>
                    <th class="px-4 py-3">Nama Item</th>
                    <th class="px-4 py-3">Jumlah</th>
                    <th class="px-4 py-3">Diapprove</th>
                    <th class="px-4 py-3">Total Harga</th>
                    <th class="px-4 py-3">Tempo</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-slate-900 text-[11px] md:text-xs">
                {{-- server-side DataTables --}}
            </tbody>
        </table>
    </div>
    {{-- Footer --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-3 sm:px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">
        <div id="custom_customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>

        {{-- Pagination aman di HP --}}
        <div class="w-full md:w-auto overflow-x-auto">
            <ul id="custom_Pagination"
                class="min-w-max inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
            </ul>
        </div>
    </div>
</div>
