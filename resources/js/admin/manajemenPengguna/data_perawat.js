import axios from "axios";
import { initFlowbite } from "flowbite";
import $ from "jquery";

// ================== DATA TABLE PERAWAT ==================
$(function () {
    var table = $("#userPerawat").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/manajemen_pengguna/data_perawat",
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
            { data: "nama_poli", name: "nama_poli" },
            { data: "nama_dokter", name: "nama_dokter" },
            { data: "nama_perawat", name: "nama_perawat" },
            { data: "username", name: "username" },
            { data: "email_user", name: "email_user" },
            { data: "role", name: "role" },
            { data: "no_hp_perawat", name: "no_hp_perawat" },
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
    $("#kasir_searchInput").on("keyup", function () {
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

// ================== ADD PERAWAT (Poli → Dokter / dokter_poli_id) ==================
$(function () {
    const addModalElement = document.getElementById("addPerawatModal");
    const addModal = addModalElement ? new Modal(addModalElement) : null;
    const $formAdd = $("#formAddPerawat");

    let tsAddPoli = null;
    let tsAddDokter = null;

    function destroyTS(ts) {
        try {
            ts && ts.destroy();
        } catch (_) {}
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

        // reset TomSelects
        destroyTS(tsAddPoli);
        destroyTS(tsAddDokter);
        tsAddPoli = null;
        tsAddDokter = null;
        $("#add_poli_select").html("");
        $("#add_dokter_select").html("");
    }

    // ---------- TomSelect Poli ----------
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
            preload: "focus",
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
                            }))
                        );
                    })
                    .catch(() => cb());
            },
            onChange: (poliId) => {
                destroyTS(tsAddDokter);
                tsAddDokter = null;
                $("#add_dokter_select").html("");

                if (poliId) {
                    initTSAddDokter(poliId);
                }
            },
        });
    }

    // ---------- TomSelect Dokter (dependen Poli) ----------
    function initTSAddDokter(poliId) {
        destroyTS(tsAddDokter);
        $("#add_dokter_select").html("");

        tsAddDokter = new TomSelect("#add_dokter_select", {
            create: false,
            maxItems: 1,
            valueField: "id", // dokter_poli_id
            labelField: "nama",
            searchField: "nama",
            placeholder: "Cari & pilih dokter…",
            preload: "focus",
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
                                id: d.dokter_poli_id, // <= pivot id
                                nama:
                                    d.nama_dokter ||
                                    d.nama ||
                                    `Dokter #${d.dokter_id || "?"}`,
                            }))
                        );
                    })
                    .catch(() => cb());
            },
        });
    }

    // tombol buka modal
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
        }
    );

    // Preview foto
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
                "File harus berupa gambar (JPG/PNG/WebP)."
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

    // drag & drop foto
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

    // submit tambah perawat
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
                    if ($("#userPerawat").length) {
                        addModal && addModal.hide();
                        $("#userPerawat").DataTable().ajax.reload(null, false);
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

// ================== EDIT PERAWAT (versi lama: Dokter → Poli) ==================
$(function () {
    const editModalElement = document.getElementById("editPerawatModal");
    const editModal = editModalElement ? new Modal(editModalElement) : null;
    const $formEdit = $("#formEditPerawat");
    const initialEditUrl = $formEdit.data("url");

    let tsDokter = null;
    let tsPoli = null;

    function destroyTS(ts) {
        try {
            ts && ts.destroy();
        } catch (_) {}
    }
    function showPoliGroup() {
        $("#group_poli_edit").removeClass("hidden");
    }
    function hidePoliGroup() {
        $("#group_poli_edit").addClass("hidden");
        destroyTS(tsPoli);
        tsPoli = null;
        $("#edit_poli_select").html("");
    }

    function resetEditForm() {
        $formEdit[0].reset();
        $formEdit.find(".is-invalid").removeClass("is-invalid");
        $formEdit.find(".text-red-600").empty();

        $formEdit.data("url", initialEditUrl);
        $formEdit.attr("action", initialEditUrl);

        // foto
        $("#edit_preview_foto_perawat").addClass("hidden").attr("src", "");
        $("#edit_placeholder_foto_perawat").removeClass("hidden");
        $("#edit_foto_drop_area_perawat")
            .removeClass("border-solid border-gray-300")
            .addClass("border-dashed border-gray-400");

        // selects
        destroyTS(tsDokter);
        tsDokter = null;
        hidePoliGroup();
        $("#edit_dokter_select").html("");
    }

    // ---------- TomSelect Dokter ----------
    function initTomSelectDokter(preId = null, preText = null) {
        destroyTS(tsDokter);

        if (preId && preText) {
            $("#edit_dokter_select").html(
                `<option value="${preId}" selected>${preText}</option>`
            );
        } else {
            $("#edit_dokter_select").html("");
        }

        tsDokter = new TomSelect("#edit_dokter_select", {
            create: false,
            maxItems: 1,
            valueField: "id",
            labelField: "nama",
            searchField: "nama",
            placeholder: "Cari & pilih dokter…",
            preload: !!preId,
            render: {
                option: (it) => `<div class="py-1 px-2">${it.nama}</div>`,
                item: (it) => `<div>${it.nama}</div>`,
            },
            load: function (q, cb) {
                axios
                    .get("/manajemen_pengguna/list_dokter", {
                        params: { q: q || "" },
                    })
                    .then(({ data }) => {
                        const arr = Array.isArray(data?.data) ? data.data : [];
                        cb(
                            arr.map((d) => ({
                                id: d.id,
                                nama:
                                    d.nama_dokter ||
                                    d.nama ||
                                    `Dokter #${d.id}`,
                            }))
                        );
                    })
                    .catch(() => cb());
            },
            onChange: (val) => {
                if (val) {
                    initTomSelectPoli(val, null, null);
                } else {
                    hidePoliGroup();
                }
            },
        });

        if (preId) tsDokter.setValue(String(preId), true);
    }

    // ---------- TomSelect Poli (depend Dokter) ----------
    function initTomSelectPoli(dokterId, preId = null, preText = null) {
        destroyTS(tsPoli);

        if (!dokterId) {
            hidePoliGroup();
            return;
        }

        if (preId && preText) {
            $("#edit_poli_select").html(
                `<option value="${preId}" selected>${preText}</option>`
            );
        } else {
            $("#edit_poli_select").html("");
        }

        tsPoli = new TomSelect("#edit_poli_select", {
            create: false,
            maxItems: 1,
            valueField: "id",
            labelField: "nama",
            searchField: "nama",
            placeholder: "Cari & pilih poli…",
            preload: "focus",
            shouldLoad: () => true,
            render: {
                option: (it) => `<div class="py-1 px-2">${it.nama}</div>`,
                item: (it) => `<div>${it.nama}</div>`,
            },
            load: function (q, cb) {
                axios
                    .get(`/manajemen_pengguna/dokter/${dokterId}/polis`, {
                        params: { q: q || "" },
                    })
                    .then(({ data }) => {
                        const arr = Array.isArray(data?.data) ? data.data : [];
                        cb(
                            arr.map((p) => ({
                                id: p.id,
                                nama: p.nama_poli || p.nama || `Poli #${p.id}`,
                            }))
                        );
                    })
                    .catch(() => cb());
            },
            onInitialize() {
                this.load("");
                setTimeout(() => this.open(), 60);
            },
            onFocus() {
                if (this.options_count === 0) this.load("");
            },
        });

        if (preId) tsPoli.setValue(String(preId), true);
        showPoliGroup();
    }

    // Preview foto edit
    $("#edit_foto_perawat").on("change", function () {
        const file = this.files?.[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => {
            $("#edit_preview_foto_perawat")
                .attr("src", e.target.result)
                .removeClass("hidden");
            $("#edit_placeholder_foto_perawat").addClass("hidden");
        };
        reader.readAsDataURL(file);
    });

    // Buka modal edit
    $("body").on("click", ".btn-edit-perawat", async function () {
        resetEditForm();
        const id = $(this).data("id");

        try {
            const { data: resp } = await axios.get(
                `/manajemen_pengguna/get_perawat_by_id/${id}`
            );
            const row = resp.data;

            const baseUrl = $formEdit.data("url");
            const finalUrl = baseUrl.replace("/0", "/" + row.id);
            $formEdit.data("url", finalUrl);
            $formEdit.attr("action", finalUrl);

            $("#edit_perawat_id").val(row.id);
            $("#edit_username_perawat").val(row.user?.username || "");
            $("#edit_email_perawat").val(row.user?.email || "");
            $("#edit_nama_perawat").val(row.nama_perawat || "");
            $("#edit_no_hp_perawat").val(row.no_hp_perawat || "");

            if (row.foto_perawat) {
                const url = `/storage/${row.foto_perawat}`;
                $("#edit_preview_foto_perawat")
                    .attr("src", url)
                    .removeClass("hidden");
                $("#edit_placeholder_foto_perawat").addClass("hidden");
                $("#edit_foto_drop_area_perawat")
                    .removeClass("border-dashed border-gray-400")
                    .addClass("border-solid border-gray-300");
            }

            const dokterId = row.dokter?.id || row.dokter_id || null;
            const dokterNama = row.dokter?.nama_dokter || null;
            const poliId = row.poli?.id || row.poli_id || null;
            const poliNama = row.poli?.nama_poli || null;

            initTomSelectDokter(dokterId, dokterNama);
            if (dokterId) {
                initTomSelectPoli(dokterId, poliId, poliNama);
            } else {
                hidePoliGroup();
            }

            editModal && editModal.show();
        } catch (e) {
            Swal.fire({
                icon: "error",
                title: "Gagal!",
                text: "Tidak dapat memuat data perawat.",
            });
        }
    });

    // Submit edit
    $formEdit.on("submit", async function (e) {
        e.preventDefault();
        const url = $formEdit.data("url");
        const formData = new FormData($formEdit[0]);
        if (!formData.has("_method")) formData.append("_method", "PUT");

        $formEdit.find(".is-invalid").removeClass("is-invalid");
        $formEdit.find('[id$="-error"]').html("");

        try {
            const { data } = await axios.post(url, formData);
            await Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: data.message || "Tersimpan.",
                timer: 1500,
                showConfirmButton: false,
            });
            editModal && editModal.hide();
            $("#userPerawat").DataTable().ajax.reload(null, false);
            resetEditForm();
        } catch (error) {
            if (error.response && error.response.status === 422) {
                const errs = error.response.data.errors || {};
                Swal.fire({
                    icon: "error",
                    title: "Validasi Gagal!",
                    text: "Periksa kembali isian Anda.",
                });
                Object.keys(errs).forEach((k) => {
                    const base = k.split(".")[0];
                    const $inp = $("#" + base);
                    if ($inp.length) $inp.addClass("is-invalid");
                    const $err = $("#" + base + "-error");
                    if ($err.length) $err.html(errs[k][0]);

                    if (base === "edit_poli_id" || base === "poli_id") {
                        $("#edit_poli_select").addClass("is-invalid");
                        $("#edit_poli_id-error").html(errs[k][0]);
                    }
                    if (base === "edit_dokter_id" || base === "dokter_id") {
                        $("#edit_dokter_select").addClass("is-invalid");
                        $("#edit_dokter_id-error").html(errs[k][0]);
                    }
                });
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error!",
                    text: "Terjadi kesalahan server.",
                });
            }
        }
    });

    $("#closeEditPerawatModal, #closeEditPerawatModal_header").on(
        "click",
        function () {
            resetEditForm();
            editModal && editModal.hide();
        }
    );
});

