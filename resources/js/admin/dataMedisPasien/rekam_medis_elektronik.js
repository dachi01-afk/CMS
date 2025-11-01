import axios from "axios";
import { initFlowbite } from "flowbite";
import $ from "jquery";

// data poli
$(function () {
    var table = $("#emrTable").DataTable({
        processing: true,
        responsive: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/data_medis_pasien/data_emr",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "nama_pasien", name: "nama_pasien" },
            { data: "nama_dokter", name: "nama_dokter" },
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
            { data: "keluhan_awal", name: "keluhan_awal" },
            { data: "keluhan_utama", name: "keluhan_utama" },
            {
                data: "riwayat_penyakit_dahulu",
                name: "riwayat_penyakit_dahulu",
            },
            {
                data: "riwayat_penyakit_keluarga",
                name: "riwayat_penyakit_keluarga",
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
    $("#emr-searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

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

// lihat detail emr
$(function () {
    const modalEl = document.getElementById("modalDetailEMR");
    const modalDetailEMR = modalEl ? new Modal(modalEl) : null;

    $("body").on("click", ".btn-detail-emr", function () {
        const id = $(this).data("id");
        const dokter = $(this).data("dokter");

        axios
            .get(`/data_medis_pasien/get-data-emr-by-id/${id}`)
            .then((response) => {
                const data = response.data.data;
                const tanggal = data.kunjungan.tanggal_kunjungan
                    ? new Date(
                          data.kunjungan.tanggal_kunjungan
                      ).toLocaleDateString("id-ID", {
                          day: "2-digit",
                          month: "long",
                          year: "numeric",
                      })
                    : "-";

                $("#detail_nama_pasien").text(
                    data.kunjungan.pasien.nama_pasien || "-"
                );
                $("#detail_nama_dokter").text(dokter || "-");
                $("#detail_tanggal_kunjungan").text(tanggal);
                $("#detail_keluhan_awal").text(
                    data.kunjungan.keluhan_awal || "-"
                );
                $("#detail_keluhan_utama").text(data.keluhan_utama || "-");
                $("#detail_riwayat_penyakit_dahulu").text(
                    data.riwayat_penyakit_dahulu || "-"
                );
                $("#detail_riwayat_penyakit_keluarga").text(
                    data.riwayat_penyakit_keluarga || "-"
                );
                $("#tekanan_darah").text(data.tekanan_darah || "-");
                $("#suhu_tubuh").text(data.suhu_tubuh || "-");
                $("#nadi").text(data.nadi || "-");
                $("#pernapasan").text(data.pernapasan || "-");
                $("#saturasi_oksigen").text(data.saturasi_oksigen || "-");
                $("#diagnosis").text(data.diagnosis || "-");

                modalDetailEMR?.show();
            })
            .catch(() => {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: "Tidak dapat memuat data EMR.",
                });
            });

        $("#closeDetailEMR").on("click", function () {
            modalDetailEMR?.hide();
        });

        $("#buttonCloseModalDetailEMR").on("click", function () {
            modalDetailEMR?.hide();
        });
    });
});

// update data layanan

// delete data
$(function () {
    $("body").on("click", ".btn-delete-layanan", function () {
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
