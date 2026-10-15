import $ from "jquery";

$(function () {
    const $table = $("#table-audit-log");

    if (!$table.length) {
        return;
    }

    function currentFilters() {
        return {
            from: $("#filter-from").val() || "",
            to: $("#filter-to").val() || "",
            user_id: $("#filter-user").val() || "",
            role: $("#filter-role").val() || "",
            module: $("#filter-module").val() || "",
            action: $("#filter-action").val() || "",
            keyword: $("#filter-keyword").val() || "",
        };
    }

    const table = $table.DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: false,
        ordering: true,
        order: [
            [1, "desc"]
        ],
        pageLength: 10,
        lengthChange: false,
        info: false,
        dom: "t",
        ajax: {
            url: "/auditor/logs/data",
            data: function (data) {
                Object.assign(data, currentFilters());
            },
        },
        columns: [{
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            {
                data: "created_at",
                name: "created_at"
            },
            {
                data: "user_name",
                name: "user_name"
            },
            {
                data: "user_role",
                name: "user_role"
            },
            {
                data: "action_label",
                name: "action",
                orderable: false
            },
            {
                data: "module",
                name: "module"
            },
            {
                data: "description",
                name: "description"
            },
            {
                data: "ip_address",
                name: "ip_address"
            },
            {
                data: "aksi",
                name: "aksi",
                orderable: false,
                searchable: false,
                className: "text-center whitespace-nowrap",
            },
        ],
        rowCallback: function (row) {
            $(row).addClass(
                "bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600",
            );
            $("td", row).addClass("px-6 py-4 text-gray-900 dark:text-white");
        },
    });

    // ---- Filter ----
    $("#filter-form").on("submit", function (event) {
        event.preventDefault();
        table.ajax.reload();
    });

    $("#filter-reset").on("click", function () {
        $("#filter-form")[0].reset();
        table.ajax.reload();
    });

    // ---- Pagination kustom ----
    const $info = $("#audit-log-custom-info");
    const $pagination = $("#audit-log-custom-pagination");
    const $perPage = $("#audit-log-page-length");

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        const startData = info.recordsDisplay === 0 ? 0 : info.start + 1;
        const endData = info.recordsDisplay === 0 ? 0 : info.end;

        $info.text(
            `Menampilkan ${startData}–${endData} dari ${info.recordsDisplay} data (Halaman ${currentPage} dari ${totalPages})`,
        );

        $pagination.empty();

        const prevDisabled = currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(
            `<li><a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a></li>`,
        );

        const maxVisible = 5;
        let start = Math.max(currentPage - Math.floor(maxVisible / 2), 1);
        let end = Math.min(start + maxVisible - 1, totalPages);

        if (end - start < maxVisible - 1) {
            start = Math.max(end - maxVisible + 1, 1);
        }

        for (let i = start; i <= end; i++) {
            const active =
                i === currentPage ?
                "text-sky-600 bg-sky-50 border-sky-300 hover:bg-sky-100" :
                "text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700";

            $pagination.append(
                `<li><a href="#" class="page-number flex items-center justify-center px-3 h-8 border ${active}" data-page="${i}">${i}</a></li>`,
            );
        }

        const nextDisabled = currentPage === totalPages ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(
            `<li><a href="#" id="btnNext" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">Next</a></li>`,
        );
    }

    $pagination.on("click", "a", function (event) {
        event.preventDefault();

        const $link = $(this);

        if ($link.hasClass("opacity-50")) {
            return;
        }

        if ($link.attr("id") === "btnPrev") {
            table.page("previous").draw("page");
        } else if ($link.attr("id") === "btnNext") {
            table.page("next").draw("page");
        } else if ($link.hasClass("page-number")) {
            table.page(parseInt($link.data("page"), 10) - 1).draw("page");
        }
    });

    $perPage.on("change", function () {
        table.page.len(parseInt($(this).val(), 10)).draw();
    });

    table.on("draw", updatePagination);
    updatePagination();

    // ---- Export mengikuti filter yang aktif ----
    function exportUrl(format) {
        const params = new URLSearchParams(currentFilters());
        params.set("format", format);

        return `/auditor/logs/export?${params.toString()}`;
    }

    $("#export-xlsx").on("click", function () {
        window.location.href = exportUrl("xlsx");
    });

    $("#export-csv").on("click", function () {
        window.location.href = exportUrl("csv");
    });
});
