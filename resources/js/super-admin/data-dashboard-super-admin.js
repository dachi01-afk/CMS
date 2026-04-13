import $ from "jquery";

$(function () {
    const $chartSection = $("#kunjunganChartSection");
    const $canvas = $("#managerChart");
    const $filter = $("#filterKunjunganChart");
    const $range = $("#kunjunganChartRange");

    const $wrapperTanggal = $("#wrapperTanggalKunjungan");
    const $wrapperMinggu = $("#wrapperMingguKunjungan");
    const $wrapperBulan = $("#wrapperBulanKunjungan");
    const $wrapperTahun = $("#wrapperTahunKunjungan");

    const $tanggal = $("#filterTanggalKunjungan");
    const $minggu = $("#filterMingguKunjungan");
    const $bulan = $("#filterBulanKunjungan");
    const $tahun = $("#filterTahunKunjungan");

    const $reportDropdownWrapper = $("#reportDropdownWrapper");
    const $reportDropdownMenu = $("#reportDropdownMenu");
    const $toggleReportDropdown = $("#btnToggleReportDropdown");
    const $reportPdf = $("#btnReportPdfKunjungan");
    const $reportExcel = $("#btnReportExcelKunjungan");

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

    function getCurrentParams() {
        const filter = $filter.val();
        const params = { filter };

        if (filter === "harian") {
            params.tanggal = $tanggal.val();
        } else if (filter === "mingguan") {
            params.minggu = $minggu.val();
        } else if (filter === "bulanan") {
            params.bulan = $bulan.val();
        } else if (filter === "tahunan") {
            params.tahun = $tahun.val();
        }

        return params;
    }

    function buildQueryString(params) {
        const searchParams = new URLSearchParams();

        Object.entries(params).forEach(([key, value]) => {
            if (
                value !== null &&
                value !== undefined &&
                String(value).trim() !== ""
            ) {
                searchParams.set(key, value);
            }
        });

        return searchParams.toString();
    }

    function updateReportLinks(params = getCurrentParams()) {
        const pdfBaseUrl = $reportPdf.data("base-url");
        const excelBaseUrl = $reportExcel.data("base-url");
        const queryString = buildQueryString(params);

        if (pdfBaseUrl) {
            $reportPdf.attr("href", `${pdfBaseUrl}?${queryString}`);
        }

        if (excelBaseUrl) {
            $reportExcel.attr("href", `${excelBaseUrl}?${queryString}`);
        }
    }

    function getValidationMessage(params = getCurrentParams()) {
        if (params.filter === "harian" && !params.tanggal) {
            return "Silakan pilih tanggal terlebih dahulu.";
        }

        if (params.filter === "mingguan" && !params.minggu) {
            return "Silakan pilih minggu terlebih dahulu.";
        }

        if (params.filter === "bulanan" && !params.bulan) {
            return "Silakan pilih bulan terlebih dahulu.";
        }

        if (params.filter === "tahunan" && !params.tahun) {
            return "Silakan input tahun terlebih dahulu.";
        }

        return "";
    }

    function validateCurrentParams(showAlert = true) {
        const message = getValidationMessage();

        if (!message) {
            return true;
        }

        if (showAlert) {
            alert(message);
        }

        return false;
    }

    function syncInputsFromPayload(payload) {
        if (!payload || !payload.selected) {
            return;
        }

        if (payload.selected.tanggal) {
            $tanggal.val(payload.selected.tanggal);
        }

        if (payload.selected.minggu) {
            $minggu.val(payload.selected.minggu);
        }

        if (payload.selected.bulan) {
            $bulan.val(payload.selected.bulan);
        }

        if (payload.selected.tahun) {
            $tahun.val(payload.selected.tahun);
        }
    }

    function togglePeriodInputs(filter) {
        $wrapperTanggal.addClass("hidden");
        $wrapperMinggu.addClass("hidden");
        $wrapperBulan.addClass("hidden");
        $wrapperTahun.addClass("hidden");

        $tanggal.prop("disabled", true);
        $minggu.prop("disabled", true);
        $bulan.prop("disabled", true);
        $tahun.prop("disabled", true);

        if (filter === "harian") {
            $wrapperTanggal.removeClass("hidden");
            $tanggal.prop("disabled", false);
        } else if (filter === "mingguan") {
            $wrapperMinggu.removeClass("hidden");
            $minggu.prop("disabled", false);
        } else if (filter === "bulanan") {
            $wrapperBulan.removeClass("hidden");
            $bulan.prop("disabled", false);
        } else if (filter === "tahunan") {
            $wrapperTahun.removeClass("hidden");
            $tahun.prop("disabled", false);
        }
    }

    function setLoadingState(isLoading) {
        $filter.prop("disabled", isLoading);

        if (isLoading) {
            $tanggal.prop("disabled", true);
            $minggu.prop("disabled", true);
            $bulan.prop("disabled", true);
            $tahun.prop("disabled", true);
        } else {
            togglePeriodInputs($filter.val());
        }

        $toggleReportDropdown
            .prop("disabled", isLoading)
            .toggleClass("opacity-60", isLoading);
    }

    function getMaxTicksLimit(filter) {
        if (filter === "harian") return 1;
        if (filter === "mingguan") return 7;
        if (filter === "bulanan") return 12;
        return 12;
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
                        backgroundColor: "rgba(245, 158, 11, 0.85)",
                        borderColor: "#f59e0b",
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false,
                        categoryPercentage: 0.68,
                        barPercentage: 0.78,
                        order: 2,
                    },
                    {
                        label: "Kunjungan Selesai",
                        data: payload.kunjungan_selesai,
                        backgroundColor: "rgba(16, 185, 129, 0.85)",
                        borderColor: "#10b981",
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false,
                        categoryPercentage: 0.68,
                        barPercentage: 0.78,
                        order: 2,
                    },
                    {
                        label: "Kunjungan Dibatalkan",
                        data: payload.kunjungan_dibatalkan,
                        backgroundColor: "rgba(244, 63, 94, 0.85)",
                        borderColor: "#f43f5e",
                        borderWidth: 1,
                        borderRadius: 8,
                        borderSkipped: false,
                        categoryPercentage: 0.68,
                        barPercentage: 0.78,
                        order: 2,
                    },
                    {
                        type: "line",
                        label: "Total Kunjungan",
                        data: payload.total_kunjungan,
                        borderColor: "#0f172a",
                        backgroundColor: "#0f172a",
                        borderWidth: 2,
                        tension: 0.32,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBorderWidth: 2,
                        pointBackgroundColor: "#ffffff",
                        pointBorderColor: "#0f172a",
                        fill: false,
                        order: 1,
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
                            label: function (context) {
                                return (
                                    context.dataset.label +
                                    ": " +
                                    formatNumber(context.parsed.y || 0)
                                );
                            },
                        },
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
                        grace: "12%",
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
        syncInputsFromPayload(payload);
        $range.text(payload.range_text);
        updateSummaryCards(payload);
        updateReportLinks({
            filter: payload.filter,
            ...(payload.selected || {}),
        });

        if (managerChart) {
            managerChart.destroy();
        }

        const ctx = $canvas[0].getContext("2d");
        managerChart = new Chart(ctx, chartConfig(payload));
    }

    function loadChartData() {
        if (!validateCurrentParams()) {
            return;
        }

        const params = getCurrentParams();
        updateReportLinks(params);

        $.ajax({
            url: $chartSection.data("chart-url"),
            type: "GET",
            data: params,
            beforeSend: function () {
                setLoadingState(true);
            },
            success: function (response) {
                renderChart(response);
            },
            error: function (xhr) {
                console.error(xhr);
                alert("Gagal memuat data grafik kunjungan.");
            },
            complete: function () {
                setLoadingState(false);
            },
        });
    }

    $toggleReportDropdown.on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();

        if (!validateCurrentParams()) {
            return;
        }

        updateReportLinks();
        $reportDropdownMenu.toggleClass("hidden");
    });

    $(document).on("click", function (e) {
        if (
            !$reportDropdownWrapper.is(e.target) &&
            $reportDropdownWrapper.has(e.target).length === 0
        ) {
            $reportDropdownMenu.addClass("hidden");
        }
    });

    $reportPdf.on("click", function (e) {
        if (!validateCurrentParams()) {
            e.preventDefault();
            return;
        }

        updateReportLinks();
        $reportDropdownMenu.addClass("hidden");
    });

    $reportExcel.on("click", function (e) {
        if (!validateCurrentParams()) {
            e.preventDefault();
            return;
        }

        updateReportLinks();
        $reportDropdownMenu.addClass("hidden");
    });

    $filter.on("change", function () {
        togglePeriodInputs($(this).val());
        updateReportLinks();
        loadChartData();
    });

    $tanggal.on("change", function () {
        if ($filter.val() === "harian") {
            updateReportLinks();
            loadChartData();
        }
    });

    $minggu.on("change", function () {
        if ($filter.val() === "mingguan") {
            updateReportLinks();
            loadChartData();
        }
    });

    $bulan.on("change", function () {
        if ($filter.val() === "bulanan") {
            updateReportLinks();
            loadChartData();
        }
    });

    $tahun.on("change", function () {
        if ($filter.val() === "tahunan") {
            updateReportLinks();
            loadChartData();
        }
    });

    togglePeriodInputs(initialChartData.filter || $filter.val());
    renderChart(initialChartData);

    $(window).on("resize", function () {
        if (managerChart) {
            managerChart.resize();
        }
    });
});
