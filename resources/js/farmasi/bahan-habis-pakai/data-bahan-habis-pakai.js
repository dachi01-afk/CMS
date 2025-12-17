import axios from "axios";
import { Modal } from "flowbite";
import $ from "jquery";

$(function () {
    const table = $("#tabelBahanHabisPakai").DataTable({
        processing: true,
        responsive: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/farmasi/bahan-habis-pakai/get-data-bhp",

        // Buttons dimunculkan tapi dibungkus di div hidden
        dom: "t",
        buttons: [
            {
                extend: "excelHtml5",
                className: "btn-export-excel",
                titleAttr: "Export ke Excel",
                exportOptions: {
                    columns: ":visible:not(:last-child)",
                },
            },
            {
                extend: "csvHtml5",
                className: "btn-export-csv",
                titleAttr: "Export ke CSV",
                exportOptions: {
                    columns: ":visible:not(:last-child)",
                },
            },
            {
                extend: "pdfHtml5",
                className: "btn-export-pdf",
                titleAttr: "Export ke PDF",
                orientation: "landscape",
                pageSize: "A4",
                exportOptions: {
                    columns: ":visible:not(:last-child)",
                },
            },
            {
                extend: "print",
                className: "btn-export-print",
                titleAttr: "Print Tabel",
                exportOptions: {
                    columns: ":visible:not(:last-child)",
                },
            },
        ],

        // ===========================================
        // KOLOM: sesuaikan dengan getDataObat() PHP:
        // kode, nama_obat, farmasi, jenis, kategori,
        // stok, harga_umum, harga_beli, avg_hpp,
        // harga_otc, margin_profit, action
        // ===========================================
        columns: [
            // NO
            {
                data: "kode",
                name: "kode",
            },

            {
                data: "nama_barang",
                name: "nama_barang",
            },

            {
                data: "brand_farmasi",
                name: "brand_farmasi",
                render: function (data, type, row) {
                    const val = data || row.brand_farmasi?.nama_brand || "-";
                    return `<span class="text-xs">${val}</span>`;
                },
            },

            {
                data: "stok",
                name: "stok",
                render: function (data, type, row) {
                    const stok = data ?? row.jumlah ?? 0;
                    const satuan = row.satuabBhp?.nama_satuan_obat || "capsul";

                    let warna =
                        stok === 0
                            ? "bg-red-50 text-red-700 border-red-100"
                            : stok < 10
                            ? "bg-amber-50 text-amber-700 border-amber-100"
                            : "bg-emerald-50 text-emerald-700 border-emerald-100";

                    return `
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] border ${warna}">
                            <i class="fa-solid fa-boxes-stacked mr-1"></i>
                            ${stok} ${satuan}
                        </span>
                    `;
                },
            },

            {
                data: "harga_jual_umum_bhp",
                name: "harga_jual_umum_bhp",
                className: "text-right text-xs font-semibold",
            },

            {
                data: "harga_beli_satuan_bhp",
                name: "harga_beli_satuan_bhp",
                className: "text-right text-xs font-semibold",
            },

            {
                data: "avg_hpp_bhp",
                name: "avg_hpp_bhp",
                className: "text-right text-xs font-semibold",
            },

            {
                data: "harga_otc_bhp",
                name: "harga_otc_bhp",
                className: "text-right text-xs font-semibold",
            },

            {
                data: "margin_profit_bhp",
                name: "margin_profit_bhp",
                className: "text-right text-xs font-semibold",
            },

            // AKSI
            {
                data: "action",
                name: "action",
                searchable: false,
                orderable: false,
                className: "text-center whitespace-nowrap",
            },
        ],

        rowCallback: function (row, data) {
            $(row).addClass(
                "bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
            );
            $("td", row).addClass(
                "px-4 md:px-6 py-3 md:py-4 text-gray-900 dark:text-white align-middle"
            );
        },
    });

    // ==========================
    // GLOBAL SEARCH (input custom)
    // ==========================
    $("#data-obat-search-input").on("keyup", function () {
        table.search(this.value).draw();
    });

    // ==========================
    // CUSTOM PAGINATION & INFO
    // ==========================
    const $info = $("#data-obat-custom-info");
    const $paginate = $("#data-obat-custom-paginate");
    const $perPage = $("#data-obat-page-length");

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${
                info.recordsDisplay
            } data (Halaman ${currentPage} dari ${totalPages})`
        );
        $paginate.empty();

        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";
        $paginate.append(
            `<li><a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-[11px] text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a></li>`
        );

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
            $paginate.append(
                `<li><a href="#" class="page-number flex items-center justify-center px-3 h-8 text-[11px] border ${active}" data-page="${i}">${i}</a></li>`
            );
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 cursor-not-allowed" : "";
        $paginate.append(
            `<li><a href="#" id="btnNext" class="flex items-center justify-center px-3 h-8 text-[11px] text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">Next</a></li>`
        );
    }

    $paginate.on("click", "a", function (e) {
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
        table.page.len(parseInt($(this).val())).draw();
    });

    table.on("draw", updatePagination);
    updatePagination();

    // ==========================
    // EXPORT BUTTONS (trigger Buttons DataTables)
    // pastikan di Blade:
    //  #btn-export-excel, #btn-export-csv, #btn-export-pdf, #btn-export-print
    // ==========================
    $("#btn-export-excel").on("click", function () {
        table.button(".buttons-excel").trigger();
    });

    $("#btn-export-csv").on("click", function () {
        table.button(".buttons-csv").trigger();
    });

    $("#btn-export-pdf").on("click", function () {
        table.button(".buttons-pdf").trigger();
    });

    $("#btn-export-print").on("click", function () {
        table.button(".buttons-print").trigger();
    });

    // ==========================
    // IMPORT (trigger input file)
    // ==========================
    $("#btn-import-obat").on("click", function () {
        $("#input-file-import-obat").trigger("click");
    });

    $("#input-file-import-obat").on("change", function () {
        if (!this.files.length) return;

        if (window.Swal) {
            Swal.fire({
                icon: "question",
                title: "Import Data Obat?",
                text: "Pastikan format file sudah sesuai template.",
                showCancelButton: true,
                confirmButtonText: "Ya, lanjutkan",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#form-import-obat").submit();
                } else {
                    $("#input-file-import-obat").val("");
                }
            });
        } else {
            if (confirm("Import data obat dari file ini?")) {
                $("#form-import-obat").submit();
            } else {
                $("#input-file-import-obat").val("");
            }
        }
    });
});

$(function () {
    const elModalCreate = document.getElementById("modalCreateBhp");
    const modalCreate = elModalCreate
        ? new Modal(elModalCreate, {
              backdrop: "static",
              closable: false,
          })
        : null;

    const $formCreate = $("#formModalCreateBhp");
    const $depotContainerCreate = $("#depot-container-create-bhp");

    const $depotTemplateCreate = $depotContainerCreate.length
        ? $depotContainerCreate
              .find(".depot-row-template-create-bhp")
              .first()
              .clone(false)
        : null;

    let brandFarmasiSelectCreate = null;
    let jenisBhpSelectCreate = null;
    let satuanBhpSelectCreate = null;

    function updateGlobalStockCreate() {
        if (!$depotContainerCreate.length) return;

        let totalStockCreate = 0;

        $depotContainerCreate
            .find(".input-stok-depot-create")
            .each(function () {
                const val = parseInt($(this).val()) || 0;
                if (!isNaN(val)) {
                    totalStockCreate += val;
                }
            });
        $("#stok_barang").val(totalStockCreate);
    }

    // ==========================
    // HELPER: FORMAT RUPIAH
    // ==========================
    function initRupiahFormatter(selector) {
        $(selector).on("input", function () {
            let nilai = $(this).val().replace(/\D/g, "");
            if (nilai) {
                nilai = new Intl.NumberFormat("id-ID").format(nilai);
            }
            $(this).val(nilai);
        });
    }

    // helper: string rupiah -> number
    function parseRupiahNumber(val) {
        val = (val || "").toString();
        val = val.replace(/\D/g, "");
        if (!val) return 0;
        return parseInt(val, 10);
    }

    // ==========================
    // INIT TOMSELECT: BRAND
    // ==========================
    function initBrandFarmasiSelectCreate() {
        const el = document.getElementById("brand_farmasi_id_create");
        const btnClear = document.getElementById("btn-clear-brand-create");
        if (!el) return;

        const urlIndex = el.dataset.urlIndex;
        const urlStore = el.dataset.urlStore;
        const urlDelete = el.dataset.urlDelete;

        if (el.tomselect) {
            el.tomselect.destroy();
        }

        brandFarmasiSelectCreate = new TomSelect(el, {
            valueField: "id",
            labelField: "nama_brand",
            searchField: "nama_brand",
            preload: true,
            maxItems: 1,
            placeholder: "Ketik untuk mencari / tambah baru",

            load: function (query, callback) {
                axios
                    .get(urlIndex, { params: { q: query } })
                    .then((res) => callback(res.data || []))
                    .catch((err) => {
                        console.error("LOAD BRAND ERROR", err);
                        callback([]);
                    });
            },

            create: function (input, callback) {
                axios
                    .post(urlStore, { nama_brand: input })
                    .then((res) => {
                        callback(res.data); // {id, nama_brand}
                    })
                    .catch((err) => {
                        console.error("CREATE BRAND ERROR", err);
                        Swal.fire({
                            icon: "error",
                            title: "Gagal menambahkan brand",
                            text: "Silakan coba lagi.",
                        });
                        callback();
                    });
            },

            onChange: function (value) {
                if (!btnClear) return;
                btnClear.classList.toggle("hidden", !value);
            },
        });

        if (btnClear) {
            btnClear.onclick = function () {
                const ts = brandFarmasiSelectCreate;
                if (!ts) return;

                const value = ts.getValue();
                if (!value) {
                    btnClear.classList.add("hidden");
                    return;
                }

                Swal.fire({
                    icon: "warning",
                    title: "Hapus brand ini?",
                    text: "Brand akan dihapus dari database jika belum dipakai di obat.",
                    showCancelButton: true,
                    confirmButtonText: "Ya, hapus",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    axios
                        .post(urlDelete, { id: value })
                        .then((res) => {
                            ts.clear();
                            ts.removeOption(value);
                            btnClear.classList.add("hidden");
                            console.log("Brand deleted:", res.data.message);
                        })
                        .catch((err) => {
                            console.error("DELETE BRAND ERROR", err);
                            Swal.fire({
                                icon: "error",
                                title: "Gagal menghapus brand",
                                text:
                                    err.response?.data?.message ||
                                    "Silakan coba lagi.",
                            });
                        });
                });
            };
        }
    }

    // ==========================
    // INIT TOMSELECT: JENIS
    // ==========================
    function initJenisBhpSelectCreate() {
        const el = document.getElementById("jenis_id_create");
        const btnClear = document.getElementById("btn-clear-jenis-create");
        if (!el) return;

        const urlIndex = el.dataset.urlIndex;
        const urlStore = el.dataset.urlStore;
        const urlDelete = el.dataset.urlDelete;

        if (el.tomselect) {
            el.tomselect.destroy();
        }

        jenisBhpSelectCreate = new TomSelect(el, {
            valueField: "id",
            labelField: "nama_jenis_obat",
            searchField: "nama_jenis_obat",
            preload: true,
            maxItems: 1,
            placeholder: "Ketik untuk mencari / tambah jenis",

            load: function (query, callback) {
                axios
                    .get(urlIndex, { params: { q: query } })
                    .then((res) => callback(res.data || []))
                    .catch((err) => {
                        console.error("LOAD JENIS ERROR", err);
                        callback([]);
                    });
            },

            create: function (input, callback) {
                axios
                    .post(urlStore, { nama_jenis_obat: input })
                    .then((res) => {
                        callback(res.data);
                    })
                    .catch((err) => {
                        console.error("CREATE JENIS ERROR", err);
                        Swal.fire({
                            icon: "error",
                            title: "Gagal menambahkan jenis",
                            text: "Silakan coba lagi.",
                        });
                        callback();
                    });
            },

            onChange: function (value) {
                if (!btnClear) return;
                btnClear.classList.toggle("hidden", !value);
            },
        });

        if (btnClear) {
            btnClear.onclick = function () {
                const ts = jenisBhpSelectCreate;
                if (!ts) return;

                const value = ts.getValue();
                if (!value) {
                    btnClear.classList.add("hidden");
                    return;
                }

                Swal.fire({
                    icon: "warning",
                    title: "Hapus jenis ini?",
                    text: "Jenis akan dihapus dari database jika belum dipakai di obat.",
                    showCancelButton: true,
                    confirmButtonText: "Ya, hapus",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    axios
                        .post(urlDelete, { id: value })
                        .then((res) => {
                            ts.clear();
                            ts.removeOption(value);
                            btnClear.classList.add("hidden");
                            console.log("Jenis deleted:", res.data.message);
                        })
                        .catch((err) => {
                            console.error("DELETE JENIS ERROR", err);
                            Swal.fire({
                                icon: "error",
                                title: "Gagal menghapus jenis",
                                text:
                                    err.response?.data?.message ||
                                    "Silakan coba lagi.",
                            });
                        });
                });
            };
        }
    }

    // ==========================
    // INIT TOMSELECT: SATUAN
    // ==========================
    function initSatuanBhpSelectCreate() {
        const el = document.getElementById("satuan_id_create");
        const btnClear = document.getElementById("btn-clear-satuan-create");
        if (!el) return;

        const urlIndex = el.dataset.urlIndex;
        const urlStore = el.dataset.urlStore;
        const urlDelete = el.dataset.urlDelete;

        if (el.tomselect) {
            el.tomselect.destroy();
        }

        satuanBhpSelectCreate = new TomSelect(el, {
            valueField: "id",
            labelField: "nama_satuan_obat",
            searchField: "nama_satuan_obat",
            preload: true,
            maxItems: 1,
            placeholder: "Ketik untuk mencari / tambah satuan",

            load: function (query, callback) {
                axios
                    .get(urlIndex, { params: { q: query } })
                    .then((res) => callback(res.data || []))
                    .catch((err) => {
                        console.error("LOAD SATUAN ERROR", err);
                        callback([]);
                    });
            },

            create: function (input, callback) {
                axios
                    .post(urlStore, { nama_satuan_obat: input })
                    .then((res) => callback(res.data))
                    .catch((err) => {
                        console.error("CREATE SATUAN ERROR", err);
                        Swal.fire({
                            icon: "error",
                            title: "Gagal menambahkan satuan",
                            text: "Silakan coba lagi.",
                        });
                        callback();
                    });
            },

            onInitialize: function () {
                const value = this.getValue();
                const hasValue = Array.isArray(value)
                    ? value.length > 0
                    : !!value;
                if (!btnClear) return;
                btnClear.classList.toggle("hidden", !hasValue);
            },

            onChange: function (value) {
                if (!btnClear) return;
                const hasValue = Array.isArray(value)
                    ? value.length > 0
                    : !!value;
                btnClear.classList.toggle("hidden", !hasValue);
            },
        });

        if (btnClear) {
            btnClear.onclick = function () {
                const ts = satuanBhpSelectCreate;
                if (!ts) return;

                const value = ts.getValue();
                const hasValue = Array.isArray(value)
                    ? value.length > 0
                    : !!value;
                if (!hasValue) {
                    btnClear.classList.add("hidden");
                    return;
                }

                Swal.fire({
                    icon: "warning",
                    title: "Hapus satuan ini?",
                    text: "Satuan akan dihapus dari database jika belum dipakai.",
                    showCancelButton: true,
                    confirmButtonText: "Ya, hapus",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    axios
                        .post(urlDelete, { id: value })
                        .then((res) => {
                            ts.clear();
                            ts.removeOption(value);
                            btnClear.classList.add("hidden");
                            console.log("Satuan deleted:", res.data.message);
                        })
                        .catch((err) => {
                            console.error("DELETE SATUAN ERROR", err);
                            Swal.fire({
                                icon: "error",
                                title: "Gagal menghapus satuan",
                                text:
                                    err.response?.data?.message ||
                                    "Silakan coba lagi.",
                            });
                        });
                });
            };
        }
    }

    // ==========================
    // INIT TOMSELECT: NAMA DEPOT
    // ==========================
    function initNamaDepotSelectCreate($row) {
        const el = $row.find(".select-nama-depot-create")[0];
        const btnClear = $row.find(".btn-clear-depot-create")[0];
        if (!el) return;

        const urlIndex = el.dataset.urlIndex;
        const urlStore = el.dataset.urlStore;
        const urlDelete = el.dataset.urlDelete;

        if (el.tomselect) {
            el.tomselect.destroy();
        }

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
                    .catch((err) => {
                        console.error("LOAD DEPOT ERROR", err);
                        callback([]);
                    });
            },

            create: function (input, callback) {
                axios
                    .post(urlStore, { nama_depot: input })
                    .then((res) => {
                        callback(res.data);
                    })
                    .catch((err) => {
                        console.error("CREATE DEPOT ERROR", err);
                        Swal.fire({
                            icon: "error",
                            title: "Gagal menambahkan depot",
                            text: "Silakan coba lagi.",
                        });
                        callback();
                    });
            },

            onChange: function (value) {
                if (!btnClear) return;
                btnClear.classList.toggle("hidden", !value);
            },
        });

        if (btnClear) {
            btnClear.onclick = function () {
                const value = ts.getValue();
                if (!value) {
                    btnClear.classList.add("hidden");
                    return;
                }

                Swal.fire({
                    icon: "warning",
                    title: "Hapus depot ini?",
                    text: "Depot akan dihapus dari database jika belum dipakai.",
                    showCancelButton: true,
                    confirmButtonText: "Ya, hapus",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    axios
                        .post(urlDelete, { id: value })
                        .then((res) => {
                            ts.clear();
                            ts.removeOption(value);
                            btnClear.classList.add("hidden");
                            console.log("Depot deleted:", res.data.message);
                        })
                        .catch((err) => {
                            console.error("DELETE DEPOT ERROR", err);
                            Swal.fire({
                                icon: "error",
                                title: "Gagal menghapus depot",
                                text:
                                    err.response?.data?.message ||
                                    "Silakan coba lagi.",
                            });
                        });
                });
            };
        }
    }

    // ==========================
    // INIT TOMSELECT: TIPE DEPOT
    // ==========================
    function initTipeDepotSelectCreate($row) {
        const el = $row.find(".select-tipe-depot-create")[0];
        const btnClear = $row.find(".btn-clear-tipe-depot-create")[0];
        if (!el) return;

        const urlIndex = el.dataset.urlIndex;
        const urlStore = el.dataset.urlStore;
        const urlDelete = el.dataset.urlDelete;

        if (el.tomselect) {
            el.tomselect.destroy();
        }

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
                    .catch((err) => {
                        console.error("LOAD TIPE DEPOT ERROR", err);
                        callback([]);
                    });
            },

            create: function (input, callback) {
                axios
                    .post(urlStore, { nama_tipe_depot: input })
                    .then((res) => callback(res.data))
                    .catch((err) => {
                        console.error("CREATE TIPE DEPOT ERROR", err);
                        Swal.fire({
                            icon: "error",
                            title: "Gagal menambahkan tipe depot",
                            text: "Silakan coba lagi.",
                        });
                        callback();
                    });
            },

            onChange: function (value) {
                if (!btnClear) return;
                btnClear.classList.toggle("hidden", !value);
            },
        });

        if (btnClear) {
            btnClear.onclick = function () {
                const value = ts.getValue();
                if (!value) {
                    btnClear.classList.add("hidden");
                    return;
                }

                Swal.fire({
                    icon: "warning",
                    title: "Hapus tipe depot ini?",
                    text: "Tipe depot akan dihapus jika belum dipakai.",
                    showCancelButton: true,
                    confirmButtonText: "Ya, hapus",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    axios
                        .post(urlDelete, { id: value })
                        .then((res) => {
                            ts.clear();
                            ts.removeOption(value);
                            btnClear.classList.add("hidden");
                        })
                        .catch((err) => {
                            console.error("DELETE TIPE DEPOT ERROR", err);
                            Swal.fire({
                                icon: "error",
                                title: "Gagal menghapus tipe depot",
                                text:
                                    err.response?.data?.message ||
                                    "Silakan coba lagi.",
                            });
                        });
                });
            };
        }
    }

    // ==========================
    // RESET DEPOT ROWS
    // ==========================
    function resetDepotRowsCreate() {
        if (!$depotContainerCreate.length) return;

        $depotContainerCreate
            .find(".depot-row-template-create-bhp")
            .not(":first")
            .remove();

        const $row = $depotContainerCreate
            .find(".depot-row-template-create-bhp")
            .first();

        // reset TomSelect nama depot
        const selectDepot = $row.find(".select-nama-depot-create")[0];
        if (selectDepot && selectDepot.tomselect) {
            selectDepot.tomselect.clear();
        } else {
            $row.find(".select-nama-depot-create").val("");
        }
        $row.find(".btn-clear-depot-create").addClass("hidden");

        // reset TomSelect tipe depot
        const selectTipe = $row.find(".select-tipe-depot-create")[0];
        if (selectTipe && selectTipe.tomselect) {
            selectTipe.tomselect.clear();
        } else {
            $row.find(".select-tipe-depot-create").val("");
        }
        $row.find(".btn-clear-tipe-depot-create").addClass("hidden");

        $row.find(".input-stok-depot-create").val(0);

        // 👉 pastikan stok global ikut 0
        updateGlobalStockCreate();
    }

    // ==========================
    // RESET FORM
    // ==========================
    function resetFormCreate() {
        if ($formCreate.length === 0) return;

        $formCreate[0].reset();
        $formCreate.find(".is-invalid").removeClass("is-invalid");
        $formCreate.find(".text-danger").empty();

        $("#stok_barang").val(0);

        resetDepotRowsCreate();
        updateGlobalStockCreate();

        if (
            brandFarmasiSelectCreate &&
            document.getElementById("brand_farmasi_id_create").tomselect
        ) {
            document
                .getElementById("brand_farmasi_id_create")
                .tomselect.clear();
        }

        $("#btn-clear-brand-create").addClass("hidden");
    }

    // ==========================
    // INISIALISASI AWAL
    // ==========================
    initRupiahFormatter("#harga_beli_satuan_bhp_create");
    initRupiahFormatter("#harga_jual_umum_bhp_create");
    initRupiahFormatter("#harga_otc_bhp_create");

    if ($depotContainerCreate.length) {
        const $firstRow = $depotContainerCreate
            .find(".depot-row-template-create-bhp")
            .first();
        initNamaDepotSelectCreate($firstRow);
        initTipeDepotSelectCreate($firstRow);
    }

    // ==========================
    // LOGIC HARGA BELI -> HARGA JUAL
    // ==========================
    const DEFAULT_MARGIN_PERCENT = 30;

    $("#harga_beli_satuan_bhp_create").on("input", function () {
        const beli = parseRupiahNumber($(this).val());
        const $jual = $("#harga_jual_umum_bhp_create");

        if (!beli) {
            $jual.val("");
            return;
        }

        if ($("#kunci_harga_obat").is(":checked")) {
            return;
        }

        const jualBaru = Math.round(beli * (1 + DEFAULT_MARGIN_PERCENT / 100));
        $jual.val(new Intl.NumberFormat("id-ID").format(jualBaru));
    });

    $("#harga_jual_umum_bhp_create").on("blur", function () {
        const beli = parseRupiahNumber(
            $("#harga_beli_satuan_bhp_create").val()
        );
        const jual = parseRupiahNumber($(this).val());

        if (!beli || !jual) return;

        if (jual < beli) {
            Swal.fire({
                icon: "warning",
                title: "Harga jual lebih kecil dari harga beli",
                text: "Harga jual umum minimal sama atau lebih besar dari harga beli satuan.",
            });

            $(this).val(new Intl.NumberFormat("id-ID").format(beli));
        }
    });

    // ==========================
    // EVENT: BUKA / TUTUP MODAL
    // ==========================
    $("#btn-open-modal-create-bhp").on("click", function () {
        resetFormCreate();
        initBrandFarmasiSelectCreate();
        initJenisBhpSelectCreate();
        initSatuanBhpSelectCreate();

        // init ulang depot row pertama
        if ($depotContainerCreate.length) {
            const $firstRow = $depotContainerCreate
                .find(".depot-row-template-create-bhp")
                .first();
            initNamaDepotSelectCreate($firstRow);
            initTipeDepotSelectCreate($firstRow);
        }

        if (modalCreate) modalCreate.show();
    });

    $("#btn-close-modal-create-bhp, #btn-cancel-modal-create-bhp").on(
        "click",
        function () {
            modalCreate?.hide();
            resetFormCreate();
        }
    );

    // ==========================
    // LOGIC TAMBAH DEPOT
    // ==========================
    $("#btn-add-depot-create-bhp").on("click", function () {
        if (!$depotContainerCreate.length || !$depotTemplateCreate) return;

        const $newRow = $depotTemplateCreate.clone(false);

        $newRow.find(".select-nama-depot-create").val("");
        $newRow.find(".btn-clear-depot-create").addClass("hidden");

        $newRow.find(".select-tipe-depot-create").val("");
        $newRow.find(".btn-clear-tipe-depot-create").addClass("hidden");

        $newRow.find(".input-stok-depot").val(0);

        $depotContainerCreate.append($newRow);

        initNamaDepotSelectCreate($newRow);
        initTipeDepotSelectCreate($newRow);

        // jika nantinya default stok != 0, ini akan jaga supaya global ikut update
        updateGlobalStockCreate();
    });

    // Hapus depot row
    $(document).on("click", ".btn-remove-depot-create", function () {
        if (!$depotContainerCreate.length) return;

        const $rows = $depotContainerCreate.find(
            ".depot-row-template-create-bhp"
        );

        if ($rows.length <= 1) {
            resetDepotRowsCreate();
            return;
        }

        $(this).closest(".depot-row-template-create-bhp").remove();
        // setelah hapus, update stok global
        updateGlobalStockCreate();
    });

    // >>> TAMBAHKAN INI (setelah handler .btn-remove-depot juga boleh)
    // setiap stok per depot diubah, update stok global
    $(document).on("input", ".input-stok-depot-create", function () {
        updateGlobalStockCreate();
    });

    $formCreate.on("submit", function (e) {
        e.preventDefault();
        const route = $formCreate.data("url");

        $(".text-danger").empty();
        $formCreate.find(".is-invalid").removeClass("is-invalid");

        const parseRupiah = (val) =>
            (val || "").replace(/\./g, "").replace(/,/g, "");

        const depot_id = [];
        const tipe_depot = [];
        const stok_depot = [];

        if ($depotContainerCreate.length) {
            $depotContainerCreate
                .find(".depot-row-template-create-bhp")
                .each(function () {
                    const $row = $(this);

                    depot_id.push(
                        $row.find(".select-nama-depot-create").val() || ""
                    );
                    tipe_depot.push(
                        (
                            $row.find(".select-tipe-depot-create").val() || ""
                        ).trim()
                    );
                    stok_depot.push(
                        $row.find(".input-stok-depot-create").val() || 0
                    );
                });
        }

        const formData = {
            kode: $("#kode_create").val(),
            brand_farmasi_id: $("#brand_farmasi_id_create").val(),
            jenis_id: $("#jenis_id_create").val(),
            satuan_id: $("#satuan_id_create").val(),
            nama_barang: $("#nama_barang").val(),
            stok_barang: $("#stok_barang").val(),
            dosis: $("#dosis").val(),
            tanggal_kadaluarsa_bhp: $("#tanggal_kadaluarsa_bhp_create").val(),
            no_batch: $("#no_batch_create").val(),
            harga_beli_satuan_bhp: parseRupiah(
                $("#harga_beli_satuan_bhp_create").val()
            ),
            // avg_hpp_bhp: $("#avg_hpp_bhp_create").val(),
            harga_jual_umum_bhp: parseRupiah(
                $("#harga_jual_umum_bhp_create").val()
            ),
            harga_otc_bhp: parseRupiah($("#harga_otc_bhp_create").val()),
            keterangan: $("#keterangan").val(),

            depot_id: depot_id,
            tipe_depot: tipe_depot,
            stok_depot: stok_depot,
        };

        axios
            .post(route, formData)
            .then((res) => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil",
                    text: res.data.message,
                    timer: 2000,
                });
                modalCreate?.hide();
                $("#tabelBahanHabisPakai").DataTable().ajax.reload(null, false);
            })
            .catch((err) => {
                const status = err.response?.status;
                const data = err.response?.data;

                if (status === 422) {
                    const errors = data?.errors || {};
                    for (const field in errors) {
                        $(`#${field}_create`).addClass("is-invalid");
                        $(`#${field}_create-error`).html(errors[field][0]);
                    }

                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal!",
                        text: data.message || "Periksa kembali inputan Anda.",
                    });
                    return;
                }

                Swal.fire({
                    icon: "error",
                    title:
                        status === 400 ? "Input tidak valid" : "Error Server!",
                    text:
                        data?.message ||
                        "Terjadi kesalahan pada server. Silahkan coba lagi nanti",
                });
            });
    });
});

