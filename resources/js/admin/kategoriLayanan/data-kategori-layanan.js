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
        ajax: "/kategori_layanan/get-data-kategori-layanan",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "nama_kategori", name: "nama_kategori" },
            { data: "deskripsi_kategori", name: "deskripsi_kategori" },
            { data: "status_kategori", name: "status_kategori" },
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
    $("#btnInfoKategoriLayanan").on("click", function () {
        Swal.fire({
            icon: "info",
            title: "Informasi Kategori Layanan",
            width: "600px",
            html: `
                <div class="flex flex-col mx-4 my-2 text-center gap-2">
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Data kategori layanan hanya ada 2, yaitu 
                    <span class="font-medium">"Pemeriksaan"</span> dan 
                    <span class="font-medium">"Non Pemeriksaan"</span>.
                </p>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Dan data kategori ini akan ada di dalam setiap layanan yang tersedia.
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

// Create Data Kategori Layanan
$(function () {
    // Inisialisasi modal menggunakan library Modal (misalnya Bootstrap Modal)
    const addModalEl = document.getElementById("modalCreateKategoriLayanan");
    const addModal = addModalEl
        ? new Modal(addModalEl, {
              backdrop: "static",
              closable: false,
          })
        : null;
    const $formAdd = $("#formCreateKategoriLayanan");

    // Fungsi untuk reset form: bersihkan input, hapus class error, dan kosongkan pesan error
    function resetAddForm() {
        $formAdd[0].reset(); // Reset semua input
        $formAdd.find(".is-invalid").removeClass("is-invalid"); // Hapus class invalid
        $formAdd.find(".text-red-500").empty(); // Kosongkan pesan error (sesuai dengan desain baru)
    }

    // Event: Buka modal saat tombol "Tambah" diklik
    $("#buttonModalCreateLayanan").on("click", function () {
        resetAddForm(); // Reset form sebelum buka
        addModal?.show(); // Tampilkan modal
    });

    // Event: Tutup modal saat tombol close diklik
    $(
        "#buttonCloseModalCreateKategoriLayanan, #buttonCloseModalCreateKategoriLayanan_footer"
    ).on("click", function () {
        addModal?.hide(); // Tutup modal
        resetAddForm(); // Reset form
    });

    // Event: Submit form untuk create data
    $formAdd.on("submit", function (e) {
        e.preventDefault(); // Cegah submit default
        const url = $formAdd.data("url"); // Ambil URL dari data attribute

        // Kumpulkan data form (sesuai skema: nama_kategori, deskripsi_kategori, status_kategori)
        const formData = {
            nama_kategori: $("#nama_kategori_create").val().trim(),
            deskripsi_kategori: $("#deskripsi_kategori_create").val().trim(),
            status_kategori: $('input[name="status_kategori"]:checked').val(), // Ambil nilai radio button yang dipilih
        };

        // Kirim request POST menggunakan Axios
        axios
            .post(url, formData)
            .then((response) => {
                // Jika berhasil, tampilkan notifikasi sukses
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text:
                        response.data.message ||
                        "Data kategori layanan berhasil ditambahkan.",
                    timer: 2000,
                    showConfirmButton: false,
                });
                addModal?.hide(); // Tutup modal
                resetAddForm(); // Reset form
                $("#layananTable").DataTable().ajax.reload(null, false); // Reload tabel tanpa reset paging
            })
            .catch((error) => {
                // Tangani error validasi (422) atau server error
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors;
                    // Tampilkan error per field
                    for (const field in errors) {
                        $(`#${field}_create`).addClass("is-invalid"); // Tambah class invalid
                        $(`#${field}-error`)
                            .html(errors[field][0])
                            .removeClass("hidden"); // Tampilkan pesan error
                    }
                    // Notifikasi validasi gagal
                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal!",
                        text: "Periksa kembali input Anda.",
                    });
                } else {
                    // Notifikasi error server
                    Swal.fire({
                        icon: "error",
                        title: "Error Server!",
                        text:
                            error.response?.data?.message ||
                            "Terjadi kesalahan server. Coba lagi.",
                    });
                }
            });
    });
});

