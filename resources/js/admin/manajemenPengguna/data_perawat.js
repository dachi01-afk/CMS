import axios from "axios";
import { initFlowbite } from "flowbite";
import $, { ajax, data } from "jquery";

function showSwalSuccess(pesan) {
    return Swal.fire({
        icon: "success",
        title: "Berhasil",
        text: pesan,
        confirmButtonText: "OK",
        confirmButtonColor: "#14b8a6",
    });
}

function showSwalError(pesan) {
    return Swal.fire({
        icon: "error",
        title: "Oops...",
        text: pesan,
        confirmButtonText: "Tutup",
        confirmButtonColor: "#ef4444",
    });
}

function showSwalWarning(message) {
    return Swal.fire({
        icon: "warning",
        title: "Peringatan",
        text: message,
        confirmButtonText: "OK",
        confirmButtonColor: "#f59e0b",
    });
}

// ================== DATA TABLE PERAWAT ==================
$(function () {
    var table = $("#table-perawat").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/management-pengguna/perawat/get-data",
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
                className: "text-center align-middle",
            },
            { data: "nama", name: "nama" },
            { data: "username", name: "username" },
            { data: "email", name: "email" },
            { data: "role", name: "role" },
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
                "bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600",
            );
            $("td", row).addClass("px-6 py-4 text-gray-900 dark:text-white");
        },
    });

    // 🔎 Search
    $("#perawat_searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    const $info = $("#perawat_customInfo");
    const $pagination = $("#perawat_customPagination");
    const $perPage = $("#perawat_pageLength");

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

        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(
            `<li><a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a></li>`,
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
                `<li><a href="#" class="page-number flex items-center justify-center px-3 h-8 border ${active}" data-page="${i}">${i}</a></li>`,
            );
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(
            `<li><a href="#" id="btnNext" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">Next</a></li>`,
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

// ================== ADD PERAWAT: multi poli & dokter (pivot dokter_poli) ==================
$(function () {
    const addModalElement = document.getElementById("addPerawatModal");
    const addModal = addModalElement
        ? new Modal(addModalElement, {
              backdrop: "static",
              closable: false,
          })
        : null;
    const $formAdd = $("#formAddPerawat");

    let tsAddPoli = null;
    let tsAddDokter = null;

    function destroyTS(ts) {
        try {
            ts && ts.destroy();
        } catch (_) {}
    }

    function escapeHtml(str) {
        if (!str) return "";
        return str
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function showDokterGroup() {
        $("#group_dokter_add").removeClass("hidden");
    }

    function hideDokterGroup() {
        $("#group_dokter_add").addClass("hidden");
        destroyTS(tsAddDokter);
        tsAddDokter = null;
        $("#add_dokter_select").html("");
    }

    function resetPenugasanList() {
        $("#penugasan_list_add").empty();
        $("#dokter_poli_id-error").html("");
    }

    function resetAddForm() {
        $formAdd[0].reset();
        $formAdd.find(".is-invalid").removeClass("is-invalid");
        $formAdd.find(".text-red-600").empty();

        // reset preview foto
        $("#preview_foto_perawat").addClass("hidden").attr("src", "");
        $("#placeholder_foto_perawat").removeClass("hidden");
        $("#foto_drop_area_perawat")
            .removeClass("border-solid border-gray-300")
            .addClass("border-dashed border-gray-400");

        // reset tomselect
        destroyTS(tsAddPoli);
        tsAddPoli = null;
        hideDokterGroup();
        $("#add_poli_select").html("");

        // reset list penugasan
        resetPenugasanList();
    }

    // ---------- TomSelect POLI ----------
    function initTSAddPoli() {
        destroyTS(tsAddPoli);
        $("#add_poli_select").html("");

        tsAddPoli = new TomSelect("#add_poli_select", {
            create: false,
            maxItems: 1,
            valueField: "id",
            labelField: "nama",
            searchField: "nama",
            placeholder: "Cari & pilih poli…",
            preload: false,
            render: {
                option: (it) => `<div class="py-1 px-2">${it.nama}</div>`,
                item: (it) => `<div>${it.nama}</div>`,
            },
            load: function (q, cb) {
                axios
                    .get("/manajemen_pengguna/list_poli", {
                        params: { q: q || "" },
                    })
                    .then(({ data }) => {
                        const arr = Array.isArray(data?.data) ? data.data : [];
                        cb(
                            arr.map((p) => ({
                                id: p.id,
                                nama: p.nama_poli || p.nama || `Poli #${p.id}`,
                            })),
                        );
                    })
                    .catch(() => cb());
            },
            onChange: (val) => {
                if (val) {
                    initTSAddDokter(val);
                } else {
                    hideDokterGroup();
                }
            },
        });
    }

    // ---------- TomSelect DOKTER (depend on poli, value = dokter_poli_id) ----------
    function initTSAddDokter(poliId) {
        destroyTS(tsAddDokter);
        $("#add_dokter_select").html("");

        if (!poliId) {
            hideDokterGroup();
            return;
        }

        tsAddDokter = new TomSelect("#add_dokter_select", {
            create: false,
            maxItems: 1,
            valueField: "id", // dokter_poli_id
            labelField: "nama",
            searchField: "nama",
            placeholder: "Cari & pilih dokter…",
            preload: "focus",
            shouldLoad: () => true,
            render: {
                option: (it) => `<div class="py-1 px-2">${it.nama}</div>`,
                item: (it) => `<div>${it.nama}</div>`,
            },
            load: function (q, cb) {
                axios
                    .get(`/manajemen_pengguna/poli/${poliId}/dokter`, {
                        params: { q: q || "" },
                    })
                    .then(({ data }) => {
                        const arr = Array.isArray(data?.data) ? data.data : [];
                        cb(
                            arr.map((d) => ({
                                id: d.dokter_poli_id, // <<=== penting
                                nama: d.nama_dokter || `Dokter #${d.dokter_id}`,
                            })),
                        );
                    })
                    .catch(() => cb());
            },
            onFocus() {
                if (this.options_count === 0) this.load("");
            },
        });

        showDokterGroup();
    }

    // ---------- Tambahkan penugasan ke list ----------
    $("#btnAddPenugasan").on("click", function () {
        const poliId = tsAddPoli ? tsAddPoli.getValue() : null;
        const dokterPoliId = tsAddDokter ? tsAddDokter.getValue() : null;

        const poliText = tsAddPoli
            ? (tsAddPoli.getItem(poliId)?.textContent || "").trim()
            : "";
        const dokterText = tsAddDokter
            ? (tsAddDokter.getItem(dokterPoliId)?.textContent || "").trim()
            : "";

        $("#add_poli_select-error").html("");
        $("#add_dokter_select-error").html("");
        $("#dokter_poli_id-error").html("");

        if (!poliId) {
            $("#add_poli_select-error").html(
                "Silakan pilih poli terlebih dahulu.",
            );
            return;
        }
        if (!dokterPoliId) {
            $("#add_dokter_select-error").html(
                "Silakan pilih dokter terlebih dahulu.",
            );
            return;
        }

        // Cek duplikat dokter_poli_id
        const exists = $(
            `#penugasan_list_add tr[data-dokter-poli-id="${dokterPoliId}"]`,
        ).length;
        if (exists) {
            $("#dokter_poli_id-error").html(
                "Kombinasi poli & dokter ini sudah ditambahkan.",
            );
            return;
        }

        // Tambah row ke list
        const rowHtml = `
        <tr data-dokter-poli-id="${dokterPoliId}" class="border-t border-slate-100 dark:border-slate-700">
            <td class="px-3 py-2 text-xs text-slate-700 dark:text-slate-100 align-middle">
                ${escapeHtml(poliText)}
            </td>
            <td class="px-3 py-2 text-xs text-slate-700 dark:text-slate-100 align-middle">
                ${escapeHtml(dokterText)}
            </td>
            <td class="px-3 py-2 text-center align-middle">
                <button type="button"
                    class="btn-remove-penugasan inline-flex items-center justify-center h-7 w-7 rounded-full
                           bg-red-50 text-red-500 hover:bg-red-100 dark:bg-red-900/30 dark:text-red-300"
                    title="Hapus penugasan">
                    <i class="fa-solid fa-xmark text-[11px]"></i>
                </button>
                <input type="hidden" name="dokter_poli_id[]" value="${dokterPoliId}">
            </td>
        </tr>
    `;

        $("#penugasan_list_add").append(rowHtml);

        // reset dokter saja
        if (tsAddDokter) {
            tsAddDokter.clear();
            tsAddDokter.clearOptions();
        }
    });

    // Hapus penugasan
    $(document).on("click", ".btn-remove-penugasan", function () {
        $(this).closest("tr").remove();
    });

    // ---------- Buka & tutup modal ----------
    $("#btnAddPerawat").on("click", function () {
        resetAddForm();
        initTSAddPoli();
        addModal && addModal.show();
    });

    $("#closeAddPerawatModal, #closeAddPerawatModal_header").on(
        "click",
        function () {
            resetAddForm();
            addModal && addModal.hide();
        },
    );

    // ---------- Preview foto (tetap seperti sebelumnya) ----------
    $("#foto_perawat").on("change", function () {
        const file = this.files?.[0];
        if (!file) {
            $("#preview_foto_perawat").addClass("hidden").attr("src", "");
            $("#placeholder_foto_perawat").removeClass("hidden");
            $("#foto_drop_area_perawat")
                .removeClass("border-solid border-gray-300")
                .addClass("border-dashed border-gray-400");
            return;
        }
        if (!file.type.startsWith("image/")) {
            $("#foto_perawat-error").text(
                "File harus berupa gambar (JPG/PNG/WebP).",
            );
            this.value = "";
            return;
        }
        const reader = new FileReader();
        reader.onload = (e) => {
            $("#preview_foto_perawat")
                .attr("src", e.target.result)
                .removeClass("hidden");
            $("#placeholder_foto_perawat").addClass("hidden");
            $("#foto_drop_area_perawat")
                .removeClass("border-dashed border-gray-400")
                .addClass("border-solid border-gray-300");
            $("#foto_perawat-error").text("");
        };
        reader.readAsDataURL(file);
    });

    // drag & drop
    const $drop = $("#foto_drop_area_perawat");
    $drop.on("dragover", function (e) {
        e.preventDefault();
        $(this).addClass("ring-2 ring-blue-400");
    });
    $drop.on("dragleave dragend drop", function (e) {
        e.preventDefault();
        $(this).removeClass("ring-2 ring-blue-400");
    });
    $drop.on("drop", function (e) {
        const dt = e.originalEvent.dataTransfer;
        if (dt && dt.files && dt.files[0]) {
            $("#foto_perawat")[0].files = dt.files;
            $("#foto_perawat").trigger("change");
        }
    });

    // ---------- Submit form ----------
    $formAdd.on("submit", function (e) {
        e.preventDefault();
        const url = $formAdd.data("url");
        const formData = new FormData($formAdd[0]);

        $(".text-red-600").empty();
        $formAdd.find(".is-invalid").removeClass("is-invalid");

        axios
            .post(url, formData, {
                headers: { "Content-Type": "multipart/form-data" },
            })
            .then((resp) => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: resp.data.message,
                    timer: 1800,
                    showConfirmButton: false,
                }).then(() => {
                    if ($("#table-perawat").length) {
                        addModal && addModal.hide();
                        $("#table-perawat")
                            .DataTable()
                            .ajax.reload(null, false);
                        resetAddForm();
                    } else {
                        window.location.reload();
                    }
                });
            })
            .catch((error) => {
                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors || {};
                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal!",
                        text: "Silakan periksa kembali isian Anda.",
                    });
                    Object.keys(errors).forEach((k) => {
                        const base = k.split(".")[0];
                        const $inp = $("#" + base);
                        if ($inp.length) $inp.addClass("is-invalid");
                        const $err = $("#" + base + "-error");
                        if ($err.length) $err.html(errors[k][0]);

                        if (base.startsWith("dokter_poli_id")) {
                            $("#dokter_poli_id-error").html(errors[k][0]);
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

// Detail Perawat
$(function () {
    let activeModalCount = 0;
    let scrollTopBeforeModal = 0;

    function lockBodyScroll() {
        if (activeModalCount === 0) {
            scrollTopBeforeModal = window.scrollY || window.pageYOffset;

            $("body").css({
                overflow: "hidden",
                position: "fixed",
                top: `-${scrollTopBeforeModal}px`,
                left: "0",
                right: "0",
                width: "100%",
            });
        }

        activeModalCount++;
    }

    function unlockBodyScroll() {
        activeModalCount = Math.max(0, activeModalCount - 1);

        if (activeModalCount === 0) {
            $("body").css({
                overflow: "",
                position: "",
                top: "",
                left: "",
                right: "",
                width: "",
            });

            window.scrollTo(0, scrollTopBeforeModal);
        }
    }

    $(document).on("click", ".btn-detail", function () {
        const url = $(this).data("url");

        $.ajax({
            url: url,
            type: "GET",
            beforeSend: function () {
                lockBodyScroll();
                $("#modal-detail-perawat")
                    .removeClass("hidden")
                    .addClass("flex");

                $("#detail-username-perawat").text("Loading ...");
                $("#detail-nama-perawat").text("Loading ...");
                $("#detail-email-perawat").text("Loading ...");
                $("#detail-no-hp-perawat").text("Loading ...");
                $("#detail-role-perawat").text("Loading ...");

                // reset foto
                $("#detail-preview-foto-perawat").attr("src", "").hide();

                $("#detail-placeholder-foto-perawat").show();

                const tbody = $("#detail-list-penugasan");
                tbody.empty();

                tbody.append(`
        <tr>
            <td colspan="3" class="px-3 py-4 text-center text-slate-400 italic">
                Loading data penugasan...
            </td>
        </tr>
    `);
            },

            success: function (response) {
                const dataAkunPerawat = response.dataAkunPerawat;
                const dataPerawat = response.dataPerawat;
                const dataPenugasan = response.dataPenugasan;

                $("#detail-username-perawat").text(dataAkunPerawat.username);
                $("#detail-nama-perawat").text(dataPerawat.nama_perawat);
                $("#detail-email-perawat").text(dataAkunPerawat.email);
                $("#detail-no-hp-perawat").text(dataPerawat.no_hp_perawat);
                $("#detail-role-perawat").text(dataAkunPerawat.role);

                // foto
                const fotoPerawat = dataPerawat.foto_perawat;

                if (fotoPerawat) {
                    const fotoUrl = `/storage/${fotoPerawat}`;

                    $("#detail-preview-foto-perawat")
                        .attr("src", fotoUrl)
                        .show();

                    $("#detail-placeholder-foto-perawat").hide();
                } else {
                    $("#detail-preview-foto-perawat").attr("src", "").hide();

                    $("#detail-placeholder-foto-perawat").show();
                }

                const tbody = $("#detail-list-penugasan");
                tbody.empty();

                if (dataPenugasan && dataPenugasan.length > 0) {
                    dataPenugasan.forEach((item, index) => {
                        tbody.append(`
                <tr>
                    <td class="px-3 py-2">${index + 1}</td>
                    <td class="px-3 py-2">${item.poli?.nama_poli ?? "-"}</td>
                    <td class="px-3 py-2">${item.dokter?.nama_dokter ?? "-"}</td>
                </tr>
            `);
                    });
                } else {
                    tbody.append(`
            <tr>
                <td colspan="3" class="px-3 py-4 text-center text-slate-400 italic">
                    Belum ada data penugasan
                </td>
            </tr>
        `);
                }
            },
        });
    });

    // Tombol Close Modal Detail
    $("#btn-close-header-modal-detail-perawat").on("click", function () {
        $("#modal-detail-perawat").removeClass("flex").addClass("hidden");
        unlockBodyScroll();
    });

    $("#btn-close-footer-modal-detail-perawat").on("click", function () {
        $("#modal-detail-perawat").removeClass("flex").addClass("hidden");
        unlockBodyScroll();
    });
});

// Edit Perawat
$(function () {
    let editPoliTom = null;
    let editDokterTom = null;
    let editPenugasanList = [];

    let activeModalCount = 0;
    let scrollTopBeforeModal = 0;

    function lockBodyScroll() {
        if (activeModalCount === 0) {
            scrollTopBeforeModal = window.scrollY || window.pageYOffset;

            $("body").css({
                overflow: "hidden",
                position: "fixed",
                top: `-${scrollTopBeforeModal}px`,
                left: "0",
                right: "0",
                width: "100%",
            });
        }

        activeModalCount++;
    }

    function unlockBodyScroll() {
        activeModalCount = Math.max(0, activeModalCount - 1);

        if (activeModalCount === 0) {
            $("body").css({
                overflow: "",
                position: "",
                top: "",
                left: "",
                right: "",
                width: "",
            });

            window.scrollTo(0, scrollTopBeforeModal);
        }
    }

    const urlGetDataPoli = "/management-pengguna/get-data-poli";

    function setTomPlaceholder(tom, text) {
        tom.settings.placeholder = text;
        tom.control_input.setAttribute("placeholder", text);
        tom.inputState();
    }

    function loadDataPoliEdit(selectedPoliSlug = null) {
        if (!editPoliTom) return;

        editPoliTom.clear(true);
        editPoliTom.clearOptions();
        setTomPlaceholder(editPoliTom, "Loading ...");
        editPoliTom.disable();

        $.ajax({
            url: urlGetDataPoli,
            type: "GET",
            dataType: "json",
            success: function (response) {
                const dataPoli = response.dataPoli;

                const option = dataPoli.map(function (data) {
                    return {
                        id: data.id,
                        nama: data.nama_poli,
                        slug: data.slug,
                    };
                });

                editPoliTom.clearOptions();
                editPoliTom.addOptions(option);
                editPoliTom.refreshOptions(false);

                setTomPlaceholder(editPoliTom, "Cari & Pilih Poli ...");
                editPoliTom.enable();

                if (selectedPoliSlug) {
                    editPoliTom.setValue(String(selectedPoliSlug), true);
                }
            },
            error: function () {
                editPoliTom.clearOptions();
                setTomPlaceholder(editPoliTom, "Gagal memuat data poli");
                editPoliTom.disable();
            },
        });
    }

    const urlGetDataDokterPoli =
        "/management-pengguna/perawat/get-data-dokter-poli";

    function loadDataDokterPoliEdit(poliSlug) {
        if (!editDokterTom) return;

        editDokterTom.clear(true);
        editDokterTom.clearOptions();
        setTomPlaceholder(editDokterTom, "Loading ...");
        editDokterTom.disable();

        if (!poliSlug) {
            $("#group_dokter_edit").addClass("hidden");
            setTomPlaceholder(editDokterTom, "Pilih poli dulu");
            return;
        }

        $.ajax({
            url: `${urlGetDataDokterPoli}/${encodeURIComponent(poliSlug)}`,
            type: "GET",
            dataType: "json",
            success: function (response) {
                const dataDokterPoli = response.dataDokterPoli;

                const option = dataDokterPoli.map(function (data) {
                    return {
                        id: data.id,
                        nama_dokter: data.dokter.nama_dokter,
                        nama_poli: data.poli.nama_poli,
                    };
                });

                editDokterTom.clearOptions();
                editDokterTom.addOptions(option);
                editDokterTom.refreshOptions(false);

                setTomPlaceholder(editDokterTom, "Cari & Pilih Dokter ...");
                editDokterTom.enable();

                if (option.length > 0) {
                    $("#group_dokter_edit").removeClass("hidden");
                } else {
                    $("#group_dokter_edit").addClass("hidden");
                }
            },
            error: function () {
                editDokterTom.clearOptions();
                setTomPlaceholder(editDokterTom, "Gagal memuat data dokter");
                editDokterTom.disable();
                $("#group_dokter_edit").addClass("hidden");
            },
        });
    }

    function escapeHtml(text) {
        return String(text ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function renderEditPenugasanTable() {
        const $tbody = $("#penugasan_list_edit");
        $tbody.empty();

        if (editPenugasanList.length === 0) {
            $tbody.append(`
            <tr>
                <td colspan="3" class="px-3 py-3 text-center text-slate-400 italic">
                    Belum ada penugasan ditambahkan
                </td>
            </tr>
        `);
            return;
        }

        editPenugasanList.forEach(function (item, index) {
            $tbody.append(`
            <tr class="border-b border-slate-100 dark:border-slate-700">
                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">
                    ${escapeHtml(item.poli_nama)}
                </td>
                <td class="px-3 py-2 text-slate-700 dark:text-slate-200">
                    ${escapeHtml(item.dokter_nama)}
                    <input type="hidden" name="dokter_poli_id[]" value="${escapeHtml(item.dokter_poli_id)}">
                </td>
                <td class="px-3 py-2 text-center">
                    <button
                        type="button"
                        class="btn-remove-edit-penugasan inline-flex items-center justify-center p-2 text-white bg-red-500 rounded-lg hover:bg-red-600 transition"
                        data-index="${index}"
                        title="Hapus">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            </tr>
        `);
        });
    }

    $(document).on("click", ".btn-edit", function () {
        const url = $(this).data("url");
        const urlUpdateDataPerawat = $(this).data("url-update-data-perawat");

        $("#formEditPerawat").attr("action", urlUpdateDataPerawat);

        $.ajax({
            url: url,
            type: "GET",
            beforeSend: function () {
                lockBodyScroll();
                $("#modal-edit-perawat").removeClass("hidden").addClass("flex");

                editPenugasanList = [];
                renderEditPenugasanTable();

                if (!editPoliTom) {
                    editPoliTom = new TomSelect("#edit-poli-select", {
                        valueField: "slug",
                        labelField: "nama",
                        searchField: "nama",
                        options: [],
                        create: false,
                        persist: false,
                        placeholder: "Cari & pilih poli...",
                    });
                    editPoliTom.on("change", function (value) {
                        loadDataDokterPoliEdit(value);
                    });
                }

                if (!editDokterTom) {
                    editDokterTom = new TomSelect("#edit-dokter-select", {
                        valueField: "id",
                        labelField: "nama_dokter",
                        searchField: "nama_dokter",
                        options: [],
                        create: false,
                        persist: false,
                        placeholder: "Cari & pilih dokter...",
                    });
                }

                $("#edit-username-perawat").val("Loading ...");
                $("#edit-nama-perawat").val("Loading ...");
                $("#edit-email-perawat").val("Loading ...");
                $("#edit-no-hp-perawat").val("Loading ...");

                // reset foto
                $("#edit-preview-foto-perawat").attr("src", "").hide();

                $("#edit-placeholder-foto-perawat").show();

                setTomPlaceholder(editPoliTom, "Loading ...");
                editPoliTom.clear(true);
                editPoliTom.clearOptions();
                editPoliTom.disable();

                setTomPlaceholder(editDokterTom, "Loading ...");
                editDokterTom.clear(true);
                editDokterTom.clearOptions();
                editDokterTom.disable();
            },
            success: function (response) {
                const dataPerawat = response.dataPerawat;
                const dataAkunPerawat = response.dataAkunPerawat;
                const dataPenugasan = response.dataPenugasan;

                // foto
                const fotoPerawat = dataPerawat.foto_perawat;

                if (fotoPerawat) {
                    setEditFotoPreview(`/storage/${fotoPerawat}`);
                } else {
                    setEditFotoPreview("");
                }

                $("#edit-username-perawat").val(dataAkunPerawat.username);
                $("#edit-nama-perawat").val(dataPerawat.nama_perawat);
                $("#edit-email-perawat").val(dataAkunPerawat.email);
                $("#edit-no-hp-perawat").val(dataPerawat.no_hp_perawat);

                editPenugasanList = dataPenugasan.map(function (item) {
                    return {
                        dokter_poli_id: String(item.id),
                        poli_slug: item.poli ? (item.poli.slug ?? "") : "",
                        poli_nama: item.poli ? item.poli.nama_poli : "-",
                        dokter_nama: item.dokter
                            ? item.dokter.nama_dokter
                            : "-",
                    };
                });

                renderEditPenugasanTable();

                loadDataPoliEdit();
            },
            error: function () {
                showSwalError("Gagal megambil detail perawat");
            },
        });
    });

    $(document).on("click", "#btnEditAddPenugasan", function () {
        const poliSlug = editPoliTom ? editPoliTom.getValue() : "";
        const dokterPoliId = editDokterTom ? editDokterTom.getValue() : "";

        $("#edit-poli-select-error").text("");
        $("#edit-dokter-select-error").text("");
        $("#edit_dokter_poli_id-error").text("");

        if (!poliSlug) {
            $("#edit-poli-select-error").text("Poli wajib dipilih.");
            return;
        }

        if (!dokterPoliId) {
            $("#edit-dokter-select-error").text("Dokter wajib dipilih.");
            return;
        }

        const poliOption = editPoliTom.options[poliSlug];
        const dokterOption = editDokterTom.options[dokterPoliId];

        const newItem = {
            dokter_poli_id: String(dokterPoliId),
            poli_slug: String(poliSlug),
            poli_nama: poliOption ? poliOption.nama : "-",
            dokter_nama: dokterOption ? dokterOption.nama_dokter : "-",
        };

        const isDuplicate = editPenugasanList.some(function (item) {
            return (
                String(item.dokter_poli_id) === String(newItem.dokter_poli_id)
            );
        });

        if (isDuplicate) {
            $("#edit_dokter_poli_id-error").text(
                "Penugasan ini sudah ada di daftar.",
            );
            return;
        }

        editPenugasanList.push(newItem);
        renderEditPenugasanTable();

        // reset poli ke mode awal
        editPoliTom.clear(true);
        setTomPlaceholder(editPoliTom, "Cari & Pilih Poli ...");

        // reset dokter ke mode awal
        editDokterTom.clear(true);
        editDokterTom.clearOptions();
        editDokterTom.disable();
        setTomPlaceholder(editDokterTom, "Pilih poli dulu");
        $("#group_dokter_edit").addClass("hidden");
    });

    $(document).on("change", "#edit_foto_perawat", function (e) {
        const file = e.target.files[0];

        $("#edit_foto_perawat-error").text("");

        if (!file) {
            return;
        }

        const allowedTypes = [
            "image/jpeg",
            "image/jpg",
            "image/png",
            "image/webp",
        ];
        const maxSize = 2 * 1024 * 1024; // 2MB

        if (!allowedTypes.includes(file.type)) {
            $("#edit_foto_perawat-error").text(
                "Format foto harus JPG, JPEG, PNG, atau WEBP.",
            );
            $(this).val("");
            return;
        }

        if (file.size > maxSize) {
            $("#edit_foto_perawat-error").text("Ukuran foto maksimal 2MB.");
            $(this).val("");
            return;
        }

        const reader = new FileReader();

        reader.onload = function (event) {
            setEditFotoPreview(event.target.result);
        };

        reader.readAsDataURL(file);
    });

    function setEditFotoPreview(src = "") {
        if (src) {
            $("#edit-preview-foto-perawat").attr("src", src).show();
            $("#edit-placeholder-foto-perawat").hide();
        } else {
            $("#edit-preview-foto-perawat").attr("src", "").hide();
            $("#edit-placeholder-foto-perawat").show();
        }
    }

    $(document).on("click", ".btn-remove-edit-penugasan", function () {
        const index = Number($(this).data("index"));

        if (Number.isNaN(index)) return;

        editPenugasanList.splice(index, 1);
        renderEditPenugasanTable();
    });

    $("#btn-close-header-modal-edit-perawat").on("click", function () {
        $("#modal-edit-perawat").removeClass("flex").addClass("hidden");
        unlockBodyScroll();
    });

    $("#btn-close-footer-modal-edit-perawat").on("click", function () {
        $("#modal-edit-perawat").removeClass("flex").addClass("hidden");
        unlockBodyScroll();
    });

    $("#formEditPerawat").on("submit", function (e) {
        e.preventDefault();

        const form = this;
        const formData = new FormData(form);

        // reset error text
        $("#edit-username-perawat-error").text("");
        $("#edit-nama-perawat-error").text("");
        $("#edit-email-perawat-error").text("");
        $("#edit-no-hp-perawat-error").text("");
        $("#edit-password-perawat-error").text("");
        $("#edit-password-confirmation-perawat-error").text("");
        $("#edit_foto_perawat-error").text("");
        $("#edit_dokter_poli_id-error").text("");
        $("#edit-poli-select-error").text("");
        $("#edit-dokter-select-error").text("");

        const $submitBtn = $(form).find('button[type="submit"]');
        $submitBtn.prop("disabled", true);

        $.ajax({
            url: $(form).attr("action"),
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
            success: async function (response) {
                await showSwalSuccess(response.pesan);

                $("#modal-edit-perawat").removeClass("flex").addClass("hidden");
                unlockBodyScroll();

                // reset form
                form.reset();
                editPenugasanList = [];
                renderEditPenugasanTable();

                if (editPoliTom) {
                    editPoliTom.clear(true);
                    editPoliTom.clearOptions();
                    setTomPlaceholder(editPoliTom, "Cari & Pilih Poli ...");
                    loadDataPoliEdit();
                }

                if (editDokterTom) {
                    editDokterTom.clear(true);
                    editDokterTom.clearOptions();
                    editDokterTom.disable();
                    setTomPlaceholder(editDokterTom, "Pilih poli dulu");
                }

                setEditFotoPreview("");
                $("#group_dokter_edit").addClass("hidden");

                // reload datatable
                if ($.fn.DataTable.isDataTable("#table-perawat")) {
                    $("#table-perawat").DataTable().ajax.reload(null, false);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors || {};

                    $("#edit-username-perawat-error").text(
                        errors.edit_username_perawat?.[0] || "",
                    );
                    $("#edit-nama-perawat-error").text(
                        errors.edit_nama_perawat?.[0] || "",
                    );
                    $("#edit-email-perawat-error").text(
                        errors.edit_email_perawat?.[0] || "",
                    );
                    $("#edit-no-hp-perawat-error").text(
                        errors.edit_no_hp_perawat?.[0] || "",
                    );
                    $("#edit-password-perawat-error").text(
                        errors.edit_password_perawat?.[0] || "",
                    );
                    $("#edit-password-confirmation-perawat-error").text(
                        errors.edit_password_perawat_confirmation?.[0] || "",
                    );
                    $("#edit_foto_perawat-error").text(
                        errors.edit_foto_perawat?.[0] || "",
                    );
                    $("#edit_dokter_poli_id-error").text(
                        errors.dokter_poli_id?.[0] || "",
                    );
                } else {
                    showSwalError(
                        xhr.responseJSON?.pesan ||
                            xhr.responseJSON?.message ||
                            "Terjadi kesalahan saat memperbarui data.",
                    );
                }
            },
            complete: function () {
                $submitBtn.prop("disabled", false);
            },
        });
    });
});

$(function () {
    $(document).on("click", ".btn-delete", function (e) {
        e.preventDefault();

        const urlDeleteDataPerawat = $(this).data("url-delete-data-perawat");

        Swal.fire({
            title: "Yakin ingin menghapus?",
            text: "Data perawat yang dihapus tidak bisa dikembalikan.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6b7280",
            confirmButtonText: "Ya, hapus!",
            cancelButtonText: "Batal",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: urlDeleteDataPerawat,
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                            "content",
                        ),
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil",
                            text: response.pesan
                        }).then(() => {
                            $("#table-perawat").DataTable().ajax.reload(null, false);
                        });
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: "error",
                            title: "Gagal",
                            text: xhr.responseJSON?.pesan || "Terjadi kesalahan saat menghapus data."
                        });
                    }
                });
            }
        });
    });
});
