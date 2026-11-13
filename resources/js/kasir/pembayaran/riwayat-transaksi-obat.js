import $ from "jquery";

$(function () {
    const formatRupiah = (data) => {
        if (!data) return "-";
        return (Number(data) || 0).toLocaleString("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        });
    };

    const table = $("#riwayatTransaksiObatTable").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/kasir/riwayat-transaksi-obat/get-data",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "kode_transaksi", name: "kode_transaksi" },
            { data: "nama_pasien", name: "nama_pasien" },
            { data: "tanggal_pembayaran", name: "tanggal_pembayaran" },
            {
                data: "total_tagihan",
                name: "total_tagihan",
                render: formatRupiah,
            },
            {
                data: "diskon_nilai",
                name: "diskon_nilai",
                render: formatRupiah,
            },
            {
                data: "total_setelah_diskon",
                name: "total_setelah_diskon",
                render: formatRupiah,
            },
            { data: "metode_pembayaran", name: "metode_pembayaran" },
            { data: "status", name: "status" },
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
                "bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600",
            );
            $("td", row).addClass("px-6 py-4 text-gray-900 dark:text-white");
        },
    });

    // 🔎 Search (ID sesuai Blade tab Obat)
    $("#transaksi-obat-search-input").on("keyup", function () {
        table.search(this.value).draw();
    });

    const $info = $("#transaksi-obat-custom-info");
    const $pagination = $("#transaksi-obat-custom-paginate");
    const $perPage = $("#transaksi-obat-page-length");

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = Math.max(info.pages, 1);
        const from = info.recordsDisplay === 0 ? 0 : info.start + 1;

        $info.text(
            `Menampilkan ${from}–${info.end} dari ${info.recordsDisplay} data (Halaman ${currentPage} dari ${totalPages})`,
        );
        $pagination.empty();

        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(
            `<li><a href="#" data-nav="prev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a></li>`,
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
            `<li><a href="#" data-nav="next" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">Next</a></li>`,
        );
    }

    $pagination.on("click", "a", function (e) {
        e.preventDefault();
        const $link = $(this);
        if ($link.hasClass("opacity-50")) return;

        if ($link.data("nav") === "prev") table.page("previous").draw("page");
        else if ($link.data("nav") === "next") table.page("next").draw("page");
        else if ($link.hasClass("page-number"))
            table.page(parseInt($link.data("page"), 10) - 1).draw("page");
    });

    $perPage.on("change", function () {
        table.page.len(parseInt($(this).val(), 10)).draw();
    });

    table.on("draw", updatePagination);
    updatePagination();
});

$(document).on("click", ".bayarSekarang", function () {
    window.location.href = $(this).data("url");
});
