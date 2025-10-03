<!--
    File: resources/views/components/patient-summary-card.blade.php
    Komponen ini menampilkan ringkasan total pasien dan rincian legendanya.
    Status BPJS disesuaikan agar hanya menampilkan teks "Tidak Terhubung BPJS" berwarna biru tua, rapi, dan tanpa underline.
-->
<div class="p-6 bg-white rounded-xl shadow-lg border border-gray-100 w-full h-full min-h-[300px] flex flex-col">

    <!-- HEADER SECTION -->
    <div class="flex items-start justify-between border-b pb-4 mb-4">
        <!-- Judul dan Info Icon -->
        <h4 class="text-lg font-semibold text-gray-800 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 ml-1 cursor-pointer hover:text-gray-600"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <path d="M12 16v-4" />
                <path d="M12 8h.01" />
            </svg>
            {{ $title }}
            <!-- Info Icon -->

        </h4>

        <!-- Connection Status (BPJS) - Teks Biru Tua Sederhana -->
        <!-- Menghapus 'underline' dan 'cursor-pointer' untuk tampilan yang lebih rapi/statis -->
        <span class="text-base font-medium text-blue-700">
            Tidak Terhubung BPJS
        </span>
    </div>

    <!-- MAIN CONTENT SECTION ( menggunakan grid internal untuk tata letak) -->
    <div class="grid grid-cols-1 md:grid-cols-3 flex-grow gap-4">

        <!-- 1. TOTAL VALUE (CENTERED) -->
        <div class="col-span-1 flex flex-col items-center justify-center space-y-1 py-4 md:py-0">
            <p class="text-6xl font-extrabold text-gray-900 leading-none">
                {{ $totalValue }}
            </p>
            <p class="text-lg text-gray-600 font-medium">
                {{ $totalLabel }}
            </p>
        </div>

        <!-- 2. CHART/LEGEND AREA -->
        <!-- Menjaga jarak legend dari tengah -->
        <div class="col-span-1 md:col-span-2 flex items-center justify-start md:justify-start pl-6">
            <div class="space-y-2 w-full max-w-sm">
                @foreach ($legendData as $item)
                    <div class="flex justify-between items-center text-sm font-medium">
                        <!-- Indikator Warna -->
                        <div class="flex items-center space-x-3">
                            <span class="w-3 h-3 rounded-full bg-{{ $item['color'] }}"></span>
                            <span class="text-gray-700">{{ $item['label'] }}</span>
                        </div>
                        <!-- Nilai Legenda -->
                        <span class="text-gray-900 font-bold">{{ $item['value'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
