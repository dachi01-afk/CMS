import $ from "jquery";
import axios from "axios";
import { initFlowbite } from "flowbite";

/* ===========================================
 *  DATATABLE — ORDER LAYANAN
 * =========================================== */
$(function () {
    var table = $("#orderLayanan").DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        ordering: true,
        pageLength: true,
        lengthChange: true,
        info: false,
        ajax: "/order-layanan/get-data-order-layanan",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "kode_transaksi", name: "kode_transaksi" },
            { data: "nama_pasien", name: "nama_pasien" },
            { data: "nama_layanan", name: "nama_layanan" },
            { data: "kategori_layanan", name: "kategori_layanan" },
            { data: "jumlah", name: "jumlah" },
            { data: "total_tagihan", name: "total_tagihan" },
            { data: "status", name: "status" },
            { data: "tanggal_transaksi", name: "tanggal_transaksi" },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
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

    /* SEARCH */
    $("#order-layanan-search-input").on("keyup", function () {
        table.search(this.value).draw();
    });

    const $info = $("#order-layanan-custom-info");
    const $pagination = $("#order-layanan-custom-pagination");
    const $perPage = $("#order-layanan-page-length");

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
            `<li><a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 ${prevDisabled}">Previous</a></li>`
        );

        const maxVisible = 5;
        let start = Math.max(currentPage - Math.floor(maxVisible / 2), 1);
        let end = Math.min(start + maxVisible - 1, totalPages);

        if (end - start < maxVisible - 1)
            start = Math.max(end - maxVisible + 1, 1);

        for (let i = start; i <= end; i++) {
            const active =
                i === currentPage
                    ? "text-blue-600 bg-blue-50 border-blue-300"
                    : "text-gray-500 bg-white border-gray-300 hover:bg-gray-100";

            $pagination.append(
                `<li><a href="#" class="page-number flex items-center justify-center px-3 h-8 border ${active}" data-page="${i}">${i}</a></li>`
            );
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(
            `<li><a href="#" id="btnNext" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 ${nextDisabled}">Next</a></li>`
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

/* ============================================================
 *  MODAL CREATE ORDER LAYANAN (POLI PAKAI TOM SELECT)
 * ============================================================ */
$(function () {
    const addModalEl = document.getElementById("modalCreateOrderLayanan");
    const addModal = addModalEl ? new Modal(addModalEl) : null;
    const $formAdd = $("#formCreateOrderLayanan");

    const $pasienSearch = $("#pasien_search_create");
    const $pasienId = $("#pasien_id_create");
    const $pasienResults = $("#pasien_search_results_create");
    const $pasienError = $("#pasien_id_create-error");
    const $pasienInfoCard = $("#pasien_info_create");
    const $pasienNamaInfo = $("#pasien_nama_info_create");
    const $pasienEmrInfo = $("#pasien_no_emr_info_create");
    const $pasienJkInfo = $("#pasien_jk_info_create");

    // --- elemen list layanan ---
    const $itemsWrapper = $("#orderItemsWrapper");
    const $itemTemplate = $("#orderItemTemplate");

    // --- POLI: Tom Select ---
    const $poliSelect = $("#poli_id_select_create");
    let poliTom = null;

    if ($poliSelect.length && typeof TomSelect !== "undefined") {
        poliTom = new TomSelect("#poli_id_select_create", {
            placeholder: "-- Pilih Poli --",
            allowEmptyOption: true,
            maxItems: 1,
            create: false,
            sortField: { field: "text", direction: "asc" },
        });
    }

    // Tambah baris layanan
    function addItemRow() {
        const $row = $itemTemplate.clone();
        $row.removeAttr("id"); // supaya tidak duplikat ID
        $row.removeClass("hidden"); // kalau kamu mau template diset hidden, bisa di-aktifkan di HTML
        $itemsWrapper.append($row);
        recalcGrandTotal();
        togglePoliSection();
    }

    // Hitung ulang grand total dari semua subtotal
    function recalcGrandTotal() {
        let grand = 0;
        $itemsWrapper.find(".order-item").each(function () {
            const sub = cleanRupiah($(this).find(".subtotal-input").val());
            grand += sub;
        });
        $("#total_tagihan_create").val(toRupiah(grand));
    }

    // Tampilkan/hilangkan section poli & jadwal
    function togglePoliSection() {
        const $sectionPoliJadwal = $("#section_poli_jadwal_create");

        const hasPemeriksaan =
            $itemsWrapper.find(".kategori-nama-input").filter(function () {
                return $(this).val() === "Pemeriksaan";
            }).length > 0;

        if (hasPemeriksaan) {
            $sectionPoliJadwal.removeClass("hidden");
        } else {
            $sectionPoliJadwal.addClass("hidden");

            // reset poli (Tom Select kalau ada)
            if (poliTom) {
                poliTom.clear();
            } else {
                $("#poli_id_select_create").val("");
            }
            $("#poli_id_create-error").text("").css("opacity", 0);

            $("#jadwal_dokter_id_create")
                .empty()
                .append('<option value="">-- Pilih Jadwal Dokter --</option>')
                .prop("disabled", true);
            $("#jadwal_dokter_id_create-error").text("").css("opacity", 0);
            $("#dokter_id_create").val("");
            $("#info_jadwal_dokter_create").addClass("hidden").text("");
        }
    }

    // Helper: format rupiah
    function toRupiah(value) {
        const number = Number(value || 0);
        return "Rp " + number.toLocaleString("id-ID");
    }

    // Helper: bersihkan string rupiah → angka murni
    function cleanRupiah(value) {
        if (!value) return 0;
        const angkaBersih = String(value).replace(/[^0-9]/g, "");
        return Number(angkaBersih) || 0;
    }

    function resetAddForm() {
        if ($formAdd[0]) {
            $formAdd[0].reset();
        }
        $formAdd.find(".is-invalid").removeClass("is-invalid");
        $formAdd.find(".text-red-600").empty().css("opacity", 0);

        // Pasien
        $pasienSearch.val("");
        $pasienId.val("");
        $pasienResults.empty().addClass("hidden");
        $pasienInfoCard.addClass("hidden");
        $pasienNamaInfo.text("-");
        $pasienEmrInfo.text("-");
        $pasienJkInfo.text("-");

        // Layanan / kategori / total
        $itemsWrapper.empty();
        addItemRow(); // selalu mulai dengan 1 baris kosong
        $("#total_tagihan_create").val("");

        // Poli & jadwal
        const $sectionPoliJadwal = $("#section_poli_jadwal_create");
        $sectionPoliJadwal.addClass("hidden");

        if (poliTom) {
            poliTom.clear();
        } else {
            $("#poli_id_select_create").val("");
        }
        $("#poli_id_create-error").text("").css("opacity", 0);

        $("#jadwal_dokter_id_create")
            .empty()
            .append('<option value="">-- Pilih Jadwal Dokter --</option>')
            .prop("disabled", true);
        $("#jadwal_dokter_id_create-error").text("").css("opacity", 0);
        $("#dokter_id_create").val("");
        $("#info_jadwal_dokter_create").addClass("hidden").text("");
    }

    // Tambah baris layanan
    $("#btnAddLayananRow").on("click", function (e) {
        e.preventDefault();
        addItemRow();
    });

    $("#buttonOpenModalCreateOrderLayanan").on("click", function () {
        resetAddForm();
        addModal?.show();
    });

    $("#buttonCloseModalCreateOrderLayanan").on("click", function () {
        addModal?.hide();
        resetAddForm();
    });

    $("#buttonCancaleModalCreateOrderLayanan").on("click", function () {
        addModal?.hide();
        resetAddForm();
    });

    // Hapus baris layanan
    $formAdd.on("click", ".btn-remove-item", function (e) {
        e.preventDefault();
        const $row = $(this).closest(".order-item");
        const count = $itemsWrapper.find(".order-item").length;

        if (count <= 1) {
            // minimal 1 baris: cuma clear isinya
            $row.find(".layanan-select").val("");
            $row.find(".kategori-nama-input").val("");
            $row.find(".kategori-id-input").val("");
            $row.find(".jumlah-input").val(1);
            $row.find(".subtotal-input").val("");
        } else {
            $row.remove();
        }

        recalcGrandTotal();
        togglePoliSection();
    });

    /* =========================
     *  SEARCH PASIEN (AJAX)
     * ========================= */
    let pasienSearchTimeout = null;

    $pasienSearch.on("keyup", function () {
        const keyword = $(this).val().trim();

        if (keyword.length < 2) {
            $pasienResults.empty().addClass("hidden");
            $pasienId.val("");
            $pasienInfoCard.addClass("hidden");
            return;
        }

        clearTimeout(pasienSearchTimeout);
        pasienSearchTimeout = setTimeout(() => {
            axios
                .get("/order-layanan/get-data-pasien", {
                    params: { q: keyword },
                })
                .then((response) => {
                    const list = response.data.data || [];

                    $pasienResults.empty();

                    if (!list.length) {
                        $pasienResults
                            .append(
                                `<div class="px-3 py-2 text-xs text-gray-500 dark:text-gray-300">Tidak ada pasien ditemukan.</div>`
                            )
                            .removeClass("hidden");
                        return;
                    }

                    list.forEach((pasien) => {
                        const infoTambahan = pasien.no_rm
                            ? `No RM: ${pasien.no_rm}`
                            : pasien.nik
                            ? `NIK: ${pasien.nik}`
                            : "";

                        $pasienResults.append(`
                            <button type="button"
                                class="pasien-item w-full text-left px-3 py-2 text-sm hover:bg-blue-50 dark:hover:bg-gray-600 flex flex-col border-b border-gray-100 dark:border-gray-600"
                                data-id="${pasien.id}"
                                data-nama="${pasien.nama_pasien}"
                                data-no-emr="${pasien.no_emr || ""}"
                                data-jk="${pasien.jenis_kelamin || "-"}">
                                <span class="font-semibold text-gray-800 dark:text-gray-100">${
                                    pasien.nama_pasien
                                }</span>
                                <span class="text-xs text-gray-500 dark:text-gray-300">${infoTambahan}</span>
                            </button>
                        `);
                    });

                    $pasienResults.removeClass("hidden");
                })
                .catch((error) => {
                    console.error("Error search pasien:", error);
                    $pasienResults
                        .empty()
                        .append(
                            `<div class="px-3 py-2 text-xs text-red-500">Terjadi kesalahan saat mencari pasien.</div>`
                        )
                        .removeClass("hidden");
                });
        }, 300);
    });

    $pasienResults.on("click", ".pasien-item", function () {
        const $item = $(this);
        const id = $item.data("id");
        const nama = $item.data("nama");
        const noEmr = $item.data("no-emr") || "-";
        const jk = $item.data("jk") || "-";

        $pasienId.val(id);
        $pasienSearch.val(nama);
        $pasienResults.empty().addClass("hidden");

        $pasienNamaInfo.text(nama);
        $pasienEmrInfo.text(noEmr);
        $pasienJkInfo.text(jk);
        $pasienInfoCard.removeClass("hidden");

        $pasienSearch.removeClass("is-invalid");
        $pasienError.text("");
    });

    $(document).on("click", function (e) {
        if (
            !$(e.target).closest("#pasien_search_create").length &&
            !$(e.target).closest("#pasien_search_results_create").length
        ) {
            $pasienResults.addClass("hidden");
        }
    });

    /* =========================
     *  LAYANAN → KATEGORI + SUBTOTAL
     * ========================= */
    $formAdd.on("change", ".layanan-select", function () {
        const $row = $(this).closest(".order-item");
        const selected = $(this).find("option:selected");

        const kategoriId = selected.data("kategori-id") || "";
        const kategoriNama = selected.data("kategori-nama") || "";
        const harga = parseFloat(selected.data("harga") || 0);

        $row.find(".kategori-id-input").val(kategoriId);
        $row.find(".kategori-nama-input").val(kategoriNama);

        const qty = parseInt($row.find(".jumlah-input").val() || 1, 10);
        const subtotal = harga * qty;
        $row.find(".subtotal-input").val(toRupiah(subtotal));

        recalcGrandTotal();
        togglePoliSection();
    });

    /* =========================
     *  JUMLAH → UPDATE SUBTOTAL
     * ========================= */
    $formAdd.on("input", ".jumlah-input", function () {
        const $row = $(this).closest(".order-item");
        const selected = $row.find(".layanan-select option:selected");
        const harga = parseFloat(selected.data("harga") || 0);
        const qty = parseInt($(this).val() || 1, 10);

        const subtotal = harga * qty;
        $row.find(".subtotal-input").val(toRupiah(subtotal));

        recalcGrandTotal();
    });

    /* =========================
     *  POLI → LOAD JADWAL DOKTER
     * ========================= */
    $("#poli_id_select_create").on("change", function () {
        const poliId = $(this).val();
        const $jadwalSelect = $("#jadwal_dokter_id_create");
        const $infoJadwal = $("#info_jadwal_dokter_create");

        $jadwalSelect
            .empty()
            .append('<option value="">-- Pilih Jadwal Dokter --</option>');
        $("#dokter_id_create").val("");
        $infoJadwal.addClass("hidden").text("");

        if (!poliId) {
            $jadwalSelect.prop("disabled", true);
            return;
        }

        axios
            .get("/order-layanan/get-data-jadwal-dokter-hari-ini", {
                params: { poli_id: poliId },
            })
            .then((response) => {
                const list = response.data.data || [];

                if (!list.length) {
                    $jadwalSelect.prop("disabled", true);
                    $infoJadwal
                        .removeClass("hidden")
                        .text(
                            "Tidak ada jadwal dokter untuk poli ini pada hari ini."
                        );
                    return;
                }

                list.forEach((jd) => {
                    $jadwalSelect.append(`
                        <option value="${jd.id}"
                            data-dokter-id="${jd.dokter_id}"
                            data-nama-dokter="${jd.nama_dokter}"
                            data-jam-awal="${jd.jam_awal}"
                            data-jam-selesai="${jd.jam_selesai}">
                            ${jd.nama_dokter} — ${jd.jam_awal} s/d ${jd.jam_selesai}
                        </option>
                    `);
                });

                $jadwalSelect.prop("disabled", false);
            })
            .catch((error) => {
                console.error("Error load jadwal:", error);
                $jadwalSelect.prop("disabled", true);
                $infoJadwal
                    .removeClass("hidden")
                    .text("Gagal memuat jadwal dokter.");
            });
    });

    /* =========================
     *  PILIH JADWAL → SET DOKTER_ID
     * ========================= */
    $("#jadwal_dokter_id_create").on("change", function () {
        const opt = $(this).find("option:selected");
        const dokterId = opt.data("dokter-id") || "";
        const namaDokter = opt.data("nama-dokter") || "";
        const jamAwal = opt.data("jam-awal") || "";
        const jamSelesai = opt.data("jam-selesai") || "";

        $("#dokter_id_create").val(dokterId);

        if (dokterId) {
            $("#info_jadwal_dokter_create")
                .removeClass("hidden")
                .text(`Dokter: ${namaDokter} (${jamAwal} - ${jamSelesai})`);
        } else {
            $("#info_jadwal_dokter_create").addClass("hidden").text("");
        }
    });

    /* =========================
     *  SUBMIT FORM
     * ========================= */
    $formAdd.on("submit", function (e) {
        e.preventDefault();
        const url = $formAdd.data("url");

        // Kumpulkan item layanan
        const items = [];
        $itemsWrapper.find(".order-item").each(function () {
            const $row = $(this);

            const layananId = $row.find(".layanan-select").val();
            if (!layananId) return; // skip baris kosong

            const kategoriId = $row.find(".kategori-id-input").val();
            const kategoriNama = $row.find(".kategori-nama-input").val();
            const qty = $row.find(".jumlah-input").val() || 1;
            const subtotal = cleanRupiah($row.find(".subtotal-input").val());

            items.push({
                layanan_id: layananId,
                kategori_layanan_id: kategoriId,
                kategori_layanan_nama: kategoriNama,
                jumlah: qty,
                total_tagihan: subtotal,
            });
        });

        const hasPemeriksaan = items.some(
            (item) => item.kategori_layanan_nama === "Pemeriksaan"
        );

        const formData = {
            pasien_id: $("#pasien_id_create").val(),
            items: items, // <--- kirim array layanan
            total_tagihan: cleanRupiah($("#total_tagihan_create").val()),
        };

        if (hasPemeriksaan) {
            formData.poli_id = $("#poli_id_select_create").val();
            formData.jadwal_dokter_id = $("#jadwal_dokter_id_create").val();
        }

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
                if ($("#orderLayanan").length) {
                    $("#orderLayanan").DataTable().ajax.reload(null, false);
                }
            })
            .catch((error) => {
                if (error.response?.status === 422) {
                    // validasi lama: pasien/poli/jadwal masih bisa dipakai
                    const errors = error.response.data.errors || {};

                    $formAdd.find(".is-invalid").removeClass("is-invalid");
                    $formAdd.find(".text-red-600").empty().css("opacity", 0);

                    let hasItemError = false;

                    for (const field in errors) {
                        const msg = errors[field][0];

                        if (field === "pasien_id") {
                            $pasienSearch.addClass("is-invalid");
                            $pasienError.text(msg).css("opacity", 1);
                        } else if (field === "poli_id") {
                            if (poliTom) {
                                $(poliTom.control).addClass("is-invalid");
                            } else {
                                $("#poli_id_select_create").addClass(
                                    "is-invalid"
                                );
                            }
                            $("#poli_id_create-error")
                                .text(msg)
                                .css("opacity", 1);
                        } else if (field === "jadwal_dokter_id") {
                            $("#jadwal_dokter_id_create").addClass(
                                "is-invalid"
                            );
                            $("#jadwal_dokter_id_create-error")
                                .text(msg)
                                .css("opacity", 1);
                        } else if (field.startsWith("items")) {
                            hasItemError = true;
                        }
                    }

                    if (hasItemError) {
                        Swal.fire({
                            icon: "error",
                            title: "Validasi Layanan",
                            text: "Periksa kembali data layanan (layanan, jumlah, dll).",
                        });
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Server Error",
                        text: "Terjadi kesalahan saat menyimpan data.",
                    });
                }
            });
    });
});

