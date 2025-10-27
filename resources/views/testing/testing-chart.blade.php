<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/testing-chart.js'])
    <title>Testing Chart Js</title>
</head>

<body class="bg-gray-50 text-gray-800 overflow-auto">
    <div class="max-w-5xl mx-auto min-h-screen py-12 px-6 flex flex-col gap-16">

        <!-- Laporan Kunjungan -->
        <section class="bg-white shadow-md rounded-2xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold text-gray-700">📊 Laporan Kunjungan</h2>
                <select id="filterKunjungan"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-200">
                    <option value="mingguan">Per Minggu</option>
                    <option value="bulanan">Per Bulan</option>
                    <option value="tahunan">Per Tahun</option>
                </select>
            </div>
            <div class="h-80">
                <canvas id="myChartJs"></canvas>
            </div>
        </section>

        <!-- Laporan Keuangan -->
        <section class="bg-white shadow-md rounded-2xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold text-gray-700">💰 Laporan Keuangan</h2>
                <select id="filterKeuangan"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-200">
                    <option value="mingguan">Per Minggu</option>
                    <option value="bulanan">Per Bulan</option>
                    <option value="tahunan">Per Tahun</option>
                </select>
            </div>
            <div class="h-80">
                <canvas id="chartKeuangan"></canvas>
            </div>
        </section>

    </div>
</body>

</html>
