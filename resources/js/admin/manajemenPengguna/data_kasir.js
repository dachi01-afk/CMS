import axios from "axios";
import { initFlowbite } from "flowbite";
import $ from "jquery";

// data tabel Apoteker
$(function () {
    var table = $("#userKasir").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/manajemen_pengguna/data_kasir",
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
            { data: "nama_kasir", name: "nama_kasir" },
            { data: "username", name: "username" },
            { data: "email_user", name: "email_user" },
            { data: "role", name: "role" },
            { data: "no_hp_kasir", name: "no_hp_kasir" },
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
    $("#kasir_searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    const $info = $("#kasir_customInfo");
    const $pagination = $("#kasir_customPagination");
    const $perPage = $("#kasir_pageLength");

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
    const addModalElement = document.getElementById("addKasirModal");
    const addModal = addModalElement
        ? new Modal(addModalElement, {
              backdrop: "static",
              closable: false,
          })
        : null;
    const $formAdd = $("#formAddKasir");

    function resetAddForm() {
        $formAdd[0].reset();
        $formAdd.find(".is-invalid").removeClass("is-invalid");
        $formAdd.find(".text-red-600").empty();

        // Reset preview foto
        $("#preview_foto_kasir").addClass("hidden").attr("src", "");
        $("#placeholder_foto_kasir").removeClass("hidden");
        $("#foto_drop_area_kasir")
            .removeClass("border-solid border-gray-300")
            .addClass("border-dashed border-gray-400");
    }

    $("#btnAddKasir").on("click", function () {
        resetAddForm();
        if (addModal) addModal.show();
    });

    $("#closeAddKasirModal_header, #closeAddKasirModal_footer").on(
        "click",
        function () {
            resetAddForm();
            if (addModal) addModal.hide();
        }
    );

    // === Preview foto kasir ===
    $("#foto_kasir").on("change", function () {
        const file = this.files && this.files[0];

        // reset tampilan saat tidak ada file
        if (!file) {
            $("#preview_foto_kasir").addClass("hidden").attr("src", "");
            $("#placeholder_foto_kasir").removeClass("hidden");
            $("#foto_drop_area_kasir")
                .removeClass("border-solid border-gray-300")
                .addClass("border-dashed border-gray-400");
            return;
        }

        // validasi tipe file
        if (!file.type.startsWith("image/")) {
            $("#foto_kasir-error").text(
                "File harus berupa gambar (JPG/PNG/WebP)."
            );
            this.value = "";
            $("#preview_foto_kasir").addClass("hidden").attr("src", "");
            $("#placeholder_foto_kasir").removeClass("hidden");
            return;
        }

        // tampilkan preview
        const reader = new FileReader();
        reader.onload = (e) => {
            $("#preview_foto_kasir")
                .attr("src", e.target.result)
                .removeClass("hidden");
            $("#placeholder_foto_kasir").addClass("hidden");
            $("#foto_drop_area_kasir")
                .removeClass("border-dashed border-gray-400")
                .addClass("border-solid border-gray-300");
            $("#foto_kasir-error").text("");
        };
        reader.readAsDataURL(file);
    });

    // (opsional) efek drag-n-drop pada drop area
    const $drop = $("#foto_drop_area_kasir");
    $drop.on("dragover", function (e) {
        e.preventDefault();
        $(this).addClass("ring-2 ring-blue-400");
    });
    $drop.on("dragleave dragend drop", function (e) {
        e.preventDefault();
        $(this).removeClass("ring-2 ring-blue-400");
    });
    $drop.on("drop", function (e) {
        const dt = e.originalEvent.dataTransfer;
        if (dt && dt.files && dt.files[0]) {
            $("#foto_kasir")[0].files = dt.files; // set file ke input
            $("#foto_kasir").trigger("change"); // pakai handler di atas
        }
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
                    if ($("#userKasir").length) {
                        addModal.hide();
                        $("#userKasir").DataTable().ajax.reload(null, false);
                        resetAddForm();
                    } else {
                        window.location.reload();
                    }
                });
            })
            .catch((error) => {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;

                    $formAdd.find(".is-invalid").removeClass("is-invalid");
                    $formAdd.find(".text-red-600").empty();

                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal!",
                        text: "Silakan periksa kembali isian formulir Anda.",
                    });

                    for (const field in errors) {
                        const inputField = $(`#${field}`);
                        inputField.addClass("is-invalid");
                        $(`#${field}-error`).html(errors[field][0]);
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error Server!",
                        text: "Terjadi kesalahan server. Silakan coba lagi.",
                    });
                }
            });
    });
});

