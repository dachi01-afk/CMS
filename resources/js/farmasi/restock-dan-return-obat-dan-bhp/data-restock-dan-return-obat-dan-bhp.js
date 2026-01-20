import $ from "jquery";
import { Modal } from "flowbite";
// TomSelect & Swal diasumsikan sudah tersedia global (seperti di project kamu)
// window.TomSelect, window.Swal

$(function () {
    // =========================================
    // CSRF untuk semua request jQuery
    // =========================================
    $.ajaxSetup({
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    // =========================================
    // DATATABLES
    // =========================================
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
                "bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
            );
            $("td", row).addClass("px-6 py-4 text-gray-900 dark:text-white");
        },
    });

    // custom search
    $("#customSearch").on("keyup", function () {
        table.search(this.value).draw();
    });

    // ✅ FIX: page length sesuai id di view -> #restock_pageLength
    $("#restock_pageLength").on("change", function () {
        table.page.len(parseInt($(this).val(), 10)).draw();
    });

    // Pagination custom
    const $info = $("#custom_customInfo");
    const $pagination = $("#custom_Pagination");

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${
                info.recordsDisplay
            } data (Halaman ${currentPage} dari ${totalPages})`
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
});

$(function () {
    // ==========================================================
    // MODAL CREATE + FORM LOGIC (PURE jQuery AJAX)
    // ==========================================================
    const elModal = document.getElementById("modalCreateRestockReturn");
    const modalCreate = elModal
        ? new Modal(elModal, { backdrop: "static", closable: false })
        : null;

    const $form = $("#formCreateRestockReturn");
    const $btnSubmit = $("#btn-submit-create");

    // --- helpers ---
    function resetErrors() {
        $form.find("[data-error]").text("");
    }

    function rupiah(n) {
        const x = Math.round(n || 0);
        return "Rp" + x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function toNumber(v) {
        if (!v) return 0;
        return parseFloat(String(v).replace(/[^\d]/g, "")) || 0;
    }

    function calcTotalObat() {
        const qty = parseInt($("#jumlah_obat").val() || "0", 10);
        const harga = toNumber($("#harga_satuan_obat").val());
        $("#harga_total_awal_obat").val(rupiah(qty * harga));
    }

    function calcTotalBhp() {
        const qty = parseInt($("#jumlah_bhp").val() || "0", 10);
        const harga = toNumber($("#harga_satuan_bhp").val());
        $("#harga_total_awal_bhp").val(rupiah(qty * harga));
    }

    $("#jumlah_obat, #harga_satuan_obat").on("input", calcTotalObat);
    $("#jumlah_bhp, #harga_satuan_bhp").on("input", calcTotalBhp);

    // --- tab logic (punyamu, aku pertahankan) ---
    function setRequired($scope, required) {
        $scope.find("input, select, textarea").each(function () {
            const $el = $(this);
            if ($el.is(":button") || $el.attr("type") === "hidden") return;

            if (required) {
                if ($el.data("was-required") === true)
                    $el.prop("required", true);
            } else {
                if ($el.prop("required")) $el.data("was-required", true);
                $el.prop("required", false);
            }
        });
    }

    function setActiveTab(tab) {
        const $tabObat = $("#tab-obat");
        const $tabBhp = $("#tab-bhp");
        const $panelObat = $("#panel-obat");
        const $panelBhp = $("#panel-bhp");

        if (tab === "obat") {
            $tabObat
                .addClass("border-pink-500 text-gray-900 dark:text-white")
                .removeClass(
                    "border-transparent text-gray-500 dark:text-gray-400"
                );
            $tabBhp
                .addClass("border-transparent text-gray-500 dark:text-gray-400")
                .removeClass("border-pink-500 text-gray-900 dark:text-white");

            $panelObat.removeClass("hidden");
            $panelBhp.addClass("hidden");

            setRequired($panelObat, true);
            setRequired($panelBhp, false);
        } else {
            $tabBhp
                .addClass("border-pink-500 text-gray-900 dark:text-white")
                .removeClass(
                    "border-transparent text-gray-500 dark:text-gray-400"
                );
            $tabObat
                .addClass("border-transparent text-gray-500 dark:text-gray-400")
                .removeClass("border-pink-500 text-gray-900 dark:text-white");

            $panelBhp.removeClass("hidden");
            $panelObat.addClass("hidden");

            setRequired($panelBhp, true);
            setRequired($panelObat, false);
        }
    }

    $("#tab-obat").on("click", () => setActiveTab("obat"));
    $("#tab-bhp").on("click", () => setActiveTab("bhp"));

    // ==========================================================
    // FORM META LOAD (kategori, satuan, depot default)
    // ==========================================================
    let DEFAULT_DEPOT_ID = null;

    function loadFormMeta(done) {
        $.get("/farmasi/restock-return/form-meta")
            .done(function (meta) {
                DEFAULT_DEPOT_ID = meta.default_depot_id || null;

                // kategori obat
                $("#kategori_obat_id")
                    .empty()
                    .append(`<option value="">Pilih kategori...</option>`);
                (meta.kategori_obat || []).forEach((k) => {
                    $("#kategori_obat_id").append(
                        `<option value="${k.id}">${k.nama}</option>`
                    );
                });

                // kategori bhp
                $("#kategori_bhp_id")
                    .empty()
                    .append(`<option value="">Pilih kategori...</option>`);
                (meta.kategori_bhp || []).forEach((k) => {
                    $("#kategori_bhp_id").append(
                        `<option value="${k.id}">${k.nama}</option>`
                    );
                });

                // satuan (dipakai obat & bhp)
                const satuanOpt = [`<option value="">Pilih satuan...</option>`]
                    .concat(
                        (meta.satuan || []).map(
                            (s) => `<option value="${s.id}">${s.nama}</option>`
                        )
                    )
                    .join("");

                $("#satuan_obat_id").html(satuanOpt);
                $("#satuan_bhp_id").html(satuanOpt);

                // hidden depot_id untuk store
                if ($("#depot_id").length === 0) {
                    $form.append(
                        `<input type="hidden" id="depot_id" name="depot_id" value="${
                            DEFAULT_DEPOT_ID || ""
                        }">`
                    );
                } else {
                    $("#depot_id").val(DEFAULT_DEPOT_ID || "");
                }

                done && done();
            })
            .fail(function () {
                console.error("Gagal load form meta");
                done && done();
            });
    }

    // ==========================================================
    // OBAT & BHP LIST (simple load awal)
    // ==========================================================
    function initObatSelect(done) {
        new TomSelect("#obat_id", {
            valueField: "id",
            labelField: "nama_obat",
            searchField: "nama_obat",
            maxItems: 1,
            preload: true,
            create: true,
            createOnBlur: true,
            openOnFocus: true,

            shouldLoad: function (query) {
                return true;
            },

            load: function (query, callback) {
                $.ajax({
                    url: "/testing-tom-select/data-obat",
                    type: "GET",
                    data: {
                        q: query || "",
                    },
                    success: function (res) {
                        callback(res);
                    },
                    error: function () {
                        callback();
                    },
                });
            },

            onChange: function (value) {
                const data = this.options[value];
                if (data) {
                    console.log("Data Terpilih:", data);

                    $("#kategori_obat_id").val(
                        data.kategori_obat.nama_kategori_obat
                    );
                } else {
                    $("#kategori_obat_id").val("");
                }
            },
        });
    }

    function initBhpSelect(done) {
        $.get("/farmasi/restock-return/bhp", { q: "" })
            .done(function (rows) {
                $("#bhp_id")
                    .empty()
                    .append(`<option value="">Pilih BHP...</option>`);
                (rows || []).forEach((b) => {
                    $("#bhp_id").append(
                        `<option value="${b.id}">${b.nama_barang}</option>`
                    );
                });
                done && done();
            })
            .fail(function () {
                console.error("Gagal load bhp");
                done && done();
            });
    }

    // ==========================================================
    // META OBAT / BHP (harga lama, kategori, satuan, batch/expired)
    // ==========================================================
    $("#obat_id").on("change", function () {
        const id = $(this).val();
        if (!id) return;

        const depotId = $("#depot_id").val() || DEFAULT_DEPOT_ID;

        $.get(`/farmasi/restock-return/obat/${id}/meta`, { depot_id: depotId })
            .done(function (res) {
                const obat = res.obat || {};
                const hist = res.batch_expired || [];

                $("#kategori_obat_id").val(obat.kategori_obat_id || "");
                $("#satuan_obat_id").val(obat.satuan_id || "");

                $("#harga_beli_lama_obat").val(rupiah(obat.harga_beli || 0));
                $("#harga_jual_lama_obat").val(rupiah(obat.harga_jual || 0));
                $("#harga_jual_otc_lama_obat").val(
                    rupiah(obat.harga_jual_otc || 0)
                );

                // default harga satuan = harga beli
                $("#harga_satuan_obat").val(rupiah(obat.harga_beli || 0));
                calcTotalObat();

                // batch unique
                const batches = [
                    ...new Set(hist.map((x) => x.batch).filter(Boolean)),
                ];
                $("#batch_obat")
                    .empty()
                    .append(`<option value="">Pilih batch...</option>`);
                batches.forEach((b) =>
                    $("#batch_obat").append(
                        `<option value="${b}">${b}</option>`
                    )
                );

                // expired unique
                const exp = [
                    ...new Set(hist.map((x) => x.expired_date).filter(Boolean)),
                ];
                $("#expired_date_obat")
                    .empty()
                    .append(`<option value="">Pilih expired...</option>`);
                exp.forEach((d) =>
                    $("#expired_date_obat").append(
                        `<option value="${d}">${d}</option>`
                    )
                );
            })
            .fail(function (xhr) {
                console.error("Gagal ambil meta obat", xhr);
            });
    });

    $("#bhp_id").on("change", function () {
        const id = $(this).val();
        if (!id) return;

        const depotId = $("#depot_id").val() || DEFAULT_DEPOT_ID;

        $.get(`/farmasi/restock-return/bhp/${id}/meta`, { depot_id: depotId })
            .done(function (res) {
                const bhp = res.bhp || {};
                const hist = res.batch_expired || [];

                $("#kategori_bhp_id").val(bhp.kategori_bhp_id || "");
                $("#satuan_bhp_id").val(bhp.satuan_id || "");

                $("#harga_beli_lama_bhp").val(rupiah(bhp.harga_beli || 0));
                $("#harga_satuan_bhp").val(rupiah(bhp.harga_beli || 0));
                calcTotalBhp();

                const batches = [
                    ...new Set(hist.map((x) => x.batch).filter(Boolean)),
                ];
                $("#batch_bhp")
                    .empty()
                    .append(`<option value="">Pilih batch...</option>`);
                batches.forEach((b) =>
                    $("#batch_bhp").append(`<option value="${b}">${b}</option>`)
                );

                const exp = [
                    ...new Set(hist.map((x) => x.expired_date).filter(Boolean)),
                ];
                $("#expired_date_bhp")
                    .empty()
                    .append(`<option value="">Pilih expired...</option>`);
                exp.forEach((d) =>
                    $("#expired_date_bhp").append(
                        `<option value="${d}">${d}</option>`
                    )
                );
            })
            .fail(function (xhr) {
                console.error("Gagal ambil meta bhp", xhr);
            });
    });

    // ==========================================================
    // SUPPLIER TOMSELECT (jQuery AJAX version)
    // ==========================================================
    let supplierSelect = null;
    let supplierJustCreatedId = null;

    function getSupplierDataset() {
        const el = document.getElementById("supplier_id");
        return {
            el,
            urlIndex: el?.dataset.urlIndex,
            urlStore: el?.dataset.urlStore,
            urlDelete: el?.dataset.urlDelete,
            urlUpdate: el?.dataset.urlUpdate,
            urlShowTpl: el?.dataset.urlShow,
        };
    }

    function showSupplierDetailCreate(data, isCreate = false) {
        $("#supplier-detail").removeClass("hidden");
        $("#btn-clear-supplier").removeClass("hidden");

        $("#supplier_kontak_person").val(data.kontak_person || "");
        $("#supplier_no_hp").val(data.no_hp || "");
        $("#supplier_email").val(data.email || "");
        $("#supplier_alamat").val(data.alamat || "");
        $("#supplier_keterangan").val(data.keterangan || "");

        if (!isCreate) {
            $("#supplier-detail input, #supplier-detail textarea")
                .prop("readonly", true)
                .prop("disabled", false);
        } else {
            $("#supplier-detail input, #supplier-detail textarea")
                .prop("readonly", false)
                .prop("disabled", false);
        }
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
        if (!el) return;
        if (supplierSelect) return;

        supplierSelect = new TomSelect(el, {
            valueField: "id",
            labelField: "nama_supplier",
            searchField: "nama_supplier",
            preload: true,
            maxOptions: 10,
            persist: true,

            create: function (input, callback) {
                $.post(urlStore, { nama_supplier: input })
                    .done(function (res) {
                        supplierJustCreatedId = String(res.id);
                        callback(res);
                        showSupplierDetailCreate(res, true);
                    })
                    .fail(function (xhr) {
                        console.error("CREATE SUPPLIER ERROR", xhr);
                        if (window.Swal) {
                            Swal.fire({
                                icon: "error",
                                title: "Gagal",
                                text: "Gagal menambahkan supplier",
                            });
                        } else {
                            alert("Gagal menambahkan supplier");
                        }
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
                ) {
                    $("#supplier-detail input, #supplier-detail textarea")
                        .prop("readonly", false)
                        .prop("disabled", false);
                    return;
                }

                const urlShow = urlShowTpl.replace("__ID__", value);
                $.get(urlShow)
                    .done(function (res) {
                        supplierJustCreatedId = null;
                        showSupplierDetailCreate(res, false);
                    })
                    .fail(function () {
                        clearSupplierDetailCreate();
                    });
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

    // ==========================================================
    // RESET FORM
    // ==========================================================
    function resetForm() {
        $form[0].reset();
        resetErrors();

        if (supplierSelect) supplierSelect.clear(true);
        supplierJustCreatedId = null;
        clearSupplierDetailCreate();

        // default tab
        setActiveTab("obat");

        // kosongkan select batch/expired
        $("#batch_obat, #expired_date_obat").html(
            `<option value="">Pilih...</option>`
        );
        $("#batch_bhp, #expired_date_bhp").html(
            `<option value="">Pilih...</option>`
        );

        // reset harga
        $(
            "#harga_beli_lama_obat, #harga_jual_lama_obat, #harga_jual_otc_lama_obat"
        ).val("");
        $("#harga_beli_lama_bhp").val("");
        $("#harga_total_awal_obat, #harga_total_awal_bhp").val("");
    }

    // ==========================================================
    // OPEN / CLOSE MODAL
    // ==========================================================
    $("#btn-open-modal-create").on("click", function () {
        resetForm();
        modalCreate?.show();

        // load meta + list + tomselect
        setTimeout(() => {
            initSupplierSelectCreate();
            loadFormMeta(() => {
                initObatSelect();
                initBhpSelect();
            });
        }, 50);
    });

    $("#btn-close-modal-create, #btn-cancel-modal-create").on(
        "click",
        function () {
            modalCreate?.hide();
            resetForm();
        }
    );

    // ==========================================================
    // SUBMIT: update supplier (optional) + create transaksi (items[])
    // ==========================================================
    $form.on("submit", function (e) {
        e.preventDefault();
        resetErrors();

        const urlTransaksi = $form.data("url");
        const supplierId = $("#supplier_id").val();
        const depotId = $("#depot_id").val() || DEFAULT_DEPOT_ID;

        const { urlUpdate } = getSupplierDataset();

        $btnSubmit.prop("disabled", true).text("Menyimpan...");

        // Build items dari tab aktif
        const tabAktif = $("#panel-obat").hasClass("hidden") ? "bhp" : "obat";
        let items = [];

        if (tabAktif === "obat") {
            items.push({
                type: "obat",
                obat_id: $("#obat_id").val(),
                batch: $("#batch_obat").val() || null,
                expired_date: $("#expired_date_obat").val() || null,
                jumlah: parseInt($("#jumlah_obat").val() || "0", 10),
                satuan_id: $("#satuan_obat_id").val() || null,
                harga_beli: toNumber($("#harga_satuan_obat").val()),
                depot_id: depotId,
                keterangan: $("#keterangan_item_obat").val() || null,
            });
        } else {
            items.push({
                type: "bhp",
                bhp_id: $("#bhp_id").val(),
                batch: $("#batch_bhp").val() || null,
                expired_date: $("#expired_date_bhp").val() || null,
                jumlah: parseInt($("#jumlah_bhp").val() || "0", 10),
                satuan_id: $("#satuan_bhp_id").val() || null,
                harga_beli: toNumber($("#harga_satuan_bhp").val()),
                depot_id: depotId,
                keterangan: $("#keterangan_item_bhp").val() || null,
            });
        }

        const payload = {
            tanggal_transaksi: $form.find('[name="tanggal_transaksi"]').val(),
            jenis_transaksi: $form.find('[name="jenis_transaksi"]').val(),
            supplier_id: supplierId || null,
            nomor_faktur: $form.find('[name="nomor_faktur"]').val() || null,
            keterangan: $form.find('[name="keterangan"]').val() || null,
            items: items,
        };

        // STEP A: update supplier (kalau ada supplier dipilih)
        const doUpdateSupplier = function (next) {
            if (!supplierId) return next();

            const payloadSupplier = {
                id: supplierId,
                kontak_person: $("#supplier_kontak_person").val() || null,
                no_hp: $("#supplier_no_hp").val() || null,
                email: $("#supplier_email").val() || null,
                alamat: $("#supplier_alamat").val() || null,
                keterangan: $("#supplier_keterangan").val() || null,
            };

            $.post(urlUpdate, payloadSupplier)
                .done(function () {
                    next();
                })
                .fail(function (xhr) {
                    console.error("Update supplier gagal", xhr);
                    next(); // tetap lanjut create transaksi (sesuaikan kalau mau stop)
                });
        };

        // STEP B: create transaksi
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
                                .text(errors[key][0] ?? "Tidak valid");
                        });
                        return;
                    }

                    alert("Terjadi kesalahan saat menyimpan transaksi.");
                    console.error(xhr);
                })
                .always(function () {
                    $btnSubmit.prop("disabled", false).text("Simpan");
                });
        };

        doUpdateSupplier(doCreateTransaksi);
    });
});
