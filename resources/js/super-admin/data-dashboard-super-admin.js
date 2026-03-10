import $ from "jquery";

$(function () {
    const $chartSection = $("#kunjunganChartSection");
    const $canvas = $("#managerChart");
    const $filter = $("#filterKunjunganChart");
    const $range = $("#kunjunganChartRange");
    const initialDataEl = document.getElementById("kunjunganChartInitialData");

    const $summaryTotal = $("#summaryTotalKunjungan");
    const $summaryAktif = $("#summaryKunjunganAktif");
    const $summarySelesai = $("#summaryKunjunganSelesai");
    const $summaryDibatalkan = $("#summaryKunjunganDibatalkan");

    if (
        !$chartSection.length ||
        !$canvas.length ||
        !$filter.length ||
        !initialDataEl
    ) {
        return;
    }

    let managerChart = null;
    let initialChartData = null;

    try {
        initialChartData = JSON.parse(initialDataEl.textContent);
    } catch (error) {
        console.error("Gagal membaca initial chart data:", error);
        return;
    }

    function formatNumber(number) {
        return new Intl.NumberFormat("id-ID").format(number || 0);
    }

    function updateSummaryCards(payload) {
        $summaryTotal.text(formatNumber(payload.summary_total));
        $summaryAktif.text(formatNumber(payload.summary_aktif));
        $summarySelesai.text(formatNumber(payload.summary_selesai));
        $summaryDibatalkan.text(formatNumber(payload.summary_dibatalkan));
    }

    function getMaxTicksLimit(filter) {
        if (filter === "harian") return 12;
        if (filter === "mingguan") return 8;
        if (filter === "bulanan") return 12;
        return 6;
    }

    function chartConfig(payload) {
        return {
            type: "bar",
            data: {
                labels: payload.labels,
                datasets: [
                    {
                        label: "Kunjungan Aktif",
                        data: payload.kunjungan_aktif,
                        backgroundColor: "#f59e0b",
                        borderColor: "#f59e0b",
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false,
                        stack: "kunjungan",
                        categoryPercentage: 0.72,
                        barPercentage: 0.9,
                    },
                    {
                        label: "Kunjungan Selesai",
                        data: payload.kunjungan_selesai,
                        backgroundColor: "#10b981",
                        borderColor: "#10b981",
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false,
                        stack: "kunjungan",
                        categoryPercentage: 0.72,
                        barPercentage: 0.9,
                    },
                    {
                        label: "Kunjungan Dibatalkan",
                        data: payload.kunjungan_dibatalkan,
                        backgroundColor: "#f43f5e",
                        borderColor: "#f43f5e",
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false,
                        stack: "kunjungan",
                        categoryPercentage: 0.72,
                        barPercentage: 0.9,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                resizeDelay: 100,
                interaction: {
                    mode: "index",
                    intersect: false,
                },
                layout: {
                    padding: {
                        top: 8,
                        right: 8,
                        bottom: 0,
                        left: 0,
                    },
                },
                plugins: {
                    legend: {
                        position: "top",
                        align: "start",
                        labels: {
                            usePointStyle: true,
                            boxWidth: 10,
                            boxHeight: 10,
                            color: "#475569",
                            font: {
                                size: 12,
                            },
                        },
                    },
                    tooltip: {
                        backgroundColor: "#0f172a",
                        titleColor: "#ffffff",
                        bodyColor: "#e2e8f0",
                        padding: 12,
                        displayColors: true,
                        callbacks: {
                            footer: function (tooltipItems) {
                                const dataIndex = tooltipItems[0].dataIndex;
                                const total =
                                    payload.total_kunjungan[dataIndex] || 0;
                                return "Total: " + formatNumber(total);
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        stacked: true,
                        grid: {
                            display: false,
                            drawBorder: false,
                        },
                        ticks: {
                            color: "#64748b",
                            maxRotation: 0,
                            minRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: getMaxTicksLimit(payload.filter),
                            font: {
                                size: 11,
                            },
                        },
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        grace: "10%",
                        ticks: {
                            precision: 0,
                            color: "#64748b",
                            font: {
                                size: 11,
                            },
                        },
                        grid: {
                            color: "rgba(148, 163, 184, 0.15)",
                            drawBorder: false,
                        },
                    },
                },
            },
        };
    }

    function renderChart(payload) {
        $range.text(payload.range_text);
        updateSummaryCards(payload);

        if (managerChart) {
            managerChart.destroy();
        }

        const ctx = $canvas[0].getContext("2d");
        managerChart = new Chart(ctx, chartConfig(payload));
    }

    function loadChartData(filter) {
        $.ajax({
            url: $chartSection.data("chart-url"),
            type: "GET",
            data: {
                filter: filter,
            },
            beforeSend: function () {
                $filter.prop("disabled", true);
            },
            success: function (response) {
                renderChart(response);
            },
            error: function (xhr) {
                console.error(xhr);
                alert("Gagal memuat data grafik kunjungan.");
            },
            complete: function () {
                $filter.prop("disabled", false);
            },
        });
    }

    renderChart(initialChartData);

    $filter.on("change", function () {
        loadChartData($(this).val());
    });

    $(window).on("resize", function () {
        if (managerChart) {
            managerChart.resize();
        }
    });
});
