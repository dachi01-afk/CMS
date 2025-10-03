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
        <div class="p-2 shadow-lg rounded-lg min-h-screen">

            <!-- CONTAINER UTAMA: Grid 2 Kolom Kosong -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 min-h-[100vh]">

                <!-- Kolom Kiri (Container 1) -->
                <div class="lg:col-span-1 bg-white p-1 rounded-xl shadow-lg flex flex-col h-full">

                    {{-- container atas --}}
                    {{-- chart grafik --}}
                    <div class="bg-blue-700 text-white rounded-lg p-1 flex-1 mb-1 flex items-center justify-center">

                        <div class="w-full h-full bg-white rounded-lg shadow-md p-4">
                            <!-- Filter Dropdowns -->
                            <input type="hidden" id="tahunFilter" value="{{ now()->year }}">

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
                                <div class="relative w-full">
                                    <select id="jenisKunjungan"
                                        class="w-full appearance-none border border-gray-300 rounded-md p-2 text-sm bg-white text-gray-700 shadow-sm transition duration-150 ease-in-out hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer">
                                        @foreach ($jenisKunjungan as $item)
                                            <option>{{ $item->jenis_kunjungan }}</option>
                                        @endforeach
                                    </select>
                                    <div
                                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">

                                    </div>
                                </div>

                                <div class="relative w-full">

                                    <select id="jenisPoli"
                                        class="w-full appearance-none border border-gray-300 rounded-md p-2 text-sm bg-white text-gray-700 shadow-sm transition duration-150 ease-in-out hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer">
                                        @foreach ($jenisPoli as $item)
                                            <option value="{{ $item->nama_poli }}">{{ $item->nama_poli }}</option>
                                        @endforeach
                                    </select>
                                    <div
                                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">

                                    </div>
                                </div>

                                <div class="relative w-full">
                                    <label for="filterPeriode" class="sr-only">Periode</label>
                                    <select id="periodeFilter"
                                        class="w-full appearance-none border border-gray-300 rounded-md p-2 text-sm bg-white text-gray-700 shadow-sm transition duration-150 ease-in-out hover:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 cursor-pointer">
                                        <option value="bulan">Bulan</option>
                                        <option value="minggu">Minggu</option>
                                    </select>
                                    <div
                                        class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">

                                    </div>
                                </div>
                            </div>


                            <!-- Statistik Angka -->
                            <div class="flex items-center space-x-4 mb-4">
                                <div id="totalKunjungan" class="text-3xl font-bold text-gray-800">0</div>
                                <div id="grafikKunjungan" class="text-green-600 text-sm flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path id="arrowKunjungan" stroke-linecap="round" stroke-linejoin="round"
                                            stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                    </svg>
                                    <p id="persenKunjungan">0%</p>
                                </div>
                                <span id="teksPerbandinganKunjungan" class="text-gray-500 text-xs">dari Bulan
                                    lalu</span>
                            </div>


                            <!-- Chart Placeholder -->
                            <div class="w-full h-64">
                                <canvas id="chartKunjungan"></canvas>
                            </div>
                        </div>
                    </div>

                    {{-- container bawah --}}
                    <div
                        class="bg-red-600 text-white rounded-lg p-1 flex-1 flex items-center justify-center h-full w-full">

                        {{-- Main Grid: 1 column on small screens, 2 columns on large screens --}}
                        {{-- Mengembalikan ke lg:grid-cols-2 --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 h-full w-full">

                            {{-- Kolom 1: Total Pasien --}}
                            @php
                                // Definisikan data Legenda (Label, Nilai, dan Warna Tailwind)
                                $patientLegend = [
                                    ['label' => 'Rawat Jalan', 'value' => 0, 'color' => 'blue-900'],
                                    ['label' => 'Rawat Inap', 'value' => 0, 'color' => 'blue-800'],
                                    ['label' => 'Kunjungan Sehat', 'value' => 0, 'color' => 'blue-400'],
                                    ['label' => 'Apotek', 'value' => 0, 'color' => 'blue-200'],
                                ];

                                // Status Koneksi
                                $connectionStatus = 'Tidak Terhubung BPJS';

                            @endphp
                            <div class="lg:col-span-1 bg-white p-1 rounded-xl shadow-inner flex flex-col h-full">
                                <!-- Kartu Ringkasan Pasien -->
                                <x-mycomponents.card_patient_summary title="Total Pasien Klinik" total-value="0"
                                    total-label="Pasien" :connection-status="$connectionStatus" :legend-data="$patientLegend" />
                            </div>

                            {{-- Kolom 2: Container untuk Pemasukan dan Pengeluaran --}}
                            <div class="lg:col-span-1 flex flex-col w-full h-full">

                                @php
                                    // Icon Pendapatan Bulan Ini (Debit Card with Up Arrow - Lucide Icons)
                                    $incomeIcon =
                                        '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/><path d="M16 19v-2a2 2 0 0 0-2-2H9.5a2.5 2.5 0 0 0 0 5H18"/><path d="m14 16 3-3 3 3"/></svg>';
                                @endphp

                                {{-- Pemasukan (di atas) --}}
                                <div
                                    class="bg-blue-700 text-white rounded-lg p-1 flex-1 mb-2 flex flex-col justify-between">
                                    <x-mycomponents.card title="Pendapatan Bulan Ini" value="Rp0" percentage="0%"
                                        context="dari bulan September" :icon-svg="$incomeIcon" :is-positive="true" />
                                </div>

                                {{-- Pengeluaran (di bawah) --}}
                                <div
                                    class="bg-green-600 text-white rounded-lg p-1 flex-1 flex flex-col justify-between">
                                    <x-mycomponents.card title="Total Pengeluaran" value="Rp5.000.000" percentage="12%"
                                        context="dibanding bulan lalu"
                                        icon-svg='<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 17.5V9l7 4 7-4 7 4v8.5a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2Z"/></svg>'
                                        :is-positive="false" />
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan (Container 2) -->
                <div class="lg:col-span-1 bg-white p-1 rounded-xl shadow-lg flex flex-col h-full">

                    {{-- atas --}}
                    <div
                        class="bg-blue-700 text-white rounded-lg p-1 flex-1 mb-1 flex items-center justify-center h-full">


                        @php
                            // --- ICON DEFINITIONS (SVGs) ---
                            $consultationIcon =
                                '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7.5"/><path d="M16 19h6"/><path d="M19 16v6"/><path d="M12 9h.01"/><path d="M8 9h.01"/><path d="M8 13h.01"/><path d="M12 13h.01"/><path d="M8 17h.01"/><path d="M12 17h.01"/></svg>';
                            $newUserIcon =
                                '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';
                            $registeredIcon =
                                '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/></svg>';
                            $doctorWaitIcon =
                                '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/><rect x="3" y="2" width="18" height="4" rx="1"/><path d="M9 12V6"/><path d="M15 12V6"/></svg>';
                            $medicineOutIcon =
                                '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="18" r="4"/><path d="M18 20l-1-1a3 3 0 0 0-3-3H9l-1 1"/></svg>';
                            $pharmacyWaitIcon =
                                '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="11" r="9"/><path d="m14 14-2 2-2-2"/><path d="M12 2v20"/></svg>';
                        @endphp

                        <div class="grid grid-cols-2 gap-1 w-full h-full p-1">

                            {{-- rata rata waktu runggu --}}
                            <div id="card-consultation" class="bg-white rounded flex items-center justify-center">
                                <x-mycomponents.card title="Rata-Rata Waktu Konsultasi" value="-" percentage="-"
                                    context="-" icon-svg="{!! $consultationIcon !!}" :is-positive="true" />
                            </div>

                            {{-- pasien baru --}}
                            <div id="card-new-patient" class="bg-white rounded flex items-center justify-center p-1">
                                <x-mycomponents.card title="Pasien Baru" value="-" percentage="-" context="-"
                                    icon-svg="{!! $newUserIcon !!}" :is-positive="true" />
                            </div>

                            {{-- pasien terdafatar --}}
                            <div id="card-registered" class="bg-white rounded flex items-center justify-center p-1">
                                <x-mycomponents.card title="Pasien Terdaftar" value="-" percentage="-"
                                    context="-" icon-svg="{!! $registeredIcon !!}" :is-positive="true" />
                            </div>

                            {{-- rata rata waktu tunggu dokter --}}
                            <div id="card-doctor-wait" class="bg-white rounded flex items-center justify-center p-1">
                                <x-mycomponents.card title="Rata-Rata Waktu Tunggu Dokter" value="-"
                                    percentage="-" context="-" icon-svg="{!! $doctorWaitIcon !!}"
                                    :is-positive="true" />
                            </div>

                            {{-- obat habis --}}
                            <div id="card-medicine" class="bg-white rounded flex items-center justify-center p-1">
                                <x-mycomponents.card title="Obat Habis" value="-" percentage="-" context="-"
                                    icon-svg="{!! $medicineOutIcon !!}" :is-positive="true" />
                            </div>

                            {{-- rata rata waktu tunggu apotek --}}
                            <div id="card-pharmacy" class="bg-white rounded flex items-center justify-center p-1">
                                <x-mycomponents.card title="Rata-Rata Waktu Tunggu Apotek" value="-"
                                    percentage="-" context="-" icon-svg="{!! $pharmacyWaitIcon !!}"
                                    :is-positive="true" />
                            </div>
                        </div>
                    </div>


                    {{-- container bawah --}}
                    <div class="grid grid-cols-1 mt-2 rounded-lg p-2 bg-white w-full">
                        <div class="p-4">
                            <div class="mb-2">
                                <h2 class="text-xl font-semibold">Pasien Antri Cepat</h2>
                            </div>

                            <table id="kunjungan-table" class="table table-bordered"
                                data-url="{{ route('dashboard.getdataantricepat') }}">
                                <thead>
                                    <tr class="border-b">
                                        <th class="px-4 py-2 text-gray-500 font-normal">Nama</th>
                                        <th class="px-4 py-2 text-gray-500 font-normal">Tenaga Medis</th>
                                        <th class="px-4 py-2 text-gray-500 font-normal">Jadwal</th>
                                        <th class="px-4 py-2 text-gray-500 font-normal">Status</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>

    @vite(['resources/js/admin/dashboard.js'])
</x-mycomponents.layout>
