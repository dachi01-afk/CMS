import $ from "jquery";
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
        ajax: "/kasir/hutang-obat/get-data-hutang-obat",
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
});

$(function () {
    function formatRupiah(angka) {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(Number(angka || 0));
    }

    function formatTanggal(tanggal) {
        if (!tanggal) return "-";

        const date = new Date(tanggal);
        if (Number.isNaN(date.getTime())) return tanggal;

        return date.toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
    }

    function setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value ?? "-";
    }

    function showAlert(message, isError = false) {
        const alertEl = document.getElementById("detail-hutang-alert");
        if (!alertEl) return;

        alertEl.textContent = message;
        alertEl.classList.remove(
            "hidden",
            "border-red-200",
            "bg-red-50",
            "text-red-600",
            "border-slate-200",
            "bg-slate-50",
            "text-slate-600",
        );

        if (isError) {
            alertEl.classList.add(
                "border-red-200",
                "bg-red-50",
                "text-red-600",
            );
        } else {
            alertEl.classList.add(
                "border-slate-200",
                "bg-slate-50",
                "text-slate-600",
            );
        }
    }

    function hideAlert() {
        const alertEl = document.getElementById("detail-hutang-alert");
        if (!alertEl) return;
        alertEl.classList.add("hidden");
        alertEl.textContent = "";
    }

    function openModalDetailHutang() {
        const modal = document.getElementById("modal-detail-hutang");
        modal.classList.remove("hidden");
        modal.classList.add("flex");
    }

    function closeModalDetailHutang() {
        const modal = document.getElementById("modal-detail-hutang");
        modal.classList.add("hidden");
        modal.classList.remove("flex");
    }

    function renderPembuat(user) {
        if (!user) return "-";

        return (
            user.admin?.nama_admin ??
            user.dokter?.nama_dokter ??
            user.pasien?.nama_pasien ??
            user.farmasi?.nama_farmasi ??
            user.perawat?.nama_perawat ??
            user.kasir?.nama_kasir ??
            "-"
        );
    }

    function resetDetailHutang() {
        const fieldIds = [
            "detail-no-faktur",
            "detail-status-hutang",
            "detail-tanggal-hutang",
            "detail-tanggal-jatuh-tempo",
            "detail-tanggal-pelunasan",
            "detail-metode-pembayaran",
            "detail-total-hutang",

            "detail-supplier-nama",
            "detail-supplier-kontak",
            "detail-supplier-nohp",
            "detail-supplier-email",
            "detail-supplier-alamat",

            "detail-restock-no-faktur",
            "detail-restock-tanggal-terima",
            "detail-restock-jatuh-tempo",
            "detail-restock-status",
            "detail-restock-depot",
            "detail-restock-total-tagihan",

            "detail-dibuat-oleh",
            "detail-diupdate-oleh",
            "detail-created-at",
            "detail-updated-at",
        ];

        fieldIds.forEach((id) => setText(id, "-"));

        const tbody = document.getElementById("detail-hutang-items-body");
        if (tbody) {
            tbody.innerHTML = `
            <tr>
                <td colspan="10" class="px-4 py-6 text-center text-slate-500">
                    Belum ada data.
                </td>
            </tr>
        `;
        }
    }

    function fillDetailHutang(data) {
        const supplier = data.supplier ?? {};
        const metodePembayaran =
            data.metode_pembayaran ?? data.metodePembayaran ?? {};
        const restockObat = data.restock_obat ?? data.restockObat ?? {};
        const depot = restockObat.depot ?? {};
        const detailItems =
            restockObat.restock_obat_detail ??
            restockObat.restock_obat_detail ??
            [];
        const dibuatOleh = data.dibuat_oleh ?? data.dibuatOleh ?? null;
        const diupdateOleh = data.diupdate_oleh ?? data.diupdateOleh ?? null;

        setText("detail-no-faktur", data.no_faktur ?? "-");
        setText("detail-status-hutang", data.status_hutang ?? "-");
        setText("detail-tanggal-hutang", formatTanggal(data.tanggal_hutang));
        setText(
            "detail-tanggal-jatuh-tempo",
            formatTanggal(data.tanggal_jatuh_tempo),
        );
        setText(
            "detail-tanggal-pelunasan",
            formatTanggal(data.tanggal_pelunasan),
        );
        setText(
            "detail-metode-pembayaran",
            metodePembayaran.nama_metode ?? "-",
        );
        setText("detail-total-hutang", formatRupiah(data.total_hutang));

        setText("detail-supplier-nama", supplier.nama_supplier ?? "-");
        setText("detail-supplier-kontak", supplier.kontak_person ?? "-");
        setText("detail-supplier-nohp", supplier.no_hp ?? "-");
        setText("detail-supplier-email", supplier.email ?? "-");
        setText("detail-supplier-alamat", supplier.alamat ?? "-");

        setText("detail-restock-no-faktur", restockObat.no_faktur ?? "-");
        setText(
            "detail-restock-tanggal-terima",
            formatTanggal(restockObat.tanggal_terima),
        );
        setText(
            "detail-restock-jatuh-tempo",
            formatTanggal(restockObat.tanggal_jatuh_tempo),
        );
        setText("detail-restock-status", restockObat.status_restock ?? "-");
        setText("detail-restock-depot", depot.nama_depot ?? depot.nama ?? "-");
        setText(
            "detail-restock-total-tagihan",
            formatRupiah(restockObat.total_tagihan),
        );

        setText("detail-dibuat-oleh", renderPembuat(dibuatOleh));
        setText("detail-diupdate-oleh", renderPembuat(diupdateOleh));
        setText("detail-created-at", formatTanggal(data.created_at));
        setText("detail-updated-at", formatTanggal(data.updated_at));

        const tbody = document.getElementById("detail-hutang-items-body");
        if (!tbody) return;

        tbody.innerHTML = "";

        if (!detailItems.length) {
            tbody.innerHTML = `
            <tr>
                <td colspan="10" class="px-4 py-6 text-center text-slate-500">
                    Tidak ada detail item.
                </td>
            </tr>
        `;
            return;
        }

        detailItems.forEach((item, index) => {
            const tr = document.createElement("tr");
            tr.className = "border-b border-slate-100";

            tr.innerHTML = `
            <td class="px-4 py-3">${index + 1}</td>
            <td class="px-4 py-3">${item.obat?.kode_obat ?? "-"}</td>
            <td class="px-4 py-3">${item.obat?.nama_obat ?? "-"}</td>
            <td class="px-4 py-3 text-right">${item.qty ?? 0}</td>
            <td class="px-4 py-3 text-right">${formatRupiah(item.harga_beli)}</td>
            <td class="px-4 py-3 text-right">${formatRupiah(item.subtotal)}</td>
            <td class="px-4 py-3">${item.diskon_type ?? "-"}</td>
            <td class="px-4 py-3 text-right">${item.diskon_value ?? 0}</td>
            <td class="px-4 py-3 text-right">${formatRupiah(item.diskon_amount)}</td>
            <td class="px-4 py-3 text-right font-semibold">${formatRupiah(item.total_setelah_diskon)}</td>
        `;

            tbody.appendChild(tr);
        });
    }

    document.addEventListener("click", async function (e) {
        const button = e.target.closest(".button-detail-hutang");
        if (!button) return;

        const noFaktur = button.dataset.noFaktur;

        openModalDetailHutang();
        resetDetailHutang();
        showAlert("Memuat detail hutang...");

        try {
            const response = await axios.get(
                `/kasir/hutang-obat/get-data-detail-hutang-obat/${encodeURIComponent(noFaktur)}`,
            );
            hideAlert();
            fillDetailHutang(response.data.data);
        } catch (error) {
            console.error(error);
            resetDetailHutang();
            showAlert("Gagal memuat detail hutang.", true);
        }
    });

    document
        .getElementById("button-close-modal-detail-hutang")
        ?.addEventListener("click", function () {
            closeModalDetailHutang();
        });

    document
        .getElementById("modal-detail-hutang")
        ?.addEventListener("click", function (e) {
            if (e.target.id === "modal-detail-hutang") {
                closeModalDetailHutang();
            }
        });

    document.addEventListener("click", function (e) {
        const buttonBayar = e.target.closest(".button-bayar-hutang");
        if (!buttonBayar) return;

        const noFaktur = buttonBayar.dataset.noFaktur;
        window.location.href = `/kasir/hutang-obat/pembayaran/${encodeURIComponent(noFaktur)}`;
    });
});
