<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Transaksi Layanan</h2>
</div>

<!-- Tabel Transaksi Layanan -->
<div class="overflow-hidden rounded-lg shadow-md">
    {{-- Top bar: page length + search --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200">
        <div>
            <select id="transaksi-layanan-page-length"
                class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-24 p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="relative">
            <input type="text" id="transaksi-layanan-search-input"
                class="block w-60 p-2 pl-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Cari data...">
        </div>
    </div>

    {{-- Tabel (dibungkus overflow-x-auto supaya bisa scroll kanan-kiri) --}}
    <div class="overflow-x-auto">
        <table id="transaksiLayananTable" class="w-full min-w-[1500px] text-sm text-left text-gray-600">
            <thead class="text-xs uppercase bg-sky-500 text-white">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3 whitespace-nowrap">Nama Pasien</th>
                    <th class="px-6 py-3 whitespace-nowrap">Nama Layanan</th>
                    <th class="px-6 py-3 whitespace-nowrap">Kategori Layanan</th>
                    <th class="px-6 py-3">Jumlah</th>
                    <th class="px-6 py-3 whitespace-nowrap">Total Tagihan</th>
                    <th class="px-6 py-3 whitespace-nowrap">Metode Pembayaran</th>
                    <th class="px-6 py-3 whitespace-nowrap">Kode Transaksi</th>
                    <th class="px-6 py-3 whitespace-nowrap">Tanggal Transaksi</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3 whitespace-nowrap">Bukti Pembayaran</th>
                    <th class="px-6 py-3 text-center whitespace-nowrap">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    {{-- Info + pagination custom --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-4 py-3 border-t border-gray-200 gap-3">
        <div id="transaksi-layanan-custom-info" class="text-sm text-gray-700 dark:text-gray-300"></div>
        <ul id="transaksi-layanan-custom-paginate" class="inline-flex -space-x-px text-sm"></ul>
    </div>
</div>

<script>
    window.transaksiLayananDataUrl = "{{ route('kasir.get.data.transaksi.layanan') }}";
</script>

@vite(['resources/js/kasir/pembayaran/data-transaksi-layanan.js'])
