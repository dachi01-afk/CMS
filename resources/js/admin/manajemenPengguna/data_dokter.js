import axios from "axios";
import { initFlowbite } from "flowbite";
import $ from "jquery";

// data tabel Dokter
$(function () {
    // Inisialisasi DataTable
    var table = $("#dokterTable").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "manajemen_pengguna/data_dokter",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            {
                data: "foto",
                name: "foto",
                orderable: false,
                searchable: false,
                className: "text-center",
            },
            { data: "nama_dokter", name: "nama_dokter" },
            { data: "username", name: "username" },
            { data: "email_user", name: "email_user" },
            { data: "role", name: "role" },
            { data: "nama_spesialis", name: "nama_spesialis" },
            { data: "nama_poli", name: "nama_poli" },
            { data: "no_hp", name: "no_hp" },
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

    // 🔎 Hubungkan search input Dokter
    $("#dokter_searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    const $info = $("#dokter_customInfo");
    const $pagination = $("#dokter_customPagination");
    const $perPage = $("#dokter_pageLength");

    // 🔁 Update Pagination Dinamis
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
});

// ADD DOKTER
$(function () {
    const addModalElement = document.getElementById("addDokterModal");
    const addModal = addModalElement ? new Modal(addModalElement) : null;
    const $formAdd = $("#formAddDokter");

    function resetAddForm() {
        $formAdd[0].reset();
        $formAdd.find(".is-invalid").removeClass("is-invalid");
        $formAdd.find(".text-red-600").empty();

        // reset preview foto
        $("#preview_foto_dokter").addClass("hidden").attr("src", "");
        $("#placeholder_foto_dokter").removeClass("hidden");
        $("#foto_drop_area")
            .removeClass("border-solid border-gray-300")
            .addClass("border-dashed border-gray-400");

        // reset pilihan tom-select
        if (tsPoli) {
            tsPoli.clear(); // kosongkan value
            tsPoli.clearOptions(); // kosongkan cache (opsional)
            // muat ulang opsi dari DOM <option> (opsional, biasanya tidak perlu)
            $("#poli_id option").each(function () {
                tsPoli.addOption({ value: this.value, text: this.text });
            });
        } else {
            $("#poli_id").val([]).trigger("change");
        }
    }

    $("#btnAddDokter").on("click", function () {
        initTomSelectPoli(); // pastikan TS siap saat modal dibuka
        resetAddForm();
        addModal?.show();
        // perbaiki lebar dropdown setelah animasi modal (kadang perlu)
        setTimeout(() => tsPoli && tsPoli.refreshOptions(false), 100);
    });

    $("#closeAddDokterModal").on("click", function () {
        addModal?.hide();
        resetAddForm();
    });

    $("#formAddDokter").on("submit", function (e) {
        e.preventDefault();
        const form = $(this);
        const url = form.data("url");
        const formData = new FormData(this);

        // bersihkan error lama
        form.find(".is-invalid").removeClass("is-invalid");
        form.find('[id$="-error"]').html("");

        axios
            .post(url, formData)
            .then((response) => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: response.data.message,
                    showConfirmButton: false,
                    timer: 1500,
                }).then(() => {
                    addModal?.hide();
                    $("#dokterTable").DataTable().ajax.reload(null, false);
                    resetAddForm();
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

                    Object.keys(errors).forEach((fieldKey) => {
                        const baseKey = fieldKey.split(".")[0]; // handle poli_id.0
                        const $input = $("#" + baseKey);
                        if ($input.length) $input.addClass("is-invalid");
                        const $error = $("#" + baseKey + "-error");
                        if ($error.length) $error.html(errors[fieldKey][0]);

                        // khusus Tom Select, tambahkan class ke wrapper juga biar terlihat
                        if (baseKey === "poli_id" && tsPoli) {
                            tsPoli.control_input.classList.add("is-invalid");
                        }
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error Server!",
                        text: "Terjadi kesalahan server. Silakan coba lagi.",
                    });
                }
            });
    });
});

// === INIT Tom Select untuk pilih Poli ===
let tsPoli = null;
function initTomSelectPoli() {
    if (tsPoli) return; // inisialisasi sekali
    tsPoli = new TomSelect("#poli_id", {
        plugins: ["remove_button"], // ada tombol × di chip
        persist: false,
        create: false, // TIDAK boleh buat poli baru
        maxOptions: 500, // batasi opsi per page
        placeholder: "Cari & pilih poli…",
        valueField: "value",
        labelField: "text",
        searchField: ["text"],
        render: {
            item: function (data, escape) {
                return `<div class="ts-chip inline-flex items-center gap-1 px-2 py-1 rounded bg-blue-50 border border-blue-200 text-blue-700 text-xs">
                  ${escape(data.text)}
                </div>`;
            },
            option: function (data, escape) {
                return `<div class="px-2 py-1 text-sm">${escape(
                    data.text
                )}</div>`;
            },
        },
        onChange: function () {
            // hapus error styling ketika ada perubahan
            $("#poli_id").removeClass("is-invalid");
            $("#poli_id-error").html("");
        },
    });
}

