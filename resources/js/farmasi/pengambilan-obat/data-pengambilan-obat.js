import axios from "axios";
import { initFlowbite } from "flowbite";
import $ from "jquery";

initFlowbite();

$(function () {
    // =========================================================
    // GLOBAL AXIOS CONFIG (CSRF)
    // =========================================================
    const csrf = $('meta[name="csrf-token"]').attr("content");
    axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
    if (csrf) axios.defaults.headers.common["X-CSRF-TOKEN"] = csrf;

    // =========================================================
    // HELPERS
    // =========================================================
    const formatRupiah = (num) => {
        const n = Number(num || 0);
        return n.toLocaleString("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        });
    };

    const formatTanggalID = (data) => {
        if (!data) return "-";
        const date = new Date(data);
        return date.toLocaleDateString("id-ID", {
            timeZone: "Asia/Jakarta",
            day: "2-digit",
            month: "long",
            year: "numeric",
        });
    };

    // =========================================================
    // 1) CETAK STIKER OBAT
    // =========================================================
    $("body").on("click", ".btnCetakStikerObat", function () {
        const resepId = $(this).data("resep-id");
        if (!resepId) {
            Swal.fire({
                icon: "warning",
                title: "Data tidak lengkap!",
                text: "Resep ID tidak ditemukan.",
            });
            return;
        }

        window.open(
            `/farmasi/pengambilan-obat/cetak-stiker-obat/${resepId}`,
            "_blank",
        );
    });

    // =========================================================
    // 2) DATATABLE PENGAMBILAN OBAT + PAGINATION CUSTOM
    // =========================================================
    const $tableEl = $("#pengambilanResepObat");

    const table = $tableEl.DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/farmasi/pengambilan-obat/get-data",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "nama_pasien", name: "nama_pasien" },
            { data: "nama_poli", name: "nama_poli" },
            { data: "nama_dokter", name: "nama_dokter" },
            { data: "no_antrian", name: "no_antrian" },
            {
                data: "tanggal_kunjungan",
                name: "tanggal_kunjungan",
                render: (data) => formatTanggalID(data),
            },
            { data: "status", name: "status" },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
                className: "text-center whitespace-nowrap",
            },
        ],
        dom: "t",
        rowCallback: function (row) {
            $(row).addClass(
                "bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600",
            );
            $("td", row).addClass("px-6 py-4 text-gray-900 dark:text-white");
        },
    });

    // Search
    $("#obat_searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    // Page length
    $("#obat_pageLength").on("change", function () {
        table.page.len(parseInt($(this).val(), 10)).draw();
    });

    // Pagination custom
    const $info = $("#obat_customInfo");
    const $pagination = $("#obat_customPagination");

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${
                info.recordsDisplay
            } data (Halaman ${currentPage} dari ${totalPages})`,
        );

        $pagination.empty();

        const prevDisabled =
            currentPage === 1 ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(`
            <li>
                <a href="#" data-nav="prev"
                   class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">
                   Previous
                </a>
            </li>
        `);

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
            $pagination.append(`
                <li>
                    <a href="#" data-page="${i}"
                       class="page-number flex items-center justify-center px-3 h-8 border ${active}">
                       ${i}
                    </a>
                </li>
            `);
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(`
            <li>
                <a href="#" data-nav="next"
                   class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">
                   Next
                </a>
            </li>
        `);
    }

    $pagination.on("click", "a", function (e) {
        e.preventDefault();
        const $link = $(this);
        if ($link.hasClass("opacity-50")) return;

        const nav = $link.data("nav");
        const page = $link.data("page");

        if (nav === "prev") table.page("previous").draw("page");
        else if (nav === "next") table.page("next").draw("page");
        else if (page) table.page(parseInt(page, 10) - 1).draw("page");
    });

    table.on("draw", updatePagination);
    updatePagination();

    // =========================================================
    // 3) MODAL CREATE RESEP (TOMSELECT + DYNAMIC OBAT ROWS)
    // =========================================================
    const modalId = "#modalCreateResep";
    const formId = "#formCreateResep";
    const obatRowsId = "#obatRows";

    const urlStoreResep = "/farmasi/pengambilan-obat/create-data-resep-obat";
    const urlSearchObat = "/farmasi/pengambilan-obat/get-data-obat";
    const urlSearchPasien = "/farmasi/pengambilan-obat/get-data-pasien";
    const urlSearchDokter = "/farmasi/pengambilan-obat/get-data-dokter";

    // ✅ endpoint khusus depot by obat
    const urlDepotByObatId =
        "/farmasi/pengambilan-obat/get-data-depot-by-obat-id";

    let tomPasien = null;
    let tomDokter = null;

    const tomObatInstances = {}; // { idx: TomSelect }
    const tomDepotInstances = {}; // { idx: TomSelect }
    const selectedObatData = {}; // { idx: obatObject }

    const rowEl = (idx) => $(`${obatRowsId} .obat-row[data-index="${idx}"]`);

    const openModal = () => {
        $(modalId).removeClass("hidden");
        $("body").addClass("overflow-hidden");
    };

    const closeModal = () => {
        $(modalId).addClass("hidden");
        $("body").removeClass("overflow-hidden");
    };

    function initTomPasien() {
        if (tomPasien) {
            tomPasien.destroy();
            tomPasien = null;
        }

        tomPasien = new TomSelect(".tom-pasien", {
            valueField: "id",
            labelField: "nama_pasien",
            searchField: ["nama_pasien", "no_rm"],
            maxItems: 1,
            preload: false,
            create: false,
            placeholder: "Cari pasien...",
            load: function (query, callback) {
                if (!query || query.length < 2) return callback();
                axios
                    .get(urlSearchPasien, { params: { q: query } })
                    .then((res) => callback(res.data?.data ?? res.data ?? []))
                    .catch(() => callback());
            },
            render: {
                option(item, escape) {
                    return `
                      <div class="py-1 px-2">
                        <div class="font-medium text-sm">${escape(
                            item.nama_pasien || "",
                        )}</div>
                        <div class="text-xs text-slate-500">RM: ${escape(
                            item.no_rm ?? "-",
                        )}</div>
                      </div>
                    `;
                },
                item(item, escape) {
                    return `<div>${escape(item.nama_pasien || "")}</div>`;
                },
            },
        });
    }

    function initTomDokter() {
        if (tomDokter) {
            tomDokter.destroy();
            tomDokter = null;
        }

        tomDokter = new TomSelect(".tom-dokter", {
            valueField: "id",
            labelField: "nama_dokter",
            searchField: ["nama_dokter", "nama_spesialis"],
            maxItems: 1,
            preload: false,
            create: false,
            placeholder: "Cari dokter...",
            load: function (query, callback) {
                if (!query || query.length < 2) return callback();
                axios
                    .get(urlSearchDokter, { params: { q: query } })
                    .then((res) => callback(res.data?.data ?? res.data ?? []))
                    .catch(() => callback());
            },
            render: {
                option(item, escape) {
                    return `
                      <div class="py-1 px-2">
                        <div class="font-medium text-sm">${escape(
                            item.nama_dokter || "",
                        )}</div>
                        <div class="text-xs text-slate-500">${escape(
                            item.nama_spesialis ?? "",
                        )}</div>
                      </div>
                    `;
                },
                item(item, escape) {
                    return `<div>${escape(item.nama_dokter || "")}</div>`;
                },
            },
        });
    }

    // =========================
    // DEPOT TOMSELECT (aktif setelah obat dipilih)
    // =========================
    function resetDepotRow(idx, message = "Pilih obat terlebih dahulu.") {
        const $row = rowEl(idx);

        // destroy tomselect depot
        if (tomDepotInstances[idx]) {
            try {
                tomDepotInstances[idx].destroy();
            } catch (_) {}
            delete tomDepotInstances[idx];
        }

        const $select = $row.find(".depot-select");

        // penting: kosongkan + disable + set option default
        $select.empty().append(`<option value="">Pilih depot</option>`);
        $select.prop("disabled", true);

        $row.find(".depot-hint").text(message);

        // jumlah ikut dikunci
        $row.find(".jumlah-input").prop("disabled", true).val(1);
    }

    function initTomSelectDepot(idx, depots = []) {
        const $row = rowEl(idx);
        const $select = $row.find(".depot-select");

        // destroy lama
        if (tomDepotInstances[idx]) {
            try {
                tomDepotInstances[idx].destroy();
            } catch (_) {}
            delete tomDepotInstances[idx];
        }

        // reset option
        $select.empty().append(`<option value="">Pilih depot</option>`);

        if (!Array.isArray(depots) || depots.length === 0) {
            $select.prop("disabled", true);
            $row.find(".depot-hint").text("Obat ini belum memiliki depot.");
            return;
        }

        depots.forEach((d) => {
            $select.append(`<option value="${d.id}">${d.nama_depot}</option>`);
        });

        // enable baru bisa diklik
        $select.prop("disabled", false);
        $row.find(".depot-hint").text("Pilih depot.");

        // ✅ init TomSelect depot (ambil dari <option>)
        tomDepotInstances[idx] = new TomSelect($select[0], {
            maxItems: 1,
            create: false,
            placeholder: "Pilih depot...",
            onChange: function (value) {
                const $jumlah = $row.find(".jumlah-input");
                if (value) {
                    $jumlah.prop("disabled", false);
                    if (!$jumlah.val()) $jumlah.val(1);
                } else {
                    $jumlah.prop("disabled", true).val(1);
                }
                recalcRowTotal(idx);
            },
        });
    }

    function obatRowTemplate(idx) {
        return `
        <div class="obat-row bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl p-3 sm:p-4"
             data-index="${idx}">

          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="text-xs text-slate-500 dark:text-slate-400">Obat #${
                  idx + 1
              }</p>
              <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Detail Obat</p>
            </div>

            <button type="button"
              class="btnRemoveObat inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded-xl
                     border border-rose-200 dark:border-rose-500/40 text-rose-600 dark:text-rose-300
                     bg-white/70 dark:bg-slate-900/20 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition">
              <i class="fa-solid fa-trash text-[11px]"></i><span>Hapus</span>
            </button>
          </div>

          <div class="mt-3 grid grid-cols-1 lg:grid-cols-12 gap-3">

            <div class="lg:col-span-4">
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
                Nama Obat <span class="text-rose-500">*</span>
              </label>

              <select class="obat-select w-full" name="obat[${idx}][obat_id]" data-index="${idx}">
                <option value="">-- Cari Obat --</option>
              </select>

              <div class="mt-1 flex items-center justify-between text-[11px] text-slate-400">
                <span class="obat-kode">Kode: -</span>
                <span class="obat-stok">Stok: -</span>
              </div>
            </div>

            <div class="lg:col-span-2">
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
                Depot <span class="text-rose-500">*</span>
              </label>

              <select class="depot-select w-full"
                  name="obat[${idx}][depot_id]"
                  data-index="${idx}"
                  disabled>
                  <option value="">Pilih depot</option>
              </select>

              <p class="mt-1 text-[11px] text-slate-400 depot-hint">
                  Pilih obat terlebih dahulu.
              </p>
            </div>

            <div class="lg:col-span-2">
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
                Jumlah <span class="text-rose-500">*</span>
              </label>
              <input type="number" min="1" value="1" disabled
                class="jumlah-input w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700
                       text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-sky-500 focus:border-sky-500"
                name="obat[${idx}][jumlah]" />
            </div>

            <div class="lg:col-span-2">
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">Satuan</label>
              <input type="text" readonly
                class="satuan-input w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-700/60
                       text-slate-800 dark:text-slate-100 px-3 py-2 text-sm"
                name="obat[${idx}][satuan]" placeholder="-" />
            </div>

            <div class="lg:col-span-2">
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">Harga Umum /unit</label>
              <input type="text" readonly
                class="harga-input w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-700/60
                       text-slate-800 dark:text-slate-100 px-3 py-2 text-sm"
                name="obat[${idx}][harga_umum]" placeholder="Rp0" />
              <input type="hidden" class="harga-raw" value="0">
            </div>

            <div class="lg:col-span-2">
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">Harga Total</label>
              <input type="text" readonly
                class="total-input w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-700/60
                       text-slate-800 dark:text-slate-100 px-3 py-2 text-sm"
                name="obat[${idx}][total]" placeholder="Rp0" />
              <input type="hidden" class="total-raw" value="0">
            </div>

            <div class="lg:col-span-6">
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
                Dosis <span class="text-rose-500">*</span>
              </label>
              <input type="text" name="obat[${idx}][dosis]" placeholder="contoh: 3x1"
                class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700
                       text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-sky-500 focus:border-sky-500" />
            </div>

            <div class="lg:col-span-6">
              <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">Keterangan</label>
              <input type="text" name="obat[${idx}][keterangan]" placeholder="sesudah makan"
                class="w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700
                       text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-sky-500 focus:border-sky-500" />
            </div>

          </div>
        </div>`;
    }

    const getNextIndex = () => $(obatRowsId).children(".obat-row").length;

    function recalcRowTotal(idx) {
        const $row = rowEl(idx);
        const qty = Number($row.find(".jumlah-input").val() || 0);
        const price = Number($row.find(".harga-raw").val() || 0);
        const total = Math.max(0, qty) * Math.max(0, price);

        $row.find(".total-raw").val(total);
        $row.find(".total-input").val(formatRupiah(total));
    }

    function initTomSelectObatRow(idx) {
        const $select = $(
            `${obatRowsId} .obat-row[data-index="${idx}"] .obat-select`,
        );

        if (tomObatInstances[idx]) {
            try {
                tomObatInstances[idx].destroy();
            } catch (_) {}
            delete tomObatInstances[idx];
        }

        // pastikan depot terkunci sebelum obat dipilih
        resetDepotRow(idx, "Pilih obat terlebih dahulu.");

        const ts = new TomSelect($select[0], {
            valueField: "id",
            labelField: "nama_obat",
            searchField: ["nama_obat", "kode_obat"],
            maxItems: 1,
            preload: false,
            create: false,
            placeholder: "Cari obat...",
            load: function (query, callback) {
                if (!query || query.length < 2) return callback();
                axios
                    .get(urlSearchObat, { params: { q: query } })
                    .then((res) => callback(res.data?.data ?? res.data ?? []))
                    .catch(() => callback());
            },
            render: {
                option: function (item, escape) {
                    return `
                      <div class="py-2 px-2">
                        <div class="flex items-center justify-between gap-3">
                          <div class="text-sm font-semibold">${escape(
                              item.nama_obat || "",
                          )}</div>
                          <div class="text-[11px] text-slate-500">Kode: ${escape(
                              item.kode_obat || "-",
                          )}</div>
                        </div>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-[11px] text-slate-500">
                          <span>Stok: <b>${escape(
                              String(item.stok_tersedia ?? 0),
                          )}</b></span>
                          <span>•</span>
                          <span>Satuan: <b>${escape(
                              item.satuan || "-",
                          )}</b></span>
                          <span>•</span>
                          <span>Harga: <b>${escape(
                              formatRupiah(item.harga_umum || 0),
                          )}</b></span>
                        </div>
                      </div>
                    `;
                },
                item: function (item, escape) {
                    return `<div>${escape(item.nama_obat || "")}</div>`;
                },
            },
            onChange: function (value) {
                const $row = rowEl(idx);

                // RESET kalau obat kosong
                if (!value) {
                    selectedObatData[idx] = null;

                    $row.find(".obat-kode").text("Kode: -");
                    $row.find(".obat-stok").text("Stok: -");

                    resetDepotRow(idx, "Pilih obat terlebih dahulu.");

                    $row.find(".satuan-input").val("-");
                    $row.find(".harga-input").val("Rp0");
                    $row.find(".harga-raw").val(0);
                    $row.find(".total-input").val("Rp0");
                    $row.find(".total-raw").val(0);
                    return;
                }

                // OBAT dipilih
                const obat = ts.options[value];
                selectedObatData[idx] = obat;

                $row.find(".obat-kode").text(`Kode: ${obat.kode_obat || "-"}`);
                $row.find(".obat-stok").text(
                    `Stok: ${obat.stok_tersedia ?? 0}`,
                );
                $row.find(".satuan-input").val(obat.satuan || "-");

                $row.find(".harga-raw").val(Number(obat.harga_umum || 0));
                $row.find(".harga-input").val(
                    formatRupiah(obat.harga_umum || 0),
                );

                // ✅ ambil depot via endpoint by obat_id
                resetDepotRow(idx, "Memuat depot...");

                axios
                    .get(urlDepotByObatId, { params: { obat_id: value } })
                    .then((res) => {
                        const depots = res.data?.data ?? res.data ?? [];
                        initTomSelectDepot(idx, depots);
                    })
                    .catch((err) => {
                        console.error("LOAD DEPOT ERROR:", err);
                        resetDepotRow(idx, "Gagal memuat depot. Coba lagi.");
                    });

                // jumlah tetap disable sampai depot dipilih
                $row.find(".jumlah-input").prop("disabled", true).val(1);

                recalcRowTotal(idx);
            },
        });

        tomObatInstances[idx] = ts;
    }

    function addObatRow() {
        const idx = getNextIndex();
        $(obatRowsId).append(obatRowTemplate(idx));
        initTomSelectObatRow(idx);
    }

    function destroyAllTomInstances() {
        Object.keys(tomObatInstances).forEach((k) => {
            try {
                tomObatInstances[k].destroy();
            } catch (_) {}
            delete tomObatInstances[k];
        });

        Object.keys(tomDepotInstances).forEach((k) => {
            try {
                tomDepotInstances[k].destroy();
            } catch (_) {}
            delete tomDepotInstances[k];
        });
    }

    function reindexRows() {
        // destroy semua instance (wajib biar gak nyangkut)
        destroyAllTomInstances();

        const rows = $(obatRowsId).children(".obat-row");

        rows.each(function (newIdx) {
            const $row = $(this);
            $row.attr("data-index", newIdx);
            $row.find("p.text-xs").text(`Obat #${newIdx + 1}`);

            $row.find("select.obat-select")
                .attr("name", `obat[${newIdx}][obat_id]`)
                .attr("data-index", newIdx);

            $row.find("select.depot-select")
                .attr("name", `obat[${newIdx}][depot_id]`)
                .attr("data-index", newIdx);

            $row.find(".jumlah-input").attr("name", `obat[${newIdx}][jumlah]`);

            $row.find('input[name*="[satuan]"]').attr(
                "name",
                `obat[${newIdx}][satuan]`,
            );
            $row.find('input[name*="[harga_umum]"]').attr(
                "name",
                `obat[${newIdx}][harga_umum]`,
            );
            $row.find('input[name*="[total]"]').attr(
                "name",
                `obat[${newIdx}][total]`,
            );
            $row.find('input[name*="[dosis]"]').attr(
                "name",
                `obat[${newIdx}][dosis]`,
            );
            $row.find('input[name*="[keterangan]"]').attr(
                "name",
                `obat[${newIdx}][keterangan]`,
            );
        });

        // init tomselect ulang
        rows.each(function () {
            const idx = Number($(this).data("index"));
            initTomSelectObatRow(idx);
        });
    }

    // Open modal
    $(document).on("click", "#buttonModalCreateResep", function () {
        openModal();

        initTomPasien();
        initTomDokter();

        if ($(obatRowsId).children(".obat-row").length === 0) addObatRow();
    });

    // Close modal
    $(document).on(
        "click",
        "#btnCloseModalCreateResepTop, #btnCloseModalCreateResepBottom",
        function () {
            closeModal();
        },
    );

    // Click backdrop close
    $(document).on("click", modalId, function (e) {
        if (e.target === this) closeModal();
    });

    // Add obat row
    $(document).on("click", "#btnTambahObat", function () {
        addObatRow();
    });

    // Remove row
    $(document).on("click", ".btnRemoveObat", function () {
        const $row = $(this).closest(".obat-row");
        const idx = Number($row.data("index"));

        if (tomObatInstances[idx]) {
            try {
                tomObatInstances[idx].destroy();
            } catch (_) {}
            delete tomObatInstances[idx];
        }
        if (tomDepotInstances[idx]) {
            try {
                tomDepotInstances[idx].destroy();
            } catch (_) {}
            delete tomDepotInstances[idx];
        }

        delete selectedObatData[idx];

        $row.remove();

        if ($(obatRowsId).children(".obat-row").length === 0) addObatRow();
        reindexRows();
    });

    // jumlah change => recalc total
    $(document).on("input", ".jumlah-input", function () {
        const idx = Number($(this).closest(".obat-row").data("index"));
        recalcRowTotal(idx);
    });

    // Submit create resep
    $(document).on("submit", formId, function (e) {
        e.preventDefault();

        const $btn = $("#btnSubmitCreateResep");
        $btn.prop("disabled", true).addClass("opacity-70 cursor-not-allowed");

        const formEl = $(this)[0];
        const formData = new FormData(formEl);

        axios
            .post(urlStoreResep, formData, {
                headers: { "Content-Type": "multipart/form-data" },
            })
            .then((res) => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: res.data?.message || "Resep berhasil disimpan!",
                    timer: 1400,
                    showConfirmButton: false,
                });

                formEl.reset();
                $(obatRowsId).empty();

                destroyAllTomInstances();

                addObatRow();
                closeModal();

                if ($("#pengambilanResepObat").length) {
                    $("#pengambilanResepObat")
                        .DataTable()
                        .ajax.reload(null, false);
                }
            })
            .catch((err) => {
                console.error("STORE RESEP ERROR:", err);

                if (err.response && err.response.status === 422) {
                    const errors = err.response.data.errors || {};
                    const firstKey = Object.keys(errors)[0];
                    const firstMsg = firstKey
                        ? errors[firstKey][0]
                        : "Validasi gagal.";
                    Swal.fire({
                        icon: "warning",
                        title: "Validasi Gagal",
                        text: firstMsg,
                    });
                    return;
                }

                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: "Gagal menyimpan resep. Silakan coba lagi.",
                });
            })
            .finally(() => {
                $btn.prop("disabled", false).removeClass(
                    "opacity-70 cursor-not-allowed",
                );
            });
    });

    // =========================================================
    // 4) UPDATE STATUS PENGAMBILAN OBAT
    // =========================================================
    $("body").on("click", ".btnUpdateStatus", function () {
        const resepId = $(this).data("resep-id");
        let statusNow = $(this).data("status"); // ✅ status dari DB (2 enum)
        let obatRaw = $(this).data("obat");

        // jaga-jaga kalau data-obat kebaca string
        if (typeof obatRaw === "string") {
            try {
                obatRaw = JSON.parse(obatRaw);
            } catch (_) {
                obatRaw = null;
            }
        }

        // build obatData
        let obatData = [];
        if (Array.isArray(obatRaw)) {
            obatData = obatRaw
                .map((item) => {
                    const id = item?.id ?? item?.obat_id ?? null;
                    const jumlah = item?.jumlah ?? item?.qty ?? null;
                    if (!id || !jumlah) return null;
                    return { id: Number(id), jumlah: Number(jumlah) };
                })
                .filter(Boolean);
        }

        if (!resepId) {
            Swal.fire({
                icon: "warning",
                title: "Oops",
                text: "Resep ID tidak ditemukan.",
            });
            return;
        }

        // default kalau kosong
        statusNow = statusNow || "waiting";

        Swal.fire({
            title: "Update Status",
            text: `Status sekarang: ${statusNow}. Lanjut update?`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Iya",
            cancelButtonText: "Batal",
        }).then((result) => {
            if (!result.isConfirmed) return;

            axios
                .post(`/farmasi/pengambilan-obat/update-status-resep`, {
                    resep_id: resepId,
                    status_now: statusNow, // ✅ hanya kirim status yang sekarang
                    obat_list: obatData, // tetap kirim untuk cek/kurangi stok saat done
                })
                .then((response) => {
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil!",
                        text:
                            response.data?.message ||
                            "Status berhasil diperbarui.",
                        timer: 1400,
                        showConfirmButton: false,
                    }).then(() => {
                        $("#pengambilanResepObat")
                            .DataTable()
                            .ajax.reload(null, false);
                    });
                })
                .catch((error) => {
                    const msg =
                        error.response?.data?.message || "Terjadi kesalahan.";
                    Swal.fire({
                        icon: "warning",
                        title: "Tidak bisa diupdate!",
                        text: msg,
                    });
                });
        });
    });

    // =========================================================
    // 5) MODAL UPDATE RESEP (FETCH BY ID + SUBMIT PUT)
    // =========================================================
    const modalUpdateId = "#modalUpdateResep";
    const formUpdateId = "#formUpdateResep";
    const obatRowsUpdateId = "#obatRowsUpdate";

    const urlShowResep = (id) =>
        `/farmasi/pengambilan-obat/get-data-resep-obat-id/${id}`; // GET detail
    const urlUpdateResep = (id) =>
        `/farmasi/pengambilan-obat/update-data-resep-obat/${id}`; // PUT update

    let resepIdAktif = null;

    // TomSelect instances untuk UPDATE
    const tomObatInstancesUpdate = {};
    const selectedObatDataUpdate = {};

    const rowElUpdate = (idx) =>
        $(`${obatRowsUpdateId} .obat-row[data-index="${idx}"]`);

    const openModalUpdate = () => {
        $(modalUpdateId).removeClass("hidden");
        $("body").addClass("overflow-hidden");
    };

    const closeModalUpdate = () => {
        $(modalUpdateId).addClass("hidden");
        $("body").removeClass("overflow-hidden");
    };

    // template row update (reuse dari create, cukup bedakan name-nya)
    function obatRowTemplateUpdate(idx) {
        // NOTE: depot saya biarkan ada, tapi tidak saya wajibkan untuk update (karena tabel pivot tidak punya depot_id)
        return `
      <div class="obat-row bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-2xl p-3 sm:p-4"
          data-index="${idx}">

        <div class="flex items-start justify-between gap-3">
          <div>
            <p class="text-xs text-slate-500 dark:text-slate-400">Obat #${
                idx + 1
            }</p>
            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">Detail Obat</p>
          </div>

          <button type="button"
            class="btnRemoveObatUpdate inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded-xl
                   border border-rose-200 dark:border-rose-500/40 text-rose-600 dark:text-rose-300
                   bg-white/70 dark:bg-slate-900/20 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition">
            <i class="fa-solid fa-trash text-[11px]"></i><span>Hapus</span>
          </button>
        </div>

        <div class="mt-3 grid grid-cols-1 lg:grid-cols-12 gap-3">

          <div class="lg:col-span-4">
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
              Nama Obat <span class="text-rose-500">*</span>
            </label>

            <select class="obat-select-update w-full" name="obat[${idx}][obat_id]" data-index="${idx}">
              <option value="">-- Cari Obat --</option>
            </select>

            <div class="mt-1 flex items-center justify-between text-[11px] text-slate-400">
              <span class="obat-kode">Kode: -</span>
              <span class="obat-stok">Stok: -</span>
            </div>
          </div>

          <div class="lg:col-span-2">
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
              Jumlah <span class="text-rose-500">*</span>
            </label>
            <input type="number" min="1" value="1"
              class="jumlah-input-update w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700
                     text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-sky-500 focus:border-sky-500"
              name="obat[${idx}][jumlah]" />
          </div>

          <div class="lg:col-span-2">
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">Satuan</label>
            <input type="text" readonly
              class="satuan-input w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-700/60
                     text-slate-800 dark:text-slate-100 px-3 py-2 text-sm"
              placeholder="-" />
          </div>

          <div class="lg:col-span-2">
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">Harga /unit</label>
            <input type="text" readonly
              class="harga-input w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-700/60
                     text-slate-800 dark:text-slate-100 px-3 py-2 text-sm"
              placeholder="Rp0" />
            <input type="hidden" class="harga-raw" value="0">
          </div>

          <div class="lg:col-span-2">
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">Harga Total</label>
            <input type="text" readonly
              class="total-input w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-slate-100 dark:bg-slate-700/60
                     text-slate-800 dark:text-slate-100 px-3 py-2 text-sm"
              placeholder="Rp0" />
            <input type="hidden" class="total-raw" value="0">
          </div>

          <div class="lg:col-span-6">
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">
              Dosis <span class="text-rose-500">*</span>
            </label>
            <input type="text" name="obat[${idx}][dosis]" placeholder="contoh: 3x1"
              class="dosis-input w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700
                     text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-sky-500 focus:border-sky-500" />
          </div>

          <div class="lg:col-span-6">
            <label class="block text-xs font-medium text-slate-700 dark:text-slate-200 mb-1">Keterangan</label>
            <input type="text" name="obat[${idx}][keterangan]" placeholder="sesudah makan"
              class="ket-input w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-700
                     text-slate-800 dark:text-slate-100 px-3 py-2 text-sm focus:ring-sky-500 focus:border-sky-500" />
          </div>

        </div>
      </div>`;
    }

    const getNextIndexUpdate = () =>
        $(obatRowsUpdateId).children(".obat-row").length;

    function recalcRowTotalUpdate(idx) {
        const $row = rowElUpdate(idx);
        const qty = Number($row.find(".jumlah-input-update").val() || 0);
        const price = Number($row.find(".harga-raw").val() || 0);
        const total = Math.max(0, qty) * Math.max(0, price);
        $row.find(".total-raw").val(total);
        $row.find(".total-input").val(formatRupiah(total));
    }

    function destroyAllTomUpdate() {
        Object.keys(tomObatInstancesUpdate).forEach((k) => {
            try {
                tomObatInstancesUpdate[k].destroy();
            } catch (_) {}
            delete tomObatInstancesUpdate[k];
        });
    }

    function initTomSelectObatRowUpdate(idx) {
        const $select = $(
            `${obatRowsUpdateId} .obat-row[data-index="${idx}"] .obat-select-update`,
        );

        if (tomObatInstancesUpdate[idx]) {
            try {
                tomObatInstancesUpdate[idx].destroy();
            } catch (_) {}
            delete tomObatInstancesUpdate[idx];
        }

        const ts = new TomSelect($select[0], {
            valueField: "id",
            labelField: "nama_obat",
            searchField: ["nama_obat", "kode_obat"],
            maxItems: 1,
            preload: false,
            create: false,
            placeholder: "Cari obat...",
            load: function (query, callback) {
                if (!query || query.length < 2) return callback();
                axios
                    .get(urlSearchObat, { params: { q: query } })
                    .then((res) => callback(res.data?.data ?? res.data ?? []))
                    .catch(() => callback());
            },
            onChange: function (value) {
                const $row = rowElUpdate(idx);

                if (!value) {
                    selectedObatDataUpdate[idx] = null;
                    $row.find(".obat-kode").text("Kode: -");
                    $row.find(".obat-stok").text("Stok: -");
                    $row.find(".satuan-input").val("-");
                    $row.find(".harga-raw").val(0);
                    $row.find(".harga-input").val("Rp0");
                    $row.find(".total-raw").val(0);
                    $row.find(".total-input").val("Rp0");
                    return;
                }

                const obat = ts.options[value];
                selectedObatDataUpdate[idx] = obat;

                $row.find(".obat-kode").text(`Kode: ${obat.kode_obat || "-"}`);
                $row.find(".obat-stok").text(
                    `Stok: ${obat.stok_tersedia ?? 0}`,
                );
                $row.find(".satuan-input").val(obat.satuan || "-");
                $row.find(".harga-raw").val(Number(obat.harga_umum || 0));
                $row.find(".harga-input").val(
                    formatRupiah(obat.harga_umum || 0),
                );

                recalcRowTotalUpdate(idx);
            },
        });

        tomObatInstancesUpdate[idx] = ts;
    }

    function addObatRowUpdate(prefill = null) {
        const idx = getNextIndexUpdate();
        $(obatRowsUpdateId).append(obatRowTemplateUpdate(idx));
        initTomSelectObatRowUpdate(idx);

        // prefill kalau ada
        if (prefill) {
            const $row = rowElUpdate(idx);

            // inject option + setValue (biar tomselect langsung selected tanpa search)
            const ts = tomObatInstancesUpdate[idx];
            ts.addOption({
                id: prefill.obat_id,
                nama_obat: prefill.nama_obat,
                kode_obat: prefill.kode_obat,
                stok_tersedia: prefill.stok,
                satuan: prefill.satuan,
                harga_umum: prefill.harga_umum,
            });
            ts.setValue(String(prefill.obat_id), true);

            $row.find(".jumlah-input-update").val(prefill.jumlah ?? 1);
            $row.find(".dosis-input").val(prefill.dosis ?? "");
            $row.find(".ket-input").val(prefill.keterangan ?? "");

            // set info tampil
            $row.find(".obat-kode").text(`Kode: ${prefill.kode_obat || "-"}`);
            $row.find(".obat-stok").text(`Stok: ${prefill.stok ?? 0}`);
            $row.find(".satuan-input").val(prefill.satuan || "-");
            $row.find(".harga-raw").val(Number(prefill.harga_umum || 0));
            $row.find(".harga-input").val(
                formatRupiah(prefill.harga_umum || 0),
            );

            recalcRowTotalUpdate(idx);
        }
    }

    // Open modal update via tombol DataTables
    $("body").on("click", ".btnUpdateResepObat", function () {
        const id = $(this).data("resep-id");
        if (!id) return;

        resepIdAktif = id;

        // bersihkan
        $("#update_resep_id").val(id);
        $(obatRowsUpdateId).empty();
        destroyAllTomUpdate();

        axios
            .get(urlShowResep(id))
            .then((res) => {
                const data = res.data?.data;

                $("#update_tanggal_resep").val(data?.tanggal_resep || "");
                $("#update_nama_pasien").val(data?.nama_pasien || "-");
                $("#update_nama_poli").val(data?.nama_poli || "-");
                $("#update_nama_dokter").val(data?.nama_dokter || "-");

                const items = data?.items || [];
                if (!items.length) {
                    addObatRowUpdate(null);
                } else {
                    items.forEach((it) => addObatRowUpdate(it));
                }

                openModalUpdate();
            })
            .catch((err) => {
                console.error("SHOW RESEP ERROR:", err);
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: "Gagal mengambil detail resep.",
                });
            });
    });

    // Add row update
    $(document).on("click", "#btnTambahObatUpdate", function () {
        addObatRowUpdate(null);
    });

    // Remove row update
    $(document).on("click", ".btnRemoveObatUpdate", function () {
        const $row = $(this).closest(".obat-row");
        const idx = Number($row.data("index"));

        if (tomObatInstancesUpdate[idx]) {
            try {
                tomObatInstancesUpdate[idx].destroy();
            } catch (_) {}
            delete tomObatInstancesUpdate[idx];
        }
        delete selectedObatDataUpdate[idx];

        $row.remove();

        // reindex sederhana: (kalau mau rapih seperti create, bisa dibuatkan fungsi reindex khusus update)
        if ($(obatRowsUpdateId).children(".obat-row").length === 0)
            addObatRowUpdate(null);
    });

    // qty change update
    $(document).on("input", ".jumlah-input-update", function () {
        const idx = Number($(this).closest(".obat-row").data("index"));
        recalcRowTotalUpdate(idx);
    });

    // Close modal update
    $(document).on(
        "click",
        "#btnCloseModalUpdateResepTop, #btnCloseModalUpdateResepBottom",
        function () {
            closeModalUpdate();
        },
    );

    // click backdrop
    $(document).on("click", modalUpdateId, function (e) {
        if (e.target === this) closeModalUpdate();
    });

    // Submit update
    $(document).on("submit", formUpdateId, function (e) {
        e.preventDefault();
        if (!resepIdAktif) return;

        const $btn = $("#btnSubmitUpdateResep");
        $btn.prop("disabled", true).addClass("opacity-70 cursor-not-allowed");

        const formEl = $(this)[0];
        const formData = new FormData(formEl);

        axios
            .post(urlUpdateResep(resepIdAktif), formData, {
                headers: { "Content-Type": "multipart/form-data" },
            })
            .then((res) => {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: res.data?.message || "Resep berhasil diupdate!",
                    timer: 1400,
                    showConfirmButton: false,
                });

                closeModalUpdate();
                $("#pengambilanResepObat").DataTable().ajax.reload(null, false);
            })
            .catch((err) => {
                console.error("UPDATE RESEP ERROR:", err);

                if (err.response && err.response.status === 422) {
                    const errors = err.response.data.errors || {};
                    const firstKey = Object.keys(errors)[0];
                    const firstMsg = firstKey
                        ? errors[firstKey][0]
                        : err.response.data.message || "Validasi gagal.";
                    Swal.fire({
                        icon: "warning",
                        title: "Validasi Gagal",
                        text: firstMsg,
                    });
                    return;
                }

                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text:
                        err.response?.data?.message ||
                        "Gagal update resep. Silakan coba lagi.",
                });
            })
            .finally(() => {
                $btn.prop("disabled", false).removeClass(
                    "opacity-70 cursor-not-allowed",
                );
            });
    });
});

