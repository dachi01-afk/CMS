import axios from "axios";
import { initFlowbite } from "flowbite";
import $ from "jquery";

// data layanan
$(function () {
    function toRupiah(val) {
        const n = Number(val || 0);
        return new Intl.NumberFormat("id-ID").format(n);
    }

    var table = $("#layananTable").DataTable({
        processing: true,
        responsive: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,

        ajax: {
            url: "/layanan/get-data-layanan",
            type: "GET",
        },

        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },

            { data: "nama_layanan", name: "nama_layanan" },

            // Poli (global / spesifik)
            {
                data: "poli_label",
                name: "poli_label",
                orderable: false,
                searchable: false,
                defaultContent: "-",
                render: function (data, type, row) {
                    if (!data) return "-";

                    // optional: kasih badge sederhana kalau global
                    if (row.is_global === true || row.is_global === 1) {
                        return `<span class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-lg
                          bg-emerald-50 text-emerald-700 border border-emerald-100">
                        ${data}
                    </span>`;
                    }

                    return data; // list nama poli
                },
            },

            // Harga sebelum diskon (format Rp di FE, value tetap angka)
            {
                data: "harga_sebelum_diskon",
                name: "harga_sebelum_diskon",
                render: function (data) {
                    return "Rp " + toRupiah(data);
                },
            },

            // Diskon -> tampilkan label_diskon (Rp / %), fallback 0
            {
                data: "label_diskon",
                name: "label_diskon",
                orderable: false,
                render: function (data) {
                    return data ? data : "-";
                },
            },

            // Harga setelah diskon
            {
                data: "harga_setelah_diskon",
                name: "harga_setelah_diskon",
                render: function (data) {
                    return "Rp " + toRupiah(data);
                },
            },

            // Kategori
            {
                data: "nama_kategori",
                name: "kategoriLayanan.nama_kategori", // ✅ penting untuk serverSide (relasi)
                defaultContent: "-",
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

        rowCallback: function (row, data) {
            $(row).addClass(
                "bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
            );
            $("td", row).addClass("px-6 py-4 text-gray-900 dark:text-white");
        },
    });

    // 🔎 Search
    $("#layanan-searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    // 🔔 Info popup
    $("#btnInfoLayanan").on("click", function () {
        Swal.fire({
            icon: "info",
            title: "Informasi Layanan",
            width: "600px",
            html: `
                <div class="flex flex-col mx-4 my-2 text-center gap-2">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Setiap layanan medis yang tersedia di sistem ini harus dikaitkan dengan
                    sebuah <span class="font-medium">kategori layanan</span>.
                </p>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Kategori layanan ini berfungsi untuk mengelompokkan layanan berdasarkan
                    jenisnya, yaitu
                    <span class="font-medium">"Pemeriksaan"</span> dan 
                    <span class="font-medium">"Non Pemeriksaan"</span>.
                </p>
                </div>
            `,
            confirmButtonText: "Saya Mengerti",
        });
    });

    const $info = $("#layanan-customInfo");
    const $pagination = $("#layanan-customPagination");
    const $perPage = $("#layanan-pageLength");

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages;

        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${
                info.recordsDisplay
            } data (Halaman ${currentPage} dari ${totalPages})`
        );

        $pagination.empty();

        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(
            `<li><a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a></li>`
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

            $pagination.append(
                `<li><a href="#" class="page-number flex items-center justify-center px-3 h-8 border ${active}" data-page="${i}">${i}</a></li>`
            );
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(
            `<li><a href="#" id="btnNext" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">Next</a></li>`
        );
    }

    $pagination.on("click", "a", function (e) {
        e.preventDefault();
        const $link = $(this);
        if ($link.hasClass("opacity-50")) return;

        if ($link.attr("id") === "btnPrev") table.page("previous").draw("page");
        else if ($link.attr("id") === "btnNext")
            table.page("next").draw("page");
        else if ($link.hasClass("page-number"))
            table.page(parseInt($link.data("page")) - 1).draw("page");
    });

    $perPage.on("change", function () {
        table.page.len(parseInt($(this).val())).draw();
    });

    table.on("draw", updatePagination);
    updatePagination();
});

