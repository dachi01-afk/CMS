import $, { ajax } from "jquery";
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
            { data: "subtotal", name: "subtotal" },
            { data: "status_order_layanan", name: "status_order_layanan" },
            { data: "tanggal_order", name: "tanggal_order" },
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

/* ============================================================
 *  MODAL CREATE ORDER LAYANAN
 *  - POLI searchable via TomSelect
 *  - LAYANAN searchable via TomSelect
 *  - Subtotal mengambil data dari harga_setelah_diskon (data-harga)
 *  - Filter poli berdasarkan layanan pemeriksaan
 * ============================================================ */
$(function () {
    let pasienTom = null;

    const layananRowTemplateHtml = $("#orderItemTemplate")[0].outerHTML;

    function tampilkanPreviewPasien(pasien) {
        $("#preview_nama_create").text(pasien.nama_pasien ?? "-");
        $("#preview_no_emr_create").text(pasien.no_emr ?? "-");
        $("#preview_jk_create").text(pasien.jenis_kelamin ?? "-");
        $("#preview_no_hp_create").text(pasien.no_hp_pasien ?? "-");

        $("#pasien_preview_create").removeClass("hidden");
    }

    function resetPreviewPasien() {
        $("#preview_nama_create").text("-");
        $("#preview_no_emr_create").text("-");
        $("#preview_jk_create").text("-");
        $("#preview_no_hp_create").text("-");

        $("#pasien_preview_create").addClass("hidden");
    }

    function initTomSelectPasien() {
        if (pasienTom) {
            pasienTom.destroy();
            pasienTom = null;
        }

        pasienTom = new TomSelect("#pasien_search_create", {
            valueField: "id",
            labelField: "nama_pasien",
            searchField: ["nama_pasien", "no_emr"],
            maxOptions: 20,
            create: false,
            preload: false,
            placeholder: "Ketik nama / No EMR / No RM / NIK pasien...",
            loadThrottle: 300,

            render: {
                option: function (item, escape) {
                    return `
                    <div class="px-3 py-2">
                        <div class="font-semibold">${escape(item.nama_pasien ?? "-")}</div>
                        <div class="text-xs text-gray-500">
                            EMR: ${escape(item.no_emr ?? "-")}
                        </div>
                    </div>
                `;
                },
            },

            load: function (query, callback) {
                if (!query.length) return callback();

                axios
                    .get("/order-layanan/get-data-pasien", {
                        params: { q: query },
                    })
                    .then(function (response) {
                        callback(response.data);
                    })
                    .catch(function () {
                        callback();
                    });
            },

            onChange: function (value) {
                $("#pasien_id_create").val(value || "");

                if (!value) {
                    resetPreviewPasien();
                    return;
                }

                const selected = this.options[value];

                if (selected) {
                    tampilkanPreviewPasien(selected);
                } else {
                    resetPreviewPasien();
                }
            },
        });
    }

    function formatRupiah(angka) {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(angka || 0);
    }

    function parseRupiahToNumber(value) {
        if (!value) return 0;

        return parseInt(String(value).replace(/[^\d]/g, ""), 10) || 0;
    }

    function hitungSubtotalRow($row) {
        const harga = parseFloat($row.find(".harga-input").val() || 0);
        const jumlah = parseInt($row.find(".jumlah-input").val() || 0);

        const subtotal = harga * jumlah;

        $row.find(".subtotal-input").val(formatRupiah(subtotal));
        hitungTotalTagihan();
    }

    function hitungTotalTagihan() {
        let total = 0;

        $("#orderItemsWrapper .order-item").each(function () {
            const harga = parseFloat($(this).find(".harga-input").val() || 0);
            const jumlah = parseInt($(this).find(".jumlah-input").val() || 0);

            total += harga * jumlah;
        });

        $("#total_tagihan_create").val(formatRupiah(total));
    }

    function resetRowLayanan($row) {
        $row.find(".kategori-nama-input")
            .val("")
            .removeClass("text-red-500 font-medium")
            .removeAttr("data-kategori-missing");

        $row.find(".kategori-id-input").val("");
        $row.find(".harga-input").val("");
        $row.find(".subtotal-input").val(formatRupiah(0));

        hitungTotalTagihan();
    }

    function ambilDetailLayanan(layananId, $row) {
        axios
            .get(`/order-layanan/get-detail-data-layanan/${layananId}`)
            .then(function (response) {
                const data = response.data;

                const kategoriNama = data.nama_kategori ?? null;
                const kategoriId = data.kategori_id ?? null;
                const hargaLayanan = data.harga_layanan ?? 0;

                if (!kategoriNama || !kategoriId) {
                    $row.find(".kategori-nama-input")
                        .val("Data Kategori Belum Ada")
                        .addClass("text-red-500 font-medium")
                        .attr("data-kategori-missing", "1");

                    $row.find(".kategori-id-input").val("");
                } else {
                    $row.find(".kategori-nama-input")
                        .val(kategoriNama)
                        .removeClass("text-red-500 font-medium")
                        .removeAttr("data-kategori-missing");

                    $row.find(".kategori-id-input").val(kategoriId);
                }

                $row.find(".harga-input").val(hargaLayanan);

                hitungSubtotalRow($row);
            })
            .catch(function (error) {
                console.error("Gagal ambil detail layanan:", error);
                resetRowLayanan($row);
            });
    }

    function hapusSatuRowLayanan() {
        const $rows = $("#orderItemsWrapper .order-item");

        if ($rows.length <= 1) {
            Swal.fire({
                icon: "error",
                title: "Tidak bisa menghapus",
                text: "Minimal harus ada 1 layanan.",
                confirmButtonText: "OK",
            });
            return;
        }

        const $lastRow = $rows.last();
        const selectEl = $lastRow.find(".layanan-select")[0];

        if (selectEl && selectEl.tomselect) {
            selectEl.tomselect.destroy();
        }

        $lastRow.remove();
        hitungTotalTagihan();
    }

    function initTomSelectLayanan(selectEl) {
        if (!selectEl) return;

        if (selectEl.tomselect) {
            selectEl.tomselect.destroy();
        }

        new TomSelect(selectEl, {
            valueField: "id",
            labelField: "nama_layanan",
            searchField: ["nama_layanan"],
            maxOptions: 20,
            create: false,
            preload: false,
            loadThrottle: 300,
            placeholder: "Pilih layanan...",

            render: {
                option: function (item, escape) {
                    return `
                    <div class="px-3 py-2">
                        <div class="font-medium">${escape(item.nama_layanan ?? "-")}</div>
                    </div>
                `;
                },
                item: function (item, escape) {
                    return `<div>${escape(item.nama_layanan ?? "-")}</div>`;
                },
                no_results: function () {
                    return `<div class="p-2 text-sm text-gray-500">Layanan tidak ditemukan</div>`;
                },
                loading: function () {
                    return `<div class="p-2 text-sm text-gray-500">Memuat data...</div>`;
                },
            },

            load: function (query, callback) {
                axios
                    .get("/order-layanan/get-data-layanan", {
                        params: { q: query },
                    })
                    .then(function (response) {
                        callback(response.data);
                    })
                    .catch(function () {
                        callback();
                    });
            },

            onChange: function (value) {
                const $row = $(selectEl).closest(".order-item");

                if (!value) {
                    resetRowLayanan($row);
                    return;
                }

                ambilDetailLayanan(value, $row);
            },
        });
    }

    function tambahRowLayanan() {
        const $newRow = $(layananRowTemplateHtml);

        $newRow.removeAttr("id");

        $newRow
            .find(".kategori-nama-input")
            .val("")
            .removeClass("text-red-500 font-medium")
            .removeAttr("data-kategori-missing");

        $newRow.find(".kategori-id-input").val("");
        $newRow.find(".harga-input").val("");
        $newRow.find(".jumlah-input").val(1);
        $newRow.find(".subtotal-input").val(formatRupiah(0));

        const selectEl = $newRow.find(".layanan-select")[0];
        selectEl.value = "";

        $("#orderItemsWrapper").append($newRow);

        initTomSelectLayanan(selectEl);
        hitungTotalTagihan();
    }

    $(document).on("input change", ".jumlah-input", function () {
        const $row = $(this).closest(".order-item");

        let jumlah = parseInt($(this).val() || 1);
        if (jumlah < 1) {
            jumlah = 1;
            $(this).val(1);
        }

        hitungSubtotalRow($row);
    });

    function resetModalCreateToDefault() {
        clearCreateErrors();

        const $form = $("#form-create-order-layanan");

        if ($form.length && $form[0]) {
            $form[0].reset();
        }

        // reset pasien
        if (pasienTom) {
            pasienTom.destroy();
            pasienTom = null;
        }

        $("#pasien_id_create").val("");
        resetPreviewPasien();

        // destroy semua tomselect layanan yang sudah ada
        $("#orderItemsWrapper .layanan-select").each(function () {
            if (this.tomselect) {
                this.tomselect.destroy();
            }
        });

        // balikin wrapper layanan jadi 1 row default
        $("#orderItemsWrapper").html(layananRowTemplateHtml);

        const $firstRow = $("#orderItemsWrapper .order-item").first();

        $firstRow.find(".kategori-nama-input").val("");
        $firstRow.find(".kategori-id-input").val("");
        $firstRow.find(".harga-input").val("");
        $firstRow.find(".jumlah-input").val(1);
        $firstRow.find(".subtotal-input").val(formatRupiah(0));

        // total tagihan default
        $("#total_tagihan_create").val(formatRupiah(0));

        // reset poli & jadwal
        $("#section_poli_jadwal_create").addClass("hidden");

        $("#poli_id_select_create").val("");

        $("#jadwal_dokter_id_create")
            .html('<option value="">-- Pilih Jadwal Dokter --</option>')
            .val("")
            .prop("disabled", true);

        $("#dokter_id_create").val("");

        $("#info_jadwal_dokter_create").text("").addClass("hidden");
    }

    function openModalCreate() {
        resetModalCreateToDefault();
        lockBodyScroll();
        $("#modal-create-order-layanan").removeClass("hidden").addClass("flex");

        initTomSelectPasien();

        const firstSelect = document.querySelector(
            "#orderItemTemplate .layanan-select",
        );
        initTomSelectLayanan(firstSelect);
    }

    function closeModalCreate() {
        unlockBodyScroll();
        $("#modal-create-order-layanan").removeClass("flex").addClass("hidden");
    }

    $("#button-open-modal-create-order-layanan").on("click", function () {
        openModalCreate();
    });

    $("#button-tambah-layanan").on("click", function () {
        tambahRowLayanan();
    });

    $(document).on("click", ".btn-remove-item", function () {
        hapusSatuRowLayanan();
    });

    $(
        "#button-close-modal-create-order-layanan-header, #button-close-modal-create-order-layanan-footer",
    ).on("click", function () {
        closeModalCreate();
    });

    function kumpulkanItemsLayanan() {
        const items = [];
        let adaError = false;
        let $firstInvalid = null;

        $("#orderItemsWrapper .order-item").each(function (index) {
            const $row = $(this);

            const $layanan = $row.find(".layanan-select");
            const $kategoriNama = $row.find(".kategori-nama-input");
            const $jumlah = $row.find(".jumlah-input");
            const $subtotal = $row.find(".subtotal-input");

            const layananId = $layanan.val();
            const kategoriLayananId = $row.find(".kategori-id-input").val();
            const jumlah = parseInt($jumlah.val() || 0, 10);
            const totalTagihanItem = parseRupiahToNumber($subtotal.val());

            const errors = [];

            if (!layananId) {
                errors.push("Layanan wajib dipilih.");
                setTomSelectError($layanan);
                if (!$firstInvalid) $firstInvalid = $layanan;
            }

            if (!kategoriLayananId) {
                const kategoriMissing =
                    $kategoriNama.attr("data-kategori-missing") === "1";

                if (kategoriMissing) {
                    errors.push("Silahkan Update Data Layanan Nya.");
                } else {
                    errors.push("Kategori belum terisi otomatis.");
                }

                setFieldError($kategoriNama, "");
                if (!$firstInvalid) $firstInvalid = $kategoriNama;
            }

            if (jumlah < 1) {
                errors.push("Jumlah minimal 1.");
                setFieldError($jumlah, "");
                if (!$firstInvalid) $firstInvalid = $jumlah;
            }

            if (totalTagihanItem <= 0) {
                errors.push("Subtotal belum valid.");
                setFieldError($subtotal, "");
                if (!$firstInvalid) $firstInvalid = $subtotal;
            }

            if (errors.length > 0) {
                adaError = true;
                setRowError($row, `Baris ${index + 1}: ${errors.join(" ")}`);
                return false;
            }

            items.push({
                layanan_id: parseInt(layananId, 10),
                kategori_layanan_id: parseInt(kategoriLayananId, 10),
                jumlah: jumlah,
                total_tagihan: totalTagihanItem,
            });
        });

        if ($firstInvalid && $firstInvalid.length) {
            $firstInvalid[0].scrollIntoView({
                behavior: "smooth",
                block: "center",
            });
        }

        return { adaError, items };
    }

    function clearCreateErrors() {
        $("#pasien_id_create-error").text("");
        $("#total_tagihan_create-error").text("").addClass("opacity-0");
        $("#poli_id_create-error").text("").addClass("opacity-0");
        $("#jadwal_dokter_id_create-error").text("").addClass("opacity-0");

        $("#form-create-order-layanan")
            .find("input, select")
            .removeClass("border-red-500 ring-1 ring-red-500");

        $("#form-create-order-layanan")
            .find(".ts-wrapper")
            .removeClass("border-red-500 ring-1 ring-red-500 rounded-lg");

        $("#form-create-order-layanan").find(".row-error-message").remove();
    }

    function setFieldError($el, message, errorSelector = null) {
        $el.addClass("border-red-500 ring-1 ring-red-500");

        if (errorSelector) {
            $(errorSelector).text(message).removeClass("opacity-0");
        }
    }

    function setTomSelectError($select, message, errorSelector = null) {
        const ts = $select[0]?.tomselect;
        const $wrapper = ts
            ? $(ts.wrapper)
            : $select.closest(".w-full").find(".ts-wrapper");

        $wrapper.addClass("border-red-500 ring-1 ring-red-500 rounded-lg");

        if (errorSelector) {
            $(errorSelector).text(message).removeClass("opacity-0");
        }
    }

    function setRowError($row, message) {
        const $msg = $(
            `<div class="row-error-message text-red-600 text-xs mt-2">${message}</div>`,
        );
        $row.append($msg);
    }

    function submitOrderLayananCreate() {
        clearCreateErrors();

        const pasienId = $("#pasien_id_create").val();
        const totalTagihan = parseRupiahToNumber(
            $("#total_tagihan_create").val(),
        );
        const poliId = $("#poli_id_select_create").val();
        const jadwalDokterId = $("#jadwal_dokter_id_create").val();

        let hasError = false;

        if (!pasienId) {
            setTomSelectError(
                $("#pasien_search_create"),
                "Pasien wajib dipilih.",
                "#pasien_id_create-error",
            );
            hasError = true;
        }

        const hasilItems = kumpulkanItemsLayanan();
        if (hasilItems.adaError || hasilItems.items.length < 1) {
            hasError = true;
        }

        const butuhPemeriksaan = !$("#section_poli_jadwal_create").hasClass(
            "hidden",
        );

        if (butuhPemeriksaan && !poliId) {
            setFieldError(
                $("#poli_id_select_create"),
                "Poli wajib dipilih.",
                "#poli_id_create-error",
            );
            hasError = true;
        }

        if (butuhPemeriksaan && !jadwalDokterId) {
            setFieldError(
                $("#jadwal_dokter_id_create"),
                "Jadwal dokter wajib dipilih.",
                "#jadwal_dokter_id_create-error",
            );
            hasError = true;
        }

        if (hasError) {
            Swal.fire({
                icon: "error",
                title: "Validasi gagal",
                text: "Masih ada kolom yang belum valid. Cek bagian yang diberi tanda merah.",
            });
            return;
        }

        const payload = {
            pasien_id: parseInt(pasienId, 10),
            total_tagihan: totalTagihan,
            items: hasilItems.items,
        };

        if (poliId) {
            payload.poli_id = parseInt(poliId, 10);
        }

        if (jadwalDokterId) {
            payload.jadwal_dokter_id = parseInt(jadwalDokterId, 10);
        }

        axios
            .post("/order-layanan/create-data-order-layanan", payload)
            .then(function (response) {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil",
                    text: response.data.message || "Data berhasil disimpan.",
                }).then(() => {
                    closeModalCreate();
                    if ($.fn.DataTable.isDataTable("#orderLayanan")) {
                        $("#orderLayanan").DataTable().ajax.reload(null, false);
                    }
                });
            })
            .catch(function (error) {
                if (error.response && error.response.status === 422) {
                    tampilkanServerValidationErrors(
                        error.response.data.errors || {},
                    );
                    Swal.fire({
                        icon: "error",
                        title: "Validasi gagal",
                        text: "Masih ada data yang belum valid. Cek kolom yang diberi tanda merah.",
                    });
                    return;
                }

                Swal.fire({
                    icon: "error",
                    title: "Gagal",
                    text:
                        error.response?.data?.message ||
                        "Terjadi kesalahan saat menyimpan data.",
                });
            });
    }

    $("#form-create-order-layanan").on("submit", function (e) {
        e.preventDefault();
        submitOrderLayananCreate();
    });
});

