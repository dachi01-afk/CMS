{{-- main --}}
<div>
    <!-- Laporan Keuangan -->
    <section class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-700 flex items-center gap-2">
                💰 Laporan Keuangan
            </h2>
            <div class="flex gap-3 items-center">
                <!-- 🔹 Tombol Export Excel -->
                <button id="exportExcel"
                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm font-medium">
                    📊 Export Excel
                </button>

                <!-- Filter utama -->
                <select id="filterKeuangan"
                    class="border border-gray-300 bg-gray-50 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300 transition">
                    <option value="mingguan">Per Minggu</option>
                    <option value="bulanan">Per Bulan</option>
                    <option value="tahunan">Per Tahun</option>
                </select>

                <!-- Filter tambahan: bulan -->
                <select id="bulanKeuangan"
                    class="hidden border border-gray-300 bg-gray-50 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300 transition">
                    <option value="">Pilih Bulan</option>
                    <option value="01">Januari</option>
                    <option value="02">Februari</option>
                    <option value="03">Maret</option>
                    <option value="04">April</option>
                    <option value="05">Mei</option>
                    <option value="06">Juni</option>
                    <option value="07">Juli</option>
                    <option value="08">Agustus</option>
                    <option value="09">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>

                <!-- Filter tambahan: tahun -->
                <select id="tahunKeuangan"
                    class="hidden border border-gray-300 bg-gray-50 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-300 transition">
                    @for ($year = now()->year; $year >= now()->year - 4; $year--)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endfor
                </select>
            </div>
        </div>

        <div class="relative h-[600px]">
            <canvas id="chartKeuangan"></canvas>
        </div>
    </section>

</div>

@vite(['resources/js/admin/laporan/laporan_keuangan.js'])