$(function () {
    const elementModalUpdate = document.getElementById("modalUpdateBhp");
    const modalUpdate = elementModalUpdate
        ? new Modal(elementModalUpdate, {
              backdrop: "static",
              closable: false,
          })
        : null;

    const $formUpdate = $("#formModalUpdateBhp");
    const $depotContainerUpdate = $("#depot-container-update-bhp");

    const $depotTemplateUpdate = $depotContainerUpdate.length
        ? $depotContainerUpdate
              .find(".depot-row-template-update-bhp")
              .first()
              .clone(false)
        : null;

    let brandFarmasiSelectUpdate = null;
    let jenisObatSelectUpdate = null;
    let satuanObatSelectUpdate = null;

    const DEFAULT_MARGIN_PERCENT = 30;

    // =====================================
    // ✅ [BARU] HELPER HITUNG TOTAL STOK DEPOT -> STOK OBAT
    // =====================================
    function toIntSafe(val) {
        const n = parseInt((val ?? "").toString().replace(/[^\d-]/g, ""), 10);
        return Number.isFinite(n) ? n : 0;
    }

    function hitungTotalStokDepotUpdate() {
        let total = 0;

        if ($depotContainerUpdate.length) {
            $depotContainerUpdate
                .find(".input-stok-depot-update")
                .each(function () {
                    const v = toIntSafe($(this).val());
                    total += v > 0 ? v : 0;
                });
        }

        $("#stok_barang_update").val(total);
        return total;
    }

    // ✅ [BARU] realtime ketika stok depot diketik
    $(document).on(
        "input change",
        "#depot-container-update-bhp .input-stok-depot-update",
        function () {
            hitungTotalStokDepotUpdate();
        }
    );

    // =====================================
    // HELPER KHUSUS UPDATE
    // =====================================
    function initRupiahFormatterUpdate(selector) {
        $(selector).on("input", function () {
            let nilai = $(this).val().replace(/\D/g, "");
            if (nilai) {
                nilai = new Intl.NumberFormat("id-ID").format(nilai);
            }
            $(this).val(nilai);
        });
    }

    function parseRupiahNumberUpdate(val) {
        val = (val || "").toString().replace(/\D/g, "");
        return val ? parseInt(val, 10) : 0;
    }

    // ==========================
    // INIT TOMSELECT UPDATE: BRAND
    // ==========================
    function initBrandFarmasiSelectUpdate() {
        const el = document.getElementById("brand_farmasi_id_update");
        const btnClear = document.getElementById("btn-clear-brand-update");
        if (!el) return;

        const urlIndex = el.dataset.urlIndex;
        const urlStore = el.dataset.urlStore;
        const urlDelete = el.dataset.urlDelete;

        if (el.tomselect) {
            el.tomselect.destroy();
        }

        brandFarmasiSelectUpdate = new TomSelect(el, {
            valueField: "id",
            labelField: "nama_brand",
            searchField: "nama_brand",
            preload: true,
            maxItems: 1,
            placeholder: "Ketik untuk mencari / tambah baru",

            load: function (query, callback) {
                axios
                    .get(urlIndex, { params: { q: query } })
                    .then((res) => callback(res.data || []))
                    .catch((err) => {
                        console.error("LOAD BRAND UPDATE ERROR", err);
                        callback([]);
                    });
            },

            create: function (input, callback) {
                axios
                    .post(urlStore, { nama_brand: input })
                    .then((res) => callback(res.data))
                    .catch((err) => {
                        console.error("CREATE BRAND UPDATE ERROR", err);
                        Swal.fire({
                            icon: "error",
                            title: "Gagal menambahkan brand",
                            text: "Silakan coba lagi.",
                        });
                        callback();
                    });
            },

            onChange: function (value) {
                if (!btnClear) return;
                btnClear.classList.toggle("hidden", !value);
            },
        });

        if (btnClear) {
            btnClear.onclick = function () {
                const ts = brandFarmasiSelectUpdate;
                if (!ts) return;

                const value = ts.getValue();
                if (!value) {
                    btnClear.classList.add("hidden");
                    return;
                }

                Swal.fire({
                    icon: "warning",
                    title: "Hapus brand ini?",
                    text: "Brand akan dihapus dari database jika belum dipakai di obat.",
                    showCancelButton: true,
                    confirmButtonText: "Ya, hapus",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    axios
                        .post(urlDelete, { id: value })
                        .then((res) => {
                            ts.clear();
                            ts.removeOption(value);
                            btnClear.classList.add("hidden");
                            console.log("Brand deleted:", res.data.message);
                        })
                        .catch((err) => {
                            console.error("DELETE BRAND UPDATE ERROR", err);
                            Swal.fire({
                                icon: "error",
                                title: "Gagal menghapus brand",
                                text:
                                    err.response?.data?.message ||
                                    "Silakan coba lagi.",
                            });
                        });
                });
            };
        }
    }

    // ==========================
    // INIT TOMSELECT UPDATE: JENIS
    // ==========================
    function initJenisObatSelectUpdate() {
        const el = document.getElementById("jenis_id_update");
        const btnClear = document.getElementById("btn-clear-jenis-update");
        if (!el) return;

        const urlIndex = el.dataset.urlIndex;
        const urlStore = el.dataset.urlStore;
        const urlDelete = el.dataset.urlDelete;

        if (el.tomselect) {
            el.tomselect.destroy();
        }

        jenisObatSelectUpdate = new TomSelect(el, {
            valueField: "id",
            labelField: "nama_jenis_obat",
            searchField: "nama_jenis_obat",
            preload: true,
            maxItems: 1,
            placeholder: "Ketik untuk mencari / tambah jenis",

            load: function (query, callback) {
                axios
                    .get(urlIndex, { params: { q: query } })
                    .then((res) => callback(res.data || []))
                    .catch((err) => {
                        console.error("LOAD JENIS UPDATE ERROR", err);
                        callback([]);
                    });
            },

            create: function (input, callback) {
                axios
                    .post(urlStore, { nama_jenis_obat: input })
                    .then((res) => callback(res.data))
                    .catch((err) => {
                        console.error("CREATE JENIS UPDATE ERROR", err);
                        Swal.fire({
                            icon: "error",
                            title: "Gagal menambahkan jenis",
                            text: "Silakan coba lagi.",
                        });
                        callback();
                    });
            },

            onChange: function (value) {
                if (!btnClear) return;
                btnClear.classList.toggle("hidden", !value);
            },
        });

        if (btnClear) {
            btnClear.onclick = function () {
                const ts = jenisObatSelectUpdate;
                if (!ts) return;

                const value = ts.getValue();
                if (!value) {
                    btnClear.classList.add("hidden");
                    return;
                }

                Swal.fire({
                    icon: "warning",
                    title: "Hapus jenis ini?",
                    text: "Jenis akan dihapus dari database jika belum dipakai di obat.",
                    showCancelButton: true,
                    confirmButtonText: "Ya, hapus",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    axios
                        .post(urlDelete, { id: value })
                        .then((res) => {
                            ts.clear();
                            ts.removeOption(value);
                            btnClear.classList.add("hidden");
                            console.log("Jenis deleted:", res.data.message);
                        })
                        .catch((err) => {
                            console.error("DELETE JENIS UPDATE ERROR", err);
                            Swal.fire({
                                icon: "error",
                                title: "Gagal menghapus jenis",
                                text:
                                    err.response?.data?.message ||
                                    "Silakan coba lagi.",
                            });
                        });
                });
            };
        }
    }

    // ==========================
    // INIT TOMSELECT UPDATE: SATUAN
    // ==========================
    function initSatuanObatSelectUpdate() {
        const el = document.getElementById("satuan_id_update");
        const btnClear = document.getElementById("btn-clear-satuan-update");
        if (!el) return;

        const urlIndex = el.dataset.urlIndex;
        const urlStore = el.dataset.urlStore;
        const urlDelete = el.dataset.urlDelete;

        if (el.tomselect) {
            el.tomselect.destroy();
        }

        satuanObatSelectUpdate = new TomSelect(el, {
            valueField: "id",
            labelField: "nama_satuan_obat",
            searchField: "nama_satuan_obat",
            preload: true,
            maxItems: 1,
            placeholder: "Ketik untuk mencari / tambah satuan",

            load: function (query, callback) {
                axios
                    .get(urlIndex, { params: { q: query } })
                    .then((res) => callback(res.data || []))
                    .catch((err) => {
                        console.error("LOAD SATUAN UPDATE ERROR", err);
                        callback([]);
                    });
            },

            create: function (input, callback) {
                axios
                    .post(urlStore, { nama_satuan_obat: input })
                    .then((res) => callback(res.data))
                    .catch((err) => {
                        console.error("CREATE SATUAN UPDATE ERROR", err);
                        Swal.fire({
                            icon: "error",
                            title: "Gagal menambahkan satuan",
                            text: "Silakan coba lagi.",
                        });
                        callback();
                    });
            },

            onInitialize: function () {
                const value = this.getValue();
                const hasValue = Array.isArray(value)
                    ? value.length > 0
                    : !!value;
                if (!btnClear) return;
                btnClear.classList.toggle("hidden", !hasValue);
            },

            onChange: function (value) {
                if (!btnClear) return;
                const hasValue = Array.isArray(value)
                    ? value.length > 0
                    : !!value;
                btnClear.classList.toggle("hidden", !hasValue);
            },
        });

        if (btnClear) {
            btnClear.onclick = function () {
                const ts = satuanObatSelectUpdate;
                if (!ts) return;

                const value = ts.getValue();
                const hasValue = Array.isArray(value)
                    ? value.length > 0
                    : !!value;
                if (!hasValue) {
                    btnClear.classList.add("hidden");
                    return;
                }

                Swal.fire({
                    icon: "warning",
                    title: "Hapus satuan ini?",
                    text: "Satuan akan dihapus dari database jika belum dipakai.",
                    showCancelButton: true,
                    confirmButtonText: "Ya, hapus",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    axios
                        .post(urlDelete, { id: value })
                        .then((res) => {
                            ts.clear();
                            ts.removeOption(value);
                            btnClear.classList.add("hidden");
                            console.log("Satuan deleted:", res.data.message);
                        })
                        .catch((err) => {
                            console.error("DELETE SATUAN UPDATE ERROR", err);
                            Swal.fire({
                                icon: "error",
                                title: "Gagal menghapus satuan",
                                text:
                                    err.response?.data?.message ||
                                    "Silakan coba lagi.",
                            });
                        });
                });
            };
        }
    }

    // ==========================
    // INIT TOMSELECT: NAMA DEPOT (UPDATE)
    // ==========================
    function initNamaDepotSelectUpdate($row) {
        const el = $row.find(".select-nama-depot-update")[0];
        const btnClear = $row.find(".btn-clear-depot-update")[0];
        if (!el) return;

        const urlIndex = el.dataset.urlIndex;
        const urlStore = el.dataset.urlStore;
        const urlDelete = el.dataset.urlDelete;

        if (el.tomselect) {
            el.tomselect.destroy();
        }

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
                    .catch((err) => {
                        console.error("LOAD DEPOT ERROR", err);
                        callback([]);
                    });
            },

            create: function (input, callback) {
                axios
                    .post(urlStore, { nama_depot: input })
                    .then((res) => {
                        callback(res.data);
                    })
                    .catch((err) => {
                        console.error("CREATE DEPOT ERROR", err);
                        Swal.fire({
                            icon: "error",
                            title: "Gagal menambahkan depot",
                            text: "Silakan coba lagi.",
                        });
                        callback();
                    });
            },

            onChange: function (value) {
                if (!btnClear) return;
                btnClear.classList.toggle("hidden", !value);
            },
        });

        if (btnClear) {
            btnClear.onclick = function () {
                const value = ts.getValue();
                if (!value) {
                    btnClear.classList.add("hidden");
                    return;
                }

                Swal.fire({
                    icon: "warning",
                    title: "Hapus depot ini?",
                    text: "Depot akan dihapus dari database jika belum dipakai.",
                    showCancelButton: true,
                    confirmButtonText: "Ya, hapus",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    axios
                        .post(urlDelete, { id: value })
                        .then((res) => {
                            ts.clear();
                            ts.removeOption(value);
                            btnClear.classList.add("hidden");
                            console.log("Depot deleted:", res.data.message);
                        })
                        .catch((err) => {
                            console.error("DELETE DEPOT ERROR", err);
                            Swal.fire({
                                icon: "error",
                                title: "Gagal menghapus depot",
                                text:
                                    err.response?.data?.message ||
                                    "Silakan coba lagi.",
                            });
                        });
                });
            };
        }
    }

    // ==========================
    // INIT TOMSELECT: TIPE DEPOT (UPDATE)
    // ==========================
    function initTipeDepotSelectUpdate($row) {
        const el = $row.find(".select-tipe-depot-update")[0];
        const btnClear = $row.find(".btn-clear-tipe-depot-update")[0];
        if (!el) return;

        const urlIndex = el.dataset.urlIndex;
        const urlStore = el.dataset.urlStore;
        const urlDelete = el.dataset.urlDelete;

        if (el.tomselect) {
            el.tomselect.destroy();
        }

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
                    .catch((err) => {
                        console.error("LOAD TIPE DEPOT ERROR", err);
                        callback([]);
                    });
            },

            create: function (input, callback) {
                axios
                    .post(urlStore, { nama_tipe_depot: input })
                    .then((res) => callback(res.data))
                    .catch((err) => {
                        console.error("CREATE TIPE DEPOT ERROR", err);
                        Swal.fire({
                            icon: "error",
                            title: "Gagal menambahkan tipe depot",
                            text: "Silakan coba lagi.",
                        });
                        callback();
                    });
            },

            onChange: function (value) {
                if (!btnClear) return;
                btnClear.classList.toggle("hidden", !value);
            },
        });

        if (btnClear) {
            btnClear.onclick = function () {
                const value = ts.getValue();
                if (!value) {
                    btnClear.classList.add("hidden");
                    return;
                }

                Swal.fire({
                    icon: "warning",
                    title: "Hapus tipe depot ini?",
                    text: "Tipe depot akan dihapus jika belum dipakai.",
                    showCancelButton: true,
                    confirmButtonText: "Ya, hapus",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    axios
                        .post(urlDelete, { id: value })
                        .then((res) => {
                            ts.clear();
                            ts.removeOption(value);
                            btnClear.classList.add("hidden");
                        })
                        .catch((err) => {
                            console.error("DELETE TIPE DEPOT ERROR", err);
                            Swal.fire({
                                icon: "error",
                                title: "Gagal menghapus tipe depot",
                                text:
                                    err.response?.data?.message ||
                                    "Silakan coba lagi.",
                            });
                        });
                });
            };
        }
    }

    // ==========================
    // RESET DEPOT ROWS UPDATE
    // ==========================
    function resetDepotRowsUpdate() {
        if (!$depotContainerUpdate.length) return;

        $depotContainerUpdate
            .find(".depot-row-template-update-bhp")
            .not(":first")
            .remove();
        const $row = $depotContainerUpdate
            .find(".depot-row-template-update-bhp")
            .first();

        const selectDepot = $row.find(".select-nama-depot-update")[0];
        if (selectDepot && selectDepot.tomselect) {
            selectDepot.tomselect.clear();
        } else {
            $row.find(".select-nama-depot-update").val("");
        }
        $row.find(".btn-clear-depot-update").addClass("hidden");

        const selectTipe = $row.find(".select-tipe-depot-update")[0];
        if (selectTipe && selectTipe.tomselect) {
            selectTipe.tomselect.clear();
        } else {
            $row.find(".select-tipe-depot-update").val("");
        }
        $row.find(".btn-clear-tipe-depot-update").addClass("hidden");

        $row.find(".input-stok-depot-update").val(0);

        // ✅ [BARU] setelah reset depot, hitung ulang stok global
        hitungTotalStokDepotUpdate();
    }

    // ==========================
    // RESET FORM UPDATE
    // ==========================
    function resetFormUpdate() {
        if ($formUpdate.length === 0) return;

        $formUpdate[0].reset();
        $formUpdate.find(".is-invalid").removeClass("is-invalid");
        $formUpdate.find(".text-danger").empty();

        $("#stok_barang_update").val(0);

        resetDepotRowsUpdate();

        if (
            brandFarmasiSelectUpdate &&
            document.getElementById("brand_farmasi_id_update").tomselect
        ) {
            document
                .getElementById("brand_farmasi_id_update")
                .tomselect.clear();
        }

        $("#btn-clear-brand-update").addClass("hidden");
        $("#btn-clear-jenis-update").addClass("hidden");
        $("#btn-clear-satuan-update").addClass("hidden");

        // ✅ [BARU] pastikan stok global 0 setelah reset
        hitungTotalStokDepotUpdate();
    }

    // ==========================
    // INISIALISASI AWAL UPDATE
    // ==========================
    initRupiahFormatterUpdate("#harga_beli_satuan_bhp_update");
    initRupiahFormatterUpdate("#harga_jual_umum_bhp_update");
    initRupiahFormatterUpdate("#harga_otc_bhp_update");

    if ($depotContainerUpdate.length) {
        const $firstRowUpdate = $depotContainerUpdate
            .find(".depot-row-template-update-bhp")
            .first();
        initNamaDepotSelectUpdate($firstRowUpdate);
        initTipeDepotSelectUpdate($firstRowUpdate);
    }

    // ==========================
    // LOGIC HARGA BELI -> HARGA JUAL (UPDATE)
    // ==========================
    $("#harga_beli_satuan_bhp_update").on("input", function () {
        const beli = parseRupiahNumberUpdate($(this).val());
        const $jual = $("#harga_jual_umum_bhp_update");

        if (!beli) {
            $jual.val("");
            return;
        }

        if ($("#edit_kunci_harga_obat").is(":checked")) {
            return;
        }

        const jualBaru = Math.round(beli * (1 + DEFAULT_MARGIN_PERCENT / 100));
        $jual.val(new Intl.NumberFormat("id-ID").format(jualBaru));
    });

    $("#harga_jual_umum_bhp_update").on("blur", function () {
        const beli = parseRupiahNumberUpdate(
            $("#harga_beli_satuan_bhp_update").val()
        );
        const jual = parseRupiahNumberUpdate($(this).val());

        if (!beli || !jual) return;

        if (jual < beli) {
            Swal.fire({
                icon: "warning",
                title: "Harga jual lebih kecil dari harga beli",
                text: "Harga jual umum minimal sama atau lebih besar dari harga beli satuan.",
            });

            $(this).val(new Intl.NumberFormat("id-ID").format(beli));
        }
    });

    // ==========================
    // EVENT: BUKA / TUTUP MODAL UPDATE
    // ==========================
    $(document).on("click", ".btn-edit-bhp", function () {
        const id = $(this).data("id");

        if (!id) {
            console.error("data id kosong");
            return;
        }

        resetFormUpdate();

        initBrandFarmasiSelectUpdate();
        initJenisObatSelectUpdate();
        initSatuanObatSelectUpdate();

        axios
            .get(`/farmasi/bahan-habis-pakai/get-data-bhp-by-id/${id}`)
            .then((response) => {
                const data = response.data.data || response.data;
                console.log(data);

                $("#id_update").val(data.id);
                $("#kode_update").val(data.kode || "");
                $("#nama_barang_update").val(data.nama_barang || "");
                $("#dosis_update").val(data.dosis || "");

                // ❗ tetap isi dulu dari data lama, tapi nanti akan ditimpa hasil sum depot
                $("#stok_barang_update").val(data.stok_barang ?? 0);

                $("#tanggal_kadaluarsa_bhp_update").val(
                    data.tanggal_kadaluarsa_bhp || ""
                );
                $("#no_batch_update").val(data.no_batch || "");

                if (data.harga_beli_satuan_bhp != null) {
                    $("#harga_beli_satuan_bhp_update").val(
                        new Intl.NumberFormat("id-ID").format(
                            Number(data.harga_beli_satuan_bhp)
                        )
                    );
                }

                if (data.harga_jual_umum_bhp != null) {
                    $("#harga_jual_umum_bhp_update").val(
                        new Intl.NumberFormat("id-ID").format(
                            Number(data.harga_jual_umum_bhp)
                        )
                    );
                }

                if (data.harga_otc_bhp != null) {
                    $("#harga_otc_bhp_update").val(
                        new Intl.NumberFormat("id-ID").format(
                            Number(data.harga_otc_bhp)
                        )
                    );
                }

                // ================== TOMSELECT ==================

                // BRAND FARMASI
                if (brandFarmasiSelectUpdate && data.brand_farmasi) {
                    brandFarmasiSelectUpdate.clearOptions();
                    brandFarmasiSelectUpdate.addOption({
                        id: data.brand_farmasi.id,
                        nama_brand: data.brand_farmasi.nama_brand,
                    });
                    brandFarmasiSelectUpdate.setValue(
                        String(data.brand_farmasi.id)
                    );
                }

                // JENIS
                if (jenisObatSelectUpdate && data.jenis_b_h_p) {
                    jenisObatSelectUpdate.clearOptions();
                    jenisObatSelectUpdate.addOption({
                        id: data.jenis_b_h_p.id,
                        nama_jenis_obat: data.jenis_b_h_p.nama_jenis_obat,
                    });
                    jenisObatSelectUpdate.setValue(String(data.jenis_b_h_p.id));
                }

                // SATUAN
                if (satuanObatSelectUpdate && data.satuan_b_h_p) {
                    satuanObatSelectUpdate.clearOptions();
                    satuanObatSelectUpdate.addOption({
                        id: data.satuan_b_h_p.id,
                        nama_satuan_obat: data.satuan_b_h_p.nama_satuan_obat,
                    });
                    satuanObatSelectUpdate.setValue(
                        String(data.satuan_b_h_p.id)
                    );
                }

                // ================== DEPOT (many to many: depot_obat) ==================
                if ($depotContainerUpdate.length) {
                    // kosongkan row lama (sisa 1 row template kosong)
                    resetDepotRowsUpdate();

                    // relasi dari controller: depotBHP => json: depot_b_h_p
                    const depots = Array.isArray(data.depot_b_h_p)
                        ? data.depot_b_h_p
                        : Array.isArray(data.depot_bhp)
                        ? data.depot_bhp
                        : Array.isArray(data.depotBHP)
                        ? data.depotBHP
                        : [];

                    if (depots.length === 0) {
                        // tidak ada depot: tetap 1 row kosong
                        const $row = $depotContainerUpdate
                            .find(".depot-row-template-update-bhp")
                            .first();
                        initNamaDepotSelectUpdate($row);
                        initTipeDepotSelectUpdate($row);

                        // ✅ [BARU] stok global jadi 0
                        hitungTotalStokDepotUpdate();
                    } else {
                        depots.forEach((depotItem, index) => {
                            let $row;

                            if (index === 0) {
                                $row = $depotContainerUpdate
                                    .find(".depot-row-template-update-bhp")
                                    .first();
                            } else {
                                const $newRow =
                                    $depotTemplateUpdate.clone(false);

                                $newRow
                                    .find(".select-nama-depot-update")
                                    .val("");
                                $newRow
                                    .find(".btn-clear-depot-update")
                                    .addClass("hidden");

                                $newRow
                                    .find(".select-tipe-depot-update")
                                    .val("");
                                $newRow
                                    .find(".btn-clear-tipe-depot-update")
                                    .addClass("hidden");

                                $newRow.find(".input-stok-depot-update").val();

                                $depotContainerUpdate.append($newRow);
                                $row = $newRow;
                            }

                            // init TomSelect utk row ini
                            initNamaDepotSelectUpdate($row);
                            initTipeDepotSelectUpdate($row);

                            const depotSelect = $row.find(
                                ".select-nama-depot-update"
                            )[0];
                            const tipeSelect = $row.find(
                                ".select-tipe-depot-update"
                            )[0];

                            // ========== NAMA DEPOT (dari depotItem.nama_depot) ==========
                            if (depotSelect && depotSelect.tomselect) {
                                const tsDepot = depotSelect.tomselect;

                                tsDepot.clearOptions();
                                tsDepot.addOption({
                                    id: depotItem.id,
                                    nama_depot: depotItem.nama_depot,
                                });
                                tsDepot.setValue(String(depotItem.id));
                            } else {
                                $row.find(".select-nama-depot-update").val(
                                    depotItem.id ?? ""
                                );
                            }

                            // ========== TIPE DEPOT ==========
                            if (tipeSelect && tipeSelect.tomselect) {
                                const tsTipe = tipeSelect.tomselect;
                                tsTipe.clearOptions();

                                if (depotItem.tipe_depot) {
                                    tsTipe.addOption({
                                        id: depotItem.tipe_depot.id,
                                        nama_tipe_depot:
                                            depotItem.tipe_depot
                                                .nama_tipe_depot,
                                    });
                                    tsTipe.setValue(
                                        String(depotItem.tipe_depot.id)
                                    );
                                }
                            } else if (depotItem.tipe_depot) {
                                $row.find(".select-tipe-depot-update").val(
                                    depotItem.tipe_depot.id ?? ""
                                );
                            }

                            // ========== STOK DEPOT (ambil dari pivot depot_bhp.stok) ==========
                            let stok = 0;

                            // pivot biasanya ada sebagai `pivot`
                            if (
                                depotItem.pivot &&
                                depotItem.pivot.stok != null
                            ) {
                                stok = Number(depotItem.pivot.stok);
                            }

                            $row.find(".input-stok-depot-update").val(stok);
                        });

                        // ✅ [BARU] setelah semua row depot di-set, hitung totalnya
                        hitungTotalStokDepotUpdate();
                    }
                }

                if (modalUpdate) modalUpdate.show();
            })
            .catch((error) => {
                console.error("LOAD DATA OBAT UNTUK UPDATE ERROR", error);
                Swal.fire({
                    icon: "error",
                    title: "Gagal memuat data",
                    text: "Tidak dapat mengambil data obat. Silakan coba lagi.",
                });
            });
    });

    $("#btn-close-modal-update-bhp, #btn-cancel-modal-update-bhp").on(
        "click",
        function () {
            resetFormUpdate();
            if (modalUpdate) modalUpdate.hide();
        }
    );

    // ==========================
    // LOGIC TAMBAH DEPOT UPDATE
    // ==========================
    $("#btn-add-depot-update").on("click", function () {
        if (!$depotContainerUpdate.length || !$depotTemplateUpdate) return;

        const $newRow = $depotTemplateUpdate.clone(false);

        $newRow.find(".select-nama-depot-update").val("");
        $newRow.find(".btn-clear-depot-update").addClass("hidden");

        $newRow.find(".select-tipe-depot-update").val("");
        $newRow.find(".btn-clear-tipe-depot-update").addClass("hidden");

        $newRow.find(".input-stok-depot-update").val(0);

        $depotContainerUpdate.append($newRow);

        initNamaDepotSelectUpdate($newRow);
        initTipeDepotSelectUpdate($newRow);

        // ✅ [BARU] update stok global
        hitungTotalStokDepotUpdate();
    });

    // Hapus depot row UPDATE
    $(document).on("click", ".btn-remove-depot-update", function () {
        if (!$depotContainerUpdate.length) return;

        const $rows = $depotContainerUpdate.find(".depot-row-template-update-bhp");

        if ($rows.length <= 1) {
            resetDepotRowsUpdate();
            return;
        }

        $(this).closest(".depot-row-template-update-bhp").remove();

        // ✅ [BARU] update stok global setelah remove row
        hitungTotalStokDepotUpdate();
    });

    // ==========================
    // SUBMIT FORM UPDATE
    // ==========================

    $formUpdate.on("submit", function (e) {
        e.preventDefault();

        // ambil id obat dari hidden input
        const id = $("#id_update").val();

        if (!id) {
            console.error(
                "Data ID Dari Tabel Bahan Habis Pakai Tidak Ditemukan, tidak bisa update"
            );
            Swal.fire({
                icon: "error",
                title: "Data tidak lengkap",
                text: "ID obat tidak ditemukan. Silakan tutup dan buka lagi form edit.",
            });
            return;
        }

        // bikin URL ke controller update
        const route = `/farmasi/bahan-habis-pakai/update-data-bhp/${id}`;

        // bersihkan error lama
        $(".text-danger").empty();
        $formUpdate.find(".is-invalid").removeClass("is-invalid");

        const depot_id = [];
        const tipe_depot = [];
        const stok_depot = [];

        if ($depotContainerUpdate.length) {
            $depotContainerUpdate
                .find(".depot-row-template-update-bhp")
                .each(function () {
                    const $row = $(this);

                    const depotVal =
                        $row.find(".select-nama-depot-update").val() || "";
                    const tipeVal =
                        $row.find(".select-tipe-depot-update").val() || "";
                    const stokVal =
                        $row.find(".input-stok-depot-update").val() || 0;

                    depot_id.push(depotVal);
                    tipe_depot.push(tipeVal);
                    stok_depot.push(stokVal);
                });
        }

        // ✅ [BARU] pastikan stok_obat yg dikirim = SUM stok depot
        const totalStok = hitungTotalStokDepotUpdate();

        const formData = {
            kode: $("#kode_update").val(),
            nama_barang: $("#nama_barang_update").val(),
            brand_farmasi_id: $("#brand_farmasi_id_update").val(),
            jenis_id: $("#jenis_id_update").val(),
            satuan_id: $("#satuan_id_update").val(),
            dosis: $("#dosis_update").val(),

            // ✅ [REVISI] kirim total sum depot, bukan nilai manual
            stok_barang: totalStok,

            tanggal_kadaluarsa_bhp: $("#tanggal_kadaluarsa_bhp_update").val(),
            no_batch: $("#no_batch_update").val(),

            harga_beli_satuan_bhp: parseRupiahNumberUpdate(
                $("#harga_beli_satuan_bhp_update").val()
            ),
            harga_jual_umum_bhp: parseRupiahNumberUpdate(
                $("#harga_jual_umum_bhp_update").val()
            ),
            harga_otc_bhp: parseRupiahNumberUpdate(
                $("#harga_otc_bhp_update").val()
            ),

            depot_id: depot_id,
            tipe_depot: tipe_depot,
            stok_depot: stok_depot,
        };

        axios
            .post(route, formData)
            .then((response) => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: response.data.message,
                    showConfirmButton: false,
                    timer: 2000,
                }).then(() => {
                    if ($("#tabelBahanHabisPakai").length) {
                        if (modalUpdate) modalUpdate.hide();
                        $("#tabelBahanHabisPakai")
                            .DataTable()
                            .ajax.reload(null, false);
                    } else {
                        window.location.reload();
                    }
                });
            })
            .catch((err) => {
                const status = err.response?.status;
                const data = err.response?.data;

                if (status === 422) {
                    const errors = data?.errors || {};
                    for (const field in errors) {
                        $(`#${field}_update`).addClass("is-invalid");
                        $(`#${field}_update-error`).html(errors[field][0]);
                    }

                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal!",
                        text: data.message || "Periksa kembali inputan Anda.",
                    });

                    return;
                }
                Swal.fire({
                    icon: "error",
                    title:
                        status === 400 ? "Input tidak valid" : "Error Server!",
                    text:
                        data?.message ||
                        "Terjadi kesalahan pada server. Silahkan coba lagi nanti",
                });
            });
    });
});

$(function () {
    $("body").on("click", ".btn-delete-bhp", function () {
        const id = $(this).data("id");
        if (!id) return;

        Swal.fire({
            icon: "warning",
            title: "Apakah Anda yakin?",
            text: "Data yang dihapus tidak bisa dikembalikan!",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, hapus!",
            cancelButtonText: "Batal",
        }).then((result) => {
            if (result.isConfirmed) {
                axios
                    .post(`/farmasi/bahan-habis-pakai/delete-data-bhp/${id}`)
                    .then((response) => {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: response.data.message,
                            showConfirmButton: false,
                            timer: 2000,
                        }).then(() => {
                            if ($("#dataObatTable".length)) {
                                $("#dataObatTable")
                                    .DataTable()
                                    .ajax.reload(null, false);
                            } else {
                                window.reload();
                            }
                        });
                    })
                    .catch((error) => {
                        console.error("SERVER ERROR", error);
                        Swal.fire({
                            icons: "error",
                            title: "ERROR!",
                            text: "Terjadi kesalahan Server. Silahkan Coba Lagi",
                        });
                    });
            }
        });
    });
});
