{{-- CARD DATA DEPOT --}}
<div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">

    {{-- HEADER (Depot) --}}
    <div class="mb-4">
        <h2 class="text-lg md:text-xl font-bold text-sky-600 dark:text-sky-400 tracking-tight">
            Depot
        </h2>

        <div class="mt-2 space-y-1.5">
            <p class="text-[12px] md:text-sm text-gray-700 dark:text-gray-200 leading-relaxed">
                Depot merupakan fitur untuk maintenance jumlah obat yang tersebar di Klinik.
            </p>
            <p class="text-[12px] md:text-sm text-gray-700 dark:text-gray-200 leading-relaxed">
                Pemilik Klinik atau Apoteker bisa mengetahui jumlah obat yang terdapat di Apotek, Ruang Dokter, Gudang,
                dan lain-lain.
            </p>
        </div>

        {{-- Optional: info update kecil (kalau mau tetap ada) --}}
        <p class="mt-2 text-[11px] text-gray-500 dark:text-gray-400">
            Last Update:
            <span class="font-medium text-gray-700 dark:text-gray-200">
                {{ $lastUpdate ?? now()->format('d/m/Y') }}
            </span>
        </p>
    </div>

    {{-- TABLE WRAPPER --}}
    <div class="rounded-2xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <table id="table-depot" class="min-w-full text-xs md:text-sm">
            <thead class="bg-slate-50">
                <tr class="text-left text-[11px] md:text-xs uppercase tracking-wider text-slate-500">
                    <th class="px-4 py-3 w-[70px]">No</th>
                    <th class="px-4 py-3">Depot</th>
                    <th class="px-4 py-3 w-[180px]">Tipe</th>
                    <th class="px-4 py-3 w-[140px]">Stok</th>
                    <th class="px-4 py-3 w-[420px] text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 text-[11px] md:text-xs">
                {{-- server-side DataTables --}}
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Overlay -->
<div id="modal-show-obat" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">

    <!-- Modal Box -->
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl mx-4">

        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h2 class="text-lg font-semibold text-slate-800">
                List Obat Pada Depot 
            </h2>

            <button id="btn-close-show-modal-obat" class="text-slate-400 hover:text-slate-600 text-xl">
                &times;
            </button>
        </div>

        <!-- Content -->
        <div class="px-6 py-4">
            <div class="overflow-x-auto">
                <table class="w-full text-sm border border-slate-200 rounded-lg">
                    <thead class="bg-slate-100 text-slate-700">
                        <tr>
                            <th class="px-4 py-2 text-left border">
                                Nama Obat / Bahan Habis Pakai
                            </th>
                            <th class="px-4 py-2 text-left border">
                                Stok Obat
                            </th>
                        </tr>
                    </thead>

                    <tbody id="modal-obat-body">
                        <!-- Diisi via jQuery -->
                        <tr>
                            <td colspan="2" class="px-4 py-6 text-center text-slate-400">
                                Memuat data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end px-6 py-4 border-t">
            <button id="btn-close-footer"
                class="px-4 py-2 rounded-lg bg-slate-200 hover:bg-slate-300 text-sm font-medium">
                Tutup
            </button>
        </div>

    </div>
</div>


@vite(['resources/js/farmasi/depot/data-depot.js'])
