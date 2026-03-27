import $ from "jquery";
import "datatables.net";
import axios from "axios";

$(function () {
    const table = $("#table-hutang").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/kasir/piutang-bahan-habis-pakai/get-data-piutang-bahan-habis-pakai",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "nama_supplier", name: "nama_supplier" },
            { data: "no_faktur", name: "no_faktur" },
            {
                data: "tanggal_piutang",
                name: "tanggal_piutang",
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
                data: "total_piutang",
                name: "total_piutang",
                render: function (data) {
                    if (!data) return "Rp 0";
                    return "Rp " + parseFloat(data).toLocaleString("id-ID");
                },
            },
            {
                data: "status_piutang",
                name: "status_piutang",
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

    $("#hutang-search-input").on("keyup", function () {
        table.search(this.value).draw();
    });

    const $info = $("#hutang-custom-info");
    const $pagination = $("#hutang-custom-pagination");
    const $perPage = $("#hutang-page-length");

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        if (info.recordsDisplay === 0) {
            $info.text("Tidak ada data yang ditampilkan");
        } else {
            $info.text(
                `Menampilkan ${info.start + 1}–${info.end} dari ${info.recordsDisplay} data (Halaman ${currentPage} dari ${totalPages})`,
            );
        }

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
            table.page("previous").draw("page");
        } else if ($link.attr("id") === "btnNext") {
            table.page("next").draw("page");
        } else if ($link.hasClass("page-number")) {
            table.page(parseInt($link.data("page")) - 1).draw("page");
        }
    });

    $perPage.on("change", function () {
        table.page.len(parseInt($(this).val(), 10)).draw();
    });

    table.on("draw", updatePagination);
    updatePagination();

    function formatDate(value) {
        if (!value) return "-";
        const date = new Date(value);
        return date.toLocaleDateString("id-ID", {
            timeZone: "Asia/Jakarta",
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
    }

    function formatCurrency(value) {
        const number = parseFloat(value || 0);
        return "Rp " + number.toLocaleString("id-ID");
    }

    function badgeStatus(status) {
        if (status === "Sudah Lunas") {
            return `<span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-medium text-emerald-700">Sudah Lunas</span>`;
        }

        return `<span class="inline-flex items-center rounded-full bg-rose-100 px-2.5 py-1 text-xs font-medium text-rose-700">Belum Lunas</span>`;
    }

    const $modal = $("#modal-detail-piutang-bhp");
    const $loading = $("#detail-piutang-bhp-loading");
    const $content = $("#detail-piutang-bhp-content");
    const $empty = $("#detail-piutang-bhp-empty");

    function openModal() {
        $modal.removeClass("hidden");
        $("body").addClass("overflow-hidden");
    }

    function closeModal() {
        $modal.addClass("hidden");
        $("body").removeClass("overflow-hidden");
    }

    $("#close-modal-detail-piutang-bhp").on("click", closeModal);

    $modal.on("click", function (e) {
        if (e.target === this) {
            closeModal();
        }
    });

    $(document).on("keydown", function (e) {
        if (e.key === "Escape") {
            closeModal();
        }
    });

    $(document).on("click", ".btn-detail-piutang-bhp", async function () {
        const id = $(this).data("id");

        openModal();
        $loading.removeClass("hidden");
        $content.addClass("hidden");
        $empty.addClass("hidden");

        try {
            const response = await axios.get(
                `/kasir/piutang-bahan-habis-pakai/get-data-detail-piutang-bahan-habis-pakai/${id}`,
            );
            const data = response.data.data;
            const header = data.header;
            const items = data.items || [];

            $("#detail-bhp-no-referensi").text(header.no_referensi ?? "-");
            $("#detail-bhp-status-piutang").html(
                badgeStatus(header.status_piutang),
            );
            $("#detail-bhp-tanggal-piutang").text(
                formatDate(header.tanggal_piutang),
            );
            $("#detail-bhp-tanggal-jatuh-tempo").text(
                formatDate(header.tanggal_jatuh_tempo),
            );
            $("#detail-bhp-tanggal-pelunasan").text(
                formatDate(header.tanggal_pelunasan),
            );
            $("#detail-bhp-total-piutang").text(
                formatCurrency(header.total_piutang),
            );
            $("#detail-bhp-metode-penerimaan").text(
                header.nama_metode_penerimaan ?? "-",
            );
            $("#detail-bhp-bukti-penerimaan").text(
                header.bukti_penerimaan ?? "-",
            );

            $("#detail-bhp-nama-supplier").text(header.nama_supplier ?? "-");
            $("#detail-bhp-kontak-person").text(header.kontak_person ?? "-");
            $("#detail-bhp-no-hp").text(header.no_hp ?? "-");
            $("#detail-bhp-email").text(header.email ?? "-");
            $("#detail-bhp-alamat").text(header.alamat ?? "-");
            $("#detail-bhp-dibuat-oleh").text(header.dibuat_oleh_nama ?? "-");
            $("#detail-bhp-diupdate-oleh").text(
                header.diupdate_oleh_nama ?? "-",
            );

            const $tbody = $("#detail-bhp-items-body");
            $tbody.empty();

            if (items.length > 0) {
                items.forEach((item, index) => {
                    const diskonText = item.diskon_type
                        ? `${item.diskon_type} (${item.diskon_value ?? 0}) / Rp ${parseFloat(item.diskon_amount ?? 0).toLocaleString("id-ID")}`
                        : "-";

                    $tbody.append(`
                        <tr class="bg-white dark:bg-slate-800">
                            <td class="px-4 py-3">${index + 1}</td>
                            <td class="px-4 py-3">${item.kode_bahan_habis_pakai ?? "-"}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-100">${item.nama_bahan_habis_pakai ?? "-"}</td>
                            <td class="px-4 py-3 text-right">${item.qty ?? 0}</td>
                            <td class="px-4 py-3 text-right">${formatCurrency(item.harga_beli)}</td>
                            <td class="px-4 py-3 text-right">${formatCurrency(item.subtotal)}</td>
                            <td class="px-4 py-3">${diskonText}</td>
                            <td class="px-4 py-3 text-right font-semibold">${formatCurrency(item.total_setelah_diskon)}</td>
                        </tr>
                    `);
                });

                $content.removeClass("hidden");
            } else {
                $empty.removeClass("hidden");
            }
        } catch (error) {
            $empty.removeClass("hidden");
            console.error(error);
        } finally {
            $loading.addClass("hidden");
        }
    });
});
