import axios from "axios";
import { initFlowbite } from "flowbite";
import $ from "jquery";

// data poli
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
                render: function (data) {
                    if (!data) return "-";
                    const formatted = Number(data).toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                        minimumFractionDigits: 0,
                    });
                    return formatted; // hasilnya: Rp1.000.000
                },
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

// Create Data Layanan
$(function () {
    const addModalEl = document.getElementById("modalCreateLayanan");
    const $formAdd = $("#formCreateLayanan");

    // Fungsi untuk reset form: bersihkan input, hapus class error, dan kosongkan pesan error
    function resetAddForm() {
        if ($formAdd[0]) {
            $formAdd[0].reset();
        }
        // Hapus class error untuk semua field yang mungkin divalidasi
        $(
            "#kategori_layanan_id_create, #nama_layanan_create, #harga_layanan_create"
        ).removeClass("is-invalid");
        // Kosongkan pesan error
        $(
            "#kategori_layanan_id-error, #nama_layanan-error, #harga_layanan-error"
        ).html("");
    }

    // Fungsi untuk show modal
    function showModal() {
        addModalEl.classList.remove("hidden");
    }

    // Fungsi untuk hide modal
    function hideModal() {
        addModalEl.classList.add("hidden");
        resetAddForm();
    }

    // Event: Buka modal saat tombol "Tambah" diklik
    $("#buttonModalCreateLayanan").on("click", function () {
        resetAddForm();
        showModal();
    });

    // Event: Tutup modal saat tombol close diklik
    $(
        "#buttonCloseModalCreateLayanan, #buttonCloseModalCreateLayanan_footer"
    ).on("click", function () {
        hideModal();
    });

    // 💰 Auto Format Rupiah Input (tampilan saja)
    $("#harga_layanan_create").on("input", function () {
        let value = $(this).val().replace(/\D/g, ""); // Hapus semua selain angka
        if (value) {
            value = new Intl.NumberFormat("id-ID").format(value);
        }
        $(this).val(value);
    });

    // Event: Submit form untuk create data
    $formAdd.on("submit", function (e) {
        e.preventDefault();
        const url = $formAdd.data("url");

        // Ambil nilai dan bersihkan harga dari pemisah ribuan
        const rawHarga = $("#harga_layanan_create").val() || "";
        const hargaNumeric = rawHarga.replace(/\D/g, ""); // Kirim hanya digit ke backend

        // Kumpulkan data form (sesuai skema: kategori_layanan_id, nama_layanan, harga_layanan)
        // Note: poli_id tidak dikirim karena tidak ada di modal (tambah jika perlu)
        const formData = {
            kategori_layanan_id: $("#kategori_layanan_id_create").val(),
            nama_layanan: $("#nama_layanan_create").val(),
            harga_layanan: hargaNumeric,
        };

        // Kirim request POST menggunakan Axios
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
                // Bersihkan error lama dulu
                $(
                    "#kategori_layanan_id_create, #nama_layanan_create, #harga_layanan_create"
                ).removeClass("is-invalid");
                $(
                    "#kategori_layanan_id-error, #nama_layanan-error, #harga_layanan-error"
                ).html("");

                if (error.response?.status === 422) {
                    const errors = error.response.data.errors || {};

                    // Mapping field ke selector input
                    const fieldMapping = {
                        kategori_layanan_id: "#kategori_layanan_id_create",
                        nama_layanan: "#nama_layanan_create",
                        harga_layanan: "#harga_layanan_create",
                    };

                    for (const field in errors) {
                        const inputSelector = fieldMapping[field];
                        if (inputSelector) {
                            $(inputSelector).addClass("is-invalid");
                        }
                        $(`#${field}-error`).html(errors[field][0]);
                    }

                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal!",
                        text: "Periksa kembali input Anda.",
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error Server!",
                        text:
                            error.response?.data?.message ||
                            "Terjadi kesalahan server.",
                    });
                }
            });
    });
});

