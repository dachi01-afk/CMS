import axios from "axios";
import { initFlowbite } from "flowbite";
import $ from "jquery";

// data tabel Apoteker
$(function () {
    var table = $("#userApoteker").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/manajemen_pengguna/data_apoteker",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            {
                data: "foto",
                name: "foto",
                orderable: false,
                searchable: false,
                className: "text-center",
            },
            { data: "nama_apoteker", name: "nama_apoteker" },
            { data: "username", name: "username" },
            { data: "email_user", name: "email_user" },
            { data: "role", name: "role" },
            { data: "no_hp_apoteker", name: "no_hp_apoteker" },
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
    $("#apoteker_searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    const $info = $("#apoteker_customInfo");
    const $pagination = $("#apoteker_customPagination");
    const $perPage = $("#apoteker_pageLength");

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

// add data apoteker
$(function () {
    const addModalElement = document.getElementById("addApotekerModal");
    const addModal = addModalElement ? new Modal(addModalElement) : null;
    const $formAdd = $("#formAddApoteker");

    function resetAddForm() {
        $formAdd[0].reset();
        $formAdd.find(".is-invalid").removeClass("is-invalid");
        $formAdd.find(".text-red-600").empty();

        // Reset preview foto
        $("#preview_foto_apoteker").addClass("hidden").attr("src", "");
        $("#placeholder_foto_apoteker").removeClass("hidden");
        $("#foto_drop_area_apoteker")
            .removeClass("border-solid border-gray-300")
            .addClass("border-dashed border-gray-400");
    }

    $("#btnAddApoteker").on("click", function () {
        resetAddForm();
        if (addModal) addModal.show();
    });

    $("#closeAddApotekerModal").on("click", function () {
        resetAddForm();
        if (addModal) addModal.hide();
    });

    $formAdd.on("submit", function (e) {
        e.preventDefault();
        const url = $formAdd.data("url");
        const formData = new FormData($formAdd[0]);

        $(".text-red-600").empty();
        $formAdd.find(".is-invalid").removeClass("is-invalid");

        axios
            .post(url, formData, {
                headers: { "Content-Type": "multipart/form-data" },
            })
            .then((response) => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: response.data.success,
                    showConfirmButton: false,
                    timer: 2000,
                }).then(() => {
                    if ($("#userApoteker").length) {
                        addModal.hide();
                        $("#userApoteker").DataTable().ajax.reload(null, false);
                        resetAddForm();
                    } else {
                        window.location.reload();
                    }
                });
            })
            .catch((error) => {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal!",
                        text: "Silakan periksa kembali isian formulir Anda.",
                    });
                    for (const field in errors) {
                        $(`#${field}`).addClass("is-invalid");
                        $(`#${field}-error`).html(errors[field][0]);
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error Server!",
                        text: "Terjadi kesalahan server. Silakan coba lagi.",
                    });
                    console.error("SERVER ERROR:", error.message);
                }
            });
    });
});