// ==========================
// UTIL
// ==========================
function onlyDigits(str) {
    return (str || "").toString().replace(/\D/g, "");
}

function formatRupiahInput(str) {
    // input -> "150000" => "150.000"
    const d = onlyDigits(str);
    return d.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function formatRupiahDisplay(num) {
    const n = Number(num || 0);
    return new Intl.NumberFormat("id-ID").format(n);
}

function getNumericValue(str) {
    const d = onlyDigits(str);
    return d ? parseInt(d, 10) : 0;
}

function calcHargaAkhir(hargaAwal, diskon, tipe) {
    let potongan = 0;

    if (tipe === "persen") {
        // batasin 0-100 biar aman
        const p = Math.max(0, Math.min(100, Number(diskon || 0)));
        potongan = (p / 100) * hargaAwal;
    } else {
        potongan = Number(diskon || 0);
    }

    let akhir = hargaAwal - potongan;
    if (akhir < 0) akhir = 0;

    return Math.round(akhir);
}

// helper set invalid + error text
function setInvalid($el, errorSelector, msg) {
    $el.addClass("is-invalid");
    $(errorSelector).html(msg || "");
}

function clearInvalid(selectors, errorSelectors) {
    $(selectors).removeClass("is-invalid");
    $(errorSelectors).html("");
}

// ==========================
// CREATE LAYANAN (CLEAN + TOMSELECT) - REVISI
// ==========================
$(function () {
    const addModalEl = document.getElementById("modalCreateLayanan");
    const $formAdd = $("#formCreateLayanan");

    let poliTomCreate = null;
    let poliLoaded = false;
    let poliLoading = false;

    function showModal() {
        addModalEl?.classList.remove("hidden");
        document.documentElement.classList.add("overflow-hidden");
        document.body.classList.add("overflow-hidden");

        initTomPoliCreate();
        ensurePoliLoaded();
        togglePoliSectionCreate();
    }

    function hideModal() {
        addModalEl?.classList.add("hidden");
        document.documentElement.classList.remove("overflow-hidden");
        document.body.classList.remove("overflow-hidden");
        resetAddForm();
    }

    function resetAddForm() {
        if ($formAdd[0]) $formAdd[0].reset();

        clearInvalid(
            "#kategori_layanan_id_create, #nama_layanan_create, #harga_sebelum_diskon_create, #diskon_create, #poli_id_create, #is_global_create",
            "#kategori_layanan_id_create-error, #nama_layanan_create-error, #harga_sebelum_diskon_create-error, #diskon_create-error, #harga_setelah_diskon_create-error, #poli_id_create-error, #is_global_create-error"
        );

        setDiskonInputStyleCreate();
        $("#harga_setelah_diskon_create").val("");

        if (poliTomCreate) {
            poliTomCreate.clear(true);
            poliTomCreate.enable();
        }

        togglePoliSectionCreate();
    }

    // helper: normalisasi response poli dari berbagai format
    function extractPoliList(res) {
        const payload = res?.data;

        if (Array.isArray(payload)) return payload; // [ ... ]
        if (Array.isArray(payload?.data)) return payload.data; // { data: [ ... ] }
        if (Array.isArray(payload?.data?.data)) return payload.data.data; // { data: { data: [ ... ] } }
        if (Array.isArray(payload?.result)) return payload.result; // { result: [ ... ] }

        return [];
    }

    function initTomPoliCreate() {
        if (poliTomCreate) return;

        poliTomCreate = new TomSelect("#poli_id_create", {
            plugins: ["remove_button"],
            maxItems: null, // multi
            closeAfterSelect: false, // biar bisa klik banyak tanpa nutup
            hideSelected: true,
            create: false,
            persist: false,

            placeholder: "Ketik untuk mencari poli...",
            searchField: ["text"], // cari dari teks option (substring)

            dropdownParent: "body", // aman dari overflow modal
        });

        // ✅ mirip seperti screenshot: begitu fokus, dropdown langsung tampil
        poliTomCreate.on("focus", () => poliTomCreate.open());
        poliTomCreate.on("type", () => poliTomCreate.open());
    }

    function ensurePoliLoaded() {
        initTomPoliCreate();
        if (!poliTomCreate) return;

        if (poliLoaded || poliLoading) return;
        poliLoading = true;

        axios
            .get("/poli/get-data-poli", {
                headers: { Accept: "application/json" },
            })
            .then((res) => {
                const payload = res.data;

                // support format response apa pun yg umum
                const list = Array.isArray(payload)
                    ? payload
                    : Array.isArray(payload?.data)
                    ? payload.data
                    : Array.isArray(payload?.data?.data)
                    ? payload.data.data
                    : [];

                // ✅ cara paling mirip screenshot: isi <option> dulu
                const sel = document.getElementById("poli_id_create");
                sel.innerHTML = "";

                list.forEach((item) => {
                    const opt = document.createElement("option");
                    opt.value = String(item.id);
                    opt.textContent = item.nama_poli;
                    sel.appendChild(opt);
                });

                // ✅ refresh tomselect setelah option dimasukkan
                poliTomCreate.clearOptions();
                poliTomCreate.sync(); // baca ulang option dari DOM
                poliTomCreate.refreshOptions(false);

                poliLoaded = true;
            })
            .catch((err) => {
                console.error("Gagal load poli:", err);
            })
            .finally(() => {
                poliLoading = false;
            });
    }

    // --------------------------
    // TOGGLE GLOBAL
    // --------------------------
    function togglePoliSectionCreate() {
        const isGlobal = $("#is_global_create").is(":checked");
        const $poliSection = $("#poli_section_create");

        if (isGlobal) {
            $poliSection.addClass("hidden");
            if (poliTomCreate) {
                poliTomCreate.clear(true);
                poliTomCreate.disable();
            }
        } else {
            $poliSection.removeClass("hidden");
            if (poliTomCreate) poliTomCreate.enable();
            ensurePoliLoaded();
        }
    }

    function setDiskonInputStyleCreate() {
        const tipe = $("#diskon_tipe_create").val();
        const $input = $("#diskon_create");
        const $prefix = $("#diskon_prefix_rp_create");

        if (tipe === "nominal") {
            $prefix.removeClass("hidden");
            $input.addClass("pl-10").removeClass("pl-3");
            $input.attr("placeholder", "0");
        } else {
            $prefix.addClass("hidden");
            $input.removeClass("pl-10").addClass("pl-3");
            $input.attr("placeholder", "0 - 100");
        }
    }

    function hitungHargaCreate() {
        const hargaAwal = getNumericValue(
            $("#harga_sebelum_diskon_create").val()
        );
        const tipe = $("#diskon_tipe_create").val();

        let diskonVal = getNumericValue($("#diskon_create").val());
        if (tipe === "persen")
            diskonVal = Math.max(0, Math.min(100, diskonVal));

        const akhir = calcHargaAkhir(hargaAwal, diskonVal, tipe);
        $("#harga_setelah_diskon_create").val(formatRupiahDisplay(akhir));
    }

    // ========= Events =========
    $("#buttonModalCreateLayanan").on("click", function () {
        resetAddForm();
        showModal();
    });

    $(
        "#buttonCloseModalCreateLayanan, #buttonCloseModalCreateLayanan_footer"
    ).on("click", function () {
        hideModal();
    });

    $("#is_global_create").on("change", togglePoliSectionCreate);

    $("#harga_sebelum_diskon_create").on("input", function () {
        $(this).val(formatRupiahInput($(this).val()));
        hitungHargaCreate();
    });

    $("#diskon_tipe_create").on("change", function () {
        $("#diskon_create").val("");
        setDiskonInputStyleCreate();
        hitungHargaCreate();
    });

    $("#diskon_create").on("input", function () {
        const tipe = $("#diskon_tipe_create").val();
        const raw = $(this).val();

        if (tipe === "nominal") {
            $(this).val(formatRupiahInput(raw));
        } else {
            let p = getNumericValue(raw);
            p = Math.max(0, Math.min(100, p));
            $(this).val(p ? String(p) : "");
        }
        hitungHargaCreate();
    });

    setDiskonInputStyleCreate();

    // ========= Submit =========
    $formAdd.on("submit", function (e) {
        e.preventDefault();

        const url = $formAdd.data("url");

        clearInvalid(
            "#kategori_layanan_id_create, #nama_layanan_create, #harga_sebelum_diskon_create, #diskon_create, #poli_id_create, #is_global_create",
            "#kategori_layanan_id_create-error, #nama_layanan_create-error, #harga_sebelum_diskon_create-error, #diskon_create-error, #harga_setelah_diskon_create-error, #poli_id_create-error, #is_global_create-error"
        );

        const hargaSebelum = getNumericValue(
            $("#harga_sebelum_diskon_create").val()
        );
        const tipe = $("#diskon_tipe_create").val();
        const diskon = getNumericValue($("#diskon_create").val());
        const hargaSetelah = getNumericValue(
            $("#harga_setelah_diskon_create").val()
        );

        const isGlobal = $("#is_global_create").is(":checked");

        // ambil dari TomSelect (pastikan array)
        let poliIds = [];
        if (!isGlobal && poliTomCreate) {
            const v = poliTomCreate.getValue();
            poliIds = Array.isArray(v) ? v : v ? String(v).split(",") : [];
        }

        const formData = {
            kategori_layanan_id: $("#kategori_layanan_id_create").val(),
            nama_layanan: $("#nama_layanan_create").val(),
            diskon_tipe: tipe,
            diskon:
                tipe === "persen" ? Math.max(0, Math.min(100, diskon)) : diskon,
            harga_sebelum_diskon: hargaSebelum,
            harga_setelah_diskon: hargaSetelah,
            is_global: isGlobal ? 1 : 0,

            // ✅ FIX: harus poli_ids (bukan poli_id)
            poli_id: poliIds,
        };

        axios
            .post(url, formData)
            .then((response) => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text:
                        response.data.message ||
                        "Data layanan berhasil disimpan.",
                    timer: 2000,
                    showConfirmButton: false,
                });

                hideModal();

                if ($.fn.DataTable && $("#layananTable").length) {
                    $("#layananTable").DataTable().ajax.reload(null, false);
                }
            })
            .catch((error) => {
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors || {};

                    const map = {
                        kategori_layanan_id: {
                            el: "#kategori_layanan_id_create",
                            err: "#kategori_layanan_id_create-error",
                        },
                        nama_layanan: {
                            el: "#nama_layanan_create",
                            err: "#nama_layanan_create-error",
                        },
                        harga_sebelum_diskon: {
                            el: "#harga_sebelum_diskon_create",
                            err: "#harga_sebelum_diskon_create-error",
                        },
                        diskon: {
                            el: "#diskon_create",
                            err: "#diskon_create-error",
                        },
                        harga_setelah_diskon: {
                            el: "#harga_setelah_diskon_create",
                            err: "#harga_setelah_diskon_create-error",
                        },
                        diskon_tipe: {
                            el: "#diskon_tipe_create",
                            err: "#diskon_create-error",
                        },
                        is_global: {
                            el: "#is_global_create",
                            err: "#is_global_create-error",
                        },
                        poli_ids: {
                            el: "#poli_id_create",
                            err: "#poli_id_create-error",
                        },
                    };

                    Object.keys(errors).forEach((field) => {
                        if (map[field]) {
                            setInvalid(
                                $(map[field].el),
                                map[field].err,
                                errors[field][0]
                            );
                        }
                    });

                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal",
                        text: "Periksa kembali input Anda.",
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error Server",
                        text:
                            error.response?.data?.message ||
                            "Terjadi kesalahan server.",
                    });
                }
            });
    });
});

