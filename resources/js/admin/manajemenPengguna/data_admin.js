import axios from "axios";
import { Modal } from "flowbite";
import $ from "jquery";

function clearValidation($form) {
    $form.find(".is-invalid").removeClass("is-invalid");
    $form.find(".field-error").empty();
}

function showValidationErrors($form, errors = {}) {
    clearValidation($form);

    Object.keys(errors).forEach((field) => {
        const $input = $form.find(`[name="${field}"]`);
        const $error = $form.find(`[data-error-for="${field}"]`);

        $input.addClass("is-invalid");
        $error.html(errors[field][0] ?? "");
    });
}

function resetImagePreview(
    previewSelector,
    placeholderSelector,
    dropAreaSelector,
) {
    $(previewSelector).attr("src", "").addClass("hidden");
    $(placeholderSelector).removeClass("hidden");
    $(dropAreaSelector)
        .removeClass("border-solid border-slate-300")
        .addClass("border-dashed border-sky-300/80");
}

function bindImagePreview({
    inputSelector,
    previewSelector,
    placeholderSelector,
    dropAreaSelector,
    errorSelector,
}) {
    $(inputSelector).on("change", function () {
        const file = this.files && this.files[0];

        if (!file) {
            resetImagePreview(
                previewSelector,
                placeholderSelector,
                dropAreaSelector,
            );
            $(errorSelector).text("");
            return;
        }

        if (!file.type.startsWith("image/")) {
            $(errorSelector).text(
                "File harus berupa gambar (JPG, PNG, JPEG, WebP).",
            );
            this.value = "";
            resetImagePreview(
                previewSelector,
                placeholderSelector,
                dropAreaSelector,
            );
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            $(previewSelector)
                .attr("src", e.target.result)
                .removeClass("hidden");
            $(placeholderSelector).addClass("hidden");
            $(dropAreaSelector)
                .removeClass("border-dashed border-sky-300/80")
                .addClass("border-solid border-slate-300");
            $(errorSelector).text("");
        };
        reader.readAsDataURL(file);
    });

    $(dropAreaSelector).on("dragover", function (e) {
        e.preventDefault();
        $(this).addClass("ring-2 ring-sky-400");
    });

    $(dropAreaSelector).on("dragleave dragend drop", function (e) {
        e.preventDefault();
        $(this).removeClass("ring-2 ring-sky-400");
    });

    $(dropAreaSelector).on("drop", function (e) {
        const dt = e.originalEvent.dataTransfer;
        if (dt && dt.files && dt.files[0]) {
            $(inputSelector)[0].files = dt.files;
            $(inputSelector).trigger("change");
        }
    });
}

