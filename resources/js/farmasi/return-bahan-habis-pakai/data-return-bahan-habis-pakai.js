import $ from "jquery";
import Swal from "sweetalert2";

function showSuccessMessage(message = "Berhasil.") {
    Swal.fire({
        icon: "success",
        title: "Berhasil",
        text: message,
        confirmButtonText: "Oke",
        confirmButtonColor: "#059669",
    });
}

function showErrorMessage(message = "Terjadi kesalahan.") {
    Swal.fire({
        icon: "error",
        title: "Gagal",
        text: message,
        confirmButtonText: "Tutup",
        confirmButtonColor: "#dc2626",
    });
}

function showValidationMessage(messages = []) {
    const html = messages
        .map((msg) => `<li style="text-align:left;">${msg}</li>`)
        .join("");

    Swal.fire({
        icon: "warning",
        title: "Validasi Gagal",
        html: `<ul style="margin:0; padding-left:18px;">${html}</ul>`,
        confirmButtonText: "Mengerti",
        confirmButtonColor: "#d97706",
    });
}

$(function () {
    window.tableReturnBahanHabisPakai = $("#table-return-bhp").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/farmasi/return-bahan-habis-pakai/get-data-return-bahan-habis-pakai",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "supplier_id", name: "supplier_id" },
            { data: "depot_id", name: "depot_id" },
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
            {
                data: "status_return",
                name: "status_return",
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

    $("#return-bhp-search-input").on("keyup", function () {
        window.tableReturnBahanHabisPakai.search(this.value).draw();
    });

    const $info = $("#return-bhp-custom-info");
    const $pagination = $("#return-bhp-custom-pagination");
    const $perPage = $("#return-bhp-page-length");

    function updatePagination() {
        const info = window.tableReturnBahanHabisPakai.page.info();
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
            window.tableReturnBahanHabisPakai.page("previous").draw("page");
        } else if ($link.attr("id") === "btnNext") {
            window.tableReturnBahanHabisPakai.page("next").draw("page");
        } else if ($link.hasClass("page-number")) {
            window.tableReturnBahanHabisPakai
                .page(parseInt($link.data("page"), 10) - 1)
                .draw("page");
        }
    });

    $perPage.on("change", function () {
        window.tableReturnBahanHabisPakai.page
            .len(parseInt($(this).val(), 10))
            .draw();
    });

    window.tableReturnBahanHabisPakai.on("draw", updatePagination);
    updatePagination();
});

