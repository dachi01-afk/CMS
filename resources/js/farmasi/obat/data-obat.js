import axios from "axios";
import $ from "jquery";

$(function () {
    // ==========================
    // HELPER FORMAT RUPIAH
    // ==========================
    function formatRupiah(value) {
        if (value === null || value === undefined || value === "") return "-";
        const num = Number(value) || 0;
        return num.toLocaleString("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        });
    }

    // ==========================
    // INIT DATATABLES
    // ==========================
    const table = $("#dataObatTable").DataTable({
        processing: true,
        responsive: true,
        serverSide: true,
        paging: true,
        searching: true,
        searchDelay: 250,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/farmasi/obat/get-data-obat",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            {
                data: "kode",
                name: "kode",
            },
            {
                data: "nama_obat",
                name: "nama_obat",
            },
            {
                data: "farmasi",
                name: "farmasi",
                render: function (data, type, row) {
                    const val = data || row.brand_farmasi?.nama_brand || "-";
                    return `<span class="text-xs">${val}</span>`;
                },
            },
            {
                data: "jenis",
                name: "jenis",
                render: function (data, type, row) {
                    const val = data || row.jenis_obat?.nama_jenis_obat || "-";
                    return `<span class="text-xs">${val}</span>`;
                },
            },
            {
                data: "kategori",
                name: "kategori",
                render: function (data, type, row) {
                    const val =
                        data || row.kategori_obat?.nama_kategori_obat || "-";
                    return `<span class="text-xs">${val}</span>`;
                },
            },
            {
                data: "stok",
                name: "stok",
                render: function (data, type, row) {
                    const stok = data ?? row.jumlah ?? 0;
                    const satuan =
                        row.satuan_obat?.nama_satuan_obat || "capsul";

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
                data: "harga_umum",
                name: "harga_umum",
                render: function (data) {
                    return `<span class="font-semibold text-gray-800 dark:text-gray-100 text-xs">
                                ${formatRupiah(data)}
                            </span>`;
                },
            },
            {
                data: "harga_beli",
                name: "harga_beli",
                render: function (data) {
                    return `<span class="text-xs">
                                ${formatRupiah(data)}
                            </span>`;
                },
            },
            {
                data: "avg_hpp",
                name: "avg_hpp",
                render: function (data) {
                    return `<span class="text-xs">
                                ${formatRupiah(data)}
                            </span>`;
                },
            },
            {
                data: "harga_otc",
                name: "harga_otc",
                render: function (data) {
                    return `<span class="text-xs">
                                ${formatRupiah(data)}
                            </span>`;
                },
            },
            {
                data: "margin_profit",
                name: "margin_profit",
                render: function (data) {
                    return `<span class="font-semibold text-emerald-700 dark:text-emerald-300 text-xs">
                                ${formatRupiah(data)}
                            </span>`;
                },
            },
            {
                data: "action",
                name: "action",
                searchable: false,
                orderable: false,
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

    // ==========================
    // GLOBAL SEARCH OBAT
    // ==========================
    const $globalSearchObat = $("#globalSearchObat");
    let searchTimer = null;
    let lastValue = "";
    let inflightXhr = null;

    if ($globalSearchObat.length && table) {
        table.on("preXhr.dt", function (e, settings) {
            if (settings.jqXHR) inflightXhr = settings.jqXHR;
        });

        const runSearch = (value) => {
            if (value === lastValue) return;
            lastValue = value;

            if (value.length < 2) {
                table.search("").draw();
                return;
            }

            table.search(value).draw();
        };

        $globalSearchObat.on("input", function () {
            const value = $(this).val().trim();

            if (inflightXhr && inflightXhr.readyState !== 4) {
                try {
                    inflightXhr.abort();
                } catch (e) {}
            }

            const delay =
                value.length <= 2 ? 300 : value.length <= 5 ? 180 : 120;

            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => runSearch(value), delay);
        });

        $globalSearchObat.on("keydown", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                clearTimeout(searchTimer);
                runSearch($(this).val().trim());
            }
        });
    }

    // ==========================
    // HELPER EXPORT
    // ==========================
    function decodeHtml(html) {
        const txt = document.createElement("textarea");
        txt.innerHTML = html;
        return txt.value;
    }

    function getExportData() {
        const headers = [];
        const rows = [];

        const colIndexes = table
            .columns(":visible")
            .indexes()
            .toArray()
            .filter((idx) => idx !== table.columns().count() - 1);

        colIndexes.forEach((idx) => {
            const text = $(table.column(idx).header()).text().trim();
            headers.push(text);
        });

        table.rows({ search: "applied" }).every(function () {
            const rowIdx = this.index();
            const rowData = [];

            colIndexes.forEach((colIdx) => {
                let cellData = table.cell(rowIdx, colIdx).data();

                if (cellData === null || cellData === undefined) {
                    cellData = "";
                } else if (typeof cellData === "object") {
                    cellData = $(cellData).text().trim();
                } else {
                    cellData = cellData
                        .toString()
                        .replace(/<[^>]*>/g, "")
                        .trim();
                    cellData = decodeHtml(cellData);
                }

                if (
                    cellData.includes('"') ||
                    cellData.includes(",") ||
                    cellData.includes("\n")
                ) {
                    cellData = '"' + cellData.replace(/"/g, '""') + '"';
                }

                rowData.push(cellData);
            });

            rows.push(rowData);
        });

        return { headers, rows };
    }

    // ==========================
    // EXPORT CSV
    // ==========================
    $("#btn-export-csv").on("click", function () {
        if (!table) return;

        const { headers, rows } = getExportData();

        let csvContent = "";
        csvContent += headers.join(",") + "\n";
        rows.forEach((r) => {
            const escapedRow = r.map((cell) => {
                if (cell == null) return "";
                const str = String(cell).replace(/"/g, '""');
                return `"${str}"`;
            });
            csvContent += escapedRow.join(",") + "\n";
        });

        const blob = new Blob([csvContent], {
            type: "text/csv;charset=utf-8;",
        });

        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, "0");
        const d = String(now.getDate()).padStart(2, "0");

        a.href = url;
        a.download = `data_obat_${y}${m}${d}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });

    // ==========================
    // PRINT PDF
    // ==========================
    $("#btn-print-obat").on("click", function () {
        const url = $(this).data("url");
        window.open(url, "_blank");
    });

    // ==========================
    // IMPORT
    // ==========================
    $("#btn-import").on("click", function () {
        $("#file-input").click();
    });

    $("#file-input").on("change", function () {
        if ($(this).val()) {
            $("#import-form").submit();
        }
    });

    // ==========================
    // PAGINATION CUSTOM
    // ==========================
    const $info = $("#obat-customInfo");
    const $pagination = $("#obat-customPagination");
    const $perPage = $("#obat-pageLength");

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages;

        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${
                info.recordsDisplay
            } data (Halaman ${currentPage} dari ${totalPages})`,
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
            table.page(parseInt($link.data("page"), 10) - 1).draw("page");
        }
    });

    $perPage.on("change", function () {
        table.page.len(parseInt($(this).val(), 10)).draw();
    });

    table.on("draw", updatePagination);
    updatePagination();
});

// ==========================
// MODAL CREATE OBAT
// ==========================
$(function () {
    const elementModalCreate = document.getElementById("modalCreateObat");
    const modalCreate = elementModalCreate
        ? new Modal(elementModalCreate, {
              backdrop: "static",
              closable: false,
          })
        : null;

    const $formCreate = $("#formModalCreate");
    const $depotContainer = $("#depot-container");

    const $depotTemplate = $depotContainer.length
        ? $depotContainer.find(".depot-row").first().clone(false)
        : null;

    let brandFarmasiSelect = null;
    let jenisObatSelect = null;
    let satuanObatSelect = null;

    function updateGlobalStock() {
        if (!$depotContainer.length) return;

        let total = 0;

        $depotContainer.find(".input-stok-depot").each(function () {
            const val = parseInt($(this).val(), 10);
            if (!isNaN(val) && val > 0) {
                total += val;
            }
        });

        $("#stok_obat").val(total);
    }

    function initRupiahFormatter(selector) {
        $(selector).on("input", function () {
            let nilai = $(this).val().replace(/\D/g, "");
            if (nilai) {
                nilai = new Intl.NumberFormat("id-ID").format(nilai);
            }
            $(this).val(nilai);
        });
    }

    function parseRupiahNumber(val) {
        val = (val || "").toString().replace(/\D/g, "");
        if (!val) return 0;
        return parseInt(val, 10);
    }

    function initBrandFarmasiSelect() {
        const el = document.getElementById("brand_farmasi_id");
        const btnClear = document.getElementById("btn-clear-brand");
        if (!el) return;

        const urlIndex = el.dataset.urlIndex;
        const urlStore = el.dataset.urlStore;
        const urlDelete = el.dataset.urlDelete;

        if (el.tomselect) {
            el.tomselect.destroy();
        }

        brandFarmasiSelect = new TomSelect(el, {
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
                        callback(res.data);
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
                const ts = brandFarmasiSelect;
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

    function initKategoriObatSelect() {
        const el = document.getElementById("kategori_obat");
        const btnClear = document.getElementById("btn-clear-kategori");
        if (!el) return;

        const urlIndex = el.dataset.urlIndex;
        const urlDelete = el.dataset.urlDelete;

        if (el.tomselect) {
            el.tomselect.destroy();
        }

        const kategoriObatSelect = new TomSelect(el, {
            valueField: "id",
            labelField: "nama_kategori_obat",
            searchField: "nama_kategori_obat",
            preload: true,
            maxItems: 1,
            placeholder: "Ketik untuk mencari / tambah kategori",

            load: function (query, callback) {
                axios
                    .get(urlIndex, { params: { q: query } })
                    .then((res) => callback(res.data || []))
                    .catch((err) => {
                        console.error("LOAD KATEGORI ERROR", err);
                        callback([]);
                    });
            },

            onChange: function (value) {
                if (!btnClear) return;
                btnClear.classList.toggle("hidden", !value);
            },
        });

        if (btnClear) {
            btnClear.onclick = function () {
                const ts = kategoriObatSelect;
                const value = ts.getValue();

                if (!value) {
                    btnClear.classList.add("hidden");
                    return;
                }

                Swal.fire({
                    icon: "warning",
                    title: "Hapus kategori ini?",
                    text: "Kategori akan hilang dari database jika belum dipakai.",
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
                            console.log("Kategori deleted:", res.data.message);
                        })
                        .catch((err) => {
                            console.error("DELETE KATEGORI ERROR", err);
                            Swal.fire({
                                icon: "error",
                                title: "Gagal menghapus kategori",
                                text:
                                    err.response?.data?.message ||
                                    "Silakan coba lagi.",
                            });
                        });
                });
            };
        }
    }

    function initJenisObatSelect() {
        const el = document.getElementById("jenis_id");
        const btnClear = document.getElementById("btn-clear-jenis");
        if (!el) return;

        const urlIndex = el.dataset.urlIndex;
        const urlStore = el.dataset.urlStore;
        const urlDelete = el.dataset.urlDelete;

        if (el.tomselect) {
            el.tomselect.destroy();
        }

        jenisObatSelect = new TomSelect(el, {
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
                const ts = jenisObatSelect;
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

    function initSatuanObatSelect() {
        const el = document.getElementById("satuan_id");
        const btnClear = document.getElementById("btn-clear-satuan");
        if (!el) return;

        const urlIndex = el.dataset.urlIndex;
        const urlStore = el.dataset.urlStore;
        const urlDelete = el.dataset.urlDelete;

        if (el.tomselect) {
            el.tomselect.destroy();
        }

        satuanObatSelect = new TomSelect(el, {
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
                const ts = satuanObatSelect;
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

    function initNamaDepotSelect($row) {
        const el = $row.find(".select-nama-depot")[0];
        const btnClear = $row.find(".btn-clear-depot")[0];
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

    function initTipeDepotSelect($row) {
        const el = $row.find(".select-tipe-depot")[0];
        const btnClear = $row.find(".btn-clear-tipe-depot")[0];
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
                        .then(() => {
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

    function resetDepotRows() {
        if (!$depotContainer.length) return;

        $depotContainer.find(".depot-row").not(":first").remove();

        const $row = $depotContainer.find(".depot-row").first();

        const selectDepot = $row.find(".select-nama-depot")[0];
        if (selectDepot && selectDepot.tomselect) {
            selectDepot.tomselect.clear();
        } else {
            $row.find(".select-nama-depot").val("");
        }
        $row.find(".btn-clear-depot").addClass("hidden");

        const selectTipe = $row.find(".select-tipe-depot")[0];
        if (selectTipe && selectTipe.tomselect) {
            selectTipe.tomselect.clear();
        } else {
            $row.find(".select-tipe-depot").val("");
        }
        $row.find(".btn-clear-tipe-depot").addClass("hidden");

        $row.find(".input-stok-depot").val(0);
        updateGlobalStock();
    }

    function resetFormCreate() {
        if ($formCreate.length === 0) return;

        $formCreate[0].reset();
        $formCreate.find(".is-invalid").removeClass("is-invalid");
        $(".text-danger").empty();

        $("#stok_obat").val(0);

        resetDepotRows();
        updateGlobalStock();

        if (
            brandFarmasiSelect &&
            document.getElementById("brand_farmasi_id")?.tomselect
        ) {
            document.getElementById("brand_farmasi_id").tomselect.clear();
        }

        $("#btn-clear-brand").addClass("hidden");
        $("#btn-clear-kategori").addClass("hidden");
        $("#btn-clear-jenis").addClass("hidden");
        $("#btn-clear-satuan").addClass("hidden");
    }

    initRupiahFormatter("#harga_beli_satuan");
    initRupiahFormatter("#harga_jual_umum");
    initRupiahFormatter("#harga_otc");

    if ($depotContainer.length) {
        const $firstRow = $depotContainer.find(".depot-row").first();
        initNamaDepotSelect($firstRow);
        initTipeDepotSelect($firstRow);
    }

    const DEFAULT_MARGIN_PERCENT = 30;

    $("#harga_beli_satuan").on("input", function () {
        const beli = parseRupiahNumber($(this).val());
        const $jual = $("#harga_jual_umum");

        if (!beli) {
            $jual.val("");
            return;
        }

        const jualBaru = Math.round(beli * (1 + DEFAULT_MARGIN_PERCENT / 100));
        $jual.val(new Intl.NumberFormat("id-ID").format(jualBaru));
    });

    $("#harga_jual_umum").on("blur", function () {
        const beli = parseRupiahNumber($("#harga_beli_satuan").val());
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

    $("#btn-open-modal-create-obat").on("click", function () {
        resetFormCreate();
        initBrandFarmasiSelect();
        initKategoriObatSelect();
        initJenisObatSelect();
        initSatuanObatSelect();

        if ($depotContainer.length) {
            const $firstRow = $depotContainer.find(".depot-row").first();
            initNamaDepotSelect($firstRow);
            initTipeDepotSelect($firstRow);
        }

        if (modalCreate) modalCreate.show();
    });

    $("#btn-close-modal-create-obat, #btn-cancel-modal-create-obat").on(
        "click",
        function () {
            resetFormCreate();
            if (modalCreate) modalCreate.hide();
        },
    );

    $("#btn-add-depot").on("click", function () {
        if (!$depotContainer.length || !$depotTemplate) return;

        const $newRow = $depotTemplate.clone(false);

        $newRow.find(".select-nama-depot").val("");
        $newRow.find(".btn-clear-depot").addClass("hidden");
        $newRow.find(".select-tipe-depot").val("");
        $newRow.find(".btn-clear-tipe-depot").addClass("hidden");
        $newRow.find(".input-stok-depot").val(0);

        $depotContainer.append($newRow);

        initNamaDepotSelect($newRow);
        initTipeDepotSelect($newRow);
        updateGlobalStock();
    });

    $(document).on("click", ".btn-remove-depot", function () {
        if (!$depotContainer.length) return;

        const $rows = $depotContainer.find(".depot-row");
        if ($rows.length <= 1) {
            resetDepotRows();
            return;
        }

        $(this).closest(".depot-row").remove();
        updateGlobalStock();
    });

    $(document).on("input", ".input-stok-depot", function () {
        updateGlobalStock();
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

        if ($depotContainer.length) {
            $depotContainer.find(".depot-row").each(function () {
                const $row = $(this);

                depot_id.push($row.find(".select-nama-depot").val() || "");
                tipe_depot.push(
                    ($row.find(".select-tipe-depot").val() || "").trim(),
                );
                stok_depot.push($row.find(".input-stok-depot").val() || 0);
            });
        }

        const formData = {
            barcode: $("#barcode").val(),
            nama_obat: $("#nama_obat").val(),
            brand_farmasi_id: $("#brand_farmasi_id").val(),
            kategori_obat: $("#kategori_obat").val(),
            jenis: $("#jenis_id").val(),
            satuan: $("#satuan_id").val(),
            dosis: $("#dosis").val(),
            stok_obat: $("#stok_obat").val(),
            expired_date: $("#expired_date").val(),
            nomor_batch: $("#nomor_batch").val(),
            harga_beli_satuan: parseRupiah($("#harga_beli_satuan").val()),
            harga_jual_umum: parseRupiah($("#harga_jual_umum").val()),
            harga_otc: parseRupiah($("#harga_otc").val()),
            kandungan: $("#kandungan").val(),
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
                    if ($("#dataObatTable").length) {
                        if (modalCreate) modalCreate.hide();
                        $("#dataObatTable")
                            .DataTable()
                            .ajax.reload(null, false);
                    } else {
                        window.location.reload();
                    }
                });
            })
            .catch((error) => {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors || {};
                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal!",
                        text: "Silahkan periksa kembali isian formulir Anda.",
                    });

                    for (const kolom in errors) {
                        if (kolom.includes(".")) {
                            const [field] = kolom.split(".");
                            $(`#${field}-error`).html(errors[kolom][0]);
                            continue;
                        }

                        $(`#${kolom}`).addClass("is-invalid");
                        $(`#${kolom}-error`).html(errors[kolom][0]);
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error Server!",
                        text: "Terjadi kesalahan server. Silahkan coba lagi.",
                    });
                    console.log("SERVER ERROR", error.message);
                }
            });
    });
});

// ==========================
// MODAL DETAIL OBAT
// ==========================
$(function () {
    const elementModalDetail = document.getElementById("modalDetailObat");
    const modalDetail = elementModalDetail
        ? new Modal(elementModalDetail, {
              backdrop: "static",
              closable: false,
          })
        : null;

    function formatRupiahDetail(value) {
        if (value === null || value === undefined || value === "") return "-";
        const num = Number(value) || 0;
        return num.toLocaleString("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        });
    }

    function formatTanggalDetail(value) {
        if (!value) return "-";

        const date = new Date(value);
        if (isNaN(date.getTime())) return value;

        return date.toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
        });
    }

    function resetDetailModal() {
        $("#detail_obat_id").val("");
        $("#detail_kode_obat").text("-");
        $("#detail_barcode").text("-");
        $("#detail_nama_obat").text("-");
        $("#detail_brand").text("-");
        $("#detail_kategori").text("-");
        $("#detail_jenis").text("-");
        $("#detail_satuan").text("-");
        $("#detail_dosis").text("-");
        $("#detail_kandungan").text("-");
        $("#detail_stok").text("-");
        $("#detail_harga_beli").text("-");
        $("#detail_harga_umum").text("-");
        $("#detail_harga_otc").text("-");
        $("#detail_expired_date").text("-");
        $("#detail_nomor_batch").text("-");

        $("#detail_batch_list").html(`
            <tr>
                <td colspan="4" class="px-3 py-4 text-center text-gray-500">
                    Belum ada data batch
                </td>
            </tr>
        `);

        $("#detail_depot_list").html(`
            <tr>
                <td colspan="4" class="px-3 py-4 text-center text-gray-500">
                    Belum ada data depot
                </td>
            </tr>
        `);
    }

    function renderBatchRows(batchs = [], obatId = null) {
        if (!Array.isArray(batchs) || batchs.length === 0) {
            $("#detail_batch_list").html(`
                <tr>
                    <td colspan="4" class="px-3 py-4 text-center text-gray-500">
                        Belum ada data batch
                    </td>
                </tr>
            `);
            return;
        }

        const html = batchs
            .map((item, index) => {
                const namaBatch = item?.nama_batch || "-";
                const expired = formatTanggalDetail(
                    item?.tanggal_kadaluarsa_obat,
                );

                return `
                    <tr class="bg-white dark:bg-gray-900">
                        <td class="px-3 py-2">${index + 1}</td>
                        <td class="px-3 py-2">${namaBatch}</td>
                        <td class="px-3 py-2">${expired}</td>
                    </tr>
                `;
            })
            .join("");

        $("#detail_batch_list").html(html);
    }

    function renderDepotRows(depots = []) {
        if (!Array.isArray(depots) || depots.length === 0) {
            $("#detail_depot_list").html(`
                <tr>
                    <td colspan="4" class="px-3 py-4 text-center text-gray-500">
                        Belum ada data depot
                    </td>
                </tr>
            `);
            return;
        }

        const html = depots
            .map((item, index) => {
                const namaDepot = item?.nama_depot || "-";
                const tipeDepot = item?.tipe_depot?.nama_tipe_depot || "-";
                const stokDepot =
                    item?.pivot?.stok_obat ?? item?.jumlah_stok_depot ?? 0;

                return `
                    <tr class="bg-white dark:bg-gray-900">
                        <td class="px-3 py-2">${index + 1}</td>
                        <td class="px-3 py-2">${namaDepot}</td>
                        <td class="px-3 py-2">${tipeDepot}</td>
                        <td class="px-3 py-2">${stokDepot}</td>
                    </tr>
                `;
            })
            .join("");

        $("#detail_depot_list").html(html);
    }

    $(document).on("click", ".btn-detail-obat", function () {
        const id = $(this).data("id");
        if (!id) return;

        resetDetailModal();

        axios
            .get(`/farmasi/obat/get-data-obat-by/${id}`)
            .then((response) => {
                const data = response.data.data || {};
                const batchPertama =
                    Array.isArray(data.batch_obat) && data.batch_obat.length
                        ? data.batch_obat[0]
                        : null;

                $("#detail_obat_id").val(data.id || "");

                $("#detail_kode_obat").text(data.kode_obat || "-");
                $("#detail_barcode").text(data.barcode || "-");
                $("#detail_nama_obat").text(data.nama_obat || "-");
                $("#detail_brand").text(data.brand_farmasi?.nama_brand || "-");
                $("#detail_kategori").text(
                    data.kategori_obat?.nama_kategori_obat || "-",
                );
                $("#detail_jenis").text(
                    data.jenis_obat?.nama_jenis_obat || "-",
                );
                $("#detail_satuan").text(
                    data.satuan_obat?.nama_satuan_obat || "-",
                );
                $("#detail_dosis").text(data.dosis || "-");
                $("#detail_kandungan").text(data.kandungan_obat || "-");

                $("#detail_stok").text(data.jumlah ?? 0);
                $("#detail_harga_beli").text(
                    formatRupiahDetail(data.total_harga),
                );
                $("#detail_harga_umum").text(
                    formatRupiahDetail(data.harga_jual_obat),
                );
                $("#detail_harga_otc").text(
                    formatRupiahDetail(data.harga_otc_obat),
                );

                $("#detail_expired_date").text(
                    formatTanggalDetail(batchPertama?.tanggal_kadaluarsa_obat),
                );
                $("#detail_nomor_batch").text(batchPertama?.nama_batch || "-");

                renderBatchRows(data.batch_obat || [], data.id);
                renderDepotRows(data.depot_obat || []);

                if (modalDetail) modalDetail.show();
            })
            .catch((error) => {
                console.error("LOAD DETAIL OBAT ERROR", error);
                Swal.fire({
                    icon: "error",
                    title: "Gagal memuat detail obat",
                    text: "Tidak dapat mengambil data detail obat. Silakan coba lagi.",
                });
            });
    });

    $("#btn-close-modal-detail-obat, #btn-cancel-modal-detail-obat").on(
        "click",
        function () {
            resetDetailModal();
            if (modalDetail) modalDetail.hide();
        },
    );

    window.getDetailObatId = function () {
        return $("#detail_obat_id").val();
    };

    window.hideDetailObatModal = function () {
        if (modalDetail) modalDetail.hide();
    };
});

// ==========================
// MODAL UPDATE OBAT
// ==========================
$(function () {
    const elementModalUpdate = document.getElementById("modalUpdateObat");
    const modalUpdate = elementModalUpdate
        ? new Modal(elementModalUpdate, {
              backdrop: "static",
              closable: false,
          })
        : null;

    const $formUpdate = $("#formModalUpdate");
    const $depotContainerUpdate = $("#depot-container-update");

    const $depotTemplateUpdate = $depotContainerUpdate.length
        ? $depotContainerUpdate.find(".depot-row").first().clone(false)
        : null;

    let brandFarmasiSelectUpdate = null;
    let jenisObatSelectUpdate = null;
    let satuanObatSelectUpdate = null;

    const DEFAULT_MARGIN_PERCENT = 30;

    function toIntSafe(val) {
        const n = parseInt((val ?? "").toString().replace(/[^\d-]/g, ""), 10);
        return Number.isFinite(n) ? n : 0;
    }

    function hitungTotalStokDepotUpdate() {
        let total = 0;

        if ($depotContainerUpdate.length) {
            $depotContainerUpdate.find(".input-stok-depot").each(function () {
                const v = toIntSafe($(this).val());
                total += v > 0 ? v : 0;
            });
        }

        $("#edit_stok_obat").val(total);
        return total;
    }

    $(document).on(
        "input change",
        "#depot-container-update .input-stok-depot",
        function () {
            hitungTotalStokDepotUpdate();
        },
    );

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

    function initBrandFarmasiSelectUpdate() {
        const el = document.getElementById("edit_brand_farmasi_id");
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

    function initKategoriObatSelectUpdate() {
        const el = document.getElementById("edit_kategori_obat");
        const btnClear = document.getElementById("btn-clear-kategori-update");
        if (!el) return;

        const urlIndex = el.dataset.urlIndex;
        const urlDelete = el.dataset.urlDelete;

        if (el.tomselect) {
            el.tomselect.destroy();
        }

        const kategoriObatSelectUpdate = new TomSelect(el, {
            valueField: "id",
            labelField: "nama_kategori_obat",
            searchField: "nama_kategori_obat",
            preload: true,
            maxItems: 1,
            placeholder: "Ketik untuk mencari / tambah kategori",

            load: function (query, callback) {
                axios
                    .get(urlIndex, { params: { q: query } })
                    .then((res) => callback(res.data || []))
                    .catch((err) => {
                        console.error("LOAD KATEGORI UPDATE ERROR", err);
                        callback([]);
                    });
            },

            onChange: function (value) {
                if (!btnClear) return;
                btnClear.classList.toggle("hidden", !value);
            },
        });

        if (btnClear) {
            btnClear.onclick = function () {
                const ts = kategoriObatSelectUpdate;
                const value = ts.getValue();

                if (!value) {
                    btnClear.classList.add("hidden");
                    return;
                }

                Swal.fire({
                    icon: "warning",
                    title: "Hapus kategori ini?",
                    text: "Kategori akan hilang dari database jika belum dipakai.",
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
                            console.log("Kategori deleted:", res.data.message);
                        })
                        .catch((err) => {
                            console.error("DELETE KATEGORI UPDATE ERROR", err);
                            Swal.fire({
                                icon: "error",
                                title: "Gagal menghapus kategori",
                                text:
                                    err.response?.data?.message ||
                                    "Silakan coba lagi.",
                            });
                        });
                });
            };
        }
    }

    function initJenisObatSelectUpdate() {
        const el = document.getElementById("edit_jenis_id");
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

    function initSatuanObatSelectUpdate() {
        const el = document.getElementById("edit_satuan_id");
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

    function initNamaDepotSelectUpdate($row) {
        const el = $row.find(".select-nama-depot")[0];
        const btnClear = $row.find(".btn-clear-depot")[0];
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

    function initTipeDepotSelectUpdate($row) {
        const el = $row.find(".select-tipe-depot")[0];
        const btnClear = $row.find(".btn-clear-tipe-depot")[0];
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
                        .then(() => {
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

    function resetDepotRowsUpdate() {
        if (!$depotContainerUpdate.length) return;

        $depotContainerUpdate.find(".depot-row").not(":first").remove();
        const $row = $depotContainerUpdate.find(".depot-row").first();

        const selectDepot = $row.find(".select-nama-depot")[0];
        if (selectDepot && selectDepot.tomselect) {
            selectDepot.tomselect.clear();
        } else {
            $row.find(".select-nama-depot").val("");
        }
        $row.find(".btn-clear-depot").addClass("hidden");

        const selectTipe = $row.find(".select-tipe-depot")[0];
        if (selectTipe && selectTipe.tomselect) {
            selectTipe.tomselect.clear();
        } else {
            $row.find(".select-tipe-depot").val("");
        }
        $row.find(".btn-clear-tipe-depot").addClass("hidden");

        $row.find(".input-stok-depot").val(0);
        hitungTotalStokDepotUpdate();
    }

    function enableDepotSection() {
        const $container = $("#depot-container-update");

        $container.find("input, select").prop("disabled", false);
        $container.find(".depot-row").removeClass("bg-gray-100/80 opacity-80");
        $container.find("input, select").removeClass("cursor-not-allowed");

        $container
            .find(".select-nama-depot, .select-tipe-depot")
            .each(function () {
                if (this.tomselect) {
                    this.tomselect.enable();
                }
            });

        $container.find(".btn-remove-depot-update").removeClass("hidden");
        $("#btn-add-depot-update").removeClass("hidden");
    }

    function disableDepotSection() {
        const $container = $("#depot-container-update");

        $container.find("input, select").prop("disabled", true);
        $container.find(".depot-row").addClass("bg-gray-100/80 opacity-80");
        $container.find("input, select").addClass("cursor-not-allowed");

        $container
            .find(".select-nama-depot, .select-tipe-depot")
            .each(function () {
                if (this.tomselect) {
                    this.tomselect.disable();
                }
            });

        $container.find(".btn-remove-depot-update").addClass("hidden");
        $container
            .find(".btn-clear-depot, .btn-clear-tipe-depot")
            .addClass("hidden");
        $("#btn-add-depot-update").addClass("hidden");
    }

    function resetFormUpdate() {
        if ($formUpdate.length === 0) return;

        $formUpdate[0].reset();
        $formUpdate.find(".is-invalid").removeClass("is-invalid");
        $(".text-danger").empty();

        $("#edit_stok_obat").val(0);
        $("#edit_batch_id").val("");

        resetDepotRowsUpdate();

        if (
            brandFarmasiSelectUpdate &&
            document.getElementById("edit_brand_farmasi_id")?.tomselect
        ) {
            document.getElementById("edit_brand_farmasi_id").tomselect.clear();
        }

        $("#btn-clear-brand-update").addClass("hidden");
        $("#btn-clear-kategori-update").addClass("hidden");
        $("#btn-clear-jenis-update").addClass("hidden");
        $("#btn-clear-satuan-update").addClass("hidden");

        enableDepotSection();
        hitungTotalStokDepotUpdate();
    }

    initRupiahFormatterUpdate("#edit_harga_beli_satuan");
    initRupiahFormatterUpdate("#edit_harga_jual_umum");
    initRupiahFormatterUpdate("#edit_harga_otc");

    if ($depotContainerUpdate.length) {
        const $firstRowUpdate = $depotContainerUpdate
            .find(".depot-row")
            .first();
        initNamaDepotSelectUpdate($firstRowUpdate);
        initTipeDepotSelectUpdate($firstRowUpdate);
    }

    $("#edit_harga_beli_satuan").on("input", function () {
        const beli = parseRupiahNumberUpdate($(this).val());
        const $jual = $("#edit_harga_jual_umum");

        if (!beli) {
            $jual.val("");
            return;
        }

        const jualBaru = Math.round(beli * (1 + DEFAULT_MARGIN_PERCENT / 100));
        $jual.val(new Intl.NumberFormat("id-ID").format(jualBaru));
    });

    $("#edit_harga_jual_umum").on("blur", function () {
        const beli = parseRupiahNumberUpdate(
            $("#edit_harga_beli_satuan").val(),
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

    function openUpdateObatModal(id, selectedBatch = null) {
        if (!id) {
            console.error("data id kosong");
            return;
        }

        resetFormUpdate();

        initBrandFarmasiSelectUpdate();
        initKategoriObatSelectUpdate();
        initJenisObatSelectUpdate();
        initSatuanObatSelectUpdate();

        axios
            .get(`/farmasi/obat/get-data-obat-by/${id}`)
            .then((response) => {
                const data = response.data.data || response.data;

                const batchTerpilih = selectedBatch
                    ? {
                          id: selectedBatch.batch_id || "",
                          nama_batch: selectedBatch.nama_batch || "",
                          tanggal_kadaluarsa_obat:
                              selectedBatch.expired_date || "",
                      }
                    : Array.isArray(data.batch_obat) && data.batch_obat.length
                      ? data.batch_obat[0]
                      : null;

                $("#edit_obat_id").val(data.id);
                $("#edit_batch_id").val(batchTerpilih?.id || "");
                $("#kode_obat").text(data.kode_obat || "-");
                $("#edit_barcode").val(data.barcode || "");
                $("#edit_nama_obat").val(data.nama_obat || "");
                $("#edit_kandungan").val(data.kandungan_obat || "");
                $("#edit_dosis").val(data.dosis || "");
                $("#edit_stok_obat").val(data.jumlah || 0);
                $("#edit_expired_date").val(
                    batchTerpilih?.tanggal_kadaluarsa_obat || "",
                );
                $("#edit_nomor_batch").val(batchTerpilih?.nama_batch || "");

                if (data.total_harga != null) {
                    $("#edit_harga_beli_satuan").val(
                        new Intl.NumberFormat("id-ID").format(
                            Number(data.total_harga),
                        ),
                    );
                }

                if (data.harga_jual_obat != null) {
                    $("#edit_harga_jual_umum").val(
                        new Intl.NumberFormat("id-ID").format(
                            Number(data.harga_jual_obat),
                        ),
                    );
                }

                if (data.harga_otc_obat != null) {
                    $("#edit_harga_otc").val(
                        new Intl.NumberFormat("id-ID").format(
                            Number(data.harga_otc_obat),
                        ),
                    );
                }

                if (brandFarmasiSelectUpdate && data.brand_farmasi) {
                    brandFarmasiSelectUpdate.clearOptions();
                    brandFarmasiSelectUpdate.addOption({
                        id: data.brand_farmasi.id,
                        nama_brand: data.brand_farmasi.nama_brand,
                    });
                    brandFarmasiSelectUpdate.setValue(
                        String(data.brand_farmasi.id),
                    );
                }

                if (jenisObatSelectUpdate && data.jenis_obat) {
                    jenisObatSelectUpdate.clearOptions();
                    jenisObatSelectUpdate.addOption({
                        id: data.jenis_obat.id,
                        nama_jenis_obat: data.jenis_obat.nama_jenis_obat,
                    });
                    jenisObatSelectUpdate.setValue(String(data.jenis_obat.id));
                }

                if (satuanObatSelectUpdate && data.satuan_obat) {
                    satuanObatSelectUpdate.clearOptions();
                    satuanObatSelectUpdate.addOption({
                        id: data.satuan_obat.id,
                        nama_satuan_obat: data.satuan_obat.nama_satuan_obat,
                    });
                    satuanObatSelectUpdate.setValue(
                        String(data.satuan_obat.id),
                    );
                }

                const kategoriSelectEl =
                    document.getElementById("edit_kategori_obat");
                if (
                    kategoriSelectEl &&
                    kategoriSelectEl.tomselect &&
                    data.kategori_obat
                ) {
                    const tsKategori = kategoriSelectEl.tomselect;
                    tsKategori.clearOptions();
                    tsKategori.addOption({
                        id: data.kategori_obat.id,
                        nama_kategori_obat:
                            data.kategori_obat.nama_kategori_obat,
                    });
                    tsKategori.setValue(String(data.kategori_obat.id));
                }

                if ($depotContainerUpdate.length) {
                    resetDepotRowsUpdate();

                    const depots = Array.isArray(data.depot_obat)
                        ? data.depot_obat
                        : [];

                    if (depots.length === 0) {
                        const $row = $depotContainerUpdate
                            .find(".depot-row")
                            .first();
                        initNamaDepotSelectUpdate($row);
                        initTipeDepotSelectUpdate($row);
                        hitungTotalStokDepotUpdate();
                    } else {
                        depots.forEach((depotItem, index) => {
                            let $row;

                            if (index === 0) {
                                $row = $depotContainerUpdate
                                    .find(".depot-row")
                                    .first();
                            } else {
                                const $newRow =
                                    $depotTemplateUpdate.clone(false);

                                $newRow.find(".select-nama-depot").val("");
                                $newRow
                                    .find(".btn-clear-depot")
                                    .addClass("hidden");
                                $newRow.find(".select-tipe-depot").val("");
                                $newRow
                                    .find(".btn-clear-tipe-depot")
                                    .addClass("hidden");
                                $newRow.find(".input-stok-depot").val(0);

                                $depotContainerUpdate.append($newRow);
                                $row = $newRow;
                            }

                            initNamaDepotSelectUpdate($row);
                            initTipeDepotSelectUpdate($row);

                            const depotSelect =
                                $row.find(".select-nama-depot")[0];
                            const tipeSelect =
                                $row.find(".select-tipe-depot")[0];

                            if (depotSelect && depotSelect.tomselect) {
                                const tsDepot = depotSelect.tomselect;
                                tsDepot.clearOptions();
                                tsDepot.addOption({
                                    id: depotItem.id,
                                    nama_depot: depotItem.nama_depot,
                                });
                                tsDepot.setValue(String(depotItem.id));
                            }

                            if (
                                tipeSelect &&
                                tipeSelect.tomselect &&
                                depotItem.tipe_depot
                            ) {
                                const tsTipe = tipeSelect.tomselect;
                                tsTipe.clearOptions();
                                tsTipe.addOption({
                                    id: depotItem.tipe_depot.id,
                                    nama_tipe_depot:
                                        depotItem.tipe_depot.nama_tipe_depot,
                                });
                                tsTipe.setValue(
                                    String(depotItem.tipe_depot.id),
                                );
                            }

                            const stok =
                                depotItem?.pivot?.stok_obat ??
                                depotItem?.jumlah_stok_depot ??
                                0;

                            $row.find(".input-stok-depot").val(stok);
                        });

                        hitungTotalStokDepotUpdate();
                    }

                    disableDepotSection();
                }

                if (window.hideDetailObatModal) {
                    window.hideDetailObatModal();
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
    }

    $(document).on("click", ".btn-update-batch", function () {
        const obatId = $(this).data("obat-id");
        const batchId = $(this).data("batch-id");
        const namaBatch = $(this).data("nama-batch");
        const expiredDate = $(this).data("expired-date");

        if (!obatId) {
            Swal.fire({
                icon: "warning",
                title: "Data tidak ditemukan",
                text: "ID obat tidak tersedia.",
            });
            return;
        }

        openUpdateObatModal(obatId, {
            batch_id: batchId,
            nama_batch: namaBatch,
            expired_date: expiredDate,
        });
    });

    $("#btn-open-modal-update-from-detail").on("click", function () {
        const id = window.getDetailObatId ? window.getDetailObatId() : null;

        if (!id) {
            Swal.fire({
                icon: "warning",
                title: "Data tidak ditemukan",
                text: "ID obat tidak tersedia.",
            });
            return;
        }

        openUpdateObatModal(id);
    });

    $("#btn-close-modal-update-obat, #btn-cancel-modal-update-obat").on(
        "click",
        function () {
            resetFormUpdate();
            if (modalUpdate) modalUpdate.hide();
        },
    );

    $("#btn-add-depot-update").on("click", function () {
        if (!$depotContainerUpdate.length || !$depotTemplateUpdate) return;

        const $newRow = $depotTemplateUpdate.clone(false);

        $newRow.find(".select-nama-depot").val("");
        $newRow.find(".btn-clear-depot").addClass("hidden");
        $newRow.find(".select-tipe-depot").val("");
        $newRow.find(".btn-clear-tipe-depot").addClass("hidden");
        $newRow.find(".input-stok-depot").val(0);

        $depotContainerUpdate.append($newRow);

        initNamaDepotSelectUpdate($newRow);
        initTipeDepotSelectUpdate($newRow);
        hitungTotalStokDepotUpdate();
    });

    $(document).on("click", ".btn-remove-depot-update", function () {
        if (!$depotContainerUpdate.length) return;

        const $rows = $depotContainerUpdate.find(".depot-row");

        if ($rows.length <= 1) {
            resetDepotRowsUpdate();
            return;
        }

        $(this).closest(".depot-row").remove();
        hitungTotalStokDepotUpdate();
    });

    $formUpdate.on("submit", function (e) {
        e.preventDefault();

        const obat_id = $("#edit_obat_id").val();

        if (!obat_id) {
            console.error("edit_obat_id kosong, tidak bisa update");
            Swal.fire({
                icon: "error",
                title: "Data tidak lengkap",
                text: "ID obat tidak ditemukan. Silakan tutup dan buka lagi form edit.",
            });
            return;
        }

        const route = `/farmasi/obat/update-data-obat/${obat_id}`;

        $(".text-danger").empty();
        $formUpdate.find(".is-invalid").removeClass("is-invalid");

        const depot_id = [];
        const tipe_depot = [];
        const stok_depot = [];

        if ($depotContainerUpdate.length) {
            $depotContainerUpdate.find(".depot-row").each(function () {
                const $row = $(this);

                depot_id.push($row.find(".select-nama-depot").val() || "");
                tipe_depot.push(
                    ($row.find(".select-tipe-depot").val() || "").trim(),
                );
                stok_depot.push($row.find(".input-stok-depot").val() || 0);
            });
        }

        const totalStok = hitungTotalStokDepotUpdate();

        const formData = {
            batch_id: $("#edit_batch_id").val(),
            barcode: $("#edit_barcode").val(),
            nama_obat: $("#edit_nama_obat").val(),
            brand_farmasi_id: $("#edit_brand_farmasi_id").val(),
            kategori_obat: $("#edit_kategori_obat").val(),
            jenis: $("#edit_jenis_id").val(),
            satuan: $("#edit_satuan_id").val(),
            dosis: $("#edit_dosis").val(),
            stok_obat: totalStok,
            expired_date: $("#edit_expired_date").val(),
            nomor_batch: $("#edit_nomor_batch").val(),
            harga_beli_satuan: parseRupiahNumberUpdate(
                $("#edit_harga_beli_satuan").val(),
            ),
            harga_jual_umum: parseRupiahNumberUpdate(
                $("#edit_harga_jual_umum").val(),
            ),
            harga_otc: parseRupiahNumberUpdate($("#edit_harga_otc").val()),
            kunci_harga_obat: $("#edit_kunci_harga_obat").is(":checked")
                ? 1
                : 0,
            kandungan: $("#edit_kandungan").val(),
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
                    if ($("#dataObatTable").length) {
                        if (modalUpdate) modalUpdate.hide();
                        $("#dataObatTable")
                            .DataTable()
                            .ajax.reload(null, false);
                    } else {
                        window.location.reload();
                    }
                });
            })
            .catch((error) => {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors || {};
                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal!",
                        text: "Silahkan periksa kembali isian formulir Anda.",
                    });

                    for (const kolom in errors) {
                        const pesan = errors[kolom][0];

                        if (kolom.includes(".")) {
                            const [field] = kolom.split(".");
                            const $errorEl = $(`#edit_${field}-error`);
                            if ($errorEl.length) {
                                $errorEl.html(pesan);
                            }
                            continue;
                        }

                        const $input = $(`#edit_${kolom}`);
                        const $error = $(`#edit_${kolom}-error`);

                        if ($input.length) {
                            $input.addClass("is-invalid");
                        }
                        if ($error.length) {
                            $error.html(pesan);
                        }
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error Server!",
                        text: "Terjadi kesalahan server. Silahkan coba lagi.",
                    });
                    console.log("SERVER ERROR UPDATE OBAT", error.message);
                }
            });
    });
});

// ==========================
// MODAL DELETE OBAT
// ==========================
$(function () {
    $("body").on("click", ".btn-delete-obat", function () {
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
                    .delete(`obat/delete-data-obat/${id}`)
                    .then((response) => {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: response.data.message,
                            showConfirmButton: false,
                            timer: 2000,
                        }).then(() => {
                            if ($("#dataObatTable").length) {
                                $("#dataObatTable")
                                    .DataTable()
                                    .ajax.reload(null, false);
                            } else {
                                window.location.reload();
                            }
                        });
                    })
                    .catch((error) => {
                        console.error("SERVER ERROR", error);
                        Swal.fire({
                            icon: "error",
                            title: "ERROR!",
                            text: "Terjadi kesalahan Server. Silahkan Coba Lagi",
                        });
                    });
            }
        });
    });
});