$(function () {
    const $tableEl = $("#adminTable");
    if (!$tableEl.length) return;

    const ajaxUrl = $tableEl.data("url");
    const table = $tableEl.DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: ajaxUrl,
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
            { data: "nama_admin", name: "nama_admin" },
            { data: "username", name: "user.username" },
            { data: "email_user", name: "user.email" },
            { data: "role", name: "user.role" },
            { data: "no_hp", name: "no_hp" },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
                className: "text-center whitespace-nowrap",
            },
        ],
        dom: "t",
        rowCallback: function (row) {
            $(row).addClass(
                "bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700/60",
            );
            $("td", row).addClass(
                "px-6 py-4 text-slate-700 dark:text-slate-100",
            );
        },
    });

    $("#admin_searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    const $info = $("#admin_customInfo");
    const $pagination = $("#admin_customPagination");
    const $perPage = $("#admin_pageLength");

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        const start = info.recordsDisplay === 0 ? 0 : info.start + 1;
        const end = info.recordsDisplay === 0 ? 0 : info.end;

        $info.text(
            `Menampilkan ${start}–${end} dari ${info.recordsDisplay} data (Halaman ${currentPage} dari ${totalPages})`,
        );

        $pagination.empty();

        const prevDisabled =
            currentPage === 1
                ? "opacity-50 cursor-not-allowed pointer-events-none"
                : "";

        $pagination.append(`
            <li>
                <a href="#" id="btnPrevAdmin"
                    class="flex items-center justify-center px-3 h-8 text-slate-500 bg-white border border-slate-300 rounded-s-lg hover:bg-slate-100 hover:text-slate-700 ${prevDisabled}">
                    Previous
                </a>
            </li>
        `);

        const maxVisible = 5;
        let pageStart = Math.max(currentPage - Math.floor(maxVisible / 2), 1);
        let pageEnd = Math.min(pageStart + maxVisible - 1, totalPages);

        if (pageEnd - pageStart < maxVisible - 1) {
            pageStart = Math.max(pageEnd - maxVisible + 1, 1);
        }

        for (let i = pageStart; i <= pageEnd; i++) {
            const active =
                i === currentPage
                    ? "text-sky-600 bg-sky-50 border-sky-300 hover:bg-sky-100"
                    : "text-slate-500 bg-white border-slate-300 hover:bg-slate-100 hover:text-slate-700";

            $pagination.append(`
                <li>
                    <a href="#" class="page-number flex items-center justify-center px-3 h-8 border ${active}" data-page="${i}">
                        ${i}
                    </a>
                </li>
            `);
        }

        const nextDisabled =
            currentPage === totalPages
                ? "opacity-50 cursor-not-allowed pointer-events-none"
                : "";

        $pagination.append(`
            <li>
                <a href="#" id="btnNextAdmin"
                    class="flex items-center justify-center px-3 h-8 text-slate-500 bg-white border border-slate-300 rounded-e-lg hover:bg-slate-100 hover:text-slate-700 ${nextDisabled}">
                    Next
                </a>
            </li>
        `);
    }

    $pagination.on("click", "a", function (e) {
        e.preventDefault();
        const $link = $(this);

        if ($link.attr("id") === "btnPrevAdmin") {
            table.page("previous").draw("page");
        } else if ($link.attr("id") === "btnNextAdmin") {
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
});

$(function () {
    bindImagePreview({
        inputSelector: "#foto_admin",
        previewSelector: "#preview_foto_admin",
        placeholderSelector: "#placeholder_foto_admin",
        dropAreaSelector: "#foto_drop_area_admin",
        errorSelector: '#formAddAdmin [data-error-for="foto_admin"]',
    });

    bindImagePreview({
        inputSelector: "#edit_foto_admin",
        previewSelector: "#edit_preview_foto_admin",
        placeholderSelector: "#edit_placeholder_foto_admin",
        dropAreaSelector: "#edit_foto_drop_area_admin",
        errorSelector: '#formEditAdmin [data-error-for="foto_admin"]',
    });
});

$(function () {
    const addModalElement = document.getElementById("addAdminModal");
    const addModal = addModalElement
        ? new Modal(addModalElement, {
              placement: "center",
              backdrop: "static",
              closable: false,
          })
        : null;

    const $formAdd = $("#formAddAdmin");

    function resetAddForm() {
        if ($formAdd.length) {
            $formAdd[0].reset();
            clearValidation($formAdd);
        }

        resetImagePreview(
            "#preview_foto_admin",
            "#placeholder_foto_admin",
            "#foto_drop_area_admin",
        );
    }

    $("#btnAddAdmin").on("click", function () {
        resetAddForm();
        addModal?.show();
    });

    $("#closeAddAdminModal_header, #closeAddAdminModal_footer").on(
        "click",
        function () {
            resetAddForm();
            addModal?.hide();
        },
    );

    $formAdd.on("submit", function (e) {
        e.preventDefault();

        const url = $formAdd.data("url");
        const formData = new FormData(this);

        clearValidation($formAdd);

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
                    timer: 1800,
                }).then(() => {
                    addModal?.hide();
                    $("#adminTable").DataTable().ajax.reload(null, false);
                    resetAddForm();
                });
            })
            .catch((error) => {
                if (error.response && error.response.status === 422) {
                    showValidationErrors(
                        $formAdd,
                        error.response.data.errors || {},
                    );

                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal!",
                        text: "Silakan periksa kembali isian formulir Anda.",
                    });
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

$(function () {
    const $tableEl = $("#adminTable");
    const showUrl = $tableEl.data("show-url");

    const editModalElement = document.getElementById("editAdminModal");
    const editModal = editModalElement
        ? new Modal(editModalElement, {
              placement: "center",
              backdrop: "static",
              closable: false,
          })
        : null;

    const $formEdit = $("#formEditAdmin");
    const urlTemplate = $formEdit.data("url");

    function resetEditForm() {
        if ($formEdit.length) {
            $formEdit[0].reset();
            clearValidation($formEdit);
            $("#edit_admin_id").val("");
            $formEdit.attr("action", urlTemplate);
            $formEdit.data("submit-url", urlTemplate);
        }

        resetImagePreview(
            "#edit_preview_foto_admin",
            "#edit_placeholder_foto_admin",
            "#edit_foto_drop_area_admin",
        );
    }

    $("body").on("click", ".btn-edit-admin", function () {
        const id = $(this).data("id");
        if (!id) return;

        resetEditForm();

        axios
            .get(`${showUrl}/${id}`)
            .then((response) => {
                const data = response.data.data;
                const submitUrl = urlTemplate.replace("__ID__", data.id);

                $formEdit.attr("action", submitUrl);
                $formEdit.data("submit-url", submitUrl);

                $("#edit_admin_id").val(data.id);
                $("#edit_username").val(data.user?.username ?? "");
                $("#edit_nama_admin").val(data.nama_admin ?? "");
                $("#edit_email").val(data.user?.email ?? "");
                $("#edit_no_hp").val(data.no_hp ?? "");

                if (data.foto_admin) {
                    $("#edit_preview_foto_admin")
                        .attr("src", `/storage/${data.foto_admin}`)
                        .removeClass("hidden");
                    $("#edit_placeholder_foto_admin").addClass("hidden");
                    $("#edit_foto_drop_area_admin")
                        .removeClass("border-dashed border-sky-300/80")
                        .addClass("border-solid border-slate-300");
                }

                editModal?.show();
            })
            .catch(() => {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: "Tidak dapat memuat data admin.",
                });
            });
    });

    $("#closeEditAdminModal_header, #closeEditAdminModal_footer").on(
        "click",
        function () {
            resetEditForm();
            editModal?.hide();
        },
    );

    $formEdit.on("submit", function (e) {
        e.preventDefault();

        const url = $formEdit.data("submit-url");
        const formData = new FormData(this);
        formData.append("_method", "PUT");

        clearValidation($formEdit);

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
                    timer: 1800,
                }).then(() => {
                    editModal?.hide();
                    $("#adminTable").DataTable().ajax.reload(null, false);
                    resetEditForm();
                });
            })
            .catch((error) => {
                if (error.response && error.response.status === 422) {
                    showValidationErrors(
                        $formEdit,
                        error.response.data.errors || {},
                    );

                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal!",
                        text: "Silakan periksa kembali isian formulir Anda.",
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: "Terjadi kesalahan server.",
                    });
                }
            });
    });
});

$(function () {
    const $tableEl = $("#adminTable");
    const deleteUrl = $tableEl.data("delete-url");

    $("body").on("click", ".btn-delete-admin", function () {
        const adminId = $(this).data("id");
        if (!adminId) return;

        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Data admin yang dihapus tidak bisa dikembalikan.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc2626",
            cancelButtonColor: "#64748b",
            confirmButtonText: "Ya, hapus!",
            cancelButtonText: "Batal",
        }).then((result) => {
            if (!result.isConfirmed) return;

            axios
                .delete(`${deleteUrl}/${adminId}`)
                .then((response) => {
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil!",
                        text: response.data.message,
                        showConfirmButton: false,
                        timer: 1500,
                    }).then(() => {
                        $("#adminTable").DataTable().ajax.reload(null, false);
                    });
                })
                .catch(() => {
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: "Terjadi kesalahan server. Silakan coba lagi.",
                    });
                });
        });
    });
});
