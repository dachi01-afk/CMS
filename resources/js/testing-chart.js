import Chart from "chart.js/auto";

const ctx = document.getElementById("chartKeuangan");
const filterKeuangan = document.getElementById("filterKeuangan");
const bulanKeuangan = document.getElementById("bulanKeuangan");
const tahunKeuangan = document.getElementById("tahunKeuangan");

let chartKeuangan;

function loadChartData(
    filter = "mingguan",
    bulan = "",
    tahun = new Date().getFullYear()
) {
    let url = `testing-chart/keuangan?filter=${filter}&tahun=${tahun}`;
    if (filter === "bulanan" && bulan) url += `&bulan=${bulan}`;

    fetch(url)
        .then((res) => res.json())
        .then((response) => {
            const data = Array.isArray(response)
                ? response
                : response.data || [];
            const labels = data.map((item) => item.periode);
            const pemasukan = data.map(
                (item) => parseFloat(item.pemasukan) || 0
            );

            if (chartKeuangan) chartKeuangan.destroy();

            chartKeuangan = new Chart(ctx, {
                type: "line",
                data: {
                    labels,
                    datasets: [
                        {
                            label: "Pemasukan",
                            data: pemasukan,
                            borderColor: "rgb(37, 99, 235)",
                            backgroundColor: "rgba(59, 130, 246, 0.15)",
                            tension: 0.35,
                            fill: true,
                            pointRadius: 4,
                        },
                    ],
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
                                    ? `Laporan Mingguan - ${getNamaBulan(
                                          bulan
                                      )} ${tahun}`
                                    : filter === "tahunan"
                                    ? `Laporan Keuangan Tahunan`
                                    : `Laporan Keuangan Mingguan`,
                            font: { size: 16, weight: "bold" },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: (val) =>
                                    "Rp " + val.toLocaleString("id-ID"),
                            },
                        },
                    },
                },
            });
        })
        .catch((err) => console.error("Gagal load chart:", err));
}

// Helper ubah angka bulan ke teks
function getNamaBulan(bulan) {
    const namaBulan = [
        "Januari",
        "Februari",
        "Maret",
        "April",
        "Mei",
        "Juni",
        "Juli",
        "Agustus",
        "September",
        "Oktober",
        "November",
        "Desember",
    ];
    return namaBulan[parseInt(bulan) - 1];
}

// Event: ganti filter utama
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

// Event: ganti bulan
bulanKeuangan.addEventListener("change", () => {
    const bulan = bulanKeuangan.value;
    const tahun = tahunKeuangan.value;
    if (bulan) loadChartData("bulanan", bulan, tahun);
});

// Event: ganti tahun
tahunKeuangan.addEventListener("change", () => {
    const filter = filterKeuangan.value;
    const bulan = bulanKeuangan.value;
    const tahun = tahunKeuangan.value;
    loadChartData(filter, bulan, tahun);
});

// Load default
loadChartData("mingguan");
