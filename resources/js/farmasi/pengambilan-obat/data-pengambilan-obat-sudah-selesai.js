import axios from "axios";
import { initFlowbite } from "flowbite";
import $ from "jquery";

// data tabel Dokter
$(function () {
    const formatTanggalID = (data) => {
        if (!data) return "-";
        const date = new Date(data);
        return date.toLocaleDateString("id-ID", {
            timeZone: "Asia/Jakarta",
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
    };

    // Inisialisasi DataTable
    var table = $("#pengambilanResepObatSudahSelesai").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/farmasi/pengambilan-obat/get-data-resep-obat-selesai",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "nama_pasien", name: "nama_pasien" },
            { data: "nama_poli", name: "nama_poli" },
            { data: "nama_dokter", name: "nama_dokter" },
            { data: "no_antrian", name: "no_antrian" },
            {
                data: "tanggal_kunjungan",
                name: "tanggal_kunjungan",
                render: (data) => formatTanggalID(data),
            },
            { data: "status", name: "status" },
            { data: "action", name: "action" },
        ],
        dom: "t",
        rowCallback: function (row, data) {
            $(row).addClass(
                "bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600",
            );
            $("td", row).addClass("px-6 py-4 text-gray-900 dark:text-white");
        },
    });

    // 🔎 Hubungkan search input Dokter
    $("#resep_obat_searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    const $info = $("#sudah_selesai_customInfo");
    const $pagination = $("#sudah_selesai_customPagination");
    const $perPage = $("#suda_selesai_pageLength");

    // 🔁 Update Pagination Dinamis
    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages;

        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${
                info.recordsDisplay
            } data (Halaman ${currentPage} dari ${totalPages})`,
        );

        $pagination.empty();

        // Tombol Prev
        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(`
            <li>
                <a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a>
            </li>
        `);

        // Nomor halaman
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
        if ($link.hasClass("opacity-50")) return;

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

    // ===========================
    // ✅ RELOAD SAAT TAB DIKLIK
    // ===========================
    $("#tab-sudah-selesai").on("click", function () {
        // reload data realtime (tanpa reset halaman)
        table.ajax.reload(null, false);

        // karena tabel awalnya hidden, ini bantu kolomnya rapi
        setTimeout(() => {
            table.columns.adjust().draw(false);
        }, 50);
    });
});

$(document).on("click", "#btn-lihat-detail-selesai", function () {
    const resepId = $(this).data("resep-id");

    // Reset dan Tampilkan Modal
    $("#resep-id-selesai").text(resepId);
    $("#resep-obat-selesai").html(`
        <tr>
            <td colspan="4" class="text-center py-10">
                <i class="fa-solid fa-spinner fa-spin text-teal-500 text-2xl"></i>
                <p class="text-gray-500 mt-2 text-xs font-medium">Mengambil data resep...</p>
            </td>
        </tr>
    `);
    $("#modal-detail-resep-selesai").removeClass("hidden");

    $.ajax({
        url: `/farmasi/pengambilan-obat/get-data-resep-obat-detail/${resepId}`,
        method: "GET",
        success: function (response) {
            let html = "";

            // Akses data obat sesuai struktur JSON kamu
            const dataObat = response.data.obat;

            if (dataObat && dataObat.length > 0) {
                dataObat.forEach((item) => {
                    // Gunakan data dari pivot agar akurat sesuai resep tersebut
                    const nama = item.nama_obat || "-";
                    const jml = item.pivot
                        ? item.pivot.jumlah
                        : item.jumlah || 0;
                    const dss = item.pivot
                        ? item.pivot.dosis
                        : item.dosis || "-";
                    const ket = item.pivot
                        ? item.pivot.keterangan
                        : item.keterangan || "-";

                    html += `
                        <tr class="hover:bg-teal-50/30 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-bold text-gray-800">${nama}</div>
                                <div class="text-[10px] text-gray-500">${item.kode_obat}</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block bg-teal-100 text-teal-700 px-2 py-0.5 rounded font-bold">
                                    ${jml}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 font-medium">${dss}</td>
                            <td class="px-4 py-3 text-gray-500 italic">${ket}</td>
                        </tr>
                    `;
                });
            } else {
                html =
                    '<tr><td colspan="4" class="text-center py-10 text-gray-400 font-medium">Tidak ada rincian obat ditemukan.</td></tr>';
            }

            $("#resep-obat-selesai").html(html);
        },
        error: function () {
            $("#resep-obat-selesai").html(
                '<tr><td colspan="4" class="text-center py-10 text-red-500 font-bold">Gagal mengambil data dari server.</td></tr>',
            );
        },
    });
});

// Fungsi untuk menutup modal
function closeModalDetail() {
    $("#modal-detail-resep-selesai").addClass("hidden");
}

$(document).on("click", "#btn-close-modal-selesai", function () {
    closeModalDetail();
});