// ==========================
// UPDATE LAYANAN (dengan Global + Poli)
// ==========================
$(function () {
    const editModalEl = document.getElementById("modalUpdateLayanan");
    const editModal = editModalEl
        ? new Modal(editModalEl, { backdrop: "static", closable: false })
        : null;

    const $formEdit = $("#formUpdateLayanan");
    const $selectKategori = $("#kategori_layanan_id_update");

    // TOMSELECT POLI UPDATE
    let poliTomUpdate = null;
    let poliLoadedUpdate = false;
    let poliLoadingUpdate = false;

    function initTomPoliUpdate() {
        if (poliTomUpdate) return;

        poliTomUpdate = new TomSelect("#poli_id_update", {
            plugins: ["remove_button"],
            maxItems: null,
            closeAfterSelect: false,
            hideSelected: true,
            create: false,
            persist: false,
            placeholder: "Ketik untuk mencari poli...",
            searchField: ["text"],
            dropdownParent: "body",
        });

        poliTomUpdate.on("focus", () => poliTomUpdate.open());
        poliTomUpdate.on("type", () => poliTomUpdate.open());
    }

    function ensurePoliLoadedUpdate() {
        initTomPoliUpdate();
        if (!poliTomUpdate) return;
        if (poliLoadedUpdate || poliLoadingUpdate) return;

        poliLoadingUpdate = true;

        axios
            .get("/poli/get-data-poli", {
                headers: { Accept: "application/json" },
            })
            .then((res) => {
                const payload = res.data;
                const list = Array.isArray(payload)
                    ? payload
                    : Array.isArray(payload?.data)
                    ? payload.data
                    : Array.isArray(payload?.data?.data)
                    ? payload.data.data
                    : [];

                const sel = document.getElementById("poli_id_update");
                sel.innerHTML = "";

                list.forEach((item) => {
                    const opt = document.createElement("option");
                    opt.value = String(item.id);
                    opt.textContent = item.nama_poli;
                    sel.appendChild(opt);
                });

                poliTomUpdate.clearOptions();
                poliTomUpdate.sync();
                poliTomUpdate.refreshOptions(false);

                poliLoadedUpdate = true;
            })
            .catch((err) => console.error("Gagal load poli (update):", err))
            .finally(() => (poliLoadingUpdate = false));
    }

    function togglePoliSectionUpdate() {
        const isGlobal = $("#is_global_update").is(":checked");
        const $poliSection = $("#poli_section_update");

        if (isGlobal) {
            $poliSection.addClass("hidden");
            if (poliTomUpdate) {
                poliTomUpdate.clear(true);
                poliTomUpdate.disable();
            }
        } else {
            $poliSection.removeClass("hidden");
            if (poliTomUpdate) poliTomUpdate.enable();
            ensurePoliLoadedUpdate();
        }
    }

    function resetEditForm() {
        if ($formEdit[0]) $formEdit[0].reset();

        clearInvalid(
            "#kategori_layanan_id_update, #nama_layanan_update, #harga_sebelum_diskon_update, #diskon_update, #is_global_update, #poli_id_update",
            "#kategori_layanan_id_update-error, #nama_layanan_update-error, #harga_sebelum_diskon_update-error, #diskon_update-error, #harga_setelah_diskon_update-error, #is_global_update-error, #poli_id_update-error"
        );

        setDiskonInputStyleUpdate();
        $("#harga_setelah_diskon_update").val("");

        initTomPoliUpdate();
        if (poliTomUpdate) {
            poliTomUpdate.clear(true);
            poliTomUpdate.enable();
        }

        // default: tidak global (supaya poli tampil)
        $("#is_global_update").prop("checked", false);
        togglePoliSectionUpdate();
    }

    function setDiskonInputStyleUpdate() {
        const tipe = $("#diskon_tipe_update").val();
        const $input = $("#diskon_update");
        const $prefix = $("#diskon_prefix_rp_update");

        if (tipe === "nominal") {
            $prefix.removeClass("hidden");
            $input.addClass("pl-10").removeClass("pl-3");
            $input.attr("placeholder", "0");
        } else {
            $prefix.addClass("hidden");
            $input.removeClass("pl-10").addClass("pl-3");
            $input.attr("placeholder", "0 - 100");
        }
    }

    function hitungHargaUpdate() {
        const hargaAwal = getNumericValue(
            $("#harga_sebelum_diskon_update").val()
        );
        const tipe = $("#diskon_tipe_update").val();

        let diskonVal = getNumericValue($("#diskon_update").val());
        if (tipe === "persen")
            diskonVal = Math.max(0, Math.min(100, diskonVal));

        const akhir = calcHargaAkhir(hargaAwal, diskonVal, tipe);
        $("#harga_setelah_diskon_update").val(formatRupiahDisplay(akhir));
    }

    function loadKategoriUpdate(selectedId = null) {
        $selectKategori.html(`<option value="">Memuat kategori...</option>`);

        axios.get("/kategori_layanan/get-data-kategori-layanan").then((res) => {
            const list = res.data.data || [];
            $selectKategori.html(
                `<option value="">Pilih kategori layanan</option>`
            );

            list.forEach((item) => {
                $selectKategori.append(
                    `<option value="${item.id}">${item.nama_kategori}</option>`
                );
            });

            if (selectedId) $selectKategori.val(selectedId);
        });
    }

    // events input format
    $("#harga_sebelum_diskon_update").on("input", function () {
        $(this).val(formatRupiahInput($(this).val()));
        hitungHargaUpdate();
    });

    $("#diskon_tipe_update").on("change", function () {
        $("#diskon_update").val("");
        setDiskonInputStyleUpdate();
        hitungHargaUpdate();
    });

    $("#diskon_update").on("input", function () {
        const tipe = $("#diskon_tipe_update").val();
        const raw = $(this).val();

        if (tipe === "nominal") {
            $(this).val(formatRupiahInput(raw));
        } else {
            let p = getNumericValue(raw);
            p = Math.max(0, Math.min(100, p));
            $(this).val(p ? String(p) : "");
        }

        hitungHargaUpdate();
    });

    // toggle global
    $("#is_global_update").on("change", togglePoliSectionUpdate);

    setDiskonInputStyleUpdate();

    // klik edit
    $("body").on("click", ".btn-edit-layanan", function () {
        resetEditForm();

        const id = $(this).data("id");

        axios
            .get(`layanan/get-data-layanan-by-id/${id}`)
            .then((response) => {
                const layanan = response.data.data;

                $("#id_update").val(layanan.id);
                $("#nama_layanan_update").val(layanan.nama_layanan);

                $("#harga_sebelum_diskon_update").val(
                    formatRupiahDisplay(layanan.harga_sebelum_diskon)
                );
                $("#harga_setelah_diskon_update").val(
                    formatRupiahDisplay(layanan.harga_setelah_diskon)
                );

                const tipe = layanan.diskon_tipe
                    ? layanan.diskon_tipe
                    : layanan.diskon >= 1 && layanan.diskon <= 100
                    ? "persen"
                    : "nominal";

                $("#diskon_tipe_update").val(tipe);
                setDiskonInputStyleUpdate();

                if (layanan.diskon) {
                    if (tipe === "persen") {
                        $("#diskon_update").val(
                            String(
                                Math.max(
                                    0,
                                    Math.min(100, Number(layanan.diskon))
                                )
                            )
                        );
                    } else {
                        $("#diskon_update").val(
                            formatRupiahDisplay(layanan.diskon)
                        );
                    }
                } else {
                    $("#diskon_update").val("");
                }

                loadKategoriUpdate(layanan.kategori_layanan_id);

                // ✅ GLOBAL + POLI
                const isGlobal = Number(layanan.is_global || 0) === 1;
                $("#is_global_update").prop("checked", isGlobal);

                ensurePoliLoadedUpdate();

                // setelah poli ter-load, set value
                // layanan.poli_id harus array ID (kita buat dari backend)
                setTimeout(() => {
                    togglePoliSectionUpdate();

                    if (!isGlobal && poliTomUpdate) {
                        const poliIds = Array.isArray(layanan.poli_id)
                            ? layanan.poli_id
                            : [];
                        poliTomUpdate.setValue(poliIds.map(String), true);
                    }
                }, 150);

                editModal?.show();
            })
            .catch(() => {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: "Tidak dapat memuat data layanan.",
                });
            });
    });

    // submit update
    $formEdit.on("submit", function (e) {
        e.preventDefault();

        clearInvalid(
            "#kategori_layanan_id_update, #nama_layanan_update, #harga_sebelum_diskon_update, #diskon_update, #is_global_update, #poli_id_update",
            "#kategori_layanan_id_update-error, #nama_layanan_update-error, #harga_sebelum_diskon_update-error, #diskon_update-error, #harga_setelah_diskon_update-error, #is_global_update-error, #poli_id_update-error"
        );

        const tipe = $("#diskon_tipe_update").val();
        const diskon = getNumericValue($("#diskon_update").val());

        const isGlobal = $("#is_global_update").is(":checked");

        let poliId = [];
        if (!isGlobal && poliTomUpdate) {
            const v = poliTomUpdate.getValue();
            poliId = Array.isArray(v) ? v : v ? String(v).split(",") : [];
        }

        const formData = {
            id: $("#id_update").val(),
            kategori_layanan_id: $("#kategori_layanan_id_update").val(),
            nama_layanan: $("#nama_layanan_update").val(),
            diskon_tipe: tipe,
            diskon:
                tipe === "persen" ? Math.max(0, Math.min(100, diskon)) : diskon,
            harga_sebelum_diskon: getNumericValue(
                $("#harga_sebelum_diskon_update").val()
            ),
            harga_setelah_diskon: getNumericValue(
                $("#harga_setelah_diskon_update").val()
            ),

            // ✅ global & poli_id
            is_global: isGlobal ? 1 : 0,
            poli_id: poliId,
        };

        axios
            .post($formEdit.data("url"), formData)
            .then((response) => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text:
                        response.data.message ||
                        "Data layanan berhasil diperbarui.",
                    timer: 2000,
                    showConfirmButton: false,
                });

                editModal?.hide();
                $("#layananTable").DataTable().ajax.reload(null, false);
            })
            .catch((error) => {
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors || {};

                    const map = {
                        kategori_layanan_id: {
                            el: "#kategori_layanan_id_update",
                            err: "#kategori_layanan_id_update-error",
                        },
                        nama_layanan: {
                            el: "#nama_layanan_update",
                            err: "#nama_layanan_update-error",
                        },
                        harga_sebelum_diskon: {
                            el: "#harga_sebelum_diskon_update",
                            err: "#harga_sebelum_diskon_update-error",
                        },
                        diskon: {
                            el: "#diskon_update",
                            err: "#diskon_update-error",
                        },
                        harga_setelah_diskon: {
                            el: "#harga_setelah_diskon_update",
                            err: "#harga_setelah_diskon_update-error",
                        },
                        diskon_tipe: {
                            el: "#diskon_tipe_update",
                            err: "#diskon_update-error",
                        },
                        is_global: {
                            el: "#is_global_update",
                            err: "#is_global_update-error",
                        },
                        poli_id: {
                            el: "#poli_id_update",
                            err: "#poli_id_update-error",
                        },
                    };

                    Object.keys(errors).forEach((field) => {
                        if (map[field]) {
                            setInvalid(
                                $(map[field].el),
                                map[field].err,
                                errors[field][0]
                            );
                        }
                    });

                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal!",
                        text: "Periksa kembali input Anda.",
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error Server",
                        text:
                            error.response?.data?.message ||
                            "Terjadi kesalahan server.",
                    });
                }
            });
    });

    // close modal update
    $(
        "#buttonCloseModalUpdateLayanan, #buttonCloseModalUpdateLayanan_footer"
    ).on("click", function () {
        editModal?.hide();
        resetEditForm();
    });
});

// delete data
$(function () {
    $("body").on("click", ".btn-delete-layanan", function () {
        const id = $(this).data("id");
        if (!id) return;

        const formData = {
            id: id,
        };

        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, hapus!",
            cancelButtonText: "Batal",
        }).then((result) => {
            if (result.isConfirmed) {
                axios
                    .post(`/layanan/delete-data-layanan`, formData)
                    .then((response) => {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: response.data.message,
                            showConfirmButton: false,
                            timer: 1500,
                        }).then(() => {
                            if ($("#layananTable").length) {
                                $("#layananTable")
                                    .DataTable()
                                    .ajax.reload(null, false);
                            } else {
                                window.location.reload();
                            }
                        });
                    })
                    .catch((error) => {
                        console.error("SERVER ERROR:", error);
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text: "Terjadi kesalahan server. Silakan coba lagi.",
                        });
                    });
            }
        });
    });
});
