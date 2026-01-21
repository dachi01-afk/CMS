import $ from "jquery";

$(function () {
    const urlGetDataPenggunaanBhp =
        "/farmasi/penggunaan-bhp/get-data-penggunaan-bhp";

    const $startDate = $("#filter_start_date");
    const $endDate = $("#filter_end_date");
    const $namaBarang = $("#filter_nama_barang");
    const $lastRefresh = $("#text-last-refresh");

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

    const tabelPenggunaanBhp = $("#table-penggunaan-barang").DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        ajax: {
            url: urlGetDataPenggunaanBhp,
            data: function (d) {
                d.filter_start_date = $("#filter_start_date").val();
                d.filter_end_date = $("#filter_end_date").val();
                d.filter_nama_barang = $("#filter_nama_barang").val();
            },
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
                className: "text-center align-middle",
            },
            {
                data: "nama_barang",
                name: "nama_barang",
                className: "text-left align-middle",
                searchable: true, // Pastikan ini true karena ini yang dicari
            },
            {
                data: "penggunaan_umum",
                name: "penggunaan_umum",
                className: "text-center align-middle",
                searchable: false,
                render: function (data, type, row) {
                    // Jika datanya null/kosong, ubah jadi 0
                    let nilai = data ? data : 0;

                    // Kita bungkus dengan div yang memaksa ke tengah
                    return `<div class="flex items-center justify-center w-full h-full">
                                <span>${nilai}</span>
                            </div>`;
                },
            },
            {
                data: "nominal_umum",
                name: "nominal_umum",
                orderable: false,
                searchable: false,
                className: "text-right font-semibold align-middle",
                render: function (data, type, row) {
                    // Jika datanya null/kosong, ubah jadi 0
                    let nilai = data ? data : 0;

                    // Kita bungkus dengan div yang memaksa ke tengah
                    return `<div class="flex items-center justify-center w-full h-full">
                                <span>${nilai}</span>
                            </div>`;
                },
            },
            {
                data: "sisa_stok",
                name: "stok_barang",
                className: "text-center align-middle",
                searchable: false,
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
                        "inline-flex items-center justify-center text-center px-2 py-0.5 rounded-full text-[10px] border ";
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
        language: {
            emptyTable: "Belum ada data penggunaan BHP dalam database",

            zeroRecords: `
            <div class="flex flex-col items-center justify-center py-8">
                <div class="bg-slate-100 dark:bg-slate-800 rounded-full p-4 mb-3">
                    <i class="fa-solid fa-magnifying-glass text-slate-400 text-2xl"></i>
                </div>
                <p class="text-slate-500 dark:text-slate-400 font-medium">Maaf, barang yang Anda cari tidak ditemukan</p>
                <p class="text-slate-400 dark:text-slate-500 text-[11px]">Coba periksa kembali ejaan atau gunakan kata kunci lain.</p>
            </div>`,
        },
        dom: "t",
        rowCallback: function (row, data) {
            $(row).addClass(
                "bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600",
            );
            $("td", row).addClass("px-6 py-4 text-gray-900 dark:text-white");
        },
    });

    const $info = $("#penggunaan-bhp-customInfo");
    const $pagination = $("#penggunaan-bhp-customPagination");
    const $perPage = $("#penggunaan-bhp-pageLength");

    function updatePagination() {
        const info = tabelPenggunaanBhp.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages;

        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${
                info.recordsDisplay
            } data (Halaman ${currentPage} dari ${totalPages})`,
        );
        $pagination.empty();

        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(
            `<li><a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a></li>`,
        );

        const maxVisible = 5;
        let start = Math.max(currentPage - Math.floor(maxVisible / 2), 1);
        let end = Math.min(start + maxVisible - 1, totalPages);
        if (end - start < maxVisible - 1)
            start = Math.max(end - maxVisible + 1, 1);

        for (let i = start; i <= end; i++) {
            const active =
                i === currentPage
                    ? "text-blue-600 bg-blue-50 border-blue-300 hover:bg-blue-100"
                    : "text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700";
            $pagination.append(
                `<li><a href="#" class="page-number flex items-center justify-center px-3 h-8 border ${active}" data-page="${i}">${i}</a></li>`,
            );
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(
            `<li><a href="#" id="btnNext" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">Next</a></li>`,
        );
    }

    $pagination.on("click", "a", function (e) {
        e.preventDefault();
        const $link = $(this);
        if ($link.hasClass("opacity-50")) return;
        if ($link.attr("id") === "btnPrev") table.page("previous").draw("page");
        else if ($link.attr("id") === "btnNext")
            table.page("next").draw("page");
        else if ($link.hasClass("page-number"))
            table.page(parseInt($link.data("page")) - 1).draw("page");
    });

    $perPage.on("change", function () {
        tabelPenggunaanBhp.page.len(parseInt($(this).val())).draw();
    });

    tabelPenggunaanBhp.on("draw", updatePagination);
    setLastRefresh();
    updatePagination();

    $("#btn-filter-penggunaan-barang").on("click", function () {
        tabelPenggunaanBhp.ajax.reload();
    });

    // Default tanggal: awal bulan s/d hari ini
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

    function formatDateInput(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, "0");
        const d = String(date.getDate()).padStart(2, "0");
        return `${y}-${m}-${d}`;
    }

    if ($startDate.length && !$startDate.val())
        $startDate.val(formatDateInput(firstDay));
    if ($endDate.length && !$endDate.val())
        $endDate.val(formatDateInput(today));

    $("#btn-reset-penggunaan-barang").on("click", function () {
        if ($startDate.length) $startDate.val(formatDateInput(firstDay));
        if ($endDate.length) $endDate.val(formatDateInput(today));
        if ($namaBarang.length) $namaBarang.val("");
        tabelPenggunaanBhp.ajax.reload();
    });

    // Search kalau tekan Enter
    $namaBarang.on("keypress", function (e) {
        if (e.which === 13) {
            tabelPenggunaanBhp.ajax.reload();
        }
    });

    // Fitur Search Cepat
    let penggunaanTimer = null;
    let penggunaanLast = "";
    let penggunaanXhr = null;

    if ($namaBarang.length && tabelPenggunaanBhp) {
        tabelPenggunaanBhp.on("preXhr.dt", function (e, settings, data) {
            if (settings.jqXHR) penggunaanXhr = settings.jqXHR;
        });

        const runPenggunaanSearch = (value) => {
            if (value === penggunaanLast) return;
            penggunaanLast = value;

            if (value.length < 2) {
                tabelPenggunaanBhp.search("").draw();
                return;
            }

            $("#filter_nama_barang").val(value);

            // Panggil draw() untuk mereload tabel
            tabelPenggunaanBhp.draw();
        };

        $namaBarang.on("input", function () {
            const value = $(this).val().trim();

            if (penggunaanXhr && penggunaanXhr.readhState !== 4) {
                try {
                    penggunaanXhr.abort();
                } catch (e) {}
            }

            const delay =
                value.length <= 2 ? 300 : value.length <= 5 ? 180 : 120;

            clearTimeout(penggunaanTimer);
            penggunaanTimer = setTimeout(
                () => runPenggunaanSearch(value),
                delay,
            );
        });

        $namaBarang.on("keydown", function (e) {
            if (e.kay === "Enter") {
                e.preventDefault();
                clearTimeout(penggunaanTimer);
                runPenggunaanSearch($(this).val().trim());
            }
        });
    }
});