// === Tom Select untuk Edit ===
let tsEditPoli = null;
function initTomSelectEditPoli() {
    if (tsEditPoli) return;
    tsEditPoli = new TomSelect("#edit_poli_id", {
        plugins: ["remove_button"],
        persist: false,
        create: false,
        maxOptions: 500,
        placeholder: "Cari & pilih poli…",
        valueField: "value",
        labelField: "text",
        searchField: ["text"],
        render: {
            item: (
                d,
                e
            ) => `<div class="inline-flex items-center gap-1 px-2 py-1 rounded-full
                             bg-blue-50 border border-blue-200 text-blue-700 text-xs">${e(
                                 d.text
                             )}</div>`,
            option: (d, e) =>
                `<div class="px-2 py-1 text-sm">${e(d.text)}</div>`,
        },
        onChange() {
            $("#edit_poli_id").removeClass("is-invalid");
            $("#edit_poli_id-error").html("");
        },
    });
}

$(function () {
    const editModalElement = document.getElementById("editDokterModal");
    const editModal = editModalElement ? new Modal(editModalElement) : null;
    const $formEdit = $("#formEditDokter");
    const initialEditUrl = $formEdit.data("url");

    function resetEditForm() {
        $formEdit[0].reset();
        $formEdit.find(".is-invalid").removeClass("is-invalid");
        $formEdit.find(".text-red-600").empty();

        // reset action URL
        $formEdit.data("url", initialEditUrl);
        $formEdit.attr("action", initialEditUrl);

        // reset preview foto
        $("#preview_edit_foto_dokter").addClass("hidden").attr("src", "");
        $("#placeholder_edit_foto_dokter").removeClass("hidden");
        $("#foto_drop_area_edit")
            .removeClass("border-solid border-gray-300")
            .addClass("border-dashed border-gray-400");

        // reset tom select
        if (tsEditPoli) tsEditPoli.clear();
    }

    // buka modal edit
    $("body").on("click", ".btn-edit-dokter", function () {
        resetEditForm();
        initTomSelectEditPoli();

        const dokterId = $(this).data("id");

        axios
            .get(`/manajemen_pengguna/get_dokter_by_id/${dokterId}`)
            .then((res) => {
                const dokter = res.data.data;

                // set action URL (ganti /0 → /{id})
                const baseUrl = $formEdit.data("url");
                const finalUrl = baseUrl.replace("/0", "/" + dokter.id);
                $formEdit.data("url", finalUrl);
                $formEdit.attr("action", finalUrl);

                // isi field
                $("#edit_dokter_id").val(dokter.id);
                $("#edit_username_dokter").val(dokter.user?.username ?? "");
                $("#edit_nama_dokter").val(dokter.nama_dokter ?? "");
                $("#edit_email_akun_dokter").val(dokter.user?.email ?? "");
                $("#edit_spesialis_dokter").val(
                    dokter.jenis_spesialis_id ?? ""
                );
                $("#edit_no_hp_dokter").val(dokter.no_hp ?? "");
                $("#edit_deskripsi_dokter").val(dokter.deskripsi_dokter ?? "");
                $("#edit_pengalaman_dokter").val(dokter.pengalaman ?? "");

                // preselect poli (array id)
                const poliId = (dokter.poli || []).map((p) => p.id);
                if (tsEditPoli) {
                    tsEditPoli.clear(); // clear value dulu
                    tsEditPoli.setValue(poliId); // set nilai terpilih
                } else {
                    $("#edit_poli_id").val(poliId).trigger("change");
                }

                // tampilkan foto existing kalau ada
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

                editModal?.show();
                // refresh options width setelah modal tampil
                setTimeout(
                    () => tsEditPoli && tsEditPoli.refreshOptions(false),
                    100
                );
            })
            .catch(() => {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: "Tidak dapat memuat data dokter.",
                });
            });
    });

    // submit update
    $formEdit.on("submit", function (e) {
        e.preventDefault();
        const url = $formEdit.data("url");
        const formData = new FormData($formEdit[0]);

        // bersihkan error lama
        $formEdit.find(".is-invalid").removeClass("is-invalid");
        $formEdit.find('[id$="-error"]').html("");

        axios
            .post(url, formData)
            .then((res) => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: res.data.success ?? res.data.message ?? "Tersimpan.",
                    showConfirmButton: false,
                    timer: 1600,
                }).then(() => {
                    editModal?.hide();
                    $("#dokterTable").DataTable().ajax.reload(null, false);
                    resetEditForm();
                });
            })
            .catch((error) => {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;

                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal!",
                        text: "Silakan cek kembali form Anda.",
                    });

                    // map error termasuk array: poli_id.0 → edit_poli_id
                    Object.keys(errors).forEach((fieldKey) => {
                        const baseKey = fieldKey.split(".")[0];

                        const $input = $("#" + baseKey);
                        if ($input.length) $input.addClass("is-invalid");

                        const $error = $("#" + baseKey + "-error");
                        if ($error.length) $error.html(errors[fieldKey][0]);

                        if (
                            baseKey === "edit_poli_id" ||
                            baseKey === "poli_id"
                        ) {
                            // kalau validator mengembalikan 'poli_id', arahkan ke edit_poli_id
                            $("#edit_poli_id").addClass("is-invalid");
                            $("#edit_poli_id-error").html(errors[fieldKey][0]);
                            if (tsEditPoli)
                                tsEditPoli.wrapper.classList.add("is-invalid");
                        }
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error Server!",
                        text: "Terjadi kesalahan. Coba lagi.",
                    });
                }
            });
    });

    $("#closeEditDokterModal").on("click", function () {
        editModal?.hide();
        resetEditForm();
    });
});