/* ============================================================
 * MODAL UPDATE ORDER LAYANAN (POLI PAKAI TOM SELECT + AJAX FILTER)
 * ============================================================ */
$(function () {
    let pasienTomUpdate = null;
    const layananRowTemplateUpdateHtml = $("#orderItemTemplateUpdate")[0]
        .outerHTML;

    function formatRupiah(angka) {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(angka || 0);
    }

    function parseRupiahToNumber(value) {
        if (!value) return 0;
        return parseInt(String(value).replace(/[^\d]/g, ""), 10) || 0;
    }

    function ensureHargaInputUpdate($row) {
        if ($row.find(".harga-input").length === 0) {
            $row.find(".kategori-id-input").after(
                '<input type="hidden" class="harga-input">',
            );
        }
    }

    function resetPreviewPasienUpdate() {
        $("#pasien_id_update").val("");

        $("#pasien_nama_info_update").text("-");
        $("#pasien_no_emr_info_update").text("-");
        $("#pasien_jk_info_update").text("-");

        $("#pasien_info_update").addClass("hidden");
    }

    function initTomSelectPasienUpdate(selectedPasien = null) {
        if (pasienTomUpdate) {
            pasienTomUpdate.destroy();
            pasienTomUpdate = null;
        }

        pasienTomUpdate = new TomSelect("#pasien_search_update", {
            valueField: "id",
            labelField: "nama_pasien",
            searchField: ["nama_pasien", "no_emr"],
            maxOptions: 20,
            create: false,
            preload: false,
            placeholder: "Ketik nama / No EMR / No RM / NIK pasien...",
            loadThrottle: 300,

            render: {
                option: function (item, escape) {
                    return `
                    <div class="px-3 py-2">
                        <div class="font-semibold">${escape(item.nama_pasien ?? "-")}</div>
                        <div class="text-xs text-gray-500">
                            EMR: ${escape(item.no_emr ?? "-")}
                        </div>
                    </div>
                `;
                },
                item: function (item, escape) {
                    return `<div>${escape(item.nama_pasien ?? "-")}</div>`;
                },
                no_results: function () {
                    return `<div class="p-2 text-sm text-gray-500">Pasien tidak ditemukan</div>`;
                },
                loading: function () {
                    return `<div class="p-2 text-sm text-gray-500">Mencari pasien...</div>`;
                },
            },

            load: function (query, callback) {
                if (!query.length) return callback();

                axios
                    .get("/order-layanan/get-data-pasien", {
                        params: { q: query },
                    })
                    .then(function (response) {
                        callback(response.data);
                    })
                    .catch(function () {
                        callback();
                    });
            },

            onChange: function (value) {
                $("#pasien_id_update").val(value || "");

                if (!value) {
                    resetPreviewPasienUpdate();
                    return;
                }

                const selected = this.options[value];

                if (selected) {
                    tampilkanPreviewPasienUpdate(selected);
                } else {
                    resetPreviewPasienUpdate();
                }
            },
        });

        if (selectedPasien && selectedPasien.id) {
            pasienTomUpdate.addOption({
                id: selectedPasien.id,
                nama_pasien: selectedPasien.nama_pasien ?? "",
                no_emr: selectedPasien.no_emr ?? "",
                jenis_kelamin: selectedPasien.jenis_kelamin ?? "",
            });

            pasienTomUpdate.setValue(String(selectedPasien.id), true);
        }
    }

    function tampilkanPreviewPasienUpdate(pasien) {
        $("#pasien_search_update").val(pasien.nama_pasien ?? "");
        $("#pasien_id_update").val(pasien.id ?? "");

        $("#pasien_nama_info_update").text(pasien.nama_pasien ?? "-");
        $("#pasien_no_emr_info_update").text(pasien.no_emr ?? "-");
        $("#pasien_jk_info_update").text(pasien.jenis_kelamin ?? "-");

        $("#pasien_info_update").removeClass("hidden");
        $("#pasien_search_results_update").addClass("hidden").empty();
    }

    function clearUpdateErrors() {
        $("#pasien_id_update-error").text("");
        $("#total_tagihan_update-error").text("").addClass("opacity-0");
        $("#poli_id_update-error").text("").addClass("opacity-0");
        $("#jadwal_dokter_id_update-error").text("").addClass("opacity-0");

        $("#formUpdateOrderLayanan")
            .find("input, select")
            .removeClass("border-red-500 ring-1 ring-red-500");

        $("#formUpdateOrderLayanan")
            .find(".ts-wrapper")
            .removeClass("border-red-500 ring-1 ring-red-500 rounded-lg");

        $("#formUpdateOrderLayanan").find(".row-error-message").remove();
    }

    function setFieldErrorUpdate($el, message, errorSelector = null) {
        $el.addClass("border-red-500 ring-1 ring-red-500");

        if (errorSelector) {
            $(errorSelector).text(message).removeClass("opacity-0");
        }
    }

    function setTomSelectErrorUpdate(
        $select,
        message = "",
        errorSelector = null,
    ) {
        const ts = $select[0]?.tomselect;
        const $wrapper = ts
            ? $(ts.wrapper)
            : $select.closest(".w-full").find(".ts-wrapper");

        $wrapper.addClass("border-red-500 ring-1 ring-red-500 rounded-lg");

        if (errorSelector) {
            $(errorSelector).text(message).removeClass("opacity-0");
        }
    }

    function setRowErrorUpdate($row, message) {
        $row.find(".row-error-message").remove();
        const $msg = $(
            `<div class="row-error-message text-red-600 text-xs mt-2">${message}</div>`,
        );
        $row.append($msg);
    }

    function isButuhPemeriksaanUpdate() {
        let adaPemeriksaan = false;

        $("#orderItemsWrapperUpdate .order-item").each(function () {
            const kategoriNama = (
                $(this).find(".kategori-nama-input").val() || ""
            )
                .trim()
                .toLowerCase();

            if (kategoriNama === "pemeriksaan") {
                adaPemeriksaan = true;
                return false;
            }
        });

        return adaPemeriksaan;
    }

    function toggleSectionPoliJadwalUpdate() {
        const butuhPemeriksaan = isButuhPemeriksaanUpdate();

        if (butuhPemeriksaan) {
            $("#section_poli_jadwal_update").removeClass("hidden");
            return;
        }

        $("#section_poli_jadwal_update").addClass("hidden");
        $("#poli_id_select_update").val("");
        $("#jadwal_dokter_id_update")
            .html('<option value="">-- Pilih Jadwal Dokter --</option>')
            .val("")
            .prop("disabled", true);

        $("#dokter_id_update").val("");
        $("#info_jadwal_dokter_update").text("").addClass("hidden");
    }

    function hitungSubtotalRowUpdate($row) {
        const harga = parseFloat($row.find(".harga-input").val() || 0);
        const jumlah = parseInt($row.find(".jumlah-input").val() || 0, 10);

        const subtotal = harga * jumlah;
        $row.find(".subtotal-input").val(formatRupiah(subtotal));

        hitungTotalTagihanUpdate();
    }

    function hitungTotalTagihanUpdate() {
        let total = 0;

        $("#orderItemsWrapperUpdate .order-item").each(function () {
            const harga = parseFloat($(this).find(".harga-input").val() || 0);
            const jumlah = parseInt(
                $(this).find(".jumlah-input").val() || 0,
                10,
            );

            total += harga * jumlah;
        });

        $("#total_tagihan_update").val(formatRupiah(total));
    }

    function resetRowLayananUpdate($row) {
        ensureHargaInputUpdate($row);

        $row.find(".kategori-nama-input")
            .val("")
            .removeClass("text-red-500 font-medium")
            .removeAttr("data-kategori-missing");

        $row.find(".kategori-id-input").val("");
        $row.find(".harga-input").val("");
        $row.find(".jumlah-input").val(1);
        $row.find(".subtotal-input").val(formatRupiah(0));
        $row.find(".row-error-message").remove();

        const selectEl = $row.find(".layanan-select")[0];
        if (selectEl?.tomselect) {
            selectEl.tomselect.clear();
        }

        toggleSectionPoliJadwalUpdate();
        hitungTotalTagihanUpdate();
    }

    function ambilDetailLayananUpdate(layananId, $row) {
        ensureHargaInputUpdate($row);

        axios
            .get(`/order-layanan/get-detail-data-layanan/${layananId}`)
            .then(function (response) {
                const data = response.data;

                const kategoriNama =
                    data.nama_kategori ?? data.kategori_nama ?? null;
                const kategoriId =
                    data.kategori_id ?? data.kategori_layanan_id ?? null;
                const hargaLayanan = parseFloat(
                    data.harga_layanan ?? data.harga ?? 0,
                );

                if (!kategoriNama || !kategoriId) {
                    $row.find(".kategori-nama-input")
                        .val("Data Kategori Belum Ada")
                        .addClass("text-red-500 font-medium")
                        .attr("data-kategori-missing", "1");

                    $row.find(".kategori-id-input").val("");
                } else {
                    $row.find(".kategori-nama-input")
                        .val(kategoriNama)
                        .removeClass("text-red-500 font-medium")
                        .removeAttr("data-kategori-missing");

                    $row.find(".kategori-id-input").val(kategoriId);
                }

                $row.find(".harga-input").val(hargaLayanan);
                hitungSubtotalRowUpdate($row);
                toggleSectionPoliJadwalUpdate();
            })
            .catch(function (error) {
                console.error("Gagal ambil detail layanan update:", error);
                resetRowLayananUpdate($row);
            });
    }

    function initTomSelectLayananUpdate(selectEl, selectedItem = null) {
        if (!selectEl) return;

        if (selectEl.tomselect) {
            selectEl.tomselect.destroy();
        }

        const ts = new TomSelect(selectEl, {
            valueField: "id",
            labelField: "nama_layanan",
            searchField: ["nama_layanan"],
            maxOptions: 20,
            create: false,
            preload: false,
            loadThrottle: 300,
            placeholder: "Pilih layanan...",

            render: {
                option: function (item, escape) {
                    return `
                        <div class="px-3 py-2">
                            <div class="font-medium">${escape(item.nama_layanan ?? "-")}</div>
                        </div>
                    `;
                },
                item: function (item, escape) {
                    return `<div>${escape(item.nama_layanan ?? "-")}</div>`;
                },
                no_results: function () {
                    return `<div class="p-2 text-sm text-gray-500">Layanan tidak ditemukan</div>`;
                },
                loading: function () {
                    return `<div class="p-2 text-sm text-gray-500">Memuat data...</div>`;
                },
            },

            load: function (query, callback) {
                axios
                    .get("/order-layanan/get-data-layanan", {
                        params: { q: query },
                    })
                    .then(function (response) {
                        callback(response.data);
                    })
                    .catch(function () {
                        callback();
                    });
            },

            onChange: function (value) {
                const $row = $(selectEl).closest(".order-item");

                if (!value) {
                    resetRowLayananUpdate($row);
                    return;
                }

                ambilDetailLayananUpdate(value, $row);
            },
        });

        if (selectedItem && selectedItem.id) {
            ts.addOption({
                id: selectedItem.id,
                nama_layanan: selectedItem.nama_layanan,
            });
            ts.setValue(String(selectedItem.id), true);
        }
    }

    function buatRowLayananUpdate(prefill = null) {
        const $row = $(layananRowTemplateUpdateHtml);
        $row.removeAttr("id");

        ensureHargaInputUpdate($row);

        $row.find(".layanan-select").html(
            '<option value="">Pilih layanan</option>',
        );
        $row.find(".jumlah-input").val(1);
        $row.find(".subtotal-input").val(formatRupiah(0));
        $row.find(".kategori-nama-input")
            .val("")
            .removeClass("text-red-500 font-medium")
            .removeAttr("data-kategori-missing");
        $row.find(".kategori-id-input").val("");
        $row.find(".harga-input").val("");

        if (prefill) {
            const kategoriNama = prefill.kategori_layanan_nama ?? null;
            const kategoriId = prefill.kategori_layanan_id ?? null;
            const jumlah = parseInt(prefill.jumlah ?? 1, 10);
            const harga = parseFloat(prefill.harga_satuan ?? 0);
            const subtotal = parseFloat(
                prefill.total_tagihan ?? harga * jumlah,
            );

            if (!kategoriNama || !kategoriId) {
                $row.find(".kategori-nama-input")
                    .val("Data Kategori Belum Ada")
                    .addClass("text-red-500 font-medium")
                    .attr("data-kategori-missing", "1");

                $row.find(".kategori-id-input").val("");
            } else {
                $row.find(".kategori-nama-input")
                    .val(kategoriNama)
                    .removeClass("text-red-500 font-medium")
                    .removeAttr("data-kategori-missing");

                $row.find(".kategori-id-input").val(kategoriId);
            }

            $row.find(".harga-input").val(harga);
            $row.find(".jumlah-input").val(jumlah);
            $row.find(".subtotal-input").val(formatRupiah(subtotal));
        }

        return $row;
    }

    function tambahRowLayananUpdate(prefill = null) {
        const $row = buatRowLayananUpdate(prefill);
        $("#orderItemsWrapperUpdate").append($row);

        initTomSelectLayananUpdate(
            $row.find(".layanan-select")[0],
            prefill
                ? {
                      id: prefill.layanan_id,
                      nama_layanan: prefill.nama_layanan ?? "",
                  }
                : null,
        );

        hitungTotalTagihanUpdate();
    }

    function hapusSatuRowLayananUpdate() {
        const $rows = $("#orderItemsWrapperUpdate .order-item");

        if ($rows.length <= 1) {
            Swal.fire({
                icon: "error",
                title: "Tidak bisa menghapus",
                text: "Minimal harus ada 1 layanan.",
                confirmButtonText: "OK",
            });
            return;
        }

        const $lastRow = $rows.last();
        const selectEl = $lastRow.find(".layanan-select")[0];

        if (selectEl?.tomselect) {
            selectEl.tomselect.destroy();
        }

        $lastRow.remove();
        hitungTotalTagihanUpdate();
        toggleSectionPoliJadwalUpdate();
    }

    function resetModalUpdateToDefault() {
        clearUpdateErrors();

        if ($("#formUpdateOrderLayanan")[0]) {
            $("#formUpdateOrderLayanan")[0].reset();
        }

        if (pasienTomUpdate) {
            pasienTomUpdate.destroy();
            pasienTomUpdate = null;
        }

        $("#pasien_search_update").html("");
        $("#order_layanan_id_update").val("");
        resetPreviewPasienUpdate();

        $("#orderItemsWrapperUpdate .layanan-select").each(function () {
            if (this.tomselect) {
                this.tomselect.destroy();
            }
        });

        $("#orderItemsWrapperUpdate").html(layananRowTemplateUpdateHtml);

        const $firstRow = $("#orderItemsWrapperUpdate .order-item").first();
        ensureHargaInputUpdate($firstRow);

        $firstRow
            .find(".layanan-select")
            .html('<option value="">Pilih layanan</option>');
        $firstRow
            .find(".kategori-nama-input")
            .val("")
            .removeClass("text-red-500 font-medium")
            .removeAttr("data-kategori-missing");
        $firstRow.find(".kategori-id-input").val("");
        $firstRow.find(".harga-input").val("");
        $firstRow.find(".jumlah-input").val(1);
        $firstRow.find(".subtotal-input").val(formatRupiah(0));

        $("#total_tagihan_update").val(formatRupiah(0));

        $("#section_poli_jadwal_update").addClass("hidden");
        $("#poli_id_select_update").val("");
        $("#jadwal_dokter_id_update")
            .html('<option value="">-- Pilih Jadwal Dokter --</option>')
            .val("")
            .prop("disabled", true);

        $("#dokter_id_update").val("");
        $("#info_jadwal_dokter_update").text("").addClass("hidden");
    }

    function isiModalUpdate(data) {
        $("#order_layanan_id_update").val(data.id ?? "");

        initTomSelectPasienUpdate(data.pasien ?? null);

        if (data.pasien) {
            tampilkanPreviewPasienUpdate({
                id: data.pasien.id ?? "",
                nama_pasien: data.pasien.nama_pasien ?? "-",
                no_emr: data.pasien.no_emr ?? "-",
                jenis_kelamin: data.pasien.jenis_kelamin ?? "-",
            });
        }

        $("#orderItemsWrapperUpdate").empty();

        if (Array.isArray(data.items) && data.items.length > 0) {
            data.items.forEach(function (item) {
                tambahRowLayananUpdate(item);
            });
        } else {
            tambahRowLayananUpdate();
        }

        $("#total_tagihan_update").val(
            formatRupiah(parseFloat(data.total_tagihan ?? 0)),
        );

        if (data.has_pemeriksaan) {
            $("#section_poli_jadwal_update").removeClass("hidden");
        } else {
            $("#section_poli_jadwal_update").addClass("hidden");
        }

        hitungTotalTagihanUpdate();
    }

    function kumpulkanItemsLayananUpdate() {
        const items = [];
        let adaError = false;
        let $firstInvalid = null;

        $("#orderItemsWrapperUpdate .order-item").each(function (index) {
            const $row = $(this);

            const $layanan = $row.find(".layanan-select");
            const $kategoriNama = $row.find(".kategori-nama-input");
            const $jumlah = $row.find(".jumlah-input");
            const $subtotal = $row.find(".subtotal-input");

            const layananId = $layanan.val();
            const kategoriLayananId = $row.find(".kategori-id-input").val();
            const jumlah = parseInt($jumlah.val() || 0, 10);
            const totalTagihanItem = parseRupiahToNumber($subtotal.val());

            const errors = [];

            if (!layananId) {
                errors.push("Layanan wajib dipilih.");
                setTomSelectErrorUpdate($layanan);
                if (!$firstInvalid) $firstInvalid = $layanan;
            }

            if (!kategoriLayananId) {
                const kategoriMissing =
                    $kategoriNama.attr("data-kategori-missing") === "1";

                if (kategoriMissing) {
                    errors.push("Silahkan Update Data Layanan Nya.");
                } else {
                    errors.push("Kategori belum terisi otomatis.");
                }

                setFieldErrorUpdate($kategoriNama, "");
                if (!$firstInvalid) $firstInvalid = $kategoriNama;
            }

            if (jumlah < 1) {
                errors.push("Jumlah minimal 1.");
                setFieldErrorUpdate($jumlah, "");
                if (!$firstInvalid) $firstInvalid = $jumlah;
            }

            if (totalTagihanItem <= 0) {
                errors.push("Subtotal belum valid.");
                setFieldErrorUpdate($subtotal, "");
                if (!$firstInvalid) $firstInvalid = $subtotal;
            }

            if (errors.length > 0) {
                adaError = true;
                setRowErrorUpdate(
                    $row,
                    `Baris ${index + 1}: ${errors.join(" ")}`,
                );
                return false;
            }

            items.push({
                layanan_id: parseInt(layananId, 10),
                kategori_layanan_id: parseInt(kategoriLayananId, 10),
                jumlah: jumlah,
                total_tagihan: totalTagihanItem,
            });
        });

        if ($firstInvalid && $firstInvalid.length) {
            const target = $firstInvalid[0].tomselect
                ? $firstInvalid[0].tomselect.wrapper
                : $firstInvalid[0];

            target.scrollIntoView({
                behavior: "smooth",
                block: "center",
            });
        }

        return { adaError, items };
    }

    function tampilkanServerValidationErrorsUpdate(errors) {
        Object.entries(errors).forEach(([key, messages]) => {
            const message = messages[0];

            if (key === "pasien_id") {
                setTomSelectErrorUpdate(
                    $("#pasien_search_update"),
                    message,
                    "#pasien_id_update-error",
                );
                return;
            }

            if (key === "total_tagihan") {
                setFieldErrorUpdate(
                    $("#total_tagihan_update"),
                    message,
                    "#total_tagihan_update-error",
                );
                return;
            }

            if (key === "poli_id") {
                setFieldErrorUpdate(
                    $("#poli_id_select_update"),
                    message,
                    "#poli_id_update-error",
                );
                return;
            }

            if (key === "jadwal_dokter_id") {
                setFieldErrorUpdate(
                    $("#jadwal_dokter_id_update"),
                    message,
                    "#jadwal_dokter_id_update-error",
                );
                return;
            }

            const match = key.match(/^items\.(\d+)\.(.+)$/);
            if (!match) return;

            const rowIndex = parseInt(match[1], 10);
            const field = match[2];
            const $row = $("#orderItemsWrapperUpdate .order-item").eq(rowIndex);

            if (!$row.length) return;

            if (field === "layanan_id") {
                setTomSelectErrorUpdate($row.find(".layanan-select"));
                setRowErrorUpdate($row, `Baris ${rowIndex + 1}: ${message}`);
            }

            if (field === "kategori_layanan_id") {
                setFieldErrorUpdate($row.find(".kategori-nama-input"), "");
                setRowErrorUpdate($row, `Baris ${rowIndex + 1}: ${message}`);
            }

            if (field === "jumlah") {
                setFieldErrorUpdate($row.find(".jumlah-input"), "");
                setRowErrorUpdate($row, `Baris ${rowIndex + 1}: ${message}`);
            }

            if (field === "total_tagihan") {
                setFieldErrorUpdate($row.find(".subtotal-input"), "");
                setRowErrorUpdate($row, `Baris ${rowIndex + 1}: ${message}`);
            }
        });
    }

    function submitUpdateOrderLayanan() {
        clearUpdateErrors();

        const orderLayananId = $("#order_layanan_id_update").val();
        const pasienId = $("#pasien_id_update").val();
        const totalTagihan = parseRupiahToNumber(
            $("#total_tagihan_update").val(),
        );
        const poliId = $("#poli_id_select_update").val();
        const jadwalDokterId = $("#jadwal_dokter_id_update").val();

        let hasError = false;

        if (!orderLayananId) {
            Swal.fire({
                icon: "error",
                title: "Gagal",
                text: "ID order layanan tidak ditemukan.",
            });
            return;
        }

        if (!pasienId) {
            setTomSelectErrorUpdate(
                $("#pasien_search_update"),
                "Pasien wajib dipilih.",
                "#pasien_id_update-error",
            );
            hasError = true;
        }

        const hasilItems = kumpulkanItemsLayananUpdate();
        if (hasilItems.adaError || hasilItems.items.length < 1) {
            hasError = true;
        }

        const butuhPemeriksaan = !$("#section_poli_jadwal_update").hasClass(
            "hidden",
        );

        if (butuhPemeriksaan && !poliId) {
            setFieldErrorUpdate(
                $("#poli_id_select_update"),
                "Poli wajib dipilih.",
                "#poli_id_update-error",
            );
            hasError = true;
        }

        if (butuhPemeriksaan && !jadwalDokterId) {
            setFieldErrorUpdate(
                $("#jadwal_dokter_id_update"),
                "Jadwal dokter wajib dipilih.",
                "#jadwal_dokter_id_update-error",
            );
            hasError = true;
        }

        if (hasError) {
            Swal.fire({
                icon: "error",
                title: "Validasi gagal",
                text: "Masih ada kolom yang belum valid. Cek bagian yang diberi tanda merah.",
            });
            return;
        }

        const payload = {
            order_layanan_id: parseInt(orderLayananId, 10),
            pasien_id: parseInt(pasienId, 10),
            total_tagihan: totalTagihan,
            items: hasilItems.items,
        };

        if (poliId) {
            payload.poli_id = parseInt(poliId, 10);
        }

        if (jadwalDokterId) {
            payload.jadwal_dokter_id = parseInt(jadwalDokterId, 10);
        }

        const updateUrl = $("#formUpdateOrderLayanan").data("url");

        axios
            .post(updateUrl, payload)
            .then(function (response) {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil",
                    text: response.data.message || "Data berhasil diupdate.",
                }).then(() => {
                    closeModalUpdateOrderLayanan();
                    if ($.fn.DataTable.isDataTable("#orderLayanan")) {
                        $("#orderLayanan").DataTable().ajax.reload(null, false);
                    }
                });
            })
            .catch(function (error) {
                if (error.response && error.response.status === 422) {
                    tampilkanServerValidationErrorsUpdate(
                        error.response.data.errors || {},
                    );
                    Swal.fire({
                        icon: "error",
                        title: "Validasi gagal",
                        text: "Masih ada data yang belum valid. Cek kolom yang diberi tanda merah.",
                    });
                    return;
                }

                Swal.fire({
                    icon: "error",
                    title: "Gagal",
                    text:
                        error.response?.data?.message ||
                        "Terjadi kesalahan saat mengupdate data.",
                });
            });
    }

    function openModalUpdateOrderLayanan(orderData) {
        resetModalUpdateToDefault();
        isiModalUpdate(orderData);

        lockBodyScroll();
        $("#modalUpdateOrderLayanan").removeClass("hidden").addClass("flex");
    }

    function closeModalUpdateOrderLayanan() {
        unlockBodyScroll();
        $("#modalUpdateOrderLayanan").removeClass("flex").addClass("hidden");
        resetModalUpdateToDefault();
    }

    $(document).on(
        "click",
        ".btn-open-modal-update-order-layanan",
        function () {
            const kodeTransaksi = $(this).data("kode-transaksi");
            const urlUpdateOrderLayanan = $(this).data(
                "url-update-order-layanan",
            );

            if (!urlUpdateOrderLayanan) {
                Swal.fire({
                    icon: "error",
                    title: "Gagal",
                    text: "URL data update tidak ditemukan.",
                });
                return;
            }

            axios
                .get(urlUpdateOrderLayanan)
                .then(function (response) {
                    console.log("RESP UPDATE:", response.data);

                    if (!response.data?.success || !response.data?.data) {
                        Swal.fire({
                            icon: "error",
                            title: "Gagal",
                            text:
                                response.data?.message ||
                                "Data order layanan tidak ditemukan.",
                        });
                        return;
                    }

                    openModalUpdateOrderLayanan(response.data.data);
                })
                .catch(function (error) {
                    console.error("Gagal ambil data update:", error);

                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text:
                            error.response?.data?.message ||
                            `Gagal mengambil data transaksi ${kodeTransaksi}.`,
                    });
                });
        },
    );

    $("#btnAddLayananRowUpdate").on("click", function () {
        tambahRowLayananUpdate();
    });

    $(document).on(
        "click",
        "#modalUpdateOrderLayanan .btn-remove-item-update",
        function () {
            hapusSatuRowLayananUpdate();
        },
    );

    $(document).on(
        "input change",
        "#orderItemsWrapperUpdate .jumlah-input",
        function () {
            const $row = $(this).closest(".order-item");

            let jumlah = parseInt($(this).val() || 1, 10);
            if (jumlah < 1) {
                jumlah = 1;
                $(this).val(1);
            }

            hitungSubtotalRowUpdate($row);
        },
    );

    $(
        "#buttonCloseModalUpdateOrderLayanan, #buttonCancaleModalUpdateOrderLayanan",
    ).on("click", function () {
        closeModalUpdateOrderLayanan();
    });

    $("#formUpdateOrderLayanan").on("submit", function (e) {
        e.preventDefault();
        submitUpdateOrderLayanan();
    });
});

