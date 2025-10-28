import axios from "axios";
import $ from "jquery";

$(function () {
    var tabel = $("#transaksiMenunggu").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/kasir/get-data-pembayaran",
        columns: [
            {
                data: "DT_RowIndex",
                nama: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "nama_pasien", name: "nama_pasien" },
            {
                data: "tanggal_kunjungan",
                name: "tanggal_kunjungan",
                render: function (data) {
                    if (!data) return "-";
                    const date = new Date(data);
                    const waktuIndonesia = date.toLocaleDateString("id-ID", {
                        timeZone: "Asia/Jakarta",
                        day: "2-digit",
                        month: "long",
                        year: "numeric",
                        hour: "2-digit",
                        minute: "2-digit",
                    });
                    return waktuIndonesia;
                },
            },
            { data: "no_antrian", name: "no_antrian" },
            { data: "nama_obat", name: "nama_obat" },
            { data: "dosis", name: "dosis" },
            { data: "jumlah", name: "jumlah" },
            { data: "nama_layanan", name: "nama_layanan" },
            { data: "jumlah_layanan", name: "jumlah_layanan" },
            { data: "total_tagihan", name: "total_tagihan" },
            { data: "metode_pembayaran", name: "metode_pembayaran" },
            { data: "status", name: "status" },
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
    $("#transaksi-menunggu-search-input").on("keyup", function () {
        tabel.search(this.value).draw();
    });

    const $info = $("#transaksi-menunggu-custom-info");
    const $pagination = $("#transaksi-menunggu-custom-pagination");
    const $perPage = $("#transaksi-menunggu-page-length");

    function updatePagination() {
        const info = tabel.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages;

        // Update text info
        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${
                info.recordsDisplay
            } data (Halaman ${currentPage} dari ${totalPages})`
        );

        // Hapus pagination lama
        $pagination.empty();

        // Kalau cuma 1 halaman, sembunyikan pagination dan selesai
        if (totalPages <= 1) {
            $pagination.hide();
            return;
        } else {
            $pagination.show();
        }

        // Tombol Prev
        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(
            `<li><a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a></li>`
        );

        // Tombol angka halaman
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

        // Tombol Next
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
        if ($link.attr("id") === "btnPrev") tabel.page("previous").draw("page");
        else if ($link.attr("id") === "btnNext")
            tabel.page("next").draw("page");
        else if ($link.hasClass("page-number"))
            tabel.page(parseInt($link.data("page")) - 1).draw("page");
    });

    $perPage.on("change", function () {
        tabel.page.len(parseInt($(this).val())).draw();
    });

    tabel.on("draw", updatePagination);
    updatePagination();
});

$(document).on("click", ".bayarSekarang", function () {
    const url = $(this).data("url");
    window.location.href = url;
});

// $(function () {
//     const modalBayarSekarang = document.getElementById("modalBayarSekarang");
//     const modalNya = modalBayarSekarang ? new Modal(addModalElement) : null;
//     const $formBayarSekarang = $("#formBayarSekarang");
//     const actionForm = $formBayarSekarang.data('url');

//     function resetModal() {
//         $formBayarSekarang[0].reset();
//         $formBayarSekarang.find(".is-invalid").removeClass("is-invalid");
//         $formBayarSekarang.find(".text-red-600").empty();
//     }

//     $("body").on("click", ".bayarSekarang", function () {
//         const id = $(this).data("id");
//         const emrId = $(this).data("emr-id");
//     });

//     axios
//         .get(`/manajemen_pengguna/get_pasien_by_id/${pasienId}`)
//         .then((response) => {
//             const pasien = response.data.data;
//             const baseUrl = $formEdit.data("url");
//             const finalUrl = baseUrl.replace("/0", "/" + pasien.id);
//             $formEdit.data("url", finalUrl);
//             $formEdit.attr("action", finalUrl);

//             $("#edit_pasien_id").val(pasien.id);
//             $("#edit_username_pasien").val(pasien.user.username);
//             $("#edit_nama_pasien").val(pasien.nama_pasien);
//             $("#edit_email_pasien").val(pasien.user.email);
//             $("#edit_alamat_pasien").val(pasien.alamat);
//             $("#edit_tanggal_lahir_pasien").val(pasien.tanggal_lahir);
//             $("#edit_jenis_kelamin_pasien").val(pasien.jenis_kelamin);

//             // Tampilkan foto existing jika ada
//             if (pasien.foto_pasien) {
//                 const fotoUrl = `/storage/${pasien.foto_pasien}`;
//                 $("#edit_preview_foto_pasien")
//                     .attr("src", fotoUrl)
//                     .removeClass("hidden");
//                 $("#edit_placeholder_foto_pasien").addClass("hidden");
//                 $("#edit_foto_drop_area_pasien")
//                     .removeClass("border-dashed border-gray-400")
//                     .addClass("border-solid border-gray-300");
//             }
//             if (editModal) editModal.show();
//         })
//         .catch(() => {
//             Swal.fire({
//                 icon: "error",
//                 title: "Gagal!",
//                 text: "Tidak dapat memuat data pasien.",
//             });
//         });

//     $formBayarSekarang.on("submit", function (e) {
//         e.preventDefault();
//         const url = $formAdd.data("url");
//         const formData = new FormData($formBayarSekarang[0]);

//         $(".text-red-600").empty();
//         $formBayarSekarang.find(".is-invalid").removeClass("is-invalid");

//         axios
//             .post(url, formData, {
//                 headers: { "Content-Type": "multipart/form-data" },
//             })
//             .then((response) => {
//                 Swal.fire({
//                     icon: "success",
//                     title: "Berhasil!",
//                     text: response.data.message,
//                     showConfirmButton: false,
//                     timer: 2000,
//                 }).then(() => {
//                     addModal.hide();
//                     $("#transaksiMenunggu")
//                         .DataTable()
//                         .ajax.reload(null, false);
//                     resetModal();
//                 });
//             })
//             .catch((error) => {
//                 if (error.response && error.response.status === 422) {
//                     const errors = error.response.data.errors;
//                     Swal.fire({
//                         icon: "error",
//                         title: "Validasi Gagal!",
//                         text: "Silakan periksa kembali isian formulir Anda.",
//                     });
//                     for (const field in errors) {
//                         $(`#${field}`).addClass("is-invalid");
//                         $(`#${field}-error`).html(errors[field][0]);
//                     }
//                 }
//             });
//     });
// });

// $(function () {
//     const editModalElement = document.getElementById("editPasienModal");
//     const editModal = editModalElement ? new Modal(editModalElement) : null;
//     const $formEdit = $("#formEditPasien");
//     const initialEditUrl = $formEdit.data("url");

//     function resetEditForm() {
//         $formEdit[0].reset();
//         $formEdit.find(".is-invalid").removeClass("is-invalid");
//         $formEdit.find(".text-red-600").html("");

//         // reset URL ke awal
//         $formEdit.data("url", initialEditUrl);
//         $formEdit.attr("action", initialEditUrl);

//         // Reset preview foto
//         $("#edit_preview_foto_pasien").addClass("hidden").attr("src", "");
//         $("#edit_placeholder_foto_pasien").removeClass("hidden");
//         $("#edit_foto_drop_area_pasien")
//             .removeClass("border-solid border-gray-300")
//             .addClass("border-dashed border-gray-400");
//     }

//     $("body").on("click", ".btn-edit-pasien", function () {
//         resetEditForm();
//         const pasienId = $(this).data("id");

//         axios
//             .get(`/manajemen_pengguna/get_pasien_by_id/${pasienId}`)
//             .then((response) => {
//                 const pasien = response.data.data;
//                 const baseUrl = $formEdit.data("url");
//                 const finalUrl = baseUrl.replace("/0", "/" + pasien.id);
//                 $formEdit.data("url", finalUrl);
//                 $formEdit.attr("action", finalUrl);

//                 $("#edit_pasien_id").val(pasien.id);
//                 $("#edit_username_pasien").val(pasien.user.username);
//                 $("#edit_nama_pasien").val(pasien.nama_pasien);
//                 $("#edit_email_pasien").val(pasien.user.email);
//                 $("#edit_alamat_pasien").val(pasien.alamat);
//                 $("#edit_tanggal_lahir_pasien").val(pasien.tanggal_lahir);
//                 $("#edit_jenis_kelamin_pasien").val(pasien.jenis_kelamin);

//                 // Tampilkan foto existing jika ada
//                 if (pasien.foto_pasien) {
//                     const fotoUrl = `/storage/${pasien.foto_pasien}`;
//                     $("#edit_preview_foto_pasien")
//                         .attr("src", fotoUrl)
//                         .removeClass("hidden");
//                     $("#edit_placeholder_foto_pasien").addClass("hidden");
//                     $("#edit_foto_drop_area_pasien")
//                         .removeClass("border-dashed border-gray-400")
//                         .addClass("border-solid border-gray-300");
//                 }
//                 if (editModal) editModal.show();
//             })
//             .catch(() => {
//                 Swal.fire({
//                     icon: "error",
//                     title: "Gagal!",
//                     text: "Tidak dapat memuat data pasien.",
//                 });
//             });
//     });

//     $formEdit.on("submit", function (e) {
//         e.preventDefault();
//         const url = $formEdit.data("url");
//         const formData = new FormData($formEdit[0]);
//         if (!formData.has("_method")) formData.append("_method", "PUT");

//         axios
//             .post(url, formData)
//             .then((response) => {
//                 Swal.fire({
//                     icon: "success",
//                     title: "Berhasil!",
//                     text: response.data.message,
//                     showConfirmButton: false,
//                     timer: 2000,
//                 }).then(() => {
//                     editModal.hide();
//                     $("#pasienTable").DataTable().ajax.reload(null, false);
//                     resetEditForm();
//                 });
//             })
//             .catch((error) => {
//                 if (error.response && error.response.status === 422) {
//                     const errors = error.response.data.errors;
//                     for (const field in errors) {
//                         $(`#edit_${field}`).addClass("is-invalid");
//                         $(`#edit_${field}-error`).html(errors[field][0]);
//                     }
//                 } else {
//                     Swal.fire({
//                         icon: "error",
//                         title: "Error!",
//                         text: "Terjadi kesalahan server.",
//                     });
//                 }
//             });
//     });

//     $("#closeEditPasienModal").on("click", function () {
//         if (editModal) editModal.hide();
//         resetEditForm();
//     });
// });
