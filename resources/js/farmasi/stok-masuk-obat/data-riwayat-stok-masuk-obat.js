import $ from "jquery";
import Swal from "sweetalert2";

let activeModalCount = 0;
let scrollTopBeforeModal = 0;

function lockBodyScroll() {
    if (activeModalCount === 0) {
        scrollTopBeforeModal = window.scrollY || window.pageYOffset;

        $("body").css({
            overflow: "hidden",
            position: "fixed",
            top: `-${scrollTopBeforeModal}px`,
            left: "0",
            right: "0",
            width: "100%",
        });
    }

    activeModalCount++;
}

function unlockBodyScroll() {
    activeModalCount = Math.max(0, activeModalCount - 1);

    if (activeModalCount === 0) {
        $("body").css({
            overflow: "",
            position: "",
            top: "",
            left: "",
            right: "",
            width: "",
        });

        window.scrollTo(0, scrollTopBeforeModal);
    }
}

function showErrorAlert(message) {
    return Swal.fire({
        icon: "error",
        title: "Terjadi Kesalahan",
        text: message,
        confirmButtonText: "OK",
    });
}

function showWarningAlert(message) {
    return Swal.fire({
        icon: "warning",
        title: "Peringatan",
        text: message,
        confirmButtonText: "OK",
    });
}

