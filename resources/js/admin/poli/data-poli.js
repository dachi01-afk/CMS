import axios from "axios";
import { initFlowbite } from "flowbite";
import $ from "jquery";

// data poli
$(function () {
    var table = $("#poliTable").DataTable({
        processing: true,
        responsive: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/poli/get-data-poli",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "nama_poli", name: "nama_poli" },
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
    $("#poli-searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    const $info = $("#poli-customInfo");
    const $pagination = $("#poli-customPagination");
    const $perPage = $("#poli-pageLength");

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

// create data poli
$(function () {
    const addModalEl = document.getElementById("modalCreatePoli");
    const addModal = addModalEl ? new Modal(addModalEl) : null;
    const $formAdd = $("#formCreatePoli");

    function resetAddForm() {
        $formAdd[0].reset();
        $formAdd.find(".is-invalid").removeClass("is-invalid");
        $formAdd.find(".text-red-600").empty();
    }

    $("#buttonModalCreatePoli").on("click", function () {
        resetAddForm();
        addModal?.show();
    });

    $("#buttonCloseModalCreatePoli, #buttonCloseModalCreatePoli_footer").on(
        "click",
        function () {
            addModal?.hide();
            resetAddForm();
        }
    );

    $formAdd.on("submit", function (e) {
        e.preventDefault();
        const url = $formAdd.data("url");

        const formData = {
            nama_poli: $("#nama_poli_create").val(),
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
                addModal?.hide();
                $("#poliTable").DataTable().ajax.reload(null, false);
            })
            .catch((error) => {
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors;
                    for (const field in errors) {
                        $(`#${field}`).addClass("is-invalid");
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
                        text: "Terjadi kesalahan server.",
                    });
                }
            });
    });
});

// update data poli
$(function () {
    const editModalEl = document.getElementById("modalUpdatePoli");
    const editModal = editModalEl ? new Modal(editModalEl) : null;
    const $formEdit = $("#formUpdatePoli");

    function resetEditForm() {
        $formEdit[0].reset();
        $formEdit.find(".is-invalid").removeClass("is-invalid");
        $formEdit.find(".text-red-600").empty();
    }

    $("body").on("click", ".btn-edit-poli", function () {
        resetEditForm();
        const id = $(this).data("id");

        axios
            .get(`poli/get-data-poli-by-id/${id}`)
            .then((response) => {
                const poli = response.data.data;
                const baseUrl = $formEdit
                    .data("url")
                    .replace("/0", "/" + poli.id);
                $formEdit.data("url", baseUrl);

                $("#id_update").val(poli.id);
                $("#nama_poli_update").val(poli.nama_poli);
                editModal?.show();
            })
            .catch(() => {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: "Tidak dapat memuat data jadwal.",
                });
            });
    });

    $formEdit.on("submit", function (e) {
        e.preventDefault();
        const url = $formEdit.data("url");

        const formData = {
            id: $("#id_update").val(),
            nama_poli: $("#nama_poli_update").val(),
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
                editModal?.hide();
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

    $("#buttonCloseModalUpdatePoli, #buttonCloseModalUpdatePoli_footer").on(
        "click",
        function () {
            editModal?.hide();
            resetEditForm();
        }
    );
});

// delete data poli
$(function () {
    $("body").on("click", ".btn-delete-poli", function () {
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
                    .post(`/poli/delete-data-poli`, formData)
                    .then((response) => {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: response.data.message,
                            showConfirmButton: false,
                            timer: 1500,
                        }).then(() => {
                            if ($("#poliTable").length) {
                                $("#poliTable")
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
