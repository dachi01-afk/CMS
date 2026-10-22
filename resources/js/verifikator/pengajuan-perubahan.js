import $ from "jquery";
import axios from "axios";

$(function () {
    const $table = $("#pengajuanTable");

    if (!$table.length) {
        return;
    }

    function escapeHtml(value) {
        return $("<div>").text(value ?? "").html();
    }

    function renderTanggal(row) {
        return `
            <div class="min-w-0">
                <p class="whitespace-nowrap text-[13px] font-semibold text-slate-700 dark:text-slate-100">${escapeHtml(row.tanggal || "-")}</p>
                <p class="mt-0.5 whitespace-nowrap text-[11px] tabular-nums text-slate-400 dark:text-slate-500">${escapeHtml(row.waktu || "-")} WIB</p>
            </div>
        `;
    }

    function renderJenis(row) {
        const isObat = row.jenis_data === "obat";
        const classes = isObat
            ? "border-emerald-100 bg-emerald-50 text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300"
            : "border-indigo-100 bg-indigo-50 text-indigo-700 dark:border-indigo-500/20 dark:bg-indigo-500/10 dark:text-indigo-300";
        const icon = isObat ? "fa-pills" : "fa-clipboard-list";

        return `
            <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border px-2.5 py-1 text-[11px] font-semibold ${classes}">
                <i class="fa-solid ${icon} text-[10px]"></i>
                ${escapeHtml(row.jenis_data_label || "-")}
            </span>
        `;
    }

    function renderData(row) {
        return `
            <div class="min-w-0 max-w-[200px] sm:max-w-[280px]">
                <p class="truncate text-[13px] font-semibold text-slate-800 dark:text-slate-100" title="${escapeHtml(row.target_label)}">
                    ${escapeHtml(row.target_label || "-")}
                </p>
                <p class="mt-0.5 text-[11px] tabular-nums text-slate-400 dark:text-slate-500">ID #${escapeHtml(row.target_id)}</p>
            </div>
        `;
    }

    function renderAksi(row) {
        const isHapus = row.aksi === "hapus";
        const classes = isHapus
            ? "border-rose-100 bg-rose-50 text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300"
            : "border-sky-100 bg-sky-50 text-sky-700 dark:border-sky-500/20 dark:bg-sky-500/10 dark:text-sky-300";
        const icon = isHapus ? "fa-trash-can" : "fa-pen-to-square";

        return `
            <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border px-2.5 py-1 text-[11px] font-semibold ${classes}">
                <i class="fa-solid ${icon} text-[10px]"></i>
                ${escapeHtml(row.aksi_label || "-")}
            </span>
        `;
    }

    function renderPengaju(row) {
        const role = (row.pengaju_role || "").toLowerCase();
        const isFarmasi = role === "farmasi";
        const isAdmin = role === "admin";

        const avatarClasses = isFarmasi
            ? "from-emerald-500 to-teal-600"
            : isAdmin
              ? "from-indigo-500 to-violet-600"
              : "from-slate-500 to-slate-700";

        const roleClasses = isFarmasi
            ? "bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300"
            : isAdmin
              ? "bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300"
              : "bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300";

        return `
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br ${avatarClasses} text-[11px] font-bold text-white shadow-sm ring-2 ring-white dark:ring-slate-800">
                    ${escapeHtml(row.pengaju_inisial || "?")}
                </div>
                <div class="min-w-0">
                    <p class="truncate text-[13px] font-semibold text-slate-700 dark:text-slate-100" title="${escapeHtml(row.pengaju_nama || "-")}">${escapeHtml(row.pengaju_nama || "-")}</p>
                    <span class="mt-1 inline-flex items-center rounded-md px-1.5 py-0.5 text-[9px] font-semibold uppercase tracking-[0.08em] ${roleClasses}">
                        ${escapeHtml(row.pengaju_role || "-")}
                    </span>
                </div>
            </div>
        `;
    }

    function renderStatus(row) {
        const styles = {
            menunggu: {
                badge: "border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300",
                dot: "bg-amber-500",
            },
            disetujui: {
                badge: "border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/20 dark:bg-emerald-500/10 dark:text-emerald-300",
                dot: "bg-emerald-500",
            },
            ditolak: {
                badge: "border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/20 dark:bg-rose-500/10 dark:text-rose-300",
                dot: "bg-rose-500",
            },
        };
        const style = styles[row.status] || styles.menunggu;

        return `
            <span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border px-2.5 py-1 text-[11px] font-semibold ${style.badge}">
                <span class="h-1.5 w-1.5 rounded-full ${style.dot}"></span>
                ${escapeHtml(row.status_label || "-")}
            </span>
        `;
    }

    function renderDetail(row) {
        return `
            <button type="button"
                class="btn-detail-pengajuan group inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-slate-600 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-slate-600 dark:bg-slate-900/40 dark:text-slate-300 dark:hover:border-indigo-500/40 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-300 dark:focus:ring-indigo-500/20"
                data-id="${escapeHtml(row.id)}"
                title="Lihat detail pengajuan">
                <i class="fa-solid fa-eye text-[10px] text-slate-400 transition group-hover:text-indigo-500 dark:text-slate-500 dark:group-hover:text-indigo-300"></i>
                Detail
            </button>
        `;
    }

    const table = $table.DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        dom: "t",
        order: [[1, "desc"]],
        ajax: {
            url: "/verifikator/pengajuan/data",
            type: "GET",
            data: function (data) {
                data.status = $("#filterStatus").val() || "";
                data.jenis_data = $("#filterJenis").val() || "";
            },
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
                render: function (data, type) {
                    if (type !== "display") {
                        return data;
                    }

                    return `<span class="inline-flex h-7 min-w-7 items-center justify-center rounded-lg bg-slate-100 px-1.5 text-[11px] font-semibold text-slate-500 dark:bg-slate-700 dark:text-slate-300">${escapeHtml(data)}</span>`;
                },
            },
            {
                data: "created_at",
                name: "created_at",
                render: function (data, type, row) {
                    return type === "display" ? renderTanggal(row) : data;
                },
            },
            {
                data: "jenis_data",
                name: "jenis_data",
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return type === "display" ? renderJenis(row) : (row.jenis_data_label || data);
                },
            },
            {
                data: "target_label",
                name: "target_label",
                render: function (data, type, row) {
                    return type === "display" ? renderData(row) : data;
                },
            },
            {
                data: "aksi",
                name: "aksi",
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return type === "display" ? renderAksi(row) : (row.aksi_label || data);
                },
            },
            {
                data: "pengaju_nama",
                name: "pengaju_nama",
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return type === "display" ? renderPengaju(row) : data;
                },
            },
            {
                data: "status",
                name: "status",
                orderable: false,
                searchable: false,
                render: function (data, type, row) {
                    return type === "display" ? renderStatus(row) : (row.status_label || data);
                },
            },
            {
                data: "id",
                name: "id",
                orderable: false,
                searchable: false,
                className: "text-right",
                render: function (data, type, row) {
                    return type === "display" ? renderDetail(row) : data;
                },
            },
        ],
        columnDefs: [
            { targets: 0, responsivePriority: 1, className: "text-center" },
            { targets: 1, responsivePriority: 3 },
            { targets: 2, responsivePriority: 4 },
            { targets: 3, responsivePriority: 1 },
            { targets: 4, responsivePriority: 3 },
            { targets: 5, responsivePriority: 3 },
            { targets: 6, responsivePriority: 1 },
            { targets: 7, responsivePriority: 1 },
        ],
        language: {
            emptyTable: "Belum ada pengajuan.",
            zeroRecords: "Tidak ada pengajuan yang cocok.",
            processing: "Memuat data...",
        },
        rowCallback: function (row, data) {
            $(row)
                .toggleClass("row-pending", data.status === "menunggu")
                .addClass(
                    "bg-white transition-colors dark:bg-slate-800",
                );
        },
        drawCallback: function () {
            updateInfo();
            updatePagination();
            renderEmptyState();
            syncStatusCards();
            syncSearchClear();
            syncResetState();
        },
    });

    function updateInfo() {
        const info = table.page.info();
        const $info = $("#pengajuanInfo");

        if (info.recordsDisplay === 0) {
            $info.html("Tidak ada pengajuan untuk ditampilkan");
            return;
        }

        let text = `Menampilkan <span class="font-semibold text-slate-700 dark:text-slate-200">${info.start + 1}–${info.end}</span> dari <span class="font-semibold text-slate-700 dark:text-slate-200">${info.recordsDisplay}</span> pengajuan`;

        if (info.recordsDisplay !== info.recordsTotal) {
            text += ` <span class="text-slate-400 dark:text-slate-500">(difilter dari ${info.recordsTotal})</span>`;
        }

        $info.html(text);
    }

    function pageButton(page, currentPage) {
        const isActive = page === currentPage;

        return `
            <li>
                <button type="button"
                    class="page-number inline-flex h-8 min-w-8 items-center justify-center rounded-lg border px-2 text-[11px] font-semibold transition ${
                        isActive
                            ? "border-indigo-600 bg-indigo-600 text-white shadow-sm"
                            : "border-slate-200 bg-white text-slate-500 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 dark:border-slate-600 dark:bg-slate-900/40 dark:text-slate-300 dark:hover:border-indigo-500/40 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-300"
                    }"
                    data-page="${page}"
                    ${isActive ? 'aria-current="page"' : ""}>
                    ${page}
                </button>
            </li>
        `;
    }

    function navButton(id, icon, label, disabled) {
        return `
            <li>
                <button type="button" id="${id}" ${disabled ? "disabled" : ""}
                    aria-label="${label}"
                    class="inline-flex h-8 items-center gap-1.5 rounded-lg border px-2.5 text-[11px] font-semibold transition ${
                        disabled
                            ? "cursor-not-allowed border-slate-100 bg-slate-50 text-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-600"
                            : "border-slate-200 bg-white text-slate-500 hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 dark:border-slate-600 dark:bg-slate-900/40 dark:text-slate-300 dark:hover:border-indigo-500/40 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-300"
                    }">
                    <i class="fa-solid ${icon} text-[9px]"></i>
                    <span class="hidden sm:inline">${label}</span>
                </button>
            </li>
        `;
    }

    function paginationItems(currentPage, totalPages) {
        if (totalPages <= 5) {
            return Array.from({ length: totalPages }, (_, index) => index + 1);
        }

        const items = [1];
        const start = Math.max(2, currentPage - 1);
        const end = Math.min(totalPages - 1, currentPage + 1);

        if (start > 2) {
            items.push("...");
        }

        for (let page = start; page <= end; page++) {
            items.push(page);
        }

        if (end < totalPages - 1) {
            items.push("...");
        }

        items.push(totalPages);

        return items;
    }

    function updatePagination() {
        const info = table.page.info();
        const totalPages = Math.max(info.pages, 1);
        const currentPage = info.page + 1;
        const $pagination = $("#pengajuanPagination");

        let html = navButton(
            "pengajuanPrev",
            "fa-chevron-left",
            "Sebelumnya",
            currentPage <= 1,
        );

        paginationItems(currentPage, totalPages).forEach((item) => {
            if (item === "...") {
                html += `<li><span class="inline-flex h-8 min-w-8 items-center justify-center text-[11px] text-slate-400 dark:text-slate-500">…</span></li>`;
                return;
            }

            html += pageButton(item, currentPage);
        });

        html += navButton(
            "pengajuanNext",
            "fa-chevron-right",
            "Berikutnya",
            currentPage >= totalPages,
        );

        $pagination.html(html);
    }

    function renderEmptyState() {
        const $empty = $(
            "#pengajuanTable tbody td.dt-empty, #pengajuanTable tbody td.dataTables_empty",
        );

        if (!$empty.length) {
            return;
        }

        const hasFilter = Boolean(
            ($("#pengajuanSearch").val() || "").trim() ||
                $("#filterStatus").val() ||
                $("#filterJenis").val(),
        );

        $empty.addClass("!px-5 !py-16");

        $empty.html(`
            <div class="flex flex-col items-center justify-center gap-2 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500 dark:bg-indigo-500/15 dark:text-indigo-300">
                    <i class="fa-regular fa-folder-open text-xl"></i>
                </div>
                <p class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200">
                    ${hasFilter ? "Tidak ada pengajuan yang cocok" : "Belum ada pengajuan"}
                </p>
                <p class="max-w-sm text-xs leading-relaxed text-slate-400 dark:text-slate-500">
                    ${
                        hasFilter
                            ? "Coba ubah kata kunci atau reset filter untuk melihat semua pengajuan."
                            : "Pengajuan edit atau hapus dari Admin (layanan) dan Farmasi (obat) akan muncul di sini."
                    }
                </p>
                ${
                    hasFilter
                        ? '<button type="button" id="emptyResetFilter" class="mt-2 inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-semibold text-slate-600 transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-900/40 dark:text-slate-300 dark:hover:bg-slate-700">Reset filter</button>'
                        : ""
                }
            </div>
        `);
    }

    let searchTimer = null;

    function hasActiveFilter() {
        return Boolean(
            ($("#pengajuanSearch").val() || "").trim() ||
                $("#filterStatus").val() ||
                $("#filterJenis").val(),
        );
    }

    function syncSearchClear() {
        $("#pengajuanSearchClear").toggleClass(
            "hidden",
            !($("#pengajuanSearch").val() || "").trim(),
        );
    }

    function syncResetState() {
        $("#resetFilter").prop("disabled", !hasActiveFilter());
    }

    function syncStatusCards() {
        const status = $("#filterStatus").val() || "";

        $(".status-card").each(function () {
            const isActive =
                $(this).attr("data-status-filter") === status;

            $(this)
                .toggleClass("is-active", isActive)
                .attr("aria-pressed", isActive ? "true" : "false");
        });
    }

    $("#pengajuanSearch").on("input", function () {
        const value = $(this).val() || "";

        syncSearchClear();
        syncResetState();

        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(function () {
            table.search(value).page(0).draw(false);
        }, 300);
    });

    $("#pengajuanSearchClear").on("click", function () {
        $("#pengajuanSearch").val("").trigger("input").trigger("focus");
    });

    $("#filterStatus, #filterJenis").on("change", function () {
        syncResetState();
        table.ajax.reload(null, true);
    });

    // Kartu ringkasan berfungsi sebagai filter cepat. Klik kartu yang sama
    // sekali lagi untuk kembali menampilkan semua pengajuan.
    $(".status-card").on("click", function () {
        const status = $(this).attr("data-status-filter") || "";
        const current = $("#filterStatus").val() || "";

        $("#filterStatus")
            .val(current === status ? "" : status)
            .trigger("change");

        const target = document.getElementById("daftar-pengajuan");

        if (target) {
            const rect = target.getBoundingClientRect();

            if (rect.top < 0 || rect.bottom > window.innerHeight) {
                target.scrollIntoView({ behavior: "smooth", block: "start" });
            }
        }
    });

    function resetFilters() {
        window.clearTimeout(searchTimer);

        $("#pengajuanSearch").val("");
        $("#filterStatus").val("");
        $("#filterJenis").val("");

        syncSearchClear();
        syncStatusCards();
        syncResetState();
        table.search("").page(0).draw();
    }

    $("#resetFilter").on("click", resetFilters);

    $(document).on("click", "#emptyResetFilter", resetFilters);

    $("#pengajuanPageLength").on("change", function () {
        const length = parseInt($(this).val(), 10) || 10;
        table.page.len(length).draw();
    });

    $("#pengajuanPagination")
        .on("click", "#pengajuanPrev", function () {
            if ($(this).prop("disabled")) {
                return;
            }

            table.page("previous").draw("page");
        })
        .on("click", "#pengajuanNext", function () {
            if ($(this).prop("disabled")) {
                return;
            }

            table.page("next").draw("page");
        })
        .on("click", ".page-number", function () {
            const page = parseInt($(this).data("page"), 10);

            if (!Number.isNaN(page)) {
                table.page(page - 1).draw("page");
            }
        });

    // =========================================================
    // Modal detail
    // =========================================================
    const $modal = $("#modalDetailPengajuan");
    let current = null;

    const badgeClasses = [
        "bg-amber-50",
        "text-amber-700",
        "border-amber-200",
        "bg-emerald-50",
        "text-emerald-700",
        "border-emerald-200",
        "bg-red-50",
        "text-red-700",
        "border-red-200",
    ];

    function openModal() {
        $modal.removeClass("hidden").addClass("flex");
    }

    function closeModal() {
        $modal.addClass("hidden").removeClass("flex");
        current = null;
    }

    function badgeClass(status) {
        if (status === "disetujui") {
            return "bg-emerald-50 text-emerald-700 border-emerald-200";
        }

        if (status === "ditolak") {
            return "bg-red-50 text-red-700 border-red-200";
        }

        return "bg-amber-50 text-amber-700 border-amber-200";
    }

    function renderRows(rows) {
        if (!rows || rows.length === 0) {
            $("#detailRows").html(
                '<tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">Tidak ada detail data.</td></tr>',
            );
            return;
        }

        const html = rows
            .map((row) => {
                const baru =
                    row.baru === null || row.baru === undefined
                        ? '<span class="text-slate-400 italic">Tidak ada (dihapus)</span>'
                        : escapeHtml(row.baru);

                return `
                    <tr class="bg-white dark:bg-slate-800">
                        <td class="px-4 py-3 font-medium text-slate-700 dark:text-slate-200">${escapeHtml(row.label)}</td>
                        <td class="px-4 py-3 text-slate-600 dark:text-slate-300">${escapeHtml(row.lama)}</td>
                        <td class="px-4 py-3 font-semibold text-slate-800 dark:text-slate-100">${baru}</td>
                    </tr>
                `;
            })
            .join("");

        $("#detailRows").html(html);
    }

    $(document).on("click", ".btn-detail-pengajuan", function () {
        const id = $(this).data("id");

        if (!id) {
            return;
        }

        current = null;
        $("#btnSetujuiPengajuan, #btnTolakPengajuan").addClass("hidden");
        $("#detailRows").html(
            '<tr><td colspan="3" class="px-4 py-6 text-center text-slate-500">Memuat data...</td></tr>',
        );
        openModal();

        axios
            .get(`/verifikator/pengajuan/${id}`)
            .then((response) => {
                const data = response.data.data;
                current = data;

                $("#detailTarget").text(data.target_label || "-");
                $("#detailJenis").text(data.jenis_data_label || "-");
                $("#detailAksi").text(data.aksi_label || "-");
                $("#detailPengaju").text(
                    `${data.pengaju || "-"} (${data.pengaju_role || "-"})`,
                );
                $("#detailTanggal").text(data.diajukan_pada || "-");

                const $badge = $("#detailStatusBadge");
                $badge.removeClass(badgeClasses.join(" "))
                    .addClass(badgeClass(data.status))
                    .text(data.status_label || "-");

                renderRows(data.rows);

                if (data.status === "menunggu") {
                    $("#catatanWrapper").removeClass("hidden");
                    $("#catatanTersimpan").addClass("hidden");
                    $("#btnSetujuiPengajuan, #btnTolakPengajuan").removeClass("hidden");
                    $("#catatanVerifikator").val("");
                    $("#detailDiverifikasi").text("Belum diverifikasi");
                } else {
                    $("#catatanWrapper").addClass("hidden");
                    $("#catatanTersimpan").removeClass("hidden");
                    $("#catatanTersimpanText").text(
                        data.catatan_verifikator || "Tidak ada catatan.",
                    );
                    $("#btnSetujuiPengajuan, #btnTolakPengajuan").addClass("hidden");
                    $("#detailDiverifikasi").text(
                        data.diverifikasi_pada
                            ? `Diverifikasi oleh ${data.diverifikasi_oleh || "-"} pada ${data.diverifikasi_pada}`
                            : "Sudah diproses",
                    );
                }
            })
            .catch((error) => {
                $("#detailRows").html(
                    '<tr><td colspan="3" class="px-4 py-6 text-center text-red-600">Gagal memuat detail pengajuan.</td></tr>',
                );
                Swal.fire({
                    icon: "error",
                    title: "Gagal memuat data",
                    text:
                        error.response?.data?.message ||
                        "Silakan coba lagi.",
                });
            });
    });

    $("#btnCloseDetailPengajuan, #btnTutupPengajuan").on("click", closeModal);

    $modal.on("click", function (event) {
        if (event.target === this) {
            closeModal();
        }
    });

    function proses(action) {
        if (!current) {
            return;
        }

        const isSetujui = action === "setujui";

        Swal.fire({
            icon: isSetujui ? "question" : "warning",
            title: isSetujui ? "Setujui pengajuan ini?" : "Tolak pengajuan ini?",
            text: isSetujui
                ? current.aksi_label === "Hapus"
                    ? "Data akan dihapus permanen setelah disetujui."
                    : "Perubahan akan langsung diterapkan ke data asli."
                : "Data asli tidak akan diubah.",
            showCancelButton: true,
            confirmButtonText: isSetujui ? "Ya, setujui" : "Ya, tolak",
            cancelButtonText: "Batal",
            confirmButtonColor: isSetujui ? "#059669" : "#dc2626",
            cancelButtonColor: "#64748b",
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            axios
                .post(`/verifikator/pengajuan/${current.id}/${action}`, {
                    catatan: $("#catatanVerifikator").val() || "",
                })
                .then((response) => {
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil!",
                        text: response.data.message,
                        showConfirmButton: false,
                        timer: 1800,
                    }).then(() => window.location.reload());
                })
                .catch((error) => {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal",
                        text:
                            error.response?.data?.message ||
                            "Terjadi kesalahan server. Silakan coba lagi.",
                    });
                });
        });
    }

    $("#btnSetujuiPengajuan").on("click", function () {
        proses("setujui");
    });

    $("#btnTolakPengajuan").on("click", function () {
        proses("tolak");
    });

    syncSearchClear();
    syncStatusCards();
    syncResetState();
});
