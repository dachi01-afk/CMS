import $ from "jquery";

$(function () {
    const $tableEl = $("#tabel-sudah-approve-diskon");
    const ajaxUrl = $tableEl.data("url");

    const table = $tableEl.DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        dom: "t",
        ajax: {
            url: ajaxUrl,
            type: "GET",
        },
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
                orderable: false,
                searchable: false,
            },
            {
                data: "kode_transaksi",
                name: "kode_transaksi",
                orderable: false,
                searchable: false,
            },
            {
                data: "requested_by",
                name: "requested_by",
                orderable: false,
                searchable: false,
            },
            {
                data: "approved_by",
                name: "approved_by",
                orderable: false,
                searchable: false,
            },
            {
                data: "status_badge",
                name: "status",
                orderable: false,
                searchable: false,
            },
            {
                data: "reason",
                name: "reason",
                orderable: false,
                searchable: false,
            },
            {
                data: "approved_at",
                name: "approved_at",
                orderable: true,
                searchable: false,
            },
            {
                data: "diskon_items_detail",
                name: "diskon_items_detail",
                orderable: false,
                searchable: false,
            },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
                className: "text-center whitespace-nowrap",
            },
        ],
        order: [[7, "desc"]],
        rowCallback: function (row) {
            $(row).addClass(
                "bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/40",
            );
            $("td", row).addClass("px-6 py-4 align-top");
        },
    });

    $("#sudahApprove-searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    const $info = $("#sudahApprove-customInfo");
    const $pagination = $("#sudahApprove-customPagination");
    const $perPage = $("#sudahApprove-pageLength");

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        if (info.recordsDisplay === 0) {
            $info.text("Tidak ada data yang ditampilkan.");
        } else {
            $info.text(
                `Menampilkan ${info.start + 1}–${info.end} dari ${info.recordsDisplay} data (Halaman ${currentPage} dari ${totalPages})`,
            );
        }

        $pagination.empty();

        const prevDisabled =
            currentPage === 1
                ? "opacity-50 cursor-not-allowed pointer-events-none"
                : "";

        $pagination.append(`
            <li>
                <a href="#" id="btnPrev"
                   class="flex items-center justify-center px-3 h-9 text-slate-600 dark:text-slate-200 bg-white dark:bg-slate-700 border-r border-slate-200 dark:border-slate-600 ${prevDisabled}">
                   Prev
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
                    ? "bg-sky-50 text-sky-700 dark:bg-slate-600 dark:text-white font-bold"
                    : "bg-white text-slate-600 dark:bg-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-600";

            $pagination.append(`
                <li>
                    <a href="#" class="page-number flex items-center justify-center px-3 h-9 border-r border-slate-200 dark:border-slate-600 ${active}"
                       data-page="${i}">
                       ${i}
                    </a>
                </li>
            `);
        }

        const nextDisabled =
            currentPage === totalPages
                ? "opacity-50 cursor-not-allowed pointer-events-none"
                : "";

        $pagination.append(`
            <li>
                <a href="#" id="btnNext"
                   class="flex items-center justify-center px-3 h-9 text-slate-600 dark:text-slate-200 bg-white dark:bg-slate-700 ${nextDisabled}">
                   Next
                </a>
            </li>
        `);
    }

    $pagination.on("click", "a", function (e) {
        e.preventDefault();
        const $link = $(this);

        if ($link.hasClass("pointer-events-none")) return;

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

    function formatRupiah(value) {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(Number(value || 0));
    }

    function formatNumber(value) {
        const n = Number(value || 0);

        if (Number.isInteger(n)) {
            return n.toString();
        }

        return n.toLocaleString("id-ID", {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2,
        });
    }

    function escapeHtml(text) {
        return String(text ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function openModalDetailDiskonSudahApprove() {
        $("#modalDetailDiskonSudahApprove")
            .removeClass("hidden")
            .addClass("flex");
        $("body").addClass("overflow-hidden");
    }

    function closeModalDetailDiskonSudahApprove() {
        $("#modalDetailDiskonSudahApprove")
            .addClass("hidden")
            .removeClass("flex");
        $("body").removeClass("overflow-hidden");
    }

    function resetModalDetailDiskonSudahApprove() {
        $("#modalDetailDiskonErrorSudahApprove").addClass("hidden").text("");
        $("#modalDetailDiskonLoadingSudahApprove").addClass("hidden");

        $("#modalNamaPasienSudahApprove").text("-");
        $("#modalKodeTransaksiSudahApprove").text("-");
        $("#modalRequesterSudahApprove").text("-");
        $("#modalReasonSudahApprove").text("-");
        $("#modalReasonWrapSudahApprove").addClass("hidden");

        $("#modalBadgeCountSudahApprove").text("0 item");
        $("#modalBadgeTotalSudahApprove").text("Total: Rp 0");
        $("#modalBadgePotonganSudahApprove").text("Potongan: Rp 0");
        $("#modalBadgeAfterSudahApprove").text("Setelah: Rp 0");

        $("#modalItemsBodySudahApprove").html(`
            <tr>
                <td colspan="8" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                    Belum ada data.
                </td>
            </tr>
        `);
    }

    $(document).on("click", ".btn-lihat-detail-item", async function () {
        const detailUrl = $(this).data("detail-url");

        resetModalDetailDiskonSudahApprove();
        openModalDetailDiskonSudahApprove();
        $("#modalDetailDiskonLoadingSudahApprove").removeClass("hidden");

        if (!detailUrl) {
            $("#modalDetailDiskonLoadingSudahApprove").addClass("hidden");
            $("#modalDetailDiskonErrorSudahApprove")
                .removeClass("hidden")
                .text("URL detail item tidak ditemukan.");
            return;
        }

        try {
            const response = await fetch(detailUrl, {
                method: "GET",
                headers: {
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
            });

            const result = await response.json();

            $("#modalDetailDiskonLoadingSudahApprove").addClass("hidden");

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || "Gagal mengambil detail item.",
                );
            }

            const data = result.data || {};
            const summary = data.summary || {
                count: data?.totals?.item_count || 0,
                total: data?.totals?.total_base || 0,
                potongan: data?.totals?.total_diskon || 0,
                setelah_diskon: data?.totals?.total_after || 0,
            };
            const items = data.items || [];

            $("#modalNamaPasienSudahApprove").text(data.nama_pasien || "-");
            $("#modalKodeTransaksiSudahApprove").text(
                data.kode_transaksi || "-",
            );
            $("#modalRequesterSudahApprove").text(
                data.requester || data.requested_by || "-",
            );

            if (data.reason) {
                $("#modalReasonSudahApprove").text(data.reason);
                $("#modalReasonWrapSudahApprove").removeClass("hidden");
            }

            $("#modalBadgeCountSudahApprove").text(
                `${summary.count || 0} item`,
            );
            $("#modalBadgeTotalSudahApprove").text(
                `Total: ${formatRupiah(summary.total || 0)}`,
            );
            $("#modalBadgePotonganSudahApprove").text(
                `Potongan: ${formatRupiah(summary.potongan || 0)}`,
            );
            $("#modalBadgeAfterSudahApprove").text(
                `Setelah: ${formatRupiah(summary.setelah_diskon || 0)}`,
            );

            if (!items.length) {
                $("#modalItemsBodySudahApprove").html(`
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                            Tidak ada detail item.
                        </td>
                    </tr>
                `);
                return;
            }

            const rows = items
                .map(
                    (item) => `
                    <tr>
                        <td class="px-4 py-3 text-left">${escapeHtml(item.jenis || "-")}</td>
                        <td class="px-4 py-3 text-left">${escapeHtml(item.item || item.nama_item || "-")}</td>
                        <td class="px-4 py-3 text-right">${formatNumber(item.qty ?? 0)}</td>
                        <td class="px-4 py-3 text-right">${formatRupiah(item.harga || 0)}</td>
                        <td class="px-4 py-3 text-right">${formatRupiah(item.subtotal || 0)}</td>
                        <td class="px-4 py-3 text-right">${formatNumber(item.diskon_persen ?? item.persen ?? 0)}%</td>
                        <td class="px-4 py-3 text-right">${formatRupiah(item.potongan || 0)}</td>
                        <td class="px-4 py-3 text-right font-semibold">${formatRupiah(item.total || 0)}</td>
                    </tr>
                `,
                )
                .join("");

            $("#modalItemsBodySudahApprove").html(rows);
        } catch (error) {
            $("#modalDetailDiskonLoadingSudahApprove").addClass("hidden");
            $("#modalDetailDiskonErrorSudahApprove")
                .removeClass("hidden")
                .text(
                    error.message ||
                        "Terjadi kesalahan saat memuat detail item.",
                );
        }
    });

    $(document).on(
        "click",
        '[data-close-modal="detail-diskon-sudah-approve"]',
        function () {
            closeModalDetailDiskonSudahApprove();
        },
    );

    $(document).on("click", "#modalDetailDiskonSudahApprove", function (e) {
        if (e.target.id === "modalDetailDiskonSudahApprove") {
            closeModalDetailDiskonSudahApprove();
        }
    });

    $(document).on("keydown", function (e) {
        if (
            e.key === "Escape" &&
            !$("#modalDetailDiskonSudahApprove").hasClass("hidden")
        ) {
            closeModalDetailDiskonSudahApprove();
        }
    });
});
