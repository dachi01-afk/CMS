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
                type: "bar", // 🔹 Ubah dari "line" ke "bar"
                data: {
                    labels,
                    datasets: [
                        {
                            label: "Pemasukan",
                            data: pemasukan,
                            backgroundColor: "rgba(37, 99, 235, 0.6)", // warna biru transparan
                            borderColor: "rgb(37, 99, 235)",
                            borderWidth: 1,
                            borderRadius: 6, // sudut membulat
                            hoverBackgroundColor: "rgba(37, 99, 235, 0.8)",
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
                                    ? `Laporan Keuangan Bulanan - ${getNamaBulan(
                                          bulan
                                      )} ${tahun}`
                                    : filter === "tahunan"
                                    ? `Laporan Keuangan Tahunan - ${tahun}`
                                    : `Laporan Keuangan Mingguan`,
                            font: { size: 16, weight: "bold" },
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) =>
                                    "Rp " +
                                    ctx.parsed.y.toLocaleString("id-ID"),
                            },
                        },
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: "Periode",
                                font: { size: 14 },
                            },
                        },
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: "Jumlah (Rp)",
                                font: { size: 14 },
                            },
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

// === EXPORT EXCEL ===
const exportExcelBtn = document.getElementById("exportExcel");

exportExcelBtn.addEventListener("click", () => {
    const filter = filterKeuangan.value;
    const bulan = bulanKeuangan.value;
    const tahun = tahunKeuangan.value || new Date().getFullYear();

    let url = `/laporan/export-laporan-keuangan?filter=${filter}&tahun=${tahun}`;
    if (filter === "bulanan" && bulan) url += `&bulan=${bulan}`;

    // Arahkan browser untuk mengunduh file Excel
    window.location.href = url;
});
