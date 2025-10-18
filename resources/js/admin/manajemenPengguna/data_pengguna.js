import axios from "axios";
import { initFlowbite } from "flowbite";
import $ from "jquery";

// data tabel
$(function () {
    // Inisialisasi DataTable
    var table = $("#userTable").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "manajemen_pengguna/data_user",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "username", name: "username" },
            { data: "role", name: "role" },
            { data: "email", name: "email" },
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

    // 🔎 Hubungkan search input Flowbite
    $("#searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    const $info = $("#customInfo");
    const $pagination = $("#customPagination");
    const $perPage = $("#pageLength");

    // 🔁 Update Pagination Dinamis
    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages;

        // Info teks
        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${
                info.recordsDisplay
            } data (Halaman ${currentPage} dari ${totalPages})`
        );

        // Hapus pagination lama
        $pagination.empty();

        // Tombol Prev
        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(`
            <li>
                <a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a>
            </li>
        `);

        // Nomor halaman (max 5 tampil di tengah)
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

            $pagination.append(`
                <li>
                    <a href="#" class="page-number flex items-center justify-center px-3 h-8 border ${active}" data-page="${i}">${i}</a>
                </li>
            `);
        }

        // Tombol Next
        const nextDisabled =
            currentPage === totalPages ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(`
            <li>
                <a href="#" id="btnNext" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">Next</a>
            </li>
        `);
    }

    // Navigasi tombol prev / next / nomor halaman
    $pagination.on("click", "a", function (e) {
        e.preventDefault();
        const $link = $(this);
        if ($link.hasClass("opacity-50")) return; // disable

        if ($link.attr("id") === "btnPrev") {
            table.page("previous").draw("page");
        } else if ($link.attr("id") === "btnNext") {
            table.page("next").draw("page");
        } else if ($link.hasClass("page-number")) {
            const page = parseInt($link.data("page")) - 1;
            table.page(page).draw("page");
        }
    });

    // Dropdown per page
    $perPage.on("change", function () {
        const val = parseInt($(this).val());
        table.page.len(val).draw();
    });

    // Update pagination setiap kali DataTable digambar ulang
    table.on("draw", updatePagination);

    // Jalankan pertama kali
    updatePagination();
});

// add data
$(function () {
    const addModalElement = document.getElementById("addData");
    const addModal = addModalElement ? new Modal(addModalElement) : null;
    const $formAdd = $("#formAdd");

    function resetAddForm() {
        const $formAdd = $("#formAdd");
        $formAdd[0].reset();
        $formAdd.find(".is-invalid").removeClass("is-invalid");
        $formAdd.find(".text-danger").each(function () {
            $(this).text("");
        });
    }
    // Reset saat modal ditutup
    if (addModalElement) {
        addModalElement.addEventListener("hidden.tw.modal", resetAddForm);
    }

    $("#btnOpenAddModal").on("click", function () {
        resetAddForm();
        if (addModal) addModal.show();
    });

    $("#closeAddModal").on("click", function () {
        resetAddForm();
        if (addModal) addModal.hide();
    });

    $("#formAdd").on("submit", function (e) {
        e.preventDefault();

        const $form = $(this);
        const url = $form.attr("action") || $form.data("url");

        //  Dapatkan data form
        const formData = {
            username: $("#username").val(),
            email: $("#email_pengguna").val(),
            role: $("#role").val(),
            password: $("#password").val(),
            password_confirmation: $("#password_confirmation").val(),
        };

        $(".text-danger").empty();
        $form.find(".is-invalid").removeClass("is-invalid");

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
                    if ($("#userTable").length) {
                        addModal.hide();
                        $("#userTable").DataTable().ajax.reload(null, false);
                    } else {
                        window.location.reload();
                    }
                });
            })
            .catch((error) => {
                if (error.response && error.response.status === 422) {
                    // Handle Validasi Gagal (Status 422)
                    const errors = error.response.data.errors;
                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal!",
                        text: "Silakan periksa kembali isian formulir Anda.",
                        confirmButtonText: "Oke",
                    });

                    for (const field in errors) {
                        if (errors.hasOwnProperty(field)) {
                            // Tambahkan kelas error pada input
                            $(`#${field}`).addClass("is-invalid");
                            // Tampilkan error pada elemen yang sesuai (asumsi: #field-error)
                            $(`#${field}-error`).html(errors[field][0]);
                        }
                    }
                } else {
                    // Handle Error Server atau Jaringan lainnya
                    console.error("SERVER ERROR:", error.message);
                    // Tampilkan Pop-up Error Server
                    Swal.fire({
                        icon: "error",
                        title: "Error Server!",
                        text: "Terjadi kesalahan server atau jaringan. Silakan coba lagi.",
                        confirmButtonText: "Tutup",
                    });
                }
            });
    });
});

