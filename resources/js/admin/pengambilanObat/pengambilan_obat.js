import axios from "axios";
import { initFlowbite } from "flowbite";
import $ from "jquery";

initFlowbite();

// =======================
// DataTable Pengambilan Obat
// =======================
$(function () {
    const table = $("#pengambilanResepObat").DataTable({
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
        const totalPages = info.pages || 1;

        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${
                info.recordsDisplay
            } data (Halaman ${currentPage} dari ${totalPages})`
        );
        $pagination.empty();

        // Prev
        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(
            `<li><a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a></li>`
        );

        // Numbered pages
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
            $pagination.append(
                `<li><a href="#" class="page-number flex items-center justify-center px-3 h-8 border ${active}" data-page="${i}">${i}</a></li>`
            );
        }

        // Next
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
});

// =======================
// Update Status Pengambilan Obat
// =======================
$(function () {
    $("body").on("click", ".btnUpdateStatus", function () {
        const resepId = $(this).data("resep-id");
        const obatRaw = $(this).data("obat"); // bisa array of object / 1 object / dll

        // 🔄 Normalisasi obatData -> array [{id, jumlah}, ...]
        let obatData = [];

        if (Array.isArray(obatRaw)) {
            obatData = obatRaw
                .map((item) => {
                    if (!item) return null;

                    // dukung beberapa kemungkinan key
                    const id = item.id ?? item.obat_id ?? item.obatId ?? null;
                    const jumlah =
                        item.jumlah ?? item.qty ?? item.kuantitas ?? null;

                    if (!id || !jumlah) return null;

                    return {
                        id: Number(id),
                        jumlah: Number(jumlah),
                    };
                })
                .filter(Boolean);
        } else if (obatRaw && typeof obatRaw === "object") {
            const id = obatRaw.id ?? obatRaw.obat_id ?? obatRaw.obatId ?? null;
            const jumlah =
                obatRaw.jumlah ?? obatRaw.qty ?? obatRaw.kuantitas ?? null;

            if (id && jumlah) {
                obatData = [
                    {
                        id: Number(id),
                        jumlah: Number(jumlah),
                    },
                ];
            }
        }

        if (!resepId || !Array.isArray(obatData) || obatData.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "Data tidak lengkap!",
                text: "Resep ID atau data obat tidak valid.",
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
            if (!result.isConfirmed) return;

            axios
                .post(`farmasi/pengambilan-obat/update-status-resep-obat`, {
                    resep_id: resepId,
                    obat_list: obatData, // [{id, jumlah}, ...]
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
                        // 🔁 reload DataTable pengambilanResepObat
                        if ($("#pengambilanResepObat").length) {
                            $("#pengambilanResepObat")
                                .DataTable()
                                .ajax.reload(null, false);
                        } else {
                            window.location.reload();
                        }
                    });
                })
                .catch((error) => {
                    const msg =
                        error.response?.data?.message ||
                        "Terjadi kesalahan server. Silakan coba lagi.";

                    if (
                        msg.includes("Belum Bayar") ||
                        msg.toLowerCase().includes("pembayaran")
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
        });
    });
});