// delete data dokter
$(function () {
    $("body").on("click", ".btn-delete-dokter", function () {
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
                    .delete(`/manajemen_pengguna/delete_dokter/${dokterId}`)
                    .then((response) => {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: response.data.success,
                            showConfirmButton: false,
                            timer: 1500,
                        }).then(() => {
                            if ($("#dokterTable").length) {
                                $("#dokterTable")
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

// pasfoto
document.addEventListener("DOMContentLoaded", function () {
    const fileInput = document.getElementById("foto_dokter");
    const previewImg = document.getElementById("preview_foto_dokter");
    const placeholder = document.getElementById("placeholder_foto_dokter");
    const dropArea = document.getElementById("foto_drop_area");
    const modalElement = document.getElementById("addDokterModal");
    const closeButton = document.getElementById("closeAddDokterModal");

    // Tampilkan preview foto saat upload
    fileInput.addEventListener("change", function (e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (event) {
                previewImg.src = event.target.result;
                previewImg.classList.remove("hidden");
                placeholder.classList.add("hidden");
                dropArea.classList.remove("border-dashed", "border-gray-400");
                dropArea.classList.add("border-solid", "border-gray-300");
            };
            reader.readAsDataURL(file);
        } else {
            resetFotoPreview();
        }
    });

    // Fungsi reset foto
    function resetFotoPreview() {
        fileInput.value = "";
        previewImg.src = "";
        previewImg.classList.add("hidden");
        placeholder.classList.remove("hidden");
        dropArea.classList.add("border-dashed", "border-gray-400");
        dropArea.classList.remove("border-solid", "border-gray-300");
    }

    // Reset foto ketika modal ditutup (klik tombol close)
    closeButton.addEventListener("click", function () {
        modalElement.classList.add("hidden"); // sembunyikan modal
        resetFotoPreview();
        document.getElementById("formAddDokter").reset(); // reset seluruh form juga
    });

    // Reset ketika klik di luar modal (backdrop)
    modalElement.addEventListener("click", function (e) {
        // jika klik di luar konten (div bg putih)
        if (e.target === modalElement) {
            modalElement.classList.add("hidden");
            resetFotoPreview();
            formAdd.reset();
        }
    });
});

// edit foto
document.addEventListener("DOMContentLoaded", function () {
    const fileInput = document.getElementById("edit_foto_dokter");
    const previewImg = document.getElementById("preview_edit_foto_dokter");
    const placeholder = document.getElementById("placeholder_edit_foto_dokter");
    const dropArea = document.getElementById("foto_drop_area_edit");
    const closeButton = document.getElementById("closeEditDokterModal");
    const formEdit = document.getElementById("formEditDokter");

    function resetFotoPreview() {
        if (fileInput) fileInput.value = "";
        if (previewImg) {
            previewImg.src = "";
            previewImg.classList.add("hidden");
        }
        if (placeholder) placeholder.classList.remove("hidden");
        if (dropArea) {
            dropArea.classList.add("border-dashed", "border-gray-400");
            dropArea.classList.remove("border-solid", "border-gray-300");
        }
    }

    // Saat user pilih file baru
    if (fileInput) {
        fileInput.addEventListener("change", function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    previewImg.src = event.target.result;
                    previewImg.classList.remove("hidden");
                    placeholder.classList.add("hidden");
                    dropArea.classList.remove(
                        "border-dashed",
                        "border-gray-400"
                    );
                    dropArea.classList.add("border-solid", "border-gray-300");
                };
                reader.readAsDataURL(file);
            }
            // ❌ jangan reset kalau batal pilih file (biar foto lama tetap tampil)
        });
    }

    // Tutup modal via tombol X
    closeButton?.addEventListener("click", function () {
        resetFotoPreview();
        formEdit.reset();
    });

    // Tutup modal via backdrop klik (optional)
    const modalElement = document.getElementById("editDokterModal");
    modalElement?.addEventListener("click", function (e) {
        if (e.target === modalElement) {
            modalElement.classList.add("hidden");
            resetFotoPreview();
            formEdit.reset();
        }
    });
});
