import axios from "axios";
import $ from "jquery";

$(function () {
    var table = $("#dataObatTable").DataTable({
        processing: true,
        responsive: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/farmasi/obat/get-data-obat",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },

            {
                data: "nama_obat",
                name: "nama_obat",
            },

            {
                data: "jumlah",
                name: "jumlah",
                render: function (data, type, row) {
                    return data + " capsul";
                },
            },

            {
                data: "dosis",
                name: "dosis",
                render: function (data, type, row) {
                    if (!data) return "-";
                    return data + " mg";
                },
            },

            {
                data: "total_harga",
                name: "total_harga",
                render: function (data) {
                    if (!data) return "-";
                    const formatRupiah = Number(data).toLocaleString("id-ID", {
                        style: "currency",
                        currency: "IDR",
                        minimumFractionDigits: 0,
                    });
                    return formatRupiah;
                },
            },

            {
                data: "action",
                name: "action",
                searchable: false,
                orderable: false,
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

    $("#data-obat-search-input").on("keyup", function () {
        table.search(this.value).draw();
    });

    const $info = $("#data-obat-custom-info");
    const $paginate = $("#data-obat-custom-paginate");
    const $perPage = $("#data-obat-page-length");

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages;

        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${
                info.recordsDisplay
            } data (Halaman ${currentPage} dari ${totalPages})`
        );
        $paginate.empty();

        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";
        $paginate.append(
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
            $paginate.append(
                `<li><a href="#" class="page-number flex items-center justify-center px-3 h-8 border ${active}" data-page="${i}">${i}</a></li>`
            );
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 cursor-not-allowed" : "";
        $paginate.append(
            `<li><a href="#" id="btnNext" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">Next</a></li>`
        );
    }

    $paginate.on("click", "a", function (e) {
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

$(function () {
    const elementModalCreate = document.getElementById("modalCreateObat");
    const modalCreate = elementModalCreate
        ? new Modal(elementModalCreate)
        : null;
    const $formCreate = $("#formModalCreate");

    function resetFormCreate() {
        $formCreate[0].reset();
        $formCreate.find(".is-invalid").removeClass("is-invalid");
        $formCreate.find(".text-danger").empty();
    }

    $("#btn-open-modal-create-obat").on("click", function () {
        resetFormCreate();
        if (modalCreate) modalCreate.show();
    });

    $("#btn-close-modal-create-obat").on("click", function () {
        resetFormCreate();
        if (modalCreate) modalCreate.hide();
    });

    $("#total_harga").on("input", function () {
        let nilai = $(this).val().replace(/\D/g, "");
        if (nilai) {
            nilai = new Intl.NumberFormat("id-ID").format(nilai);
        }
        $(this).val(nilai);
    });

    $formCreate.on("submit", function (e) {
        e.preventDefault();
        const route = $formCreate.data("url");

        const totalHarga = $("#total_harga").val().replace(/\./g, "");

        const formData = {
            nama_obat: $("#nama_obat").val(),
            jumlah: $("#jumlah").val(),
            dosis: $("#dosis").val(),
            total_harga: totalHarga,
        };

        $(".text-danger").empty();
        $formCreate.find(".is-invalid").removeClass("is-invalid");

        axios
            .post(route, formData)
            .then((response) => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: response.data.message,
                    showConfirmButton: false,
                    timer: 2000,
                }).then(() => {
                    if ($("#dataObatTable").length) {
                        modalCreate.hide();
                        $("#dataObatTable")
                            .DataTable()
                            .ajax.reload(null, false);
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
                        text: "Silahkan periksa kembali isian formulir Anda.",
                    });
                    for (const kolom in errors) {
                        $(`#${kolom}`).addClass("is-invalid");
                        $(`#${kolom}-error`).html(errors[kolom][0]);
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error Server!",
                        text: "Terjadi kesalahan server. Silahkan coba lagi",
                    });
                    console.log("SERVER ERROR", error.message);
                }
            });
    });
});

$(function () {
    const elementModalUpdate = document.getElementById("modalUpdateObat");
    const modalUpdate = elementModalUpdate
        ? new Modal(elementModalUpdate)
        : null;
    const $formUpdate = $("#formModalUpdate");

    function resetFormUpdate() {
        $formUpdate[0].reset();
        $formUpdate.find(".is-invalid").removeClass("is-invalid");
        $formUpdate.find(".is-invalid").empty();
    }

    $("#total-harga-update").on("input", function () {
        let nilai = $(this).val().replace(/\D/g, "");
        if (nilai) {
            nilai = new Intl.NumberFormat("id-ID").format(nilai);
        }
        $(this).val(nilai);
    });

    $("body").on("click", ".btn-edit-obat", function () {
        resetFormUpdate();
        const id = $(this).data("id");

        axios
            .get(`obat/get-data-obat-by/${id}`)
            .then((response) => {
                const obat = response.data.data;
                const $form = $formUpdate;
                const route = $form.data("url");
                const finalRoute = route.replace("/0", "/" + obat.id);
                $form.data("url", finalRoute);

                $("#id_update").val(obat.id);
                $("#nama-obat-update").val(obat.nama_obat);
                $("#jumlah-update").val(obat.jumlah);
                $("#dosis-update").val(obat.dosis);
                $("#total-harga-update").val(
                    new Intl.NumberFormat("id-ID").format(obat.total_harga)
                );

                if (modalUpdate) modalUpdate.show();
            })
            .catch((error) => {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: "Tidak Dapat Memuat Data Obat",
                });
            });
    });

    $formUpdate.on("submit", function (e) {
        e.preventDefault();
        const $form = $(this);
        const route = $form.data("url");

        const formData = {
            nama_obat: $("#nama-obat-update").val(),
            jumlah: $("#jumlah-update").val(),
            dosis: $("#dosis-update").val(),
            total_harga: $("#total-harga-update").val(),
        };

        axios
            .post(route, formData)
            .then((response) => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: response.data.message,
                    showConfirmButton: false,
                    timer: 2000,
                }).then(() => {
                    if ($("#dataObatTable").length) {
                        modalUpdate.hide();
                        $("#dataObatTable")
                            .DataTable()
                            .ajax.reload(null, false);
                    } else {
                        window.location.reload();
                    }
                });
            })
            .catch((error) => {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.error;
                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal!",
                        text: "Silahkan periksa kembali isian formulir Anda.",
                    });
                    for (const kolom in errors) {
                        $(`#${kolom}-update`).addClass("is-invalid");
                        $(`#$(kolom)-update-error`).html(errors[kolom][0]);
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error Server!",
                        text: "Terjadi kesalahan serve. Silahkan coba lagi.",
                    });
                    console.error("SERVER ERROR", error.message);
                }
            });
    });

    $("#btn-close-modal-update-obat").on("click", function () {
        if (modalUpdate) modalUpdate.hide();
        resetFormUpdate();
    });
});

$(function () {
    $("body").on("click", ".btn-delete-obat", function () {
        const id = $(this).data("id");
        if (!id) return;

        Swal.fire({
            icon: "warning",
            title: "Apakah Anda yakin?",
            text: "Data yang dihapus tidak bisa dikembalikan!",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, hapus!",
            cancelButtonText: "Batal",
        }).then((result) => {
            if (result.isConfirmed) {
                axios
                    .delete(`obat/delete-data-obat/${id}`)
                    .then((response) => {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: response.data.message,
                            showConfirmButton: false,
                            timer: 2000,
                        }).then(() => {
                            if ($("#dataObatTable".length)) {
                                $("#dataObatTable")
                                    .DataTable()
                                    .ajax.reload(null, false);
                            } else {
                                window.reload();
                            }
                        });
                    })
                    .catch((error) => {
                        console.error("SERVER ERROR", error);
                        Swal.fire({
                            icons: "error",
                            title: "ERROR!",
                            text: "Terjadi kesalahan Server. Silahkan Coba Lagi",
                        });
                    });
            }
        });
    });
});