// edit data apoteker
$(function () {
    const editModalElement = document.getElementById("editApotekerModal");
    const editModal = editModalElement ? new Modal(editModalElement) : null;
    const $formEdit = $("#formEditApoteker");
    const initialEditUrl = $formEdit.data("url");

    function resetEditForm() {
        $formEdit[0].reset();
        $formEdit.find(".is-invalid").removeClass("is-invalid");
        $formEdit.find(".text-red-600").empty();

        // reset URL ke awal
        $formEdit.data("url", initialEditUrl);
        $formEdit.attr("action", initialEditUrl);

        // Reset preview foto
        $("#edit_preview_foto_apoteker").addClass("hidden").attr("src", "");
        $("#edit_placeholder_foto_apoteker").removeClass("hidden");
        $("#edit_foto_drop_area_apoteker")
            .removeClass("border-solid border-gray-300")
            .addClass("border-dashed border-gray-400");
    }

    $("body").on("click", ".btn-edit-apoteker", function () {
        resetEditForm();
        const id = $(this).data("id");

        axios
            .get(`/manajemen_pengguna/get_apoteker_by_id/${id}`)
            .then((response) => {
                const data = response.data.data;

                const baseUrl = $formEdit.data("url");
                const finalUrl = baseUrl.replace("/0", "/" + data.id);
                $formEdit.data("url", finalUrl);
                $formEdit.attr("action", finalUrl);

                $("#edit_apoteker_id").val(data.id);
                $("#edit_username_apoteker").val(data.user.username);
                $("#edit_email_apoteker").val(data.user.email);
                $("#edit_nama_apoteker").val(data.nama_apoteker);
                $("#edit_no_hp_apoteker").val(data.no_hp_apoteker);

                // Tampilkan foto existing jika ada
                if (data.foto_apoteker) {
                    const fotoUrl = `/storage/${data.foto_apoteker}`;
                    $("#edit_preview_foto_apoteker")
                        .attr("src", fotoUrl)
                        .removeClass("hidden");
                    $("#edit_placeholder_foto_apoteker").addClass("hidden");
                    $("#edit_foto_drop_area_apoteker")
                        .removeClass("border-dashed border-gray-400")
                        .addClass("border-solid border-gray-300");
                }

                if (editModal) editModal.show();
            })
            .catch(() => {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: "Tidak dapat memuat data apoteker.",
                });
            });
    });

    $formEdit.on("submit", function (e) {
        e.preventDefault();
        const url = $formEdit.data("url");
        const formData = new FormData($formEdit[0]);
        if (!formData.has("_method")) formData.append("_method", "PUT");

        axios
            .post(url, formData)
            .then((response) => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: response.data.message,
                    showConfirmButton: false,
                    timer: 2000,
                }).then(() => {
                    editModal.hide();
                    $("#userApoteker").DataTable().ajax.reload(null, false);
                    resetEditForm();
                });
            })
            .catch((error) => {
                console.error("AXIOS ERROR:", error);

                if (error.response) {
                    const status = error.response.status;

                    if (status === 422) {
                        const errors = error.response.data.errors;
                        for (const field in errors) {
                            $(`#edit_${field}`).addClass("is-invalid");
                            $(`#edit_${field}-error`).html(errors[field][0]);
                        }
                    } else if (status === 413) {
                        Swal.fire({
                            icon: "error",
                            title: "Ukuran File Terlalu Besar!",
                            text: "Maksimal ukuran file yang diperbolehkan adalah 5 MB.",
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text:
                                error.response.data.message ||
                                "Terjadi kesalahan server.",
                        });
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: "Tidak ada respon dari server.",
                    });
                }
            });
    });

    $("#closeEditApotekerModal").on("click", function () {
        resetEditForm();
        if (editModal) editModal.hide();
    });
});

// delete data dokter
$(function () {
    $("body").on("click", ".btn-delete-apoteker", function () {
        const dokterId = $(this).data("id");
        if (!dokterId) return;

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
                    .delete(`/manajemen_pengguna/delete_apoteker/${dokterId}`)
                    .then((response) => {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: response.data.success,
                            showConfirmButton: false,
                            timer: 1500,
                        }).then(() => {
                            if ($("#userApoteker").length) {
                                $("#userApoteker")
                                    .DataTable()
                                    .ajax.reload(null, false);
                            } else {
                                window.location.reload();
                            }
                        });
                    })
                    .catch((error) => {
                        console.error("AXIOS ERROR OBJECT:", error);
                        if (error.response) {
                            console.error(
                                "Response status:",
                                error.response.status
                            );
                            console.error(
                                "Response data:",
                                error.response.data
                            );
                            console.error(
                                "Response headers:",
                                error.response.headers
                            );
                        } else if (error.request) {
                            console.error(
                                "No response received, request:",
                                error.request
                            );
                        } else {
                            console.error("Error message:", error.message);
                        }

                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text:
                                error.response &&
                                error.response.data &&
                                error.response.data.message
                                    ? error.response.data.message
                                    : "Terjadi kesalahan server. Silakan coba lagi.",
                        });
                    });
            }
        });
    });
});

