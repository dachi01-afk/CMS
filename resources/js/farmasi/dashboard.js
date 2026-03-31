$(document).ready(function () {
    let chartInstance = null;
    const defaultPeriode = "tahunan";
    const formatter = new Intl.NumberFormat("id-ID");

    function formatNumber(value) {
        return formatter.format(Number(value || 0));
    }

    function formatRupiah(value) {
        return "Rp " + formatter.format(Number(value || 0));
    }

    function escapeHtml(text) {
        return String(text ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatTanggal(iso) {
        if (!iso) return "-";

        const date = new Date(iso);
        if (isNaN(date.getTime())) return iso;

        return date.toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
    }

    function formatTanggalJam(iso) {
        if (!iso) return "-";

        const date = new Date(iso);
        if (isNaN(date.getTime())) return iso;

        return date.toLocaleString("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    }

    function formatModeLabel(mode) {
        if (mode === "harian") return "Harian";
        if (mode === "mingguan") return "Mingguan";
        if (mode === "bulanan") return "Bulanan";
        if (mode === "tahunan") return "Tahunan";
        return "Tahunan";
    }

    function getCurrentWeekInputValue() {
        const now = new Date();
        const date = new Date(
            Date.UTC(now.getFullYear(), now.getMonth(), now.getDate()),
        );
        const dayNum = date.getUTCDay() || 7;
        date.setUTCDate(date.getUTCDate() + 4 - dayNum);
        const yearStart = new Date(Date.UTC(date.getUTCFullYear(), 0, 1));
        const weekNo = Math.ceil(((date - yearStart) / 86400000 + 1) / 7);

        return `${date.getUTCFullYear()}-W${String(weekNo).padStart(2, "0")}`;
    }

    function initDefaultFilterValues() {
        if (!$("#filterTanggalChart").val()) {
            $("#filterTanggalChart").val(
                new Date().toISOString().split("T")[0],
            );
        }

        if (!$("#filterMingguChart").val()) {
            $("#filterMingguChart").val(getCurrentWeekInputValue());
        }

        if (!$("#filterBulanChart").val()) {
            $("#filterBulanChart").val(new Date().toISOString().slice(0, 7));
        }

        if (!$("#filterTahunChart").val()) {
            $("#filterTahunChart").val(new Date().getFullYear());
        }
    }

    function toggleChartFilterInputs() {
        const periode = $("#filterPeriodeChart").val();

        $(
            "#filterHarianWrap, #filterMingguanWrap, #filterBulananWrap, #filterTahunanWrap",
        ).addClass("hidden");

        if (periode === "harian") {
            $("#filterHarianWrap").removeClass("hidden");
        } else if (periode === "mingguan") {
            $("#filterMingguanWrap").removeClass("hidden");
        } else if (periode === "bulanan") {
            $("#filterBulananWrap").removeClass("hidden");
        } else {
            $("#filterTahunanWrap").removeClass("hidden");
        }
    }

    function getChartParams() {
        const periode = $("#filterPeriodeChart").val();
        const params = { periode };

        if (periode === "harian") {
            params.tanggal = $("#filterTanggalChart").val();
        } else if (periode === "mingguan") {
            params.minggu = $("#filterMingguChart").val();
        } else if (periode === "bulanan") {
            params.bulan = $("#filterBulanChart").val();
        } else {
            params.tahun = $("#filterTahunChart").val();
        }

        return params;
    }

    function buildStockBadge(status) {
        if (status === "Habis") {
            return `<span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-600">Habis</span>`;
        }

        return `<span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-600">Menipis</span>`;
    }

    function buildTransactionBadge(status) {
        const safeStatus = escapeHtml(status || "-");

        if (status === "Sudah Bayar") {
            return `<span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-600">${safeStatus}</span>`;
        }

        if (status === "Belum Bayar") {
            return `<span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-600">${safeStatus}</span>`;
        }

        if (status === "Batal") {
            return `<span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-600">${safeStatus}</span>`;
        }

        return `<span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">${safeStatus}</span>`;
    }

    function setProgressBar(selector, value, maxValue) {
        const percent =
            maxValue > 0
                ? Math.round((Number(value || 0) / Number(maxValue || 0)) * 100)
                : 0;
        const finalWidth = Number(value || 0) > 0 ? Math.max(percent, 8) : 0;
        $(selector).css("width", `${finalWidth}%`);
    }

    async function loadSummary() {
        try {
            const response = await $.getJSON("/farmasi/dashboard/summary");
            const data = response.data || {};
            const meta = response.meta || {};

            $("#totalStokObat").text(formatNumber(data.total_stok_obat));
            $("#stokMenipis").text(formatNumber(data.stok_menipis));
            $("#stokHabis").text(formatNumber(data.stok_habis));
            $("#pemasukanHariIni").text(data.pemasukan_hari_ini);
            $("#totalPenjualanObat").text(
                formatNumber(data.total_keseluruhan_transaksi),
            );

            $("#stokMenipisInfo").text(
                `Batas stok kritis 1 - ${formatNumber(meta.batas_stok_menipis || 10)}`,
            );

            $("#totalKeseluruhanTransaksiObat").text(
                `Total transaksi hari ini: ${formatNumber(data.transaksi_hari_ini)}`,
            );

            $("#quickTransaksiHariIni").text(
                formatNumber(data.transaksi_hari_ini),
            );
            $("#quickStokMenipis").text(formatNumber(data.stok_menipis));
            $("#quickStokHabis").text(formatNumber(data.stok_habis));

            const maxBar = Math.max(
                Number(data.transaksi_hari_ini || 0),
                Number(data.stok_menipis || 0),
                Number(data.stok_habis || 0),
                1,
            );

            setProgressBar(
                "#barTransaksiHariIni",
                data.transaksi_hari_ini,
                maxBar,
            );
            setProgressBar("#barStokMenipis", data.stok_menipis, maxBar);
            setProgressBar("#barStokHabis", data.stok_habis, maxBar);
        } catch (error) {
            console.error("Gagal memuat summary dashboard farmasi:", error);
        }
    }

    async function loadCriticalStocks() {
        try {
            const response = await $.getJSON("/farmasi/dashboard/stok-kritis");
            const items = response.data || [];

            if (!items.length) {
                $("#criticalStockTableBody").html(`
                    <tr>
                        <td colspan="3" class="px-5 py-8 text-center text-sm text-slate-400">
                            Tidak ada obat dengan stok kritis.
                        </td>
                    </tr>
                `);

                $("#quickCriticalStockList").html(`
                    <div class="text-sm text-slate-400">
                        Tidak ada alert stok kritis saat ini.
                    </div>
                `);
                return;
            }

            let tableRows = "";
            let quickRows = "";

            $.each(items, function (index, item) {
                tableRows += `
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-4 font-semibold text-slate-800">
                            ${escapeHtml(item.nama_obat)}
                        </td>
                        <td class="px-5 py-4">
                            <span class="font-extrabold ${Number(item.stok) <= 0 ? "text-rose-500" : "text-amber-500"}">
                                ${formatNumber(item.stok)}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            ${buildStockBadge(item.status_stok)}
                        </td>
                    </tr>
                `;

                if (index < 4) {
                    quickRows += `
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 px-3 py-3">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">
                                    ${escapeHtml(item.nama_obat)}
                                </p>
                                <p class="mt-1 text-xs text-slate-400">
                                    Status: ${escapeHtml(item.status_stok)}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-extrabold ${Number(item.stok) <= 0 ? "text-rose-500" : "text-amber-500"}">
                                    ${formatNumber(item.stok)}
                                </p>
                                <p class="text-[11px] text-slate-400">stok</p>
                            </div>
                        </div>
                    `;
                }
            });

            $("#criticalStockTableBody").html(tableRows);
            $("#quickCriticalStockList").html(quickRows);
        } catch (error) {
            console.error("Gagal memuat stok kritis:", error);

            $("#criticalStockTableBody").html(`
                <tr>
                    <td colspan="3" class="px-5 py-8 text-center text-sm text-rose-400">
                        Gagal memuat data stok kritis.
                    </td>
                </tr>
            `);

            $("#quickCriticalStockList").html(`
                <div class="text-sm text-rose-400">
                    Gagal memuat alert stok.
                </div>
            `);
        }
    }

    async function loadRecentTransactions() {
        try {
            const response = await $.getJSON(
                "/farmasi/dashboard/transaksi-terbaru",
            );
            const items = response.data || [];

            if (!items.length) {
                $("#recentTransactionTableBody").html(`
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-sm text-slate-400">
                            Belum ada transaksi terbaru.
                        </td>
                    </tr>
                `);
                return;
            }

            let rows = "";

            $.each(items, function (_, item) {
                rows += `
                    <tr class="hover:bg-slate-50">
                        <td class="px-5 py-4 font-bold text-slate-800">
                            ${escapeHtml(item.kode_transaksi)}
                        </td>
                        <td class="px-5 py-4 text-slate-600">
                            ${escapeHtml(item.pasien.nama_pasien || "-")}
                        </td>
                        <td class="px-5 py-4 text-slate-600">
                            ${formatTanggalJam(item.tanggal_transaksi)}
                        </td>
                        <td class="px-5 py-4">
                            ${buildTransactionBadge(item.status)}
                        </td>
                        <td class="px-5 py-4 text-right font-extrabold text-slate-800">
                            ${formatRupiah(item.total_setelah_diskon)}
                        </td>
                    </tr>
                `;
            });

            $("#recentTransactionTableBody").html(rows);
        } catch (error) {
            console.error("Gagal memuat transaksi terbaru:", error);

            $("#recentTransactionTableBody").html(`
                <tr>
                    <td colspan="5" class="px-5 py-8 text-center text-sm text-rose-400">
                        Gagal memuat data transaksi terbaru.
                    </td>
                </tr>
            `);
        }
    }

    function normalizeBarDatasets(datasets, labels) {
        return (datasets || []).map(function (dataset) {
            const isPemasukan =
                (dataset.label || "").toLowerCase().includes("pemasukan") ||
                (dataset.label || "").toLowerCase().includes("rp");

            return {
                label: dataset.label || "",
                data: dataset.data || [],
                type: "bar",
                yAxisID: dataset.yAxisID || (isPemasukan ? "y1" : "y"),
                backgroundColor: isPemasukan
                    ? "rgba(59, 130, 246, 0.82)"
                    : "rgba(16, 185, 129, 0.82)",
                borderColor: isPemasukan ? "#3b82f6" : "#10b981",
                borderWidth: 1,
                borderRadius: 12,
                borderSkipped: false,
                maxBarThickness: (labels || []).length === 1 ? 90 : 34,
            };
        });
    }

    function renderChart(labels, datasets) {
        const canvas = document.getElementById("chartPenjualanObat");
        if (!canvas) return;

        const ctx = canvas.getContext("2d");

        if (chartInstance) {
            chartInstance.destroy();
        }

        chartInstance = new Chart(ctx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: normalizeBarDatasets(datasets, labels),
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: 0,
                },
                interaction: {
                    mode: "index",
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: true,
                        position: "top",
                        labels: {
                            color: "#334155",
                            font: {
                                size: 11,
                                weight: "600",
                            },
                            usePointStyle: true,
                            pointStyle: "rectRounded",
                        },
                    },
                    tooltip: {
                        backgroundColor: "#0f172a",
                        titleColor: "#ffffff",
                        bodyColor: "#ffffff",
                        padding: 12,
                        callbacks: {
                            label: function (context) {
                                const label = context.dataset.label || "";
                                const value = context.raw ?? 0;

                                if (
                                    (context.dataset.yAxisID || "") === "y1" ||
                                    label.toLowerCase().includes("rp")
                                ) {
                                    return ` ${label}: ${formatRupiah(value)}`;
                                }

                                return ` ${label}: ${formatNumber(value)}`;
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
                            color: "#64748b",
                            font: {
                                size: 11,
                            },
                        },
                        title: {
                            display: false,
                        },
                    },
                    y: {
                        beginAtZero: true,
                        position: "left",
                        ticks: {
                            color: "#64748b",
                            precision: 0,
                            callback: function (value) {
                                return formatNumber(value);
                            },
                        },
                        grid: {
                            color: "rgba(148, 163, 184, 0.15)",
                            drawBorder: false,
                        },
                        title: {
                            display: true,
                            text: "Jumlah Transaksi",
                            color: "#64748b",
                            font: {
                                size: 12,
                                weight: "600",
                            },
                        },
                    },
                    y1: {
                        beginAtZero: true,
                        position: "right",
                        ticks: {
                            color: "#64748b",
                            callback: function (value) {
                                return formatRupiah(value);
                            },
                        },
                        grid: {
                            drawOnChartArea: false,
                            drawBorder: false,
                        },
                        title: {
                            display: true,
                            text: "Total Pemasukan (Rp)",
                            color: "#64748b",
                            font: {
                                size: 12,
                                weight: "600",
                            },
                        },
                    },
                },
            },
        });
    }

    function loadChart() {
        if ($("#heroFilterBadge").length) {
            $("#heroFilterBadge").text("Memuat grafik...");
        }

        return $.getJSON("/farmasi/chart/penjualan-obat", getChartParams())
            .done(function (response) {
                const labels = response.labels || [];
                const datasets = response.datasets || [];
                const values = response.values || [];
                const totalTransaksi = values.reduce(
                    (sum, item) => sum + Number(item || 0),
                    0,
                );

                renderChart(labels, datasets);

                $("#chartTitle").text(
                    response.chart_title ||
                        `Penjualan Obat ${response.mode_label || "-"} — ${response.filter_label || "-"}`,
                );

                if ($("#heroModePeriode").length) {
                    $("#heroModePeriode").text(response.mode_label || "-");
                }

                if ($("#heroFilterBadge").length) {
                    $("#heroFilterBadge").text("Grafik berhasil dimuat");
                }

                if ($("#chartSummary").length) {
                    $("#chartSummary").text(
                        `${formatNumber(totalTransaksi)} transaksi tercatat pada ${response.filter_label || "periode terpilih"}.`,
                    );
                }

                if ($("#chartModeText").length) {
                    $("#chartModeText").text(response.mode_label || "-");
                }

                if ($("#chartModeHero").length) {
                    $("#chartModeHero").text(response.mode_label || "-");
                }

                if ($("#chartActiveFilter").length) {
                    $("#chartActiveFilter").text(response.filter_label || "-");
                }

                if ($("#heroPeriodeBadge").length) {
                    $("#heroPeriodeBadge").html(
                        `<i class="fa-solid fa-calendar-days"></i>${response.filter_label || "-"}`,
                    );
                }
            })
            .fail(function () {
                $("#chartTitle").text("Gagal memuat grafik penjualan obat.");

                if ($("#heroFilterBadge").length) {
                    $("#heroFilterBadge").text("Grafik gagal dimuat");
                }

                if ($("#chartSummary").length) {
                    $("#chartSummary").text("Data grafik gagal dimuat.");
                }

                if ($("#chartModeText").length) {
                    $("#chartModeText").text("-");
                }

                if ($("#chartModeHero").length) {
                    $("#chartModeHero").text("-");
                }

                if ($("#chartActiveFilter").length) {
                    $("#chartActiveFilter").text("-");
                }

                console.error("Gagal memuat data grafik penjualan obat.");
            });
    }

    function resetChartFilter() {
        $("#filterPeriodeChart").val(defaultPeriode);
        $("#filterTanggalChart").val(new Date().toISOString().split("T")[0]);
        $("#filterMingguChart").val(getCurrentWeekInputValue());
        $("#filterBulanChart").val(new Date().toISOString().slice(0, 7));
        $("#filterTahunChart").val(new Date().getFullYear());

        toggleChartFilterInputs();
        loadChart();
    }

    $("#filterPeriodeChart").on("change", function () {
        toggleChartFilterInputs();
    });

    $("#btnApplyChartFilter").on("click", function () {
        loadChart();
    });

    $("#btnResetDashboardFilter").on("click", function () {
        resetChartFilter();
    });

    initDefaultFilterValues();
    toggleChartFilterInputs();
    loadSummary();
    loadCriticalStocks();
    loadRecentTransactions();
    loadChart();

    $("#dashboardUpdatedAt").text(
        `Update terakhir: ${new Date().toLocaleString("id-ID", {
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        })}`,
    );
});
