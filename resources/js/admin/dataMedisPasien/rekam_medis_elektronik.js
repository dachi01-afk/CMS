import axios from "axios";
import { initFlowbite, Modal } from "flowbite";
import $ from "jquery";

/* =========================
   KONFIGURASI ENDPOINT
========================= */
const ENDPOINT_LIST = "/data_medis_pasien/data_emr";
const DETAIL_BASE = "/data_medis_pasien/detail-emr";

/* =========================
   UTIL: Format Tanggal Aman
   - Mendukung input "YYYY-MM-DD" atau ISO datetime.
   - Jika parsable: tampilkan dd MMMM yyyy HH.mm (WIB)
   - Jika hanya tanggal: tampilkan dd MMMM yyyy
========================= */
function formatTanggalID(tanggal) {
    if (!tanggal) return "-";
    // Cek pola tanggal saja (YYYY-MM-DD)
    const onlyDate = /^\d{4}-\d{2}-\d{2}$/.test(tanggal);
    const d = new Date(tanggal);
    if (isNaN(d.getTime())) {
        // fallback: tampilkan raw jika Date gagal parse
        return tanggal;
    }
    if (onlyDate) {
        return d.toLocaleDateString("id-ID", {
            timeZone: "Asia/Jakarta",
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
    }
    return d
        .toLocaleString("id-ID", {
            timeZone: "Asia/Jakarta",
            day: "2-digit",
            month: "long",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        })
        .replace(".", ":"); // opsional: 10.05 -> 10:05
}

/* =========================
   DATA TABLE EMR
========================= */
$(function () {
    initFlowbite?.();

    const table = $("#emrTable").DataTable({
        processing: true,
        responsive: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: ENDPOINT_LIST,
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
                className: "whitespace-nowrap",
            },
            { data: "nama_pasien", name: "nama_pasien", defaultContent: "-" },
            { data: "nama_dokter", name: "nama_dokter", defaultContent: "-" },
            {
                data: "tanggal_kunjungan",
                name: "tanggal_kunjungan",
                render: (data) => formatTanggalID(data),
                className: "whitespace-nowrap",
            },
            { data: "keluhan_awal", name: "keluhan_awal", defaultContent: "-" },
            {
                data: "keluhan_utama",
                name: "keluhan_utama",
                defaultContent: "-",
            },
            {
                data: "riwayat_penyakit_dahulu",
                name: "riwayat_penyakit_dahulu",
                defaultContent: "-",
            },
            {
                data: "riwayat_penyakit_keluarga",
                name: "riwayat_penyakit_keluarga",
                defaultContent: "-",
            },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
                className: "text-center whitespace-nowrap",
                defaultContent: "",
            },
        ],
        dom: "t",
        rowCallback: function (row) {
            $(row).addClass(
                "bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
            );
            $("td", row).addClass("px-6 py-4 text-gray-900 dark:text-white");
        },
    });

    /* =========================
     SEARCH
  ========================= */
    $("#emr-searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    /* =========================
     PAGINATION CUSTOM
  ========================= */
    const $info = $("#emr-customInfo");
    const $pagination = $("#emr-customPagination");
    const $perPage = $("#emr-pageLength");

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
            currentPage === 1 ? "opacity-50 pointer-events-none" : "";
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
            currentPage === totalPages ? "opacity-50 pointer-events-none" : "";
        $pagination.append(
            `<li><a href="#" id="btnNext" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">Next</a></li>`
        );
    }

    $pagination.on("click", "a", function (e) {
        e.preventDefault();
        const $link = $(this);
        if (
            $link.hasClass("opacity-50") ||
            $link.hasClass("pointer-events-none")
        )
            return;

        if ($link.attr("id") === "btnPrev") {
            table.page("previous").draw("page");
        } else if ($link.attr("id") === "btnNext") {
            table.page("next").draw("page");
        } else if ($link.hasClass("page-number")) {
            table.page(parseInt($link.data("page")) - 1).draw("page");
        }
    });

    $perPage.on("change", function () {
        table.page.len(parseInt($(this).val(), 10)).draw();
    });

    table.on("draw", updatePagination);
    updatePagination();

    /* =========================
     LIHAT DETAIL EMR
     - Tombol dari server: .btn-detail-emr data-id
     - Redirect ke /data_medis_pasien/detail/{id}
     - (Opsional) ganti ke modal AJAX kalau dibutuhkan
  ========================= */
    $("body").on("click", ".btn-detail-emr", function () {
        const id = $(this).data("id");
        if (!id) return;
        window.location.href = `${DETAIL_BASE}/${id}`;
    });
});

/* =========================
   DELETE DATA LAYANAN (tetap)
========================= */
$(function () {
    $("body").on("click", ".btn-delete-layanan", function () {
        const id = $(this).data("id");
        if (!id) return;

        const formData = { id };

        // Pastikan Swal tersedia secara global
        // Jika pakai ES module, impor: import Swal from 'sweetalert2'
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
                    .post(`/layanan/delete-data-layanan`, formData)
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
