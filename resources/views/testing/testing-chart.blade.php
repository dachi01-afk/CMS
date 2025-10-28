<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/css/app.css', 'resources/js/testing-chart.js'])
    <title>Testing Chart Js</title>
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="max-w-6xl mx-auto min-h-screen py-10 px-6 space-y-12">

        <!-- Laporan Keuangan -->
        <section class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-700 flex items-center gap-2">
                    💰 Laporan Keuangan
                </h2>
                <div class="flex gap-3 items-center">
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

            <div class="relative h-[400px]">
                <canvas id="chartKeuangan"></canvas>
            </div>
        </section>
    </div>

    <script type="module">
        import Chart from "chart.js/auto";

        const ctx = document.getElementById("chartKeuangan");
        const filterKeuangan = document.getElementById("filterKeuangan");
        const bulanKeuangan = document.getElementById("bulanKeuangan");
        const tahunKeuangan = document.getElementById("tahunKeuangan");

        let chartKeuangan;

        function loadChartData(filter = "mingguan", bulan = "", tahun = new Date().getFullYear()) {
            let url = `testing-chart/keuangan?filter=${filter}&tahun=${tahun}`;
            if (filter === "bulanan" && bulan) url += `&bulan=${bulan}`;

            fetch(url)
                .then((res) => res.json())
                .then((response) => {
                    const data = Array.isArray(response)
                        ? response
                        : response.data || [];
                    const labels = data.map((item) => item.periode);
                    const pemasukan = data.map((item) => parseFloat(item.pemasukan) || 0);

                    if (chartKeuangan) chartKeuangan.destroy();

                    chartKeuangan = new Chart(ctx, {
                        type: "line",
                        data: {
                            labels,
                            datasets: [{
                                label: "Pemasukan",
                                data: pemasukan,
                                borderColor: "rgb(37, 99, 235)",
                                backgroundColor: "rgba(59, 130, 246, 0.15)",
                                tension: 0.35,
                                fill: true,
                                pointRadius: 4,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: "bottom" },
                                title: {
                                    display: true,
                                    text:
                                        filter === "bulanan" && bulan
                                            ? `Laporan Mingguan - ${getNamaBulan(bulan)} ${tahun}`
                                            : filter === "tahunan"
                                            ? `Laporan Keuangan Tahun ${tahun}`
                                            : filter === "bulanan"
                                            ? `Laporan Keuangan Bulanan ${tahun}`
                                            : `Laporan Keuangan Mingguan`,
                                    font: { size: 16, weight: "bold" },
                                },
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: (val) => "Rp " + val.toLocaleString("id-ID"),
                                    },
                                },
                            },
                        },
                    });
                })
                .catch((err) => console.error("Gagal load chart:", err));
        }

        function getNamaBulan(bulan) {
            const namaBulan = [
                "Januari", "Februari", "Maret", "April", "Mei", "Juni",
                "Juli", "Agustus", "September", "Oktober", "November", "Desember",
            ];
            return namaBulan[parseInt(bulan) - 1];
        }

        // === Event Listeners ===
        filterKeuangan.addEventListener("change", (e) => {
            const value = e.target.value;

            if (value === "bulanan") {
                bulanKeuangan.classList.remove("hidden");
                tahunKeuangan.classList.remove("hidden");
            } else if (value === "tahunan") {
                bulanKeuangan.classList.add("hidden");
                tahunKeuangan.classList.remove("hidden");
                loadChartData(value, "", tahunKeuangan.value);
            } else {
                bulanKeuangan.classList.add("hidden");
                tahunKeuangan.classList.add("hidden");
                loadChartData(value);
            }
        });

        bulanKeuangan.addEventListener("change", () => {
            const bulan = bulanKeuangan.value;
            const tahun = tahunKeuangan.value;
            if (bulan) loadChartData("bulanan", bulan, tahun);
        });

        tahunKeuangan.addEventListener("change", () => {
            const filter = filterKeuangan.value;
            const bulan = bulanKeuangan.value;
            const tahun = tahunKeuangan.value;
            loadChartData(filter, bulan, tahun);
        });

        // Default: tampil mingguan
        loadChartData("mingguan");
    </script>
</body>
</html>