// Update Data Kategori Layanan
$(function () {
    const editModalEl = document.getElementById("modalUpdateKategoriLayanan");
    const $formEdit = $("#formUpdateKategoriLayanan");

    // Fungsi untuk reset form: bersihkan input, radio, dan pesan error
    function resetEditForm() {
        if ($formEdit[0]) {
            $formEdit[0].reset();
        }

        // Hilangkan class error di semua input dalam modal update
        $("#modalUpdateKategoriLayanan")
            .find(".is-invalid")
            .removeClass("is-invalid");

        // Sembunyikan semua pesan error
        $("#modalUpdateKategoriLayanan")
            .find(
                "#nama_kategori-error, #deskripsi_kategori-error, #status_kategori-error"
            )
            .addClass("hidden")
            .empty();

        // Pastikan semua radio status_kategori kosong
        $("#modalUpdateKategoriLayanan input[name='status_kategori']").prop(
            "checked",
            false
        );
    }

    // Show modal
    function showModal() {
        editModalEl.classList.remove("hidden");
    }

    // Hide modal
    function hideModal() {
        editModalEl.classList.add("hidden");
        resetEditForm();
    }

    // Klik tombol edit di tabel
    $("body").on("click", ".btn-edit-kategori-layanan", function () {
        resetEditForm();

        const id = $(this).data("id");

        axios
            .get(`kategori_layanan/get-data-kategori-layanan-by-id/${id}`)
            .then((response) => {
                const kategori = response.data.data;

                // Isi form
                $("#id_update").val(kategori.id);
                $("#nama_kategori_update").val(kategori.nama_kategori);
                $("#deskripsi_kategori_update").val(
                    kategori.deskripsi_kategori
                );

                // 🔴 Set radio button status berdasarkan enum: 'Aktif' / 'Tidak Aktif'
                const status = (kategori.status_kategori ?? "").trim();

                // Kosongkan dulu semua radio di modal UPDATE
                const $radioStatus = $(
                    "#modalUpdateKategoriLayanan input[name='status_kategori']"
                );
                $radioStatus.prop("checked", false);

                if (status === "Aktif") {
                    $(
                        "#modalUpdateKategoriLayanan input[name='status_kategori'][value='Aktif']"
                    ).prop("checked", true);
                } else if (status === "Tidak Aktif") {
                    $(
                        "#modalUpdateKategoriLayanan input[name='status_kategori'][value='Tidak Aktif']"
                    ).prop("checked", true);
                }

                showModal();
            })
            .catch(() => {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: "Tidak dapat memuat data kategori layanan.",
                });
            });
    });

    // Submit form update
    $formEdit.on("submit", function (e) {
        e.preventDefault();
        const url = $formEdit.data("url");

        const formData = {
            id: $("#id_update").val(),
            nama_kategori: $("#nama_kategori_update").val().trim(),
            deskripsi_kategori: $("#deskripsi_kategori_update").val().trim(),
            status_kategori: $(
                "#modalUpdateKategoriLayanan input[name='status_kategori']:checked"
            ).val(),
        };

        axios
            .post(url, formData)
            .then((response) => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text:
                        response.data.message ||
                        "Data kategori layanan berhasil diperbarui.",
                    timer: 2000,
                    showConfirmButton: false,
                });
                hideModal();
                $("#layananTable").DataTable().ajax.reload(null, false);
            })
            .catch((error) => {
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors || {};

                    // Bersihkan pesan error lama
                    $("#modalUpdateKategoriLayanan")
                        .find(
                            "#nama_kategori-error, #deskripsi_kategori-error, #status_kategori-error"
                        )
                        .addClass("hidden")
                        .empty();
                    $("#modalUpdateKategoriLayanan")
                        .find(".is-invalid")
                        .removeClass("is-invalid");

                    // Tampilkan error field satu per satu
                    for (const field in errors) {
                        if (field === "status_kategori") {
                            $("#status_kategori-error")
                                .html(errors[field][0])
                                .removeClass("hidden");
                        } else {
                            $(`#${field}_update`).addClass("is-invalid");
                            $(`#${field}-error`)
                                .html(errors[field][0])
                                .removeClass("hidden");
                        }
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
                            "Terjadi kesalahan server. Coba lagi.",
                    });
                }
            });
    });

    // Tombol Cancel
    $(
        "#buttonCloseModalUpdateLayanan , #buttonCloseModalUpdateKategoriLayanan"
    ).on("click", function () {
        hideModal();
    });
});

// delete data
$(function () {
    $("body").on("click", ".btn-delete-kategori-layanan", function () {
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
                    .post(
                        `/kategori_layanan/delete-data-kategori-layanan`,
                        formData
                    )
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
