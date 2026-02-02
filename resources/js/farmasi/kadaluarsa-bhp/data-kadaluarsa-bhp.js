import axios from "axios";
import $ from "jquery";

$(function () {
    const warningThreshold = 90; // hari ke depan untuk warning kecil
    const tableThreshold = 90; // hari ke depan untuk tabel besar

    $("#warningThresholdText").text(warningThreshold);
    $("#tableThresholdText").text(tableThreshold);

    // ==========================
    // HELPER
    // ==========================
    function formatTanggalIndo(dateStr) {
        if (!dateStr) return "-";
        const d = new Date(dateStr);
        if (Number.isNaN(d.getTime())) return dateStr;

        return new Intl.DateTimeFormat("id-ID", {
            day: "2-digit",
            month: "long",
            year: "numeric",
        }).format(d);
    }

    function hitungSisaHari(dateStr) {
        if (!dateStr) return null;
        const today = new Date();
        const target = new Date(dateStr);
        const diffTime = target.getTime() - today.setHours(0, 0, 0, 0);
        return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    }

    // ==========================
    // LOAD WARNING (ATAS)
    // ==========================
    function loadWarningKadaluarsa() {
        const $tbody = $("#warningKadaluarsaBody");
        $tbody.html(`
            <tr>
                <td colspan="3" class="px-3 py-3 text-center text-[11px] md:text-xs text-slate-400">
                    Memuat data...
                </td>
            </tr>
        `);

        axios
            .get(`/farmasi/kadaluarsa-bhp/get-data-warning-kadaluarsa-bhp`, {
                params: { threshold: warningThreshold },
            })
            .then(function (response) {
                const data = response.data || [];
                $tbody.empty();

                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (!data.length) {
                    $tbody.append(`
                        <tr>
                            <td colspan="3" class="px-3 py-3 text-center text-[11px] md:text-xs text-emerald-600">
                                Tidak ada obat yang mendekati tanggal kadaluarsa 🎉
                            </td>
                        </tr>
                    `);
                    return;
                }

                data.forEach(function (item) {
                    let listTanggal = "";
                    let listBatch = "";
                    let listStok = "";

                    item.batch_bahan_habis_pakai.forEach(function (batch) {
                        // Parsing tanggal kadaluarsa dari database
                        const expDate = new Date(
                            batch.tanggal_kadaluarsa_bahan_habis_pakai,
                        );
                        expDate.setHours(0, 0, 0, 0);

                        // Hitung selisih hari
                        const diffTime = expDate - today;
                        const diffDays = Math.round(
                            diffTime / (1000 * 60 * 60 * 24),
                        );

                        let labelSisa = "";
                        let badgeColor = "";

                        // --- LOGIKA REVISI BADGE ---
                        if (diffDays === 0) {
                            labelSisa = "[ Hari Ini ]";
                            badgeColor = "text-rose-600 font-bold";
                        } else if (diffDays > 0) {
                            labelSisa = `[ ${diffDays} Hari Lagi ]`;
                            badgeColor = "text-amber-600";
                        } else {
                            // Nilai negatif berarti sudah lewat
                            const lewatDays = Math.abs(diffDays);
                            labelSisa = `[ Lewat ${lewatDays} Hari ]`;
                            badgeColor = "text-red-700 font-bold";
                        }

                        const tglIndo = formatTanggalIndo(
                            batch.tanggal_kadaluarsa_bahan_habis_pakai,
                        );

                        // Render baris berdasarkan stok di depot (500, 2000, dst)
                        if (
                            batch.batch_bahan_habis_pakai_depot &&
                            batch.batch_bahan_habis_pakai_depot.length > 0
                        ) {
                            batch.batch_bahan_habis_pakai_depot.forEach(
                                function (b_depot) {
                                    listTanggal += `<div class="mb-2">${tglIndo} <span class="${badgeColor} text-[10px]">${labelSisa}</span></div>`;
                                    listBatch += `<div class="mb-2 text-slate-600">${batch.nama_batch ?? "-"}</div>`;
                                    listStok += `<div class="mb-2 font-bold text-slate-700">${b_depot.stok_bahan_habis_pakai} Unit</div>`;
                                },
                            );
                        } else {
                            // Jika tidak ada stok di depot (seperti data 04 Feb)
                            listTanggal += `<div class="mb-2">${tglIndo} <span class="${badgeColor} text-[10px]">${labelSisa}</span></div>`;
                            listBatch += `<div class="mb-2 text-slate-600">${batch.nama_batch ?? "-"}</div>`;
                            listStok += `<div class="mb-2 text-slate-400">0 Unit</div>`;
                        }
                    });

                    $tbody.append(`
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/60 transition">
                            <td class="px-3 py-2 align-top">
                                <div class="font-medium text-[11px] md:text-xs text-slate-800 dark:text-slate-50">
                                    ${item.nama_barang ?? "-"}
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    ${item.kode ?? ""}
                                </div>
                            </td>
                            <td class="px-3 py-2 align-top text-[11px] md:text-xs text-slate-700 dark:text-slate-100">
                                ${listTanggal}
                            </td>
                            <td class="px-3 py-2 align-top text-[11px] md:text-xs text-slate-700 dark:text-slate-100">
                                ${listBatch}
                            </td>
                            <td class="px-3 py-2 align-top text-[11px] md:text-xs text-slate-700 dark:text-slate-100">
                                ${listStok}
                            </td>
                        </tr>
                    `);
                });
            })
            .catch(function (error) {
                console.error("ERROR WARNING KADALUARSA", error);
                $tbody.html(`
                    <tr>
                        <td colspan="3" class="px-3 py-3 text-center text-[11px] md:text-xs text-red-500">
                            Gagal memuat data warning kadaluarsa.
                        </td>
                    </tr>
                `);
            });
    }

    loadWarningKadaluarsa();

    // ==========================
    // DATATABLES TABEL BESAR
    // ==========================
    const table = $("#tableKadaluarsaBHP").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false, // pakai select custom
        info: false,
        dom: "t",

        ajax: function (data, callback, settings) {
            axios
                .get(`/farmasi/kadaluarsa-bhp/get-data-kadaluarsa-bhp`, {
                    params: Object.assign({}, data, {
                        threshold: tableThreshold,
                    }),
                })
                .then(function (response) {
                    callback(response.data);
                    const now = new Date();
                    $("#lastUpdateKadaluarsa").text(
                        new Intl.DateTimeFormat("id-ID", {
                            day: "2-digit",
                            month: "2-digit",
                            year: "numeric",
                            hour: "2-digit",
                            minute: "2-digit",
                        }).format(now),
                    );
                })
                .catch(function (error) {
                    console.error("ERROR DATATABLE KADALUARSA", error);
                    callback({
                        data: [],
                        recordsTotal: 0,
                        recordsFiltered: 0,
                    });
                });
        },

        order: [[3, "asc"]], // kolom 3 = tanggal

        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            {
                data: "kode",
                name: "kode",
                defaultContent: "-",
            },
            {
                data: "nama_barang",
                name: "nama_barang",
                defaultContent: "-",
            },
            {
                // 🔹 kolom tanggal yang benar
                data: "tanggal_kadaluarsa_bhp",
                name: "tanggal_kadaluarsa_bhp",
                render: function (data, type, row) {
                    if (type === "display" || type === "filter") {
                        const sisa =
                            row.sisa_hari ??
                            hitungSisaHari(row.tanggal_kadaluarsa_bhp);
                        const labelSisa =
                            sisa !== null && sisa >= 0
                                ? `<span class="ml-1 text-[10px] text-amber-600">(${sisa} hari lagi)</span>`
                                : "";
                        return `<span>${formatTanggalIndo(
                            row.tanggal_kadaluarsa_bhp,
                        )}</span> ${labelSisa}`;
                    }
                    return data;
                },
            },
            {
                data: "stok_barang",
                name: "stok_barang",
                render: function (data, type, row) {
                    const jumlah = data ?? 0;
                    const satuan = row.satuan ?? "";
                    return `${jumlah} ${satuan}`.trim();
                },
            },
            {
                data: "status_kadaluarsa",
                name: "status_kadaluarsa",
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    if (data) return data;

                    const sisa =
                        row.sisa_hari ??
                        hitungSisaHari(row.tanggal_kadaluarsa_bhp);
                    if (sisa === null) return "-";

                    if (sisa < 0) {
                        return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] bg-red-50 text-red-600 border border-red-200">
                                    Expired
                                </span>`;
                    } else if (sisa <= 7) {
                        return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] bg-amber-50 text-amber-700 border border-amber-200">
                                    Warning
                                </span>`;
                    } else {
                        return `<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Aman
                                </span>`;
                    }
                },
            },
        ],

        rowCallback: function (row, data) {
            $(row).addClass(
                "bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600",
            );
            $("td", row).addClass("px-6 py-4 text-gray-900 dark:text-white");
        },
    });

    // === Custom pagination & info ===
    const $info = $("#data-kadaluarsa-obat-customInfo");
    const $pagination = $("#data-kadaluarsa-obat-customPagination");
    const $perPage = $("#data-kadaluarsa-obat-pageLength"); // pastikan id ini ada di blade

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
        $pagination.append(
            `<li><a href="#" id="btnPrevKadaluarsa" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a></li>`,
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
                `<li><a href="#" class="kadaluarsa-page-number flex items-center justify-center px-3 h-8 border ${active}" data-page="${i}">${i}</a></li>`,
            );
        }

        const nextDisabled =
            currentPage === totalPages ? "opacity-50 cursor-not-allowed" : "";
        $pagination.append(
            `<li><a href="#" id="btnNextKadaluarsa" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700 ${nextDisabled}">Next</a></li>`,
        );
    }

    $pagination.on("click", "a", function (e) {
        e.preventDefault();
        const $link = $(this);
        if ($link.hasClass("opacity-50")) return;

        if ($link.attr("id") === "btnPrevKadaluarsa")
            table.page("previous").draw("page");
        else if ($link.attr("id") === "btnNextKadaluarsa")
            table.page("next").draw("page");
        else if ($link.hasClass("kadaluarsa-page-number"))
            table.page(parseInt($link.data("page")) - 1).draw("page");
    });

    $perPage.on("change", function () {
        table.page.len(parseInt($(this).val())).draw();
    });

    table.on("draw", updatePagination);
    updatePagination();

    // =========================
    // Search cepat - Kadaluarsa Obat
    // =========================
    const $searchKadaluarsaObat = $("#searchKadaluarsaObat");

    let kdTimer = null;
    let kdLastValue = "";
    let kdXhr = null;

    if ($searchKadaluarsaObat.length && table) {
        // simpan jqXHR yang dipakai DataTables agar bisa di-abort
        table.on("preXhr.dt", function (e, settings, data) {
            if (settings.jqXHR) kdXhr = settings.jqXHR;
        });

        const runSearchKadaluarsa = (value) => {
            if (value === kdLastValue) return;
            kdLastValue = value;

            // minimal 2 huruf (biar ga spam request)
            if (value.length < 2) {
                table.search("").draw();
                return;
            }

            table.search(value).draw();
        };

        // realtime feel (lebih enak dari keyup)
        $searchKadaluarsaObat.on("input", function () {
            const value = $(this).val().trim();

            // abort request sebelumnya biar gak balapan response
            if (kdXhr && kdXhr.readyState !== 4) {
                try {
                    kdXhr.abort();
                } catch (e) {}
            }

            // debounce adaptif
            const delay =
                value.length <= 2 ? 300 : value.length <= 5 ? 180 : 120;

            clearTimeout(kdTimer);
            kdTimer = setTimeout(() => runSearchKadaluarsa(value), delay);
        });

        // Enter = langsung cari
        $searchKadaluarsaObat.on("keydown", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                clearTimeout(kdTimer);
                runSearchKadaluarsa($(this).val().trim());
            }
        });

        // ESC = clear cepat
        $searchKadaluarsaObat.on("keydown", function (e) {
            if (e.key === "Escape") {
                $(this).val("");
                clearTimeout(kdTimer);
                kdLastValue = "";
                table.search("").draw();
            }
        });
    }
});
