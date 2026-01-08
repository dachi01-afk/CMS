import axios from "axios";
import { initFlowbite } from "flowbite";
import $ from "jquery";

// data layanan
$(function () {
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
        ajax: "/layanan/get-data-layanan",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "nama_layanan", name: "nama_layanan" },
            {
                data: "harga_layanan",
                name: "harga_layanan",
                orderable: false,
            },
            { data: "nama_kategori", name: "nama_kategori" },
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

    // 🔔 Ketika tombol "Penting!" diklik → munculkan pop up
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
// CREATE LAYANAN
// ==========================
$(function () {
    const addModalEl = document.getElementById("modalCreateLayanan");
    const $formAdd = $("#formCreateLayanan");

    function showModal() {
        addModalEl?.classList.remove("hidden");
    }

    function hideModal() {
        addModalEl?.classList.add("hidden");
        resetAddForm();
    }

    function resetAddForm() {
        if ($formAdd[0]) $formAdd[0].reset();

        clearInvalid(
            "#kategori_layanan_id_create, #nama_layanan_create, #harga_sebelum_diskon_create, #diskon_create",
            "#kategori_layanan_id_create-error, #nama_layanan_create-error, #harga_sebelum_diskon_create-error, #diskon_create-error, #harga_setelah_diskon_create-error"
        );

        // reset tampilan prefix diskon
        setDiskonInputStyleCreate();
        $("#harga_setelah_diskon_create").val("");
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

        let diskonVal = 0;
        if (tipe === "persen") {
            diskonVal = getNumericValue($("#diskon_create").val());
            diskonVal = Math.max(0, Math.min(100, diskonVal));
        } else {
            diskonVal = getNumericValue($("#diskon_create").val());
        }

        const akhir = calcHargaAkhir(hargaAwal, diskonVal, tipe);
        $("#harga_setelah_diskon_create").val(formatRupiahDisplay(akhir));
    }

    // open/close
    $("#buttonModalCreateLayanan").on("click", function () {
        resetAddForm();
        showModal();
    });

    $(
        "#buttonCloseModalCreateLayanan, #buttonCloseModalCreateLayanan_footer"
    ).on("click", function () {
        hideModal();
    });

    // harga input format + hitung
    $("#harga_sebelum_diskon_create").on("input", function () {
        $(this).val(formatRupiahInput($(this).val()));
        hitungHargaCreate();
    });

    // diskon tipe berubah
    $("#diskon_tipe_create").on("change", function () {
        $("#diskon_create").val("");
        setDiskonInputStyleCreate();
        hitungHargaCreate();
    });

    // diskon input
    $("#diskon_create").on("input", function () {
        const tipe = $("#diskon_tipe_create").val();
        const raw = $(this).val();

        if (tipe === "nominal") {
            $(this).val(formatRupiahInput(raw));
        } else {
            // persen hanya angka, batasi 0-100
            let p = getNumericValue(raw);
            p = Math.max(0, Math.min(100, p));
            $(this).val(p ? String(p) : "");
        }

        hitungHargaCreate();
    });

    // init style
    setDiskonInputStyleCreate();

    // submit
    $formAdd.on("submit", function (e) {
        e.preventDefault();

        const url = $formAdd.data("url");

        clearInvalid(
            "#kategori_layanan_id_create, #nama_layanan_create, #harga_sebelum_diskon_create, #diskon_create",
            "#kategori_layanan_id_create-error, #nama_layanan_create-error, #harga_sebelum_diskon_create-error, #diskon_create-error, #harga_setelah_diskon_create-error"
        );

        const hargaSebelum = getNumericValue(
            $("#harga_sebelum_diskon_create").val()
        );
        const tipe = $("#diskon_tipe_create").val();
        const diskon = getNumericValue($("#diskon_create").val());
        const hargaSetelah = getNumericValue(
            $("#harga_setelah_diskon_create").val()
        );

        const formData = {
            kategori_layanan_id: $("#kategori_layanan_id_create").val(),
            nama_layanan: $("#nama_layanan_create").val(),
            diskon_tipe: tipe,
            diskon:
                tipe === "persen" ? Math.max(0, Math.min(100, diskon)) : diskon,
            harga_sebelum_diskon: hargaSebelum,
            harga_setelah_diskon: hargaSetelah,
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
                $("#layananTable").DataTable().ajax.reload(null, false);
            })
            .catch((error) => {
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors || {};

                    // mapping error -> selector input + div error
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
// UPDATE LAYANAN
// ==========================
$(function () {
    const editModalEl = document.getElementById("modalUpdateLayanan");
    const editModal = editModalEl
        ? new Modal(editModalEl, { backdrop: "static", closable: false })
        : null;

    const $formEdit = $("#formUpdateLayanan");
    const $selectKategori = $("#kategori_layanan_id_update");

    function resetEditForm() {
        if ($formEdit[0]) $formEdit[0].reset();

        clearInvalid(
            "#kategori_layanan_id_update, #nama_layanan_update, #harga_sebelum_diskon_update, #diskon_update",
            "#kategori_layanan_id_update-error, #nama_layanan_update-error, #harga_sebelum_diskon_update-error, #diskon_update-error, #harga_setelah_diskon_update-error"
        );

        setDiskonInputStyleUpdate();
        $("#harga_setelah_diskon_update").val("");
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

    // events format input
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

    // init style
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

                // jika backend punya diskon_tipe, pakai itu. kalau tidak, fallback inferensi.
                const tipe = layanan.diskon_tipe
                    ? layanan.diskon_tipe
                    : layanan.diskon >= 1 && layanan.diskon <= 100
                    ? "persen"
                    : "nominal";

                $("#diskon_tipe_update").val(tipe);
                setDiskonInputStyleUpdate();

                if (layanan.diskon) {
                    if (tipe === "persen")
                        $("#diskon_update").val(
                            String(
                                Math.max(
                                    0,
                                    Math.min(100, Number(layanan.diskon))
                                )
                            )
                        );
                    else
                        $("#diskon_update").val(
                            formatRupiahDisplay(layanan.diskon)
                        );
                } else {
                    $("#diskon_update").val("");
                }

                loadKategoriUpdate(layanan.kategori_layanan_id);
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
            "#kategori_layanan_id_update, #nama_layanan_update, #harga_sebelum_diskon_update, #diskon_update",
            "#kategori_layanan_id_update-error, #nama_layanan_update-error, #harga_sebelum_diskon_update-error, #diskon_update-error, #harga_setelah_diskon_update-error"
        );

        const tipe = $("#diskon_tipe_update").val();
        const diskon = getNumericValue($("#diskon_update").val());

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
