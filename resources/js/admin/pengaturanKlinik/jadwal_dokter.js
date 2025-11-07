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
            { data: "nama_dokter", name: "dokter" },
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

// ADD JADWAL DOKTER - pilih Dokter dulu lalu Poli
// $(function () {
//     const addModalEl = document.getElementById("addJadwalModal");
//     const addModal = addModalEl ? new Modal(addModalEl) : null;
//     const $formAdd = $("#formAddJadwalDokter");

//     const searchInput = document.getElementById("search_dokter_create");
//     const resultsDiv = document.getElementById("search_results_create");
//     const loader = document.getElementById("search_loader_create");
//     const dokterChip = document.getElementById("dokter_chip_create");
//     const dokterChipName = document.getElementById("dokter_chip_name");
//     const dokterChipClear = document.getElementById("dokter_chip_clear");

//     const $dokterId = $("#dokter_id_create");
//     const $poliId = $("#poli_id_create");
//     const $poliSelect = $("#poli_select_create");
//     const $poliHelp = $("#poli_select_help");

//     function resetAddForm() {
//         $formAdd[0].reset();
//         $formAdd.find(".is-invalid").removeClass("is-invalid");
//         $formAdd.find(".text-red-600").empty();

//         // reset state
//         resultsDiv.classList.add("hidden");
//         resultsDiv.innerHTML = "";
//         loader.classList.add("hidden");

//         dokterChip.classList.add("hidden");
//         dokterChipName.textContent = "";

//         $dokterId.val("");
//         $poliId.val("");

//         $poliSelect
//             .prop("disabled", true)
//             .empty()
//             .append(`<option value="">— pilih poli —</option>`);
//         $poliHelp.text("Cari & pilih dokter terlebih dahulu.");
//     }

//     $("#btnAddJadwalDokter").on("click", function () {
//         resetAddForm();
//         addModal?.show();
//     });
//     $("#closeAddJadwalModal").on("click", function () {
//         addModal?.hide();
//         resetAddForm();
//     });

//     // Dedupe dokter dari hasil /search (yang mungkin flatten)
//     function dedupeDokter(items) {
//         const map = new Map(); // id -> {id,nama_dokter}
//         items.forEach((it) => {
//             if (!map.has(it.id))
//                 map.set(it.id, { id: it.id, nama_dokter: it.nama_dokter });
//         });
//         return Array.from(map.values());
//     }

//     // Fetch poli milik dokter terpilih
//     async function loadPoliByDokter(dokterId) {
//         $poliSelect
//             .prop("disabled", true)
//             .empty()
//             .append(`<option value="">Loading…</option>`);
//         $poliHelp.text("Memuat daftar poli…");

//         try {
//             const res = await fetch(
//                 `/pengaturan_klinik/search-poli-by-dokter/${dokterId}`
//             );
//             const polis = await res.json(); // [{id, nama_poli}, …]

//             $poliSelect
//                 .empty()
//                 .append(`<option value="">— pilih poli —</option>`);
//             if (polis.length === 0) {
//                 $poliSelect.prop("disabled", true);
//                 $poliHelp.text("Dokter ini belum memiliki poli terkait.");
//             } else {
//                 polis.forEach((p) =>
//                     $poliSelect.append(
//                         `<option value="${p.id}">${p.nama_poli}</option>`
//                     )
//                 );
//                 $poliSelect.prop("disabled", false);
//                 $poliHelp.text("Pilih salah satu poli di atas.");
//             }
//         } catch (e) {
//             $poliSelect
//                 .prop("disabled", true)
//                 .empty()
//                 .append(`<option value="">Gagal memuat poli</option>`);
//             $poliHelp.text("Terjadi kesalahan ketika memuat poli.");
//             console.error(e);
//         }
//     }

//     // Search dokter (≥2 char), tampilkan list unik
//     searchInput.addEventListener("keyup", async () => {
//         const query = searchInput.value.trim();
//         if (query.length < 2) {
//             resultsDiv.classList.add("hidden");
//             resultsDiv.innerHTML = "";
//             return;
//         }
//         loader.classList.remove("hidden");

//         try {
//             const response = await fetch(
//                 `/pengaturan_klinik/search?query=${encodeURIComponent(query)}`
//             );
//             const data = await response.json(); // bisa flatten (dokter,poli)
//             const listDokter = dedupeDokter(data);

