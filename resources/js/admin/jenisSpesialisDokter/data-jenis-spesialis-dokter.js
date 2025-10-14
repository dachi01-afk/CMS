import axios from "axios";
import { initFlowbite } from "flowbite";
import $ from "jquery";

// data jenis spesialis dokter
$(function () {
    var table = $("#jenisSpesialisDokter").DataTable({
        processing: true,
        responsive: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/jenis-spesialis/data-jenis-spesialis",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "nama_spesialis", name: "nama_spesialis" },
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
    $("#jenis-spesialis-dokter_searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    const $info = $("#jenis-spesialis-dokter_customInfo");
    const $pagination = $("#jenis-spesialis-dokter_customPagination");
    const $perPage = $("#jenis-spesialis-dokter_pageLength");

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

// create data jenis spesialis dokter
$(function () {
    const addModalElement = document.getElementById(
        "addJenisSpesialisDokterModal"
    );
    const addModal = addModalElement ? new Modal(addModalElement) : null;
    const $formAdd = $("#formAddJenisSpesialisDokter");

    function resetAddForm() {
        $formAdd[0].reset();
        $formAdd.find(".is-invalid").removeClass("is-invalid");
        $formAdd.find(".text-red-600").empty();
    }

    $("#btnAddJenisSpesialisDokter").on("click", function () {
        resetAddForm();
        if (addModal) addModal.show();
    });

    $("#closeAddJenisSpesialisDokterModal").on("click", function () {
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
                    text: response.data.message,
                    showConfirmButton: false,
                    timer: 2000,
                }).then(() => {
                    if ($("#jenisSpesialisDokter").length) {
                        addModal.hide();
                        $("#jenisSpesialisDokter")
                            .DataTable()
                            .ajax.reload(null, false);
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

// update data jenis spesialis dokter
// $(function () {
//     const editModalEl = document.getElementById(
//         "updateJenisSpesialisDokterModal"
//     );
//     const modalUpdateJenisSpesialis = editModalEl
//         ? new Modal(editModalEl)
//         : null;
//     const $formEdit = $("#formUpdateJenisSpesialisDokter");

//     function showModalFix() {
//         const modalEl = modalUpdateJenisSpesialis;
//         modalEl.classList.remove("hidden");
//         modalEl.classList.add("flex");
//         modalEl.style.zIndex = "99999";
//         modalEl.style.opacity = "1";
//         modalEl.querySelector(".bg-white").style.opacity = "1";
//     }

//     function resetEditForm() {
//         $formEdit[0].reset();
//         $formEdit.find(".is-invalid").removeClass("is-invalid");
//         $formEdit.find(".text-red-600").empty();
//     }

//     $("body").on("click", ".btn-edit-jenis-spesialis-dokter", function () {
//         resetEditForm();
//         const id = $(this).data("id");

//         axios
//             .get(`jenis-spesialis/get-data-jenis-spesialis/${id}`)
//             .then((response) => {
//                 const spesialis = response.data.data;

//                 console.log(spesialis);
//                 const baseUrl = $formEdit
//                     .data("url")
//                     .replace("/0", "/" + spesialis.id);
//                 $formEdit.data("url", baseUrl);

//                 $("#id_update").val(spesialis.id);
//                 $("#update-jenis-spesialis-dokter-nama-spesialis").val(
//                     spesialis.nama_spesialis
//                 );

//                 // modalUpdateJenisSpesialis?.show();
//                 showModalFix();
//             })
//             .catch(() => {
//                 Swal.fire({
//                     icon: "error",
//                     title: "Gagal!",
//                     text: "Tidak dapat memuat data jadwal.",
//                 });
//             });
//     });

//     $formEdit.on("submit", function (e) {
//         e.preventDefault();
//         const url = $formEdit.data("url");

//         const formData = {
//             id: $("#id_update").val(),
//             nama_poli: $("#update-jenis-spesialis-dokter-nama-spesialis").val(),
//         };

//         axios
//             .post(url, formData)
//             .then((response) => {
//                 Swal.fire({
//                     icon: "success",
//                     title: "Berhasil!",
//                     text: response.data.message,
//                     timer: 2000,
//                     showConfirmButton: false,
//                 });
//                 showModalFix();
//                 $("#poliTable").DataTable().ajax.reload(null, false);
//             })
//             .catch((error) => {
//                 if (error.response?.status === 422) {
//                     const errors = error.response.data.errors;
//                     for (const field in errors) {
//                         $(`#${field}_edit`).addClass("is-invalid");
//                         $(`#${field}_edit-error`).html(errors[field][0]);
//                     }
//                     Swal.fire({
//                         icon: "error",
//                         title: "Validasi Gagal!",
//                         text: "Periksa kembali input Anda.",
//                     });
//                 } else {
//                     Swal.fire({
//                         icon: "error",
//                         title: "Error Server!",
//                         text: "Terjadi kesalahan server.",
//                     });
//                 }
//             });
//     });

//     $("#buttonCloseModalUpdatePoli").on("click", function () {
//         resetEditForm();
//         showModalFix();
//     });
// });

$(function () {
    const editModalEl = document.getElementById(
        "updateJenisSpesialisDokterModal"
    );
    const modalUpdateJenisSpesialis = editModalEl
        ? new Modal(editModalEl)
        : null;
    const $formEdit = $("#formUpdateJenisSpesialisDokter");

    function showModalFix() {
        editModalEl.classList.remove("hidden");
        editModalEl.classList.add("flex");
        editModalEl.style.zIndex = "99999";
        editModalEl.style.opacity = "1";
        editModalEl.querySelector(".bg-white").style.opacity = "1";
    }

    function resetEditForm() {
        $formEdit[0].reset();
        $formEdit.find(".is-invalid").removeClass("is-invalid");
        $formEdit.find(".text-red-600").empty();
    }

    // 🔹 Klik tombol edit
    $("body").on("click", ".btn-edit-jenis-spesialis-dokter", function () {
        resetEditForm();
        const id = $(this).data("id");

        axios
            .get(`jenis-spesialis/get-data-jenis-spesialis/${id}`)
            .then((response) => {
                const spesialis = response.data.data;
                const baseUrl = $formEdit
                    .data("url")
                    .replace("/0", "/" + spesialis.id);
                $formEdit.data("url", baseUrl);

                $("#id_update").val(spesialis.id);
                $("#update-jenis-spesialis-dokter-nama-spesialis").val(
                    spesialis.nama_spesialis
                );

                // 🔹 tampilkan modal
                showModalFix();
            })
            .catch(() => {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: "Tidak dapat memuat data spesialis.",
                });
            });
    });

    // 🔹 Submit form update
    $formEdit.on("submit", function (e) {
        e.preventDefault();
        const url = $formEdit.data("url");

        const formData = {
            id: $("#id_update").val(),
            nama_spesialis: $(
                "#update-jenis-spesialis-dokter-nama-spesialis"
            ).val(),
        };

        axios
            .post(url, formData)
            .then((response) => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: response.data.message,
                    timer: 2000,
                    showConfirmButton: false,
                });
                modalUpdateJenisSpesialis?.hide();
                $("#poliTable").DataTable().ajax.reload(null, false);
            })
            .catch((error) => {
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors;
                    for (const field in errors) {
                        $(`#${field}_edit`).addClass("is-invalid");
                        $(`#${field}_edit-error`).html(errors[field][0]);
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
                        text: "Terjadi kesalahan server.",
                    });
                }
            });
    });

    // 🔹 Tombol Close modal
    $("#buttonCloseModalUpdatePoli").on("click", function () {
        modalUpdateJenisSpesialis?.hide();
        resetEditForm();
    });
});

// edit data jenis spesialis dokter
// $(function () {
//     // 🔹 Inisialisasi modal
//     const editModalElement = document.getElementById(
//         "updateJenisSpesialisDokterModal"
//     );
//     const editModal = editModalElement ? new Modal(editModalElement) : null;
//     const $formEdit = $("#formUpdateJenisSpesialisDokter");

//     // 🔹 Reset form edit
//     function resetEditForm() {
//         $formEdit[0].reset();
//         $formEdit.find(".is-invalid").removeClass("is-invalid");
//         $formEdit.find(".text-red-600").empty();
//     }

//     // 🔹 Saat klik tombol edit
//     $("body").on("click", ".btn-edit-jenis-spesialis-dokter", function () {
//         resetEditForm();
//         const id = $(this).data("id");

//         axios
//             .get(`jenis-spesialis/get-data-jenis-spesialis/${id}`)
//             .then((response) => {
//                 const jenisSpesialis = response.data.data;
//                 console.log("📦 Data diterima:", jenisSpesialis);

//                 // 🔸 Update URL form
//                 const baseUrl = $formEdit
//                     .data("url")
//                     .replace("/0", "/" + jenisSpesialis.id);
//                 $formEdit.data("url", baseUrl);

//                 // 🔸 Isi form
//                 $("#id_update").val(jenisSpesialis.id);
//                 $("#update-jenis-spesialis-dokter-nama-spesialis").val(
//                     jenisSpesialis.nama_spesialis
//                 );

//                 // Tampilkan modal
//                 // $("#updateJenisSpesialisDokterModal").removeClass("hidden");
//                 $("#updateJenisSpesialisDokterModal").addClass("flex").removeClass("hidden").css("z-index", "9999");

//                 editModal?.show();
//             })
//             .catch((error) => {
//                 console.error("❌ Gagal memuat data:", error);
//                 Swal.fire({
//                     icon: "error",
//                     title: "Gagal!",
//                     text: "Tidak dapat memuat data jenis spesialis.",
//                 });
//             });
//     });

//     // 🔹 Saat submit form edit
//     $formEdit.on("submit", function (e) {
//         e.preventDefault();
//         const url = $formEdit.data("url");

//         const formData = {
//             dokter_id: $("#dokter_id_edit").val(),
//             hari: $("#hari_edit").val(),
//             jam_awal: $("#jam_awal_edit").val(),
//             jam_selesai: $("#jam_selesai_edit").val(),
//             _method: "PUT",
//         };

//         axios
//             .post(url, formData)
//             .then((response) => {
//                 Swal.fire({
//                     icon: "success",
//                     title: "Berhasil!",
//                     text: response.data.message,
//                     timer: 2000,
//                     showConfirmButton: false,
//                 });

//                 // 🔸 Tutup modal dan reset form
//                 $("#editJenisSpesialisDokterModal").addClass("hidden");
//                 editModal?.hide();
//                 resetEditForm();

//                 // 🔸 Reload DataTable
//                 $("#jadwalTable").DataTable().ajax.reload(null, false);
//             })
//             .catch((error) => {
//                 if (error.response?.status === 422) {
//                     const errors = error.response.data.errors;
//                     for (const field in errors) {
//                         $(`#${field}_edit`).addClass("is-invalid");
//                         $(`#${field}_edit-error`).html(errors[field][0]);
//                     }
//                     Swal.fire({
//                         icon: "error",
//                         title: "Validasi Gagal!",
//                         text: "Periksa kembali input Anda.",
//                     });
//                 } else {
//                     Swal.fire({
//                         icon: "error",
//                         title: "Error Server!",
//                         text: "Terjadi kesalahan server.",
//                     });
//                 }
//             });
//     });

//     // 🔹 Saat klik tombol close
//     $("#closeEditJadwalModal").on("click", function () {
//         editModal?.hide();
//         $("#updateJenisSpesialisDokterModal").addClass("hidden");
//         resetEditForm();
//     });
// });

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
                            if ($("#jenisSpesialisDokter").length) {
                                $("#jenisSpesialisDokter")
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

// edit foto
document.addEventListener("DOMContentLoaded", function () {
    const fileInput = document.getElementById("edit_foto_apoteker");
    const previewImg = document.getElementById("edit_preview_foto_apoteker");
    const placeholder = document.getElementById(
        "edit_placeholder_foto_apoteker"
    );
    const dropArea = document.getElementById("edit_foto_drop_area_apoteker");
    const closeButton = document.getElementById(
        "closeEditJenisSpesialisDokterModal"
    );
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
