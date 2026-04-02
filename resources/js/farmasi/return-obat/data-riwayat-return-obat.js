import $, { ajax } from "jquery";

$(function () {
    window.tabelRiwayatReturnObat = $("#table-riwayat-return-obat").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/farmasi/riwayat-return-obat/get-data",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "kode_return", name: "kode_return" },
            { data: "format_tanggal_return", name: "format_tanggal_return" },
            { data: "nama_supplier", name: "nama_supplier" },
            { data: "nama_depot", name: "nama_depot" },
            { data: "status_return", name: "status_return" },
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

    $("#riwayat-return-obat-search-input").on("keyup", function () {
        window.tableReturnObat.search(this.value).draw();
    });

    const $info = $("#riwayat-return-obat-custom-info");
    const $pagination = $("#riwayat-return-obat-custom-pagination");
    const $perPage = $("#riwayat-return-obat-page-length");

    function updatePagination() {
        const info = window.tableReturnObat.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        const startData = info.recordsDisplay === 0 ? 0 : info.start + 1;
        const endData = info.recordsDisplay === 0 ? 0 : info.end;

        $info.text(
            `Menampilkan ${startData}–${endData} dari ${info.recordsDisplay} data (Halaman ${currentPage} dari ${totalPages})`,
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

        if (end - start < maxVisible - 1) {
            start = Math.max(end - maxVisible + 1, 1);
        }

        for (let i = start; i <= end; i++) {
            const active =
                i === currentPage
                    ? "text-amber-600 bg-amber-50 border-amber-300 hover:bg-amber-100"
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

        if ($link.attr("id") === "btnPrev") {
            window.tableReturnObat.page("previous").draw("page");
        } else if ($link.attr("id") === "btnNext") {
            window.tableReturnObat.page("next").draw("page");
        } else if ($link.hasClass("page-number")) {
            window.tableReturnObat
                .page(parseInt($link.data("page"), 10) - 1)
                .draw("page");
        }
    });

    $perPage.on("change", function () {
        window.tableReturnObat.page.len(parseInt($(this).val(), 10)).draw();
    });

    window.tableReturnObat.on("draw", updatePagination);
    updatePagination();
});

$(function () {
    $(document).on(
        "click",
        "#button-open-modal-detail-riwayat-return-obat",
        function () {
            const url = $(this).data("url");
            $("#modal-detail-riwayat-return-obat")
                .removeClass("hidden")
                .addClass("flex");

            $.ajax({
                url: url,
                type: "GET",
                beforeSend: () => {
                    $("#detail-riwayat-kode-return").text("Loading...");
                    $("#detail-riayat-tanggal-return").text("Loading...");
                    $("#detail-riwayat-status-return").text("Loading...");
                    $("#detail-riwayat-total-tagihan").text("Loading...");
                    $("#detail-riwayat-supplier").text("Loading...");
                    $("#detail-riwayat-kontak-person").text("Loading...");
                    $("#detail-riwayat-depot").text("Loading...");
                    $("#detail-riwayat-no-referensi").text("Loading...");
                    $("#detail-riwayat-status-piutang").text("Loading...");
                    $("#detail-riwayat-total-piutang").text("Loading...");
                    $("#detail-riwayat-tanggal-piutang").text("Loading...");
                    $("#detail-riwayat-keterangan").text("Loading...");

                    $("#detail-riwayat-return-obat-items").html(`
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">
                            Sedang mengambil data detail...
                        </td>
                    </tr>
                `);
                },
                success: (response) => {
                    const dataSupplier = response.data.supplier ?? {};
                    const dataDepot = response.data.depot ?? {};
                    const dataReturn = response.data ?? {};
                    const dataDetailReturn =
                        response.data.return_obat_detail ?? {};
                    const dataPiutang = response.data.piutang ?? {};

                    console.log(dataDetailReturn);

                    $("#detail-riwayat-kode-return").text(
                        dataReturn.kode_return ?? "-",
                    );
                    $("#detail-riwayat-tanggal-return").text(
                        dataReturn.format_tanggal_return ?? "-",
                    );

                    const status = dataReturn.status_return ?? "-";
                    $("#detail-riwayat-status-return")
                        .text(status)
                        .removeClass(
                            "bg-slate-100 text-slate-700 bg-emerald-100 text-emerald-700 bg-rose-100 text-rose-700",
                        );

                    if (status === "Succeed") {
                        $("#detail-riwayat-status-return").addClass(
                            "bg-emerald-100 text-emerald-700",
                        );
                    } else if (status === "Canceled") {
                        $("#detail-riwayat-status-return").addClass(
                            "bg-rose-100 text-rose-700",
                        );
                    } else {
                        $("#detail-riwayat-status-return").addClass(
                            "bg-slate-100 text-slate-700",
                        );
                    }

                    $("#detail-riwayat-total-tagihan").text(
                        dataReturn.format_total_tagihan ?? "-",
                    );

                    $("#detail-riwayat-supplier").text(
                        dataSupplier.nama_supplier ?? "-",
                    );

                    $("#detail-riwayat-kontak-person").text(
                        dataSupplier.kontak_person ?? "-",
                    );

                    $("#detail-riwayat-depot").text(
                        dataDepot.nama_depot ?? "-",
                    );

                    $("#detail-riwayat-no-referensi").text(
                        dataPiutang.no_referensi ?? "-",
                    );

                    const statusPiutang = dataPiutang.status_piutang ?? "-";
                    $("#detail-riwayat-status-piutang")
                        .text(statusPiutang)
                        .removeClass(
                            "bg-slate-100 text-slate-700 bg-emerald-100 text-emerald-700 bg-rose-100 text-rose-700",
                        );

                    if (statusPiutang === "Sudah Lunas") {
                        $("#detail-riwayat-status-piutang").addClass(
                            "bg-emerald-100 text-emerald-700",
                        );
                    } else if (statusPiutang === "Belum Lunas") {
                        $("#detail-riwayat-status-piutang").addClass(
                            "bg-rose-100 text-rose-700",
                        );
                    } else {
                        $("#detail-riwayat-status-piutang").addClass(
                            "bg-slate-100 text-slate-700",
                        );
                    }

                    $("#detail-riwayat-total-piutang").text(
                        dataPiutang.format_total_piutang ?? "-",
                    );

                    $("#detail-riwayat-tanggal-piutang").text(
                        dataPiutang.format_tanggal_piutang ?? "-",
                    );

                    $("#detail-riwayat-keterangan").text(
                        dataReturn.keterangan ?? "-",
                    );

                    if (dataDetailReturn.length > 0) {
                        let rows = "";

                        dataDetailReturn.forEach((item, index) => {
                            rows += `
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 text-slate-700">${index + 1}</td>
                                    <td class="px-4 py-3 text-slate-700 font-medium">
                                        ${item.obat.nama_obat ?? "-"}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 font-medium">
                                        ${item.obat.kode_obat ?? 0}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 font-medium">
                                        ${item.batch_obat.nama_batch ?? 0}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 font-medium">
                                        ${item.batch_obat.format_tanggal_kadaluarsa_obat ?? 0}
                                    </td>
                                    <td class="px-4 py-3 text-slate-700 font-medium">
                                        ${item.qty ?? 0}
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-800">
                                        ${item.format_harga_beli ?? 0}
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-800">
                                        ${item.format_subtotal ?? 0}
                                    </td>
                                </tr>
                            `;
                        });

                        $("#detail-riwayat-return-obat-items").html(rows);
                    } else {
                        $("#detail-riwayat-return-obat-items").html(`
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-slate-500">
                                    Tidak ada detail return obat
                                </td>
                            </tr>
                        `);
                    }
                },
                error: (xhr) => {
                    console.log(xhr.responseText);

                    $("#modal-detail-riwayat-return-obat")
                        .addClass("hidden")
                        .removeClass("flex");

                    alert(
                        "Terjadi kesalahan saat mengambil detail return obat",
                    );
                },
            });
        },
    );

    $(document).on(
        "click",
        "#button-close-modal-detail-riwayat-return-obat, #button-close-footer-modal-detail-return-obat",
        function () {
            $("#modal-detail-riwayat-return-obat")
                .removeClass("flex")
                .addClass("hidden");
        },
    );
});
