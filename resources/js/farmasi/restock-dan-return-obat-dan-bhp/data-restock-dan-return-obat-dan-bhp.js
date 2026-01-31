import $ from "jquery";
import { Modal } from "flowbite";

/**
 * Assumptions:
 * - TomSelect is global: window.TomSelect
 * - axios is global (used in depot repeater)
 * - DataTables is loaded globally
 */

$(function () {
    // =====================================================
    // GLOBAL AJAX SETUP
    // =====================================================
    $.ajaxSetup({
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    // =====================================================
    // HELPERS: number & currency (SINGLE SOURCE)
    // =====================================================
    function toNumber(v) {
        if (v === null || v === undefined || v === "") return 0;
        if (typeof v === "number") return v;

        const s = String(v);
        if (s.includes("Rp")) return parseFloat(s.replace(/[^\d]/g, "")) || 0;
        return parseFloat(s) || 0;
    }

    function rupiah(n) {
        const x = Math.round(toNumber(n));
        return "Rp. " + x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Format rupiah realtime (ONE handler only)
    $(document).on("input", ".input-rupiah", function () {
        $(this).val(rupiah($(this).val()));
        recalcSummary();
    });

    $(document).on("click focus keyup", ".input-rupiah", function () {
        // keep cursor after "Rp. "
        try {
            if (this.selectionStart < 4) this.setSelectionRange(4, 4);
        } catch (e) {
            // ignore
        }
    });

    // =====================================================
    // 1) DATATABLES
    // =====================================================
    const table = $("#table-restock-return").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        searching: true,
        ajax: {
            url: "/farmasi/restock-return/get-data-restock-dan-return-barang-dan-obat",
            type: "GET",
        },
        columns: [
            { data: "kode_transaksi", name: "kode_transaksi" },
            { data: "nomor_faktur", name: "nomor_faktur", defaultContent: "-" },
            {
                data: "jenis_transaksi",
                name: "jenis_transaksi",
                orderable: false,
                searchable: false,
            },
            {
                data: "tanggal_pengiriman",
                name: "tanggal_pengiriman",
                defaultContent: "-",
            },
            { data: "tanggal_pembuatan", name: "tanggal_pembuatan" },
            { data: "supplier_nama", name: "supplier_nama" },
            { data: "nama_item", name: "nama_item", defaultContent: "-" },
            { data: "total_jumlah", name: "total_jumlah", searchable: false },
            {
                data: "approved_by_nama",
                name: "approved_by_nama",
                defaultContent: "-",
            },
            { data: "total_harga", name: "total_harga", searchable: false },
            { data: "tempo", name: "tempo", defaultContent: "-" },
            {
                data: "aksi",
                orderable: false,
                searchable: false,
                className: "text-right",
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

    $("#customSearch").on("keyup", function () {
        table.search(this.value).draw();
    });

    $("#restock_pageLength").on("change", function () {
        table.page.len(parseInt($(this).val(), 10)).draw();
    });

    const $info = $("#custom_customInfo");
    const $pagination = $("#custom_Pagination");

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        $info.text(
            `Showing ${info.start + 1}–${info.end} of ${info.recordsDisplay} (Page ${currentPage} / ${totalPages})`,
        );

        $pagination.empty();

        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(`
            <li>
                <a href="#" data-nav="prev"
                   class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">
                   Previous
                </a>
            </li>
        `);

        const maxVisible = 5;
        let start = Math.max(currentPage - Math.floor(maxVisible / 2), 1);
        let end = Math.min(start + maxVisible - 1, totalPages);
        if (end - start < maxVisible - 1)
            start = Math.max(end - maxVisible + 1, 1);

        for (let i = start; i <= end; i++) {
            const active =
                i === currentPage
                    ? "text-blue-600 bg-blue-50 border-blue-300 hover:bg-blue-100"
                    : "text-gray-500 bg-white border-gray-300 hover:bg-gray-100 hover:text-gray-700";

            $pagination.append(`
                <li>
                    <a href="#" data-page="${i}"
                       class="page-number flex items-center justify-center px-3 h-8 border ${active}">
                       ${i}
                    </a>
                </li>
            `);
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(`
            <li>
                <a href="#" data-nav="next"
                   class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">
                   Next
                </a>
            </li>
        `);
    }

    $pagination.on("click", "a", function (e) {
        e.preventDefault();
        const $link = $(this);
        if ($link.hasClass("opacity-50")) return;

        const nav = $link.data("nav");
        const page = $link.data("page");

        if (nav === "prev") table.page("previous").draw("page");
        else if (nav === "next") table.page("next").draw("page");
        else if (page) table.page(parseInt(page, 10) - 1).draw("page");
    });

    table.on("draw", updatePagination);
    updatePagination();

    // =====================================================
    // 2) MODAL + FORM
    // =====================================================
    const elModal = document.getElementById("modalCreateRestockReturn");
    const modalCreate = elModal
        ? new Modal(elModal, { backdrop: "static", closable: false })
        : null;

    const $form = $("#formCreateRestockReturn");
    const $btnSubmit = $("#btn-submit-create");

    function resetErrors() {
        $form.find("[data-error]").text("");
    }

    // =====================================================
    // SIDEBAR SUMMARY
    // =====================================================
    let activeTab = "obat";

    function getActiveSubtotal() {
        if (activeTab === "obat")
            return toNumber($("#harga_total_awal_obat").val());
        return toNumber($("#harga_total_awal_bhp").val());
    }

    function recalcSummary() {
        const subtotal = getActiveSubtotal();
        const pajakPct = parseFloat($("#sum-pajak").val() || "0") || 0;
        const biayaLainnya = toNumber($("#sum-biaya-lainnya").val());

        const pajakNominal = (subtotal * pajakPct) / 100;
        const total = subtotal + pajakNominal + biayaLainnya;

        $("#sum-subtotal").text(rupiah(subtotal));
        $("#sum-total").text(rupiah(total));
    }

    $("#sum-pajak").on("input", recalcSummary);
    $("#sum-biaya-lainnya").on("input", recalcSummary);

    // =====================================================
    // TOTAL OBAT (TOTAL = QTY * HARGA BARU)
    // =====================================================
    function getQtyObatByMode() {
        const mode = ($("#transaksi_obat").val() || "Restock").trim();
        return mode === "Return"
            ? Number($("#jumlah_obat").val() || 0)
            : Number($("#jumlah_obat_restock").val() || 0);
    }

    function recalcObatTotal() {
        const qty = getQtyObatByMode();
        const harga = toNumber($("#harga_satuan_obat_baru").val());
        const total = qty * harga;
        $("#harga_total_awal_obat").val(rupiah(total));
        recalcSummary();
    }

    $(document).on(
        "input",
        "#jumlah_obat, #jumlah_obat_restock, #harga_satuan_obat_baru",
        recalcObatTotal,
    );

    // =====================================================
    // TOTAL BHP (TOTAL = QTY * HARGA)
    // =====================================================
    function recalcBhpTotal() {
        const qty = Number($("#jumlah_bhp").val() || 0);
        const price = toNumber($("#harga_satuan_bhp").val());
        const total = qty * price;
        $("#harga_total_awal_bhp").val(rupiah(total));
        recalcSummary();
    }
    $(document).on("input", "#jumlah_bhp, #harga_satuan_bhp", recalcBhpTotal);

    // =====================================================
    // TABS (Obat / BHP)
    // =====================================================
    function updateTambahRincianButton() {
        $("#btn-tambah-rincian").html(
            `Tambah Rincian <i class="fa-solid fa-angle-right text-[10px]"></i>`,
        );
    }

    function setActiveTab(tab) {
        activeTab = tab;

        const tabs = [
            {
                btn: document.getElementById("tab-obat"),
                panel: document.getElementById("panel-obat"),
                key: "obat",
            },
            {
                btn: document.getElementById("tab-bhp"),
                panel: document.getElementById("panel-bhp"),
                key: "bhp",
            },
        ];

        tabs.forEach((t) => {
            const isActive = t.key === tab;

            t.btn?.classList.toggle("border-pink-500", isActive);
            t.btn?.classList.toggle("text-gray-900", isActive);
            t.btn?.classList.toggle("dark:text-white", isActive);

            t.btn?.classList.toggle("border-transparent", !isActive);
            t.btn?.classList.toggle("text-gray-500", !isActive);
            t.btn?.classList.toggle("dark:text-gray-400", !isActive);

            if (t.panel) t.panel.classList.toggle("hidden", !isActive);
        });

        updateTambahRincianButton();
        recalcSummary();
    }

    $("#tab-obat").on("click", () => setActiveTab("obat"));
    $("#tab-bhp").on("click", () => setActiveTab("bhp"));

    $("#btn-tambah-rincian").on("click", function () {
        if (activeTab === "obat")
            $("#btn-tambah-rincian-obat").trigger("click");
        else $("#btn-tambah-rincian-bhp").trigger("click");
    });

    function hideDuplicatePanelButtons() {
        $("#btn-tambah-rincian-obat").closest("div").addClass("hidden");
        $("#btn-tambah-rincian-bhp").closest("div").addClass("hidden");
    }

    // =====================================================
    // PURCHASE ORDER TOGGLE (FIXED, no merge conflict)
    // =====================================================
    function initPurchaseOrderToggle() {
        const toggle = document.getElementById("togglePurchaseOrder");
        const label = document.getElementById("labelPurchaseOrder");
        const fields = document.getElementById("purchaseOrderFields");
        const tempo = document.getElementById("tempo_pembayaran");
        const tgl = document.getElementById("tanggal_pengiriman");

        if (!toggle || !label || !fields || !tempo || !tgl) return;

        function syncPurchaseOrderUI() {
            const on = toggle.checked;
            fields.classList.toggle("hidden", !on);
            tempo.required = on;
            tgl.required = on;

            label.classList.toggle("text-gray-400", !on);
            label.classList.toggle("text-blue-600", on);
        }

        toggle.addEventListener("change", syncPurchaseOrderUI);
        syncPurchaseOrderUI();
    }

    // =====================================================
    // FORM META LOAD
    // =====================================================
    let DEFAULT_DEPOT_ID = null;

    function loadFormMeta(done) {
        $.get("/farmasi/restock-return/form-meta")
            .done(function (meta) {
                DEFAULT_DEPOT_ID = meta.default_depot_id || null;

                const $jtSelect = $("#jenis_transaksi");
                $jtSelect
                    .empty()
                    .append('<option value="">-- Select --</option>');
                (meta.jenis_transaksi || []).forEach((item) => {
                    $jtSelect.append(
                        `<option value="${item.value}">${item.label}</option>`,
                    );
                });

                done && done();
            })
            .fail(function (xhr) {
                console.error("Failed load meta", xhr);
                done && done();
            });
    }

    // =====================================================
    // TomSelect: Obat / Depot / BHP
    // =====================================================
    let obatSelect = null;
    let depotSelect = null;
    let bhpSelect = null;

    function initObatSelect() {
        if (obatSelect) return;
        const el = document.querySelector("#obat_id");
        if (!el) return;

        obatSelect = new window.TomSelect(el, {
            valueField: "id",
            labelField: "nama_obat",
            searchField: "nama_obat",
            maxItems: 1,
            preload: true,
            load: function (query, callback) {
                $.get("/testing-tom-select/data-obat", { q: query || "" })
                    .done((res) => callback(res))
                    .fail(() => callback());
            },
            onChange: function (value) {
                if (!value) {
                    clearObatMeta();
                    syncTransaksiObatUI();
                    return;
                }
                syncTransaksiObatUI();
                fillObatMeta(value);
            },
        });
    }

    function initDepotSelect() {
        if (depotSelect) return;
        const el = document.querySelector("#depot_id");
        if (!el) return;

        depotSelect = new window.TomSelect(el, {
            valueField: "id",
            labelField: "nama_depot",
            searchField: "nama_depot",
            maxItems: 1,
            preload: true,
            load: function (query, callback) {
                const obatId = $("#obat_id").val();
                $.get("/farmasi/restock-return/get-data-depot", {
                    q: query || "",
                    obat_id: obatId,
                })
                    .done((res) => callback(res))
                    .fail(() => callback());
            },
            onChange: function (value) {
                const data = this.options[value];
                if (data) {
                    $("#info-stok-depot").removeClass("hidden");
                    $("#nilai-stok").text(data.stok_obat);
                } else {
                    $("#info-stok-depot").addClass("hidden");
                }

                const id = $("#obat_id").val();
                if (id) fillObatMeta(id);
            },
        });
    }

    function initBhpSelect() {
        if (bhpSelect) return;
        const el = document.querySelector("#bhp_id");
        if (!el) return;

        bhpSelect = new window.TomSelect(el, {
            valueField: "id",
            labelField: "nama_barang",
            searchField: "nama_barang",
            maxItems: 1,
            preload: true,
            load: function (query, callback) {
                $.get("/farmasi/restock-return/get-data-depot-bhp", {
                    q: query || "",
                })
                    .done((res) => callback(res))
                    .fail(() => callback());
            },
            onChange: function () {
                // enable transaksi_bhp + default Restock
                syncTransaksiBhpUI();
            },
        });
    }

    // =====================================================
    // OBAT: Restock/Return UI (SINGLE TRUTH)
    // =====================================================
    function syncTransaksiObatUI() {
        const obatId = $("#obat_id").val();
        const $trx = $("#transaksi_obat");

        $trx.prop("disabled", !obatId);

        if (obatId && !$trx.val()) $trx.val("Restock");

        const mode = ($trx.val() || "").trim();

        $("#return_only_fields").toggleClass("hidden", mode !== "Return");
        $("#restock_only_fields").toggleClass("hidden", mode !== "Restock");

        // Return requires expired+batch; Restock doesn't
        $("#expired_date_obat").prop("required", mode === "Return");
        $("#batch_obat").prop("required", mode === "Return");

        // total stok only for Return (kalau kamu punya wrapper khusus, pakai itu)
        // Kalau tidak ada wrapper, minimal disable/enable field:
        if (mode === "Return") {
            $("#total_stok_item")
                .prop("readonly", true)
                .prop("disabled", false);
        } else {
            $("#total_stok_item")
                .val("")
                .prop("readonly", true)
                .prop("disabled", true);
        }

        // total depends on qty (different field per mode)
        recalcObatTotal();
    }

    $(document).on("change", "#transaksi_obat", function () {
        syncTransaksiObatUI();
        const id = $("#obat_id").val();
        if (id) fillObatMeta(id);
    });

    function clearObatMeta() {
        $("#kategori_obat_id").val("");
        $("#satuan_obat_id").val("");
        $("#satuan_obat_id_restock").val("");

        $("#harga_beli_satuan_obat_lama").val("");
        $("#harga_jual_lama_obat").val("");
        $("#harga_jual_otc_lama_obat").val("");

        $("#batch_obat").val("");
        $("#expired_date_obat").val("");
        $("#total_stok_item").val("");

        $("#jumlah_obat").val("0");
        $("#jumlah_obat_restock").val("0");
        $("#harga_satuan_obat_baru").val("Rp. 0");

        $("#harga_total_awal_obat").val("Rp. 0");
        recalcSummary();
    }

    function fillObatMeta(id) {
        if (!id) return;

        const depotId = $("#depot_id").val() || DEFAULT_DEPOT_ID;
        const mode = ($("#transaksi_obat").val() || "Restock").trim();
        const isReturn = mode === "Return";

        $.get(`/farmasi/restock-return/obat/${id}/meta`, { depot_id: depotId })
            .done(function (res) {
                $("#kategori_obat_id").val(res.nama_kategori || "");

                $("#satuan_obat_id").val(res.nama_satuan || "");
                $("#satuan_obat_id_restock").val(res.nama_satuan || "");

                $("#harga_beli_satuan_obat_lama").val(
                    rupiah(res.harga_beli_satuan_obat_lama || 0),
                );
                $("#harga_jual_lama_obat").val(
                    rupiah(res.harga_jual_lama || 0),
                );
                $("#harga_jual_otc_lama_obat").val(
                    rupiah(res.harga_jual_otc_obat_lama || 0),
                );

                if (isReturn) {
                    $("#expired_date_obat").val(res.expired_lama || "");
                    $("#batch_obat").val(res.batch_lama || "");
                    $("#total_stok_item").val(
                        res.stok_sekarang ?? res.jumlah ?? 0,
                    );
                } else {
                    $("#expired_date_obat").val("");
                    $("#batch_obat").val("");
                    $("#total_stok_item").val("");
                }

                recalcObatTotal();
                recalcSummary();
            })
            .fail((xhr) => console.error("Failed get meta", xhr));
    }

    // =====================================================
    // BHP: Restock/Return UI (SINGLE TRUTH)
    // =====================================================
    function syncTransaksiBhpUI() {
        const bhpId = $("#bhp_id").val();
        const $trx = $("#transaksi_bhp");

        $trx.prop("disabled", !bhpId);

        if (bhpId && !$trx.val()) $trx.val("Restock");

        const mode = ($trx.val() || "").trim();

        $("#bhp_return_only_fields").toggleClass("hidden", mode !== "Return");
        $("#bhp_restock_only_fields").toggleClass("hidden", mode !== "Restock");

        $("#expired_date_bhp").prop("required", mode === "Return");
        $("#batch_bhp").prop("required", mode === "Return");

        recalcBhpTotal();
    }

    $(document).on("change", "#bhp_id", function () {
        syncTransaksiBhpUI();
    });

    $(document).on("change", "#transaksi_bhp", function () {
        syncTransaksiBhpUI();
    });

    // =====================================================
    // SUPPLIER TomSelect (Create / Detail)
    // =====================================================
    let supplierSelect = null;
    let supplierJustCreatedId = null;

    function getSupplierDataset() {
        const el = document.getElementById("supplier_id");
        return {
            el,
            urlIndex: el?.dataset.urlIndex,
            urlStore: el?.dataset.urlStore,
            urlUpdate: el?.dataset.urlUpdate,
            urlShowTpl: el?.dataset.urlShow,
        };
    }

    function showSupplierDetailCreate(data) {
        $("#supplier-detail").removeClass("hidden");
        $("#btn-clear-supplier").removeClass("hidden");
        $("#supplier_kontak_person").val(data.kontak_person || "");
        $("#supplier_no_hp").val(data.no_hp || "");
        $("#supplier_email").val(data.email || "");
        $("#supplier_alamat").val(data.alamat || "");
        $("#supplier_keterangan").val(data.keterangan || "");
        $("#supplier-detail input, #supplier-detail textarea")
            .prop("readonly", false)
            .prop("disabled", false);
    }

    function clearSupplierDetailCreate() {
        $("#supplier-detail").addClass("hidden");
        $("#btn-clear-supplier").addClass("hidden");
        $("#supplier-detail input, #supplier-detail textarea")
            .val("")
            .prop("readonly", false)
            .prop("disabled", false);
    }

    function initSupplierSelectCreate() {
        const { el, urlIndex, urlStore, urlShowTpl } = getSupplierDataset();
        if (!el || supplierSelect) return;

        supplierSelect = new window.TomSelect(el, {
            valueField: "id",
            labelField: "nama_supplier",
            searchField: "nama_supplier",
            preload: true,
            maxOptions: 10,
            create: function (input, callback) {
                $.post(urlStore, { nama_supplier: input })
                    .done(function (res) {
                        supplierJustCreatedId = String(res.id);
                        callback(res);
                        showSupplierDetailCreate(res);
                    })
                    .fail(function () {
                        alert("Failed to add supplier");
                        callback();
                    });
            },
            load: function (query, callback) {
                $.get(urlIndex, { q: query })
                    .done((res) => callback(res))
                    .fail(() => callback());
            },
            onChange: function (value) {
                if (!value) {
                    supplierJustCreatedId = null;
                    clearSupplierDetailCreate();
                    return;
                }

                if (
                    supplierJustCreatedId &&
                    String(value) === String(supplierJustCreatedId)
                )
                    return;

                const urlShow = urlShowTpl.replace("__ID__", value);
                $.get(urlShow)
                    .done((res) => {
                        supplierJustCreatedId = null;
                        showSupplierDetailCreate(res);
                    })
                    .fail(clearSupplierDetailCreate);
            },
        });

        $("#btn-clear-supplier")
            .off("click")
            .on("click", function () {
                supplierSelect.clear(true);
                supplierJustCreatedId = null;
                clearSupplierDetailCreate();
            });
    }

    // =====================================================
    // RESET FORM
    // =====================================================
    function resetForm() {
        $form[0]?.reset?.();
        resetErrors();

        $("#container-rincian").empty();

        if (supplierSelect) supplierSelect.clear(true);
        supplierJustCreatedId = null;
        clearSupplierDetailCreate();

        $("#sum-pajak").val("0");
        $("#sum-biaya-lainnya").val("Rp. 0");

        clearObatMeta();
        $("#harga_total_awal_bhp").val("Rp. 0");
        $("#jumlah_bhp").val("0");
        $("#harga_satuan_bhp").val("Rp. 0");

        $("#transaksi_obat").val("").prop("disabled", true);
        $("#transaksi_bhp").val("").prop("disabled", true);

        $("#return_only_fields").addClass("hidden");
        $("#restock_only_fields").addClass("hidden");
        $("#bhp_return_only_fields").addClass("hidden");
        $("#bhp_restock_only_fields").addClass("hidden");

        $("#info-stok-depot").addClass("hidden");

        setActiveTab("obat");
        hideDuplicatePanelButtons();
        recalcSummary();
    }

    // =====================================================
    // OPEN/CLOSE MODAL (ONE init only)
    // =====================================================
    $("#btn-open-modal-create")
        .off("click")
        .on("click", function () {
            resetForm();
            modalCreate?.show();

            setTimeout(() => {
                initPurchaseOrderToggle();
                initSupplierSelectCreate();
                loadFormMeta(() => {
                    initObatSelect();
                    initDepotSelect();
                    initBhpSelect();

                    // set default states after select ready
                    syncTransaksiObatUI();
                    syncTransaksiBhpUI();
                });
            }, 50);
        });

    $("#btn-close-modal-create, #btn-cancel-modal-create")
        .off("click")
        .on("click", function () {
            modalCreate?.hide();
            resetForm();
        });

    // =====================================================
    // ADD DETAIL: OBAT (sidebar card)
    // =====================================================
    $("#btn-tambah-rincian-obat")
        .off("click")
        .on("click", function () {
            const obatId = $("#obat_id").val();
            if (!obatId) return alert("Please select a drug first.");

            const mode = ($("#transaksi_obat").val() || "").trim();
            if (!mode) return alert("Please select transaction mode.");

            const namaObat =
                obatSelect?.getItem(obatId)?.textContent ||
                $("#obat_id option:selected").text();
            const jumlah = getQtyObatByMode();

            if (!jumlah || jumlah <= 0)
                return alert("Quantity must be greater than 0.");

            const satuan =
                $("#satuan_obat_id").val() ||
                $("#satuan_obat_id_restock").val() ||
                "-";

            const hargaBeli = $("#harga_satuan_obat_baru").val() || "Rp. 0";
            const totalAwal = $("#harga_total_awal_obat").val() || "Rp. 0";
            const expDate = $("#expired_date_obat").val() || "-";
            const batch = $("#batch_obat").val() || "-";

            const extraInfo =
                mode === "Return"
                    ? `<p class="text-[10px] text-gray-500 italic">Exp. ${expDate}</p>
                       <p class="text-[10px] text-gray-500 italic">Batch: ${batch}</p>`
                    : `<p class="text-[10px] text-gray-500 italic">Restock item</p>`;

            const rincianHTML = `
            <div class="rincian-item bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm relative mb-3">
                <button type="button" class="btn-hapus-rincian absolute top-2 right-2 text-pink-500 hover:text-pink-700">
                    <i class="fa-solid fa-circle-xmark text-lg"></i>
                </button>

                <div class="flex justify-between items-start">
                    <div>
                        <h4 class="text-blue-500 font-bold text-sm">${namaObat}</h4>
                        <p class="text-xs text-gray-700 dark:text-gray-300 font-medium">${mode}</p>
                        <div class="mt-2">
                            <p class="text-[10px] text-gray-500 italic">Avg purchase price</p>
                            ${extraInfo}
                        </div>
                    </div>

                    <div class="text-right pt-4">
                        <p class="text-xs text-gray-600 dark:text-gray-400">${jumlah} ${satuan}</p>
                        <p class="text-sm font-bold text-gray-800 dark:text-white">${totalAwal}</p>
                        <p class="text-[10px] text-gray-500">@ ${hargaBeli}</p>
                    </div>
                </div>
            </div>
            `;

            $("#container-rincian").append(rincianHTML);

            // reset detail fields only
            $("#expired_date_obat").val("");
            $("#batch_obat").val("");
            $("#jumlah_obat").val("0");
            $("#jumlah_obat_restock").val("0");
            $("#harga_satuan_obat_baru").val("Rp. 0");
            recalcObatTotal();
        });

    $(document).on("click", ".btn-hapus-rincian", function () {
        $(this).closest(".rincian-item").remove();
        recalcSummary();
    });

    // =====================================================
    // SUBMIT TRANSACTION
    // =====================================================
    $form.off("submit").on("submit", function (e) {
        e.preventDefault();
        resetErrors();

        const urlTransaksi = $form.data("url");
        const supplierId = $("#supplier_id").val();
        const depotId = $("#depot_id").val() || DEFAULT_DEPOT_ID;
        const { urlUpdate } = getSupplierDataset();

        $btnSubmit.prop("disabled", true).text("Saving...");

        const items = [];
        const tabAktif = activeTab;

        if (tabAktif === "obat") {
            const mode = ($("#transaksi_obat").val() || "Restock").trim();
            const jumlah = getQtyObatByMode();

            items.push({
                type: "obat",
                transaksi_obat: mode,
                obat_id: $("#obat_id").val(),
                batch:
                    mode === "Return" ? $("#batch_obat").val() || null : null,
                expired_date:
                    mode === "Return"
                        ? $("#expired_date_obat").val() || null
                        : null,
                jumlah,
                satuan:
                    $("#satuan_obat_id").val() ||
                    $("#satuan_obat_id_restock").val() ||
                    null,
                harga_beli: toNumber($("#harga_satuan_obat_baru").val()),
                depot_id: depotId,
                keterangan: $("#keterangan_obat").val() || null,
            });
        } else {
            const mode = ($("#transaksi_bhp").val() || "Restock").trim();
            items.push({
                type: "bhp",
                transaksi_bhp: mode,
                bhp_id: $("#bhp_id").val(),
                batch: mode === "Return" ? $("#batch_bhp").val() || null : null,
                expired_date:
                    mode === "Return"
                        ? $("#expired_date_bhp").val() || null
                        : null,
                jumlah: parseInt($("#jumlah_bhp").val() || "0", 10),
                harga_beli: toNumber($("#harga_satuan_bhp").val()),
                depot_id: $("#depot_id_bhp").val() || null,
                keterangan: $("#keterangan_bhp").val() || null,
            });
        }

        const payload = {
            tanggal_transaksi: $form.find('[name="tanggal_transaksi"]').val(),
            jenis_transaksi: $form.find('[name="jenis_transaksi"]').val(),
            supplier_id: supplierId || null,
            nomor_faktur: $form.find('[name="nomor_faktur"]').val() || null,
            keterangan: $form.find('[name="keterangan"]').val() || null,
            pajak_persen: parseFloat($("#sum-pajak").val() || "0") || 0,
            biaya_lainnya: toNumber($("#sum-biaya-lainnya").val()),
            items,
        };

        const doCreateTransaksi = function () {
            $.ajax({
                url: urlTransaksi,
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify(payload),
            })
                .done(function (res) {
                    modalCreate?.hide();
                    resetForm();

                    if ($.fn.DataTable.isDataTable("#table-restock-return")) {
                        $("#table-restock-return")
                            .DataTable()
                            .ajax.reload(null, false);
                    }
                    if (res?.redirect_url)
                        window.location.href = res.redirect_url;
                })
                .fail(function (xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        Object.keys(errors).forEach((key) => {
                            $form
                                .find(`[data-error="${key}"]`)
                                .text(errors[key][0] ?? "Invalid");
                        });
                    } else {
                        alert("Error saving transaction.");
                        console.error(xhr);
                    }
                })
                .always(function () {
                    $btnSubmit.prop("disabled", false).text("Simpan Transaksi");
                });
        };

        // update supplier detail if selected
        if (supplierId) {
            const payloadSupplier = {
                id: supplierId,
                kontak_person: $("#supplier_kontak_person").val() || null,
                no_hp: $("#supplier_no_hp").val() || null,
                email: $("#supplier_email").val() || null,
                alamat: $("#supplier_alamat").val() || null,
                keterangan: $("#supplier_keterangan").val() || null,
            };

            $.post(urlUpdate, payloadSupplier)
                .done(() => doCreateTransaksi())
                .fail(() => doCreateTransaksi());
        } else {
            doCreateTransaksi();
        }
    });

    // defaults on load
    setActiveTab("obat");
    hideDuplicatePanelButtons();
    recalcSummary();

    // =====================================================
    // DEPOT REPEATER (SINGLE, FIX tomselect null crash)
    // =====================================================
    const $depotContainer = $("#depot-container-restock");
    if ($depotContainer.length) {
        const $depotTemplate = $depotContainer
            .find(".depot-row")
            .first()
            .clone(false);

        function initNamaDepotSelect($row) {
            const el = $row.find(".select-nama-depot")[0];
            const btnClear = $row.find(".btn-clear-depot")[0];
            if (!el) return;

            const urlIndex = el.dataset.urlIndex;
            const urlStore = el.dataset.urlStore;

            if (el.tomselect) el.tomselect.destroy();

            const ts = new window.TomSelect(el, {
                valueField: "id",
                labelField: "nama_depot",
                searchField: "nama_depot",
                preload: true,
                maxItems: 1,
                placeholder: "Select / type depot name",
                load: function (query, callback) {
                    axios
                        .get(urlIndex, { params: { q: query } })
                        .then((res) => callback(res.data || []))
                        .catch(() => callback([]));
                },
                create: function (input, callback) {
                    axios
                        .post(urlStore, { nama_depot: input })
                        .then((res) => callback(res.data))
                        .catch(() => callback());
                },
                onChange: function (value) {
                    if (!btnClear) return;
                    btnClear.classList.toggle("hidden", !value);
                },
            });

            if (btnClear) {
                btnClear.onclick = function () {
                    const value = ts.getValue();
                    if (!value) return btnClear.classList.add("hidden");
                    ts.clear();
                    btnClear.classList.add("hidden");
                };
            }
        }

        function initTipeDepotSelect($row) {
            const el = $row.find(".select-tipe-depot")[0];
            const btnClear = $row.find(".btn-clear-tipe-depot")[0];
            if (!el) return;

            const urlIndex = el.dataset.urlIndex;
            const urlStore = el.dataset.urlStore;

            if (el.tomselect) el.tomselect.destroy();

            const ts = new window.TomSelect(el, {
                valueField: "id",
                labelField: "nama_tipe_depot",
                searchField: "nama_tipe_depot",
                preload: true,
                maxItems: 1,
                placeholder: "Select / type depot type",
                load: function (query, callback) {
                    axios
                        .get(urlIndex, { params: { q: query } })
                        .then((res) => callback(res.data || []))
                        .catch(() => callback([]));
                },
                create: function (input, callback) {
                    axios
                        .post(urlStore, { nama_tipe_depot: input })
                        .then((res) => callback(res.data))
                        .catch(() => callback());
                },
                onChange: function (value) {
                    if (!btnClear) return;
                    btnClear.classList.toggle("hidden", !value);
                },
            });

            if (btnClear) {
                btnClear.onclick = function () {
                    const value = ts.getValue();
                    if (!value) return btnClear.classList.add("hidden");
                    ts.clear();
                    btnClear.classList.add("hidden");
                };
            }
        }

        // init first row
        const $firstRow = $depotContainer.find(".depot-row").first();
        initNamaDepotSelect($firstRow);
        initTipeDepotSelect($firstRow);

        $("#btn-add-depot-restock")
            .off("click")
            .on("click", function () {
                const $newRow = $depotTemplate.clone(false);

                $newRow.find(".select-nama-depot").val("");
                $newRow.find(".btn-clear-depot").addClass("hidden");
                $newRow.find(".select-tipe-depot").val("");
                $newRow.find(".btn-clear-tipe-depot").addClass("hidden");
                $newRow.find(".input-stok-depot").val(0);

                $depotContainer.append($newRow);

                initNamaDepotSelect($newRow);
                initTipeDepotSelect($newRow);
            });

        $(document).on(
            "click",
            "#depot-container-restock .btn-remove-depot",
            function () {
                const $rows = $depotContainer.find(".depot-row");
                if ($rows.length <= 1) {
                    const $row = $rows.first();
                    const depotEl = $row.find(".select-nama-depot")[0] || null;
                    depotEl?.tomselect?.clear?.();

                    const tipeEl = $row.find(".select-tipe-depot")[0] || null;
                    tipeEl?.tomselect?.clear?.();

                    $row.find(".btn-clear-depot").addClass("hidden");
                    $row.find(".btn-clear-tipe-depot").addClass("hidden");
                    $row.find(".input-stok-depot").val(0);
                    return;
                }
                $(this).closest(".depot-row").remove();
            },
        );
    }
});
