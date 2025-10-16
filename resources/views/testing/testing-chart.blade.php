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

<body class=" overflow-auto">
    <div class="max-w-7xl mx-auto grid items-center justify-center h-screen py-12 px-8 lg:px-0 ">
        <div >
            <h2 class="text-xl font-semibold text-center">Laporan Kunjungan</h2>

            <div class="flex justify-end">
                <select id="filterKunjungan" class="border rounded p-2">
                    <option value="mingguan">Per Minggu</option>
                    <option value="bulanan">Per Bulan</option>
                    <option value="tahunan">Per Tahun</option>
                </select>
            </div>
            <canvas id="myChartJs" class=""></canvas>
        </div>

        <div class="mt-10">
            <h2 class="text-xl font-semibold text-center">Laporan Keuangan</h2>

            <div class="flex justify-end">
                <select id="filterKeuangan" class="border rounded p-2">
                    <option value="mingguan">Per Minggu</option>
                    <option value="bulanan">Per Bulan</option>
                    <option value="tahunan">Per Tahun</option>
                </select>
            </div>
            <canvas id="chartKeuangan" class="pb-10"></canvas>
        </div>
    </div>

</body>

</html>