$(document).on("click", ".btnLihatDetail", function () {
    const resepId = $(this).data("resep-id");

    // Reset dan Tampilkan Modal
    $("#resep_id_display").text(resepId);
    $("#resep_obat_list").html(`
        <tr>
            <td colspan="4" class="text-center py-10">
                <i class="fa-solid fa-spinner fa-spin text-teal-500 text-2xl"></i>
                <p class="text-gray-500 mt-2 text-xs font-medium">Mengambil data resep...</p>
            </td>
        </tr>
    `);
    $("#modalDetailResep").removeClass("hidden");

    $.ajax({
        url: `/farmasi/pengambilan-obat/get-data-resep-obat-detail/${resepId}`,
        method: "GET",
        success: function (response) {
            let html = "";

            // Akses data obat sesuai struktur JSON kamu
            const dataObat = response.data.obat;

            if (dataObat && dataObat.length > 0) {
                dataObat.forEach((item) => {
                    // Gunakan data dari pivot agar akurat sesuai resep tersebut
                    const nama = item.nama_obat || "-";
                    const jml = item.pivot
                        ? item.pivot.jumlah
                        : item.jumlah || 0;
                    const dss = item.pivot
                        ? item.pivot.dosis
                        : item.dosis || "-";
                    const ket = item.pivot
                        ? item.pivot.keterangan
                        : item.keterangan || "-";

                    html += `
                        <tr class="hover:bg-teal-50/30 transition-colors">
                            <td class="px-4 py-3">
                                <div class="font-bold text-gray-800">${nama}</div>
                                <div class="text-[10px] text-gray-500">${item.kode_obat}</div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-block bg-teal-100 text-teal-700 px-2 py-0.5 rounded font-bold">
                                    ${jml}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 font-medium">${dss}</td>
                            <td class="px-4 py-3 text-gray-500 italic">${ket}</td>
                        </tr>
                    `;
                });
            } else {
                html =
                    '<tr><td colspan="4" class="text-center py-10 text-gray-400 font-medium">Tidak ada rincian obat ditemukan.</td></tr>';
            }

            $("#resep_obat_list").html(html);
        },
        error: function () {
            $("#resep_obat_list").html(
                '<tr><td colspan="4" class="text-center py-10 text-red-500 font-bold">Gagal mengambil data dari server.</td></tr>',
            );
        },
    });
});

// Fungsi untuk menutup modal
function closeModalDetail() {
    $("#modalDetailResep").addClass("hidden");
}

$(document).on("click", "#btnCloseModal", function () {
    closeModalDetail();
});
