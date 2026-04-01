import $ from "jquery";
import Swal from "sweetalert2";

$(function () {
    window.tableStokMasukBahanHabisPakai = $(
        "#table-stok-masuk-bahan-habis-pakai",
    ).DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/farmasi/stok-masuk-bahan-habis-pakai/get-data-stok-masuk-bahan-habis-pakai",
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
                    if (!data) return "Rp 0";
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
                className:
                    "text-center whitespace-nowrap sticky right-0 bg-white dark:bg-slate-800",
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

    $("#stok-masuk-obat-search-input").on("keyup", function () {
        window.tableStokMasukBahanHabisPakai.search(this.value).draw();
    });

    const $info = $("#stok-masuk-obat-custom-info");
    const $pagination = $("#stok-masuk-obat-custom-pagination");
    const $perPage = $("#stok-masuk-obat-page-length");

    function updatePagination() {
        const info = window.tableStokMasukBahanHabisPakai.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        if (info.recordsDisplay === 0) {
            $info.text("Belum ada data yang ditampilkan");
        } else {
            $info.text(
                `Menampilkan ${info.start + 1}–${info.end} dari ${info.recordsDisplay} data (Halaman ${currentPage} dari ${totalPages})`,
            );
        }

        $pagination.empty();

        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";

        $pagination.append(`
            <li>
                <a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">
                    Previous
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
                    ? "text-blue-600 bg-blue-50 border-blue-300 hover:bg-blue-100"
                    : "text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700";

            $pagination.append(`
                <li>
                    <a href="#" class="page-number flex items-center justify-center px-3 h-8 border ${active}" data-page="${i}">
                        ${i}
                    </a>
                </li>
            `);
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 cursor-not-allowed" : "";

        $pagination.append(`
            <li>
                <a href="#" id="btnNext" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">
                    Next
                </a>
            </li>
        `);
    }

    $pagination.on("click", "a", function (e) {
        e.preventDefault();

        const $link = $(this);

        if ($link.hasClass("opacity-50")) return;

        if ($link.attr("id") === "btnPrev") {
            window.tableStokMasukBahanHabisPakai.page("previous").draw("page");
        } else if ($link.attr("id") === "btnNext") {
            window.tableStokMasukBahanHabisPakai.page("next").draw("page");
        } else if ($link.hasClass("page-number")) {
            window.tableStokMasukBahanHabisPakai
                .page(parseInt($link.data("page")) - 1)
                .draw("page");
        }
    });

    $perPage.on("change", function () {
        window.tableStokMasukBahanHabisPakai.page
            .len(parseInt($(this).val(), 10))
            .draw();
    });

    window.tableStokMasukBahanHabisPakai.on("draw", updatePagination);
    updatePagination();

    $(document).on("click", ".btn-konfirmasi-stok-masuk", function () {
        const id = $(this).data("id");
        console.log("Konfirmasi stok masuk ID:", id);
    });
});

$(function () {
    function formatDateIndonesia(dateString) {
        if (!dateString) return "-";
        const date = new Date(dateString);
        return date.toLocaleDateString("id-ID", {
            timeZone: "Asia/Jakarta",
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
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

    function formatRupiah(value) {
        return "Rp " + parseFloat(value || 0).toLocaleString("id-ID");
    }

    function openModalDetail() {
        $("#modal-detail-stok-masuk-bahan-habis-pakai")
            .removeClass("hidden")
            .addClass("flex");
    }

    function closeModalDetail() {
        $("#modal-detail-stok-masuk-bahan-habis-pakai")
            .removeClass("flex")
            .addClass("hidden");
    }

    function resetModalDetail() {
        $("#detail-nama-supplier").text("-");
        $("#detail-nama-depot").text("-");
        $("#detail-no-faktur").text("-");
        $("#detail-status-hutang").text("-");
        $("#detail-tanggal-terima").text("-");
        $("#detail-tanggal-jatuh-tempo").text("-");
        $("#detail-total-tagihan").text("-");
        $("#detail-status-restock").text("-");
        $("#detail-stok-masuk-items-body").html("");
    }

    $(document).on("click", ".btn-detail-stok-masuk", function () {
        const noFaktur = $(this).data("noFaktur");

        resetModalDetail();
        openModalDetail();

        $("#detail-stok-masuk-loading").removeClass("hidden");
        $("#detail-stok-masuk-content").addClass("hidden");

        $.ajax({
            url: `/farmasi/stok-masuk-bahan-habis-pakai/get-data-detail-stok-masuk-bahan-habis-pakai/${noFaktur}`,
            type: "GET",
            success: function (response) {
                const data = response.data;

                console.log(data);
                
                $("#detail-nama-supplier").text(data.nama_supplier ?? "-");
                $("#detail-nama-depot").text(data.nama_depot ?? "-");
                $("#detail-no-faktur").text(data.no_faktur ?? "-");
                $("#detail-status-hutang").text(data.status_hutang ?? "-");
                $("#detail-tanggal-terima").text(
                    formatDateIndonesia(data.tanggal_terima),
                );
                $("#detail-tanggal-jatuh-tempo").text(
                    formatDateIndonesia(data.tanggal_jatuh_tempo),
                );
                $("#detail-total-tagihan").text(
                    formatRupiah(data.total_tagihan),
                );
                $("#detail-status-restock").text(data.status_restock ?? "-");

                $("#detail-dibuat-oleh").text(data.dibuatOleh ?? "-");
                $("#detail-dikonfirmasi-oleh").text(data.dikonfirmasiOleh ?? "-");

                let html = "";

                if (data.items && data.items.length > 0) {
                    data.items.forEach((item, index) => {
                        html += `
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                                <td class="px-4 py-3">${index + 1}</td>
                                <td class="px-4 py-3">${item.kode ?? "-"}</td>
                                <td class="px-4 py-3">${item.nama_barang ?? "-"}</td>
                                <td class="px-4 py-3">${item.nama_batch ?? "-"}</td>
                                <td class="px-4 py-3">${formatDateIndonesia(item.tanggal_kadaluarsa_bahan_habis_pakai)}</td>
                                <td class="px-4 py-3">${item.qty ?? 0}</td>
                                <td class="px-4 py-3">${formatRupiah(item.harga_beli)}</td>
                                <td class="px-4 py-3">${renderDiskon(item)}</td>
                                <td class="px-4 py-3">${formatRupiah(item.total_setelah_diskon)}</td>
                            </tr>
                        `;
                    });
                } else {
                    html = `
                        <tr>
                            <td colspan="9" class="px-4 py-4 text-center text-slate-500 dark:text-slate-300">
                                Tidak ada detail item bahan habis pakai.
                            </td>
                        </tr>
                    `;
                }

                $("#detail-stok-masuk-items-body").html(html);

                $("#detail-stok-masuk-loading").addClass("hidden");
                $("#detail-stok-masuk-content").removeClass("hidden");
            },
            error: function (xhr) {
                $("#detail-stok-masuk-loading").addClass("hidden");
                $("#detail-stok-masuk-content").removeClass("hidden");

                $("#detail-stok-masuk-items-body").html(`
                    <tr>
                        <td colspan="9" class="px-4 py-4 text-center text-red-500">
                            Gagal memuat detail data.
                        </td>
                    </tr>
                `);
            },
        });
    });

    $(
        "#btn-close-modal-detail-stok-masuk-bahan-habis-pakai, #btn-close-footer-modal-detail-stok-masuk-bahan-habis-pakai",
    ).on("click", function () {
        closeModalDetail();
    });

    $(document).on("click", ".btn-konfirmasi-stok-masuk", function () {
        const id = $(this).data("id");

        Swal.fire({
            title: "Konfirmasi stok masuk?",
            text: "Stok bahan habis pakai akan ditambahkan ke stok global, stok depot, dan stok batch depot.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, konfirmasi",
            cancelButtonText: "Batal",
            reverseButtons: true,
            customClass: {
                confirmButton: "swal2-confirm",
                cancelButton: "swal2-cancel",
            },
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/farmasi/stok-masuk-bahan-habis-pakai/konfirmasi/${id}`,
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr("content"),
                },
                beforeSend: function () {
                    Swal.fire({
                        title: "Memproses...",
                        text: "Sedang mengonfirmasi stok masuk bahan habis pakai.",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });
                },
                success: function (response) {
                    if (window.tableStokMasukBahanHabisPakai) {
                        window.tableStokMasukBahanHabisPakai.ajax.reload(
                            null,
                            false,
                        );
                    }

                    if (window.tableRiwayatStokMasukBahanHabisPakai) {
                        window.tableRiwayatStokMasukBahanHabisPakai.ajax.reload(
                            null,
                            false,
                        );
                    }

                    Swal.fire({
                        icon: "success",
                        title: "Berhasil",
                        text: response.message,
                        timer: 1800,
                        showConfirmButton: false,
                    });
                },
                error: function (xhr) {
                    let message =
                        "Terjadi kesalahan saat konfirmasi stok masuk.";

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }

                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text: message,
                    });
                },
            });
        });
    });
});
