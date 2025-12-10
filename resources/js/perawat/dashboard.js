// resources/js/perawat/dashboard.js
// pastikan chart.js sudah dimuat di layout

$(function () {
    // Helper aman untuk parse JSON dari attribute
    function parseJsonAttr(raw, fallback = []) {
        if (!raw) return fallback;

        // Kalau sudah array, langsung balikin
        if (Array.isArray(raw)) return raw;

        try {
            return JSON.parse(raw);
        } catch (e) {
            console.error("Gagal parse JSON dari data-attr:", raw, e);
            return fallback;
        }
    }

    /* ============================================================
     *  LINE CHART – Grafik Triage 7 Hari Terakhir
     * ============================================================ */
    const $triageCanvas = $("#triageChart");

    if ($triageCanvas.length && typeof Chart !== "undefined") {
        // Pakai .attr supaya selalu dapat string mentah dari HTML
        const rawLabels = $triageCanvas.attr("data-labels");
        const rawValues = $triageCanvas.attr("data-values");

        const labels = parseJsonAttr(rawLabels, []);
        const values = parseJsonAttr(rawValues, []);

        new Chart($triageCanvas, {
            type: "line",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: "Jumlah Triage",
                        data: values,
                        borderColor: "#0ea5e9",
                        backgroundColor: "rgba(14,165,233,0.15)",
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 4,
                        pointBackgroundColor: "#0ea5e9",
                        tension: 0.35,
                        fill: true,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: "index", intersect: false },
                },
                interaction: {
                    mode: "index",
                    intersect: false,
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: "#6b7280", font: { size: 11 } },
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: "rgba(148,163,184,0.25)" },
                        ticks: {
                            stepSize: 1,
                            color: "#6b7280",
                            font: { size: 11 },
                        },
                    },
                },
            },
        });
    }

    /* ============================================================
     *  DONUT CHART – Status Triage (Menunggu / Selesai / Konsultasi)
     * ============================================================ */
    const $statusCanvas = $("#chartStatusTriage");

    if ($statusCanvas.length && typeof Chart !== "undefined") {
        const menunggu = parseInt($statusCanvas.data("menunggu") || 0, 10);
        const triage = parseInt($statusCanvas.data("triage") || 0, 10);
        const konsultasi = parseInt($statusCanvas.data("konsultasi") || 0, 10);
        const total = menunggu + triage + konsultasi;

        new Chart($statusCanvas, {
            type: "doughnut",
            data: {
                labels: [
                    "Menunggu Triage",
                    "Selesai Triage",
                    "Sedang Konsultasi",
                ],
                datasets: [
                    {
                        data: [menunggu, triage, konsultasi],
                        backgroundColor: [
                            "rgba(59,130,246,0.9)",
                            "rgba(16,185,129,0.9)",
                            "rgba(234,179,8,0.9)",
                        ],
                        borderColor: [
                            "rgba(59,130,246,1)",
                            "rgba(16,185,129,1)",
                            "rgba(234,179,8,1)",
                        ],
                        borderWidth: 1,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: "70%",
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const value = ctx.parsed;
                                if (!total) return `${ctx.label}: ${value}`;
                                const percent = ((value / total) * 100).toFixed(
                                    1
                                );
                                return `${ctx.label}: ${value} pasien (${percent}%)`;
                            },
                        },
                    },
                },
            },
        });
    }

    /* ============================================================
     *  PROGRESS BAR – Animasi Persentase Triage
     * ============================================================ */
    const $progress = $("#triageProgressInner");

    if ($progress.length) {
        const target = parseInt($progress.data("percent") || 0, 10);
        let current = 0;

        function animateProgress() {
            current += 2;
            if (current > target) current = target;

            $progress.css("width", current + "%");

            if (current < target) {
                requestAnimationFrame(animateProgress);
            }
        }

        setTimeout(() => requestAnimationFrame(animateProgress), 200);
    }
});