//             resultsDiv.innerHTML = "";
//             if (listDokter.length === 0) {
//                 resultsDiv.classList.remove("hidden");
//                 resultsDiv.innerHTML = `<div class="px-4 py-2 text-gray-500 text-sm">Tidak ditemukan</div>`;
//                 return;
//             }

//             resultsDiv.classList.remove("hidden");
//             listDokter.forEach((d) => {
//                 const item = document.createElement("button");
//                 item.type = "button";
//                 item.className =
//                     "w-full text-left px-4 py-2 hover:bg-indigo-50 cursor-pointer text-sm";
//                 item.innerHTML = `<i class="fa-solid fa-user-doctor mr-2 text-indigo-600"></i>${d.nama_dokter}`;
//                 item.onclick = () => {
//                     $dokterId.val(d.id);
//                     searchInput.value = d.nama_dokter;
//                     dokterChipName.textContent = d.nama_dokter;
//                     dokterChip.classList.remove("hidden");
//                     resultsDiv.classList.add("hidden");
//                     // kosongkan poli_id dan muat opsi poli milik dokter ini
//                     $poliId.val("");
//                     loadPoliByDokter(d.id);
//                 };
//                 resultsDiv.appendChild(item);
//             });
//         } finally {
//             loader.classList.add("hidden");
//         }
//     });

//     // Clear dokter terpilih
//     dokterChipClear.addEventListener("click", () => {
//         searchInput.value = "";
//         dokterChip.classList.add("hidden");
//         dokterChipName.textContent = "";
//         $dokterId.val("");
//         $poliId.val("");
//         $poliSelect
//             .prop("disabled", true)
//             .empty()
//             .append(`<option value="">— pilih poli —</option>`);
//         $poliHelp.text("Cari & pilih dokter terlebih dahulu.");
//         searchInput.focus();
//     });

//     // Saat pilih poli dari select
//     $poliSelect.on("change", function () {
//         $poliId.val(this.value || "");
//     });

//     // Submit
//     $formAdd.on("submit", function (e) {
//         e.preventDefault();
//         const url = $formAdd.data("url");

//         const formData = {
//             dokter_id: $("#dokter_id_create").val(),
//             poli_id: $("#poli_id_create").val(),
//             hari: $("#hari").val(),
//             jam_awal: $("#jam_awal").val(),
//             jam_selesai: $("#jam_selesai").val(),
//         };

//         axios
//             .post(url, formData)
//             .then((response) => {
//                 Swal.fire({
//                     icon: "success",
//                     title: "Berhasil!",
//                     text: response.data.message,
//                     timer: 2000,
//                     showConfirmButton: false,
//                 });
//                 addModal?.hide();
//                 $("#jadwalTable").DataTable().ajax.reload(null, false);
//             })
//             .catch((error) => {
//                 if (error.response?.status === 422) {
//                     const errors = error.response.data.errors || {};
//                     for (const field in errors) {
//                         $(`#${field}`).addClass("is-invalid");
//                         $(`#${field}-error`).html(errors[field][0]);
//                     }
//                     Swal.fire({
//                         icon: "error",
//                         title: "Validasi Gagal!",
//                         text: "Periksa kembali input Anda.",
//                     });
//                 } else {
//                     Swal.fire({
//                         icon: "error",
//                         title: "Error Server!",
//                         text: "Terjadi kesalahan server.",
//                     });
//                 }
//             });
//     });
// });

