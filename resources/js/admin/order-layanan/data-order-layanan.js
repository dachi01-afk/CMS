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
                "bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600",
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
            } data (Halaman ${currentPage} dari ${totalPages})`,
        );
        $pagination.empty();

        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(
            `<li><a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 ${prevDisabled}">Previous</a></li>`,
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
                `<li><a href="#" class="page-number flex items-center justify-center px-3 h-8 border ${active}" data-page="${i}">${i}</a></li>`,
            );
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(
            `<li><a href="#" id="btnNext" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 ${nextDisabled}">Next</a></li>`,
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
 *  - POLI TomSelect (searchable)
 *  - Filter poli berdasarkan layanan_poli pivot + is_global
 *  - Semua data master diambil via API (render di JS)
 * ============================================================ */
$(function () {
    const addModalEl = document.getElementById("modalCreateOrderLayanan");
    const addModal = addModalEl ? new Modal(addModalEl) : null;
    const $formAdd = $("#formCreateOrderLayanan");

    // ==========================
    // API ENDPOINTS (ubah sesuai route kamu)
    // ==========================
    const API_MASTER = {
        poliAll: "/order-layanan/get-data-poli",
        searchPasien: "/order-layanan/get-data-pasien",
        jadwalHariIni: "/order-layanan/get-data-jadwal-dokter-hari-ini",
    };

    // ==========================
    // ELEMENTS
    // ==========================
    const $pasienSearch = $("#pasien_search_create");
    const $pasienId = $("#pasien_id_create");
    const $pasienResults = $("#pasien_search_results_create");
    const $pasienError = $("#pasien_id_create-error");
    const $pasienInfoCard = $("#pasien_info_create");
    const $pasienNamaInfo = $("#pasien_nama_info_create");
    const $pasienEmrInfo = $("#pasien_no_emr_info_create");
    const $pasienJkInfo = $("#pasien_jk_info_create");

    const $itemsWrapper = $("#orderItemsWrapper");
    const $itemTemplate = $("#orderItemTemplate");

    const $sectionPoliJadwal = $("#section_poli_jadwal_create");

    const $poliSelect = $("#poli_id_select_create");
    const $jadwalSelect = $("#jadwal_dokter_id_create");
    const $infoJadwal = $("#info_jadwal_dokter_create");

    // ==========================
    // MASTER CACHE (render di JS)
    // ==========================
    let masterLoaded = false;
    let poliAll = []; // [{id,nama_poli}]
    let layananPoliMeta = {}; // { [layananId]: {is_global:boolean, poli_ids:number[]} }

    // TomSelect instance
    let poliTom = null;

    // current allowed poli set:
    // null => semua poli boleh
    // Set => hanya yang ada di set
    let currentAllowedPoliSet = null;

    // ==========================
    // Helpers Rupiah
    // ==========================
    function toRupiah(value) {
        const number = Number(value || 0);
        return "Rp " + number.toLocaleString("id-ID");
    }

    function toNumber(val) {
        if (val === null || val === undefined || val === "") return 0;
        if (typeof val === "number") return val;

        // Hapus simbol mata uang dan spasi
        let s = String(val).replace(/Rp\s?/g, "");

        // Jika formatnya Indonesia (15.000), hapus titik ribuan
        // Tapi hati-hati jangan hapus koma desimal jika ada
        s = s.replace(/\./g, "");

        const num = parseFloat(s);
        return isNaN(num) ? 0 : num;
    }

    function cleanRupiah(value) {
        if (!value) return 0;
        const angkaBersih = String(value).replace(/[^0-9]/g, "");
        return Number(angkaBersih) || 0;
    }

    function showFieldError($el, $errEl, msg) {
        $el.addClass("is-invalid");
        $errEl.text(msg).css("opacity", 1);
    }

    function clearFieldError($el, $errEl) {
        $el.removeClass("is-invalid");
        $errEl.text("").css("opacity", 0);
    }

    // ==========================
    // MASTER LOAD (ambil data ke JS)
    // ==========================
    async function ensureMasterLoaded() {
        if (masterLoaded) return;

        try {
            const res = await axios.get(API_MASTER.poliAll);

            console.log("Poli Master Loaded:", res.data);

            poliAll = (poliRes.data?.data || []).map((p) => ({
                id: Number(p.id),
                nama_poli: String(p.nama_poli || ""),
            }));

            masterLoaded = true;

            initPoliTomSelect(); // init sekali
            fillPoliOptions(poliAll); // default tampil semua poli
        } catch (e) {
            masterLoaded = false;
            console.error(e);
            // kalau master gagal, tetap biarkan select normal agar tidak blank total
            $("#poli_id_create-error")
                .text("Gagal memuat master poli. Coba refresh halaman.")
                .css("opacity", 1);
        }
    }

    // ==========================
    // TomSelect INIT + RENDER OPTIONS
    // ==========================
    function initPoliTomSelect() {
        if (!$poliSelect.length || typeof TomSelect === "undefined") return;
        if (poliTom) return; // jangan init ulang

        poliTom = new TomSelect("#poli_id_select_create", {
            placeholder: "-- Pilih Poli --",
            allowEmptyOption: true,
            maxItems: 1,

            // ✅ biar seperti foto: klik langsung tampil list
            openOnFocus: true,
            maxOptions: 500, // biar tidak kepotong 50 default
            selectOnTab: true,
            closeAfterSelect: true,

            create: false, // poli master, jadi TIDAK bikin poli baru
            persist: false,

            // ✅ aman di modal
            dropdownParent: addModalEl,

            plugins: ["clear_button"],

            render: {
                option: function (data, escape) {
                    // sembunyikan option kosong dari dropdown list
                    if (String(data.value) === "") return "";
                    return `<div class="py-1.5 px-2">${escape(
                        data.text,
                    )}</div>`;
                },
                item: function (data, escape) {
                    return `<div>${escape(data.text)}</div>`;
                },
            },
            onInitialize() {
                // kecil-kecilan supaya styling lebih nyatu tailwind (optional)
                this.control.classList.add("!rounded-lg", "!min-h-[42px]");
            },
        });

        // ✅ cukup satu jalur event (native change juga akan ke-trigger)
        poliTom.on("change", function () {
            $("#poli_id_select_create").trigger("change");
        });

        // ✅ supaya begitu fokus langsung open (kadang openOnFocus masih “malu-malu” kalau modal baru tampil)
        poliTom.on("focus", function () {
            if (!this.isOpen) this.open();
        });
    }

    function fillPoliOptions(list) {
        // Jika list kosong tapi kita ingin menampilkan semua, pakai poliAll
        const safeList =
            Array.isArray(list) && list.length > 0 ? list : poliAll || [];

        if (!poliTom) {
            $poliSelect
                .prop("disabled", false)
                .empty()
                .append('<option value="">-- Pilih Poli --</option>');
            safeList.forEach((p) => {
                $poliSelect.append(
                    `<option value="${p.id}">${p.nama_poli}</option>`,
                );
            });
            return;
        }

        const currentVal = poliTom.getValue();
        poliTom.enable();
        poliTom.clearOptions();

        // Re-render options
        poliTom.addOption({ value: "", text: "-- Pilih Poli --" });
        safeList.forEach((p) => {
            poliTom.addOption({ value: String(p.id), text: p.nama_poli });
        });

        poliTom.refreshOptions(false);

        // Restore nilai jika masih valid di list yang baru
        if (currentVal && poliTom.options[currentVal]) {
            poliTom.setValue(currentVal, true);
        } else {
            poliTom.clear(true);
        }
    }

    function lockPoliTo(poliId, message) {
        if (poliTom) {
            // pastikan option ada
            const found = poliAll.find((p) => String(p.id) === String(poliId));
            if (found) {
                poliTom.addOption({
                    value: String(found.id),
                    text: found.nama_poli,
                });
            }
            poliTom.setValue(String(poliId), true);
            poliTom.disable();
        } else {
            $poliSelect.val(String(poliId)).prop("disabled", true);
        }

        if (message) {
            $("#poli_id_create-error").text(message).css("opacity", 1);
        }
    }

    function unlockPoli() {
        if (poliTom) {
            poliTom.enable();
        } else {
            $poliSelect.prop("disabled", false);
        }
        $("#poli_id_create-error").text("").css("opacity", 0);
    }

    // ==========================
    // Logic: Poli allowed by selected layanan pemeriksaan
    // ==========================
    function hasAnyPemeriksaanRow() {
        return (
            $itemsWrapper.find(".kategori-nama-input").filter(function () {
                return $(this).val() === "Pemeriksaan";
            }).length > 0
        );
    }

    function getSelectedPemeriksaanLayananIds() {
        const ids = [];
        $itemsWrapper.find(".order-item").each(function () {
            const $row = $(this);
            const layananId = $row.find(".layanan-select").val();
            const kategoriNama = $row.find(".kategori-nama-input").val();

            if (layananId && kategoriNama === "Pemeriksaan") {
                ids.push(String(layananId));
            }
        });
        return [...new Set(ids)];
    }

    // aturan:
    // - jika ada layanan pemeriksaan yang is_global=true => null (all poli)
    // - else intersection poli_ids dari semua layanan pemeriksaan
    function computeAllowedPoliSet() {
        const layananIds = getSelectedPemeriksaanLayananIds();
        if (!layananIds.length) return null;

        // kalau ada is_global true => semua poli
        for (const id of layananIds) {
            const meta = layananPoliMeta[id];
            if (meta && meta.is_global) return null;
        }

        let allowed = null; // Set
        for (const id of layananIds) {
            const meta = layananPoliMeta[id] || {
                is_global: false,
                poli_ids: [],
            };
            const s = new Set((meta.poli_ids || []).map(Number));

            if (allowed === null) allowed = s;
            else allowed = new Set([...allowed].filter((x) => s.has(x)));
        }

        return allowed ?? new Set();
    }

    function resetJadwalUI() {
        $jadwalSelect
            .empty()
            .append('<option value="">-- Pilih Jadwal Dokter --</option>')
            .prop("disabled", true);

        $("#dokter_id_create").val("");
        $infoJadwal.addClass("hidden").text("");
        $("#jadwal_dokter_id_create-error").text("").css("opacity", 0);
    }

    async function applyPoliFilter() {
        if (!hasAnyPemeriksaanRow()) {
            currentAllowedPoliSet = null;
            unlockPoli();
            fillPoliOptions(poliAll);
            if (poliTom) poliTom.clear(true);
            $("#poli_id_create-error").css("opacity", 0); // Bersihkan error
            resetJadwalUI();
            return;
        }

        const layananIds = getSelectedPemeriksaanLayananIds();

        try {
            const res = await axios.get(API_MASTER.poliAll, {
                params: { layanan_id: layananIds },
            });

            const filtered = res.data?.data || [];
            const mode = res.data?.mode;

            resetJadwalUI();
            $("#poli_id_create-error").css("opacity", 0); // Sembunyikan error setiap ada update

            // Mode ALL: Layanan is_global = true (Seperti di foto ke-4)
            if (mode === "all") {
                currentAllowedPoliSet = null; // Reset filter
                unlockPoli();
                fillPoliOptions(filtered.length > 0 ? filtered : poliAll);

                if (poliTom && !poliTom.getValue()) {
                    poliTom.open();
                }
                return;
            }

            // Mode FILTERED: Layanan is_global = false
            currentAllowedPoliSet = new Set(filtered.map((p) => Number(p.id)));

            if (!filtered.length) {
                if (poliTom) {
                    poliTom.clear(true);
                    poliTom.disable();
                }
                $("#poli_id_create-error")
                    .text("Poli tidak tersedia untuk layanan ini.")
                    .css("opacity", 1);
                return;
            }

            unlockPoli();
            fillPoliOptions(filtered);

            if (filtered.length === 1) {
                const poliId = filtered[0].id;

                // Pilih dan Kunci Poli
                lockPoliTo(poliId, "Poli otomatis dipilih (khusus).");

                // KRITIKAL: Trigger change manual agar Jadwal Dokter ikut ter-load
                if (poliTom) {
                    poliTom.setValue(String(poliId));
                } else {
                    $poliSelect.val(poliId).trigger("change");
                }
                return;
            }

            const currentVal = poliTom ? poliTom.getValue() : $poliSelect.val();
            const ok =
                currentVal && currentAllowedPoliSet.has(Number(currentVal));

            if (!ok) {
                if (poliTom) poliTom.clear(true);
            }

            if (poliTom) poliTom.open();
        } catch (e) {
            console.error("Gagal filter poli:", e);
        }
    }

    function togglePoliSection() {
        if (hasAnyPemeriksaanRow()) {
            $sectionPoliJadwal.removeClass("hidden");
        } else {
            $sectionPoliJadwal.addClass("hidden");

            // reset poli
            if (poliTom) {
                poliTom.enable();
                poliTom.clear(true);
            } else {
                $poliSelect.prop("disabled", false).val("");
            }
            $("#poli_id_create-error").text("").css("opacity", 0);

            // reset jadwal
            resetJadwalUI();
        }
    }

    // ==========================
    // Items (Layanan rows)
    // ==========================
    function recalcGrandTotal() {
        let grand = 0;
        $itemsWrapper.find(".order-item").each(function () {
            const sub = cleanRupiah($(this).find(".subtotal-input").val());
            grand += sub;
        });
        $("#total_tagihan_create").val(toRupiah(grand));
    }

    function addItemRow() {
        const $row = $itemTemplate.clone();
        $row.removeAttr("id");
        $itemsWrapper.append($row);

        // trigger change untuk hitung kalau ada default selected
        $row.find(".layanan-select").trigger("change");

        recalcGrandTotal();
        togglePoliSection();
        applyPoliFilter();
    }

    // ==========================
    // Reset Form
    // ==========================
    function resetAddForm() {
        if ($formAdd[0]) $formAdd[0].reset();

        $formAdd.find(".is-invalid").removeClass("is-invalid");
        $formAdd.find(".text-red-600").empty().css("opacity", 0);

        // pasien
        $pasienSearch.val("");
        $pasienId.val("");
        $pasienResults.empty().addClass("hidden");
        $pasienInfoCard.addClass("hidden");
        $pasienNamaInfo.text("-");
        $pasienEmrInfo.text("-");
        $pasienJkInfo.text("-");

        // items
        $itemsWrapper.empty();
        addItemRow(); // minimal 1 row
        $("#total_tagihan_create").val("");

        // poli + jadwal
        $sectionPoliJadwal.addClass("hidden");
        resetJadwalUI();

        // reset poli to all (but master must be loaded)
        if (masterLoaded) {
            unlockPoli();
            fillPoliOptions(poliAll);
            if (poliTom) poliTom.clear(true);
            else $poliSelect.val("");
        } else {
            // kalau master belum, biarkan select existing
            if (poliTom) poliTom.clear(true);
        }
    }

    // ==========================
    // Open / Close modal
    // ==========================
    $("#buttonOpenModalCreateOrderLayanan").on("click", async function () {
        await ensureMasterLoaded();
        resetAddForm();
        addModal?.show();

        // ✅ fix: kalau modal baru tampil (awal hidden), tomselect perlu refresh biar dropdown normal
        setTimeout(() => {
            if (poliTom) {
                poliTom.refreshOptions(false);
            }
        }, 50);
    });

    $(
        "#buttonCloseModalCreateOrderLayanan, #buttonCancaleModalCreateOrderLayanan",
    ).on("click", function () {
        addModal?.hide();
        resetAddForm();
    });

    // tambah row
    $("#btnAddLayananRow").on("click", function (e) {
        e.preventDefault();
        addItemRow();
    });

    // hapus row
    $formAdd.on("click", ".btn-remove-item", function (e) {
        e.preventDefault();
        const $row = $(this).closest(".order-item");
        const count = $itemsWrapper.find(".order-item").length;

        if (count <= 1) {
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
        applyPoliFilter();
    });

    // ==========================
    // Search Pasien
    // ==========================
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
                .get(API_MASTER.searchPasien, { params: { q: keyword } })
                .then((response) => {
                    const list = response.data.data || [];
                    $pasienResults.empty();

                    if (!list.length) {
                        $pasienResults
                            .append(
                                `<div class="px-3 py-2 text-xs text-gray-500 dark:text-gray-300">Tidak ada pasien ditemukan.</div>`,
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
                .catch(() => {
                    $pasienResults
                        .empty()
                        .append(
                            `<div class="px-3 py-2 text-xs text-red-500">Terjadi kesalahan saat mencari pasien.</div>`,
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
        $pasienError.text("").css("opacity", 0);
    });

    $(document).on("click", function (e) {
        if (
            !$(e.target).closest("#pasien_search_create").length &&
            !$(e.target).closest("#pasien_search_results_create").length
        ) {
            $pasienResults.addClass("hidden");
        }
    });

    // ==========================
    // Layanan -> kategori + subtotal
    // ==========================
    $formAdd.on("change", ".layanan-select", function () {
        const $row = $(this).closest(".order-item");
        const selected = $(this).find("option:selected");

        const kategoriId = selected.data("kategori-id") || "";
        const kategoriNama = selected.data("kategori-nama") || "";

        // Ambil harga mentah. Paksa ke Number agar tidak ada titik/koma string
        const harga = Number(selected.data("harga")) || 0;

        $row.find(".kategori-id-input").val(kategoriId);
        $row.find(".kategori-nama-input").val(kategoriNama);

        // Simpan harga asli di element input jumlah untuk referensi saat ngetik qty
        $row.find(".jumlah-input").data("harga-asli", harga);

        const qty = parseInt($row.find(".jumlah-input").val() || 1, 10);
        const subtotal = harga * qty;

        $row.find(".subtotal-input").val(toRupiah(subtotal));

        recalcGrandTotal();
        togglePoliSection();
        applyPoliFilter();
    });

    // jumlah -> update subtotal
    $formAdd.on("input", ".jumlah-input", function () {
        const $row = $(this).closest(".order-item");

        // Ambil harga dari data-attribute yang disimpan saat pilih layanan (LEBIH AMAN)
        let harga = $(this).data("harga-asli");

        // Fallback jika user langsung ngetik tanpa ganti layanan
        if (harga === undefined) {
            const selected = $row.find(".layanan-select option:selected");
            harga = Number(selected.data("harga")) || 0;
        }

        let qty = parseInt($(this).val());

        // Proteksi jika input kosong atau bukan angka
        if (isNaN(qty) || qty < 1) {
            qty = 0;
        }

        const subtotal = harga * qty;
        $row.find(".subtotal-input").val(toRupiah(subtotal));
        recalcGrandTotal();
    });

    // ==========================
    // Poli -> load jadwal dokter
    // ==========================
    // Gunakan event 'change' dari TomSelect jika ada,
    // atau pastikan listener jQuery ini menangkap perubahan dari TomSelect
    $("#poli_id_select_create").on("change", function () {
        const poliId = $(this).val();

        // Reset Dropdown Jadwal
        $jadwalSelect
            .empty()
            .append('<option value="">-- Pilih Jadwal Dokter --</option>');
        $("#dokter_id_create").val("");
        $infoJadwal.addClass("hidden").text("");
        $("#jadwal_dokter_id_create-error").css("opacity", 0);

        if (!poliId) {
            $jadwalSelect.prop("disabled", true);
            return;
        }

        // Tampilkan loading state (opsional)
        $infoJadwal.removeClass("hidden").text("Memuat jadwal...");

        axios
            .get(API_MASTER.jadwalHariIni, { params: { poli_id: poliId } })
            .then((response) => {
                const list = response.data.data || [];
                $infoJadwal.addClass("hidden").text(""); // Sembunyikan pesan loading

                if (list.length === 0) {
                    $jadwalSelect.prop("disabled", true);
                    $infoJadwal
                        .removeClass("hidden")
                        .text(
                            "Tidak ada jadwal dokter untuk poli ini hari ini.",
                        );
                    return;
                }

                // Isi data ke dropdown
                list.forEach((jd) => {
                    $jadwalSelect.append(`
                    <option value="${jd.id}"
                        data-dokter-id="${jd.dokter_id}"
                        data-nama-dokter="${jd.nama_dokter}"
                        data-jam-awal="${jd.jam_awal}"
                        data-jam-selesai="${jd.jam_selesai}">
                        ${jd.nama_dokter} — (${jd.jam_awal} - ${jd.jam_selesai})
                    </option>
                `);
                });

                $jadwalSelect.prop("disabled", false);

                // Auto-select jika hanya ada 1 jadwal
                if (list.length === 1) {
                    $jadwalSelect.val(list[0].id).trigger("change");
                }
            })
            .catch((error) => {
                console.error(error);
                $jadwalSelect.prop("disabled", true);
                $infoJadwal
                    .removeClass("hidden")
                    .text("Gagal memuat jadwal dokter.");
            });
    });

    // Listener tambahan untuk mengisi hidden input dokter_id saat jadwal dipilih
    $jadwalSelect.on("change", function () {
        const $selected = $(this).find(":selected");
        const dokterId = $selected.data("dokter-id");
        $("#dokter_id_create").val(dokterId || "");
    });

    $("#jadwal_dokter_id_create").on("change", function () {
        const opt = $(this).find("option:selected");
        const dokterId = opt.data("dokter-id") || "";
        const namaDokter = opt.data("nama-dokter") || "";
        const jamAwal = opt.data("jam-awal") || "";
        const jamSelesai = opt.data("jam-selesai") || "";

        $("#dokter_id_create").val(dokterId);

        if (dokterId) {
            $infoJadwal
                .removeClass("hidden")
                .text(`Dokter: ${namaDokter} (${jamAwal} - ${jamSelesai})`);
        } else {
            $infoJadwal.addClass("hidden").text("");
        }
    });

    // ==========================
    // Submit
    // ==========================
    $formAdd.on("submit", function (e) {
        e.preventDefault();
        const url = $formAdd.data("url");

        const items = [];
        $itemsWrapper.find(".order-item").each(function () {
            const $row = $(this);

            const layananId = $row.find(".layanan-select").val();
            if (!layananId) return;

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
            (item) => item.kategori_layanan_nama === "Pemeriksaan",
        );

        const formData = {
            pasien_id: $("#pasien_id_create").val(),
            items: items,
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
                    const errors = error.response.data.errors || {};

                    $formAdd.find(".is-invalid").removeClass("is-invalid");
                    $formAdd.find(".text-red-600").empty().css("opacity", 0);

                    let hasItemError = false;

                    for (const field in errors) {
                        const msg = errors[field][0];

                        if (field === "pasien_id") {
                            showFieldError($pasienSearch, $pasienError, msg);
                        } else if (field === "poli_id") {
                            if (poliTom)
                                $(poliTom.control).addClass("is-invalid");
                            else $poliSelect.addClass("is-invalid");

                            $("#poli_id_create-error")
                                .text(msg)
                                .css("opacity", 1);
                        } else if (field === "jadwal_dokter_id") {
                            showFieldError(
                                $jadwalSelect,
                                $("#jadwal_dokter_id_create-error"),
                                msg,
                            );
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

    // init default: start 1 row (kalau modal langsung ada row)
    if ($itemsWrapper.find(".order-item").length === 0) {
        addItemRow();
    }
});

/* ============================================================
 * MODAL UPDATE ORDER LAYANAN (POLI PAKAI TOM SELECT + AJAX FILTER)
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
            valueField: "id",
            labelField: "nama_poli",
            searchField: "nama_poli",
            sortField: { field: "nama_poli", direction: "asc" },
            options: [], // Opsi akan diisi via AJAX
            render: {
                option: function (data, escape) {
                    return "<div>" + escape(data.nama_poli) + "</div>";
                },
                item: function (data, escape) {
                    return "<div>" + escape(data.nama_poli) + "</div>";
                },
            },
        });
    }

    function lockPoliToUpdate(poliId, message) {
        if (poliTomUpdate) {
            poliTomUpdate.setValue(String(poliId));
            // Opsional: Jika ingin mendisable agar tidak bisa diubah user saat terkunci:
            // poliTomUpdate.disable();
        } else {
            $("#poli_id_select_update").val(poliId).trigger("change");
        }

        // Tampilkan pesan teks merah di bawah input poli (seperti di foto modal create kamu)
        $("#poli_id_update-error")
            .text(message)
            .removeClass("hidden")
            .css({ opacity: 1, color: "red", display: "block" });
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

    // ============================================================
    //  CORE LOGIC: AJAX FETCH ALLOWED POLI (Sesuai Controller Kamu)
    // ============================================================
    function fetchAllowedPoliUpdate() {
        let layananIds = [];
        $itemsWrapper.find(".layanan-select").each(function () {
            const val = $(this).val();
            if (val) layananIds.push(val);
        });

        if (layananIds.length === 0) return;

        axios
            .get("/order-layanan/get-data-poli", {
                params: { layanan_id: layananIds },
            })
            .then((response) => {
                if (response.data.success && poliTomUpdate) {
                    const filtered = response.data.data; // Data poli hasil filter

                    // Bersihkan opsi lama
                    poliTomUpdate.clearOptions();
                    poliTomUpdate.addOption(filtered);
                    poliTomUpdate.refreshOptions(false);

                    // --- LOGIKA UTAMA: JIKA HANYA ADA 1 POLI (KHUSUS) ---
                    if (filtered.length === 1) {
                        const poliId = filtered[0].id;

                        // Pilih dan Kunci Poli
                        lockPoliToUpdate(
                            poliId,
                            "Poli otomatis dipilih (khusus).",
                        );

                        // Trigger change manual agar Jadwal Dokter ikut ter-load
                        $("#poli_id_select_update").trigger("change");

                        return; // Keluar dari fungsi
                    }

                    // Jika lebih dari 1 poli (Layanan Global), biarkan user memilih
                    $("#poli_id_update-error").text("").css("opacity", 0);
                    // poliTomUpdate.enable(); // aktifkan kembali jika sebelumnya di-disable
                }
            })
            .catch((error) => {
                console.error("Gagal mengambil data poli:", error);
            });
    }

    // Tampilkan/hilangkan section poli & jadwal (UPDATE)
    function togglePoliSectionUpdate() {
        const $sectionPoliJadwal = $("#section_poli_jadwal_update");

        // Cek apakah ada layanan kategori "Pemeriksaan"
        const hasPemeriksaan =
            $itemsWrapper.find(".kategori-nama-input").filter(function () {
                return ($(this).val() || "").toLowerCase() === "pemeriksaan";
            }).length > 0;

        if (hasPemeriksaan) {
            $sectionPoliJadwal.removeClass("hidden");
            // Trigger fetch poli options agar dropdown terisi sesuai layanan
            fetchAllowedPoliUpdate();
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
            poliTomUpdate.clearOptions(); // Clear options juga
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
    $(document).on("click", ".btn-update-order-layanan", function () {
        const kodeTransaksi =
            $(this).data("kodeTransaksi") || $(this).data("kode-transaksi");
        if (!kodeTransaksi) return;

        resetEditForm();

        const detailUrl = `/order-layanan/get-data-order-layanan/${kodeTransaksi}`;

        axios
            .get(detailUrl)
            .then((response) => {
                const data = response.data.data || {};

                // ... (Logic Pasien & Items SAMA SEPERTI KODEMU SEBELUMNYA) ...

                // 1. SET DATA PASIEN
                const pasienObj = data.pasien || {};
                const pasienNama =
                    pasienObj.nama_pasien || data.nama_pasien || "";
                const pasienEmr = pasienObj.no_emr || data.pasien_no_emr || "-";
                const pasienJk =
                    pasienObj.jenis_kelamin || data.pasien_jenis_kelamin || "-";
                const pasienId = pasienObj.id || data.pasien_id || "";

                $pasienId.val(pasienId);
                $pasienSearch.val(pasienNama);
                $pasienNamaInfo.text(pasienNama || "-");
                $pasienEmrInfo.text(pasienEmr || "-");
                $pasienJkInfo.text(pasienJk || "-");
                $pasienInfoCard.removeClass("hidden");

                // 2. SET ORDER ID
                $("#order_layanan_id_update").val(
                    data.id || data.order_layanan_id || "",
                );

                // 3. ITEMS
                let items = Array.isArray(data.items) ? data.items : [];
                // fallback single item logic if needed...
                if (!items.length && data.layanan_id) {
                    items = [
                        {
                            id: data.id,
                            layanan_id: data.layanan_id,
                            nama_layanan: data.nama_layanan,
                            kategori_layanan_id: data.kategori_layanan_id,
                            kategori_layanan_nama: data.kategori_layanan,
                            jumlah: data.jumlah,
                            total_tagihan: data.total_tagihan,
                        },
                    ];
                }

                $itemsWrapper.empty();

                if (items.length) {
                    items.forEach((item) => {
                        const $row = addItemRowUpdate();

                        // pilih layanan di dropdown
                        $row.find(".layanan-select")
                            .val(item.layanan_id || "")
                            .trigger("change"); // Penting: ini akan men-trigger logic hitung subtotal

                        // override kategori & subtotal manual dari DB
                        if (item.kategori_layanan_id) {
                            $row.find(".kategori-id-input").val(
                                item.kategori_layanan_id,
                            );
                        }
                        if (item.kategori_layanan_nama) {
                            $row.find(".kategori-nama-input").val(
                                item.kategori_layanan_nama,
                            );
                        }
                        $row.find(".jumlah-input").val(item.jumlah || 1);
                        $row.find(".subtotal-input").val(
                            toRupiah(item.total_tagihan || 0),
                        );
                    });
                } else {
                    addItemRowUpdate();
                }

                // Total Tagihan
                if (typeof data.total_tagihan !== "undefined") {
                    $("#total_tagihan_update").val(
                        toRupiah(data.total_tagihan || 0),
                    );
                } else {
                    recalcGrandTotalUpdate();
                }

                // ===========================
                // POLI & JADWAL
                // ===========================
                const hasPemeriksaan = items.some(
                    (it) =>
                        (it.kategori_layanan_nama || "").toLowerCase() ===
                        "pemeriksaan",
                );

                if (hasPemeriksaan) {
                    $("#section_poli_jadwal_update").removeClass("hidden");

                    // A. Panggil AJAX Poli dulu untuk mengisi opsi
                    // Kita gunakan Promise manual atau callback, tapi agar simpel:
                    // Kita panggil fetchAllowedPoliUpdate, lalu set value-nya via timeout kecil
                    // atau kita modifikasi fetchAllowedPoliUpdate untuk menerima callback.
                    // Cara paling aman dengan struktur saat ini:

                    let layananIds = items
                        .map((i) => i.layanan_id)
                        .filter((id) => id);

                    // Request khusus saat load modal agar bisa setValue
                    axios
                        .post("/order-layanan/get-allowed-poli", {
                            layanan_id: layananIds,
                        })
                        .then((res) => {
                            if (res.data.success && poliTomUpdate) {
                                poliTomUpdate.clearOptions();
                                poliTomUpdate.addOption(res.data.data);
                                poliTomUpdate.refreshOptions(false);

                                // Set Value Poli dari DB
                                if (data.poli_id) {
                                    poliTomUpdate.setValue(
                                        String(data.poli_id),
                                    );

                                    // Load Jadwal
                                    $("#poli_id_select_update").trigger(
                                        "change",
                                    ); // Trigger logic load jadwal

                                    // Set Jadwal (perlu delay sedikit menunggu ajax jadwal selesai)
                                    // Atau kita handle di chain axios terpisah
                                    // Sederhananya biarkan user pilih jadwal ulang atau:
                                    setTimeout(() => {
                                        if (data.jadwal_dokter_id) {
                                            // Logic ini agak tricky karena load jadwal juga ajax.
                                            // Idealnya load jadwal dipanggil manual disini.
                                            loadJadwalDokter(
                                                data.poli_id,
                                                data.jadwal_dokter_id,
                                            );
                                        }
                                    }, 800);
                                }
                            }
                        });
                } else {
                    togglePoliSectionUpdate();
                }

                editModal?.show();
            })
            .catch((error) => {
                console.error("Error ambil detail order:", error);
                Swal.fire({
                    icon: "error",
                    title: "Gagal",
                    text: "Gagal memuat data order.",
                });
            });
    });

    // Fungsi Helper Khusus Load Jadwal saat Edit (Mencegah Race Condition)
    function loadJadwalDokter(poliId, selectedJadwalId = null) {
        axios
            .get("/order-layanan/get-data-jadwal-dokter-hari-ini", {
                params: { poli_id: poliId },
            })
            .then((response) => {
                const list = response.data.data || [];
                const $jadwalSelect = $("#jadwal_dokter_id_update");
                const $infoJadwal = $("#info_jadwal_dokter_update");

                $jadwalSelect
                    .empty()
                    .append(
                        '<option value="">-- Pilih Jadwal Dokter --</option>',
                    );

                if (!list.length) {
                    $jadwalSelect.prop("disabled", true);
                    $infoJadwal
                        .removeClass("hidden")
                        .text("Tidak ada jadwal dokter hari ini.");
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

                if (selectedJadwalId) {
                    $jadwalSelect.val(selectedJadwalId).trigger("change");
                }
            });
    }

    // ========================
    //  BUTTON CLOSE / CANCEL
    // ========================
    $(
        "#buttonCloseModalUpdateOrderLayanan, #buttonCancaleModalUpdateOrderLayanan",
    ).on("click", function () {
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
            $row.find(".layanan-select").val("");
            $row.find(".kategori-nama-input").val("");
            $row.find(".kategori-id-input").val("");
            $row.find(".jumlah-input").val(1);
            $row.find(".subtotal-input").val("");
            // Reset Tom Select Poli jika data direset
            if (poliTomUpdate) poliTomUpdate.clear();
        } else {
            $row.remove();
        }

        recalcGrandTotalUpdate();
        togglePoliSectionUpdate();

        // 🔥 UPDATE POLI OPTIONS SETELAH HAPUS
        // Kita panggil fetch agar opsi poli diupdate sesuai sisa layanan
        fetchAllowedPoliUpdate();
    });

    // =========================
    //  SEARCH PASIEN (AJAX) - UPDATE
    // =========================
    let pasienSearchTimeoutUpdate = null;
    $pasienSearch.on("keyup", function () {
        const keyword = $(this).val().trim();
        if (keyword.length < 2) {
            $pasienResults.empty().addClass("hidden");
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
                                `<div class="px-3 py-2 text-xs text-gray-500">Tidak ada pasien ditemukan.</div>`,
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
                            <button type="button" class="pasien-item-update w-full text-left px-3 py-2 text-sm hover:bg-blue-50 border-b"
                                data-id="${pasien.id}" data-nama="${pasien.nama_pasien}"
                                data-no-emr="${pasien.no_emr || ""}" data-jk="${pasien.jenis_kelamin || "-"}">
                                <span class="font-semibold">${pasien.nama_pasien}</span>
                                <span class="text-xs text-gray-500 block">${infoTambahan}</span>
                            </button>
                        `);
                    });
                    $pasienResults.removeClass("hidden");
                });
        }, 300);
    });

    $pasienResults.on("click", ".pasien-item-update", function () {
        const $item = $(this);
        $pasienId.val($item.data("id"));
        $pasienSearch.val($item.data("nama"));
        $pasienResults.empty().addClass("hidden");
        $pasienNamaInfo.text($item.data("nama"));
        $pasienEmrInfo.text($item.data("no-emr") || "-");
        $pasienJkInfo.text($item.data("jk") || "-");
        $pasienInfoCard.removeClass("hidden");
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
     * LAYANAN → KATEGORI + SUBTOTAL + AUTO KEEP POLI (UPDATE)
     * ========================= */
    $formEdit.on("change", ".layanan-select", function () {
        const $row = $(this).closest(".order-item");
        const selected = $(this).find("option:selected");

        const kategoriId = selected.data("kategori-id") || "";
        const kategoriNama = (
            selected.data("kategori-nama") || ""
        ).toLowerCase();
        const harga = parseFloat(selected.data("harga") || 0);

        // Ambil info is_global dan poli_id dari atribut data option
        // Pastikan di HTML option sudah ada: data-is-global dan data-poli-id
        const isGlobal = selected.data("is-global");
        const poliTerikat = selected.data("poli-id");

        $row.find(".kategori-id-input").val(kategoriId);
        $row.find(".kategori-nama-input").val(kategoriNama);

        const qty = parseInt($row.find(".jumlah-input").val() || 1, 10);
        const subtotal = harga * qty;
        $row.find(".subtotal-input").val(toRupiah(subtotal));

        recalcGrandTotalUpdate();
        togglePoliSectionUpdate();

        // --- LOGIKA AGAR LANGSUNG TER-KEEP / TERPILIH (SAMA SEPERTI CREATE) ---
        if (kategoriNama === "pemeriksaan") {
            // Jika is_global false (0 atau "0") artinya layanan khusus poli tertentu
            if (isGlobal == 0 || isGlobal === "0") {
                if (poliTerikat && poliTomUpdate) {
                    // 1. Langsung masukkan ID Poli ke TomSelect agar teksnya muncul di box
                    poliTomUpdate.setValue(String(poliTerikat));

                    // 2. Paksa trigger event change agar jadwal dokter ikut muncul
                    $("#poli_id_select_update").trigger("change");
                }
            } else {
                // Jika layanan bersifat global, biarkan user memilih atau hanya update opsinya
                fetchAllowedPoliUpdate();
            }
        }
    });

    /* =========================
     * JUMLAH → UPDATE SUBTOTAL (UPDATE)
     * ========================= */
    $formEdit.on("input", ".jumlah-input", function () {
        const $row = $(this).closest(".order-item");
        const selected = $row.find(".layanan-select option:selected");
        const harga = parseFloat(selected.data("harga") || 0);
        const qty = parseInt($(this).val() || 1, 10);
        $row.find(".subtotal-input").val(toRupiah(harga * qty));
        recalcGrandTotalUpdate();
    });

    /* =========================
     * POLI → LOAD JADWAL DOKTER (UPDATE)
     * ========================= */
    $("#poli_id_select_update").on("change", function () {
        const poliId = $(this).val();

        // Jika tidak ada poli ID (misal user clear selection), reset jadwal
        if (!poliId) {
            $("#jadwal_dokter_id_update")
                .empty()
                .append('<option value="">-- Pilih Jadwal Dokter --</option>')
                .prop("disabled", true);
            $("#info_jadwal_dokter_update").addClass("hidden");
            $("#dokter_id_update").val("");
            return;
        }

        // Panggil helper loadJadwal (agar konsisten dengan saat edit modal open)
        loadJadwalDokter(poliId);
    });

    /* =========================
     * PILIH JADWAL → SET DOKTER_ID (UPDATE)
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
     * SUBMIT FORM UPDATE
     * ========================= */
    $formEdit.on("submit", function (e) {
        e.preventDefault();
        const url = $formEdit.data("url");

        const items = [];
        $itemsWrapper.find(".order-item").each(function () {
            const $row = $(this);
            const layananId = $row.find(".layanan-select").val();
            if (!layananId) return;

            items.push({
                layanan_id: layananId,
                kategori_layanan_id: $row.find(".kategori-id-input").val(),
                kategori_layanan_nama: $row.find(".kategori-nama-input").val(),
                jumlah: $row.find(".jumlah-input").val() || 1,
                total_tagihan: cleanRupiah($row.find(".subtotal-input").val()),
            });
        });

        const hasPemeriksaan = items.some(
            (item) =>
                (item.kategori_layanan_nama || "").toLowerCase() ===
                "pemeriksaan",
        );
        const anchorItem = items.length ? items[0] : null;

        const formData = {
            order_layanan_id: $("#order_layanan_id_update").val(),
            pasien_id: $("#pasien_id_update").val(),
            items: items,
            total_tagihan: cleanRupiah($("#total_tagihan_update").val()),
        };

        // Fallback fields
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
                if ($("#orderLayanan").length)
                    $("#orderLayanan").DataTable().ajax.reload(null, false);
            })
            .catch((error) => {
                // ... (Error handling sama seperti sebelumnya) ...
                if (error.response?.status === 422) {
                    const errors = error.response.data.errors || {};
                    // ... loop errors ...
                    Swal.fire({
                        icon: "error",
                        title: "Validasi Gagal",
                        text: "Periksa kembali inputan Anda.",
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Terjadi kesalahan server.",
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
                        `/order-layanan/delete-data-order-layanan/${kodeTransaksi}`,
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
