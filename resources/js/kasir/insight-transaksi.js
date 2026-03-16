import $ from "jquery";
import Chart from "chart.js/auto";

$(document).ready(function () {
    const $chartSection = $("#chartSection");
    const $canvas = $("#transaksiChart");
    const $filter = $("#filterChart");
    const $range = $("#chartRange");
    const initialDataEl = document.getElementById("chartInitialData");

    if (
        !$chartSection.length ||
        !$canvas.length ||
        !$filter.length ||
        !initialDataEl
    ) {
        return;
    }

    let chartInstance = null;
    let initialChartData = null;

    try {
        initialChartData = JSON.parse(initialDataEl.textContent || "{}");
    } catch (error) {
        console.error("Gagal membaca initial chart data:", error);
        return;
    }

    function rupiah(nominal) {
        return (
            "Rp " + new Intl.NumberFormat("id-ID").format(Number(nominal) || 0)
        );
    }

    function getMaxTicksLimit(filter) {
        switch (filter) {
            case "harian":
                return 12;
            case "mingguan":
                return 8;
            case "bulanan":
                return 12;
            case "tahunan":
                return 6;
            default:
                return 12;
        }
    }

    function makeChartConfig(payload) {
        return {
            type: "bar",
            data: {
                labels: payload.labels || [],
                datasets: [
                    {
                        type: "bar",
                        label: "Jumlah Transaksi",
                        data: payload.jumlah || [],
                        backgroundColor: "#3b82f6",
                        borderColor: "#3b82f6",
                        borderWidth: 1,
                        borderRadius: 8,
                        yAxisID: "y",
                    },
                    {
                        type: "line",
                        label: "Pendapatan",
                        data: payload.pendapatan || [],
                        borderColor: "#10b981",
                        backgroundColor: "#10b981",
                        borderWidth: 2,
                        tension: 0.35,
                        fill: false,
                        yAxisID: "y1",
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: "index",
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: "top",
                        align: "start",
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const label = context.dataset.label || "";
                                const value = context.parsed.y ?? 0;

                                if (label === "Pendapatan") {
                                    return `${label}: ${rupiah(value)}`;
                                }

                                return `${label}: ${new Intl.NumberFormat("id-ID").format(value)}`;
                            },
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            maxTicksLimit: getMaxTicksLimit(payload.filter),
                        },
                    },
                    y: {
                        beginAtZero: true,
                        position: "left",
                        title: {
                            display: true,
                            text: "Jumlah Transaksi",
                        },
                        ticks: {
                            precision: 0,
                        },
                    },
                    y1: {
                        beginAtZero: true,
                        position: "right",
                        grid: {
                            drawOnChartArea: false,
                        },
                        title: {
                            display: true,
                            text: "Pendapatan (Rp)",
                        },
                        ticks: {
                            callback: function (value) {
                                return rupiah(value);
                            },
                        },
                    },
                },
            },
        };
    }

    function renderChart(payload) {
        if (!payload) return;

        $range.text(payload.range_text || "-");

        const ctx = $canvas[0].getContext("2d");
        if (!ctx) {
            console.error("Canvas context tidak ditemukan.");
            return;
        }

        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        chartInstance = new Chart(ctx, makeChartConfig(payload));
    }

    function loadChartData(filter) {
        $.ajax({
            url: $chartSection.data("chart-url"),
            method: "GET",
            dataType: "json",
            data: { filter: filter },
            beforeSend: function () {
                $filter.prop("disabled", true);
            },
            success: function (response) {
                renderChart(response);
            },
            error: function (xhr) {
                console.error("Gagal load chart:", xhr.responseText || xhr);
                alert("Gagal memuat data chart.");
            },
            complete: function () {
                $filter.prop("disabled", false);
            },
        });
    }

    renderChart(initialChartData);

    $filter.on("change", function () {
        const selectedFilter = $(this).val();
        loadChartData(selectedFilter);
    });

    $(window).on("resize", function () {
        if (chartInstance) {
            chartInstance.resize();
        }
    });
});
