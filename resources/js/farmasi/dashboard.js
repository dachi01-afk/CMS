document.addEventListener("DOMContentLoaded", async () => {
    const ctx = document.getElementById("chartPenjualanObat")?.getContext("2d");
    const rangeEl = document.getElementById("rangeFilter");
    const titleEl = document.getElementById("chartTitle");
    let chart;

    const fmtTanggal = (iso) => {
        if (!iso) return "";
        const d = new Date(iso);
        return d.toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
    };

    function setTitle(range, meta) {
        if (!titleEl || !meta) return;
        if (range === "harian")
            titleEl.textContent = `Penjualan Obat Hari Ini — ${fmtTanggal(
                meta.tanggal
            )}`;
        if (range === "mingguan")
            titleEl.textContent = `Penjualan Obat Minggu Ini (${fmtTanggal(
                meta.start
            )} - ${fmtTanggal(meta.end)})`;
        if (range === "bulanan")
            titleEl.textContent = `Penjualan Obat Bulan ${new Date(
                meta.bulan + "-01"
            ).toLocaleDateString("id-ID", { month: "long", year: "numeric" })}`;
        if (range === "tahunan")
            titleEl.textContent = `Penjualan Obat Tahun ${meta.tahun}`;
    }

    async function loadChart(range = "harian") {
        const url = `/farmasi/chart/penjualan-obat?range=${encodeURIComponent(
            range
        )}`;
        const res = await fetch(url, {
            headers: { "X-Requested-With": "XMLHttpRequest" },
        });
        const data = await res.json();

        setTitle(range, data.meta);

        const options = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
                tooltip: {
                    mode: "index",
                    intersect: false,
                    callbacks: {
                        label: (ctx) => {
                            const label = ctx.dataset.label || "";
                            const v = ctx.parsed.y ?? ctx.raw ?? 0;
                            if (label.includes("Rp"))
                                return `${label}: Rp ${Number(v).toLocaleString(
                                    "id-ID"
                                )}`;
                            return `${label}: ${v}`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    title: { display: true, text: data.meta?.x_title ?? "" },
                },
                y: {
                    beginAtZero: true,
                    title: { display: true, text: "Jumlah Transaksi" },
                },
                y1: {
                    beginAtZero: true,
                    position: "right",
                    grid: { drawOnChartArea: false },
                    title: { display: true, text: "Total Pemasukan (Rp)" },
                    ticks: {
                        callback: (v) =>
                            "Rp " + Number(v).toLocaleString("id-ID"),
                    },
                },
            },
        };

        if (chart) {
            chart.data.labels = data.label;
            chart.data.datasets = data.dataset;
            chart.options = options;
            chart.update();
        } else {
            chart = new Chart(ctx, {
                type: "bar",
                data: { labels: data.label, datasets: data.dataset },
                options,
            });
        }
    }

    // ===============================
    // 🧾 CARD MINI — Fetch Data Total
    // ===============================
    async function getData(url, targetId) {
        try {
            const res = await fetch(url, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            const data = await res.json();
            const el = document.getElementById(targetId);
            if (el) el.textContent = (data.total ?? 0).toLocaleString("id-ID");
        } catch (error) {
            console.error(`Gagal ambil data ${targetId}:`, error);
        }
    }

    // initial loads
    await Promise.all([
        loadChart(rangeEl?.value || "harian"),
        // jika nanti kamu hidupkan kartu lain, tinggal buka komentar di bawah
        getData(
            "/farmasi/get-jumlah-penjualan-obat-hari-ini",
            "totalPenjualanObatHariIni"
        ),
        getData(
            "/farmasi/get-jumlah-keseluruhan-penjualan-obat",
            "totalKeseluruhanTransaksiObat"
        ),
        // getData("/admin/total_farmasi", "totalFarmasi"),
        getData("/farmasi/get-jumlah-stok-obat", "totalObat"),
    ]);

    // filter change
    rangeEl?.addEventListener("change", (e) => loadChart(e.target.value));
});