// ================== DELETE PERAWAT ==================
$(function () {
    $("body").on("click", ".btn-delete-perawat", function () {
        const perawatId = $(this).data("id");
        if (!perawatId) return;

        const csrf =
            document.querySelector('meta[name="csrf-token"]')?.content || "";

        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Data yang dihapus tidak bisa dikembalikan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, hapus!",
            cancelButtonText: "Batal",
            reverseButtons: true,
        }).then((res) => {
            if (!res.isConfirmed) return;

            Swal.showLoading();

            axios
                .delete(`/manajemen_pengguna/delete_perawat/${perawatId}`, {
                    headers: {
                        "X-CSRF-TOKEN": csrf,
                        Accept: "application/json",
                    },
                })
                .then(({ data }) => {
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil!",
                        text: data.message || "Data perawat berhasil dihapus.",
                        timer: 1400,
                        showConfirmButton: false,
                    }).then(() => {
                        if ($("#userPerawat").length) {
                            $("#userPerawat")
                                .DataTable()
                                .ajax.reload(null, false);
                        } else {
                            window.location.reload();
                        }
                    });
                })
                .catch((error) => {
                    const status = error?.response?.status || 500;
                    const msg =
                        error?.response?.data?.message ||
                        "Terjadi kesalahan server. Silakan coba lagi.";

                    if (status === 409) {
                        Swal.fire({
                            icon: "error",
                            title: "Tidak bisa dihapus",
                            html: msg.replace(/\n/g, "<br>"),
                        });
                    } else if (status === 404) {
                        Swal.fire({
                            icon: "error",
                            title: "Tidak ditemukan",
                            text: "Data perawat tidak ditemukan.",
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error!",
                            text: msg,
                        });
                    }
                });
        });
    });
});
