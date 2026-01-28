<x-mycomponents.layout>

    {{-- main --}}
    <div>

        <div class="mb-6 mt-2">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">

                <div class="flex flex-col space-y-1">
                    <p class="text-lg font-medium text-gray-500">
                        Selamat Datang, Manajer!
                    </p>

                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-chart-pie fa-2xl text-emerald-600"></i>

                        <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                            Dashboard Eksekutif
                        </h1>
                    </div>
                </div>

                <div
                    class="flex items-center bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-100 text-gray-600 space-x-2">
                    <i class="fa-regular fa-calendar-check text-emerald-500"></i>
                    <span class="font-semibold text-sm">{{ date('d F Y') }}</span>
                </div>
            </div>

            <p class="mt-2 text-sm text-gray-500">
                Ringkasan performa bisnis, total transaksi, dan pertumbuhan pasien.
            </p>
        </div>

        <hr class="mb-6 border-gray-200">

        <div class="bg-white p-4 rounded-xl shadow-lg flex flex-col h-full border-t-4 border-emerald-500">

            {{-- ========== 4 Card Statistik Utama (Revisi Manajer) ========== --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

                {{-- 1. Jumlah Pasien (Total Database) --}}
                <div
                    class="relative overflow-hidden bg-gradient-to-br from-blue-500 to-blue-700 text-white rounded-xl shadow-md p-5 hover:shadow-lg transition-shadow duration-300">
                    <div class="relative z-10 flex justify-between items-center">
                        <div>
                            <p class="text-blue-100 text-sm font-medium uppercase tracking-wider">Total Pasien</p>
                            <h3 id="totalPasien" class="text-3xl font-bold mt-1">{{ $totalPasien ?? '0' }}</h3>
                        </div>
                        <div class="p-3 bg-blue-600/30 rounded-full">
                            <i class="fa-solid fa-users text-2xl text-blue-100"></i>
                        </div>
                    </div>
                    <i class="fa-solid fa-users absolute -bottom-4 -right-4 text-9xl opacity-10 text-white"></i>
                </div>

                {{-- 2. Jumlah Transaksi (Total Invoice/Kasir) --}}
                <div
                    class="relative overflow-hidden bg-gradient-to-br from-emerald-500 to-emerald-700 text-white rounded-xl shadow-md p-5 hover:shadow-lg transition-shadow duration-300">
                    <div class="relative z-10 flex justify-between items-center">
                        <div>
                            <p class="text-emerald-100 text-sm font-medium uppercase tracking-wider">Total Transaksi</p>
                            <h3 id="totalTransaksi" class="text-3xl font-bold mt-1">{{ $totalTransaksi ?? '0' }}</h3>
                        </div>
                        <div class="p-3 bg-emerald-600/30 rounded-full">
                            <i class="fa-solid fa-file-invoice-dollar text-2xl text-emerald-100"></i>
                        </div>
                    </div>
                    <i
                        class="fa-solid fa-file-invoice-dollar absolute -bottom-4 -right-4 text-9xl opacity-10 text-white"></i>
                </div>

                {{-- 3. Pendapatan / Omset (Opsional tapi penting buat Manajer) --}}
                <div
                    class="relative overflow-hidden bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-xl shadow-md p-5 hover:shadow-lg transition-shadow duration-300">
                    <div class="relative z-10 flex justify-between items-center">
                        <div>
                            <p class="text-amber-100 text-sm font-medium uppercase tracking-wider">Pendapatan</p>
                            <h3 id="totalPendapatan" class="text-3xl font-bold mt-1">{{ $pendapatanRupiah ?? 'Rp. 0' }}
                            </h3>
                        </div>
                        <div class="p-3 bg-amber-600/30 rounded-full">
                            <i class="fa-solid fa-sack-dollar text-2xl text-amber-100"></i>
                        </div>
                    </div>
                    <i class="fa-solid fa-sack-dollar absolute -bottom-4 -right-4 text-9xl opacity-10 text-white"></i>
                </div>

                {{-- 4. Kunjungan Hari Ini (Operasional Harian) --}}
                <div
                    class="relative overflow-hidden bg-gradient-to-br from-purple-500 to-purple-700 text-white rounded-xl shadow-md p-5 hover:shadow-lg transition-shadow duration-300">
                    <div class="relative z-10 flex justify-between items-center">
                        <div>
                            <p class="text-purple-100 text-sm font-medium uppercase tracking-wider">Pasien Hari Ini</p>
                            <h3 id="kunjunganHariIni" class="text-3xl font-bold mt-1">{{ $pasienHariIni ?? '0' }}</h3>
                        </div>
                        <div class="p-3 bg-purple-600/30 rounded-full">
                            <i class="fa-solid fa-hospital-user text-2xl text-purple-100"></i>
                        </div>
                    </div>
                    <i class="fa-solid fa-hospital-user absolute -bottom-4 -right-4 text-9xl opacity-10 text-white"></i>
                </div>
            </div>

            {{-- ========== Grafik Utama (Analisa) ========== --}}
            <div class="bg-white rounded-xl border border-gray-100 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-gray-800 flex items-center">
                        <i class="fa-solid fa-chart-line text-emerald-500 mr-2"></i>
                        Tren Transaksi & Kunjungan
                    </h2>

                    <select
                        class="text-sm border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring focus:ring-emerald-200">
                        <option>Bulan Ini</option>
                        <option>Tahun Ini</option>
                    </select>
                </div>
                <div class="relative h-72 w-full">
                    <canvas id="managerChart"></canvas> {{-- ID diganti biar spesifik --}}
                </div>
            </div>

        </div>

    </div>

    {{-- Script Khusus Dashboard Manajer --}}
    @vite(['resources/js/admin/dashboard.js'])
</x-mycomponents.layout>
