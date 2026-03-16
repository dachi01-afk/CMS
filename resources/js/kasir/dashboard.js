import $ from "jquery";

$(function () {
    const $chartSection = $("#transaksiChartSection");
    const $canvas = $("#kasirTransactionChart");
    const $filter = $("#filterTransaksiChart");
    const $range = $("#transaksiChartRange");

    const initialDataEl = document.getElementById("transaksiChartInitialData");

    const $summaryTotal = $("#summaryTotalTransaksi");
    const $summaryPembayaran = $("#summaryPembayaran");
    const $summaryObat = $("#summaryObat");
    const $summaryLayanan = $("#summaryLayanan");

    if (
        !$chartSection.length ||
        !$canvas.length ||
        !$filter.length ||
        !initialDataEl
    ) {
        return;
    }

    let kasirChart = null;
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
        $summaryPembayaran.text(formatNumber(payload.summary_pembayaran));
        $summaryObat.text(formatNumber(payload.summary_obat));
        $summaryLayanan.text(formatNumber(payload.summary_layanan));
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
                        label: "Pembayaran",
                        data: payload.pembayaran,
                        backgroundColor: "#3b82f6",
                        borderColor: "#3b82f6",
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false,
                        stack: "transaksi",
                        categoryPercentage: 0.72,
                        barPercentage: 0.9,
                    },
                    {
                        label: "Transaksi Obat",
                        data: payload.obat,
                        backgroundColor: "#10b981",
                        borderColor: "#10b981",
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false,
                        stack: "transaksi",
                        categoryPercentage: 0.72,
                        barPercentage: 0.9,
                    },
                    {
                        label: "Transaksi Layanan",
                        data: payload.layanan,
                        backgroundColor: "#f59e0b",
                        borderColor: "#f59e0b",
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false,
                        stack: "transaksi",
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
                                    payload.total_transaksi[dataIndex] || 0;
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

        if (kasirChart) {
            kasirChart.destroy();
        }

        const ctx = $canvas[0].getContext("2d");
        kasirChart = new Chart(ctx, chartConfig(payload));
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
                alert("Gagal memuat data grafik transaksi.");
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
        if (kasirChart) {
            kasirChart.resize();
        }
    });
});
