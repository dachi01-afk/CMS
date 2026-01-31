import $ from "jquery";
import { Modal } from "flowbite";

// Assumptions:
// - TomSelect is global: window.TomSelect
// - axios is global (used in depot section)
// - DataTables is loaded globally
// - Your Blade uses INPUT for expired_date_obat & batch_obat (NOT select)

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

    // =====================================================
    // HELPERS: number & currency
    // =====================================================
    function toNumber(v) {
        if (!v) return 0;
        if (typeof v === "number") return v;
        const s = String(v);
        if (s.includes("Rp")) return parseFloat(s.replace(/[^\d]/g, "")) || 0;
        return parseFloat(v) || 0;
    }

    function rupiah(n) {
        const x = Math.round(toNumber(n));
        return "Rp. " + x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    $(document).on("input", ".input-rupiah", function () {
        $(this).val(rupiah($(this).val()));
        recalcSummary();
    });

    $(document).on("click focus keyup", ".input-rupiah", function () {
        if (this.selectionStart < 4) this.setSelectionRange(4, 4);
    });

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
    // TOTAL OBAT
    // =====================================================
    function calcTotalObat() {
        const hargaSatuan = $("#harga_satuan_obat_baru").val() || "Rp. 0";
        $("#harga_total_awal_obat").val(hargaSatuan);
        recalcSummary();
    }
    $("#harga_satuan_obat_baru").on("input keyup", calcTotalObat);

    // =====================================================
    // TOTAL BHP
    // =====================================================
    function recalcBhpTotal() {
        const qty = Number($("#jumlah_bhp").val() || 0);
        const price = toNumber($("#harga_satuan_bhp").val());
        const total = qty * price;
        $("#harga_total_awal_bhp").val(rupiah(total));
        recalcSummary();
    }
    $("#jumlah_bhp").on("input", recalcBhpTotal);
    $("#harga_satuan_bhp").on("input", recalcBhpTotal);

    // =====================================================
    // TABS (Obat / BHP)
    // =====================================================
    function updateTambahRincianButton() {
        const $btn = $("#btn-tambah-rincian");
        if (activeTab === "obat") {
            $btn.html(
                `Tambah Rincian <i class="fa-solid fa-angle-right text-[10px]"></i>`,
            );
        } else {
            $btn.html(
                `Tambah Rincian <i class="fa-solid fa-angle-right text-[10px]"></i>`,
            );
        }
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
    // PURCHASE ORDER TOGGLE
    // =====================================================
    function initPurchaseOrderToggle() {
        const toggle = document.getElementById("togglePurchaseOrder");
        const label = document.getElementById("labelPurchaseOrder");
        const fields = document.getElementById("purchaseOrderFields");
        const tempo = document.getElementById("tempo_pembayaran");
        const tgl = document.getElementById("tanggal_pengiriman");

// <<<<<<< HEAD
//         if (!toggle || !label || !fields || !tempo || !tgl) return;

//         function syncPurchaseOrderUI() {
//             const on = toggle.checked;
//             fields.classList.toggle("hidden", !on);
//             tempo.required = on;
//             tgl.required = on;
//             label.classList.toggle("text-gray-400", !on);
//             label.classList.toggle("text-blue-600", on);
// =======
    // Jika user memilih jenis transaksi "Return", maka field "Total Stok Sekarang" akan muncul.
    // Jika pilih "Restock", field stok tersebut akan disembunyikan karena dianggap menambah barang baru.
    function toggleTransactionMode() {
        const jenisTransaksi = $("#jenis_transaksi").val() || "";
        const isReturn = jenisTransaksi.toLowerCase().includes("return");
        const $wrapperStok = $("#total_stok_item").closest("div");
        const $labelJumlah = $("label[for='jumlah_obat']");
        const $selectEDReturn = $("#expired_date_obat_return");
        const $selectEDRestock = $("#expired_date_obat_restock");

        if (isReturn) {
            $wrapperStok.removeClass("hidden").addClass('md:col-span-2');
            $selectEDReturn.removeClass("hidden");
            $selectEDRestock.addClass("hidden");
            $("#total_stok_item")
                .prop("readonly", true)
                .prop("disabled", false);
            $labelJumlah.text("Jumlah Return *");
        } else {
            $wrapperStok.addClass("hidden");
            $selectEDRestock.removeClass("hidden");
            $("#total_stok_item").val("");
            $labelJumlah.text("Jumlah Obat / Restock *");
// >>>>>>> b4a6960a24898a0e8c39141f0e3d2b146bac4f2b
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

                // Jenis transaksi header
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
    // TomSelect Obat / Depot / BHP
    // =====================================================
    let obatSelect = null;
    let depotSelect = null;
    let bhpSelect = null;

    function initObatSelect() {
        if (obatSelect) return;

        obatSelect = new window.TomSelect("#obat_id", {
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
                    syncTransaksiObatUI(); // disables transaksi select
                    return;
                }
                syncTransaksiObatUI(); // enable transaksi select & set default Restock
                fillObatMeta(value);
            },
        });
    }

    function initDepotSelect() {
        if (depotSelect) return;

        depotSelect = new window.TomSelect("#depot_id", {
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

                // refresh meta when depot changes
                const id = $("#obat_id").val();
                if (id) fillObatMeta(id);
            },
        });
    }

    function initBhpSelect() {
        if (bhpSelect) return;

        bhpSelect = new window.TomSelect("#bhp_id", {
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
        });
    }

    // =====================================================
    // OBAT UI LOGIC: Restock/Return toggle (SINGLE TRUTH)
    // =====================================================
    function syncTransaksiObatUI() {
        const obatId = $("#obat_id").val();
        const $trx = $("#transaksi_obat");

        // disable if no drug selected
        $trx.prop("disabled", !obatId);

        // default Restock when enabled
        if (obatId && !$trx.val()) $trx.val("Restock");

        const mode = ($trx.val() || "").trim(); // "Restock" / "Return"

        $("#return_only_fields").toggleClass("hidden", mode !== "Return");
        $("#restock_only_fields").toggleClass("hidden", mode !== "Restock");

        // required only on Return
        $("#expired_date_obat").prop("required", mode === "Return");
        $("#batch_obat").prop("required", mode === "Return");
    }

    // change transaksi_obat
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

        $("#harga_total_awal_obat").val("Rp. 0");
        recalcSummary();
    }

    // =====================================================
    // Fill Meta Drug (MATCHES YOUR BLADE INPUTS)
    // =====================================================
    function fillObatMeta(id) {
        if (!id) return;

        const depotId = $("#depot_id").val() || DEFAULT_DEPOT_ID;
        const mode = ($("#transaksi_obat").val() || "Restock").trim();
        const isReturn = mode === "Return";

        $.get(`/farmasi/restock-return/obat/${id}/meta`, { depot_id: depotId })
            .done(function (res) {
                $("#kategori_obat_id").val(res.nama_kategori || "");

                // Blade uses INPUT for satuan
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
                    // Restock mode: keep return-only fields empty
                    $("#expired_date_obat").val("");
                    $("#batch_obat").val("");
                    $("#total_stok_item").val("");
                }

                calcTotalObat();
                recalcSummary();
            })
            .fail((xhr) => console.error("Failed get meta", xhr));
    }

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

        $("#btn-clear-supplier").on("click", function () {
            supplierSelect.clear(true);
            supplierJustCreatedId = null;
            clearSupplierDetailCreate();
        });
    }

    // =====================================================
    // RESET FORM
    // =====================================================
    function resetForm() {
        $form[0].reset();
        resetErrors();

        if (supplierSelect) supplierSelect.clear(true);
        supplierJustCreatedId = null;
        clearSupplierDetailCreate();

        $("#sum-pajak").val("0");
        $("#sum-biaya-lainnya").val("Rp. 0");
        $("#harga_total_awal_obat").val("Rp. 0");
        $("#harga_total_awal_bhp").val("Rp. 0");

        clearObatMeta();

        // reset transaksi_obat UI
        $("#transaksi_obat").val("").prop("disabled", true);
        $("#return_only_fields").addClass("hidden");
        $("#restock_only_fields").addClass("hidden");

        // reset depot info
        $("#info-stok-depot").addClass("hidden");

        setActiveTab("obat");
        hideDuplicatePanelButtons();
        recalcSummary();
    }

    // =====================================================
    // OPEN/CLOSE MODAL
    // =====================================================
    $("#btn-open-modal-create").on("click", function () {
        resetForm();
        modalCreate?.show();

        setTimeout(() => {
            initPurchaseOrderToggle();
            initSupplierSelectCreate();
            loadFormMeta(() => {
                initObatSelect();
                initDepotSelect();
                initBhpSelect();
            });
        }, 50);
    });

    $("#btn-close-modal-create, #btn-cancel-modal-create").on(
        "click",
        function () {
            modalCreate?.hide();
            resetForm();
        },
    );

    // =====================================================
    // ADD DETAIL: OBAT (sidebar card)
    // =====================================================
    $("#btn-tambah-rincian-obat").on("click", function () {
        const obatId = $("#obat_id").val();
        if (!obatId) {
            alert("Please select a drug first.");
            return;
        }

        const mode = ($("#transaksi_obat").val() || "").trim();
        if (!mode) {
            alert("Please select transaction mode: Restock or Return.");
            return;
        }

        const namaObat = $("#obat_id option:selected").text();

        let jumlah = 0;
        if (mode === "Return") jumlah = Number($("#jumlah_obat").val() || 0);
        else jumlah = Number($("#jumlah_obat_restock").val() || 0);

        if (!jumlah || jumlah <= 0) {
            alert("Quantity must be greater than 0.");
            return;
        }

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

        // reset detail fields (don’t nuke header)
        // clear return fields
        $("#expired_date_obat").val("");
        $("#batch_obat").val("");
        $("#jumlah_obat").val("0");
        $("#jumlah_obat_restock").val("0");
        $("#harga_satuan_obat_baru").val("Rp. 0");
        $("#harga_total_awal_obat").val("Rp. 0");

        calcTotalObat();
        recalcSummary();
    });

    $(document).on("click", ".btn-hapus-rincian", function () {
        $(this).closest(".rincian-item").remove();
        recalcSummary();
    });

    // =====================================================
    // SUBMIT TRANSACTION
    // =====================================================
    $form.on("submit", function (e) {
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

            const jumlah =
                mode === "Return"
                    ? parseInt($("#jumlah_obat").val() || "0", 10)
                    : parseInt($("#jumlah_obat_restock").val() || "0", 10);

            items.push({
                type: "obat",
                transaksi_obat: mode, // <--- IMPORTANT
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
            items.push({
                type: "bhp",
                bhp_id: $("#bhp_id").val(),
                batch: $("#batch_bhp").val() || null,
                expired_date: $("#expired_date_bhp").val() || null,
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
});

// =====================================================
// 3) DEPOT REPEATER (your code kept, just cleaned slightly)
// =====================================================
$(function () {
    const $depotContainer = $("#depot-container-restock");
    if (!$depotContainer.length) return;

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

    $("#btn-add-depot-restock").on("click", function () {
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
});

function toNumber(v) {
    if (!v) return 0;
    if (typeof v === "number") return v;
    const s = String(v);
    if (s.includes("Rp")) return parseFloat(s.replace(/[^\d]/g, "")) || 0;
    return parseFloat(v) || 0;
}

function rupiah(n) {
    const x = Math.round(toNumber(n));
    return "Rp. " + x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Format rupiah realtime
$(document).on("input", ".input-rupiah", function () {
    $(this).val(rupiah($(this).val()));
    calcHargaTotalAwal();
    calcDiskon();
});

function calcHargaTotalAwal() {
    // kamu bisa ubah ini kalau total awal = qty * harga baru.
    // Tapi screenshot kamu menunjukkan total awal seperti field sendiri.
    // Aku bikin default: total = qty * harga beli satuan baru.
    const qty = parseInt($("#jumlah_obat").val() || "0", 10) || 0;
    const hargaBaru = toNumber($("#harga_satuan_obat_baru").val());
    const total = qty * hargaBaru;

    $("#harga_total_awal_obat").val(rupiah(total));
}

function calcDiskon() {
    const totalAwal = toNumber($("#harga_total_awal_obat").val());
    const diskonPct = parseFloat($("#diskon_obat_persen").val() || "0") || 0;
    const diskonNominal = (totalAwal * diskonPct) / 100;

    $("#harga_total_diskon_obat").val(rupiah(diskonNominal));
}

$("#jumlah_obat").on("input", function () {
    calcHargaTotalAwal();
    calcDiskon();
});

$("#diskon_obat_persen").on("input", function () {
    calcDiskon();
});

// SINGLE SOURCE OF TRUTH
function syncTransaksiObatUI() {
    const val = ($("#transaksi_obat").val() || "").trim();
    const isReturn = val === "Return";

    // Return-only: total stok item
    $("#wrapper_total_stok").toggleClass("hidden", !isReturn);

    // Required rules: expired & batch kamu mau wajib untuk Restock juga
    const mustRequire = val === "Return" || val === "Restock";
    $("#expired_date_obat").prop("required", mustRequire);
    $("#batch_obat").prop("required", mustRequire);

    // Label jumlah
    $("#label_jumlah_obat").text(
        isReturn ? "Jumlah Return *" : "Jumlah Obat *",
    );
}

$(document).on("change", "#transaksi_obat", function () {
    syncTransaksiObatUI();

    const obatId = $("#obat_id").val();
    if (obatId) window.fillObatMeta?.(obatId);
});

$(document).on("change", "#obat_id", function () {
    const obatId = $(this).val();

    $("#transaksi_obat").prop("disabled", !obatId);

    // default: Restock saat pilih obat
    if (obatId && !$("#transaksi_obat").val()) {
        $("#transaksi_obat").val("Restock");
    }

    syncTransaksiObatUI();

    if (obatId) window.fillObatMeta?.(obatId);
});

// Pastikan init
syncTransaksiObatUI();
calcHargaTotalAwal();
calcDiskon();

/* =========================================================
   BHP UI: Transaksi Restock/Return (NEW)
========================================================= */
function syncTransaksiBhpUI() {
    const val = ($("#transaksi_bhp").val() || "").trim();

    $("#bhp_return_only_fields").toggleClass("hidden", val !== "Return");
    $("#bhp_restock_only_fields").toggleClass("hidden", val !== "Restock");

    // Return requires expired + batch (like your design)
    $("#expired_date_bhp").prop("required", val === "Return");
    $("#batch_bhp").prop("required", val === "Return");
}

// When bhp changes: enable transaksi + default Restock
$(document).on("change", "#bhp_id", function () {
    const bhpId = $(this).val();

    if (!bhpId) {
        $("#transaksi_bhp").val("").prop("disabled", true);
        syncTransaksiBhpUI();
        return;
    }

    $("#transaksi_bhp").prop("disabled", false);

    if (!$("#transaksi_bhp").val()) {
        $("#transaksi_bhp").val("Restock").trigger("change");
    } else {
        syncTransaksiBhpUI();
    }
});

$(document).on("change", "#transaksi_bhp", function () {
    syncTransaksiBhpUI();

    // optional: refresh meta like obat
    const bhpId = $("#bhp_id").val();
    if (bhpId && window.fillBhpMeta) window.fillBhpMeta(bhpId);
});

/* =========================================================
   MODAL INIT SAFE
========================================================= */
$(function () {
    const elModal = document.getElementById("modalCreateRestockReturn");
    const modalCreate = elModal
        ? new Modal(elModal, { backdrop: "static", closable: false })
        : null;

    $("#btn-open-modal-create").on("click", function () {
        modalCreate?.show();

        // Default states when modal opens
        $("#transaksi_obat").val("").prop("disabled", true);
        $("#transaksi_bhp").val("").prop("disabled", true);

        syncTransaksiObatUI();
        syncTransaksiBhpUI();
    });

    $("#btn-close-modal-create, #btn-cancel-modal-create").on(
        "click",
        function () {
            modalCreate?.hide();
        },
    );
});

/* =========================================================
   DEPOT (FIX tomselect null crash)
========================================================= */
$(function () {
    const $depotContainer = $("#depot-container-restock");
    if (!$depotContainer.length) return;

    const $depotTemplate = $depotContainer
        .find(".depot-row")
        .first()
        .clone(false);

    function initNamaDepotSelect($row) {
        const el = $row.find(".select-nama-depot")[0];
        const btnClear = $row.find(".btn-clear-depot")[0];
        if (!el) return; // ✅ prevents null crash

        if (el.tomselect) el.tomselect.destroy(); // ✅ safe destroy

        const urlIndex = el.dataset.urlIndex;
        const urlStore = el.dataset.urlStore;

        const ts = new TomSelect(el, {
            valueField: "id",
            labelField: "nama_depot",
            searchField: "nama_depot",
            preload: true,
            maxItems: 1,
            placeholder: "Pilih / ketik nama depot",
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
                if (btnClear) btnClear.classList.toggle("hidden", !value);
            },
        });

        if (btnClear) {
            btnClear.onclick = function () {
                ts.clear();
                btnClear.classList.add("hidden");
            };
        }
    }

    function initTipeDepotSelect($row) {
        const el = $row.find(".select-tipe-depot")[0];
        const btnClear = $row.find(".btn-clear-tipe-depot")[0];
        if (!el) return; // ✅ prevents null crash

        if (el.tomselect) el.tomselect.destroy(); // ✅ safe destroy

        const urlIndex = el.dataset.urlIndex;
        const urlStore = el.dataset.urlStore;

        const ts = new TomSelect(el, {
            valueField: "id",
            labelField: "nama_tipe_depot",
            searchField: "nama_tipe_depot",
            preload: true,
            maxItems: 1,
            placeholder: "Pilih / ketik tipe depot",
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
                if (btnClear) btnClear.classList.toggle("hidden", !value);
            },
        });

        if (btnClear) {
            btnClear.onclick = function () {
                ts.clear();
                btnClear.classList.add("hidden");
            };
        }
    }

    // init first row
    const $firstRow = $depotContainer.find(".depot-row").first();
    initNamaDepotSelect($firstRow);
    initTipeDepotSelect($firstRow);

    // add row
    $("#btn-add-depot-restock").on("click", function () {
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

    // remove row
    $(document).on(
        "click",
        "#depot-container-restock .btn-remove-depot",
        function () {
            const $rows = $depotContainer.find(".depot-row");
            if ($rows.length <= 1) {
                // reset instead of delete last row
                const $row = $rows.first();
                const depotEl = $row.find(".select-nama-depot")[0];
                if (depotEl && depotEl.tomselect) depotEl.tomselect.clear();

                const tipeEl = $row.find(".select-tipe-depot")[0];
                if (tipeEl && tipeEl.tomselect) tipeEl.tomselect.clear();

                $row.find(".btn-clear-depot").addClass("hidden");
                $row.find(".btn-clear-tipe-depot").addClass("hidden");
                $row.find(".input-stok-depot").val(0);
                return;
            }

            $(this).closest(".depot-row").remove();
        },
    );
});