// form edit
$(function () {
    const editModalElement = document.getElementById("editData");
    const editModal = editModalElement ? new Modal(editModalElement) : null;
    const $formEdit = $("#formEdit");

    function resetEditForm() {
        $formEdit[0].reset();
        $formEdit.find(".is-invalid").removeClass("is-invalid");
        $formEdit.find(".text-danger").empty();
    }

    $("body").on("click", ".btn-edit-user", function () {
        let userId;
        resetEditForm();
        userId = $(this).data("id");

        $("#formEdit")[0].reset();
        $(".text-danger").empty();
        $("#formEdit").find(".is-invalid").removeClass("is-invalid");

        axios
            .get(`/manajemen_pengguna/get_user_by_id/${userId}`)
            .then((response) => {
                const user = response.data.data;
                const $form = $("#formEdit");
                const baseUrl = $form.data("url");
                const finalUrl = baseUrl.replace("/0", "/" + user.id);
                $form.data("url", finalUrl);

                $("#edit_user_id").val(user.id);
                $("#edit_username").val(user.username);
                $("#edit_email_pengguna").val(user.email);
                $("#edit_role").val(user.role);

                if (editModal) {
                    editModal.show();
                }
            })
            .catch((error) => {
                console.error("Gagal mengambil data pengguna:", error);
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: "Tidak dapat memuat data pengguna.",
                });
            });
    });

    $("#formEdit").on("submit", function (e) {
        e.preventDefault();

        const $form = $(this);
        const url = $form.data("url");

        const formData = {
            id: $("#edit_user_id").val(),
            username: $("#edit_username").val(),
            email: $("#edit_email_pengguna").val(),
            role: $("#edit_role").val(),
            password: $("#edit_password").val(),
            password_confirmation: $("#edit_password_confirmation").val(),
            _method: "PUT",
        };

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
                    if ($("#userTable").length) {
                        editModal.hide();
                        $("#userTable").DataTable().ajax.reload(null, false);
                    } else {
                        window.location.reload();
                    }
                });
            })
            .catch((error) => {
                if (error.response && error.response.status === 422) {
                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal!",
                        text: "Silakan periksa kembali isian formulir Anda.",
                        confirmButtonText: "Oke",
                    });

                    const errors = error.response.data.errors;
                    const fieldMap = {
                        username: "edit_username",
                        email: "edit_email_pengguna",
                        role: "edit_role",
                        password: "edit_password",
                        password_confirmation: "edit_password_confirmation",
                    };
                    for (const field in errors) {
                        const inputId = fieldMap[field];
                        $(`#${inputId}-error`).html(errors[field][0]);
                        $(`#${inputId}`).addClass("is-invalid");
                    }
                } else {
                    console.error("SERVER ERROR:", error.message);
                    Swal.fire({
                        icon: "error",
                        title: "Error Server!",
                        text: "Terjadi kesalahan server atau jaringan. Silakan coba lagi.",
                        confirmButtonText: "Tutup",
                    });
                }
            });
    });
    $("#closeEditModal").on("click", function () {
        if (editModal) editModal.hide();
        resetEditForm();
    });
});

// Delete User
$(function () {
    $("body").on("click", ".btn-delete", function () {
        const userId = $(this).data("id");
        if (!userId) return;

        // Tampilkan SweetAlert konfirmasi
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
                // Lakukan delete via axios
                axios
                    .delete(`/manajemen_pengguna/delete_user${userId}`)
                    .then((response) => {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: response.data.message,
                            showConfirmButton: false,
                            timer: 1500,
                        }).then(() => {
                            // Reload DataTable atau halaman
                            if ($("#userTable").length) {
                                $("#userTable")
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
                            confirmButtonText: "Tutup",
                        });
                    });
            }
        });
    });
});
