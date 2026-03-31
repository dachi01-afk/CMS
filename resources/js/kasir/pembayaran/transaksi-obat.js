import $ from "jquery";

const rupiah = (angka = 0) =>
    Number(angka || 0).toLocaleString("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    });

$(function () {
    const $table = $("#transaksiObatTable");
    const $paginate = $("#transaksi-obat-custom-paginate");
    const $info = $("#transaksi-obat-custom-info");
    const $perPage = $("#transaksi-obat-page-length");
    const $searchInput = $("#transaksi-obat-search-input");

    const table = $table.DataTable({
        processing: true,
        responsive: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/kasir/get-data-transaksi-obat",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            {
                data: "kode_transaksi",
                name: "kode_transaksi",
            },
            {
                data: "nama_pasien",
                name: "pasien.nama_pasien",
            },
            {
                data: "nama_obat",
                name: "nama_obat",
                orderable: false,
            },
            {
                data: "jumlah_item",
                name: "jumlah_item",
                render: function (data) {
                    return `<span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">${data} item</span>`;
                },
            },
            {
                data: "total_tagihan",
                name: "total_tagihan",
                render: function (data) {
                    return `<span class="font-semibold text-slate-800 dark:text-slate-100">${rupiah(data)}</span>`;
                },
            },
            {
                data: "metode_pembayaran",
                name: "metodePembayaran.nama_metode",
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
                    });
                },
            },
            {
                data: "status",
                name: "status",
                render: function (data) {
                    const badge =
                        data === "Sudah Bayar"
                            ? "bg-emerald-100 text-emerald-700"
                            : "bg-amber-100 text-amber-700";

                    return `<span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ${badge}">${data}</span>`;
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
                searchable: false,
                orderable: false,
                className: "text-center whitespace-nowrap",
            },
        ],
        dom: "t",
        rowCallback: function (row) {
            $(row).addClass(
                "bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/50",
            );
            $("td", row).addClass(
                "px-4 md:px-6 py-4 text-slate-700 dark:text-slate-100 align-top",
            );
        },
    });

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        $info.text(
            `Menampilkan ${info.recordsDisplay ? info.start + 1 : 0}–${info.end} dari ${info.recordsDisplay} data`,
        );

        $paginate.empty();

        const prevDisabled =
            currentPage === 1 ? "opacity-50 pointer-events-none" : "";

        $paginate.append(`
            <li>
                <a href="#" id="btnPrevTransaksiObat" class="flex h-9 items-center justify-center px-4 bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 ${prevDisabled}">
                    Prev
                </a>
            </li>
        `);

        const maxVisible = 5;
        let start = Math.max(currentPage - Math.floor(maxVisible / 2), 1);
        let end = Math.min(start + maxVisible - 1, totalPages);

        if (end - start < maxVisible - 1) {
            start = Math.max(end - maxVisible + 1, 1);
        }

        for (let i = start; i <= end; i++) {
            const active =
                i === currentPage
                    ? "bg-sky-600 text-white"
                    : "bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700";

            $paginate.append(`
                <li>
                    <a href="#" class="page-number-obat flex h-9 items-center justify-center px-4 border-r last:border-r-0 border-slate-200 dark:border-slate-600 ${active}" data-page="${i}">
                        ${i}
                    </a>
                </li>
            `);
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 pointer-events-none" : "";

        $paginate.append(`
            <li>
                <a href="#" id="btnNextTransaksiObat" class="flex h-9 items-center justify-center px-4 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700 ${nextDisabled}">
                    Next
                </a>
            </li>
        `);
    }

    $paginate.on("click", "a", function (e) {
        e.preventDefault();
        const $link = $(this);

        if ($link.attr("id") === "btnPrevTransaksiObat") {
            table.page("previous").draw("page");
        } else if ($link.attr("id") === "btnNextTransaksiObat") {
            table.page("next").draw("page");
        } else if ($link.hasClass("page-number-obat")) {
            table.page(parseInt($link.data("page")) - 1).draw("page");
        }
    });

    $perPage.on("change", function () {
        table.page.len(parseInt($(this).val())).draw();
    });

    $searchInput.on("keyup", function () {
        table.search(this.value).draw();
    });

    table.on("draw", updatePagination);
    updatePagination();
});

$(document).on("click", ".bayarSekarang", function () {
    const url = $(this).data("url");
    window.location.href = url;
});
