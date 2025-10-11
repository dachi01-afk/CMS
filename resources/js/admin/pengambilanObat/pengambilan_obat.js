import axios from "axios";
import { initFlowbite } from "flowbite";
import $ from "jquery";

// data tabel Pasien
$(function () {
    var table = $("#pengambilanResepObat").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/pengambilan_obat/get-data",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "nama_dokter", name: "nama_dokter" },
            { data: "nama_pasien", name: "nama_pasien" },
            { data: "no_antrian", name: "no_antrian" },
            { data: "tanggal_kunjungan", name: "tanggal_kunjungan" },
            {
                data: "nama_obat",
                name: "nama_obat",
                orderable: false,
                searchable: false,
            },
            {
                data: "jumlah",
                name: "jumlah",
                orderable: false,
                searchable: false,
            },
            {
                data: "keterangan",
                name: "keterangan",
                orderable: false,
                searchable: false,
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

// edit data obat
$(function () {
    const modalElement = document.getElementById("updateStatusModal");
    const updateModal = modalElement ? new Modal(modalElement) : null;

    function resetForm() {
        $("#formUpdateStatus")[0].reset();
        $(".is-invalid").removeClass("is-invalid");
        $(".text-danger").empty();
    }

    // buka modal dari tombol di tabel
    $("body").on("click", ".btn-edit-status", function () {
        resetForm();
        const resepId = $(this).data("id");
        const obatId = $(this).data("obat-id");

        $("#resep_id").val(resepId);
        $("#obat_id").val(obatId);

        if (updateModal) updateModal.show();
    });

    // tutup modal
    $("#closeAddObatModal").on("click", function () {
        resetForm();
        updateModal?.hide();
    });

    // submit update status
    $("#formUpdateStatus").on("submit", function (e) {
        e.preventDefault();

        const url = $(this).data("url");
        const formData = {
            resep_id: $("#resep_id").val(),
            obat_id: $("#obat_id").val(),
            status: $("#status").val(),
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
                });
                updateModal.hide();
                table.ajax.reload(null, false);
            })
            .catch((error) => {
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors;
                    for (const field in errors) {
                        $(`#${field}`).addClass("is-invalid");
                        $(`#${field}-error`).text(errors[field][0]);
                    }
                    Swal.fire(
                        "Validasi Gagal!",
                        "Silakan periksa kembali isian Anda.",
                        "error"
                    );
                } else {
                    Swal.fire("Error!", "Terjadi kesalahan server.", "error");
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

// EDIT DOKTER
$(function () {
    const editModalElement = document.getElementById("editDokterModal");
    const editModal = editModalElement ? new Modal(editModalElement) : null;
    const $formEdit = $("#formEditDokter");
    const initialEditUrl = $formEdit.data("url");

    function resetEditForm() {
        $formEdit[0].reset();
        $formEdit.find(".is-invalid").removeClass("is-invalid");
        $formEdit.find(".text-red-600").empty();

        // reset URL ke awal
        $formEdit.data("url", initialEditUrl);
        $formEdit.attr("action", initialEditUrl);

        // Reset preview foto
        $("#preview_edit_foto_dokter").addClass("hidden").attr("src", "");
        $("#placeholder_edit_foto_dokter").removeClass("hidden");
        $("#foto_drop_area_edit")
            .removeClass("border-solid border-gray-300")
            .addClass("border-dashed border-gray-400");
    }

    // buka modal edit
    $("body").on("click", ".btn-edit-dokter", function () {
        resetEditForm();
        const dokterId = $(this).data("id");

        axios
            .get(`/manajemen_pengguna/get_dokter_by_id/${dokterId}`)
            .then((res) => {
                const dokter = res.data.data;
                const baseUrl = $formEdit.data("url");
                const finalUrl = baseUrl.replace("/0", "/" + dokter.id);
                $formEdit.data("url", finalUrl);
                $formEdit.attr("action", finalUrl);

                $("#edit_dokter_id").val(dokter.id);
                $("#edit_username_dokter").val(dokter.user.username);
                $("#edit_nama_dokter").val(dokter.nama_dokter);
                $("#edit_email_akun_dokter").val(dokter.user.email);
                $("#edit_spesialis_dokter").val(dokter.jenis_spesialis_id);
                $("#edit_no_hp_dokter").val(dokter.no_hp);
                $("#edit_deskripsi_dokter").val(dokter.deskripsi_dokter);
                $("#edit_pengalaman_dokter").val(dokter.pengalaman);

                // Tampilkan foto existing jika ada
                if (dokter.foto_dokter) {
                    const fotoUrl = `/storage/${dokter.foto_dokter}`;
                    $("#preview_edit_foto_dokter")
                        .attr("src", fotoUrl)
                        .removeClass("hidden");
                    $("#placeholder_edit_foto_dokter").addClass("hidden");
                    $("#foto_drop_area_edit")
                        .removeClass("border-dashed border-gray-400")
                        .addClass("border-solid border-gray-300");
                }

                if (editModal) editModal.show();
            })
            .catch((err) => {
                console.error(err);
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: "Tidak dapat memuat data dokter.",
                });
            });
    });

    // simpan update
    $formEdit.on("submit", function (e) {
        e.preventDefault();
        const url = $formEdit.data("url");
        const formData = new FormData($formEdit[0]);
        if (!formData.has("_method")) formData.append("_method", "PUT");

        axios
            .post(url, formData)
            .then((res) => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: res.data.success,
                    showConfirmButton: false,
                    timer: 2000,
                }).then(() => {
                    editModal.hide();
                    $("#dokterTable").DataTable().ajax.reload(null, false);
                    resetEditForm();
                });
            })
            .catch((err) => {
                if (err.response?.status === 422) {
                    const errors = err.response.data.errors;
                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal!",
                        text: "Silakan periksa kembali input Anda.",
                    });
                    for (const field in errors) {
                        $(`#edit_${field}`).addClass("is-invalid");
                        $(`#edit_${field}-error`).html(errors[field][0]);
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error Server!",
                        text: "Terjadi kesalahan server.",
                    });
                    console.error(err);
                }
            });
    });

    $("#closeEditDokterModal").on("click", function () {
        editModal?.hide();
        resetEditForm();
    });
});
