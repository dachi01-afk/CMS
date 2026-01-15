import $ from "jquery";
import axios from "axios";
import { Modal } from "flowbite";

$(function () {
    const table = $("#table-depot").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        searching: true,
        ajax: {
            url: "/farmasi/depot/get-data-depot", // sesuaikan route kamu
            type: "GET",
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "nama_depot", name: "depot.nama_depot" }, // ini HTML card depot + badge
            { data: "nama_tipe_depot", name: "tipe_depot.nama_tipe_depot" }, // kolom tipe
            {
                data: "jumlah_stok_depot",
                name: "depot.jumlah_stok_depot",
                searchable: false,
            },
            {
                data: "aksi",
                name: "aksi",
                searchable: false,
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

    // custom search (seperti input assist.id)
    $("#customSearch").on("keyup", function () {
        table.search(this.value).draw();
    });

    // Page length
    $("#custom_pageLength").on("change", function () {
        table.page.len(parseInt($(this).val(), 10)).draw();
    });

    // Pagination custom
    const $info = $("#custom_customInfo");
    const $pagination = $("#custom_Pagination");

    function updatePagination() {
        const info = table.page.info();
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
        $pagination.append(`
                <li>
                    <a href="#" data-nav="prev"
                       class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">
                       Previous
                    </a>
                </li>
            `);

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
            $pagination.append(`
                    <li>
                        <a href="#" data-page="${i}"
                           class="page-number flex items-center justify-center px-3 h-8 border ${active}">
                           ${i}
                        </a>
                    </li>
                `);
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(`
                <li>
                    <a href="#" data-nav="next"
                       class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">
                       Next
                    </a>
                </li>
            `);
    }

    $pagination.on("click", "a", function (e) {
        e.preventDefault();
        const $link = $(this);
        if ($link.hasClass("opacity-50")) return;

        const nav = $link.data("nav");
        const page = $link.data("page");

        if (nav === "prev") table.page("previous").draw("page");
        else if (nav === "next") table.page("next").draw("page");
        else if (page) table.page(parseInt(page, 10) - 1).draw("page");
    });

    table.on("draw", updatePagination);
    updatePagination();
});
