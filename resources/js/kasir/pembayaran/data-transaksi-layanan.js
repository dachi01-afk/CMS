import axios from "axios";
import $ from "jquery";
import "datatables.net-dt";

$(function () {
    const $tableEl = $("#transaksiLayananTable");
    if (!$tableEl.length) return;

    // ⬇️ ambil URL dari Blade, fallback ke path lama kalau belum diset
    const dataUrl =
           window.transaksiLayananDataUrl || "/kasir/get-data-transaksi-layanan";


    const table = $tableEl.DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        ordering: true,
        paging: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        scrollX: true, // penting supaya header & body ikut scroll X
        ajax: {
            url: dataUrl,
            type: "GET",
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            {
                data: "nama_pasien",
                name: "nama_pasien",
            },
            {
                data: "nama_layanan",
                name: "nama_layanan",
            },
            {
                data: "kategori_layanan",
                name: "kategori_layanan",
            },
            {
                data: "jumlah",
                name: "jumlah",
                render: function (data) {
                    if (!data) return "-";
                    return data + " x";
                },
            },
            {
                data: "total_tagihan",
                name: "total_tagihan",
                render: function (data) {
                    if (!data) return "-";
                    const n = Number(data) || 0;
                    return n.toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                        minimumFractionDigits: 0,
                    });
                },
            },
            {
                data: "metode_pembayaran",
                name: "metode_pembayaran",
                render: function (data) {
                    return data || "-";
                },
            },
            {
                data: "kode_transaksi",
                name: "kode_transaksi",
                render: function (data) {
                    return data || "-";
                },
            },
            {
                data: "tanggal_transaksi",
                name: "tanggal_transaksi",
                render: function (data) {
                    if (!data) return "-";
                    const date = new Date(data);
                    return date.toLocaleString("id-ID", {
                        timeZone: "Asia/Jakarta",
                        day: "2-digit",
                        month: "long",
                        year: "numeric",
                        hour: "2-digit",
                        minute: "2-digit",
                    });
                },
            },
            {
                data: "status",
                name: "status",
                render: function (data) {
                    return data || "-";
                },
            },
            {
                data: "bukti_pembayaran",
                name: "bukti_pembayaran",
                orderable: false,
                searchable: false,
            },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
                className: "text-center whitespace-nowrap",
            },
        ],
        dom: "t",
        rowCallback: function (row) {
            $(row).addClass(
                "bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
            );
            $("td", row).addClass("px-6 py-4 text-gray-900 dark:text-white");
        },
    });

    /* ============== SEARCH ============== */
    $("#transaksi-layanan-search-input").on("keyup", function () {
        table.search(this.value).draw();
    });

    /* ============== PAGE LENGTH ============== */
    $("#transaksi-layanan-page-length").on("change", function () {
        const len = parseInt($(this).val(), 10) || 10;
        table.page.len(len).draw();
    });

    /* ============== CUSTOM INFO & PAGINATION ============== */
    const $info = $("#transaksi-layanan-custom-info");
    const $paginate = $("#transaksi-layanan-custom-paginate");

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        // Info text
        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${
                info.recordsDisplay
            } data (Halaman ${currentPage} dari ${totalPages})`
        );

        // Pagination numbers
        $paginate.empty();

        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";
        $paginate.append(
            `<li>
                <a href="#" data-role="prev"
                   class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-l-lg hover:bg-gray-100 ${prevDisabled}">
                    Previous
                </a>
            </li>`
        );

        const maxVisible = 5;
        let start = Math.max(currentPage - Math.floor(maxVisible / 2), 1);
        let end = Math.min(start + maxVisible - 1, totalPages);
        if (end - start < maxVisible - 1) {
            start = Math.max(end - maxVisible + 1, 1);
        }

        for (let i = start; i <= end; i++) {
            const active =
                i === currentPage
                    ? "text-blue-600 bg-blue-50 border-blue-300"
                    : "text-gray-500 bg-white border-gray-300 hover:bg-gray-100";

            $paginate.append(
                `<li>
                    <a href="#" data-page="${i}"
                       class="page-number flex items-center justify-center px-3 h-8 border ${active}">
                        ${i}
                    </a>
                </li>`
            );
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 cursor-not-allowed" : "";
        $paginate.append(
            `<li>
                <a href="#" data-role="next"
                   class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-r-lg hover:bg-gray-100 ${nextDisabled}">
                    Next
                </a>
            </li>`
        );
    }

    $paginate.on("click", "a", function (e) {
        e.preventDefault();
        const role = $(this).data("role");
        const page = $(this).data("page");

        if ($(this).hasClass("opacity-50")) return;

        if (role === "prev") {
            table.page("previous").draw("page");
        } else if (role === "next") {
            table.page("next").draw("page");
        } else if (page) {
            table.page(page - 1).draw("page");
        }
    });

    table.on("draw", updatePagination);
    updatePagination();

    /* ============== AKSI BAYAR (contoh) ============== */
    $("body").on("click", ".btn-bayar-layanan", function () {
        const url = $(this).data("url");
        if (url) {
            window.location.href = url;
        }
    });
});
