import axios from "axios";
import { initFlowbite } from "flowbite";
import $ from "jquery";

// data tabel Pasien
$(function () {
    var table = $("#obatTable").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/pengaturan_klinik/data_obat",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "nama_obat", name: "nama_obat" },
            {
                data: "jumlah",
                name: "jumlah",
                render: function (data, type, row) {
                    if (!data) return "-"; // kalau null / kosong
                    return data + " capsul";
                },
            },
            {
                data: "dosis",
                name: "dosis",
                render: function (data, type, row) {
                    if (!data) return "-"; // kalau null / kosong
                    return data + " mg";
                },
            },
            {
                data: "total_harga",
                name: "total_harga",
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
    $("#obat_searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    const $info = $("#obat_customInfo");
    const $pagination = $("#obat_customPagination");
    const $perPage = $("#obat_pageLength");

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

// add data obat
$(function () {
    const addModalElement = document.getElementById("addObatModal");
    const addModal = addModalElement ? new Modal(addModalElement) : null;
    const $formAdd = $("#formAddObat");

    function resetAddForm() {
        $formAdd[0].reset();
        $formAdd.find(".is-invalid").removeClass("is-invalid");
        $formAdd.find(".text-danger").empty();
    }

    $("#btnAddObat").on("click", function () {
        resetAddForm();
        if (addModal) addModal.show();
    });

    $("#closeAddObatModal").on("click", function () {
        resetAddForm();
        if (addModal) addModal.hide();
    });

    // 💰 Auto Format Rupiah Input
    $("#total_harga").on("input", function () {
        let value = $(this).val().replace(/\D/g, ""); // hapus semua selain angka
        if (value) {
            value = new Intl.NumberFormat("id-ID").format(value);
        }
        $(this).val(value);
    });

    $formAdd.on("submit", function (e) {
        e.preventDefault();
        const url = $formAdd.data("url");

        // 🧹 Bersihkan titik sebelum dikirim ke server
        const hargaBersih = $("#total_harga").val().replace(/\./g, "");

        const formData = {
            nama_obat: $("#nama_obat").val(),
            jumlah: $("#jumlah").val(),
            dosis: $("#dosis").val(),
            total_harga: hargaBersih,
        };

        $(".text-danger").empty();
        $formAdd.find(".is-invalid").removeClass("is-invalid");

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
                    if ($("#obatTable").length) {
                        addModal.hide();
                        $("#obatTable").DataTable().ajax.reload(null, false);
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

// edit data obat
$(function () {
    const editModalElement = document.getElementById("editObatModal");
    const editModal = editModalElement ? new Modal(editModalElement) : null;
    const $formEdit = $("#formEditObat");

    function resetEditForm() {
        $formEdit[0].reset();
        $formEdit.find(".is-invalid").removeClass("is-invalid");
        $formEdit.find(".text-danger").empty();
    }

    // 💰 Auto Format Rupiah Input (Edit)
    $("#total_harga_edit").on("input", function () {
        let value = $(this).val().replace(/\D/g, ""); // hapus semua selain angka
        if (value) {
            value = new Intl.NumberFormat("id-ID").format(value);
        }
        $(this).val(value);
    });

    $("body").on("click", ".btn-edit-obat", function () {
        resetEditForm();
        const obatId = $(this).data("id");

        axios
            .get(`pengaturan_klinik/get_obat_by_id/${obatId}`)
            .then((response) => {
                const obat = response.data.data;
                const $form = $formEdit;
                const baseUrl = $form.data("url");
                const finalUrl = baseUrl.replace("/0", "/" + obat.id);
                $form.data("url", finalUrl);

                $("#obat_id_edit").val(obat.id);
                $("#nama_obat_edit").val(obat.nama_obat);
                $("#jumlah_edit").val(obat.jumlah);
                $("#dosis_edit").val(obat.dosis);
                $("#total_harga_edit").val(
                    new Intl.NumberFormat("id-ID").format(obat.total_harga)
                );

                if (editModal) editModal.show();
            })
            .catch((error) => {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: "Tidak dapat memuat data obat.",
                });
            });
    });

    $formEdit.on("submit", function (e) {
        e.preventDefault();
        const $form = $(this);
        const url = $form.data("url");

        const formData = {
            nama_obat: $("#nama_obat_edit").val(),
            jumlah: $("#jumlah_edit").val(),
            dosis: $("#dosis_edit").val(),
            total_harga: $("#total_harga_edit").val(),
            _method: "PUT",
        };

        axios
            .post(url, formData)
            .then((response) => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: response.data.massage,
                    showConfirmButton: false,
                    timer: 2000,
                }).then(() => {
                    if ($("#obatTable").length) {
                        editModal.hide();
                        $("#obatTable").DataTable().ajax.reload(null, false);
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
                        $(`#${field}_edit`).addClass("is-invalid");
                        $(`#${field}_edit-error`).html(errors[field][0]);
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

    $("#closeEditObatModal").on("click", function () {
        if (editModal) editModal.hide();
        resetEditForm();
    });
});

// delete data
$(function () {
    $("body").on("click", ".btn-delete-obat", function () {
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
                    .delete(`/pengaturan_klinik/delete_obat/${dokterId}`)
                    .then((response) => {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: response.data.message,
                            showConfirmButton: false,
                            timer: 1500,
                        }).then(() => {
                            if ($("#obatTable").length) {
                                $("#obatTable")
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
