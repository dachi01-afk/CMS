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

function showSuccessMessage(message = "Berhasil.") {
    return Swal.fire({
        icon: "success",
        title: "Berhasil",
        text: message,
        confirmButtonText: "Oke",
        confirmButtonColor: "#059669",
    });
}

function showErrorMessage(message = "Terjadi kesalahan.") {
    return Swal.fire({
        icon: "error",
        title: "Gagal",
        text: message,
        confirmButtonText: "Tutup",
        confirmButtonColor: "#dc2626",
    });
}

function showWarningMessage(message = "Perhatian.") {
    return Swal.fire({
        icon: "warning",
        title: "Perhatian",
        text: message,
        confirmButtonText: "Mengerti",
        confirmButtonColor: "#d97706",
    });
}

function showValidationMessage(messages = []) {
    const html = messages
        .map((msg) => `<li style="text-align:left;">${msg}</li>`)
        .join("");

    return Swal.fire({
        icon: "warning",
        title: "Validasi Gagal",
        html: `<ul style="margin:0; padding-left:18px;">${html}</ul>`,
        confirmButtonText: "Mengerti",
        confirmButtonColor: "#d97706",
    });
}

function normalizeResponseArray(response) {
    if (Array.isArray(response?.data)) {
        return response.data;
    }

    if (Array.isArray(response)) {
        return response;
    }

    return [];
}

function formatNumberIndonesia(value) {
    return new Intl.NumberFormat("id-ID").format(parseFloat(value || 0));
}

function parseFormattedNumber(value) {
    if (value === null || value === undefined) return 0;
    return parseFloat(String(value).replace(/[^\d]/g, "")) || 0;
}

function formatRupiahInput(value) {
    return formatNumberIndonesia(value);
}

