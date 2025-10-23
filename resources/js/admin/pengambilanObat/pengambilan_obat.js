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
            },
            {
                data: "jumlah",
                name: "jumlah",
            },
            {
                data: "keterangan",
                name: "keterangan",
            },
            {
                data: "status",
                name: "status",
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

$(function () {
    $("body").on("click", ".btnUpdateStatus", function () {
        const resepId = $(this).data("resep-id");
        const obatId = $(this).data("obat-id");
        const jumlah = $(this).data("jumlah");

        if (!resepId || !obatId) {
            Swal.fire({
                icon: "warning",
                title: "Data tidak lengkap!",
                text: "Resep ID atau Obat ID tidak ditemukan.",
            });
            return;
        }

        Swal.fire({
            title: "Ubah Status Pengambilan Obat",
            text: "Apakah pasien sudah mengambil obat?",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Iya",
            cancelButtonText: "Belum",
        }).then((result) => {
            if (result.isConfirmed) {
                axios
                    .post(`/pengambilan_obat/update-status-resep-obat`, {
                        resep_id: resepId,
                        obat_id: obatId,
                        jumlah_obat: jumlah,
                    })
                    .then((response) => {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text:
                                response.data.message ||
                                "Status resep obat berhasil diperbarui.",
                            showConfirmButton: false,
                            timer: 1500,
                        }).then(() => {
                            // reload DataTable atau halaman
                            if ($("#resepTable").length) {
                                $("#resepTable")
                                    .DataTable()
                                    .ajax.reload(null, false);
                            } else {
                                window.location.reload();
                            }
                        });
                    })
                    .catch((error) => {
                        console.error("SERVER ERROR:", error);

                        // Ambil pesan dari backend
                        const msg =
                            error.response?.data?.message ||
                            "Terjadi kesalahan server. Silakan coba lagi.";

                        // Tampilkan pesan berbeda kalau belum bayar
                        if (
                            msg.includes("Belum Bayar") ||
                            msg.includes("pembayaran")
                        ) {
                            Swal.fire({
                                icon: "warning",
                                title: "Tidak Bisa Diupdate!",
                                text: msg,
                                confirmButtonText: "OK",
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Gagal!",
                                text: msg,
                                confirmButtonText: "OK",
                            });
                        }
                    });
            }
        });
    });
});
