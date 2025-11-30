import $ from "jquery";
import "datatables.net-dt";
import axios from "axios";

$(function () {
    const tabel = $("#riwayatTransaksiLayanan").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/kasir/get-data-riwayat-transaksi-layanan",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "nama_pasien", name: "nama_pasien" },
            {
                data: "tanggal_kunjungan",
                name: "tanggal_kunjungan",
                render: function (data) {
                    if (!data || data === "-") return "-";
                    const date = new Date(data);
                    if (isNaN(date.getTime())) return "-";

                    const waktuIndonesia = date.toLocaleString("id-ID", {
                        timeZone: "Asia/Jakarta",
                        day: "2-digit",
                        month: "long",
                        year: "numeric",
                        hour: "2-digit",
                        minute: "2-digit",
                    });

                    return waktuIndonesia;
                },
            },
            { data: "no_antrian", name: "no_antrian" },
            { data: "nama_layanan", name: "nama_layanan" },
            { data: "nama_kategori", name: "nama_kategori" },
            { data: "jumlah_layanan", name: "jumlah_layanan" },
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
            { data: "metode_pembayaran", name: "metode_pembayaran" },
            { data: "status", name: "status" },
            { data: "bukti_pembayaran", name: "bukti_pembayaran" },
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
            $("td", row).addClass(
                "px-6 py-4 text-gray-900 dark:text-white align-top"
            );
        },
    });

    // 🔎 Search
    $("#riwayat-transaksi-search-input").on("keyup", function () {
        tabel.search(this.value).draw();
    });

    const $info = $("#riwayat-transaksi-custom-info");
    const $pagination = $("#riwayat-transaksi-custom-pagination");
    const $perPage = $("#riwayat-transaksi-page-length");

    function updatePagination() {
        const info = tabel.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${
                info.recordsDisplay
            } data (Halaman ${currentPage} dari ${totalPages})`
        );
        $pagination.empty();

        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(
            `<li><a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a></li>`
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
                `<li><a href="#" class="page-number flex items-center justify-center px-3 h-8 border ${active}" data-page="${i}">${i}</a></li>`
            );
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(
            `<li><a href="#" id="btnNext" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">Next</a></li>`
        );
    }

    $pagination.on("click", "a", function (e) {
        e.preventDefault();
        const $link = $(this);
        if ($link.hasClass("opacity-50")) return;

        if ($link.attr("id") === "btnPrev") {
            tabel.page("previous").draw("page");
        } else if ($link.attr("id") === "btnNext") {
            tabel.page("next").draw("page");
        } else if ($link.hasClass("page-number")) {
            tabel.page(parseInt($link.data("page")) - 1).draw("page");
        }
    });

    $perPage.on("change", function () {
        tabel.page.len(parseInt($(this).val(), 10)).draw();
    });

    tabel.on("draw", updatePagination);
    updatePagination();
});

// tombol cetak kuitansi
$(document).on("click", ".cetakKuitansi", function () {
    const url = $(this).data("url");
    if (!url) return;
    window.open(url, "_blank");
});
