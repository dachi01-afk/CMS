import $ from "jquery";

$(function () {
    const tableOrderLab = $("#tabel-order-lab").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/perawat/order-lab/get-data-order-lab",
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
                data: "item_pemeriksaan",
                name: "item_pemeriksaan",
            },
            {
                data: "nama_dokter",
                name: "nama_dokter",
            },
            {
                data: "status_badge",
                name: "status_badge",
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
                "bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors",
            );
            $("td", row).addClass(
                "px-5 py-3 align-middle text-slate-700 dark:text-slate-50 text-sm",
            );
        },
        order: [[1, "asc"]], // urut berdasar No Antrian
        language: {
            emptyTable: "Tidak ada data.",
            processing: "Memuat...",
        },
    });

    // 🔎 Search
    $("#triage_searchInput").on("keyup", function () {
        tableOrderLab.search(this.value).draw();
    });

    // 🔢 Custom pagination & info
    const $info = $("#triage_customInfo");
    const $pagination = $("#triage_customPagination");
    const $perPage = $("#triage_pageLength");

    function updatePagination() {
        const info = tableOrderLab.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

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
        if ($link.attr("id") === "btnPrev")
            tableOrderLab.page("previous").draw("page");
        else if ($link.attr("id") === "btnNext")
            tableOrderLab.page("next").draw("page");
        else if ($link.hasClass("page-number"))
            tableOrderLab.page(parseInt($link.data("page")) - 1).draw("page");
    });

    $perPage.on("change", function () {
        tableOrderLab.page.len(parseInt($(this).val())).draw();
    });

    tableOrderLab.on("draw", updatePagination);
    updatePagination();
});