// ADD JADWAL DOKTER - pilih Dokter dulu lalu Poli
$(function () {
    const addModalEl = document.getElementById("addJadwalModal");
    const addModal = addModalEl ? new Modal(addModalEl) : null;
    const $formAdd = $("#formAddJadwalDokter");

    const searchInput = document.getElementById("search_dokter_create");
    const resultsDiv = document.getElementById("search_results_create");
    const loader = document.getElementById("search_loader_create");
    const dokterChip = document.getElementById("dokter_chip_create");
    const dokterChipName = document.getElementById("dokter_chip_name");
    const dokterChipClear = document.getElementById("dokter_chip_clear");

    const $dokterId = $("#dokter_id_create");
    const $poliId = $("#poli_id_create");
    const $poliSelect = $("#poli_select_create");
    const $poliHelp = $("#poli_select_help");
    const groupPoli = document.getElementById("group_poli_create"); // ⬅️ kontrol tampil/sembunyi

    function resetAddForm() {
        $formAdd[0].reset();
        $formAdd.find(".is-invalid").removeClass("is-invalid");
        $formAdd.find(".text-red-600").empty();

        resultsDiv.classList.add("hidden");
        resultsDiv.innerHTML = "";
        loader.classList.add("hidden");

        dokterChip.classList.add("hidden");
        dokterChipName.textContent = "";

        $dokterId.val("");
        $poliId.val("");

        // sembunyikan blok poli saat awal
        groupPoli.classList.add("hidden");
        $poliSelect
            .prop("disabled", true)
            .empty()
            .append(`<option value="">— pilih poli —</option>`);
        $poliHelp.text("Cari & pilih dokter terlebih dahulu.");
    }

    $("#btnAddJadwalDokter").on("click", function () {
        resetAddForm();
        addModal?.show();
    });
    $("#closeAddJadwalModal").on("click", function () {
        addModal?.hide();
        resetAddForm();
    });

    function dedupeDokter(items) {
        const map = new Map();
        items.forEach((it) => {
            if (!map.has(it.id))
                map.set(it.id, { id: it.id, nama_dokter: it.nama_dokter });
        });
        return Array.from(map.values());
    }

    async function loadPoliByDokter(dokterId) {
        // tampilkan blok Poli & set state loading
        groupPoli.classList.remove("hidden");
        $poliSelect
            .prop("disabled", true)
            .empty()
            .append(`<option value="">Loading…</option>`);
        $poliHelp.text("Memuat daftar poli…");

        try {
            const res = await fetch(
                `/pengaturan_klinik/search-poli-by-dokter/${dokterId}`
            );
            const polis = await res.json(); // [{id, nama_poli}, …]

            $poliSelect
                .empty()
                .append(`<option value="">— pilih poli —</option>`);
            if (!Array.isArray(polis) || polis.length === 0) {
                $poliSelect.prop("disabled", true);
                $poliHelp.text("Dokter ini belum memiliki poli terkait.");
            } else {
                polis.forEach((p) => {
                    $poliSelect.append(
                        `<option value="${p.id}">${p.nama_poli}</option>`
                    );
                });
                $poliSelect.prop("disabled", false);
                $poliHelp.text("Pilih salah satu poli di atas.");
            }
        } catch (e) {
            $poliSelect
                .prop("disabled", true)
                .empty()
                .append(`<option value="">Gagal memuat poli</option>`);
            $poliHelp.text("Terjadi kesalahan ketika memuat poli.");
            console.error(e);
        }
    }

    // Search dokter (≥2 char)
    searchInput.addEventListener("keyup", async () => {
        const query = searchInput.value.trim();
        if (query.length < 2) {
            resultsDiv.classList.add("hidden");
            resultsDiv.innerHTML = "";
            return;
        }
        loader.classList.remove("hidden");

        try {
            const response = await fetch(
                `/pengaturan_klinik/search?query=${encodeURIComponent(query)}`
            );
            const data = await response.json(); // flatten (dokter,poli)
            const listDokter = dedupeDokter(data);

            resultsDiv.innerHTML = "";
            if (listDokter.length === 0) {
                resultsDiv.classList.remove("hidden");
                resultsDiv.innerHTML = `<div class="px-4 py-2 text-gray-500 text-sm">Tidak ditemukan</div>`;
                return;
            }

            resultsDiv.classList.remove("hidden");
            listDokter.forEach((d) => {
                const item = document.createElement("button");
                item.type = "button";
                item.className =
                    "w-full text-left px-4 py-2 hover:bg-indigo-50 cursor-pointer text-sm";
                item.innerHTML = `<i class="fa-solid fa-user-doctor mr-2 text-indigo-600"></i>${d.nama_dokter}`;
                item.onclick = () => {
                    // set dokter
                    $dokterId.val(d.id);
                    searchInput.value = d.nama_dokter;
                    dokterChipName.textContent = d.nama_dokter;
                    dokterChip.classList.remove("hidden");
                    resultsDiv.classList.add("hidden");

                    // reset poli_id dan muat opsi poli dokter
                    $poliId.val("");
                    loadPoliByDokter(d.id);
                };
                resultsDiv.appendChild(item);
            });
        } finally {
            loader.classList.add("hidden");
        }
    });

    // Ganti dokter
    dokterChipClear.addEventListener("click", () => {
        searchInput.value = "";
        dokterChip.classList.add("hidden");
        dokterChipName.textContent = "";
        $dokterId.val("");
        $poliId.val("");

        // sembunyikan blok poli kembali
        groupPoli.classList.add("hidden");
        $poliSelect
            .prop("disabled", true)
            .empty()
            .append(`<option value="">— pilih poli —</option>`);
        $poliHelp.text("Cari & pilih dokter terlebih dahulu.");

        searchInput.focus();
    });

    // Saat pilih poli
    $poliSelect.on("change", function () {
        $poliId.val(this.value || "");
    });

    // Submit
    //   $formAdd.on("submit", function (e) {
    //     e.preventDefault();
    //     const url = $formAdd.data("url");

    //     const formData = {
    //       dokter_id: $("#dokter_id_create").val(),
    //       poli_id:   $("#poli_id_create").val(),
    //       hari:      $("#hari").val(),
    //       jam_awal:  $("#jam_awal").val(),
    //       jam_selesai: $("#jam_selesai").val(),
    //     };

    //     axios.post(url, formData)
    //       .then((response) => {
    //         Swal.fire({ icon: "success", title: "Berhasil!", text: response.data.message, timer: 2000, showConfirmButton: false });
    //         addModal?.hide();
    //         $("#jadwalTable").DataTable().ajax.reload(null, false);
    //       })
    //       .catch((error) => {
    //         if (error.response?.status === 422) {
    //           const errors = error.response.data.errors || {};
    //           for (const field in errors) {
    //             $(`#${field}`).addClass("is-invalid");
    //             $(`#${field}-error`).html(errors[field][0]);
    //           }
    //           Swal.fire({ icon: "error", title: "Validasi Gagal!", text: "Periksa kembali input Anda." });
    //         } else {
    //           Swal.fire({ icon: "error", title: "Error Server!", text: "Terjadi kesalahan server." });
    //         }
    //       });
    //   });

    $formAdd.on("submit", function (e) {
        e.preventDefault();

        // Guard: pastikan dokter & poli sudah dipilih
        const dokterIdVal = $("#dokter_id_create").val();
        const poliIdVal = $("#poli_id_create").val();

        if (!dokterIdVal) {
            Swal.fire({
                icon: "warning",
                title: "Pilih Dokter",
                text: "Silakan pilih dokter terlebih dahulu.",
            });
            $("#search_dokter_create").focus();
            return;
        }
        if (!poliIdVal) {
            Swal.fire({
                icon: "warning",
                title: "Pilih Poli",
                text: "Silakan pilih poli untuk dokter tersebut.",
            });
            $("#poli_select_create").focus();
            return;
        }

        const url = $formAdd.data("url");
        const formData = {
            dokter_id: dokterIdVal,
            poli_id: poliIdVal,
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
                    const errors = error.response.data.errors || {};
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
// $(function () {
//     const editModalEl = document.getElementById("editJadwalModal");
//     const editModal = editModalEl ? new Modal(editModalEl) : null;
//     const $formEdit = $("#formEditJadwalDokter");

//     // 🔁 Reset form edit setiap kali modal ditutup
//     function resetEditForm() {
//         $formEdit[0].reset();
//         $formEdit.find(".is-invalid").removeClass("is-invalid");
//         $formEdit.find(".text-red-600").empty();
//         $("#dokter_data_update").addClass("hidden");
//         $("#search_results_update").addClass("hidden");
//     }

//     // 🩺 Search Dokter (sama seperti create, tapi untuk modal edit)
//     const searchInputUpdate = document.getElementById("search_dokter_update");
//     const resultsDivUpdate = document.getElementById("search_results_update");
//     const dokterDataDivUpdate = document.getElementById("dokter_data_update");

//     if (searchInputUpdate) {
//         searchInputUpdate.addEventListener("keyup", async () => {
//             const query = searchInputUpdate.value.trim();
//             if (query.length < 2) {
//                 resultsDivUpdate.classList.add("hidden");
//                 return;
//             }

//             const response = await fetch(
//                 `/pengaturan_klinik/search?query=${query}`
//             );
//             const data = await response.json();

//             resultsDivUpdate.innerHTML = "";
//             if (data.length > 0) {
//                 resultsDivUpdate.classList.remove("hidden");
//                 data.forEach((dokter) => {
//                     const item = document.createElement("div");
//                     item.className =
//                         "px-4 py-2 hover:bg-indigo-100 cursor-pointer text-sm";
//                     item.textContent = `${dokter.nama_dokter} (${
//                         dokter.nama_poli || "-"
//                     })`;

//                     item.onclick = () => {
//                         $("#dokter_id_update").val(dokter.id);
//                         $("#poli_id_update").val(dokter.poli_id);
//                         $("#nama_dokter_update").text(dokter.nama_dokter);
//                         $("#nama_poli_update").text(dokter.nama_poli || "-");

//                         dokterDataDivUpdate.classList.remove("hidden");
//                         resultsDivUpdate.classList.add("hidden");
//                         searchInputUpdate.value = dokter.nama_dokter;
//                     };

//                     resultsDivUpdate.appendChild(item);
//                 });
//             } else {
//                 resultsDivUpdate.classList.remove("hidden");
//                 resultsDivUpdate.innerHTML = `<div class="px-4 py-2 text-gray-500 text-sm">Tidak ditemukan</div>`;
//             }
//         });
//     }

//     // ✏️ Klik tombol Edit
//     $("body").on("click", ".btn-edit-jadwal", function () {
//         resetEditForm();
//         const jadwalId = $(this).data("id");

//         axios
//             .get(`/pengaturan_klinik/get_jadwal_dokter_by_id/${jadwalId}`)
//             .then((response) => {
//                 const jadwal = response.data.data;

//                 // Set action URL
//                 const baseUrl = $formEdit
//                     .data("url")
//                     .replace("/0", "/" + jadwal.id);
//                 $formEdit.data("url", baseUrl);

//                 // Isi form dengan data dari backend
//                 $("#jadwal_id_update").val(jadwal.id);
//                 $("#dokter_id_update").val(jadwal.dokter.id);
//                 $("#poli_id_update").val(jadwal.poli.id);
//                 $("#hari_edit").val(jadwal.hari);
//                 $("#jam_awal_edit").val(jadwal.jam_awal);
//                 $("#jam_selesai_edit").val(jadwal.jam_selesai);

//                 // Tampilkan data dokter di modal edit
//                 $("#nama_dokter_update").text(jadwal.dokter.nama_dokter);
//                 $("#nama_poli_update").text(jadwal.poli.nama_poli);
//                 $("#search_dokter_update").val(jadwal.dokter.nama_dokter);
//                 $("#dokter_data_update").removeClass("hidden");

//                 editModal?.show();
//             })
//             .catch(() => {
//                 Swal.fire({
//                     icon: "error",
//                     title: "Gagal!",
//                     text: "Tidak dapat memuat data jadwal.",
//                 });
//             });
//     });

//     // 💾 Submit form edit
//     $formEdit.on("submit", function (e) {
//         e.preventDefault();
//         const url = $formEdit.data("url");

//         const formData = {
//             dokter_id: $("#dokter_id_update").val(),
//             poli_id: $("#poli_id_update").val(),
//             hari: $("#hari_edit").val(),
//             jam_awal: $("#jam_awal_edit").val(),
//             jam_selesai: $("#jam_selesai_edit").val(),
//             _method: "PUT",
//         };

//         axios
//             .post(url, formData)
//             .then((response) => {
//                 Swal.fire({
//                     icon: "success",
//                     title: "Berhasil!",
//                     text: response.data.message,
//                     timer: 2000,
//                     showConfirmButton: false,
//                 });
//                 editModal?.hide();
//                 $("#jadwalTable").DataTable().ajax.reload(null, false);
//             })
//             .catch((error) => {
//                 if (error.response?.status === 422) {
//                     const errors = error.response.data.errors;
//                     for (const field in errors) {
//                         $(`#${field}_edit`).addClass("is-invalid");
//                         $(`#${field}_edit-error`).html(errors[field][0]);
//                     }
//                     Swal.fire({
//                         icon: "error",
//                         title: "Validasi Gagal!",
//                         text: "Periksa kembali input Anda.",
//                     });
//                 } else {
//                     Swal.fire({
//                         icon: "error",
//                         title: "Error Server!",
//                         text: "Terjadi kesalahan server.",
//                     });
//                 }
//             });
//     });

//     // ❌ Tutup modal
//     $("#closeEditJadwalModal").on("click", function () {
//         editModal?.hide();
//         resetEditForm();
//     });
// });

// EDIT JADWAL DOKTER (dokter locked; hanya poli yang boleh diubah)
$(function () {
    const editModalEl = document.getElementById("editJadwalModal");
    const editModal = editModalEl ? new Modal(editModalEl) : null;
    const $formEdit = $("#formEditJadwalDokter");

    const $dokterId = $("#dokter_id_update");
    const $poliId = $("#poli_id_update");
    const $poliSelect = $("#poli_select_update");
    const $poliHelp = $("#poli_select_help_update");

    function resetEditForm() {
        $formEdit[0].reset();
        $formEdit.find(".is-invalid").removeClass("is-invalid");
        $formEdit.find(".text-red-600").empty();

        // kunci dokter (tetap kosong sampai data di-load)
        $("#search_dokter_update")
            .val("")
            .prop("readonly", true)
            .addClass("cursor-not-allowed select-none");
        $dokterId.val("");

        // reset poli
        $poliId.val("");
        $poliSelect
            .prop("disabled", true)
            .empty()
            .append(`<option value="">— pilih poli —</option>`);
        $poliHelp.text("Pilih poli untuk dokter ini.");
    }

    async function loadPoliByDokter(dokterId, selectedPoliId = null) {
        $poliSelect
            .prop("disabled", true)
            .empty()
            .append(`<option value="">Loading…</option>`);
        $poliHelp.text("Memuat daftar poli…");
        try {
            const res = await fetch(
                `/pengaturan_klinik/search-poli-by-dokter/${dokterId}`
            );
            const polis = await res.json();

            $poliSelect
                .empty()
                .append(`<option value="">— pilih poli —</option>`);
            if (!Array.isArray(polis) || polis.length === 0) {
                $poliSelect.prop("disabled", true);
                $poliHelp.text("Dokter ini belum memiliki poli terkait.");
            } else {
                polis.forEach((p) =>
                    $poliSelect.append(
                        `<option value="${p.id}">${p.nama_poli}</option>`
                    )
                );
                if (selectedPoliId) {
                    $poliSelect.val(String(selectedPoliId));
                    $poliId.val(String(selectedPoliId));
                }
                $poliSelect.prop("disabled", false);
                $poliHelp.text("Pilih salah satu poli di atas.");
            }
        } catch (e) {
            console.error(e);
            $poliSelect
                .prop("disabled", true)
                .empty()
                .append(`<option value="">Gagal memuat poli</option>`);
            $poliHelp.text("Terjadi kesalahan ketika memuat poli.");
        }
    }

    // buka modal edit
    $("body").on("click", ".btn-edit-jadwal", function () {
        resetEditForm();
        const jadwalId = $(this).data("id");

        axios
            .get(`/pengaturan_klinik/get_jadwal_dokter_by_id/${jadwalId}`)
            .then((res) => {
                const jd = res.data.data;

                // set action URL
                const urlTemplate = $formEdit.data("url-template");
                $formEdit.data("url", urlTemplate.replace("__ID__", jd.id));

                // set field jam/hari
                $("#jadwal_id_update").val(jd.id);
                $("#hari_edit").val(jd.hari);
                $("#jam_awal_edit").val(jd.jam_awal.substring(0, 5));
                $("#jam_selesai_edit").val(jd.jam_selesai.substring(0, 5));

                // set dokter (LOCKED)
                if (jd.dokter) {
                    $("#search_dokter_update").val(jd.dokter.nama_dokter);
                    $dokterId.val(jd.dokter.id);
                }

                // load poli sesuai dokter & pilih poli yg tersimpan
                const dokterId = jd.dokter ? jd.dokter.id : null;
                const poliId = jd.poli ? jd.poli.id : null;
                if (dokterId) loadPoliByDokter(dokterId, poliId);

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

    // pilih poli
    $poliSelect.on("change", function () {
        $poliId.val(this.value || "");
    });

    // submit PUT
    $formEdit.on("submit", function (e) {
        e.preventDefault();
        const url = $formEdit.data("url");

        const dokterIdVal = $("#dokter_id_update").val();
        const poliIdVal = $("#poli_id_update").val();
        if (!dokterIdVal) {
            Swal.fire({
                icon: "warning",
                title: "Dokter kosong",
                text: "Data dokter tidak valid.",
            });
            return;
        }
        if (!poliIdVal) {
            Swal.fire({
                icon: "warning",
                title: "Pilih Poli",
                text: "Silakan pilih poli.",
            });
            return;
        }

        const formData = {
            dokter_id: dokterIdVal, // tetap dikirim (server-side guard optional)
            poli_id: poliIdVal,
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
                    const errors = error.response.data.errors || {};
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