/* ============================================================
 *  MODAL UPDATE ORDER LAYANAN (POLI PAKAI TOM SELECT)
 * ============================================================ */
$(function () {
    const editModalEl = document.getElementById("modalUpdateOrderLayanan");
    const editModal = editModalEl ? new Modal(editModalEl) : null;
    const $formEdit = $("#formUpdateOrderLayanan");

    const $pasienSearch = $("#pasien_search_update");
    const $pasienId = $("#pasien_id_update");
    const $pasienResults = $("#pasien_search_results_update");
    const $pasienError = $("#pasien_id_update-error");
    const $pasienInfoCard = $("#pasien_info_update");
    const $pasienNamaInfo = $("#pasien_nama_info_update");
    const $pasienEmrInfo = $("#pasien_no_emr_info_update");
    const $pasienJkInfo = $("#pasien_jk_info_update");

    // --- elemen list layanan ---
    const $itemsWrapper = $("#orderItemsWrapperUpdate");
    const $itemTemplate = $("#orderItemTemplateUpdate");

    // --- POLI: Tom Select ---
    const $poliSelect = $("#poli_id_select_update");
    let poliTomUpdate = null;

    if ($poliSelect.length && typeof TomSelect !== "undefined") {
        poliTomUpdate = new TomSelect("#poli_id_select_update", {
            placeholder: "-- Pilih Poli --",
            allowEmptyOption: true,
            maxItems: 1,
            create: false,
            sortField: { field: "text", direction: "asc" },
        });
    }

    // ========================
    // Helper: format rupiah
    // ========================
    function toRupiah(value) {
        const number = Number(value || 0);
        return "Rp " + number.toLocaleString("id-ID");
    }

    // Helper: bersihkan string rupiah → angka murni
    function cleanRupiah(value) {
        if (!value) return 0;
        const angkaBersih = String(value).replace(/[^0-9]/g, "");
        return Number(angkaBersih) || 0;
    }

    // Tambah baris layanan (UPDATE)
    function addItemRowUpdate() {
        const $row = $itemTemplate.clone();
        $row.removeAttr("id"); // supaya tidak duplikat ID
        $row.removeClass("hidden"); // pastikan tampil
        $itemsWrapper.append($row);
        recalcGrandTotalUpdate();
        togglePoliSectionUpdate();
        return $row;
    }

    // 🔹 tombol "Tambah Layanan" di modal UPDATE
    $("#btnAddLayananRowUpdate").on("click", function (e) {
        e.preventDefault();
        addItemRowUpdate();
    });

    // Hitung ulang grand total dari semua subtotal (UPDATE)
    function recalcGrandTotalUpdate() {
        let grand = 0;
        $itemsWrapper.find(".order-item").each(function () {
            const sub = cleanRupiah($(this).find(".subtotal-input").val());
            grand += sub;
        });
        $("#total_tagihan_update").val(toRupiah(grand));
    }

    // Tampilkan/hilangkan section poli & jadwal (UPDATE)
    function togglePoliSectionUpdate() {
        const $sectionPoliJadwal = $("#section_poli_jadwal_update");

        const hasPemeriksaan =
            $itemsWrapper.find(".kategori-nama-input").filter(function () {
                return $(this).val() === "Pemeriksaan";
            }).length > 0;

        if (hasPemeriksaan) {
            $sectionPoliJadwal.removeClass("hidden");
        } else {
            $sectionPoliJadwal.addClass("hidden");

            // reset poli (Tom Select kalau ada)
            if (poliTomUpdate) {
                poliTomUpdate.clear();
            } else {
                $("#poli_id_select_update").val("");
            }
            $("#poli_id_update-error").text("").css("opacity", 0);

            $("#jadwal_dokter_id_update")
                .empty()
                .append('<option value="">-- Pilih Jadwal Dokter --</option>')
                .prop("disabled", true);
            $("#jadwal_dokter_id_update-error").text("").css("opacity", 0);
            $("#dokter_id_update").val("");
            $("#info_jadwal_dokter_update").addClass("hidden").text("");
        }
    }

    function resetEditForm() {
        if ($formEdit[0]) {
            $formEdit[0].reset();
        }
        $formEdit.find(".is-invalid").removeClass("is-invalid");
        $formEdit.find(".text-red-600").empty().css("opacity", 0);

        // ID order
        $("#order_layanan_id_update").val("");

        // Pasien
        $pasienSearch.val("");
        $pasienId.val("");
        $pasienResults.empty().addClass("hidden");
        $pasienInfoCard.addClass("hidden");
        $pasienNamaInfo.text("-");
        $pasienEmrInfo.text("-");
        $pasienJkInfo.text("-");

        // Layanan / kategori / total
        $itemsWrapper.empty();
        addItemRowUpdate(); // minimal 1 baris
        $("#total_tagihan_update").val("");

        // Poli & jadwal
        const $sectionPoliJadwal = $("#section_poli_jadwal_update");
        $sectionPoliJadwal.addClass("hidden");

        if (poliTomUpdate) {
            poliTomUpdate.clear();
        } else {
            $("#poli_id_select_update").val("");
        }
        $("#poli_id_update-error").text("").css("opacity", 0);

        $("#jadwal_dokter_id_update")
            .empty()
            .append('<option value="">-- Pilih Jadwal Dokter --</option>')
            .prop("disabled", true);
        $("#jadwal_dokter_id_update-error").text("").css("opacity", 0);
        $("#dokter_id_update").val("");
        $("#info_jadwal_dokter_update").addClass("hidden").text("");
    }

    // ========================
    //  OPEN MODAL EDIT
    // ========================
    // tombol edit di tabel: <button class="btn-update-order-layanan" data-kode-transaksi="...">
    $(document).on("click", ".btn-update-order-layanan", function () {
        // pakai data-kode-transaksi dari tombol
        // Saran HTML: data-kode-transaksi="XXX"
        const kodeTransaksi =
            $(this).data("kodeTransaksi") || $(this).data("kode-transaksi");
        if (!kodeTransaksi) return;

        resetEditForm();

        const detailUrl = `/order-layanan/get-data-order-layanan/${kodeTransaksi}`;

        axios
            .get(detailUrl)
            .then((response) => {
                const data = response.data.data || {};

                // ===========================
                // PERSIAPAN DATA HEADER & PASIEN
                // ===========================
                const pasienObj = data.pasien || {};

                const pasienNama =
                    pasienObj.nama_pasien || data.nama_pasien || "";
                const pasienEmr = pasienObj.no_emr || data.pasien_no_emr || "-";
                const pasienJk =
                    pasienObj.jenis_kelamin || data.pasien_jenis_kelamin || "-";
                const pasienId = pasienObj.id || data.pasien_id || "";

                // ===========================
                // PERSIAPAN ITEMS + ANCHOR order_layanan_id
                // ===========================
                let items = Array.isArray(data.items) ? data.items : [];

                // fallback kalau backend masih kirim single layanan di root
                if (!items.length && data.layanan_id) {
                    items = [
                        {
                            id: data.id,
                            layanan_id: data.layanan_id,
                            nama_layanan: data.nama_layanan || "",
                            kategori_layanan_id: data.kategori_layanan_id,
                            kategori_layanan_nama: data.kategori_layanan,
                            jumlah: data.jumlah,
                            total_tagihan: data.total_tagihan,
                        },
                    ];
                }

                // SET order_layanan_id (anchor satu baris)
                let anchorOrderId = null;
                if (data.order_layanan_id) {
                    anchorOrderId = data.order_layanan_id;
                } else if (items.length) {
                    anchorOrderId = items[0].id || null;
                }
                $("#order_layanan_id_update").val(anchorOrderId || "");

                // SET INPUT PASIEN + KARTU INFO
                $pasienId.val(pasienId);
                $pasienSearch.val(pasienNama);

                $pasienNamaInfo.text(pasienNama || "-");
                $pasienEmrInfo.text(pasienEmr || "-");
                $pasienJkInfo.text(pasienJk || "-");
                $pasienInfoCard.removeClass("hidden");

                // ===========================
                // BANGUN BARIS LAYANAN SESUAI JUMLAH ITEMS
                // ===========================
                $itemsWrapper.empty();

                if (items.length) {
                    items.forEach((item) => {
                        const $row = addItemRowUpdate();

                        // pilih layanan di dropdown
                        $row.find(".layanan-select")
                            .val(item.layanan_id || "")
                            .trigger("change"); // otomatis set kategori & harga dari option data-*

                        // override kategori dari backend kalau ada
                        if (item.kategori_layanan_id) {
                            $row.find(".kategori-id-input").val(
                                item.kategori_layanan_id
                            );
                        }
                        if (item.kategori_layanan_nama) {
                            $row.find(".kategori-nama-input").val(
                                item.kategori_layanan_nama
                            );
                        }

                        // jumlah
                        $row.find(".jumlah-input").val(item.jumlah || 1);

                        // subtotal
                        const sub = item.total_tagihan || 0;
                        $row.find(".subtotal-input").val(toRupiah(sub));
                    });
                } else {
                    addItemRowUpdate();
                }

                // ===========================
                // TOTAL TAGIHAN
                // ===========================
                if (typeof data.total_tagihan !== "undefined") {
                    $("#total_tagihan_update").val(
                        toRupiah(data.total_tagihan || 0)
                    );
                } else {
                    recalcGrandTotalUpdate();
                }

                // ===========================
                // POLI & JADWAL (KALAU ADA PEMERIKSAAN)
                // ===========================
                const hasPemeriksaan = items.some(
                    (it) =>
                        (it.kategori_layanan_nama || "").toLowerCase() ===
                        "pemeriksaan"
                );

                if (hasPemeriksaan) {
                    $("#section_poli_jadwal_update").removeClass("hidden");

                    // kalau backend sudah kirim poli_id & jadwal_dokter_id, bisa auto-set di sini
                    if (data.poli_id) {
                        if (poliTomUpdate) {
                            poliTomUpdate.setValue(String(data.poli_id));
                        } else {
                            $("#poli_id_select_update")
                                .val(data.poli_id)
                                .trigger("change");
                        }

                        // load jadwal dokter untuk poli tsb (opsional)
                        axios
                            .get(
                                "/order-layanan/get-data-jadwal-dokter-hari-ini",
                                { params: { poli_id: data.poli_id } }
                            )
                            .then((resJadwal) => {
                                const list = resJadwal.data.data || [];
                                const $jadwalSelect = $(
                                    "#jadwal_dokter_id_update"
                                );
                                const $infoJadwal = $(
                                    "#info_jadwal_dokter_update"
                                );

                                $jadwalSelect
                                    .empty()
                                    .append(
                                        '<option value="">-- Pilih Jadwal Dokter --</option>'
                                    );

                                if (!list.length) {
                                    $jadwalSelect.prop("disabled", true);
                                    $infoJadwal
                                        .removeClass("hidden")
                                        .text(
                                            "Tidak ada jadwal dokter untuk poli ini pada hari ini."
                                        );
                                    return;
                                }

                                list.forEach((jd) => {
                                    $jadwalSelect.append(`
                                        <option value="${jd.id}"
                                            data-dokter-id="${jd.dokter_id}"
                                            data-nama-dokter="${jd.nama_dokter}"
                                            data-jam-awal="${jd.jam_awal}"
                                            data-jam-selesai="${jd.jam_selesai}">
                                            ${jd.nama_dokter} — ${jd.jam_awal} s/d ${jd.jam_selesai}
                                        </option>
                                    `);
                                });

                                $jadwalSelect.prop("disabled", false);

                                if (data.jadwal_dokter_id) {
                                    $jadwalSelect
                                        .val(data.jadwal_dokter_id)
                                        .trigger("change");
                                }
                            })
                            .catch((errJadwal) => {
                                console.error("Error load jadwal:", errJadwal);
                            });
                    }
                } else {
                    togglePoliSectionUpdate();
                }

                // ===========================
                // TAMPILKAN MODAL
                // ===========================
                editModal?.show();
            })
            .catch((error) => {
                console.error("Error ambil detail order:", error);
                Swal.fire({
                    icon: "error",
                    title: "Gagal",
                    text: "Tidak dapat memuat data order layanan.",
                });
            });
    });

    // ========================
    //  BUTTON CLOSE / CANCEL
    // ========================
    $("#buttonCloseModalUpdateOrderLayanan").on("click", function () {
        editModal?.hide();
        resetEditForm();
    });

    $("#buttonCancaleModalUpdateOrderLayanan").on("click", function () {
        editModal?.hide();
        resetEditForm();
    });

    // ========================
    //  HAPUS BARIS LAYANAN
    // ========================
    $formEdit.on("click", ".btn-remove-item", function (e) {
        e.preventDefault();
        const $row = $(this).closest(".order-item");
        const count = $itemsWrapper.find(".order-item").length;

        if (count <= 1) {
            // minimal 1 baris: clear isi
            $row.find(".layanan-select").val("");
            $row.find(".kategori-nama-input").val("");
            $row.find(".kategori-id-input").val("");
            $row.find(".jumlah-input").val(1);
            $row.find(".subtotal-input").val("");
        } else {
            $row.remove();
        }

        recalcGrandTotalUpdate();
        togglePoliSectionUpdate();
    });

    /* =========================
     *  SEARCH PASIEN (AJAX) - UPDATE
     * ========================= */
    let pasienSearchTimeoutUpdate = null;

    $pasienSearch.on("keyup", function () {
        const keyword = $(this).val().trim();

        if (keyword.length < 2) {
            $pasienResults.empty().addClass("hidden");
            // jangan kosongkan pasien_id kalau cuma edit
            return;
        }

        clearTimeout(pasienSearchTimeoutUpdate);
        pasienSearchTimeoutUpdate = setTimeout(() => {
            axios
                .get("/order-layanan/get-data-pasien", {
                    params: { q: keyword },
                })
                .then((response) => {
                    const list = response.data.data || [];

                    $pasienResults.empty();

                    if (!list.length) {
                        $pasienResults
                            .append(
                                `<div class="px-3 py-2 text-xs text-gray-500 dark:text-gray-300">Tidak ada pasien ditemukan.</div>`
                            )
                            .removeClass("hidden");
                        return;
                    }

                    list.forEach((pasien) => {
                        const infoTambahan = pasien.no_rm
                            ? `No RM: ${pasien.no_rm}`
                            : pasien.nik
                            ? `NIK: ${pasien.nik}`
                            : "";

                        $pasienResults.append(`
                            <button type="button"
                                class="pasien-item-update w-full text-left px-3 py-2 text-sm hover:bg-blue-50 dark:hover:bg-gray-600 flex flex-col border-b border-gray-100 dark:border-gray-600"
                                data-id="${pasien.id}"
                                data-nama="${pasien.nama_pasien}"
                                data-no-emr="${pasien.no_emr || ""}"
                                data-jk="${pasien.jenis_kelamin || "-"}">
                                <span class="font-semibold text-gray-800 dark:text-gray-100">${
                                    pasien.nama_pasien
                                }</span>
                                <span class="text-xs text-gray-500 dark:text-gray-300">${infoTambahan}</span>
                            </button>
                        `);
                    });

                    $pasienResults.removeClass("hidden");
                })
                .catch((error) => {
                    console.error("Error search pasien:", error);
                    $pasienResults
                        .empty()
                        .append(
                            `<div class="px-3 py-2 text-xs text-red-500">Terjadi kesalahan saat mencari pasien.</div>`
                        )
                        .removeClass("hidden");
                });
        }, 300);
    });

    $pasienResults.on("click", ".pasien-item-update", function () {
        const $item = $(this);
        const id = $item.data("id");
        const nama = $item.data("nama");
        const noEmr = $item.data("no-emr") || "-";
        const jk = $item.data("jk") || "-";

        $pasienId.val(id);
        $pasienSearch.val(nama);
        $pasienResults.empty().addClass("hidden");

        $pasienNamaInfo.text(nama);
        $pasienEmrInfo.text(noEmr);
        $pasienJkInfo.text(jk);
        $pasienInfoCard.removeClass("hidden");

        $pasienSearch.removeClass("is-invalid");
        $pasienError.text("");
    });

    $(document).on("click", function (e) {
        if (
            !$(e.target).closest("#pasien_search_update").length &&
            !$(e.target).closest("#pasien_search_results_update").length
        ) {
            $pasienResults.addClass("hidden");
        }
    });

    /* =========================
     *  LAYANAN → KATEGORI + SUBTOTAL (UPDATE)
     * ========================= */
    $formEdit.on("change", ".layanan-select", function () {
        const $row = $(this).closest(".order-item");
        const selected = $(this).find("option:selected");

        const kategoriId = selected.data("kategori-id") || "";
        const kategoriNama = selected.data("kategori-nama") || "";
        const harga = parseFloat(selected.data("harga") || 0);

        $row.find(".kategori-id-input").val(kategoriId);
        $row.find(".kategori-nama-input").val(kategoriNama);

        const qty = parseInt($row.find(".jumlah-input").val() || 1, 10);
        const subtotal = harga * qty;
        $row.find(".subtotal-input").val(toRupiah(subtotal));

        recalcGrandTotalUpdate();
        togglePoliSectionUpdate();
    });

    /* =========================
     *  JUMLAH → UPDATE SUBTOTAL (UPDATE)
     * ========================= */
    $formEdit.on("input", ".jumlah-input", function () {
        const $row = $(this).closest(".order-item");
        const selected = $row.find(".layanan-select option:selected");
        const harga = parseFloat(selected.data("harga") || 0);
        const qty = parseInt($(this).val() || 1, 10);

        const subtotal = harga * qty;
        $row.find(".subtotal-input").val(toRupiah(subtotal));

        recalcGrandTotalUpdate();
    });

    /* =========================
     *  POLI → LOAD JADWAL DOKTER (UPDATE)
     * ========================= */
    $("#poli_id_select_update").on("change", function () {
        const poliId = $(this).val();
        const $jadwalSelect = $("#jadwal_dokter_id_update");
        const $infoJadwal = $("#info_jadwal_dokter_update");

        $jadwalSelect
            .empty()
            .append('<option value="">-- Pilih Jadwal Dokter --</option>');
        $("#dokter_id_update").val("");
        $infoJadwal.addClass("hidden").text("");

        if (!poliId) {
            $jadwalSelect.prop("disabled", true);
            return;
        }

        axios
            .get("/order-layanan/get-data-jadwal-dokter-hari-ini", {
                params: { poli_id: poliId },
            })
            .then((response) => {
                const list = response.data.data || [];

                if (!list.length) {
                    $jadwalSelect.prop("disabled", true);
                    $infoJadwal
                        .removeClass("hidden")
                        .text(
                            "Tidak ada jadwal dokter untuk poli ini pada hari ini."
                        );
                    return;
                }

                list.forEach((jd) => {
                    $jadwalSelect.append(`
                        <option value="${jd.id}"
                            data-dokter-id="${jd.dokter_id}"
                            data-nama-dokter="${jd.nama_dokter}"
                            data-jam-awal="${jd.jam_awal}"
                            data-jam-selesai="${jd.jam_selesai}">
                            ${jd.nama_dokter} — ${jd.jam_awal} s/d ${jd.jam_selesai}
                        </option>
                    `);
                });

                $jadwalSelect.prop("disabled", false);
            })
            .catch((error) => {
                console.error("Error load jadwal (update):", error);
                $jadwalSelect.prop("disabled", true);
                $infoJadwal
                    .removeClass("hidden")
                    .text("Gagal memuat jadwal dokter.");
            });
    });

    /* =========================
     *  PILIH JADWAL → SET DOKTER_ID (UPDATE)
     * ========================= */
    $("#jadwal_dokter_id_update").on("change", function () {
        const opt = $(this).find("option:selected");
        const dokterId = opt.data("dokter-id") || "";
        const namaDokter = opt.data("nama-dokter") || "";
        const jamAwal = opt.data("jam-awal") || "";
        const jamSelesai = opt.data("jam-selesai") || "";

        $("#dokter_id_update").val(dokterId);

        if (dokterId) {
            $("#info_jadwal_dokter_update")
                .removeClass("hidden")
                .text(`Dokter: ${namaDokter} (${jamAwal} - ${jamSelesai})`);
        } else {
            $("#info_jadwal_dokter_update").addClass("hidden").text("");
        }
    });

    /* =========================
     *  SUBMIT FORM UPDATE
     * ========================= */
    $formEdit.on("submit", function (e) {
        e.preventDefault();
        const url = $formEdit.data("url");

        // Kumpulkan item layanan
        const items = [];
        $itemsWrapper.find(".order-item").each(function () {
            const $row = $(this);

            const layananId = $row.find(".layanan-select").val();
            if (!layananId) return; // skip baris kosong

            const kategoriId = $row.find(".kategori-id-input").val();
            const kategoriNama = $row.find(".kategori-nama-input").val();
            const qty = $row.find(".jumlah-input").val() || 1;
            const subtotal = cleanRupiah($row.find(".subtotal-input").val());

            items.push({
                layanan_id: layananId,
                kategori_layanan_id: kategoriId,
                kategori_layanan_nama: kategoriNama,
                jumlah: qty,
                total_tagihan: subtotal,
            });
        });

        const hasPemeriksaan = items.some(
            (item) => item.kategori_layanan_nama === "Pemeriksaan"
        );

        const anchorItem = items.length ? items[0] : null;

        const formData = {
            order_layanan_id: $("#order_layanan_id_update").val(),
            pasien_id: $("#pasien_id_update").val(),
            items: items,
            total_tagihan: cleanRupiah($("#total_tagihan_update").val()),
        };

        // Biar kompatibel dengan backend yang masih pakai field tunggal
        if (anchorItem) {
            formData.layanan_id = anchorItem.layanan_id;
            formData.kategori_layanan_id = anchorItem.kategori_layanan_id;
            formData.jumlah = anchorItem.jumlah;
        }

        if (hasPemeriksaan) {
            formData.poli_id = $("#poli_id_select_update").val();
            formData.jadwal_dokter_id = $("#jadwal_dokter_id_update").val();
        }

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
                if ($("#orderLayanan").length) {
                    $("#orderLayanan").DataTable().ajax.reload(null, false);
                }
            })
            .catch((error) => {
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors || {};

                    $formEdit.find(".is-invalid").removeClass("is-invalid");
                    $formEdit.find(".text-red-600").empty().css("opacity", 0);

                    let hasItemError = false;

                    for (const field in errors) {
                        const msg = errors[field][0];

                        if (field === "pasien_id") {
                            $pasienSearch.addClass("is-invalid");
                            $pasienError.text(msg).css("opacity", 1);
                        } else if (field === "poli_id") {
                            if (poliTomUpdate) {
                                $(poliTomUpdate.control).addClass("is-invalid");
                            } else {
                                $("#poli_id_select_update").addClass(
                                    "is-invalid"
                                );
                            }
                            $("#poli_id_update-error")
                                .text(msg)
                                .css("opacity", 1);
                        } else if (field === "jadwal_dokter_id") {
                            $("#jadwal_dokter_id_update").addClass(
                                "is-invalid"
                            );
                            $("#jadwal_dokter_id_update-error")
                                .text(msg)
                                .css("opacity", 1);
                        } else if (field.startsWith("items")) {
                            hasItemError = true;
                        } else if (field === "order_layanan_id") {
                            // kalau masih error id, highlight saja (kalau mau ditambahin elemen error)
                            console.warn("Validasi order_layanan_id:", msg);
                        }
                    }

                    if (hasItemError) {
                        Swal.fire({
                            icon: "error",
                            title: "Validasi Layanan",
                            text: "Periksa kembali data layanan (layanan, jumlah, dll).",
                        });
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Server Error",
                        text: "Terjadi kesalahan saat mengupdate data.",
                    });
                }
            });
    });
});

/* ============================================================
 *  DELETE ORDER LAYANAN
 * ============================================================ */
$(function () {
    $("body").on("click", ".btn-delete-order-layanan", function () {
        const kodeTransaksi = $(this).data("kode-transaksi");

        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, hapus!",
            cancelButtonText: "Batal",
        }).then((result) => {
            if (result.isConfirmed) {
                axios
                    .post(
                        `/order-layanan/delete-data-order-layanan/${kodeTransaksi}`
                    )
                    .then((response) => {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: response.data.message,
                            timer: 1500,
                            showConfirmButton: false,
                        });

                        $("#orderLayanan").DataTable().ajax.reload(null, false);
                    })
                    .catch((error) => {
                        Swal.fire({
                            icon: "error",
                            title: "Server Error!",
                            text:
                                error.response?.data?.message ||
                                "Terjadi kesalahan saat menghapus data.",
                        });
                    });
            }
        });
    });
});