$(function () {
    window.tableReturnObat = $("#table-return-obat").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/farmasi/return-obat/get-data-return-obat",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "kode_return", name: "kode_return" },
            {
                data: "tanggal_return",
                name: "tanggal_return",
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
            { data: "supplier_id", name: "supplier_id" },
            { data: "depot_id", name: "depot_id" },
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

    $("#return-obat-search-input").on("keyup", function () {
        window.tableReturnObat.search(this.value).draw();
    });

    const $info = $("#return-obat-custom-info");
    const $pagination = $("#return-obat-custom-pagination");
    const $perPage = $("#return-obat-page-length");

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
    let supplierTom = null;
    let depotTom = null;

    const pageEl = document.getElementById("return-obat-page");

    const batchUrlTemplate = pageEl ? pageEl.dataset.batchUrl || "" : "";
    const stockBatchUrlTemplate = pageEl
        ? pageEl.dataset.stockBatchUrl || ""
        : "";
    const getSupplierUrl = pageEl ? pageEl.dataset.getSupplierUrl || "" : "";
    const getDepotBySupplierUrl = pageEl
        ? pageEl.dataset.getDepotBySupplierUrl || ""
        : "";
    const getObatBySupplierDepotUrl = pageEl
        ? pageEl.dataset.getObatBySupplierDepotUrl || ""
        : "";
    const createReturnObatUrl = pageEl
        ? pageEl.dataset.createReturnObatUrl || ""
        : "";

    const detailReturnUrlTemplate = pageEl
        ? pageEl.dataset.detailReturnUrl || ""
        : "";

    function buildDetailReturnUrl(kodeReturn) {
        return detailReturnUrlTemplate.replace(
            ":kodeReturn",
            encodeURIComponent(kodeReturn),
        );
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

    function formatCurrency(value) {
        return `Rp ${formatNumberIndonesia(value || 0)}`;
    }

    function openModalDetailReturn() {
        lockBodyScroll();
        $("#modal-detail-return-obat").removeClass("hidden").addClass("flex");
    }

    function closeModalDetailReturn() {
        unlockBodyScroll();
        $("#modal-detail-return-obat").removeClass("flex").addClass("hidden");
    }

    function resetDetailModalContent() {
        $("#detail-kode-return").text("-");
        $("#detail-tanggal-return").text("-");
        $("#detail-status-return").text("-");
        $("#detail-total-tagihan").text("Rp 0");
        $("#detail-supplier").text("-");
        $("#detail-kontak-person").text("-");
        $("#detail-depot").text("-");
        $("#detail-no-referensi").text("-");
        $("#detail-status-piutang").text("-");
        $("#detail-total-piutang").text("Rp 0");
        $("#detail-tanggal-piutang").text("-");
        $("#detail-keterangan").text("-");

        $("#detail-return-obat-items").html(`
        <tr>
            <td colspan="8" class="px-4 py-6 text-center text-slate-500">
                Belum ada data.
            </td>
        </tr>
    `);
    }

    function renderDetailReturnModal(data) {
        $("#detail-kode-return").text(data.kode_return || "-");
        $("#detail-tanggal-return").text(
            formatDateIndonesia(data.tanggal_return),
        );
        $("#detail-status-return").text(data.status_return || "-");
        $("#detail-total-tagihan").text(
            formatCurrency(data.total_tagihan || 0),
        );

        $("#detail-supplier").text(data.supplier?.nama_supplier || "-");
        $("#detail-kontak-person").text(data.supplier?.kontak_person || "-");
        $("#detail-depot").text(data.depot?.nama_depot || "-");

        console.log(data);

        $("#detail-no-referensi").text(data.piutang_obat?.no_referensi || "-");
        $("#detail-status-piutang").text(
            data.piutang_obat?.status_piutang || "-",
        );
        $("#detail-total-piutang").text(
            formatCurrency(data.piutang_obat?.total_piutang || 0),
        );
        $("#detail-tanggal-piutang").text(
            formatDateIndonesia(data.piutang_obat?.tanggal_piutang),
        );

        $("#detail-keterangan").text(data.keterangan || "-");

        const details = Array.isArray(data.details) ? data.details : [];

        if (details.length === 0) {
            $("#detail-return-obat-items").html(`
            <tr>
                <td colspan="8" class="px-4 py-6 text-center text-slate-500">
                    Tidak ada detail item return.
                </td>
            </tr>
        `);
            return;
        }

        const rows = details
            .map((item, index) => {
                return `
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3">${index + 1}</td>
                    <td class="px-4 py-3 font-medium text-slate-800">
                        ${item.obat?.nama_obat || "-"}
                    </td>
                    <td class="px-4 py-3">
                        ${item.obat?.kode_obat || "-"}
                    </td>
                    <td class="px-4 py-3">
                        ${item.batch_obat?.nama_batch || "-"}
                    </td>
                    <td class="px-4 py-3">
                        ${formatDateIndonesia(item.batch_obat?.tanggal_kadaluarsa_obat)}
                    </td>
                    <td class="px-4 py-3 text-right">
                        ${formatNumberIndonesia(item.qty || 0)}
                    </td>
                    <td class="px-4 py-3 text-right">
                        ${formatCurrency(item.harga_beli || 0)}
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-slate-800">
                        ${formatCurrency(item.subtotal || 0)}
                    </td>
                </tr>
            `;
            })
            .join("");

        $("#detail-return-obat-items").html(rows);
    }

    function openModalReturn() {
        lockBodyScroll();
        $("#modal-create-return-obat").removeClass("hidden").addClass("flex");
    }

    function closeModalReturn() {
        unlockBodyScroll();
        $("#modal-create-return-obat").removeClass("flex").addClass("hidden");
    }

    $("#button-open-modal-create-return-obat").on("click", function () {
        openModalReturn();
        calculateAllRows();
    });

    $("#button-close-modal-create-return-obat").on("click", function () {
        closeModalReturn();
    });

    $("#button-cancel-modal-create-return-obat").on("click", function () {
        closeModalReturn();
    });

    function isSupplierAndDepotSelected() {
        return !!(
            supplierTom &&
            supplierTom.getValue() &&
            depotTom &&
            depotTom.getValue()
        );
    }

    function buildBatchUrl(obatId) {
        const depotId = depotTom ? depotTom.getValue() : "";
        const supplierId = supplierTom ? supplierTom.getValue() : "";
        const baseUrl = batchUrlTemplate.replace(":obatId", obatId);

        const params = new URLSearchParams();

        if (depotId) {
            params.append("depot_id", depotId);
        }

        if (supplierId) {
            params.append("supplier_id", supplierId);
        }

        const queryString = params.toString();

        return queryString ? `${baseUrl}?${queryString}` : baseUrl;
    }

    function buildStockBatchUrl(batchObatId, depotId, supplierId, obatId) {
        const baseUrl = stockBatchUrlTemplate
            .replace(":batchObatId", batchObatId)
            .replace(":depotId", depotId);

        const params = new URLSearchParams();

        if (supplierId) {
            params.append("supplier_id", supplierId);
        }

        if (obatId) {
            params.append("obat_id", obatId);
        }

        const queryString = params.toString();

        return queryString ? `${baseUrl}?${queryString}` : baseUrl;
    }

    function syncHargaBeliDisplayToHidden($row) {
        const displayValue = $row.find(".detail-harga-beli-display").val();
        const numericValue = parseFormattedNumber(displayValue);

        $row.find(".detail-harga-beli").val(numericValue);
        $row.find(".detail-harga-beli-display").val(
            formatRupiahInput(numericValue),
        );
    }

    function calculateDetailRow(rowElement) {
        const $row = $(rowElement);

        const qty = parseFloat($row.find(".detail-qty").val()) || 0;
        const hargaBeli =
            parseFloat($row.find(".detail-harga-beli").val()) || 0;

        const subtotal = qty * hargaBeli;

        $row.find(".detail-subtotal-display").val(
            formatNumberIndonesia(subtotal),
        );
        $row.find(".detail-subtotal-input").val(subtotal.toFixed(2));
    }

    function calculateGrandTotal() {
        let grandTotal = 0;

        $("#return-detail-container .detail-row").each(function () {
            const totalPerRow =
                parseFloat($(this).find(".detail-subtotal-input").val()) || 0;
            grandTotal += totalPerRow;
        });

        $("#grand-total-display").val(formatNumberIndonesia(grandTotal));
        $("#grand-total-input").val(grandTotal.toFixed(2));
    }

    function calculateAllRows() {
        $("#return-detail-container .detail-row").each(function () {
            const $row = $(this);
            syncHargaBeliDisplayToHidden($row);
            calculateDetailRow(this);
        });

        calculateGrandTotal();
    }

    function resetRowComputedFields($row) {
        $row.find(".detail-harga-beli").val("0");
        $row.find(".detail-harga-beli-display").val("0");
        $row.find(".detail-subtotal-display").val("0");
        $row.find(".detail-subtotal-input").val("0");
    }

    function resetBatchFields($row) {
        $row.find(".batch-obat-id-input").val("");
        $row.find(".batch-obat-nama-input").val("");
        $row.find(".tanggal-kadaluarsa-input").val("");
        $row.find(".stok-tersedia-input").val("0");
        $row.find(".stok-tersedia-display").val("0");
        $row.find(".detail-qty").val("1");
        resetRowComputedFields($row);
    }

    function applyBatchMetaToRow($row, data) {
        $row.find(".batch-obat-id-input").val(
            data.id || data.batch_obat_id || "",
        );
        $row.find(".batch-obat-nama-input").val(data.nama_batch || "");
        $row.find(".tanggal-kadaluarsa-input").val(
            data.tanggal_kadaluarsa_obat || "",
        );
    }

    function applyStockAndHargaToRow($row, data) {
        const stok = Number(data.stok_obat ?? data.stok_tersedia ?? 0);
        const hargaBeli = Number(data.harga_beli ?? 0);

        $row.find(".stok-tersedia-input").val(stok);
        $row.find(".stok-tersedia-display").val(stok);
        $row.find(".detail-harga-beli").val(hargaBeli);
        $row.find(".detail-harga-beli-display").val(
            formatRupiahInput(hargaBeli),
        );

        calculateDetailRow($row);
        calculateGrandTotal();
    }

    function fetchStockAndHargaByBatch(batchObatId, $row) {
        const depotId = depotTom ? depotTom.getValue() : "";
        const supplierId = supplierTom ? supplierTom.getValue() : "";

        const obatSelectEl = $row.find(".obat-select")[0];
        const obatId =
            obatSelectEl && obatSelectEl.tomselect
                ? obatSelectEl.tomselect.getValue()
                : "";

        if (!batchObatId || !depotId || !supplierId || !obatId) {
            resetBatchFields($row);
            calculateDetailRow($row);
            calculateGrandTotal();
            return;
        }

        $.ajax({
            url: buildStockBatchUrl(batchObatId, depotId, supplierId, obatId),
            type: "GET",
            dataType: "json",
            success: function (response) {
                applyBatchMetaToRow($row, response);
                applyStockAndHargaToRow($row, response);
            },
            error: function () {
                showErrorMessage(
                    "Gagal mengambil stok batch obat pada depot dan harga beli.",
                );
                resetBatchFields($row);
                calculateDetailRow($row);
                calculateGrandTotal();
            },
        });
    }

    function resetDetailRowSelection($row) {
        const obatSelectEl = $row.find(".obat-select")[0];
        const batchSelectEl = $row.find(".batch-obat-select")[0];

        if (obatSelectEl && obatSelectEl.tomselect) {
            obatSelectEl.tomselect.clear(true);
            obatSelectEl.tomselect.clearOptions();
        }

        if (batchSelectEl && batchSelectEl.tomselect) {
            batchSelectEl.tomselect.clear(true);
            batchSelectEl.tomselect.clearOptions();
            batchSelectEl.tomselect.addOption({
                value: "",
                text: "Pilih batch obat",
            });
            batchSelectEl.tomselect.refreshOptions(false);
            batchSelectEl.tomselect.disable();
        }

        resetBatchFields($row);
        calculateDetailRow($row);
    }

    function resetAllDetailRows() {
        $("#return-detail-container .detail-row").each(function () {
            resetDetailRowSelection($(this));
        });

        calculateGrandTotal();
    }

    function toggleDetailObatState() {
        const ready = isSupplierAndDepotSelected();

        $("#return-detail-container .detail-row").each(function () {
            const obatSelectEl = $(this).find(".obat-select")[0];
            const batchSelectEl = $(this).find(".batch-obat-select")[0];

            if (obatSelectEl && obatSelectEl.tomselect) {
                if (ready) {
                    obatSelectEl.tomselect.enable();
                } else {
                    obatSelectEl.tomselect.disable();
                }
            }

            if (batchSelectEl && batchSelectEl.tomselect) {
                batchSelectEl.tomselect.disable();
            }
        });
    }

    function reindexDetailRows() {
        $("#return-detail-container .detail-row").each(function (index) {
            $(this)
                .find("[name]")
                .each(function () {
                    const currentName = $(this).attr("name");
                    if (!currentName) return;

                    const newName = currentName.replace(
                        /details\[\d+\]/g,
                        `details[${index}]`,
                    );

                    $(this).attr("name", newName);
                });
        });
    }

    if (document.getElementById("supplier_id")) {
        supplierTom = new TomSelect("#supplier_id", {
            valueField: "id",
            labelField: "nama_supplier",
            searchField: ["nama_supplier"],
            create: false,
            preload: true,
            placeholder: "Pilih Supplier",
            maxOptions: 20,
            load: function (query, callback) {
                $.ajax({
                    url: getSupplierUrl,
                    type: "GET",
                    dataType: "json",
                    data: { q: query },
                    success: function (res) {
                        callback(normalizeResponseArray(res));
                    },
                    error: function () {
                        callback();
                    },
                });
            },
            render: {
                option: function (item, escape) {
                    return `
                        <div class="py-2">
                            <div class="font-medium">${escape(item.nama_supplier || "")}</div>
                            ${
                                item.kontak_person
                                    ? `<div class="text-xs text-slate-500">${escape(item.kontak_person)}</div>`
                                    : ""
                            }
                        </div>
                    `;
                },
                item: function (item, escape) {
                    return `<div>${escape(item.nama_supplier || "")}</div>`;
                },
            },
            onChange: function (value) {
                if (depotTom) {
                    depotTom.clear(true);
                    depotTom.clearOptions();

                    if (value) {
                        depotTom.enable();
                        depotTom.load("");
                    } else {
                        depotTom.disable();
                    }
                }

                resetAllDetailRows();
                toggleDetailObatState();
            },
        });
    }

    if (document.getElementById("depot_id")) {
        depotTom = new TomSelect("#depot_id", {
            valueField: "id",
            labelField: "nama_depot",
            searchField: ["nama_depot"],
            create: false,
            preload: false,
            placeholder: "Pilih Depot",
            maxOptions: 20,
            load: function (query, callback) {
                const supplierId = supplierTom ? supplierTom.getValue() : null;

                if (!supplierId) {
                    callback();
                    return;
                }

                $.ajax({
                    url: getDepotBySupplierUrl,
                    type: "GET",
                    dataType: "json",
                    data: {
                        q: query,
                        supplier_id: supplierId,
                    },
                    success: function (res) {
                        callback(normalizeResponseArray(res));
                    },
                    error: function () {
                        callback();
                    },
                });
            },
            render: {
                option: function (item, escape) {
                    return `
                        <div class="py-2">
                            <div class="font-medium">${escape(item.nama_depot || "")}</div>
                        </div>
                    `;
                },
                item: function (item, escape) {
                    return `<div>${escape(item.nama_depot || "")}</div>`;
                },
            },
            onChange: function () {
                resetAllDetailRows();
                toggleDetailObatState();

                $("#return-detail-container .detail-row").each(function () {
                    const obatSelectEl = $(this).find(".obat-select")[0];

                    if (obatSelectEl && obatSelectEl.tomselect) {
                        obatSelectEl.tomselect.clear(true);
                        obatSelectEl.tomselect.clearOptions();

                        if (supplierTom?.getValue() && depotTom?.getValue()) {
                            obatSelectEl.tomselect.enable();
                            obatSelectEl.tomselect.load("");
                        } else {
                            obatSelectEl.tomselect.disable();
                        }
                    }
                });
            },
        });

        depotTom.disable();
    }

    function initDetailRow(row) {
        const obatSelectEl = row.querySelector(".obat-select");
        const batchSelectEl = row.querySelector(".batch-obat-select");

        if (!obatSelectEl || !batchSelectEl) return;

        if (!obatSelectEl.tomselect) {
            new TomSelect(obatSelectEl, {
                valueField: "id",
                labelField: "nama_obat",
                searchField: ["nama_obat", "kode_obat"],
                create: false,
                preload: false,
                placeholder: "Pilih Obat",
                maxOptions: 20,
                load: function (query, callback) {
                    const supplierId = supplierTom
                        ? supplierTom.getValue()
                        : null;
                    const depotId = depotTom ? depotTom.getValue() : null;

                    if (!supplierId || !depotId) {
                        callback();
                        return;
                    }

                    $.ajax({
                        url: getObatBySupplierDepotUrl,
                        type: "GET",
                        dataType: "json",
                        data: {
                            q: query,
                            supplier_id: supplierId,
                            depot_id: depotId,
                        },
                        success: function (res) {
                            callback(normalizeResponseArray(res));
                        },
                        error: function () {
                            callback();
                        },
                    });
                },
                render: {
                    option: function (item, escape) {
                        return `
                            <div class="py-2">
                                <div class="font-medium">${escape(item.nama_obat || "")}</div>
                                ${
                                    item.kode_obat
                                        ? `<div class="text-xs text-slate-500">${escape(item.kode_obat)}</div>`
                                        : ""
                                }
                            </div>
                        `;
                    },
                    item: function (item, escape) {
                        return `<div>${escape(item.nama_obat || "")}</div>`;
                    },
                },
            });
        }

        if (!batchSelectEl.tomselect) {
            new TomSelect(batchSelectEl, {
                valueField: "value",
                labelField: "text",
                searchField: ["text", "nama_batch"],
                create: false,
                persist: false,
                placeholder: "Pilih batch obat",
                allowEmptyOption: true,
                maxOptions: 100,
                render: {
                    option: function (data, escape) {
                        const exp = data.tanggal_kadaluarsa_obat
                            ? `<div class="text-xs text-slate-500">EXP: ${escape(data.tanggal_kadaluarsa_obat)}</div>`
                            : "";

                        return `
                            <div class="py-2">
                                <div class="font-medium">${escape(data.nama_batch || data.text || "")}</div>
                                ${exp}
                            </div>
                        `;
                    },
                    item: function (data, escape) {
                        return `<div>${escape(data.nama_batch || data.text || "")}</div>`;
                    },
                },
            });
        }

        const obatTom = obatSelectEl.tomselect;
        const batchTom = batchSelectEl.tomselect;
        const $row = $(row);

        if (isSupplierAndDepotSelected()) {
            obatTom.enable();
        } else {
            obatTom.disable();
        }

        batchTom.disable();

        function loadBatchByObat(obatId) {
            resetBatchFields($row);

            batchTom.clear(true);
            batchTom.clearOptions();
            batchTom.addOption({
                value: "",
                text: "Pilih batch obat",
            });
            batchTom.refreshOptions(false);

            if (!obatId) {
                batchTom.disable();
                return;
            }

            $.ajax({
                url: buildBatchUrl(obatId),
                type: "GET",
                dataType: "json",
                beforeSend: function () {
                    batchTom.disable();
                    batchTom.clear(true);
                    batchTom.clearOptions();
                    batchTom.addOption({
                        value: "",
                        text: "Memuat batch...",
                    });
                    batchTom.refreshOptions(false);
                },
                success: function (response) {
                    batchTom.clear(true);
                    batchTom.clearOptions();
                    batchTom.addOption({
                        value: "",
                        text: "Pilih batch obat",
                    });

                    const batches = normalizeResponseArray(response);

                    batches.forEach((item) => {
                        batchTom.addOption({
                            value: String(item.value ?? item.id ?? ""),
                            text: item.text ?? item.nama_batch ?? "",
                            id: String(item.id ?? item.value ?? ""),
                            nama_batch: item.nama_batch ?? "",
                            tanggal_kadaluarsa_obat:
                                item.tanggal_kadaluarsa_obat ?? "",
                            stok_tersedia: Number(item.stok_tersedia ?? 0),
                            harga_beli: Number(item.harga_beli ?? 0),
                        });
                    });

                    if (batches.length > 0) {
                        batchTom.enable();
                        batchTom.refreshOptions(true);

                        setTimeout(() => {
                            batchTom.open();
                        }, 0);
                    } else {
                        batchTom.disable();
                        batchTom.refreshOptions(false);
                    }
                },
                error: function () {
                    batchTom.clear(true);
                    batchTom.clearOptions();
                    batchTom.addOption({
                        value: "",
                        text: "Gagal memuat batch",
                    });
                    batchTom.refreshOptions(false);
                    batchTom.disable();
                },
            });
        }

        obatTom.off("change");
        obatTom.on("change", function (value) {
            loadBatchByObat(value);
        });

        batchTom.off("change");
        batchTom.on("change", function (value) {
            if (!value) {
                resetBatchFields($row);
                calculateDetailRow($row);
                calculateGrandTotal();
                return;
            }

            const selected = batchTom.options[value];

            if (!selected) {
                resetBatchFields($row);
                calculateDetailRow($row);
                calculateGrandTotal();
                return;
            }

            applyBatchMetaToRow($row, selected);
            fetchStockAndHargaByBatch(value, $row);
        });
    }

    $("#return-detail-container .detail-row").each(function () {
        initDetailRow(this);
    });

    toggleDetailObatState();
    calculateAllRows();
    reindexDetailRows();

    $("#button-add-detail-row").on("click", function () {
        const container = document.getElementById("return-detail-container");
        const template = document.getElementById(
            "template-detail-row-return-obat",
        );

        const index = container.querySelectorAll(".detail-row").length;
        const html = template.innerHTML.replaceAll("__INDEX__", index);

        const wrapper = document.createElement("div");
        wrapper.innerHTML = html.trim();

        const newRow = wrapper.firstElementChild;
        container.appendChild(newRow);

        initDetailRow(newRow);
        toggleDetailObatState();

        const $newRow = $(newRow);
        syncHargaBeliDisplayToHidden($newRow);
        calculateDetailRow(newRow);
        calculateGrandTotal();
        reindexDetailRows();
    });

    $(document).on("click", ".button-remove-detail-row", function () {
        const totalRows = $("#return-detail-container .detail-row").length;

        if (totalRows <= 1) {
            showWarningMessage("Minimal harus ada 1 item detail.");
            return;
        }

        $(this).closest(".detail-row").remove();
        reindexDetailRows();
        calculateGrandTotal();
    });

    $(document).on("input", ".detail-harga-beli-display", function () {
        const $row = $(this).closest(".detail-row");
        const rawValue = parseFormattedNumber($(this).val());

        $row.find(".detail-harga-beli").val(rawValue);
        $(this).val(formatRupiahInput(rawValue));

        calculateDetailRow($row);
        calculateGrandTotal();
    });

    $(document).on("input change", ".detail-qty", function () {
        const $row = $(this).closest(".detail-row");
        const qty = parseFloat($row.find(".detail-qty").val()) || 0;
        const stokTersedia =
            parseFloat($row.find(".stok-tersedia-input").val()) || 0;

        if (stokTersedia > 0 && qty > stokTersedia) {
            $row.find(".detail-qty").val(stokTersedia);
        }

        if (qty < 1) {
            $row.find(".detail-qty").val(1);
        }

        calculateDetailRow($row);
        calculateGrandTotal();
    });

    $("#form-create-return-obat").on("submit", function (e) {
        e.preventDefault();

        const $form = $(this);
        const $button = $form.find('button[type="submit"]');

        calculateAllRows();

        $.ajax({
            url: createReturnObatUrl,
            type: "POST",
            data: $form.serialize(),
            dataType: "json",
            beforeSend: function () {
                $button.prop("disabled", true).html(`
                    <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                    Menyimpan...
                `);
            },
            success: function (response) {
                closeModalReturn();

                const form = $("#form-create-return-obat")[0];
                if (form) {
                    form.reset();
                }

                if (supplierTom) {
                    supplierTom.clear(true);
                }

                if (depotTom) {
                    depotTom.clear(true);
                    depotTom.clearOptions();
                    depotTom.disable();
                }

                $("#return-detail-container .detail-row").each(
                    function (index) {
                        const $row = $(this);

                        if (index === 0) {
                            resetDetailRowSelection($row);
                        } else {
                            $row.remove();
                        }
                    },
                );

                reindexDetailRows();
                toggleDetailObatState();
                calculateAllRows();

                if (window.tableReturnObat) {
                    window.tableReturnObat.ajax.reload(null, false);
                }

                showSuccessMessage(
                    response.message || "Data return obat berhasil disimpan.",
                );
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const errors = xhr.responseJSON.errors;
                    const errorMessages = [];

                    Object.keys(errors).forEach(function (key) {
                        errorMessages.push(errors[key][0]);
                    });

                    showValidationMessage(errorMessages);
                    return;
                }

                showErrorMessage(
                    xhr.responseJSON?.message ||
                        "Terjadi kesalahan saat menyimpan data return obat.",
                );
            },
            complete: function () {
                $button.prop("disabled", false).html(`
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    Simpan Return
                `);
            },
        });
    });

    $(document).on(
        "click",
        ".button-open-modal-detail-return-obat",
        function () {
            const kodeReturn = $(this).data("kode-return");

            if (!kodeReturn) {
                showWarningMessage("Kode return tidak ditemukan.");
                return;
            }

            resetDetailModalContent();
            openModalDetailReturn();

            $("#detail-return-obat-items").html(`
            <tr>
                <td colspan="8" class="px-4 py-6 text-center text-slate-500">
                    Memuat data detail return obat...
                </td>
            </tr>
        `);

            $.ajax({
                url: buildDetailReturnUrl(kodeReturn),
                type: "GET",
                dataType: "json",
                success: function (response) {
                    console.log(response.data);
                    renderDetailReturnModal(response.data || {});
                },
                error: function (xhr) {
                    closeModalDetailReturn();

                    showErrorMessage(
                        xhr.responseJSON?.message ||
                            "Gagal mengambil detail return obat.",
                    );
                },
            });
        },
    );

    $("#button-close-modal-detail-return-obat").on("click", function () {
        unlockBodyScroll();
        closeModalDetailReturn();
    });

    $("#button-close-footer-modal-detail-return-obat").on("click", function () {
        unlockBodyScroll();
        closeModalDetailReturn();
    });
});
