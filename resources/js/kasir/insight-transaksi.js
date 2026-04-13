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

$(function () {
    function renderDetailItems(items) {
        if (!items || !items.length) {
            $detailItems.html(`
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">
                            Tidak ada detail transaksi.
                        </td>
                    </tr>
                `);
            return;
        }

        const rows = items
            .map(
                (item, index) => `
                <tr class="border-b border-slate-100">
                    <td class="px-4 py-3">${index + 1}</td>
                    <td class="px-4 py-3 font-semibold text-slate-800">${escapeHtml(item.nama_obat || "-")}</td>
                    <td class="px-4 py-3 text-center">${formatNumber(item.jumlah)}</td>
                    <td class="px-4 py-3 text-right">${formatRupiah(item.harga_satuan)}</td>
                    <td class="px-4 py-3 text-right">${formatRupiah(item.sub_total)}</td>
                    <td class="px-4 py-3 text-center">${escapeHtml(formatDiskonLabel(item))}</td>
                    <td class="px-4 py-3 text-right font-extrabold text-emerald-600">${formatRupiah(item.total_setelah_diskon)}</td>
                </tr>
            `,
            )
            .join("");

        $detailItems.html(rows);
    }

    function formatTanggalJam(value) {
        if (!value) return "-";

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return value;

        return date.toLocaleString("id-ID", {
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
    }

    const formatter = new Intl.NumberFormat("id-ID");

    const $detailItems = $("#detailPenjualanItems");

    function formatRupiah(value) {
        return "Rp " + formatter.format(Number(value || 0));
    }

    function formatNumber(value) {
        return formatter.format(Number(value || 0));
    }

    $(document).on("click", ".btn-detail-penjualan", function () {
        const url = $(this).data("url");

        function openDetailModal() {
            $("#modalDetailPenjualan").removeClass("hidden").addClass("flex");
            $("body").addClass("overflow-hidden");
        }

        function resetDetailModal() {
            $("#detailKodeTransaksi").text("-");
            $("#detailNamaPasien").text("-");
            $("#detailTanggalTransaksi").text("-");
            $("#detailStatusTransaksi").text("-");
            $("#detailMetodePembayaran").text("-");
            $("#detailGrandTotal").text("Rp 0");

            $detailItems.html(`
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-sm text-slate-500">
                        Memuat detail transaksi...
                    </td>
                </tr>
            `);
        }

        resetDetailModal();
        openDetailModal();

        $.ajax({
            url: url,
            method: "GET",
            success: function (response) {
                const data = response.data || {};

                $("#detailKodeTransaksi").text(data.kode_transaksi || "-");
                $("#detailNamaPasien").text(data.nama_pasien || "-");
                $("#detailTanggalTransaksi").text(
                    formatTanggalJam(data.tanggal_transaksi),
                );
                $("#detailStatusTransaksi").text(data.status || "-");
                $("#detailMetodePembayaran").text(
                    data.metode_pembayaran || "-",
                );
                $("#detailGrandTotal").text(
                    formatRupiah(data.total_setelah_diskon || 0),
                );

                renderDetailItems(data.details || []);
            },
            error: function () {
                $detailItems.html(`
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-rose-500">
                                Gagal memuat detail transaksi obat.
                            </td>
                        </tr>
                    `);
            },
        });
    });

    $("#btnCloseModalDetailPenjualan").on("click", function () {
        function closeDetailModal() {
            $("#modalDetailPenjualan").removeClass("flex").addClass("hidden");
            $("body").removeClass("overflow-hidden");
        }

        closeDetailModal();
    });

    function escapeHtml(text) {
        return String(text ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatDiskonLabel(item) {
        if (!item.diskon_tipe || Number(item.diskon_nilai || 0) <= 0) {
            return "-";
        }

        if (item.diskon_tipe === "persen") {
            return `${item.diskon_nilai}%`;
        }

        return formatRupiah(item.diskon_nilai);
    }
});
