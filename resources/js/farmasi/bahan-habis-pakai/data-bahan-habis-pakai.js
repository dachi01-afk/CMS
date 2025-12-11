import axios from "axios";
import $ from "jquery";

$(function () {
    // ==========================
    // HELPER FORMAT RUPIAH
    // ==========================
    function formatRupiah(value) {
        if (value === null || value === undefined || value === "") return "-";
        const num = Number(value) || 0;
        return num.toLocaleString("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        });
    }

    // ==========================
    // INIT DATATABLES
    // ==========================
    const table = $("#tabelBahanHabisPakai").DataTable({
        processing: true,
        responsive: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/farmasi/obat/get-data-obat",

        // Buttons dimunculkan tapi dibungkus di div hidden
        dom: "t",
        buttons: [
            {
                extend: "excelHtml5",
                className: "btn-export-excel",
                titleAttr: "Export ke Excel",
                exportOptions: {
                    columns: ":visible:not(:last-child)",
                },
            },
            {
                extend: "csvHtml5",
                className: "btn-export-csv",
                titleAttr: "Export ke CSV",
                exportOptions: {
                    columns: ":visible:not(:last-child)",
                },
            },
            {
                extend: "pdfHtml5",
                className: "btn-export-pdf",
                titleAttr: "Export ke PDF",
                orientation: "landscape",
                pageSize: "A4",
                exportOptions: {
                    columns: ":visible:not(:last-child)",
                },
            },
            {
                extend: "print",
                className: "btn-export-print",
                titleAttr: "Print Tabel",
                exportOptions: {
                    columns: ":visible:not(:last-child)",
                },
            },
        ],

        // ===========================================
        // KOLOM: sesuaikan dengan getDataObat() PHP:
        // kode, nama_obat, farmasi, jenis, kategori,
        // stok, harga_umum, harga_beli, avg_hpp,
        // harga_otc, margin_profit, action
        // ===========================================
        columns: [
            // NO
            {
                data: "kode",
                name: "kode",
            },

            // NAMA OBAT (plus KODE & KATEGORI)
            {
                data: "nama_obat",
                name: "nama_obat",
            },

            // FARMASI (brand)
            {
                data: "farmasi",
                name: "farmasi",
                render: function (data, type, row) {
                    const val = data || row.brand_farmasi?.nama_brand || "-";
                    return `<span class="text-xs">${val}</span>`;
                },
            },

            // JENIS
            {
                data: "jenis",
                name: "jenis",
                render: function (data, type, row) {
                    const val = data || row.jenis_obat?.nama_jenis_obat || "-";
                    return `<span class="text-xs">${val}</span>`;
                },
            },

            // KATEGORI
            {
                data: "kategori",
                name: "kategori",
                render: function (data, type, row) {
                    const val =
                        data || row.kategori_obat?.nama_kategori_obat || "-";
                    return `<span class="text-xs">${val}</span>`;
                },
            },

            // STOK (global)
            {
                data: "stok",
                name: "stok",
                render: function (data, type, row) {
                    const stok = data ?? row.jumlah ?? 0;
                    const satuan =
                        row.satuan_obat?.nama_satuan_obat || "capsul";

                    let warna =
                        stok === 0
                            ? "bg-red-50 text-red-700 border-red-100"
                            : stok < 10
                            ? "bg-amber-50 text-amber-700 border-amber-100"
                            : "bg-emerald-50 text-emerald-700 border-emerald-100";

                    return `
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] border ${warna}">
                            <i class="fa-solid fa-boxes-stacked mr-1"></i>
                            ${stok} ${satuan}
                        </span>
                    `;
                },
            },

            // HARGA UMUM
            {
                data: "harga_umum",
                name: "harga_umum",
                render: function (data) {
                    return `<span class="font-semibold text-gray-800 dark:text-gray-100 text-xs">
                                ${formatRupiah(data)}
                            </span>`;
                },
            },

            // HARGA BELI
            {
                data: "harga_beli",
                name: "harga_beli",
                render: function (data) {
                    return `<span class="text-xs">
                                ${formatRupiah(data)}
                            </span>`;
                },
            },

            // AVG HPP
            {
                data: "avg_hpp",
                name: "avg_hpp",
                render: function (data) {
                    return `<span class="text-xs">
                                ${formatRupiah(data)}
                            </span>`;
                },
            },

            // HARGA OTC
            {
                data: "harga_otc",
                name: "harga_otc",
                render: function (data) {
                    return `<span class="text-xs">
                                ${formatRupiah(data)}
                            </span>`;
                },
            },

            // MARGIN PROFIT
            {
                data: "margin_profit",
                name: "margin_profit",
                render: function (data) {
                    return `<span class="font-semibold text-emerald-700 dark:text-emerald-300 text-xs">
                                ${formatRupiah(data)}
                            </span>`;
                },
            },

            // AKSI
            {
                data: "action",
                name: "action",
                searchable: false,
                orderable: false,
                className: "text-center whitespace-nowrap",
            },
        ],

        rowCallback: function (row, data) {
            $(row).addClass(
                "bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
            );
            $("td", row).addClass(
                "px-4 md:px-6 py-3 md:py-4 text-gray-900 dark:text-white align-middle"
            );
        },
    });

    // ==========================
    // GLOBAL SEARCH (input custom)
    // ==========================
    $("#data-obat-search-input").on("keyup", function () {
        table.search(this.value).draw();
    });

    // ==========================
    // CUSTOM PAGINATION & INFO
    // ==========================
    const $info = $("#data-obat-custom-info");
    const $paginate = $("#data-obat-custom-paginate");
    const $perPage = $("#data-obat-page-length");

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${
                info.recordsDisplay
            } data (Halaman ${currentPage} dari ${totalPages})`
        );
        $paginate.empty();

        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";
        $paginate.append(
            `<li><a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-[11px] text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a></li>`
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
            $paginate.append(
                `<li><a href="#" class="page-number flex items-center justify-center px-3 h-8 text-[11px] border ${active}" data-page="${i}">${i}</a></li>`
            );
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 cursor-not-allowed" : "";
        $paginate.append(
            `<li><a href="#" id="btnNext" class="flex items-center justify-center px-3 h-8 text-[11px] text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">Next</a></li>`
        );
    }

    $paginate.on("click", "a", function (e) {
        e.preventDefault();
        const $link = $(this);
        if ($link.hasClass("opacity-50")) return;

        if ($link.attr("id") === "btnPrev") {
            table.page("previous").draw("page");
        } else if ($link.attr("id") === "btnNext") {
            table.page("next").draw("page");
        } else if ($link.hasClass("page-number")) {
            table.page(parseInt($link.data("page")) - 1).draw("page");
        }
    });

    $perPage.on("change", function () {
        table.page.len(parseInt($(this).val())).draw();
    });

    table.on("draw", updatePagination);
    updatePagination();

    // ==========================
    // EXPORT BUTTONS (trigger Buttons DataTables)
    // pastikan di Blade:
    //  #btn-export-excel, #btn-export-csv, #btn-export-pdf, #btn-export-print
    // ==========================
    $("#btn-export-excel").on("click", function () {
        table.button(".buttons-excel").trigger();
    });

    $("#btn-export-csv").on("click", function () {
        table.button(".buttons-csv").trigger();
    });

    $("#btn-export-pdf").on("click", function () {
        table.button(".buttons-pdf").trigger();
    });

    $("#btn-export-print").on("click", function () {
        table.button(".buttons-print").trigger();
    });

    // ==========================
    // IMPORT (trigger input file)
    // ==========================
    $("#btn-import-obat").on("click", function () {
        $("#input-file-import-obat").trigger("click");
    });

    $("#input-file-import-obat").on("change", function () {
        if (!this.files.length) return;

        if (window.Swal) {
            Swal.fire({
                icon: "question",
                title: "Import Data Obat?",
                text: "Pastikan format file sudah sesuai template.",
                showCancelButton: true,
                confirmButtonText: "Ya, lanjutkan",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#form-import-obat").submit();
                } else {
                    $("#input-file-import-obat").val("");
                }
            });
        } else {
            if (confirm("Import data obat dari file ini?")) {
                $("#form-import-obat").submit();
            } else {
                $("#input-file-import-obat").val("");
            }
        }
    });
});
