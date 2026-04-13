import $ from "jquery";
import "datatables.net";

window.$ = $;
window.jQuery = $;

$(function () {
    const table = $("#table-restock-return").DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        paging: true,
        lengthChange: false,
        info: true,
        ordering: true,
        pageLength: parseInt($("#restock_pageLength").val() || 10),
        autoWidth: false,
        responsive: false,
        dom: "t",
        ajax: {
            url: "/farmasi/restock-dan-return-obat/get-data",
            type: "GET",
            data: function (d) {
                // kalau nanti search custom diaktifkan
                d.custom_search = $("#customSearch").val();
            },
        },
        columns: [
            { data: "kode", name: "kode", orderable: true, searchable: false },
            {
                data: "no_faktur",
                name: "no_faktur",
                orderable: true,
                searchable: true,
            },
            {
                data: "jenis",
                name: "jenis",
                orderable: false,
                searchable: false,
            },
            {
                data: "tanggal_kirim",
                name: "tanggal_kirim",
                orderable: true,
                searchable: false,
            },
            {
                data: "tanggal_buat",
                name: "tanggal_buat",
                orderable: true,
                searchable: false,
            },
            {
                data: "supplier",
                name: "supplier",
                orderable: true,
                searchable: true,
            },
            {
                data: "nama_item",
                name: "nama_item",
                orderable: false,
                searchable: true,
            },
            {
                data: "jumlah",
                name: "jumlah",
                orderable: true,
                searchable: false,
            },
            {
                data: "diapprove",
                name: "diapprove",
                orderable: false,
                searchable: false,
            },
            {
                data: "total_harga",
                name: "total_harga",
                orderable: true,
                searchable: false,
            },
            {
                data: "tempo",
                name: "tempo",
                orderable: true,
                searchable: false,
            },
            { data: "aksi", name: "aksi", orderable: false, searchable: false },
        ],
        columnDefs: [
            {
                targets: [2, 8, 11],
                className: "text-center",
            },
            {
                targets: [9, 11],
                className: "text-right",
            },
            {
                targets: [2, 8, 11],
                render: function (data, type) {
                    return type === "display" ? data : $(data).text();
                },
            },
        ],
        language: {
            processing: "Memuat data...",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada data restock obat",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            paginate: {
                first: "Awal",
                last: "Akhir",
                next: "›",
                previous: "‹",
            },
        },
        drawCallback: function () {
            updateCustomInfo(this.api());
            renderCustomPagination(this.api());
        },
        initComplete: function () {
            updateCustomInfo(this.api());
            renderCustomPagination(this.api());
        },
    });

    $("#restock_pageLength").on("change", function () {
        const length = parseInt($(this).val());
        table.page.len(length).draw();
    });

    $("#customSearch").on("keyup", function () {
        table.search(this.value).draw();
    });

    $(document).on("click", ".btn-detail", function () {
        const id = $(this).data("id");
        console.log("Detail ID:", id);
        // nanti sambungkan ke modal / route detail
    });

    $(document).on("click", ".btn-edit", function () {
        const id = $(this).data("id");
        console.log("Edit ID:", id);
        // nanti sambungkan ke modal / route edit
    });

    $("#btn-open-modal-create").on("click", function () {
        console.log("Open modal create restock obat");
        // nanti sambungkan ke modal create
    });

    function updateCustomInfo(api) {
        const info = api.page.info();

        if (info.recordsTotal === 0) {
            $("#custom_customInfo").html("Tidak ada data untuk ditampilkan");
            return;
        }

        $("#custom_customInfo").html(
            `Menampilkan <span class="font-semibold">${info.start + 1}</span> - <span class="font-semibold">${info.end}</span> dari <span class="font-semibold">${info.recordsDisplay}</span> data`,
        );
    }

    function renderCustomPagination(api) {
        const info = api.page.info();
        const currentPage = info.page;
        const totalPages = info.pages;

        let html = "";

        html += `
            <li>
                <button class="custom-page-btn px-3 py-2 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700 ${currentPage === 0 ? "opacity-50 cursor-not-allowed" : ""}"
                    data-page="prev" ${currentPage === 0 ? "disabled" : ""}>
                    ‹
                </button>
            </li>
        `;

        for (let i = 0; i < totalPages; i++) {
            const activeClass =
                i === currentPage
                    ? "bg-sky-600 text-white"
                    : "bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700";

            html += `
                <li>
                    <button class="custom-page-btn px-3 py-2 border-r border-slate-200 dark:border-slate-600 ${activeClass}"
                        data-page="${i}">
                        ${i + 1}
                    </button>
                </li>
            `;
        }

        html += `
            <li>
                <button class="custom-page-btn px-3 py-2 bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-700 ${currentPage === totalPages - 1 || totalPages === 0 ? "opacity-50 cursor-not-allowed" : ""}"
                    data-page="next" ${currentPage === totalPages - 1 || totalPages === 0 ? "disabled" : ""}>
                    ›
                </button>
            </li>
        `;

        $("#custom_Pagination").html(html);
    }

    $(document).on("click", ".custom-page-btn", function () {
        const page = $(this).data("page");

        if (page === "prev") {
            table.page("previous").draw("page");
        } else if (page === "next") {
            table.page("next").draw("page");
        } else {
            table.page(parseInt(page)).draw("page");
        }
    });
});
