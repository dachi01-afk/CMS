$(document).ready(function () {
    let kunjunganChartInstance = null;
    const formatter = new Intl.NumberFormat("id-ID");

    function formatNumber(value) {
        return formatter.format(Number(value || 0));
    }

    function setValue(selector, value) {
        $(selector).text(formatNumber(value));
    }

    function setStatus(text, type = "loading") {
        const $status = $("#dashboardStatus");

        $status.removeClass(
            "bg-amber-50 text-amber-700 bg-emerald-50 text-emerald-700 bg-rose-50 text-rose-700",
        );

        if (type === "success") {
            $status.addClass("bg-emerald-50 text-emerald-700");
        } else if (type === "error") {
            $status.addClass("bg-rose-50 text-rose-700");
        } else {
            $status.addClass("bg-amber-50 text-amber-700");
        }

        $status.html(
            `<span class="h-2 w-2 rounded-full bg-current"></span>${text}`,
        );
    }

    function setProgress(barSelector, value, maxValue) {
        const percent = maxValue > 0 ? Math.round((value / maxValue) * 100) : 0;
        const finalWidth = value > 0 ? Math.max(percent, 8) : 0;
        $(barSelector).css("width", `${finalWidth}%`);
    }

    function updateDistribution(stats) {
        const maxValue = Math.max(
            stats.dokter,
            stats.pasien,
            stats.farmasi,
            stats.obat,
            1,
        );

        setValue("#totalDokterMini", stats.dokter);
        setValue("#totalPasienMini", stats.pasien);
        setValue("#totalFarmasiMini", stats.farmasi);
        setValue("#totalObatMini", stats.obat);

        setProgress("#barDokter", stats.dokter, maxValue);
        setProgress("#barPasien", stats.pasien, maxValue);
        setProgress("#barFarmasi", stats.farmasi, maxValue);
        setProgress("#barObat", stats.obat, maxValue);
    }

    function updateCards(stats) {
        setValue("#totalDokter", stats.dokter);
        setValue("#totalPasien", stats.pasien);
        setValue("#totalFarmasi", stats.farmasi);
        setValue("#totalObat", stats.obat);

        updateDistribution(stats);
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
        if (!$("#filterMingguChart").val()) {
            $("#filterMingguChart").val(getCurrentWeekInputValue());
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
        const params = {
            periode: periode,
        };

        if (periode === "harian") {
            params.tanggal = $("#filterTanggalChart").val();
        } else if (periode === "mingguan") {
            params.minggu = $("#filterMingguChart").val();
        } else if (periode === "bulanan") {
            params.bulan = $("#filterBulanChart").val();
        } else if (periode === "tahunan") {
            params.tahun = $("#filterTahunChart").val();
        }

        return params;
    }

    function renderChart(labels, values, datasetLabel = "Jumlah Kunjungan") {
        const canvas = document.getElementById("kunjunganChart");
        if (!canvas) return;

        const ctx = canvas.getContext("2d");

        if (kunjunganChartInstance) {
            kunjunganChartInstance.destroy();
        }

        kunjunganChartInstance = new Chart(ctx, {
            type: "bar",
            data: {
                labels: labels,
                datasets: [
                    {
                        label: datasetLabel,
                        data: values,
                        backgroundColor: "rgba(79, 70, 229, 0.75)",
                        borderColor: "rgba(79, 70, 229, 1)",
                        borderWidth: 1,
                        borderRadius: 10,
                        borderSkipped: false,
                        maxBarThickness: 30,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                    tooltip: {
                        backgroundColor: "#0f172a",
                        titleColor: "#ffffff",
                        bodyColor: "#ffffff",
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            label: function (context) {
                                return ` ${formatNumber(context.raw)} kunjungan`;
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
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: "#64748b",
                            precision: 0,
                        },
                        grid: {
                            color: "rgba(148, 163, 184, 0.15)",
                            drawBorder: false,
                        },
                    },
                },
            },
        });
    }

    function loadChart() {
        setStatus("Memuat grafik...", "loading");

        $.getJSON("/admin/chart_kunjungan", getChartParams())
            .done(function (response) {
                const labels = response.labels || [];
                const values = response.values || [];
                const totalKunjungan = values.reduce(
                    (sum, item) => sum + Number(item || 0),
                    0,
                );

                renderChart(
                    labels,
                    values,
                    response.dataset_label || "Jumlah Kunjungan",
                );

                $("#chartModeText").text(response.mode_label || "-");
                $("#chartActiveFilter").text(response.filter_label || "-");
                $("#chartSummary").text(
                    `${formatNumber(totalKunjungan)} kunjungan tercatat pada ${response.filter_label || "periode terpilih"}.`,
                );

                if ($("#chartYear").length) {
                    $("#chartYear").text(response.short_label || "-");
                }

                if ($("#chartYearText").length) {
                    $("#chartYearText").text(response.short_label || "-");
                }

                setStatus("Grafik berhasil dimuat", "success");
            })
            .fail(function () {
                $("#chartSummary").text("Data grafik gagal dimuat.");
                $("#chartModeText").text("-");
                $("#chartActiveFilter").text("-");
                setStatus("Gagal memuat grafik", "error");
                console.error("Gagal memuat data grafik.");
            });
    }

    function loadStats() {
        $.when(
            $.getJSON("/admin/total_dokter"),
            $.getJSON("/admin/total_pasien"),
            $.getJSON("/admin/total_farmasi"),
            $.getJSON("/admin/stok_obat"),
        )
            .done(function (dokterRes, pasienRes, farmasiRes, obatRes) {
                const stats = {
                    dokter: Number(dokterRes[0]?.total ?? 0),
                    pasien: Number(pasienRes[0]?.total ?? 0),
                    farmasi: Number(farmasiRes[0]?.total ?? 0),
                    obat: Number(obatRes[0]?.total ?? 0),
                };

                updateCards(stats);

                if ($("#distributionNote").length) {
                    $("#distributionNote").text(
                        "Perbandingan relatif antar statistik utama berhasil dimuat.",
                    );
                }
            })
            .fail(function () {
                updateCards({
                    dokter: 0,
                    pasien: 0,
                    farmasi: 0,
                    obat: 0,
                });

                if ($("#distributionNote").length) {
                    $("#distributionNote").text(
                        "Sebagian data gagal dimuat. Periksa endpoint dashboard.",
                    );
                }

                console.error("Gagal memuat salah satu data statistik.");
            });
    }

    $("#filterPeriodeChart").on("change", function () {
        toggleChartFilterInputs();
    });

    $("#btnApplyChartFilter").on("click", function () {
        loadChart();
    });

    initDefaultFilterValues();
    toggleChartFilterInputs();
    loadChart();
    loadStats();
});
