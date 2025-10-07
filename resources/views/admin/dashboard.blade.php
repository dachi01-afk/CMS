<x-mycomponents.layout>

    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">

                <div class="flex flex-col space-y-1">

                    <p class="text-lg font-medium text-gray-500">
                        Selamat Datang Kembali, Admin!
                    </p>

                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-gauge-high fa-2xl text-indigo-600"></i>

                        <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                            Dashboard Utama
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
                <h2 class="text-lg font-semibold text-gray-700 mb-2">Grafik Jumlah Kunjungan Pasien</h2>
                <canvas id="kunjunganChart" class="w-full h-64"></canvas>
            </div>

            {{-- ========== 4 Card Statistik Mini ========== --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-2">
                {{-- Jumlah Dokter --}}
                <div
                    class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg shadow">
                    <div>
                        <p class="text-sm">Jumlah Dokter</p>
                        <h3 id="totalDokter" class="text-2xl font-bold">0</h3>
                    </div>
                    <i class="fa-solid fa-user-md text-3xl opacity-70"></i>
                </div>

                {{-- Jumlah Pasien --}}
                <div
                    class="flex items-center justify-between p-4 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg shadow">
                    <div>
                        <p class="text-sm">Jumlah Pasien</p>
                        <h3 id="totalPasien" class="text-2xl font-bold">0</h3>
                    </div>
                    <i class="fa-solid fa-users text-3xl opacity-70"></i>
                </div>

                {{-- Jumlah Apoteker --}}
                <div
                    class="flex items-center justify-between p-4 bg-gradient-to-r from-cyan-500 to-cyan-600 text-white rounded-lg shadow">
                    <div>
                        <p class="text-sm">Jumlah Apoteker</p>
                        <h3 id="totalApoteker" class="text-2xl font-bold">0</h3>
                    </div>
                    <i class="fa-solid fa-prescription-bottle-medical text-3xl opacity-70"></i>
                </div>

                {{-- Stok Obat --}}
                <div
                    class="flex items-center justify-between p-4 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg shadow">
                    <div>
                        <p class="text-sm">Total Stok Obat</p>
                        <h3 id="totalObat" class="text-2xl font-bold">0</h3>
                    </div>
                    <i class="fa-solid fa-pills text-3xl opacity-70"></i>
                </div>
            </div>

        </div>


        {{-- </div> --}}

    </div>

    @vite(['resources/js/admin/dashboard.js'])
</x-mycomponents.layout>