// pasfoto
document.addEventListener("DOMContentLoaded", function () {
    const fileInput = document.getElementById("foto_apoteker");
    const previewImg = document.getElementById("preview_foto_apoteker");
    const placeholder = document.getElementById("placeholder_foto_apoteker");
    const dropArea = document.getElementById("foto_drop_area_apoteker");
    const modalElement = document.getElementById("addApotekerModal");
    const closeButton = document.getElementById("closeAddApotekerModal");

    // Tampilkan preview foto saat upload
    fileInput.addEventListener("change", function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (event) {
                previewImg.src = event.target.result;
                previewImg.classList.remove("hidden");
                placeholder.classList.add("hidden");
                dropArea.classList.remove("border-dashed", "border-gray-400");
                dropArea.classList.add("border-solid", "border-gray-300");
            };
            reader.readAsDataURL(file);
        } else {
            resetFotoPreview();
        }
    });

    // Fungsi reset foto
    function resetFotoPreview() {
        fileInput.value = "";
        previewImg.src = "";
        previewImg.classList.add("hidden");
        placeholder.classList.remove("hidden");
        dropArea.classList.add("border-dashed", "border-gray-400");
        dropArea.classList.remove("border-solid", "border-gray-300");
    }

    // Reset foto ketika modal ditutup (klik tombol close)
    closeButton.addEventListener("click", function () {
        modalElement.classList.add("hidden"); // sembunyikan modal
        resetFotoPreview();
        document.getElementById("formAddDokter").reset(); // reset seluruh form juga
    });

    // Reset ketika klik di luar modal (backdrop)
    modalElement.addEventListener("click", function (e) {
        // jika klik di luar konten (div bg putih)
        if (e.target === modalElement) {
            modalElement.classList.add("hidden");
            resetFotoPreview();
            formAdd.reset();
        }
    });
});

// edit foto
document.addEventListener("DOMContentLoaded", function () {
    const fileInput = document.getElementById("edit_foto_apoteker");
    const previewImg = document.getElementById("edit_preview_foto_apoteker");
    const placeholder = document.getElementById(
        "edit_placeholder_foto_apoteker"
    );
    const dropArea = document.getElementById("edit_foto_drop_area_apoteker");
    const closeButton = document.getElementById("closeEditApotekerModal");
    const formEdit = document.getElementById("formEditApoteker");

    function resetFotoPreview() {
        if (fileInput) fileInput.value = "";
        if (previewImg) {
            previewImg.src = "";
            previewImg.classList.add("hidden");
        }
        if (placeholder) placeholder.classList.remove("hidden");
        if (dropArea) {
            dropArea.classList.add("border-dashed", "border-gray-400");
            dropArea.classList.remove("border-solid", "border-gray-300");
        }
    }

    // Saat user pilih file baru
    if (fileInput) {
        fileInput.addEventListener("change", function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    previewImg.src = event.target.result;
                    previewImg.classList.remove("hidden");
                    placeholder.classList.add("hidden");
                    dropArea.classList.remove(
                        "border-dashed",
                        "border-gray-400"
                    );
                    dropArea.classList.add("border-solid", "border-gray-300");
                };
                reader.readAsDataURL(file);
            }
            // ❌ jangan reset kalau batal pilih file (biar foto lama tetap tampil)
        });
    }

    // Tutup modal via tombol X
    closeButton?.addEventListener("click", function () {
        resetFotoPreview();
        formEdit.reset();
    });

    // Tutup modal via backdrop klik (optional)
    const modalElement = document.getElementById("editDokterModal");
    modalElement?.addEventListener("click", function (e) {
        if (e.target === modalElement) {
            modalElement.classList.add("hidden");
            resetFotoPreview();
            formEdit.reset();
        }
    });
});