$(function () {
    window.tableRiwayatStokMasukObat = $(
        "#table-riwayat-stok-masuk-obat",
    ).DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/farmasi/riwayat-stok-masuk-obat/get-data-riwayat-stok-masuk-obat",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "supplier_id", name: "supplier_id" },
            { data: "depot_id", name: "depot_id" },
            { data: "no_faktur", name: "no_faktur" },
            {
                data: "tanggal_terima",
                name: "tanggal_terima",
                render: function (data) {
                    if (!data) return "-";
                    const date = new Date(data);
                    return date.toLocaleDateString("id-ID", {
                        timeZone: "Asia/Jakarta",
                        day: "2-digit",
                        month: "long",
                        year: "numeric",
                    });
                },
            },
            {
                data: "tanggal_jatuh_tempo",
                name: "tanggal_jatuh_tempo",
                render: function (data) {
                    if (!data) return "-";
                    const date = new Date(data);
                    return date.toLocaleDateString("id-ID", {
                        timeZone: "Asia/Jakarta",
                        day: "2-digit",
                        month: "long",
                        year: "numeric",
                    });
                },
            },
            {
                data: "total_tagihan",
                name: "total_tagihan",
                render: function (data) {
                    if (!data) return "-";
                    return "Rp " + parseFloat(data).toLocaleString("id-ID");
                },
            },
            {
                data: "status_restock",
                name: "status_restock",
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

    $("#riwayat-stok-masuk-obat-search-input").on("keyup", function () {
        window.tableRiwayatStokMasukObat.search(this.value).draw();
    });

    const $info = $("#riwayat-stok-masuk-obat-custom-info");
    const $pagination = $("#riwayat-stok-masuk-obat-custom-pagination");
    const $perPage = $("#riwayat-stok-masuk-obat-page-length");

    function updatePagination() {
        const info = window.tableRiwayatStokMasukObat.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${info.recordsDisplay} data (Halaman ${currentPage} dari ${totalPages})`,
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

        if ($link.attr("id") === "btnPrev") {
            window.tableRiwayatStokMasukObat.page("previous").draw("page");
        } else if ($link.attr("id") === "btnNext") {
            window.tableRiwayatStokMasukObat.page("next").draw("page");
        } else if ($link.hasClass("page-number")) {
            window.tableRiwayatStokMasukObat
                .page(parseInt($link.data("page"), 10) - 1)
                .draw("page");
        }
    });

    $perPage.on("change", function () {
        window.tableRiwayatStokMasukObat.page
            .len(parseInt($(this).val(), 10))
            .draw();
    });

    window.tableRiwayatStokMasukObat.on("draw", updatePagination);
    updatePagination();
});

$(function () {
    const pageEl = document.getElementById("riwayat-stok-masuk-obat-page");

    const getDataStokMasukObatDetailUrl = pageEl
        ? pageEl.dataset.getDataStokMasukObatDetailUrl
        : "";

    $(
        "#button-close-modal-detail-riwayat-stok-masuk-obat, #button-close-footer-modal-detail-riwayat-stok-masuk-obat",
    ).on("click", function () {
        closeModalDetailStokMasukObat();
    });

    function buildDetailStokMasukObatUrl(id) {
        return getDataStokMasukObatDetailUrl.replace(":id", id);
    }

    function openModalDetailStokMasukObat() {
        lockBodyScroll();
        $("#riwayat-modal-detail-stok-masuk-obat")
            .removeClass("hidden")
            .addClass("flex");
    }

    function closeModalDetailStokMasukObat() {
        unlockBodyScroll();
        $("#riwayat-modal-detail-stok-masuk-obat")
            .removeClass("flex")
            .addClass("hidden");
    }

    function formatDateIndonesia(value) {
        if (!value) return "-";

        const date = new Date(value);
        return date.toLocaleDateString("id-ID", {
            timeZone: "Asia/Jakarta",
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
    }

    function formatRupiah(value) {
        return "Rp " + parseFloat(value || 0).toLocaleString("id-ID");
    }

    function renderDiskon(detail) {
        if (!detail.diskon_type || !detail.diskon_value) {
            return "-";
        }

        if (detail.diskon_type === "persen") {
            return `${parseFloat(detail.diskon_value)}%`;
        }

        return formatRupiah(detail.diskon_value);
    }

    function resetModalDetailStokMasukObat() {
        $("#riwayat-stok-masuk-obat-detail_supplier").text("-");
        $("#riwayat-stok-masuk-obat-detail_depot").text("-");
        $("#riwayat-stok-masuk-obat-detail_no_faktur").text("-");
        $("#riwayat-stok-masuk-obat-detail_tanggal_terima").text("-");
        $("#riwayat-stok-masuk-obat-detail_tanggal_jatuh_tempo").text("-");
        $("#riwayat-stok-masuk-obat-detail_status_transaksi").text("-");
        $("#riwayat-stok-masuk-obat-detail_total_tagihan").text("Rp 0");

        $("#detail-riwayat-stok-masuk-obat-tbody").html(`
            <tr>
                <td colspan="10" class="px-4 py-4 text-center text-slate-500">
                    Belum ada data
                </td>
            </tr>
        `);
    }

    function fillModalDetailStokMasukObat(data) {
        $("#riwayat-stok-masuk-obat-detail_supplier").text(
            data.supplier?.nama_supplier || "-",
        );
        $("#riwayat-stok-masuk-obat-detail_depot").text(
            data.depot?.nama_depot || "-",
        );
        $("#riwayat-stok-masuk-obat-detail_no_faktur").text(
            data.no_faktur || "-",
        );
        $("#riwayat-stok-masuk-obat-detail_tanggal_terima").text(
            formatDateIndonesia(data.tanggal_terima),
        );
        $("#riwayat-stok-masuk-obat-detail_tanggal_jatuh_tempo").text(
            formatDateIndonesia(data.tanggal_jatuh_tempo),
        );
        $("#riwayat-stok-masuk-obat-detail_status_transaksi").text(
            data.status_restock || "-",
        );
        $("#riwayat-stok-masuk-obat-detail_total_tagihan").text(
            formatRupiah(data.total_tagihan),
        );

        const details = Array.isArray(data.restock_obat_detail)
            ? data.restock_obat_detail
            : [];

        if (details.length < 1) {
            $("#detail-riwayat-stok-masuk-obat-tbody").html(`
                <tr>
                    <td colspan="10" class="px-4 py-4 text-center text-slate-500">
                        Tidak ada detail item
                    </td>
                </tr>
            `);
            return;
        }

        let html = "";

        details.forEach((detail, index) => {
            html += `
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">${index + 1}</td>
                    <td class="px-4 py-3">${detail.obat?.nama_obat || "-"}</td>
                    <td class="px-4 py-3">${detail.batch_obat?.nama_batch || "-"}</td>
                    <td class="px-4 py-3">${detail.batch_obat?.tanggal_kadaluarsa_obat || "-"}</td>
                    <td class="px-4 py-3">${detail.qty || 0}</td>
                    <td class="px-4 py-3">${formatRupiah(detail.harga_beli)}</td>
                    <td class="px-4 py-3">${formatRupiah(detail.subtotal)}</td>
                    <td class="px-4 py-3">${renderDiskon(detail)}</td>
                    <td class="px-4 py-3">${formatRupiah(detail.diskon_amount)}</td>
                    <td class="px-4 py-3 font-semibold text-emerald-600">${formatRupiah(detail.total_setelah_diskon)}</td>
                </tr>
            `;
        });

        $("#detail-riwayat-stok-masuk-obat-tbody").html(html);
    }

    $(document).on(
        "click",
        ".button-detail-riwayat-stok-masuk-obat",
        function () {
            const id = $(this).data("id");

            if (!id) {
                showWarningAlert("ID stok masuk obat tidak ditemukan.");
                return;
            }

            resetModalDetailStokMasukObat();
            openModalDetailStokMasukObat();

            $.ajax({
                url: buildDetailStokMasukObatUrl(id),
                type: "GET",
                dataType: "json",
                beforeSend: function () {
                    $("#detail-riwayat-stok-masuk-obat-tbody").html(`
                    <tr>
                        <td colspan="10" class="px-4 py-4 text-center text-slate-500">
                            Memuat data...
                        </td>
                    </tr>
                `);
                },
                success: function (response) {
                    fillModalDetailStokMasukObat(response.data);
                },
                error: function (xhr) {
                    closeModalDetailStokMasukObat();
                    showErrorAlert(
                        xhr.responseJSON?.message ||
                            "Terjadi kesalahan saat mengambil detail stok masuk obat.",
                    );
                },
            });
        },
    );
});

$(function () {
    const pageEl = document.getElementById("riwayat-stok-masuk-obat-page");

    const getDataRestockObatDetailUrl = pageEl
        ? pageEl.dataset.getDataRestockObatDetailUrl
        : "";

    $(
        "#button-close-modal-detail-riwayat-stok-masuk-obat, #button-close-footer-modal-detail-riwayat-stok-masuk-obat",
    ).on("click", function () {
        closeModalDetailRestockObat();
    });

    function buildDetailRestockObatUrl(id) {
        return getDataRestockObatDetailUrl.replace(":id", id);
    }

    function openModalDetailRestockObat() {
        lockBodyScroll();
        $("#riwayat-modal-detail-stok-masuk-obat")
            .removeClass("hidden")
            .addClass("flex");
    }

    function closeModalDetailRestockObat() {
        unlockBodyScroll();
        $("#riwayat-modal-detail-stok-masuk-obat")
            .removeClass("flex")
            .addClass("hidden");
    }

    function formatDateIndonesia(value) {
        if (!value) return "-";

        const date = new Date(value);
        return date.toLocaleDateString("id-ID", {
            timeZone: "Asia/Jakarta",
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
    }

    function formatRupiah(value) {
        return "Rp " + parseFloat(value || 0).toLocaleString("id-ID");
    }

    function formatPersen(value) {
        const number = parseFloat(value || 0);

        if (Number.isInteger(number)) {
            return `${number}%`;
        }

        return `${number.toLocaleString("id-ID")} %`;
    }

    function renderDiskon(detail) {
        const diskonType = (detail.diskon_type || "").toString().toLowerCase();
        const diskonValue = parseFloat(detail.diskon_value || 0);

        if (!diskonType || diskonValue <= 0) {
            return "-";
        }

        if (diskonType === "persen") {
            return formatPersen(diskonValue);
        }

        if (diskonType === "nominal") {
            return formatRupiah(diskonValue);
        }

        return "-";
    }

    function resetModalDetailRestockObat() {
        $("#riwayat-stok-masuk-obat-detail_supplier").text("-");
        $("#riwayat-stok-masuk-obat-detail_depot").text("-");
        $("#riwayat-stok-masuk-obat-detail_no_faktur").text("-");
        $("#riwayat-stok-masuk-obat-detail_tanggal_terima").text("-");
        $("#riwayat-stok-masuk-obat-detail_tanggal_jatuh_tempo").text("-");
        $("#riwayat-stok-masuk-obat-detail_status_transaksi").text("-");
        $("#riwayat-stok-masuk-obat-detail_total_tagihan").text("Rp 0");

        $("#detail-riwayat-stok-masuk-obat-tbody").html(`
            <tr>
                <td colspan="10" class="px-4 py-4 text-center text-slate-500">
                    Belum ada data
                </td>
            </tr>
        `);
    }

    function fillModalDetailRestockObat(data) {
        $("#riwayat-stok-masuk-obat-detail_supplier").text(
            data.supplier?.nama_supplier || "-",
        );
        $("#riwayat-stok-masuk-obat-detail_depot").text(
            data.depot?.nama_depot || "-",
        );
        $("#riwayat-stok-masuk-obat-detail_no_faktur").text(
            data.no_faktur || "-",
        );
        $("#riwayat-stok-masuk-obat-detail_tanggal_terima").text(
            formatDateIndonesia(data.tanggal_terima),
        );
        $("#riwayat-stok-masuk-obat-detail_tanggal_jatuh_tempo").text(
            formatDateIndonesia(data.tanggal_jatuh_tempo),
        );
        $("#riwayat-stok-masuk-obat-detail_status_transaksi").text(
            data.status_restock || "-",
        );
        $("#riwayat-stok-masuk-obat-detail_total_tagihan").text(
            formatRupiah(data.total_tagihan),
        );

        const details = Array.isArray(data.restock_obat_detail)
            ? data.restock_obat_detail
            : [];

        if (details.length < 1) {
            $("#detail-riwayat-stok-masuk-obat-tbody").html(`
                <tr>
                    <td colspan="10" class="px-4 py-4 text-center text-slate-500">
                        Tidak ada detail item
                    </td>
                </tr>
            `);
            return;
        }

        let html = "";

        details.forEach((detail, index) => {
            html += `
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">${index + 1}</td>
                    <td class="px-4 py-3">${detail.obat?.nama_obat || "-"}</td>
                    <td class="px-4 py-3">${detail.batch_obat?.nama_batch || "-"}</td>
                    <td class="px-4 py-3">${detail.batch_obat?.tanggal_kadaluarsa_obat || "-"}</td>
                    <td class="px-4 py-3">${detail.qty || 0}</td>
                    <td class="px-4 py-3">${formatRupiah(detail.harga_beli)}</td>
                    <td class="px-4 py-3">${formatRupiah(detail.subtotal)}</td>
                    <td class="px-4 py-3">${renderDiskon(detail)}</td>
                    <td class="px-4 py-3">${formatRupiah(detail.diskon_amount)}</td>
                    <td class="px-4 py-3 font-semibold text-emerald-600">${formatRupiah(detail.total_setelah_diskon)}</td>
                </tr>
            `;
        });

        $("#detail-riwayat-stok-masuk-obat-tbody").html(html);
    }

    $(document).on("click", ".btn-detail-riwayat-stok-masuk-obat", function () {
        const id = $(this).data("id");

        if (!id) {
            showWarningAlert("ID restock obat tidak ditemukan.");
            return;
        }

        resetModalDetailRestockObat();
        openModalDetailRestockObat();

        $.ajax({
            url: buildDetailRestockObatUrl(id),
            type: "GET",
            dataType: "json",
            beforeSend: function () {
                $("#detail-riwayat-stok-masuk-obat-tbody").html(`
                    <tr>
                        <td colspan="10" class="px-4 py-4 text-center text-slate-500">
                            Memuat data...
                        </td>
                    </tr>
                `);
            },
            success: function (response) {
                fillModalDetailRestockObat(response.data);
            },
            error: function (xhr) {
                closeModalDetailRestockObat();
                showErrorAlert(
                    xhr.responseJSON?.message ||
                        "Terjadi kesalahan saat mengambil detail restock obat.",
                );
            },
        });
    });
});
