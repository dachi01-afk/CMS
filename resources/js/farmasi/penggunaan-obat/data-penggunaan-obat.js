$(function () {
    const $table = $("#table-penggunaan-obat");

    // ==========================
    // CFG (URL dari data-atribut tabel)
    // ==========================
    const cfg = {
        urlData: $table.data("url-data") || null,
        urlExport: $table.data("url-export") || null,
        urlPrint: $table.data("url-print") || null,
    };

    console.log("CFG PENGGUNAAN OBAT:", cfg);

    const $startDate = $("#filter_start_date");
    const $endDate = $("#filter_end_date");
    const $namaObat = $("#filter_nama_obat");
    const $lastRefresh = $("#text-last-refresh");

    // ==========================
    // Helper: update label "Last refresh"
    // ==========================
    function setLastRefresh() {
        if (!$lastRefresh.length) return;

        const now = new Date();
        const formatter = new Intl.DateTimeFormat("id-ID", {
            weekday: "short",
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });

        $lastRefresh.text(formatter.format(now));
    }

    // Default tanggal: awal bulan s/d hari ini
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

    function formatDateInput(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, "0");
        const d = String(date.getDate()).padStart(2, "0");
        return `${y}-${m}-${d}`;
    }

    if ($startDate.length && !$startDate.val()) $startDate.val(formatDateInput(firstDay));
    if ($endDate.length && !$endDate.val()) $endDate.val(formatDateInput(today));

    // ==========================
    // Helper format rupiah
    // ==========================
    function formatRupiah(angka) {
        const num = Number(angka) || 0;
        return num.toLocaleString("id-ID", {
            style: "currency",
            currency: "IDR",
            maximumFractionDigits: 0,
        });
    }

    // ==========================
    // Helper: build URL + query params dengan aman
    // - buang param kosong
    // - minimal 2 huruf untuk nama_obat (biar konsisten dengan UI)
    // - anti "??" / query dobel
    // ==========================
    function buildUrlWithParams(baseUrl, paramsObj) {
        if (!baseUrl) return null;

        const urlObj = new URL(baseUrl, window.location.origin);

        Object.entries(paramsObj || {}).forEach(([key, val]) => {
            if (val === undefined || val === null) return;

            const v = String(val).trim();
            if (!v) return;

            if (key === "nama_obat" && v.length < 2) return;

            urlObj.searchParams.set(key, v);
        });

        return urlObj.toString();
    }

    // ==========================
    // DataTables init
    // ==========================
    const dtPenggunaanObat = $table.DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        lengthChange: true,
        pageLength: 10,
        searchDelay: 250,
        order: [[1, "asc"]],

        ajax: {
            url: cfg.urlData,
            type: "GET",
            data: function (d) {
                d.start_date = $startDate.length ? ($startDate.val() || "") : "";
                d.end_date = $endDate.length ? ($endDate.val() || "") : "";
                d.nama_obat = $namaObat.length ? ($namaObat.val() || "") : "";
            },
        },

        columns: [
            {
                data: null,
                name: "rownum",
                orderable: false,
                searchable: false,
                className: "px-3 py-2 align-top text-[11px] text-slate-500",
                render: function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
            },
            {
                data: "nama_obat",
                name: "nama_obat",
                className:
                    "px-3 py-2 align-top font-semibold text-[12px] text-slate-800 dark:text-slate-50",
                render: function (data, type, row) {
                    if (!data) return "-";
                    let badge = "";
                    if (row.depot_nama) {
                        badge = `<span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px]
                                    bg-sky-50 text-sky-700 border border-sky-100
                                    dark:bg-sky-900/40 dark:text-sky-200 dark:border-sky-800">
                            ${row.depot_nama}
                         </span>`;
                    }
                    return `<div class="flex flex-col">
                            <span>${data}</span>
                            ${
                                row.kandungan_obat
                                    ? `<span class="text-[10px] text-slate-500 dark:text-slate-300">${row.kandungan_obat}</span>`
                                    : ""
                            }
                            ${badge}
                        </div>`;
                },
            },
            {
                data: "penggunaan_umum",
                name: "penggunaan_umum",
                searchable: false,
                className: "px-3 py-2 align-middle text-center text-[11px]",
                render: function (data, type, row) {
                    return (data ?? 0) + " " + (row.satuan || "Unit");
                },
            },
            {
                data: "nominal_umum",
                name: "nominal_umum",
                searchable: false,
                className: "px-3 py-2 align-middle text-center text-[11px]",
                render: function (data) {
                    const val = data ?? 0;
                    return formatRupiah(val);
                },
            },
            {
                data: "penggunaan_bpjs",
                name: "penggunaan_bpjs",
                searchable: false,
                className: "px-3 py-2 align-middle text-center text-[11px]",
                render: function (data, type, row) {
                    return (data ?? 0) + " " + (row.satuan || "Unit");
                },
            },
            {
                data: "nominal_bpjs",
                name: "nominal_bpjs",
                searchable: false,
                className: "px-3 py-2 align-middle text-center text-[11px]",
                render: function (data) {
                    const val = data ?? 0;
                    return formatRupiah(val);
                },
            },
            {
                data: "sisa_obat",
                name: "sisa_obat",
                searchable: false,
                className: "px-3 py-2 align-middle text-center text-[11px]",
                render: function (data, type, row) {
                    const sisa = data ?? 0;
                    const satuan = row.satuan || "Unit";

                    const level =
                        sisa === 0
                            ? "danger"
                            : sisa <= (row.minimal_stok || 0)
                            ? "warning"
                            : "normal";

                    let kelas =
                        "inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] border ";
                    if (level === "danger") {
                        kelas +=
                            "bg-rose-50 text-rose-600 border-rose-200 dark:bg-rose-900/40 dark:text-rose-200 dark:border-rose-800";
                    } else if (level === "warning") {
                        kelas +=
                            "bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/40 dark:text-amber-200 dark:border-amber-800";
                    } else {
                        kelas +=
                            "bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-200 dark:border-emerald-800";
                    }

                    return `<span class="${kelas}">${sisa} ${satuan}</span>`;
                },
            },
        ],

        dom: "t",

        rowCallback: function (row, data) {
            $(row).addClass(
                "bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
            );
            $("td", row).addClass("px-6 py-4 text-gray-900 dark:text-white");
        },
    });

    // ==========================
    // Custom Pagination + Info
    // ==========================
    const $info = $("#poli-customInfo");
    const $pagination = $("#poli-customPagination");
    const $perPage = $("#poli-pageLength");

    function updatePagination() {
        const info = dtPenggunaanObat.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages;

        if ($info.length) {
            $info.text(
                `Menampilkan ${info.start + 1}–${info.end} dari ${info.recordsDisplay} data (Halaman ${currentPage} dari ${totalPages})`
            );
        }

        if (!$pagination.length) return;
        $pagination.empty();

        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed pointer-events-none" : "";
        $pagination.append(
            `<li><a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a></li>`
        );

        const maxVisible = 5;
        let start = Math.max(currentPage - Math.floor(maxVisible / 2), 1);
        let end = Math.min(start + maxVisible - 1, totalPages);
        if (end - start < maxVisible - 1) start = Math.max(end - maxVisible + 1, 1);

        for (let i = start; i <= end; i++) {
            const active =
                i === currentPage
                    ? "text-blue-600 bg-blue-50 border-blue-300 hover:bg-blue-100"
                    : "text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700";
            $pagination.append(
                `<li><a href="#" class="page-number flex items-center justify-center px-3 h-8 border ${active}" data-page="${i}">${i}</a></li>`
            );
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 cursor-not-allowed pointer-events-none" : "";
        $pagination.append(
            `<li><a href="#" id="btnNext" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">Next</a></li>`
        );
    }

    $pagination.on("click", "a", function (e) {
        e.preventDefault();
        const $link = $(this);

        // kalau disable, abaikan
        if ($link.hasClass("pointer-events-none") || $link.hasClass("opacity-50")) return;

        if ($link.attr("id") === "btnPrev") dtPenggunaanObat.page("previous").draw("page");
        else if ($link.attr("id") === "btnNext") dtPenggunaanObat.page("next").draw("page");
        else if ($link.hasClass("page-number"))
            dtPenggunaanObat.page(parseInt($link.data("page")) - 1).draw("page");
    });

    $perPage.on("change", function () {
        dtPenggunaanObat.page.len(parseInt($(this).val())).draw();
    });

    dtPenggunaanObat.on("draw", function () {
        updatePagination();
        setLastRefresh();
    });

    updatePagination();
    setLastRefresh();

    // ==========================
    // Events
    // ==========================
    $("#btn-filter-penggunaan-obat").on("click", function () {
        dtPenggunaanObat.ajax.reload();
    });

    $("#btn-reset-penggunaan-obat").on("click", function () {
        if ($startDate.length) $startDate.val(formatDateInput(firstDay));
        if ($endDate.length) $endDate.val(formatDateInput(today));
        if ($namaObat.length) $namaObat.val("");
        dtPenggunaanObat.ajax.reload();
    });

    // Search kalau tekan Enter
    $namaObat.on("keypress", function (e) {
        if (e.which === 13) {
            dtPenggunaanObat.ajax.reload();
        }
    });

    // ==========================
    // Export & Print (SAFE)
    // ==========================
    $("#btn-export-penggunaan-obat").on("click", function (e) {
        e.preventDefault();
        if (!cfg.urlExport) {
            console.warn("URL Export kosong. Pastikan data-url-export terisi.");
            return;
        }

        const url = buildUrlWithParams(cfg.urlExport, {
            start_date: $startDate.length ? $startDate.val() : "",
            end_date: $endDate.length ? $endDate.val() : "",
            nama_obat: $namaObat.length ? $namaObat.val() : "",
        });

        if (!url) return;
        window.open(url, "_blank");
    });

    $("#btn-print-penggunaan-obat").on("click", function (e) {
        e.preventDefault();
        if (!cfg.urlPrint) {
            console.warn("URL Print kosong. Pastikan data-url-print terisi.");
            return;
        }

        const url = buildUrlWithParams(cfg.urlPrint, {
            start_date: $startDate.length ? $startDate.val() : "",
            end_date: $endDate.length ? $endDate.val() : "",
            nama_obat: $namaObat.length ? $namaObat.val() : "",
        });

        if (!url) return;
        window.open(url, "_blank");
    });

    // =========================
    // Search cepat - Penggunaan Obat (debounce + abort)
    // =========================
    const $filterNamaObat = $("#filter_nama_obat");

    let penggunaanTimer = null;
    let penggunaanLast = "";
    let penggunaanXhr = null;

    if ($filterNamaObat.length && dtPenggunaanObat) {
        // ambil jqXHR DataTables sebelum request dikirim
        dtPenggunaanObat.on("preXhr.dt", function (e, settings, data) {
            if (settings.jqXHR) penggunaanXhr = settings.jqXHR;
        });

        const runPenggunaanSearch = (value) => {
            if (value === penggunaanLast) return;
            penggunaanLast = value;

            if (value.length < 2) {
                dtPenggunaanObat.search("").draw();
                return;
            }

            dtPenggunaanObat.search(value).draw();
        };

        $filterNamaObat.on("input", function () {
            const value = $(this).val().trim();

            // abort request sebelumnya biar ga numpuk / balapan response
            if (penggunaanXhr && penggunaanXhr.readyState !== 4) {
                try {
                    penggunaanXhr.abort();
                } catch (e) {}
            }

            const delay = value.length <= 2 ? 300 : value.length <= 5 ? 180 : 120;

            clearTimeout(penggunaanTimer);
            penggunaanTimer = setTimeout(() => runPenggunaanSearch(value), delay);
        });

        // Enter = langsung cari
        $filterNamaObat.on("keydown", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                clearTimeout(penggunaanTimer);
                runPenggunaanSearch($(this).val().trim());
            }
        });
    }
});
