import $ from "jquery";
import axios from "axios";

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

function showSuccessAlert(message) {
    return Swal.fire({
        icon: "success",
        title: "Berhasil",
        text: message,
        confirmButtonText: "OK",
    });
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

function showValidationAlert(messages = []) {
    return Swal.fire({
        icon: "warning",
        title: "Validasi Gagal",
        html: `
            <div style="text-align:left;">
                <ul style="margin:0; padding-left:18px;">
                    ${messages.map((msg) => `<li>${msg}</li>`).join("")}
                </ul>
            </div>
        `,
        confirmButtonText: "OK",
    });
}

$(function () {
    window.tableRestockBahanHabisPakai = $(
        "#table-restock-bahan-habis-pakai",
    ).DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/farmasi/restock-bahan-habis-pakai/get-data-restock-bahan-habis-pakai",
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

    $("#restock-bhp-search-input").on("keyup", function () {
        window.tableRestockBahanHabisPakai.search(this.value).draw();
    });

    const $info = $("#restock-bhp-custom-info");
    const $pagination = $("#restock-bhp-custom-pagination");
    const $perPage = $("#restock-bhp-page-length");

    function updatePagination() {
        const info = window.tableRestockBahanHabisPakai.page.info();
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
            window.tableRestockBahanHabisPakai.page("previous").draw("page");
        } else if ($link.attr("id") === "btnNext") {
            window.tableRestockBahanHabisPakai.page("next").draw("page");
        } else if ($link.hasClass("page-number")) {
            window.tableRestockBahanHabisPakai
                .page(parseInt($link.data("page")) - 1)
                .draw("page");
        }
    });

    $perPage.on("change", function () {
        window.tableRestockBahanHabisPakai.page
            .len(parseInt($(this).val(), 10))
            .draw();
    });

    window.tableRestockBahanHabisPakai.on("draw", updatePagination);
    updatePagination();
});