/* ============================================================
 *  DELETE ORDER LAYANAN
 * ============================================================ */
$(function () {
    $("body").on("click", ".btn-delete-order-layanan", function () {
        const kodeTransaksi = $(this).data("kodeTransaksi");

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

/* ============================================================
 * DETAIL ORDER LAYANAN
 * ============================================================ */
$(function () {
    const rupiah = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
    });

    function lockPageScroll() {
        $("html, body").addClass("overflow-hidden");
    }

    function unlockPageScroll() {
        $("html, body").removeClass("overflow-hidden");
    }

    $(document).on("click", ".btn-detail-order-layanan", function (e) {
        e.preventDefault();

        const $btn = $(this);
        const kodeTransaksi = $btn.data("kode-transaksi");
        const urlDetail = $btn.data("url-detail-order-layanan");

        if (!urlDetail) {
            console.error("URL detail tidak ditemukan");
            return;
        }

        $.ajax({
            url: urlDetail,
            type: "GET",
            data: {
                kodeTransaksi: kodeTransaksi,
            },
            beforeSend: () => {
                $("#detail-kode-transaksi").text("Loading...");
                $("#detail-tanggal-order").text("Loading...");
                $("#detail-pasien").text("Loading...");
                $("#detail-metode-pembayaran").text("Loading...");
                $("#detail-tanggal-pembayaran").text("Loading...");
                $("#detail-label-bukti-pembayaran").text("Loading...");
                $("#detail-subtotal").text("Loading...");
                $("#detail-diskon").text("Loading...");
                $("#detail-potongan-pesanan").text("Loading...");
                $("#detail-total-bayar").text("Loading...");
                $("#detail-uang-diterima").text("Loading...");
                $("#detail-kembalian").text("Loading...");
                $("#detail-status-order-layanan").text("Loading...");

                $("#wrapper-detail-bukti-pembayaran").addClass("hidden");
                $("#img-detail-bukti-pembayaran").attr("src", "");
                $("#link-detail-bukti-pembayaran").attr("href", "#");

                $("#detail-order-layanan-items").html(`
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">
                            Sedang mengambil data detail...
                        </td>
                    </tr>
                `);
            },
            success: (response) => {
                const order = response.dataOrderLayanan ?? {};
                const details = response.dataDetailOrderLayanan ?? [];

                $("#detail-kode-transaksi").text(order.kode_transaksi ?? "-");
                $("#detail-tanggal-order").text(order.tanggal_order ?? "-");
                $("#detail-pasien").text(order.nama_pasien ?? "-");
                $("#detail-metode-pembayaran").text(
                    order.metode_pembayaran?.nama_metode_pembayaran ??
                        order.nama_metode_pembayaran ??
                        "-",
                );
                $("#detail-tanggal-pembayaran").text(
                    order.tanggal_pembayaran ?? "-",
                );

                $("#detail-subtotal").text(order.subtotal ?? 0);
                $("#detail-potongan-pesanan").text(
                    rupiah.format(Number(order.potongan_pesanan ?? 0)),
                );
                $("#detail-total-bayar").text(order.total_bayar ?? 0);
                $("#detail-uang-diterima").text(
                    rupiah.format(Number(order.uang_yang_diterima ?? 0)),
                );
                $("#detail-kembalian").text(
                    rupiah.format(Number(order.kembalian ?? 0)),
                );

                if ((order.diskon_tipe ?? "") === "persen") {
                    $("#detail-diskon").text(
                        `${Number(order.diskon_nilai ?? 0)}%`,
                    );
                } else if ((order.diskon_tipe ?? "") === "rupiah") {
                    $("#detail-diskon").text(
                        rupiah.format(Number(order.diskon_nilai ?? 0)),
                    );
                } else {
                    $("#detail-diskon").text("-");
                }

                $("#modal-detail-order-layanan")
                    .removeClass("hidden")
                    .addClass("flex");

                lockPageScroll();

                const status = order.status_order_layanan ?? "-";
                $("#detail-status-order-layanan")
                    .text(status)
                    .removeClass(
                        "bg-slate-100 text-slate-700 bg-emerald-100 text-emerald-700 bg-rose-100 text-rose-700",
                    );

                if (status === "Sudah Bayar") {
                    $("#detail-status-order-layanan").addClass(
                        "bg-emerald-100 text-emerald-700",
                    );
                } else if (status === "Belum Bayar") {
                    $("#detail-status-order-layanan").addClass(
                        "bg-rose-100 text-rose-700",
                    );
                } else {
                    $("#detail-status-order-layanan").addClass(
                        "bg-slate-100 text-slate-700",
                    );
                }

                if (order.bukti_pembayaran) {
                    $("#detail-label-bukti-pembayaran").text("Tersedia");
                    $("#wrapper-detail-bukti-pembayaran").removeClass("hidden");
                    $("#img-detail-bukti-pembayaran").attr(
                        "src",
                        order.bukti_pembayaran_url ?? order.bukti_pembayaran,
                    );
                    $("#link-detail-bukti-pembayaran").attr(
                        "href",
                        order.bukti_pembayaran_url ?? order.bukti_pembayaran,
                    );
                } else {
                    $("#detail-label-bukti-pembayaran").text("Tidak ada");
                    $("#wrapper-detail-bukti-pembayaran").addClass("hidden");
                    $("#img-detail-bukti-pembayaran").attr("src", "");
                    $("#link-detail-bukti-pembayaran").attr("href", "#");
                }

                if (details.length > 0) {
                    let rows = "";

                    details.forEach((item, index) => {
                        rows += `
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 text-slate-700">${index + 1}</td>
                                <td class="px-4 py-3 text-slate-700 font-medium">
                                    ${item.nama_layanan ?? "-"}
                                </td>
                                <td class="px-4 py-3 text-center text-slate-700">
                                    ${item.qty ?? 0}
                                </td>
                                <td class="px-4 py-3 text-right text-slate-700">
                                    ${rupiah.format(Number(item.harga_satuan ?? 0))}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-800">
                                    ${rupiah.format(Number(item.total_harga_item ?? 0))}
                                </td>
                            </tr>
                        `;
                    });

                    $("#detail-order-layanan-items").html(rows);
                } else {
                    $("#detail-order-layanan-items").html(`
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">
                                Tidak ada detail layanan.
                            </td>
                        </tr>
                    `);
                }
            },
            error: (xhr) => {
                console.log(xhr.responseText);

                $("#modal-detail-order-layanan")
                    .addClass("hidden")
                    .removeClass("flex");

                alert("Terjadi kesalahan saat mengambil detail order layanan");
            },
        });
    });

    $(document).on(
        "click",
        "#buttonCloseModalDetailOrderLayanan, #buttonTutupModalDetailOrderLayanan",
        function () {
            $("#modal-detail-order-layanan")
                .addClass("hidden")
                .removeClass("flex");

            unlockPageScroll();
        },
    );
});
