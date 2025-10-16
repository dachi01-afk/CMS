document.addEventListener("DOMContentLoaded", async () => {
    // ===============================
    // 📊 GRAFIK KUNJUNGAN PASIEN
    // ===============================
    const ctx = document.getElementById("kunjunganChart").getContext("2d");

    try {
        const response = await fetch("/admin/chart_kunjungan");
        const data = await response.json();

        new Chart(ctx, {
            type: "bar",
            data: {
                labels: data.labels,
                datasets: [{
                    label: "Jumlah Kunjungan",
                    data: data.values,
                    backgroundColor: "rgba(37, 99, 235, 0.7)",
                    borderColor: "rgba(37, 99, 235, 1)",
                    borderWidth: 1,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: true }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true }
                }
            }
        });
    } catch (err) {
        console.error("Gagal memuat data grafik:", err);
    }

    // ===============================
    // 🧾 CARD MINI — Fetch Data Total
    // ===============================
    async function getData(url, targetId) {
        try {
            const res = await fetch(url);
            const data = await res.json();
            document.getElementById(targetId).textContent = data.total ?? 0;
        } catch (error) {
            console.error(`Gagal ambil data ${targetId}:`, error);
        }
    }

    await Promise.all([
        getData('/admin/total_dokter', "totalDokter"),
        getData('/admin/total_pasien', "totalPasien"),
        getData('/admin/total_apoteker', "totalApoteker"),
        getData('/admin/stok_obat' , "totalObat"),
    ]);
});