$(function () {
    let supplierTom = null;
    let depotTom = null;

    const pageEl = document.getElementById("restock-bhp-page");
    const batchUrlTemplate = pageEl ? pageEl.dataset.batchUrl : "";
    const getSupplierUrl = pageEl ? pageEl.dataset.getSupplierUrl : "";
    const createSupplierUrl = pageEl ? pageEl.dataset.createSupplierUrl : "";
    const createRestockBahanHabisPakaiUrl = pageEl
        ? pageEl.dataset.createRestockBhpUrl
        : "";

    function openModalRestock() {
        lockBodyScroll();
        $("#modal-create-restock-bhp").removeClass("hidden").addClass("flex");
    }

    function closeModalRestock() {
        unlockBodyScroll();
        $("#modal-create-restock-bhp").removeClass("flex").addClass("hidden");
    }

    $("#button-open-modal-create-restock-bhp").on("click", function () {
        openModalRestock();
        calculateAllRows();
    });

    $("#button-close-modal-create-restock-bhp").on("click", function () {
        closeModalRestock();
    });

    $("#button-cancel-modal-create-restock-bhp").on("click", function () {
        closeModalRestock();
    });

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
                        callback(res);
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
        });
    }

    if (document.getElementById("depot_id")) {
        depotTom = new TomSelect("#depot_id", {
            create: false,
            placeholder: "Pilih Depot",
        });
    }

    function buildBatchUrl(bhpId) {
        return batchUrlTemplate.replace(":bhpId", bhpId);
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

    function formatPercentInput(value) {
        return parseFormattedNumber(value);
    }

    function updateDiskonPrefix($row) {
        const diskonType = $row.find(".detail-diskon-type").val() || "";
        const $prefix = $row.find(".detail-diskon-prefix");

        $prefix.text(diskonType === "persen" ? "%" : "Rp");
    }

    function syncHargaBeliDisplayToHidden($row) {
        const displayValue = $row.find(".detail-harga-beli-display").val();
        const numericValue = parseFormattedNumber(displayValue);

        $row.find(".detail-harga-beli").val(numericValue);
        $row.find(".detail-harga-beli-display").val(
            formatRupiahInput(numericValue),
        );
    }

    function syncDiskonDisplayToHidden($row) {
        const diskonType = $row.find(".detail-diskon-type").val() || "";
        const displayValue = $row.find(".detail-diskon-value-display").val();
        const numericValue = parseFormattedNumber(displayValue);

        $row.find(".detail-diskon-value").val(numericValue);

        if (diskonType === "persen") {
            $row.find(".detail-diskon-value-display").val(
                formatPercentInput(numericValue),
            );
        } else {
            $row.find(".detail-diskon-value-display").val(
                formatRupiahInput(numericValue),
            );
        }
    }

    function calculateDetailRow(rowElement) {
        const $row = $(rowElement);

        const qty = parseFloat($row.find(".detail-qty").val()) || 0;
        const hargaBeli =
            parseFloat($row.find(".detail-harga-beli").val()) || 0;
        const diskonType = $row.find(".detail-diskon-type").val() || "";
        const diskonValue =
            parseFloat($row.find(".detail-diskon-value").val()) || 0;

        const subtotal = qty * hargaBeli;
        let diskonAmount = 0;

        if (diskonType === "nominal") {
            diskonAmount = diskonValue;
        } else if (diskonType === "persen") {
            diskonAmount = subtotal * (diskonValue / 100);
        }

        if (diskonAmount > subtotal) {
            diskonAmount = subtotal;
        }

        const totalSetelahDiskon = subtotal - diskonAmount;

        $row.find(".detail-subtotal-display").val(
            formatNumberIndonesia(subtotal),
        );
        $row.find(".detail-subtotal-input").val(subtotal.toFixed(2));

        $row.find(".detail-diskon-amount-display").val(
            formatNumberIndonesia(diskonAmount),
        );
        $row.find(".detail-diskon-amount-input").val(diskonAmount.toFixed(2));

        $row.find(".detail-total-display").val(
            formatNumberIndonesia(totalSetelahDiskon),
        );
        $row.find(".detail-total-input").val(totalSetelahDiskon.toFixed(2));
    }

    function calculateGrandTotal() {
        let grandTotal = 0;

        $("#restock-detail-container .detail-row").each(function () {
            const totalPerRow =
                parseFloat($(this).find(".detail-total-input").val()) || 0;
            grandTotal += totalPerRow;
        });

        $("#grand-total-display").val(formatNumberIndonesia(grandTotal));
        $("#grand-total-input").val(grandTotal.toFixed(2));
    }

    function calculateAllRows() {
        $("#restock-detail-container .detail-row").each(function () {
            const $row = $(this);
            updateDiskonPrefix($row);
            syncHargaBeliDisplayToHidden($row);
            syncDiskonDisplayToHidden($row);
            calculateDetailRow(this);
        });

        calculateGrandTotal();
    }

    function resetBatchFields($row) {
        $row.find(".batch-bhp-id-input").val("");
        $row.find(".batch-bhp-nama-input").val("");
        $row.find(".tanggal-kadaluarsa-input").val("");
    }

    function setExistingBatchToRow($row, data) {
        $row.find(".batch-bhp-id-input").val(data.id || "");
        $row.find(".batch-bhp-nama-input").val(data.nama_batch || "");
        $row.find(".tanggal-kadaluarsa-input").val(
            data.tanggal_kadaluarsa_bahan_habis_pakai || "",
        );
    }

    function setNewBatchToRow($row, batchName) {
        $row.find(".batch-bhp-id-input").val("");
        $row.find(".batch-bhp-nama-input").val(batchName || "");
    }

    function initDetailRow(row) {
        const bhpSelectEl = row.querySelector(".bhp-select");
        const batchSelectEl = row.querySelector(".batch-bhp-select");

        if (!bhpSelectEl || !batchSelectEl) return;

        if (!bhpSelectEl.tomselect) {
            new TomSelect(bhpSelectEl, {
                create: false,
                placeholder: "Pilih Bahan Habis Pakai",
            });
        }

        if (!batchSelectEl.tomselect) {
            new TomSelect(batchSelectEl, {
                create: function (input) {
                    return {
                        value: `new::${input}`,
                        text: `${input} (Batch Baru)`,
                        id: "",
                        nama_batch: input,
                        tanggal_kadaluarsa_bahan_habis_pakai: "",
                        is_new: true,
                    };
                },
                persist: false,
                placeholder: "Pilih / ketik batch baru",
                allowEmptyOption: true,
                createOnBlur: true,
                maxOptions: 100,
                render: {
                    option: function (data, escape) {
                        const badge = data.is_new
                            ? `<span class="ml-2 inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">Baru</span>`
                            : "";

                        const exp = data.tanggal_kadaluarsa_bahan_habis_pakai
                            ? `<div class="text-xs text-slate-500">EXP: ${escape(data.tanggal_kadaluarsa_bahan_habis_pakai)}</div>`
                            : "";

                        return `
                            <div class="py-2">
                                <div class="font-medium">
                                    ${escape(data.nama_batch || "")}
                                    ${badge}
                                </div>
                                ${exp}
                            </div>
                        `;
                    },
                    item: function (data, escape) {
                        return `<div>${escape(data.nama_batch || "")}</div>`;
                    },
                },
                createFilter: function (input) {
                    const normalizedInput = String(input || "")
                        .trim()
                        .toLowerCase();

                    if (!normalizedInput) return false;

                    let exists = false;
                    Object.keys(this.options).forEach((key) => {
                        const option = this.options[key];
                        const optionName = String(
                            option.nama_batch || option.text || "",
                        )
                            .trim()
                            .toLowerCase();

                        if (optionName === normalizedInput && !option.is_new) {
                            exists = true;
                        }
                    });

                    return !exists;
                },
            });
        }

        const bhpTom = bhpSelectEl.tomselect;
        const batchTom = batchSelectEl.tomselect;
        const $row = $(row);

        batchTom.disable();

        function loadBatchByBhp(bhpId) {
            resetBatchFields($row);

            batchTom.clear(true);
            batchTom.clearOptions();
            batchTom.addOption({
                value: "",
                text: "Pilih / ketik batch baru",
                nama_batch: "",
                tanggal_kadaluarsa_bahan_habis_pakai: "",
            });
            batchTom.refreshOptions(false);

            if (!bhpId) {
                batchTom.disable();
                return;
            }

            $.ajax({
                url: buildBatchUrl(bhpId),
                type: "GET",
                dataType: "json",
                beforeSend: function () {
                    batchTom.disable();
                    batchTom.clear(true);
                    batchTom.clearOptions();
                    batchTom.addOption({
                        value: "",
                        text: "Memuat Batch",
                        nama_batch: "",
                        tanggal_kadaluarsa_bahan_habis_pakai: "",
                    });
                    batchTom.refreshOptions(false);
                },
                success: function (response) {
                    batchTom.clear(true);
                    batchTom.clearOptions();
                    batchTom.addOption({
                        value: "",
                        text: "Pilih / ketik batch baru",
                        nama_batch: "",
                        tanggal_kadaluarsa_bahan_habis_pakai: "",
                    });

                    if (Array.isArray(response) && response.length > 0) {
                        response.forEach((item) => {
                            batchTom.addOption({
                                value: String(item.id),
                                text: item.text,
                                id: String(item.id),
                                nama_batch: item.nama_batch,
                                tanggal_kadaluarsa_bahan_habis_pakai:
                                    item.tanggal_kadaluarsa_bahan_habis_pakai,
                                is_new: false,
                            });
                        });
                    }

                    batchTom.enable();
                    batchTom.refreshOptions(false);
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

        bhpTom.on("change", function (value) {
            loadBatchByBhp(value);
        });

        batchTom.on("change", function (value) {
            if (!value) {
                resetBatchFields($row);
                return;
            }

            const selected = batchTom.options[value];

            if (!selected) {
                resetBatchFields($row);
                return;
            }

            if (String(value).startsWith("new::") || selected.is_new) {
                setNewBatchToRow($row, selected.nama_batch || "");
                return;
            }

            setExistingBatchToRow($row, selected);
        });
    }

    $("#restock-detail-container .detail-row").each(function () {
        initDetailRow(this);
    });

    calculateAllRows();

    $("#button-add-detail-row").on("click", function () {
        const container = document.getElementById("restock-detail-container");
        const template = document.getElementById(
            "template-detail-row-restock-bhp",
        );

        const index = container.querySelectorAll(".detail-row").length;
        const html = template.innerHTML.replaceAll("__INDEX__", index);

        const wrapper = document.createElement("div");
        wrapper.innerHTML = html.trim();

        const newRow = wrapper.firstElementChild;
        container.appendChild(newRow);

        initDetailRow(newRow);

        const $newRow = $(newRow);
        updateDiskonPrefix($newRow);
        syncHargaBeliDisplayToHidden($newRow);
        syncDiskonDisplayToHidden($newRow);
        calculateDetailRow(newRow);
        calculateGrandTotal();
    });

    $(document).on("click", ".button-remove-detail-row", function () {
        const totalRows = $("#restock-detail-container .detail-row").length;

        if (totalRows <= 1) {
            showWarningAlert("Minimal harus ada 1 item detail");
            return;
        }

        $(this).closest(".detail-row").remove();
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

    $(document).on("input", ".detail-diskon-value-display", function () {
        const $row = $(this).closest(".detail-row");
        const diskonType = $row.find(".detail-diskon-type").val() || "";
        const rawValue = parseFormattedNumber($(this).val());

        $row.find(".detail-diskon-value").val(rawValue);

        if (diskonType === "persen") {
            $(this).val(rawValue);
        } else {
            $(this).val(formatRupiahInput(rawValue));
        }

        calculateDetailRow($row);
        calculateGrandTotal();
    });

    $(document).on("change", ".detail-diskon-type", function () {
        const $row = $(this).closest(".detail-row");
        updateDiskonPrefix($row);
        syncDiskonDisplayToHidden($row);
        calculateDetailRow($row);
        calculateGrandTotal();
    });

    $(document).on("input change", ".detail-qty", function () {
        const $row = $(this).closest(".detail-row");
        calculateDetailRow($row);
        calculateGrandTotal();
    });

    function clearSupplierFormErrors() {
        $("#form-create-supplier .error-text").each(function () {
            $(this).text("").addClass("hidden");
        });
    }

    function showSupplierFormErrors(errors = {}) {
        Object.keys(errors).forEach(function (key) {
            const $errorEl = $(
                `#form-create-supplier .error-text[data-error-for="${key}"]`,
            );

            if ($errorEl.length) {
                $errorEl.text(errors[key][0]).removeClass("hidden");
            }
        });
    }

    function resetSupplierForm() {
        const form = $("#form-create-supplier")[0];
        if (form) {
            form.reset();
        }
        clearSupplierFormErrors();
    }

    function openCreateSupplierModal() {
        lockBodyScroll();
        $("#modal-create-supplier").removeClass("hidden").addClass("flex");
    }

    function closeCreateSupplierModal() {
        $("#modal-create-supplier").removeClass("flex").addClass("hidden");
        resetSupplierForm();
        unlockBodyScroll();
    }

    $("#button-open-modal-create-supplier").on("click", function () {
        openCreateSupplierModal();
    });

    $(
        "#button-close-modal-create-supplier, #button-cancel-modal-create-supplier",
    ).on("click", function () {
        closeCreateSupplierModal();
    });

    $("#form-create-supplier").on("submit", function (e) {
        e.preventDefault();

        clearSupplierFormErrors();

        const $button = $("#button-submit-create-supplier");
        const formData = $(this).serialize();

        $.ajax({
            url: createSupplierUrl,
            type: "POST",
            data: formData,
            dataType: "json",
            beforeSend: function () {
                $button.prop("disabled", true).html(`
                    <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                    Menyimpan...
                `);
            },
            success: function (response) {
                if (supplierTom) {
                    supplierTom.addOption(response);
                    supplierTom.addItem(String(response.id));
                    supplierTom.refreshOptions(false);
                } else {
                    const option = new Option(
                        response.nama_supplier,
                        response.id,
                        true,
                        true,
                    );
                    $("#supplier_id").append(option).trigger("change");
                }

                closeCreateSupplierModal();
                showSuccessAlert(
                    response.message || "Data supplier berhasil disimpan.",
                );
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    showSupplierFormErrors(xhr.responseJSON.errors);
                    return;
                }

                showErrorAlert(
                    "Terjadi kesalahan saat menyimpan data supplier.",
                );
            },
            complete: function () {
                $button.prop("disabled", false).html(`
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    Simpan Supplier
                `);
            },
        });
    });

    $("#form-create-restock-bhp").on("submit", function (e) {
        e.preventDefault();

        const $form = $(this);
        const $button = $form.find('button[type="submit"]');

        calculateAllRows();

        const formData = $form.serialize();

        $.ajax({
            url: createRestockBahanHabisPakaiUrl,
            type: "POST",
            data: formData,
            dataType: "json",
            beforeSend: function () {
                $button.prop("disabled", true).html(`
                    <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                    Menyimpan...
                `);
            },
            success: async function (response) {
                closeModalRestock();

                const form = $("#form-create-restock-bhp")[0];
                if (form) {
                    form.reset();
                }

                if (window.tableRestockBahanHabisPakai) {
                    window.tableRestockBahanHabisPakai.ajax.reload(null, false);
                }

                if (window.tableRiwayatRestockObat) {
                    window.tableRiwayatRestockObat.ajax.reload(null, false);
                }

                await showSuccessAlert(
                    response.message || "Data restock berhasil disimpan.",
                );
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const errors = xhr.responseJSON.errors;
                    const errorMessages = [];

                    Object.keys(errors).forEach(function (key) {
                        errorMessages.push(errors[key][0]);
                    });

                    showValidationAlert(errorMessages);
                    return;
                }

                showErrorAlert(
                    xhr.responseJSON?.message ||
                        "Terjadi kesalahan saat menyimpan data restock obat.",
                );
            },
            complete: function () {
                $button.prop("disabled", false).html(`
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    Simpan Restock
                `);
            },
        });
    });
});

$(function () {
    const pageEl = document.getElementById("restock-bhp-page");

    const getDataRestockBhpDetailUrl = pageEl
        ? pageEl.dataset.getDataRestockBhpDetailUrl
        : "";

    $(
        "#button-close-modal-detail-restock-bhp, #button-close-footer-modal-detail-restock-bhp",
    ).on("click", function () {
        closeModalDetailRestockBhp();
    });

    function buildDetailRestockBhpUrl(id) {
        return getDataRestockBhpDetailUrl.replace(":id", id);
    }

    function openModalDetailRestockBhp() {
        lockBodyScroll();
        $("#modal-detail-restock-bhp").removeClass("hidden").addClass("flex");
    }

    function closeModalDetailRestockBhp() {
        unlockBodyScroll();
        $("#modal-detail-restock-bhp").removeClass("flex").addClass("hidden");
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

    function resetModalDetailRestockBhp() {
        $("#detail_supplier").text("-");
        $("#detail_depot").text("-");
        $("#detail_no_faktur").text("-");
        $("#detail_tanggal_jatuh_tempo").text("-");
        $("#detail_status_transaksi").text("-");
        $("#detail_total_tagihan").text("Rp 0");

        $("#detail-restock-bhp-tbody").html(`
            <tr>
                <td colspan="10" class="px-4 py-4 text-center text-slate-500">
                    Belum ada data
                </td>
            </tr>
        `);
    }

    function fillModalDetailRestockBhp(data) {
        $("#detail_supplier").text(data.supplier?.nama_supplier || "-");
        $("#detail_depot").text(data.depot?.nama_depot || "-");
        $("#detail_no_faktur").text(data.no_faktur || "-");
        $("#detail_tanggal_jatuh_tempo").text(
            formatDateIndonesia(data.tanggal_jatuh_tempo),
        );
        $("#detail_status_transaksi").text(data.status_restock || "-");
        $("#detail_total_tagihan").text(formatRupiah(data.total_tagihan));

        const details = Array.isArray(data.restock_bahan_habis_pakai_detail)
            ? data.restock_bahan_habis_pakai_detail
            : [];

        if (details.length < 1) {
            $("#detail-restock-bhp-tbody").html(`
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
                <td class="px-4 py-3">${formatDateIndonesia(detail.batch_bahan_habis_pakai?.tanggal_kadaluarsa_bahan_habis_pakai) || "-"}</td>
                <td class="px-4 py-3">${detail.qty || 0}</td>
                <td class="px-4 py-3">${formatRupiah(detail.harga_beli)}</td>
                <td class="px-4 py-3">${formatRupiah(detail.subtotal)}</td>
                <td class="px-4 py-3">${renderDiskon(detail)}</td>
                <td class="px-4 py-3">${formatRupiah(detail.diskon_amount)}</td>
                <td class="px-4 py-3 font-semibold text-emerald-600">${formatRupiah(detail.total_setelah_diskon)}</td>
            </tr>
        `;
        });

        $("#detail-restock-bhp-tbody").html(html);
    }

    $(document).on("click", ".button-detail-restock-bhp", function () {
        const id = $(this).data("id");

        if (!id) {
            showWarningAlert("ID restock bahan habis pakai tidak ditemukan.");
            return;
        }

        resetModalDetailRestockBhp();
        openModalDetailRestockBhp();

        $.ajax({
            url: buildDetailRestockBhpUrl(id),
            type: "GET",
            dataType: "json",
            beforeSend: function () {
                $("#detail-restock-bhp-tbody").html(`
                    <tr>
                        <td colspan="10" class="px-4 py-4 text-center text-slate-500">
                            Memuat data...
                        </td>
                    </tr>
                `);
            },
            success: function (response) {
                fillModalDetailRestockBhp(response.data);
            },
            error: function (xhr) {
                closeModalDetailRestockBhp();
                showErrorAlert(
                    xhr.responseJSON?.message ||
                        "Terjadi kesalahan saat mengambil detail restock obat.",
                );
            },
        });
    });
});

$(function () {
    $(document).on("click", ".button-cancel-restock-bhp", function () {
        const id = $(this).data("id");
        const noFaktur = $(this).data("noFaktur");

        Swal.fire({
            title: "Batalkan restock Bahan Habis Pakai?",
            html: `
                <div class="text-sm text-slate-600">
                    Data restock dengan no faktur <b>${noFaktur ?? "-"}</b> akan diubah menjadi <b>Canceled</b>.
                    <br><br>
                    Tindakan ini menan7dakan bahwa pemesanan restock dibatalkan.
                </div>
            `,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, batalkan",
            cancelButtonText: "Tutup",
            reverseButtons: true,
            customClass: {
                confirmButton: "swal2-confirm",
                cancelButton: "swal2-cancel",
            },
        }).then((result) => {
            if (!result.isConfirmed) return;

            $.ajax({
                url: `/farmasi/restock-bahan-habis-pakai/cancel/${noFaktur}`,
                type: "POST",
                data: {
                    _token: $('meta[name="csrf-token"]').attr("content"),
                },
                beforeSend: function () {
                    Swal.fire({
                        title: "Memproses...",
                        text: "Sedang membatalkan data restock obat.",
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });
                },
                success: function (response) {
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil",
                        text: response.message,
                        showConfirmButton: false,
                        timer: 2000,
                    });

                    if (window.tableRestockBahanHabisPakai) {
                        window.tableRestockBahanHabisPakai.ajax.reload(
                            null,
                            false,
                        );
                    }

                    if (window.tableRiwayatRestockBahanHabisPakai) {
                        window.tableRiwayatRestockBahanHabisPakai.ajax.reload(
                            null,
                            false,
                        );
                    }
                },
                error: function (xhr) {
                    let message =
                        "Terjadi kesalahan saat membatalkan restock obat.";

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