// update data layanan
$(function () {
    const editModalEl = document.getElementById("modalUpdateLayanan");
    const editModal = editModalEl
        ? new Modal(editModalEl, {
              backdrop: "static",
              closable: false,
          })
        : null;
    const $formEdit = $("#formUpdateLayanan");
    const $selectKategori = $("#kategori_layanan_id_update");

    function resetEditForm() {
        if ($formEdit[0]) $formEdit[0].reset();

        $(
            "#nama_layanan_update, #harga_layanan_update, #kategori_layanan_id_update"
        ).removeClass("is-invalid");

        $(
            "#nama_layanan-error, #harga_layanan-error, #kategori_layanan_id-error"
        ).html("");
    }

    /**
     * Load kategori layanan ke dalam dropdown UPDATE
     * lalu set selected berdasarkan kategori_layanan_id
     */
    function loadKategoriUpdate(selectedId = null) {
        $selectKategori.html(`<option value="">Memuat kategori...</option>`);

        axios.get("/kategori_layanan/get-data-kategori-layanan").then((res) => {
            const list = res.data.data || [];

            $selectKategori.html(
                `<option value="">Pilih kategori layanan</option>`
            );

            list.forEach((item) => {
                $selectKategori.append(`
                        <option value="${item.id}">
                            ${item.nama_kategori}
                        </option>
                    `);
            });

            // Set selected value berdasarkan ID layanan yg sedang di-edit
            if (selectedId) {
                $selectKategori.val(selectedId);
            }
        });
    }

    // Auto format rupiah
    $("#harga_layanan_update").on("input", function () {
        let value = $(this).val().replace(/\D/g, "");
        if (value) value = new Intl.NumberFormat("id-ID").format(value);
        $(this).val(value);
    });

    // Klik tombol edit
    $("body").on("click", ".btn-edit-layanan", function () {
        resetEditForm();

        const id = $(this).data("id");

        axios
            .get(`layanan/get-data-layanan-by-id/${id}`)
            .then((response) => {
                const layanan = response.data.data;

                // Isi form awal
                $("#id_update").val(layanan.id);
                $("#nama_layanan_update").val(layanan.nama_layanan);

                $("#harga_layanan_update").val(
                    new Intl.NumberFormat("id-ID").format(layanan.harga_layanan)
                );

                /**
                 * INI YANG PALING PENTING:
                 * loadKategoriUpdate() HARUS diberikan
                 * layanan.kategori_layanan_id
                 * (bukan layanan.kategoriLayanan.id)
                 */
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

    // Submit form update
    $formEdit.on("submit", function (e) {
        e.preventDefault();

        const formData = {
            id: $("#id_update").val(),
            kategori_layanan_id: $("#kategori_layanan_id_update").val(),
            nama_layanan: $("#nama_layanan_update").val(),
            harga_layanan: $("#harga_layanan_update").val().replace(/\D/g, ""),
        };

        axios
            .post($formEdit.data("url"), formData)
            .then((response) => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: response.data.message,
                    timer: 2000,
                    showConfirmButton: false,
                });

                editModal?.hide();
                $("#layananTable").DataTable().ajax.reload(null, false);
            })
            .catch((error) => {
                $(
                    "#nama_layanan_update, #harga_layanan_update, #kategori_layanan_id_update"
                ).removeClass("is-invalid");
                $(
                    "#nama_layanan-error, #harga_layanan-error, #kategori_layanan_id-error"
                ).html("");

                if (error.response?.status === 422) {
                    const errors = error.response.data.errors;

                    if (errors.nama_layanan) {
                        $("#nama_layanan_update").addClass("is-invalid");
                        $("#nama_layanan-error").html(errors.nama_layanan[0]);
                    }

                    if (errors.harga_layanan) {
                        $("#harga_layanan_update").addClass("is-invalid");
                        $("#harga_layanan-error").html(errors.harga_layanan[0]);
                    }

                    if (errors.kategori_layanan_id) {
                        $("#kategori_layanan_id_update").addClass("is-invalid");
                        $("#kategori_layanan_id-error").html(
                            errors.kategori_layanan_id[0]
                        );
                    }

                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal!",
                        text: "Periksa kembali input Anda.",
                    });
                }
            });
    });

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
