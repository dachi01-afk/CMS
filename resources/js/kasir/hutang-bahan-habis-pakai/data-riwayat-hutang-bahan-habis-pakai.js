import $ from "jquery";
import axios from "axios";

$(function () {
    window.tableRiwayatHutangBhp = $(
        "#table-data-riwayat-hutang-bhp",
    ).DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/kasir/riwayat-hutang-bahan-habis-pakai/get-data-riwayat-hutang-bahan-habis-pakai",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "supplier_id", name: "supplier_id" },
            { data: "no_faktur", name: "no_faktur" },
            {
                data: "tanggal_hutang",
                name: "tanggal_hutang",
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
                data: "tanggal_pelunasan",
                name: "tanggal_pelunasan",
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
                data: "total_hutang",
                name: "total_hutang",
                render: function (data) {
                    if (!data) return "-";
                    return "Rp " + parseFloat(data).toLocaleString("id-ID");
                },
            },
            {
                data: "status_hutang",
                name: "status_hutang",
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

    $("#data-riwayat-hutang-search-input").on("keyup", function () {
        window.tableRiwayatHutangBhp.search(this.value).draw();
    });

    const $info = $("#data-riwayat-hutang-custom-info");
    const $pagination = $("#data-riwayat-hutang-custom-pagination");
    const $perPage = $("#data-riwayat-hutang-page-length");

    function updatePagination() {
        const info = window.tableRiwayatHutangBhp.page.info();
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
            window.tableRiwayatHutangBhp.page("previous").draw("page");
        } else if ($link.attr("id") === "btnNext") {
            window.tableRiwayatHutangBhp.page("next").draw("page");
        } else if ($link.hasClass("page-number")) {
            window.tableRiwayatHutangBhp
                .page(parseInt($link.data("page")) - 1)
                .draw("page");
        }
    });

    $perPage.on("change", function () {
        window.tableRiwayatHutangBhp.page
            .len(parseInt($(this).val(), 10))
            .draw();
    });

    window.tableRiwayatHutangBhp.on("draw", updatePagination);
    updatePagination();
});

