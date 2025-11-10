<x-layout-perawat>
    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">

                <div class="flex flex-col space-y-1">

                    <p class="text-lg font-medium text-gray-500">
                        Selamat Datang Kembali, Perawat!
                    </p>

                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-gauge-high fa-2xl text-indigo-600"></i>

                        <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                            Dashboard Perawat
                        </h1>
                    </div>
                </div>

                <div class="hidden sm:flex items-center text-gray-600 space-x-2">
                    <i class="fa-regular fa-calendar-days"></i>
                    <span class="font-semibold text-sm">{{ date('d M Y') }}</span>
                </div>
            </div>

            <p class="mt-2 text-sm text-gray-500">
                Ringkasan statistik klinis dan performa sistem secara keseluruhan.
            </p>
        </div>
        <hr class="mb-2 border-gray-200">


        <!-- Konten -->
        {{-- <div class="p-2 shadow-lg rounded-lg min-h-screen"> --}}

        <div class="lg:col-span-1 bg-white p-1 rounded-xl shadow-lg flex flex-col h-full">

            {{-- ========== Grafik Utama ========== --}}
            <div class="bg-white rounded-lg shadow p-4 mb-3">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-700">Grafik Penjualan Obat</h2>
                        {{-- judul dinamis (tanggal/range) --}}
                        <h3 id="chartTitle" class="text-sm text-gray-500 mt-1"></h3>
                    </div>

                    <select id="rangeFilter" class="text-sm border rounded-md px-2 py-1">
                        <option value="harian" selected>Harian</option>
                        <option value="mingguan">Mingguan</option>
                        <option value="bulanan">Bulanan</option>
                        <option value="tahunan">Tahunan</option>
                    </select>
                </div>

                {{-- wrapper fixed height agar chart tidak “memanjang” --}}
                <div class="relative w-full" style="height: 720px;">
                    <canvas id="chartPenjualanObat"></canvas>
                </div>
            </div>
            {{-- ========== 4 Card Statistik Mini ========== --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2 px-4 pb-4">
                <!-- Jumlah Transaksi  Hari Ini -->
                <div
                    class="flex items-center justify-between p-4 bg-gradient-to-r from-sky-500 to-sky-600 text-white rounded-lg shadow">
                    <div>
                        <p class="text-sm">Jumlah Transaksi Hari Ini</p>
                        <h3 id="totalTransaksiHariIni" class="text-2xl font-bold">0</h3>
                    </div>
                    <i class="fa-solid fa-hand-holding-dollar text-3xl opacity-70" aria-hidden="true"></i>
                </div>

                <!-- Jumlah Keseluruhan Transaksi  -->
                <div
                    class="flex items-center justify-between p-4 bg-gradient-to-r from-indigo-500 to-indigo-700 text-white rounded-lg shadow">
                    <div>
                        <p class="text-sm">Jumlah Keseluruhan Transaksi</p>
                        <h3 id="totalKeseluruhanTransaksi" class="text-2xl font-bold">0</h3>
                    </div>
                    <i class="fa-solid fa-chart-column text-3xl opacity-70" aria-hidden="true"></i>
                </div>

                <!-- Jumlah Transaksi Obat Hari Ini -->
                <div
                    class="flex items-center justify-between p-4 bg-gradient-to-r from-emerald-500 to-emerald-600 text-white rounded-lg shadow">
                    <div>
                        <p class="text-sm">Jumlah Transaksi Obat Hari Ini</p>
                        <h3 id="totalTransaksiObatHariIni" class="text-2xl font-bold">0</h3>
                    </div>
                    <i class="fa-solid fa-prescription-bottle-medical text-3xl opacity-70" aria-hidden="true"></i>
                </div>

                <!-- Jumlah Keseluruhan Transaksi Obat -->
                <div
                    class="flex items-center justify-between p-4 bg-gradient-to-r from-green-500 to-green-700 text-white rounded-lg shadow">
                    <div>
                        <p class="text-sm">Jumlah Keseluruhan Transaksi Obat</p>
                        <h3 id="totalKeseluruhanTransaksiObat" class="text-2xl font-bold">0</h3>
                    </div>
                    <i class="fa-solid fa-capsules text-3xl opacity-70" aria-hidden="true"></i>
                </div>
            </div>

        </div>


        {{-- </div> --}}

    </div>

    @vite(['resources/js/perawat/dashboard.js'])
</x-layout-perawat>
