import $ from "jquery";

$(function () {
    const $chartSection = $("#perawatChartSection");
    const $canvas = $("#perawatDashboardChart");
    const $filter = $("#filterPerawatChart");
    const $range = $("#perawatChartRange");

    const $summaryAssigned = $("#summaryAssignedTotal");
    const $summaryHandled = $("#summaryHandledTotal");

    const initialDataEl = document.getElementById("perawatChartInitialData");

    if (
        !$chartSection.length ||
        !$canvas.length ||
        !$filter.length ||
        !initialDataEl
    ) {
        return;
    }

    let dashboardChart = null;
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
        $summaryAssigned.text(formatNumber(payload.summary_assigned_total));
        $summaryHandled.text(formatNumber(payload.summary_handled_total));
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
                        type: "bar",
                        label: "Area Tugas",
                        data: payload.assigned_total,
                        backgroundColor: "#0ea5e9",
                        borderColor: "#0ea5e9",
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false,
                        categoryPercentage: 0.68,
                        barPercentage: 0.85,
                    },
                    {
                        type: "line",
                        label: "Sudah Ditangani",
                        data: payload.handled_total,
                        borderColor: "#10b981",
                        backgroundColor: "rgba(16,185,129,0.15)",
                        borderWidth: 3,
                        tension: 0.35,
                        fill: false,
                        pointRadius: 4,
                        pointHoverRadius: 5,
                        pointBackgroundColor: "#10b981",
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
                    },
                },
                scales: {
                    x: {
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

        if (dashboardChart) {
            dashboardChart.destroy();
        }

        const ctx = $canvas[0].getContext("2d");
        dashboardChart = new Chart(ctx, chartConfig(payload));
    }

    function loadChartData(filter) {
        $.ajax({
            url: $chartSection.data("chart-url"),
            type: "GET",
            data: { filter },
            beforeSend: function () {
                $filter.prop("disabled", true);
            },
            success: function (response) {
                renderChart(response);
            },
            error: function (xhr) {
                console.error(xhr);
                alert("Gagal memuat data grafik dashboard perawat.");
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
        if (dashboardChart) {
            dashboardChart.resize();
        }
    });
});
