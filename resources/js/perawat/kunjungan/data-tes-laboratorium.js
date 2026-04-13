import $ from "jquery";

$(function () {
    const tableOrderLab = $("#tabel-order-lab").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/perawat/order-lab/get-data-order-lab",
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            {
                data: "nama_pasien",
                name: "nama_pasien",
            },
            {
                data: "item_pemeriksaan",
                name: "item_pemeriksaan",
            },
            {
                data: "nama_dokter",
                name: "nama_dokter",
            },
            {
                data: "status_badge",
                name: "status_badge",
            },
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
                "bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors",
            );
            $("td", row).addClass(
                "px-5 py-3 align-middle text-slate-700 dark:text-slate-50 text-sm",
            );
        },
        order: [[1, "asc"]],
        language: {
            emptyTable: "Tidak ada data.",
            processing: "Memuat...",
        },
    });

    $("#triage_searchInput").on("keyup", function () {
        tableOrderLab.search(this.value).draw();
    });

    const $info = $("#triage_customInfo");
    const $pagination = $("#triage_customPagination");
    const $perPage = $("#triage_pageLength");

    function updatePagination() {
        const info = tableOrderLab.page.info();
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
        if ($link.attr("id") === "btnPrev")
            tableOrderLab.page("previous").draw("page");
        else if ($link.attr("id") === "btnNext")
            tableOrderLab.page("next").draw("page");
        else if ($link.hasClass("page-number"))
            tableOrderLab.page(parseInt($link.data("page")) - 1).draw("page");
    });

    $perPage.on("change", function () {
        tableOrderLab.page.len(parseInt($(this).val())).draw();
    });

    tableOrderLab.on("draw", updatePagination);
    updatePagination();

    // =========================
    // MODAL DETAIL ORDER LAB
    // =========================
    const $modal = $("#detailOrderLabModal");
    const $backdrop = $("#detailOrderLabBackdrop");
    const $content = $("#detailOrderLabContent");
    const $btnClose = $("#btnCloseDetailOrderLab");
    const $btnCloseFooter = $("#btnCloseDetailOrderLabFooter");

    function openModal() {
        $modal.removeClass("hidden");
        $("body").addClass("overflow-hidden");
    }

    function closeModal() {
        $modal.addClass("hidden");
        $("body").removeClass("overflow-hidden");
    }

    function escapeHtml(text) {
        if (text === null || text === undefined || text === "") return "-";
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function formatCurrency(value) {
        const number = Number(value || 0);
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0,
        }).format(number);
    }

    function renderStatusBadge(status) {
        const map = {
            Selesai: {
                bg: "bg-green-100 dark:bg-green-900/30",
                text: "text-green-700 dark:text-green-300",
            },
            Pending: {
                bg: "bg-yellow-100 dark:bg-yellow-900/30",
                text: "text-yellow-700 dark:text-yellow-300",
            },
            Diproses: {
                bg: "bg-blue-100 dark:bg-blue-900/30",
                text: "text-blue-700 dark:text-blue-300",
            },
            Dibatalkan: {
                bg: "bg-red-100 dark:bg-red-900/30",
                text: "text-red-700 dark:text-red-300",
            },
        };

        const config = map[status] || {
            bg: "bg-gray-100 dark:bg-slate-700",
            text: "text-gray-700 dark:text-slate-200",
        };

        return `
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${config.bg} ${config.text}">
                ${escapeHtml(status)}
            </span>
        `;
    }

    function renderLoader() {
        return `
            <div class="py-14 text-center">
                <div class="inline-flex items-center gap-3 text-slate-500 dark:text-slate-300">
                    <svg class="animate-spin h-5 w-5 text-sky-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    <span>Memuat detail order laboratorium...</span>
                </div>
            </div>
        `;
    }

    function renderError(message) {
        return `
            <div class="py-10 text-center">
                <div class="inline-flex flex-col items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                        <i class="fa-solid fa-circle-exclamation text-red-500"></i>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-300">
                        ${escapeHtml(message || "Gagal memuat detail data.")}
                    </p>
                </div>
            </div>
        `;
    }

    function buildItemsTable(items) {
        if (!items || items.length === 0) {
            return `
                <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-300">
                    Tidak ada detail pemeriksaan.
                </div>
            `;
        }

        const rows = items
            .map((item, index) => {
                const satuan = item.nama_satuan
                    ? ` ${escapeHtml(item.nama_satuan)}`
                    : "";
                const nilaiNormal =
                    item.nilai_normal !== null &&
                    item.nilai_normal !== undefined
                        ? `${escapeHtml(item.nilai_normal)}${satuan}`
                        : "-";

                const nilaiHasil =
                    item.nilai_hasil !== null && item.nilai_hasil !== undefined
                        ? `${escapeHtml(item.nilai_hasil)}${satuan}`
                        : "-";

                const nilaiRujukan =
                    item.nilai_rujukan !== null &&
                    item.nilai_rujukan !== undefined
                        ? `${escapeHtml(item.nilai_rujukan)}${satuan}`
                        : nilaiNormal;

                const waktuHasil =
                    item.hasil_tanggal_pemeriksaan || item.hasil_jam_pemeriksaan
                        ? `${escapeHtml(item.hasil_tanggal_pemeriksaan || "-")} ${escapeHtml(item.hasil_jam_pemeriksaan || "")}`
                        : "-";

                return `
                    <tr class="border-b border-slate-100 dark:border-slate-700">
                        <td class="px-4 py-3 align-top">${index + 1}</td>
                        <td class="px-4 py-3 align-top">
                            <div class="font-semibold text-slate-700 dark:text-slate-100">${escapeHtml(item.nama_pemeriksaan)}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kode: ${escapeHtml(item.kode_pemeriksaan)}</div>
                        </td>
                        <td class="px-4 py-3 align-top">${escapeHtml(item.status_pemeriksaan)}</td>
                        <td class="px-4 py-3 align-top">${nilaiNormal}</td>
                        <td class="px-4 py-3 align-top">${nilaiHasil}</td>
                        <td class="px-4 py-3 align-top">${nilaiRujukan}</td>
                        <td class="px-4 py-3 align-top">${escapeHtml(item.keterangan)}</td>
                        <td class="px-4 py-3 align-top">${escapeHtml(item.perawat_pemeriksa)}</td>
                        <td class="px-4 py-3 align-top">${waktuHasil}</td>
                        <td class="px-4 py-3 align-top">${formatCurrency(item.harga_pemeriksaan_lab)}</td>
                    </tr>
                `;
            })
            .join("");

        return `
            <div class="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                <table class="min-w-[1200px] w-full text-sm text-left">
                    <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-700 dark:text-slate-200">
                        <tr>
                            <th class="px-4 py-3 font-semibold">No</th>
                            <th class="px-4 py-3 font-semibold">Pemeriksaan</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Nilai Normal</th>
                            <th class="px-4 py-3 font-semibold">Nilai Hasil</th>
                            <th class="px-4 py-3 font-semibold">Nilai Rujukan</th>
                            <th class="px-4 py-3 font-semibold">Keterangan</th>
                            <th class="px-4 py-3 font-semibold">Perawat Pemeriksa</th>
                            <th class="px-4 py-3 font-semibold">Waktu Hasil</th>
                            <th class="px-4 py-3 font-semibold">Harga</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-100">
                        ${rows}
                    </tbody>
                </table>
            </div>
        `;
    }

    function buildDetailHtml(data) {
        const perawatTerkait =
            Array.isArray(data.perawat_terkait) && data.perawat_terkait.length
                ? data.perawat_terkait
                      .map((item) => escapeHtml(item))
                      .join(", ")
                : "-";

        return `
            <div class="space-y-6">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800 dark:text-slate-50">
                            Detail Order Laboratorium
                        </h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                            Nomor Order: <span class="font-semibold">${escapeHtml(data.no_order_lab)}</span>
                        </p>
                    </div>
                    <div>
                        ${renderStatusBadge(data.status)}
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50/70 dark:bg-slate-900/40">
                        <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Nama Pasien</div>
                        <div class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(data.nama_pasien)}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50/70 dark:bg-slate-900/40">
                        <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Nama Dokter</div>
                        <div class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(data.nama_dokter)}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50/70 dark:bg-slate-900/40">
                        <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Poli</div>
                        <div class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(data.nama_poli)}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50/70 dark:bg-slate-900/40">
                        <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">No Antrian</div>
                        <div class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(data.no_antrian)}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50/70 dark:bg-slate-900/40">
                        <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Tanggal Order</div>
                        <div class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(data.tanggal_order)}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50/70 dark:bg-slate-900/40">
                        <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Tanggal Pemeriksaan</div>
                        <div class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(data.tanggal_pemeriksaan)} ${escapeHtml(data.jam_pemeriksaan)}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50/70 dark:bg-slate-900/40">
                        <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Perawat Penginput</div>
                        <div class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(data.perawat_penginput)}</div>
                    </div>

                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50/70 dark:bg-slate-900/40 md:col-span-2">
                        <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Perawat Terkait Dokter/Poli</div>
                        <div class="mt-1 text-sm font-semibold text-slate-800 dark:text-slate-100">${perawatTerkait}</div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 bg-white dark:bg-slate-800">
                    <div class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Keluhan Awal</div>
                    <div class="mt-2 text-sm text-slate-700 dark:text-slate-100 leading-relaxed">
                        ${escapeHtml(data.keluhan_awal)}
                    </div>
                </div>

                <div>
                    <div class="mb-3">
                        <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100">Detail Pemeriksaan Laboratorium</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Menampilkan seluruh jenis pemeriksaan, status, hasil, nilai rujukan, dan harga.</p>
                    </div>
                    ${buildItemsTable(data.items)}
                </div>
            </div>
        `;
    }

    $("#tabel-order-lab").on("click", ".btn-detail-order-lab", function () {
        const url = $(this).attr("data-detail-url");

        openModal();
        $content.html(renderLoader());

        $.ajax({
            url: url,
            type: "GET",
            dataType: "json",
            success: function (response) {
                if (!response.success) {
                    $content.html(
                        renderError(response.message || "Gagal memuat detail."),
                    );
                    return;
                }

                $content.html(buildDetailHtml(response.data));
            },
            error: function (xhr) {
                const message =
                    xhr.responseJSON?.message ||
                    "Terjadi kesalahan saat mengambil detail order laboratorium.";
                $content.html(renderError(message));
            },
        });
    });

    $btnClose.on("click", closeModal);
    $btnCloseFooter.on("click", closeModal);
    $backdrop.on("click", closeModal);

    $(document).on("keydown", function (e) {
        if (e.key === "Escape" && !$modal.hasClass("hidden")) {
            closeModal();
        }
    });
});
