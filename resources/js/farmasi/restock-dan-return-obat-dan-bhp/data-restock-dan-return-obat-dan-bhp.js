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
                "bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600",
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
            } data (Halaman ${currentPage} dari ${totalPages})`,
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
    const elModal = document.getElementById("modalCreateRestockReturn");
    const modalCreate = elModal
        ? new Modal(elModal, { backdrop: "static", closable: false })
        : null;

    const $form = $("#formCreateRestockReturn");
    const $btnSubmit = $("#btn-submit-create");

    function rupiah(n) {
        if (n === null || n === undefined || n === "") return "Rp. 0";

        // Ambil angka murni, lalu bulatkan (misal 33.78 jadi 34)
        const x = Math.round(toNumber(n));

        // Format ke string dengan titik ribuan
        return "Rp. " + x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function toNumber(v) {
        if (!v) return 0;
        if (typeof v === "number") return v;

        // Jika string mengandung "Rp", hapus semua kecuali angka
        if (String(v).includes("Rp")) {
            return parseFloat(String(v).replace(/[^\d]/g, "")) || 0;
        }

        // Jika angka desimal murni dari API (misal: 33.783), langsung parse
        return parseFloat(v) || 0;
    }

    // Auto format saat mengetik di input dengan class .input-rupiah
    $(document).on("input", ".input-rupiah", function () {
        let val = $(this).val();
        $(this).val(rupiah(val));
    });

    // Proteksi: Rp. tidak bisa dihapus dan kursor selalu di belakang
    $(document).on("click focus keyup", ".input-rupiah", function () {
        if (this.selectionStart < 4) {
            this.setSelectionRange(4, 4);
        }
    });

    function resetErrors() {
        $form.find("[data-error]").text("");
    }

    function calcTotalObat() {
        // Ambil nilai dari Harga Satuan Obat (Harga Beli Baru)
        const hargaSatuanObatBaru =
            $("#harga_satuan_obat_baru").val() || "Rp. 0";
        $("#harga_jual_baru_obat").val() || "Rp. 0";
        $("#harga_jual_otc_baru_obat").val() || "Rp. 0";

        // Set nilainya ke Harga Total Awal secara langsung
        $("#harga_total_awal_obat").val(hargaSatuanObatBaru);
    }

    function toggleTransactionMode() {
        const jenisTransaksi = $("#jenis_transaksi").val() || "";
        const isReturn = jenisTransaksi.toLowerCase().includes("return"); // Deteksi kata 'return'
        const $wrapperStok = $("#total_stok_item").closest("div");
        const $labelJumlah = $("label[for='jumlah_obat']");

        if (isReturn) {
            $wrapperStok.removeClass("hidden");
            $("#total_stok_item")
                .prop("readonly", true)
                .prop("disabled", false);

            // Ubah Label
            $labelJumlah.text("Jumlah Return *");
        } else {
            $wrapperStok.addClass("hidden");
            $("#total_stok_item").val(""); // Kosongkan visual

            $labelJumlah.text("Jumlah Obat / Restock *");
        }
    }

    $("#jenis_transaksi").on("change", function () {
        toggleTransactionMode();

        const obatId = $("#obat_id").val();
        if (obatId) {
            fillObatMeta(obatId);
        }
    });

    function setRequired($scope, required) {
        $scope.find("input, select, textarea").each(function () {
            const $el = $(this);
            if ($el.is(":button") || $el.attr("type") === "hidden") return;

            if ($el.attr("id") === "total_stok_item") return;

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
        const $panelObat = $("#panel-obat");

        if (tab === "obat") {
            $tabObat
                .addClass("border-pink-500 text-gray-900 dark:text-white")
                .removeClass(
                    "border-transparent text-gray-500 dark:text-gray-400",
                );
            $panelObat.removeClass("hidden");
            setRequired($panelObat, true);
        }
    }

    $("#tab-obat").on("click", () => setActiveTab("obat"));

    let DEFAULT_DEPOT_ID = null;

    function loadFormMeta(done) {
        $.get("/farmasi/restock-return/form-meta")
            .done(function (meta) {
                DEFAULT_DEPOT_ID = meta.default_depot_id || null;

                // Render Jenis Transaksi
                const $jtSelect = $("#jenis_transaksi");
                $jtSelect
                    .empty()
                    .append('<option value="">-- Pilih --</option>');

                if (meta.jenis_transaksi && meta.jenis_transaksi.length > 0) {
                    meta.jenis_transaksi.forEach((item) => {
                        $jtSelect.append(
                            `<option value="${item.value}">${item.label}</option>`,
                        );
                    });
                }

                // Render Satuan
                const satuanOpt = [`<option value="">Pilih satuan...</option>`]
                    .concat(
                        (meta.satuan || []).map(
                            (s) => `<option value="${s.id}">${s.nama}</option>`,
                        ),
                    )
                    .join("");
                $("#satuan_obat_id").html(satuanOpt);

                done && done();
            })
            .fail(function (xhr) {
                console.error("Gagal load meta", xhr);
                done && done();
            });
    }

    let obatSelect = null;
    let depotSelect = null;

    function initObatSelect() {
        if (obatSelect) return;

        obatSelect = new TomSelect("#obat_id", {
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
                    $(
                        "#kategori_obat_id, #satuan_obat_id, #total_stok_item",
                    ).val("");
                    $(
                        "#harga_beli_satuan_obat_lama, #harga_jual_lama_obat, #harga_jual_otc_lama_obat",
                    ).val("");
                    $("#batch_obat, #expired_date_obat").html(
                        `<option value="">Pilih...</option>`,
                    );
                    return;
                }
                fillObatMeta(value);
            },
        });
    }

    function initDepotSelect() {
        if (depotSelect) return;

        depotSelect = new TomSelect("#depot_id", {
            valueField: "id",
            labelField: "nama_depot",
            searchField: "nama_depot",
            maxItems: 1,
            preload: true,
            load: function (query, callback) {
                // Ambil ID obat yang sedang dipilih user saat ini
                const obatId = $("#obat_id").val(); // Pastikan ID select obatmu benar

                $.get("/farmasi/restock-return/get-data-depot", {
                    q: query || "",
                    obat_id: obatId, // Kirim ID obat ke server
                })
                    .done((res) => callback(res))
                    .fail(() => callback());
            },
            onChange: function (value) {
                const data = this.options[value];
                if (data) {
                    // Tampilkan info stok di bawah select
                    $("#info-stok-depot").removeClass("hidden");
                    $("#nilai-stok").text(data.stok_obat);
                } else {
                    $("#info-stok-depot").addClass("hidden");
                }
            },
        });
    }

    function fillObatMeta(id) {
        if (!id) return;

        const depotId = $("#depot_id").val() || DEFAULT_DEPOT_ID;
        const jenisTransaksi = $("#jenis_transaksi").val() || "";
        const isReturn = jenisTransaksi.toLowerCase().includes("return");

        $.get(`/farmasi/restock-return/obat/${id}/meta`, { depot_id: depotId })
            .done(function (res) {
                console.log(res);

                // 1. Kategori & Satuan
                $("#kategori_obat_id").val(res.nama_kategori || "");
                if ($("#kategori_obat_id_hidden").length)
                    $("#kategori_obat_id_hidden").val(res.kategori_id || "");
                $("#satuan_obat_id")
                    .val(res.nama_satuan || "")
                    .trigger("change");

                // 2. Data Harga Lama (Readonly)
                $("#harga_beli_satuan_obat_lama").val(
                    rupiah(res.harga_beli_satuan_obat_lama || 0),
                );
                $("#harga_jual_lama_obat").val(
                    rupiah(res.harga_jual_lama || 0),
                );
                $("#harga_jual_otc_lama_obat").val(
                    rupiah(res.harga_jual_otc_obat_lama || 0),
                );

                calcTotalObat();
                $("#batch_obat")
                    .empty()
                    .append(`<option value="">Pilih batch...</option>`);
                if (res.batch_lama) {
                    $("#batch_obat").append(
                        `<option value="${res.batch_lama}" selected>${res.batch_lama}</option>`,
                    );
                }
                $("#expired_date_obat")
                    .empty()
                    .append(`<option value="">Pilih expired...</option>`);
                if (res.expired_lama) {
                    $("#expired_date_obat").append(
                        `<option value="${res.expired_lama}" selected>${res.expired_lama}</option>`,
                    );
                }
                if (isReturn) {
                    const stok = res.stok_sekarang || res.jumlah || 0; // Sesuaikan key dari server
                    $("#total_stok_item").val(stok);
                } else {
                    $("#total_stok_item").val("");
                }
            })
            .fail((xhr) => console.error("Gagal ambil meta obat", xhr));
    }

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

    function showSupplierDetailCreate(data, isCreate = false) {
        $("#supplier-detail").removeClass("hidden");
        $("#btn-clear-supplier").removeClass("hidden");
        $("#supplier_kontak_person").val(data.kontak_person || "");
        $("#supplier_no_hp").val(data.no_hp || "");
        $("#supplier_email").val(data.email || "");
        $("#supplier_alamat").val(data.alamat || "");
        $("#supplier_keterangan").val(data.keterangan || "");
        $("#supplier-detail input, #supplier-detail textarea")
            .removeAttr("readonly")
            .prop("readonly", false)
            .prop("disabled", false);
    }

    function clearSupplierDetailCreate() {
        $("#supplier-detail").addClass("hidden");
        $("#btn-clear-supplier").addClass("hidden");
        $("#supplier-detail input, #supplier-detail textarea")
            .val("")
            .removeAttr("readonly") // Hapus paksa
            .prop("readonly", false)
            .prop("disabled", false);
    }

    function initSupplierSelectCreate() {
        const { el, urlIndex, urlStore, urlShowTpl } = getSupplierDataset();
        if (!el || supplierSelect) return;

        supplierSelect = new TomSelect(el, {
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
                        showSupplierDetailCreate(res, true);
                    })
                    .fail(function () {
                        alert("Gagal menambahkan supplier");
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
                    return;
                }
                const urlShow = urlShowTpl.replace("__ID__", value);
                $.get(urlShow)
                    .done((res) => {
                        supplierJustCreatedId = null;
                        showSupplierDetailCreate(res, false);
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

    function resetForm() {
        $form[0].reset();
        resetErrors();
        if (supplierSelect) supplierSelect.clear(true);
        supplierJustCreatedId = null;
        clearSupplierDetailCreate();
        setActiveTab("obat");
        $("#batch_obat, #expired_date_obat").html(
            `<option value="">Pilih...</option>`,
        );
        $(
            "#harga_beli_satuan_obat_lama, #harga_jual_lama_obat, #harga_jual_otc_lama_obat, #total_stok_item",
        ).val("");

        toggleTransactionMode();
    }

    $("#btn-open-modal-create").on("click", function () {
        resetForm();
        modalCreate?.show();
        setTimeout(() => {
            initSupplierSelectCreate();
            loadFormMeta(() => initObatSelect());
            loadFormMeta(() => initDepotSelect());
        }, 50);
    });

    $("#btn-close-modal-create, #btn-cancel-modal-create").on(
        "click",
        function () {
            modalCreate?.hide();
            resetForm();
        },
    );

    $form.on("submit", function (e) {
        e.preventDefault();
        resetErrors();

        const urlTransaksi = $form.data("url");
        const supplierId = $("#supplier_id").val();
        const depotId = $("#depot_id").val() || DEFAULT_DEPOT_ID;
        const { urlUpdate } = getSupplierDataset();

        $btnSubmit.prop("disabled", true).text("Menyimpan...");

        let items = [];
        const tabAktif = !$("#panel-obat").hasClass("hidden") ? "obat" : "bhp";

        if (tabAktif === "obat") {
            items.push({
                type: "obat",
                obat_id: $("#obat_id").val(),
                batch: $("#batch_obat").val() || null,
                expired_date: $("#expired_date_obat").val() || null,
                jumlah: parseInt($("#jumlah_obat").val() || "0", 10),
                satuan_id: $("#satuan_obat_id").val() || null,
                harga_beli: toNumber($("#harga_satuan_obat_baru").val()),
                depot_id: depotId,
                keterangan: $("#keterangan_item_obat").val() || null,
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
                    } else {
                        alert("Terjadi kesalahan saat menyimpan transaksi.");
                        console.error(xhr);
                    }
                })
                .always(function () {
                    $btnSubmit.prop("disabled", false).text("Simpan");
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
                .fail(() => doCreateTransaksi()); // Tetap lanjut walau update supplier gagal
        } else {
            doCreateTransaksi();
        }
    });

    // Trigger: Setiap kali harga satuan diisi, total awal langsung ngikut
    $("#harga_satuan_obat_baru").on("input keyup", calcTotalObat);

    function setActiveTab(tab) {
        const tabs = [
            {
                btn: document.getElementById("tab-obat"),
                panel: document.getElementById("panel-obat"),
            },
            {
                btn: document.getElementById("tab-bhp"),
                panel: document.getElementById("panel-bhp"),
            },
        ];

        tabs.forEach((t) => {
            const active = t.btn?.dataset.tab === tab;

            // button style
            t.btn.classList.toggle("border-pink-500", active);
            t.btn.classList.toggle("text-gray-900", active);
            t.btn.classList.toggle("dark:text-white", active);

            t.btn.classList.toggle("border-transparent", !active);
            t.btn.classList.toggle("text-gray-500", !active);
            t.btn.classList.toggle("dark:text-gray-400", !active);

            // panel visibility
            if (t.panel) t.panel.classList.toggle("hidden", !active);
        });
    }

    // default tab
    setActiveTab("obat");

    document
        .getElementById("tab-obat")
        ?.addEventListener("click", () => setActiveTab("obat"));
    document
        .getElementById("tab-bhp")
        ?.addEventListener("click", () => setActiveTab("bhp"));

    // ====== BHP total calc ======
    const jumlahBhp = document.getElementById("jumlah_bhp");
    const hargaSatuanBhp = document.getElementById("harga_satuan_bhp");
    const hargaTotalAwalBhp = document.getElementById("harga_total_awal_bhp");

    function parseRupiah(val) {
        if (!val) return 0;
        // remove "Rp", dots, spaces; convert comma to dot if needed
        return (
            Number(
                String(val)
                    .replace(/[^0-9,]/g, "")
                    .replace(",", "."),
            ) || 0
        );
    }

    function formatRupiah(num) {
        // keep it simple: use Indonesian formatting
        const n = Number(num || 0);
        return "Rp" + n.toLocaleString("id-ID");
    }

    function recalcBhpTotal() {
        const qty = Number(jumlahBhp?.value || 0);
        const price = parseRupiah(hargaSatuanBhp?.value);
        const total = qty * price;

        if (hargaTotalAwalBhp) hargaTotalAwalBhp.value = formatRupiah(total);
    }

    jumlahBhp?.addEventListener("input", recalcBhpTotal);
    hargaSatuanBhp?.addEventListener("input", recalcBhpTotal);

    // If you already have an "input-rupiah" formatter in your app,
    // this will still work; it just reads the formatted value and parses it.
});
