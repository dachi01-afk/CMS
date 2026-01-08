import $ from "jquery";
import axios from "axios";
import { Modal } from "flowbite";

$(function () {
    const table = $("#table-restock-return").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        searching: true,
        ajax: {
            url: "/farmasi/restock-return/get-data-restock-dan-return-barang-dan-obat", // sesuaikan route kamu
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

    // custom search (seperti input assist.id)
    $("#customSearch").on("keyup", function () {
        table.search(this.value).draw();
    });

    // Page length
    $("#custom_pageLength").on("change", function () {
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
    // =========================
    // MODAL INIT
    // =========================
    const elModal = document.getElementById("modalCreateRestockReturn");
    const modalCreate = elModal
        ? new Modal(elModal, { backdrop: "static", closable: false })
        : null;

    const $form = $("#formCreateRestockReturn");
    const $btnSubmit = $("#btn-submit-create");

    // =========================
    // HELPERS
    // =========================
    function resetErrors() {
        $form.find("[data-error]").text("");
    }

    // penting: clear tomselect juga, bukan cuma form.reset()
    function resetForm() {
        $form[0].reset();
        resetErrors();

        // clear tomselect value + state
        if (supplierSelect) {
            supplierSelect.clear(true);
            // jangan remove option, cukup clear value
        }
        supplierJustCreatedId = null;

        clearSupplierDetailCreate();

        // default tab: OBAT
        setActiveTab("obat");
    }

    // =========================
    // TAB OBAT / BHP
    // =========================
    function setRequired($scope, required) {
        $scope.find("input, select, textarea").each(function () {
            const $el = $(this);
            // jangan ubah tombol/hidden input csrf
            if ($el.is(":button") || $el.attr("type") === "hidden") return;

            // toggle required hanya kalau memang ada required attribute di HTML
            if (required) {
                if ($el.data("was-required") === true)
                    $el.prop("required", true);
            } else {
                // simpan jejak required asli
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

            // ✅ hanya panel aktif yang required
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

    // =========================
    // SUPPLIER TOMSELECT
    // =========================
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
            urlShowTpl: el?.dataset.urlShow, // ".../__ID__"
        };
    }

    function initSupplierSelectCreate() {
        const { el, urlIndex, urlStore, urlShowTpl } = getSupplierDataset();
        if (!el) return;

        // jangan init ulang
        if (supplierSelect) return;

        supplierSelect = new TomSelect(el, {
            valueField: "id",
            labelField: "nama_supplier",
            searchField: "nama_supplier",
            preload: true,
            maxOptions: 10,
            persist: true,

            create: function (input, callback) {
                axios
                    .post(urlStore, { nama_supplier: input })
                    .then((res) => {
                        supplierJustCreatedId = String(res.data.id);

                        // masukkan option + select
                        callback(res.data);

                        // ✅ PAKSA editable & tidak disabled saat create baru
                        showSupplierDetailCreate(res.data, true);
                    })
                    .catch((err) => {
                        console.error("CREATE SUPPLIER ERROR", err);
                        Swal.fire({
                            icon: "error",
                            title: "Gagal",
                            text: "Gagal menambahkan supplier",
                        });
                        callback();
                    });
            },

            load: function (query, callback) {
                axios
                    .get(urlIndex, { params: { q: query } })
                    .then((res) => callback(res.data))
                    .catch(() => callback());
            },

            onChange: function (value) {
                if (!value) {
                    supplierJustCreatedId = null;
                    clearSupplierDetailCreate();
                    return;
                }

                // ✅ kalau value hasil CREATE barusan → jangan fetch ulang dan jangan readonly
                if (
                    supplierJustCreatedId &&
                    String(value) === String(supplierJustCreatedId)
                ) {
                    // pastikan tidak readonly/disabled (guard tambahan)
                    $("#supplier-detail input, #supplier-detail textarea")
                        .prop("readonly", false)
                        .prop("disabled", false);
                    return;
                }

                // existing → fetch detail & readonly
                const urlShow = urlShowTpl.replace("__ID__", value);

                axios
                    .get(urlShow)
                    .then((res) => {
                        supplierJustCreatedId = null;
                        showSupplierDetailCreate(res.data, false);
                    })
                    .catch((err) => {
                        console.error("GET SUPPLIER DETAIL ERROR", err);
                        clearSupplierDetailCreate();
                    });
            },
        });

        $("#btn-clear-supplier").on("click", function () {
            supplierSelect.clear(true);
            supplierJustCreatedId = null;
            clearSupplierDetailCreate();
        });
    }

    function showSupplierDetailCreate(data, isCreate = false) {
        $("#supplier-detail").removeClass("hidden");
        $("#btn-clear-supplier").removeClass("hidden");

        $("#supplier_kontak_person").val(data.kontak_person || "");
        $("#supplier_no_hp").val(data.no_hp || "");
        $("#supplier_email").val(data.email || "");
        $("#supplier_alamat").val(data.alamat || "");
        $("#supplier_keterangan").val(data.keterangan || "");

        // ✅ IMPORTANT: jangan pernah disabled, cuma readonly
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

    // =========================
    // OPEN / CLOSE MODAL
    // =========================
    $("#btn-open-modal-create").on("click", function () {
        resetForm();
        modalCreate?.show();
        setTimeout(() => initSupplierSelectCreate(), 50);
    });

    $("#btn-close-modal-create, #btn-cancel-modal-create").on(
        "click",
        function () {
            modalCreate?.hide();
            resetForm();
        }
    );

    // =========================
    // SUBMIT (2 STEP)
    // 1) update supplier (kalau ada supplier_id)
    // 2) create transaksi stok
    // =========================
    $form.on("submit", async function (e) {
        e.preventDefault();
        resetErrors();

        const urlTransaksi = $form.data("url");
        const formData = new FormData($form[0]);

        const supplierId = $("#supplier_id").val();
        const { urlUpdate } = getSupplierDataset();

        $btnSubmit.prop("disabled", true).text("Menyimpan...");

        try {
            // STEP A: update supplier detail (sesuai alur kamu)
            if (supplierId) {
                const payloadSupplier = {
                    id: supplierId,
                    kontak_person: $("#supplier_kontak_person").val() || null,
                    no_hp: $("#supplier_no_hp").val() || null,
                    email: $("#supplier_email").val() || null,
                    alamat: $("#supplier_alamat").val() || null,
                    keterangan: $("#supplier_keterangan").val() || null,
                };

                await axios.post(urlUpdate, payloadSupplier, {
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                });
            }

            // STEP B: create transaksi stok
            const res = await axios.post(urlTransaksi, formData, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });

            modalCreate?.hide();
            resetForm();

            if ($.fn.DataTable.isDataTable("#table-restock-return")) {
                $("#table-restock-return").DataTable().ajax.reload(null, false);
            }

            if (res.data?.redirect_url) {
                window.location.href = res.data.redirect_url;
            }
        } catch (err) {
            if (err.response?.status === 422) {
                const errors = err.response.data.errors || {};
                Object.keys(errors).forEach((key) => {
                    $form
                        .find(`[data-error="${key}"]`)
                        .text(errors[key][0] ?? "Tidak valid");
                });
                return;
            }

            alert("Terjadi kesalahan saat menyimpan transaksi.");
            console.error(err);
        } finally {
            $btnSubmit.prop("disabled", false).text("Simpan");
        }
    });
});