// edit data kasir
$(function () {
    const editModalElement = document.getElementById("editKasirModal");
    const editModal = editModalElement
        ? new Modal(editModalElement, {
              backdrop: "static",
              closable: false,
          })
        : null;
    const $formEdit = $("#formEditKasir");
    const initialEditUrl = $formEdit.data("url");

    function resetEditForm() {
        $formEdit[0].reset();
        $formEdit.find(".is-invalid").removeClass("is-invalid");
        $formEdit.find(".text-red-600").empty();

        // reset URL ke awal
        $formEdit.data("url", initialEditUrl);
        $formEdit.attr("action", initialEditUrl);

        // Reset preview foto
        $("#edit_preview_foto_kasir").addClass("hidden").attr("src", "");
        $("#edit_placeholder_foto_kasir").removeClass("hidden");
        $("#edit_foto_drop_area_kasir")
            .removeClass("border-solid border-gray-300")
            .addClass("border-dashed border-gray-400");
    }

    // contoh handler preview untuk edit
    $("#edit_foto_kasir").on("change", function () {
        const file = this.files?.[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            $("#edit_preview_foto_kasir")
                .attr("src", e.target.result)
                .removeClass("hidden");
            $("#edit_placeholder_foto_kasir").addClass("hidden");
        };
        reader.readAsDataURL(file);
    });

    $("body").on("click", ".btn-edit-kasir", function () {
        resetEditForm();
        const id = $(this).data("id");

        axios
            .get(`/manajemen_pengguna/get_kasir_by_id/${id}`)
            .then((response) => {
                const data = response.data.data;

                const baseUrl = $formEdit.data("url");
                const finalUrl = baseUrl.replace("/0", "/" + data.id);
                $formEdit.data("url", finalUrl);
                $formEdit.attr("action", finalUrl);

                $("#edit_kasir_id").val(data.id);
                $("#edit_username_kasir").val(data.user.username);
                $("#edit_email_kasir").val(data.user.email);
                $("#edit_nama_kasir").val(data.nama_kasir);
                $("#edit_no_hp_kasir").val(data.no_hp_kasir);

                // Tampilkan foto existing jika ada
                if (data.foto_kasir) {
                    const fotoUrl = `/storage/${data.foto_kasir}`;
                    $("#edit_preview_foto_kasir")
                        .attr("src", fotoUrl)
                        .removeClass("hidden");
                    $("#edit_placeholder_foto_kasir").addClass("hidden");
                    $("#edit_foto_drop_area_kasir")
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
                    $("#userKasir").DataTable().ajax.reload(null, false);
                    resetEditForm();
                });
            })
            .catch((error) => {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;

                    $formEdit.find(".is-invalid").removeClass("is-invalid");
                    $formEdit.find(".text-red-600").empty();

                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal!",
                        text: "Silakan periksa kembali isian formulir Anda.",
                    });

                    for (const field in errors) {
                        const inputField = $(`#${field}`);
                        inputField.addClass("is-invalid");
                        $(`#${field}-error`).html(errors[field][0]);
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: "Terjadi kesalahan server.",
                    });
                }
            });
    });

    $("#closeEditKasirModal_header ,#closeEditKasirrModal_footer").on(
        "click",
        function () {
            resetEditForm();
            if (editModal) editModal.hide();
        }
    );
});

// delete data dokter
$(function () {
    $("body").on("click", ".btn-delete-kasir", function () {
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
                    .delete(`/manajemen_pengguna/delete_kasir/${dokterId}`)
                    .then((response) => {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: response.data.success,
                            showConfirmButton: false,
                            timer: 1500,
                        }).then(() => {
                            if ($("#userKasir").length) {
                                $("#userKasir")
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
