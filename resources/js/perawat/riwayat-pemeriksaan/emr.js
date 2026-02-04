import $ from "jquery";

$(function () {
    const table = $("#tabelRiwayatEmr").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/perawat/riwayat-pemeriksaan/emr/data",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "no_emr", name: "id" }, // ✅ jangan name no_emr (itu bukan kolom)
            { data: "nama_pasien", name: "pasien.nama_pasien" }, // ✅ kalau server support join/with
            { data: "dokter", name: "dokter.nama_dokter" }, // ✅
            { data: "tanggal", name: "created_at" }, // ✅ penting
            { data: "ringkasan", name: "keluhan_utama", orderable: false }, // ✅
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
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
        order: [[4, "desc"]],
        language: { emptyTable: "Tidak ada data.", processing: "Memuat..." },
    });

    $("#emr_searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    const $info = $("#emr_customInfo");
    const $pagination = $("#emr_customPagination");
    const $perPage = $("#emr_pageLength");

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${info.recordsDisplay} data (Halaman ${currentPage} dari ${totalPages})`,
        );

        $pagination.empty();

        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(
            `<li><a href="#" id="btnPrevEmr" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a></li>`,
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
                `<li><a href="#" class="page-number-emr flex items-center justify-center px-3 h-8 border ${active}" data-page="${i}">${i}</a></li>`,
            );
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(
            `<li><a href="#" id="btnNextEmr" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">Next</a></li>`,
        );
    }

    $pagination.on("click", "a", function (e) {
        e.preventDefault();
        const $link = $(this);
        if ($link.hasClass("opacity-50")) return;
        if ($link.attr("id") === "btnPrevEmr")
            table.page("previous").draw("page");
        else if ($link.attr("id") === "btnNextEmr")
            table.page("next").draw("page");
        else if ($link.hasClass("page-number-emr"))
            table.page(parseInt($link.data("page")) - 1).draw("page");
    });

    $perPage.on("change", function () {
        table.page.len(parseInt($(this).val())).draw();
    });

    table.on("draw", updatePagination);
    updatePagination();
});
