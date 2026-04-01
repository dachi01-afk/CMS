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
    window.tableRiwayatRestockBahanHabisPakai = $(
        "#table-riwayat-restock-bahan-habis-pakai",
    ).DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/farmasi/riwayat-restock-bahan-habis-pakai/get-data-riwayat-restock-bahan-habis-pakai",
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

    $("#riwayat-restock-bhp-search-input").on("keyup", function () {
        window.tableRiwayatRestockBahanHabisPakai.search(this.value).draw();
    });

    const $info = $("#riwayat-restock-bhp-custom-info");
    const $pagination = $("#riwayat-restock-bhp-custom-pagination");
    const $perPage = $("#riwayat-restock-bhp-page-length");

    function updatePagination() {
        const info = window.tableRiwayatRestockBahanHabisPakai.page.info();
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
            window.tableRiwayatRestockBahanHabisPakai
                .page("previous")
                .draw("page");
        } else if ($link.attr("id") === "btnNext") {
            window.tableRiwayatRestockBahanHabisPakai.page("next").draw("page");
        } else if ($link.hasClass("page-number")) {
            window.tableRiwayatRestockBahanHabisPakai
                .page(parseInt($link.data("page")) - 1)
                .draw("page");
        }
    });

    $perPage.on("change", function () {
        window.tableRiwayatRestockBahanHabisPakai.page
            .len(parseInt($(this).val(), 10))
            .draw();
    });

    window.tableRiwayatRestockBahanHabisPakai.on("draw", updatePagination);
    updatePagination();
});

$(function () {
    const pageEl = document.getElementById("riwayat-restock-bhp-page");

    const getDataRestockBahanHabisPakaiDetailUrl = pageEl
        ? pageEl.dataset.getDataRestockBhpDetailUrl
        : "";

    $(
        "#button-close-modal-detail-riwayat-restock-bhp, #button-close-footer-modal-detail-riwayat-restock-bhp",
    ).on("click", function () {
        closeModalDetailRestockBahanHabisPakai();
    });

    function buildDetailRestockBahanHabisPakaiUrl(id) {
        return getDataRestockBahanHabisPakaiDetailUrl.replace(":id", id);
    }

    function openModalDetailRestockBahanHabisPakai() {
        lockBodyScroll();
        $("#riwayat-modal-detail-restock-bhp")
            .removeClass("hidden")
            .addClass("flex");
    }

    function closeModalDetailRestockBahanHabisPakai() {
        unlockBodyScroll();
        $("#riwayat-modal-detail-restock-bhp")
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

    function resetModalDetailRestockBahanHabisPakai() {
        $("#riwayat-restock-bhp-detail_supplier").text("-");
        $("#riwayat-restock-bhp-detail_depot").text("-");
        $("#riwayat-restock-bhp-detail_no_faktur").text("-");
        $("#riwayat-restock-bhp-detail_tanggal_terima").text("-");
        $("#riwayat-restock-bhp-detail_tanggal_jatuh_tempo").text("-");
        $("#riwayat-restock-bhp-detail_status_transaksi").text("-");
        $("#riwayat-restock-bhp-detail_total_tagihan").text("Rp 0");

        $("#detail-riwayat-restock-bhp-tbody").html(`
            <tr>
                <td colspan="10" class="px-4 py-4 text-center text-slate-500">
                    Belum ada data
                </td>
            </tr>
        `);
    }

    function fillModalDetailRestockBahanHabisPakai(
        data,
        dibuatOleh,
        dikonfirmasiOleh,
    ) {
        $("#riwayat-restock-bhp-detail_supplier").text(
            data.supplier?.nama_supplier || "-",
        );

        $("#riwayat-restock-bhp-detail_dibuat_oleh").text(dibuatOleh || "-");

        $("#riwayat-restock-bhp-detail_dikonfirmasi_oleh").text(
            dikonfirmasiOleh || "-",
        );

        $("#riwayat-restock-bhp-detail_depot").text(
            data.depot?.nama_depot || "-",
        );

        $("#riwayat-restock-bhp-detail_no_faktur").text(data.no_faktur || "-");

        $("#riwayat-restock-bhp-detail_tanggal_terima").text(
            formatDateIndonesia(data.tanggal_terima),
        );

        $("#riwayat-restock-bhp-detail_tanggal_jatuh_tempo").text(
            formatDateIndonesia(data.tanggal_jatuh_tempo),
        );

        $("#riwayat-restock-bhp-detail_status_transaksi").text(
            data.status_restock || "-",
        );

        $("#riwayat-restock-bhp-detail_total_tagihan").text(
            formatRupiah(data.total_tagihan),
        );

        const details = Array.isArray(data.restock_bahan_habis_pakai_detail)
            ? data.restock_bahan_habis_pakai_detail
            : [];

        if (details.length < 1) {
            $("#detail-riwayat-restock-bhp-tbody").html(`
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
                    <td class="px-4 py-3">${detail.bahan_habis_pakai?.nama_barang || "-"}</td>
                    <td class="px-4 py-3">${detail.batch_bahan_habis_pakai?.nama_batch || "-"}</td>
                    <td class="px-4 py-3">${detail.batch_bahan_habis_pakai.format_tanggal_kadaluarsa_bahan_habis_pakai || "-"}</td>
                    <td class="px-4 py-3">${detail.qty || 0}</td>
                    <td class="px-4 py-3">${formatRupiah(detail.harga_beli)}</td>
                    <td class="px-4 py-3">${formatRupiah(detail.subtotal)}</td>
                    <td class="px-4 py-3">${renderDiskon(detail)}</td>
                    <td class="px-4 py-3">${formatRupiah(detail.diskon_amount)}</td>
                    <td class="px-4 py-3 font-semibold text-emerald-600">${formatRupiah(detail.total_setelah_diskon)}</td>
                </tr>
            `;
        });

        $("#detail-riwayat-restock-bhp-tbody").html(html);
    }

    $(document).on(
        "click",
        ".button-detail-riwayat-stok-masuk-bhp",
        function () {
            const id = $(this).data("id");

            if (!id) {
                showWarningAlert(
                    "ID restock Bahan Habis Pakai tidak ditemukan.",
                );
                return;
            }

            resetModalDetailRestockBahanHabisPakai();
            openModalDetailRestockBahanHabisPakai();

            $.ajax({
                url: buildDetailRestockBahanHabisPakaiUrl(id),
                type: "GET",
                dataType: "json",
                beforeSend: function () {
                    $("#detail-riwayat-restock-bhp-tbody").html(`
                    <tr>
                        <td colspan="10" class="px-4 py-4 text-center text-slate-500">
                            Memuat data...
                        </td>
                    </tr>
                `);
                },
                success: function (response) {
                    fillModalDetailRestockBahanHabisPakai(
                        response.data,
                        response.dibuatOleh,
                        response.dikonfirmasiOleh,
                    );
                },
                error: function (xhr) {
                    closeModalDetailRestockBahanHabisPakai();
                    showErrorAlert(
                        xhr.responseJSON?.message ||
                            "Terjadi kesalahan saat mengambil detail restock obat.",
                    );
                },
            });
        },
    );
});