$(function () {
    const $modal = $("#modal-create-return-bhp");
    const $form = $("#formCreateReturnBhp");
    const $detailBody = $("#tableReturnBhpDetailBody");
    const $grandTotalText = $("#grandTotalReturnBhpText");

    let supplierTom = null;
    let depotTom = null;
    let rowIndex = 0;

    function formatRupiah(angka) {
        const number = parseFloat(angka || 0);
        return "Rp " + number.toLocaleString("id-ID");
    }

    function hitungSubtotal($card) {
        const qty = parseFloat($card.find(".input-qty").val()) || 0;
        const harga = parseFloat($card.find(".input-harga-beli").val()) || 0;
        const subtotal = qty * harga;

        $card.find(".input-subtotal").val(subtotal);
        $card.find(".text-subtotal").text(formatRupiah(subtotal));

        hitungGrandTotal();
    }

    function hitungGrandTotal() {
        let total = 0;

        $detailBody.find(".return-item-card").each(function () {
            total += parseFloat($(this).find(".input-subtotal").val()) || 0;
        });

        $grandTotalText.text(formatRupiah(total));
    }

    function resetFormReturnBhp() {
        $form[0].reset();
        $detailBody.empty();
        $grandTotalText.text("Rp 0");
        rowIndex = 0;

        if (supplierTom) {
            supplierTom.clear(true);
            supplierTom.clearOptions();
        }

        if (depotTom) {
            depotTom.clear(true);
            depotTom.clearOptions();
        }
    }

    function openModalCreateReturnBhp() {
        $modal.removeClass("hidden").addClass("flex");
        $("body").addClass("overflow-hidden");
    }

    function closeModalCreateReturnBhp() {
        $modal.addClass("hidden").removeClass("flex");
        $("body").removeClass("overflow-hidden");
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
                    url: "/farmasi/get-data-supplier",
                    type: "GET",
                    dataType: "json",
                    data: { q: query },
                    success: function (res) {
                        callback(res || []);
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
            valueField: "id",
            labelField: "nama_depot",
            searchField: ["nama_depot"],
            create: false,
            placeholder: "Pilih Depot",
            render: {
                option: function (item, escape) {
                    return `<div class="py-2">${escape(item.nama_depot || "")}</div>`;
                },
                item: function (item, escape) {
                    return `<div>${escape(item.nama_depot || "")}</div>`;
                },
            },
        });
    }

    function generateRowDetail(data = {}) {
        const currentIndex = rowIndex++;

        return `
            <div class="return-item-card rounded-[24px] border border-slate-200 bg-slate-50/70 p-4 sm:p-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">

                    <div class="xl:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Bahan Habis Pakai <span class="text-rose-500">*</span></label>
                        <select
                            name="details[${currentIndex}][bahan_habis_pakai_id]"
                            class="select-bhp w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm">
                            <option value="">Pilih Bahan Habis Pakai</option>
                        </select>
                    </div>

                    <div class="xl:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Batch <span class="text-rose-500">*</span></label>
                        <select
                            name="details[${currentIndex}][batch_bahan_habis_pakai_id]"
                            class="select-batch-bhp w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm"
                            disabled>
                            <option value="">Pilih Batch</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Tanggal Kadaluarsa</label>
                        <input type="date"
                            name="details[${currentIndex}][tanggal_kadaluarsa]"
                            class="input-expired-date w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-700 shadow-sm outline-none"
                            readonly
                            value="${data.tanggal_kadaluarsa ?? ""}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Stok Tersedia</label>
                        <input type="text"
                            class="input-stok-bhp w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-700 shadow-sm outline-none"
                            placeholder="0"
                            readonly
                            value="${data.stok_bahan_habis_pakai ?? 0}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Qty Return <span class="text-rose-500">*</span></label>
                        <input type="number"
                            min="1"
                            name="details[${currentIndex}][qty]"
                            class="input-qty w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none"
                            placeholder="0"
                            value="${data.qty ?? ""}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Harga Beli</label>
                        <input type="text"
                            name="details[${currentIndex}][harga_beli_display]"
                            class="input-harga-beli-display w-full rounded-2xl border border-slate-200 bg-slate-100 px-4 py-3 text-sm text-slate-700 shadow-sm outline-none"
                            placeholder="Rp 0"
                            readonly
                            value="${formatRupiah(data.harga_beli ?? 0)}">

                        <input type="hidden"
                            name="details[${currentIndex}][harga_beli]"
                            class="input-harga-beli"
                            value="${data.harga_beli ?? 0}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Subtotal</label>
                        <input type="hidden"
                            name="details[${currentIndex}][subtotal]"
                            class="input-subtotal"
                            value="${data.subtotal ?? 0}">
                        <div class="text-subtotal rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                            ${formatRupiah(data.subtotal ?? 0)}
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Catatan Item</label>
                        <input type="text"
                            name="details[${currentIndex}][keterangan_item]"
                            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 shadow-sm outline-none"
                            placeholder="Opsional"
                            value="${data.keterangan_item ?? ""}">
                    </div>

                    <div class="flex items-end justify-start xl:justify-end">
                        <button type="button"
                            class="btn-remove-row inline-flex items-center justify-center rounded-2xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-100">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    function initBhpSelect(
        $select,
        selectedValue = null,
        selectedLabel = null,
    ) {
        const tom = new TomSelect($select[0], {
            valueField: "id",
            labelField: "nama_barang",
            searchField: ["nama_barang", "kode"],
            create: false,
            preload: true,
            placeholder: "Pilih Bahan Habis Pakai",
            maxOptions: 30,
            load: function (query, callback) {
                $.ajax({
                    url: "/farmasi/return-bahan-habis-pakai/get-data-bahan-habis-pakai",
                    type: "GET",
                    dataType: "json",
                    data: { q: query },
                    success: function (res) {
                        callback(res || []);
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
                            <div class="font-medium">${escape(item.nama_barang || "")}</div>
                            ${
                                item.kode
                                    ? `<div class="text-xs text-slate-500">${escape(item.kode)}</div>`
                                    : ""
                            }
                        </div>
                    `;
                },
                item: function (item, escape) {
                    return `<div>${escape(item.nama_barang || "")}</div>`;
                },
            },
        });

        if (selectedValue) {
            tom.addOption({
                id: selectedValue,
                nama_barang: selectedLabel || "Bahan Habis Pakai",
            });
            tom.setValue(String(selectedValue), true);
        }

        return tom;
    }

    function initBatchSelect(
        $select,
        bhpId,
        selectedBatchId = null,
        selectedBatchData = null,
    ) {
        const $card = $select.closest(".return-item-card");

        if ($select[0].tomselect) {
            $select[0].tomselect.destroy();
        }

        $select.prop("disabled", false);

        const tom = new TomSelect($select[0], {
            valueField: "id",
            labelField: "nama_batch",
            searchField: ["nama_batch"],
            create: false,
            preload: true,
            placeholder: "Pilih Batch",
            maxOptions: 50,
            load: function (query, callback) {
                $.ajax({
                    url: `/farmasi/return-bahan-habis-pakai/get-data-batch-by-bahan-habis-pakai-id/${bhpId}`,
                    type: "GET",
                    dataType: "json",
                    data: { q: query },
                    success: function (res) {
                        callback(res.data || []);
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
                            <div class="font-medium">${escape(item.nama_batch || "-")}</div>
                            <div class="text-xs text-slate-500">
                                ED: ${escape(item.tanggal_kadaluarsa_bahan_habis_pakai || "-")}
                                ${item.harga_beli_satuan_bhp ? ` • Harga: Rp ${Number(item.harga_beli_satuan_bhp).toLocaleString("id-ID")}` : ""}
                            </div>
                        </div>
                    `;
                },
                item: function (item, escape) {
                    return `<div>${escape(item.nama_batch || "-")}</div>`;
                },
                no_results: function () {
                    return `<div class="px-3 py-2 text-sm text-slate-500">Batch tidak ditemukan</div>`;
                },
            },
            onChange: function (value) {
                const opt = this.options[value];
                if (!opt) return;

                const hargaBeli = parseFloat(opt.harga_beli_satuan_bhp) || 0;

                $card
                    .find(".input-expired-date")
                    .val(opt.tanggal_kadaluarsa_bahan_habis_pakai || "");
                $card.find(".input-harga-beli").val(hargaBeli);
                $card
                    .find(".input-harga-beli-display")
                    .val(formatRupiah(hargaBeli));

                loadStokBatchBhp($card, value);
                hitungSubtotal($card);
            },
        });

        if (selectedBatchId) {
            tom.addOption({
                id: selectedBatchId,
                nama_batch: selectedBatchData?.nama_batch || "-",
                tanggal_kadaluarsa_bahan_habis_pakai:
                    selectedBatchData?.tanggal_kadaluarsa_bahan_habis_pakai ||
                    "",
                harga_beli_satuan_bhp:
                    selectedBatchData?.harga_beli_satuan_bhp || 0,
            });
            tom.setValue(String(selectedBatchId), true);
        }

        return tom;
    }

    function loadStokBatchBhp($card, batchBhpId) {
        const depotId = $("#depot_id").val();

        if (!batchBhpId || !depotId) {
            $card.find(".input-stok-bhp").val(0);
            return;
        }

        $.ajax({
            url: `/farmasi/return-bahan-habis-pakai/get-stok-batch-bhp-depot/${batchBhpId}/${depotId}`,
            type: "GET",
            dataType: "json",
            success: function (res) {
                $card
                    .find(".input-stok-bhp")
                    .val(res.stok_bahan_habis_pakai ?? 0);
            },
            error: function () {
                $card.find(".input-stok-bhp").val(0);
            },
        });
    }

    function addRowDetail(data = {}) {
        $detailBody.append(generateRowDetail(data));

        const $lastCard = $detailBody.find(".return-item-card:last");
        const $bhpSelect = $lastCard.find(".select-bhp");
        const $batchSelect = $lastCard.find(".select-batch-bhp");

        initBhpSelect(
            $bhpSelect,
            data.bahan_habis_pakai_id ?? null,
            data.nama_barang ?? null,
        );

        if (data.bahan_habis_pakai_id) {
            initBatchSelect(
                $batchSelect,
                data.bahan_habis_pakai_id,
                data.batch_bahan_habis_pakai_id ?? null,
                {
                    nama_batch: data.nama_batch ?? "-",
                    tanggal_kadaluarsa_bahan_habis_pakai:
                        data.tanggal_kadaluarsa ?? "",
                    harga_beli_satuan_bhp: data.harga_beli ?? 0,
                },
            );

            $lastCard
                .find(".input-expired-date")
                .val(data.tanggal_kadaluarsa ?? "");
            $lastCard.find(".input-harga-beli").val(data.harga_beli ?? 0);
            $lastCard
                .find(".input-harga-beli-display")
                .val(formatRupiah(data.harga_beli ?? 0));
            $lastCard
                .find(".input-stok-bhp")
                .val(data.stok_bahan_habis_pakai ?? 0);
            $lastCard.find(".input-subtotal").val(data.subtotal ?? 0);
            $lastCard
                .find(".text-subtotal")
                .text(formatRupiah(data.subtotal ?? 0));
        }

        $bhpSelect.on("change", function () {
            const bhpId = $(this).val();

            $lastCard.find(".input-expired-date").val("");
            $lastCard.find(".input-harga-beli").val(0);
            $lastCard.find(".input-harga-beli-display").val("Rp 0");
            $lastCard.find(".input-stok-bhp").val(0);
            $lastCard.find(".input-subtotal").val(0);
            $lastCard.find(".text-subtotal").text("Rp 0");

            if (!bhpId) {
                if ($batchSelect[0].tomselect) {
                    $batchSelect[0].tomselect.destroy();
                }

                $batchSelect.html('<option value="">Pilih Batch</option>');
                $batchSelect.prop("disabled", true);
                hitungGrandTotal();
                return;
            }

            initBatchSelect($batchSelect, bhpId);
        });

        hitungSubtotal($lastCard);
    }

    function setFormHeader(data) {
        $("#kode_return").val(data.kode_return || "");
        $("#tanggal_return").val(data.tanggal_return || "");
        $("#keterangan").val(data.keterangan || "");

        if (supplierTom && data.supplier) {
            supplierTom.clear(true);
            supplierTom.clearOptions();
            supplierTom.addOption({
                id: data.supplier.id,
                nama_supplier: data.supplier.nama_supplier,
                kontak_person: data.supplier.kontak_person || "",
            });
            supplierTom.setValue(String(data.supplier.id), true);
        } else {
            $("#supplier_id")
                .val(data.supplier_id || "")
                .trigger("change");
        }

        if (depotTom && data.depot) {
            depotTom.clear(true);
            depotTom.clearOptions();
            depotTom.addOption({
                id: data.depot.id,
                nama_depot: data.depot.nama_depot,
            });
            depotTom.setValue(String(data.depot.id), true);
        } else {
            $("#depot_id")
                .val(data.depot_id || "")
                .trigger("change");
        }
    }

    function loadDataReturnBhp(kodeReturn) {
        $.ajax({
            url: `/farmasi/return-bahan-habis-pakai/get-data-return-bhp-by-kode-return/${encodeURIComponent(kodeReturn)}`,
            type: "GET",
            dataType: "json",
            beforeSend: function () {
                resetFormReturnBhp();
                openModalCreateReturnBhp();
            },
            success: function (response) {
                if (!response || !response.data) {
                    showErrorMessage(
                        "Data return bahan habis pakai tidak ditemukan.",
                    );
                    return;
                }

                const data = response.data;
                setFormHeader(data);

                $detailBody.empty();

                if (
                    Array.isArray(data.return_bahan_habis_pakai_detail) &&
                    data.return_bahan_habis_pakai_detail.length > 0
                ) {
                    data.return_bahan_habis_pakai_detail.forEach(
                        function (item) {
                            addRowDetail({
                                bahan_habis_pakai_id:
                                    item.bahan_habis_pakai_id ?? "",
                                batch_bahan_habis_pakai_id:
                                    item.batch_bahan_habis_pakai_id ?? "",
                                tanggal_kadaluarsa:
                                    item.batch_bahan_habis_pakai
                                        ?.tanggal_kadaluarsa_bahan_habis_pakai ??
                                    "",
                                qty: item.qty ?? 0,
                                harga_beli:
                                    parseFloat(
                                        item.harga_beli ??
                                            item.bahan_habis_pakai
                                                ?.harga_beli_satuan_bhp ??
                                            0,
                                    ) || 0,
                                subtotal:
                                    parseFloat(item.subtotal) ||
                                    (parseFloat(item.qty) || 0) *
                                        (parseFloat(item.harga_beli) || 0),
                                keterangan_item: item.keterangan_item ?? "",
                                stok_bahan_habis_pakai:
                                    item.bahan_habis_pakai?.stok_barang ?? 0,
                                nama_barang:
                                    item.bahan_habis_pakai?.nama_barang ?? "",
                                nama_batch:
                                    item.batch_bahan_habis_pakai?.nama_batch ??
                                    "-",
                            });
                        },
                    );
                } else {
                    addRowDetail();
                }

                hitungGrandTotal();
            },
            error: function () {
                showErrorMessage(
                    "Gagal mengambil data return bahan habis pakai.",
                );
            },
        });
    }

    $(document).on(
        "click",
        "#button-open-modal-create-return-bhp",
        function () {
            resetFormReturnBhp();
            addRowDetail();
            openModalCreateReturnBhp();
        },
    );

    $(document).on(
        "click",
        "#btnCloseModalCreateReturnBhp, #btnCancelModalCreateReturnBhp",
        function () {
            closeModalCreateReturnBhp();
        },
    );

    $(document).on("click", "#btnAddRowReturnBhp", function () {
        addRowDetail();
    });

    $(document).on("click", ".btn-remove-row", function () {
        $(this).closest(".return-item-card").remove();
        hitungGrandTotal();

        if ($detailBody.find(".return-item-card").length === 0) {
            addRowDetail();
        }
    });

    $(document).on("input", ".input-qty, .input-harga-beli", function () {
        const $card = $(this).closest(".return-item-card");
        hitungSubtotal($card);
    });

    $(document).on(
        "click",
        ".btn-edit-return-bhp, .btn-detail-return-bhp",
        function () {
            const kodeReturn = $(this).data("kode-return");

            if (!kodeReturn) {
                showErrorMessage("Kode return tidak ditemukan.");
                return;
            }

            loadDataReturnBhp(kodeReturn);
        },
    );

    $(document).on("submit", "#formCreateReturnBhp", function (e) {
        e.preventDefault();

        const $form = $(this);

        Swal.fire({
            title: "Simpan return bahan habis pakai?",
            text: "Data return bahan habis pakai akan disimpan dan piutang supplier akan dibuat otomatis.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Ya, simpan",
            cancelButtonText: "Batal",
            confirmButtonColor: "#059669",
            cancelButtonColor: "#94a3b8",
        }).then((result) => {
            if (!result.isConfirmed) return;

            submitCreateReturnBhp($form);
        });
    });

    function submitCreateReturnBhp($form) {
        const $btn = $("#btnSubmitCreateReturnBhp");
        const originalHtml = $btn.html();

        $.ajax({
            url: "/farmasi/return-bahan-habis-pakai/create-data-return-bahan-habis-pakai",
            type: "POST",
            data: $form.serialize(),
            dataType: "json",
            beforeSend: function () {
                $btn.prop("disabled", true).html(`
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle>
                            <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="4" class="opacity-75"></path>
                        </svg>
                        Menyimpan...
                    </span>
                `);
            },
            success: function (response) {
                closeModalCreateReturnBhp();
                resetFormReturnBhp();

                showSuccessMessage(
                    response.message ||
                        "Data return bahan habis pakai berhasil disimpan.",
                );

                if (window.tableReturnBahanHabisPakai) {
                    window.tableReturnBahanHabisPakai.ajax.reload();
                }
            },
            error: function (xhr) {
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const errors = [];

                    Object.values(xhr.responseJSON.errors).forEach(
                        function (item) {
                            if (Array.isArray(item)) {
                                item.forEach((msg) => errors.push(msg));
                            }
                        },
                    );

                    showValidationMessage(errors);
                    return;
                }

                showErrorMessage(
                    xhr.responseJSON?.message ||
                        "Gagal menyimpan data return bahan habis pakai.",
                );
            },
            complete: function () {
                $btn.prop("disabled", false).html(originalHtml);
            },
        });
    }

    $(document).on("change", "#depot_id", function () {
        $detailBody.find(".return-item-card").each(function () {
            const $card = $(this);
            const $batchSelect = $card.find(".select-batch-bhp");

            let batchId = $batchSelect.val();

            if ($batchSelect[0] && $batchSelect[0].tomselect) {
                batchId = $batchSelect[0].tomselect.getValue();
            }

            if (batchId) {
                loadStokBatchBhp($card, batchId);
            } else {
                $card.find(".input-stok-bhp").val(0);
            }
        });
    });
});
