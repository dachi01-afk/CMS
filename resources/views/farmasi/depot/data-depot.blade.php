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

@vite(['resources/js/farmasi/depot/data-depot.js'])
