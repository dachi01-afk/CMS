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
 *  MODAL CREATE ORDER LAYANAN
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
        $("#kategori_layanan_nama_create").val("");
        $("#kategori_layanan_id_create").val("");
        $("#jumlah_create").val(1);
        $("#total_tagihan_create").val("");

        // Poli & jadwal
        const $sectionPoliJadwal = $("#section_poli_jadwal_create");
        $sectionPoliJadwal.addClass("hidden");

        $("#poli_id_select_create").val("");
        $("#poli_id_create-error").text("").css("opacity", 0);

        $("#jadwal_dokter_id_create")
            .empty()
            .append('<option value="">-- Pilih Jadwal Dokter --</option>')
            .prop("disabled", true);
        $("#jadwal_dokter_id_create-error").text("").css("opacity", 0);
        $("#dokter_id_create").val("");
        $("#info_jadwal_dokter_create").addClass("hidden").text("");
    }

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
     *  LAYANAN → KATEGORI + HARGA + SHOW/HIDE POLI/JADWAL
     * ========================= */
    $("#layanan_id_create").on("change", function () {
        const selected = $(this).find("option:selected");

        const kategoriId = selected.data("kategori-id") || "";
        const kategoriNama = selected.data("kategori-nama") || "";
        const harga = parseFloat(selected.data("harga") || 0);

        $("#kategori_layanan_id_create").val(kategoriId);
        $("#kategori_layanan_nama_create").val(kategoriNama);

        const qty = $("#jumlah_create").val() || 1;
        const total = harga * qty;
        $("#total_tagihan_create").val(toRupiah(total));

        const $sectionPoliJadwal = $("#section_poli_jadwal_create");

        if (kategoriNama === "Pemeriksaan") {
            $sectionPoliJadwal.removeClass("hidden");

            $("#poli_id_select_create").val("");
            $("#jadwal_dokter_id_create")
                .empty()
                .append('<option value="">-- Pilih Jadwal Dokter --</option>')
                .prop("disabled", true);
            $("#dokter_id_create").val("");
            $("#info_jadwal_dokter_create").addClass("hidden").text("");
        } else {
            $sectionPoliJadwal.addClass("hidden");
            $("#poli_id_select_create").val("");
            $("#jadwal_dokter_id_create")
                .empty()
                .append('<option value="">-- Pilih Jadwal Dokter --</option>')
                .prop("disabled", true);
            $("#dokter_id_create").val("");
            $("#info_jadwal_dokter_create").addClass("hidden").text("");
        }
    });

    /* =========================
     *  JUMLAH → UPDATE TOTAL
     * ========================= */
    $("#jumlah_create").on("input", function () {
        const qty = parseInt($(this).val() || 1, 10);
        const selected = $("#layanan_id_create").find("option:selected");
        const harga = parseFloat(selected.data("harga") || 0);

        const total = harga * qty;
        $("#total_tagihan_create").val(toRupiah(total));
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

        const kategoriNama = $("#kategori_layanan_nama_create").val();

        const formData = {
            pasien_id: $("#pasien_id_create").val(),
            layanan_id: $("#layanan_id_create").val(),
            kategori_layanan_id: $("#kategori_layanan_id_create").val(),
            jumlah: $("#jumlah_create").val(),
            // kirim angka murni ke backend
            total_tagihan: cleanRupiah($("#total_tagihan_create").val()),
        };

        if (kategoriNama === "Pemeriksaan") {
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
                    const errors = error.response.data.errors || {};

                    $formAdd.find(".is-invalid").removeClass("is-invalid");
                    $formAdd.find(".text-red-600").empty().css("opacity", 0);

                    for (const field in errors) {
                        const msg = errors[field][0];

                        if (field === "pasien_id") {
                            $pasienSearch.addClass("is-invalid");
                            $pasienError.text(msg).css("opacity", 1);
                        } else if (field === "poli_id") {
                            $("#poli_id_select_create").addClass("is-invalid");
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
                        } else {
                            const fieldId = `#${field}_create`;
                            const errId = `#${field}_create-error`;
                            $(fieldId).addClass("is-invalid");
                            $(errId).text(msg).css("opacity", 1);
                        }
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
 *  MODAL UPDATE ORDER LAYANAN
 * ============================================================ */
$(function () {
    const editModalEl = document.getElementById("modalUpdateOrderLayanan");
    const editModal = editModalEl ? new Modal(editModalEl) : null;
    const $formEdit = $("#formUpdateOrderLayanan");

    const $sectionPoliJadwal = $("#section_poli_jadwal_update");
    const $poliSelect = $("#poli_id_select_update");
    const $jadwalSelect = $("#jadwal_dokter_id_update");
    const $infoJadwal = $("#info_jadwal_dokter_update");

    // Helper: format rupiah
    function toRupiah(value) {
        const number = Number(value || 0);
        return "Rp " + number.toLocaleString("id-ID");
    }

    // Helper: bersihkan rupiah → angka murni
    function cleanRupiah(value) {
        if (!value) return 0;
        const angkaBersih = String(value).replace(/[^0-9]/g, "");
        return Number(angkaBersih) || 0;
    }

    function resetEditForm() {
        if ($formEdit[0]) {
            $formEdit[0].reset();
        }

        $formEdit.find(".is-invalid").removeClass("is-invalid");
        $formEdit
            .find(".text-red-600")
            .empty()
            .removeClass("opacity-100")
            .addClass("opacity-0");

        $("#pasien_nama_info_update").text("-");
        $("#pasien_no_emr_info_update").text("-");
        $("#pasien_jk_info_update").text("-");
        $("#pasien_info_update").addClass("hidden");

        // poli/jadwal
        $sectionPoliJadwal.addClass("hidden");
        $poliSelect.val("");
        $jadwalSelect
            .empty()
            .append('<option value="">-- Pilih Jadwal Dokter --</option>')
            .prop("disabled", true);
        $("#dokter_id_update").val("");
        $infoJadwal.addClass("hidden").text("");
    }

    // ========= BUKA MODAL EDIT =========
    $("body").on("click", ".btn-update-order-layanan", function () {
        resetEditForm();

        const id = $(this).data("id");

        axios
            .get(`/order-layanan/get-data-order-layanan/${id}`)
            .then((response) => {
                const order = response.data.data;

                $("#id_update").val(order.id);

                // --- PASIEN ---
                const $pasienSelect = $("#pasien_id_update");
                $pasienSelect.empty();
                if (order.pasien_id) {
                    $pasienSelect.append(
                        `<option value="${order.pasien_id}">
                            ${order.nama_pasien || "-"}
                        </option>`
                    );
                }
                $pasienSelect.val(order.pasien_id);

                $("#pasien_nama_info_update").text(order.nama_pasien || "-");
                $("#pasien_no_emr_info_update").text(
                    order.pasien_no_emr || "-"
                );
                $("#pasien_jk_info_update").text(
                    order.pasien_jenis_kelamin || "-"
                );
                $("#pasien_info_update").removeClass("hidden");

                // --- LAYANAN & KATEGORI ---
                const $layananSelect = $("#layanan_id_update");
                if (
                    order.layanan_id &&
                    $layananSelect.find(`option[value="${order.layanan_id}"]`)
                        .length === 0
                ) {
                    // fallback kalau option belum ada
                    $layananSelect.prepend(
                        `<option value="${order.layanan_id}">
                            ${order.nama_layanan || "-"}
                        </option>`
                    );
                }
                $layananSelect.val(order.layanan_id);

                $("#kategori_layanan_id_update").val(order.kategori_layanan_id);
                $("#kategori_layanan_nama_update").val(
                    order.kategori_layanan || ""
                );

                // --- JUMLAH & TOTAL ---
                const qty = Number(order.jumlah || 1);
                $("#jumlah_update").val(qty);

                // ambil harga dari option terpilih
                const $selectedOpt = $layananSelect.find(
                    `option[value="${order.layanan_id}"]`
                );
                const harga = Number($selectedOpt.data("harga") || 0);

                // kalau total_tagihan di DB > 0 pakai itu,
                // kalau 0/null hitung dari harga * qty
                const totalFromDb = Number(order.total_tagihan || 0);
                const total = totalFromDb > 0 ? totalFromDb : harga * qty;

                // tampilkan dalam format Rupiah
                $("#total_tagihan_update").val(toRupiah(total));

                // --- POLI & JADWAL kalau Pemeriksaan ---
                const isPemeriksaan =
                    (order.kategori_layanan || "") === "Pemeriksaan";

                if (isPemeriksaan) {
                    $sectionPoliJadwal.removeClass("hidden");

                    const poliIdFromOrder =
                        order.poli_id || order.kunjungan_poli_id || "";
                    const jadwalIdFromOrder = order.jadwal_dokter_id || "";

                    if (poliIdFromOrder) {
                        $poliSelect.val(poliIdFromOrder);

                        axios
                            .get(
                                "/order-layanan/get-data-jadwal-dokter-hari-ini",
                                {
                                    params: { poli_id: poliIdFromOrder },
                                }
                            )
                            .then((res) => {
                                const list = res.data.data || [];
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

                                if (jadwalIdFromOrder) {
                                    $jadwalSelect.val(jadwalIdFromOrder);
                                    const opt =
                                        $jadwalSelect.find("option:selected");
                                    const dokterId =
                                        opt.data("dokter-id") || "";
                                    const namaDokter =
                                        opt.data("nama-dokter") || "";
                                    const jamAwal = opt.data("jam-awal") || "";
                                    const jamSelesai =
                                        opt.data("jam-selesai") || "";

                                    $("#dokter_id_update").val(dokterId);
                                    if (dokterId) {
                                        $infoJadwal
                                            .removeClass("hidden")
                                            .text(
                                                `Dokter: ${namaDokter} (${jamAwal} - ${jamSelesai})`
                                            );
                                    }
                                }
                            })
                            .catch((err) => {
                                console.error(
                                    "Error load jadwal (update):",
                                    err
                                );
                            });
                    }
                } else {
                    $sectionPoliJadwal.addClass("hidden");
                }

                editModal?.show();
            })
            .catch((error) => {
                console.error(error);
                Swal.fire({
                    icon: "error",
                    title: "Gagal Memuat Data!",
                });
            });
    });

    // ========= JUMLAH → UPDATE TOTAL =========
    $("#jumlah_update").on("input", function () {
        const qty = parseInt($(this).val(), 10) || 1;
        const $selected = $("#layanan_id_update").find("option:selected");
        const harga = Number($selected.data("harga") || 0);
        const total = harga * qty;
        $("#total_tagihan_update").val(toRupiah(total));
    });

    // ========= LAYANAN → KATEGORI + TOTAL + SHOW/HIDE POLI/JADWAL =========
    $("#layanan_id_update").on("change", function () {
        const $selected = $(this).find("option:selected");

        const kategoriId = $selected.data("kategori-id") || "";
        const kategoriNama = $selected.data("kategori-nama") || "";
        const harga = Number($selected.data("harga") || 0);
        const qty = parseInt($("#jumlah_update").val(), 10) || 1;

        $("#kategori_layanan_id_update").val(kategoriId);
        $("#kategori_layanan_nama_update").val(kategoriNama);
        $("#total_tagihan_update").val(toRupiah(harga * qty));

        if (kategoriNama === "Pemeriksaan") {
            $sectionPoliJadwal.removeClass("hidden");

            $poliSelect.val("");
            $jadwalSelect
                .empty()
                .append('<option value="">-- Pilih Jadwal Dokter --</option>')
                .prop("disabled", true);
            $("#dokter_id_update").val("");
            $infoJadwal.addClass("hidden").text("");
        } else {
            $sectionPoliJadwal.addClass("hidden");
            $poliSelect.val("");
            $jadwalSelect
                .empty()
                .append('<option value="">-- Pilih Jadwal Dokter --</option>')
                .prop("disabled", true);
            $("#dokter_id_update").val("");
            $infoJadwal.addClass("hidden").text("");
        }
    });

    // ========= POLI → LOAD JADWAL DOKTER =========
    $poliSelect.on("change", function () {
        const poliId = $(this).val();

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
            .then((res) => {
                const list = res.data.data || [];

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
            .catch((err) => {
                console.error("Error load jadwal (update):", err);
                $jadwalSelect.prop("disabled", true);
                $infoJadwal
                    .removeClass("hidden")
                    .text("Gagal memuat jadwal dokter.");
            });
    });

    // ========= PILIH JADWAL → SET DOKTER_ID =========
    $jadwalSelect.on("change", function () {
        const opt = $(this).find("option:selected");
        const dokterId = opt.data("dokter-id") || "";
        const namaDokter = opt.data("nama-dokter") || "";
        const jamAwal = opt.data("jam-awal") || "";
        const jamSelesai = opt.data("jam-selesai") || "";

        $("#dokter_id_update").val(dokterId);

        if (dokterId) {
            $infoJadwal
                .removeClass("hidden")
                .text(`Dokter: ${namaDokter} (${jamAwal} - ${jamSelesai})`);
        } else {
            $infoJadwal.addClass("hidden").text("");
        }
    });

    // ========= SUBMIT UPDATE =========
    $formEdit.on("submit", function (e) {
        e.preventDefault();

        const url = $formEdit.data("url");
        const kategoriNama = $("#kategori_layanan_nama_update").val();

        const formData = {
            id: $("#id_update").val(),
            pasien_id: $("#pasien_id_update").val(),
            layanan_id: $("#layanan_id_update").val(),
            kategori_layanan_id: $("#kategori_layanan_id_update").val(),
            jumlah: $("#jumlah_update").val(),
            // kirim angka murni
            total_tagihan: cleanRupiah($("#total_tagihan_update").val()),
            _method: "POST",
        };

        if (kategoriNama === "Pemeriksaan") {
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
                    $formEdit
                        .find(".text-red-600")
                        .empty()
                        .removeClass("opacity-100")
                        .addClass("opacity-0");

                    for (const field in errors) {
                        if (field === "poli_id") {
                            $("#poli_id_select_update").addClass("is-invalid");
                            $("#poli_id_update-error")
                                .html(errors[field][0])
                                .removeClass("opacity-0")
                                .addClass("opacity-100");
                        } else if (field === "jadwal_dokter_id") {
                            $("#jadwal_dokter_id_update").addClass(
                                "is-invalid"
                            );
                            $("#jadwal_dokter_id_update-error")
                                .html(errors[field][0])
                                .removeClass("opacity-0")
                                .addClass("opacity-100");
                        } else {
                            const fieldId = `#${field}_update`;
                            const errorId = `#${field}_update-error`;
                            $(fieldId).addClass("is-invalid");
                            $(errorId)
                                .html(errors[field][0])
                                .removeClass("opacity-0")
                                .addClass("opacity-100");
                        }
                    }
                } else {
                    console.error(error);
                    Swal.fire({
                        icon: "error",
                        title: "Gagal Update!",
                    });
                }
            });
    });

    // ========= TUTUP MODAL =========
    $("#buttonCloseModalUpdateOrderLayanan").on("click", function () {
        editModal?.hide();
        resetEditForm();
    });

    $("#buttonCancleModalUpdateOrderLayanan").on("click", function () {
        editModal?.hide();
        resetEditForm();
    });
});

/* ============================================================
 *  DELETE ORDER LAYANAN
 * ============================================================ */
$(function () {
    $("body").on("click", ".btn-delete-order-layanan", function () {
        const id = $(this).data("id");

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
                    .post(`/order-layanan/delete-data-order-layanan/${id}`)
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
