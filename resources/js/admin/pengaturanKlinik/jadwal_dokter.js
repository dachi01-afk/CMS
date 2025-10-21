import axios from "axios";
import { initFlowbite } from "flowbite";
import $ from "jquery";

// data jadwal dokter
$(function () {
    var table = $("#jadwalTable").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/pengaturan_klinik/jadwal_dokter",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "dokter", name: "dokter" },
            { data: "nama_poli", name: "nama_poli" },
            { data: "hari_formatted", name: "hari" },
            { data: "jam_awal", name: "jam_awal" },
            { data: "jam_selesai", name: "jam_selesai" },
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
    $("#jadwal_searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    const $info = $("#jadwal_customInfo");
    const $pagination = $("#jadwal_customPagination");
    const $perPage = $("#jadwal_pageLength");

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

// add jadwal dokter
$(function () {
    const addModalEl = document.getElementById("addJadwalModal");
    const addModal = addModalEl ? new Modal(addModalEl) : null;
    const $formAdd = $("#formAddJadwalDokter");

    function resetAddForm() {
        $formAdd[0].reset();
        $formAdd.find(".is-invalid").removeClass("is-invalid");
        $formAdd.find(".text-red-600").empty();
        $("#dokter_data_create").addClass("hidden");
        $("#search_results_create").addClass("hidden");
    }

    $("#btnAddJadwalDokter").on("click", function () {
        resetAddForm();
        addModal?.show();
    });

    $("#closeAddJadwalModal").on("click", function () {
        addModal?.hide();
        resetAddForm();
    });

    const searchInput = document.getElementById("search_dokter_create");
    const resultsDiv = document.getElementById("search_results_create");
    const dokterDataDiv = document.getElementById("dokter_data_create");

    searchInput.addEventListener("keyup", async () => {
        const query = searchInput.value.trim();
        if (query.length < 2) {
            resultsDiv.classList.add("hidden");
            return;
        }

        const response = await fetch(
            `/pengaturan_klinik/search?query=${query}`
        );
        const data = await response.json();

        console.log(data);

        resultsDiv.innerHTML = "";
        if (data.length > 0) {
            resultsDiv.classList.remove("hidden");
            data.forEach((dokter) => {
                const item = document.createElement("div");
                item.className =
                    "px-4 py-2 hover:bg-indigo-100 cursor-pointer text-sm";
                item.textContent = `${dokter.nama_dokter} (${dokter.nama_poli})`; // tampilkan poli di hasil pencarian

                item.onclick = () => {
                    document.getElementById("dokter_id_create").value =
                        dokter.id;
                    document.getElementById("poli_id_create").value =
                        dokter.poli_id;
                    document.getElementById("nama_dokter_create").textContent =
                        dokter.nama_dokter;
                    document.getElementById("nama_poli_create").textContent =
                        dokter.nama_poli || "-";
                    dokterDataDiv.classList.remove("hidden");
                    resultsDiv.classList.add("hidden");
                    searchInput.value = dokter.nama_dokter;
                };

                resultsDiv.appendChild(item);
            });
        } else {
            resultsDiv.classList.remove("hidden");
            resultsDiv.innerHTML = `<div class="px-4 py-2 text-gray-500 text-sm">Tidak ditemukan</div>`;
        }
    });

    $formAdd.on("submit", function (e) {
        e.preventDefault();
        const url = $formAdd.data("url");

        const formData = {
            dokter_id: $("#dokter_id_create").val(),
            poli_id: $("#poli_id_create").val(),
            hari: $("#hari").val(),
            jam_awal: $("#jam_awal").val(),
            jam_selesai: $("#jam_selesai").val(),
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
                $("#jadwalTable").DataTable().ajax.reload(null, false);
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

// edit jadwal dokter
$(function () {
    const editModalEl = document.getElementById("editJadwalModal");
    const editModal = editModalEl ? new Modal(editModalEl) : null;
    const $formEdit = $("#formEditJadwalDokter");

    // 🔁 Reset form edit setiap kali modal ditutup
    function resetEditForm() {
        $formEdit[0].reset();
        $formEdit.find(".is-invalid").removeClass("is-invalid");
        $formEdit.find(".text-red-600").empty();
        $("#dokter_data_update").addClass("hidden");
        $("#search_results_update").addClass("hidden");
    }

    // 🩺 Search Dokter (sama seperti create, tapi untuk modal edit)
    const searchInputUpdate = document.getElementById("search_dokter_update");
    const resultsDivUpdate = document.getElementById("search_results_update");
    const dokterDataDivUpdate = document.getElementById("dokter_data_update");

    if (searchInputUpdate) {
        searchInputUpdate.addEventListener("keyup", async () => {
            const query = searchInputUpdate.value.trim();
            if (query.length < 2) {
                resultsDivUpdate.classList.add("hidden");
                return;
            }

            const response = await fetch(
                `/pengaturan_klinik/search?query=${query}`
            );
            const data = await response.json();

            resultsDivUpdate.innerHTML = "";
            if (data.length > 0) {
                resultsDivUpdate.classList.remove("hidden");
                data.forEach((dokter) => {
                    const item = document.createElement("div");
                    item.className =
                        "px-4 py-2 hover:bg-indigo-100 cursor-pointer text-sm";
                    item.textContent = `${dokter.nama_dokter} (${
                        dokter.nama_poli || "-"
                    })`;

                    item.onclick = () => {
                        $("#dokter_id_update").val(dokter.id);
                        $("#poli_id_update").val(dokter.poli_id);
                        $("#nama_dokter_update").text(dokter.nama_dokter);
                        $("#nama_poli_update").text(dokter.nama_poli || "-");

                        dokterDataDivUpdate.classList.remove("hidden");
                        resultsDivUpdate.classList.add("hidden");
                        searchInputUpdate.value = dokter.nama_dokter;
                    };

                    resultsDivUpdate.appendChild(item);
                });
            } else {
                resultsDivUpdate.classList.remove("hidden");
                resultsDivUpdate.innerHTML = `<div class="px-4 py-2 text-gray-500 text-sm">Tidak ditemukan</div>`;
            }
        });
    }

    // ✏️ Klik tombol Edit
    $("body").on("click", ".btn-edit-jadwal", function () {
        resetEditForm();
        const jadwalId = $(this).data("id");

        axios
            .get(`/pengaturan_klinik/get_jadwal_dokter_by_id/${jadwalId}`)
            .then((response) => {
                const jadwal = response.data.data;

                // Set action URL
                const baseUrl = $formEdit
                    .data("url")
                    .replace("/0", "/" + jadwal.id);
                $formEdit.data("url", baseUrl);

                // Isi form dengan data dari backend
                $("#jadwal_id_update").val(jadwal.id);
                $("#dokter_id_update").val(jadwal.dokter.id);
                $("#poli_id_update").val(jadwal.poli.id);
                $("#hari_edit").val(jadwal.hari);
                $("#jam_awal_edit").val(jadwal.jam_awal);
                $("#jam_selesai_edit").val(jadwal.jam_selesai);

                // Tampilkan data dokter di modal edit
                $("#nama_dokter_update").text(jadwal.dokter.nama_dokter);
                $("#nama_poli_update").text(jadwal.poli.nama_poli);
                $("#search_dokter_update").val(jadwal.dokter.nama_dokter);
                $("#dokter_data_update").removeClass("hidden");

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

    // 💾 Submit form edit
    $formEdit.on("submit", function (e) {
        e.preventDefault();
        const url = $formEdit.data("url");

        const formData = {
            dokter_id: $("#dokter_id_update").val(),
            poli_id: $("#poli_id_update").val(),
            hari: $("#hari_edit").val(),
            jam_awal: $("#jam_awal_edit").val(),
            jam_selesai: $("#jam_selesai_edit").val(),
            _method: "PUT",
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
                $("#jadwalTable").DataTable().ajax.reload(null, false);
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

    // ❌ Tutup modal
    $("#closeEditJadwalModal").on("click", function () {
        editModal?.hide();
        resetEditForm();
    });
});

// delete data
$(function () {
    $("body").on("click", ".btn-delete-jadwal", function () {
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
                    .delete(
                        `/pengaturan_klinik/delete_jadwal_dokter/${dokterId}`
                    )
                    .then((response) => {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: response.data.message,
                            showConfirmButton: false,
                            timer: 1500,
                        }).then(() => {
                            if ($("#jadwalTable").length) {
                                $("#jadwalTable")
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
