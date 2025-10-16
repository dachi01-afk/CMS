import Chart from "chart.js/auto";
import { getRelativePosition } from "chart.js/helpers";
import axios from "axios";

axios.defaults.headers.common["X-CSRF-TOKEN"] = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");

const data = ["Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu", "Minggu"];

const dataset = [10, 12, 15, 8, 10, 0, 0];

axios.get("testing-chart/kunjungan")
.then(response => {
    const dataKunjungan = response.data;
    console.log(dataKunjungan);
});

const chartjS = document.getElementById("myChartJs");

const chart = new Chart(chartjS, {
    type: "bar",
    data: {
        labels: data,
        datasets: [
            {
                label: "# of Votes",
                data: dataset,
                borderWidth: 1,
            },
        ],
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
            },
        },
        onClick: (e) => {
            const canvasPosition = getRelativePosition(e, chart);

            // Substitute the appropriate scale IDs
            const dataX = chart.scales.x.getValueForPixel(canvasPosition.x);
            const dataY = chart.scales.y.getValueForPixel(canvasPosition.y);
        },
    },
});

// ==== INISIALISASI CHART ====
const ctx = document.getElementById("chartKeuangan");
let chartKeuangan;

// ==== FUNGSI AMBIL DATA DARI SERVER ====
function loadChartData(filter = "mingguan") {
    fetch(`testing-chart/keuangan?filter=${filter}`)
        .then((res) => res.json())
        .then((response) => {
            // ✅ Pastikan yang diproses adalah array
            const data = Array.isArray(response)
                ? response
                : response.data || [];

            // Kalau kosong, kasih peringatan kecil di console
            if (!data.length) {
                console.warn(
                    "⚠️ Tidak ada data keuangan ditemukan untuk filter:",
                    filter
                );
            }

            // Ambil labels & nilai pemasukan
            const labels = data.map((item) => item.periode);
            const pemasukan = data.map(
                (item) => parseFloat(item.pemasukan) || 0
            );

            // NOTE: kalau belum ada pengeluaran, bisa isi array kosong
            const pengeluaran = data.map(() => 0); // nanti bisa diganti real data

            // Jika chart sudah ada, destroy dulu biar gak duplikat
            if (chartKeuangan) chartKeuangan.destroy();

            chartKeuangan = new Chart(ctx, {
                type: "line",
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: "Pemasukan",
                            data: pemasukan,
                            borderColor: "rgb(54, 162, 235)",
                            backgroundColor: "rgba(54, 162, 235, 0.1)",
                            tension: 0.3,
                            fill: true,
                        },
                        {
                            label: "Pengeluaran",
                            data: pengeluaran,
                            borderColor: "rgb(255, 99, 132)",
                            backgroundColor: "rgba(255, 99, 132, 0.1)",
                            tension: 0.3,
                            fill: true,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    interaction: { intersect: false, mode: "index" },
                    plugins: {
                        legend: { position: "bottom" },
                        title: {
                            display: true,
                            text:
                                "Laporan Keuangan " +
                                filter.charAt(0).toUpperCase() +
                                filter.slice(1),
                            font: { size: 16 },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return "Rp " + value.toLocaleString();
                                },
                            },
                        },
                    },
                    onClick: (e) => {
                        const pos = getRelativePosition(e, chartKeuangan);
                        const x = chartKeuangan.scales.x.getValueForPixel(
                            pos.x
                        );
                        const y = chartKeuangan.scales.y.getValueForPixel(
                            pos.y
                        );
                        console.log(`Klik di: X=${x}, Y=${y}`);
                    },
                },
            });
        })
        .catch((err) => console.error("Gagal load chart:", err));
}

// ==== EVENT FILTER ====
document.getElementById("filterKeuangan").addEventListener("change", (e) => {
    const filter = e.target.value;
    loadChartData(filter);
});

// ==== LOAD DEFAULT ====
loadChartData("mingguan");