$(function () {
    const $modal = $("#modal-detail-riwayat-hutang-bhp");
    const $loading = $("#detail-riwayat-hutang-bhp-loading");
    const $content = $("#detail-riwayat-hutang-bhp-content");
    const $error = $("#detail-riwayat-hutang-bhp-error");

    function openModalDetailRiwayatHutangBhp() {
        $modal.removeClass("hidden").addClass("flex");
        $("body").addClass("overflow-hidden");
    }

    function closeModalDetailRiwayatHutangBhp() {
        $modal.addClass("hidden").removeClass("flex");
        $("body").removeClass("overflow-hidden");
    }

    function resetModalDetailRiwayatHutangBhp() {
        $loading.addClass("hidden");
        $content.addClass("hidden");
        $error.addClass("hidden").text("");

        $("#detail-riwayat-hutang-bhp-no-faktur").text("-");
        $("#detail-riwayat-hutang-bhp-status-hutang").html("-");
        $("#detail-riwayat-hutang-bhp-tanggal-hutang").text("-");
        $("#detail-riwayat-hutang-bhp-tanggal-jatuh-tempo").text("-");
        $("#detail-riwayat-hutang-bhp-tanggal-pelunasan").text("-");
        $("#detail-riwayat-hutang-bhp-metode-pembayaran").text("-");
        $("#detail-riwayat-hutang-bhp-total-hutang").text("Rp 0");

        $("#detail-riwayat-hutang-bhp-nama-supplier").text("-");
        $("#detail-riwayat-hutang-bhp-kontak-person").text("-");
        $("#detail-riwayat-hutang-bhp-no-hp-supplier").text("-");
        $("#detail-riwayat-hutang-bhp-email-supplier").text("-");
        $("#detail-riwayat-hutang-bhp-alamat-supplier").text("-");

        $("#detail-riwayat-hutang-bhp-no-faktur-restock").text("-");
        $("#detail-riwayat-hutang-bhp-tanggal-terima").text("-");
        $("#detail-riwayat-hutang-bhp-tanggal-jatuh-tempo-restock").text("-");
        $("#detail-riwayat-hutang-bhp-status-restock").html("-");
        $("#detail-riwayat-hutang-bhp-depot").text("-");
        $("#detail-riwayat-hutang-bhp-total-tagihan-restock").text("Rp 0");

        $("#detail-riwayat-hutang-bhp-dibuat-oleh").text("-");
        $("#detail-riwayat-hutang-bhp-diupdate-oleh").text("-");
        $("#detail-riwayat-hutang-bhp-created-at").text("-");
        $("#detail-riwayat-hutang-bhp-updated-at").text("-");

        $("#detail-riwayat-hutang-bhp-detail-item-body").html(`
            <tr>
                <td colspan="10" class="px-4 py-4 text-center text-slate-500">Belum ada data item.</td>
            </tr>
        `);

        $("#detail-riwayat-hutang-bhp-bukti-pembayaran-wrapper").html(`
            <p class="text-sm text-slate-500">-</p>
        `);
    }

    function showLoadingModal() {
        resetModalDetailRiwayatHutangBhp();
        $loading.removeClass("hidden");
        openModalDetailRiwayatHutangBhp();
    }

    function showErrorModal(message) {
        $loading.addClass("hidden");
        $content.addClass("hidden");
        $error.removeClass("hidden").text(message);
    }

    function renderBadgeStatusHutang(status) {
        if (!status) return "-";

        let classes =
            "inline-flex px-3 py-1 rounded-full text-xs font-semibold";

        if (status === "Sudah Lunas") {
            classes += " bg-emerald-100 text-emerald-700";
        } else if (status === "Dibatalkan") {
            classes += " bg-rose-100 text-rose-700";
        } else {
            classes += " bg-amber-100 text-amber-700";
        }

        return `<span class="${classes}">${status}</span>`;
    }

    function renderBadgeStatusRestock(status) {
        if (!status) return "-";

        let classes =
            "inline-flex px-3 py-1 rounded-full text-xs font-semibold";

        const normalized = String(status).toLowerCase();

        if (
            normalized.includes("selesai") ||
            normalized.includes("lunas") ||
            normalized.includes("diterima")
        ) {
            classes += " bg-emerald-100 text-emerald-700";
        } else if (
            normalized.includes("cancel") ||
            normalized.includes("batal")
        ) {
            classes += " bg-rose-100 text-rose-700";
        } else {
            classes += " bg-amber-100 text-amber-700";
        }

        return `<span class="${classes}">${status}</span>`;
    }

    function setDetailContent(data) {
        $("#detail-riwayat-hutang-bhp-no-faktur").text(data.no_faktur ?? "-");
        $("#detail-riwayat-hutang-bhp-status-hutang").html(
            renderBadgeStatusHutang(data.status_hutang),
        );
        $("#detail-riwayat-hutang-bhp-tanggal-hutang").text(
            formatDateIndonesia(data.tanggal_hutang),
        );
        $("#detail-riwayat-hutang-bhp-tanggal-jatuh-tempo").text(
            formatDateIndonesia(data.tanggal_jatuh_tempo),
        );
        $("#detail-riwayat-hutang-bhp-tanggal-pelunasan").text(
            formatDateIndonesia(data.tanggal_pelunasan),
        );
        $("#detail-riwayat-hutang-bhp-metode-pembayaran").text(
            data.metode_pembayaran ?? "-",
        );
        $("#detail-riwayat-hutang-bhp-total-hutang").text(
            formatRupiah(data.total_hutang),
        );

        $("#detail-riwayat-hutang-bhp-nama-supplier").text(
            data.supplier?.nama_supplier ?? "-",
        );
        $("#detail-riwayat-hutang-bhp-kontak-person").text(
            data.supplier?.kontak_person ?? "-",
        );
        $("#detail-riwayat-hutang-bhp-no-hp-supplier").text(
            data.supplier?.no_hp ?? "-",
        );
        $("#detail-riwayat-hutang-bhp-email-supplier").text(
            data.supplier?.email ?? "-",
        );
        $("#detail-riwayat-hutang-bhp-alamat-supplier").text(
            data.supplier?.alamat ?? "-",
        );

        $("#detail-riwayat-hutang-bhp-no-faktur-restock").text(
            data.restock?.no_faktur_restock ?? "-",
        );
        $("#detail-riwayat-hutang-bhp-tanggal-terima").text(
            formatDateIndonesia(data.restock?.tanggal_terima),
        );
        $("#detail-riwayat-hutang-bhp-tanggal-jatuh-tempo-restock").text(
            formatDateIndonesia(data.restock?.tanggal_jatuh_tempo),
        );
        $("#detail-riwayat-hutang-bhp-status-restock").html(
            renderBadgeStatusRestock(data.restock?.status_restock),
        );
        $("#detail-riwayat-hutang-bhp-depot").text(data.restock?.depot ?? "-");
        $("#detail-riwayat-hutang-bhp-total-tagihan-restock").text(
            formatRupiah(data.restock?.total_tagihan),
        );

        $("#detail-riwayat-hutang-bhp-dibuat-oleh").text(
            data.audit?.dibuat_oleh ?? "-",
        );
        $("#detail-riwayat-hutang-bhp-diupdate-oleh").text(
            data.audit?.diupdate_oleh ?? "-",
        );
        $("#detail-riwayat-hutang-bhp-created-at").text(
            formatDateTimeIndonesia(data.audit?.created_at),
        );
        $("#detail-riwayat-hutang-bhp-updated-at").text(
            formatDateTimeIndonesia(data.audit?.updated_at),
        );

        renderDetailItems(data.restock?.detail_item ?? []);
        renderBuktiPembayaran(data.bukti_pembayaran);

        $loading.addClass("hidden");
        $error.addClass("hidden").text("");
        $content.removeClass("hidden");
    }

    function renderDetailItems(items) {
        if (!items || items.length === 0) {
            $("#detail-riwayat-hutang-bhp-detail-item-body").html(`
                <tr>
                    <td colspan="10" class="px-4 py-4 text-center text-slate-500">Belum ada data item.</td>
                </tr>
            `);
            return;
        }

        const rows = items
            .map(
                (item) => `
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                    <td class="px-4 py-3">${item.no ?? "-"}</td>
                    <td class="px-4 py-3">${item.kode_bhp ?? "-"}</td>
                    <td class="px-4 py-3">${item.nama_bhp ?? "-"}</td>
                    <td class="px-4 py-3">${item.qty ?? 0}</td>
                    <td class="px-4 py-3">${formatRupiah(item.harga_beli)}</td>
                    <td class="px-4 py-3">${formatRupiah(item.subtotal)}</td>
                    <td class="px-4 py-3">${item.diskon_type ?? "-"}</td>
                    <td class="px-4 py-3">${Number(item.diskon_value ?? 0).toLocaleString("id-ID")}</td>
                    <td class="px-4 py-3">${formatRupiah(item.diskon_amount)}</td>
                    <td class="px-4 py-3">${formatRupiah(item.total_setelah_diskon)}</td>
                </tr>
            `,
            )
            .join("");

        $("#detail-riwayat-hutang-bhp-detail-item-body").html(rows);
    }

    function renderBuktiPembayaran(buktiPembayaran) {
        if (buktiPembayaran) {
            const imageUrl = `/storage/${buktiPembayaran}`;
            $("#detail-riwayat-hutang-bhp-bukti-pembayaran-wrapper").html(`
                <a href="${imageUrl}" target="_blank" class="inline-block">
                    <img src="${imageUrl}" alt="Bukti Pembayaran"
                        class="max-h-80 rounded-xl border border-slate-200 dark:border-slate-700 object-contain">
                </a>
            `);
        } else {
            $("#detail-riwayat-hutang-bhp-bukti-pembayaran-wrapper").html(`
                <p class="text-sm text-slate-500">Tidak ada bukti pembayaran.</p>
            `);
        }
    }

    $(document).on(
        "click",
        ".button-detail-riwayat-hutang-bhp",
        async function () {
            const noFaktur = $(this).data("no-faktur");

            showLoadingModal();

            try {
                const response = await axios.get(
                    `/kasir/riwayat-hutang-bahan-habis-pakai/get-data-detail-riwayat-hutang-bahan-habis-pakai/${encodeURIComponent(noFaktur)}`,
                );

                if (response.data.success) {
                    setDetailContent(response.data.data);
                } else {
                    showErrorModal(
                        response.data.message ||
                            "Gagal mengambil detail data hutang.",
                    );
                }
            } catch (error) {
                const message =
                    error?.response?.data?.message ||
                    "Terjadi kesalahan saat mengambil detail data hutang.";
                showErrorModal(message);
            }
        },
    );

    $("#close-modal-detail-riwayat-hutang-bhp").on("click", function () {
        closeModalDetailRiwayatHutangBhp();
    });

    $modal.on("click", function (e) {
        if (e.target === this) {
            closeModalDetailRiwayatHutangBhp();
        }
    });

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

    function formatDateTimeIndonesia(dateString) {
        if (!dateString) return "-";

        const date = new Date(dateString);
        return date.toLocaleString("id-ID", {
            timeZone: "Asia/Jakarta",
            day: "2-digit",
            month: "long",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
    }

    function formatRupiah(value) {
        return "Rp " + parseFloat(value || 0).toLocaleString("id-ID");
    }
});
