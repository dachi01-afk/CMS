import $ from "jquery";

$(function () {
    const tableOrderRadiologi = $("#tabel-order-radiologi").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,

        ajax: "/perawat/order-radiologi/get-data-order-radiologi",
        // {
        //     url: "/perawat/order-radiologi/get-data-order-radiologi",
        //     type: "GET",
        //     data: function (d) {
        //         d.status = "pending";
        //     },
        // },

        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "nama_pasien", name: "nama_pasien" },
            { data: "item_pemeriksaan", name: "item_pemeriksaan" },
            { data: "nama_dokter", name: "nama_dokter" },
            { data: "status_badge", name: "status_badge" },
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
            emptyTable: "Tidak ada data pending.",
            processing: "Memuat...",
        },
    });

    // 🔎 Search
    $("#triage_searchInput").on("keyup", function () {
        tableOrderRadiologi.search(this.value).draw();
    });

    // 🔢 Custom pagination & info
    const $info = $("#triage_customInfo");
    const $pagination = $("#triage_customPagination");
    const $perPage = $("#triage_pageLength");

    function updatePagination() {
        const info = tableOrderRadiologi.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${info.recordsDisplay} data (Halaman ${currentPage} dari ${totalPages})`,
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
            tableOrderRadiologi.page("previous").draw("page");
        else if ($link.attr("id") === "btnNext")
            tableOrderRadiologi.page("next").draw("page");
        else if ($link.hasClass("page-number"))
            tableOrderRadiologi
                .page(parseInt($link.data("page")) - 1)
                .draw("page");
    });

    $perPage.on("change", function () {
        tableOrderRadiologi.page.len(parseInt($(this).val())).draw();
    });

    tableOrderRadiologi.on("draw", updatePagination);
    updatePagination();

    function escapeHtml(value) {
        if (value === null || value === undefined) return "-";
        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function renderStatusBadge(status) {
        const map = {
            Selesai: "bg-green-100 text-green-700",
            Pending: "bg-yellow-100 text-yellow-700",
            Diproses: "bg-blue-100 text-blue-700",
            Dibatalkan: "bg-red-100 text-red-700",
        };

        const cls = map[status] || "bg-gray-100 text-gray-700";

        return `
        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ${cls}">
            ${escapeHtml(status || "-")}
        </span>
    `;
    }

    function openModalDetailRadiologi() {
        $("#modalDetailOrderRadiologi").removeClass("hidden");
        $("body").addClass("overflow-hidden");
    }

    function closeModalDetailRadiologi() {
        $("#modalDetailOrderRadiologi").addClass("hidden");
        $("body").removeClass("overflow-hidden");
    }

    function renderDetailOrderRadiologi(data) {
        const detailItems = (data.detail_pemeriksaan || [])
            .map((item, index) => {
                const fotoHtml = item.foto_hasil_url
                    ? `
                    <a href="${item.foto_hasil_url}" target="_blank" class="inline-block">
                        <img src="${item.foto_hasil_url}" alt="Foto Hasil Radiologi"
                            class="mt-3 w-full max-w-xs rounded-xl border border-slate-200 shadow-sm object-cover">
                    </a>
                  `
                    : `<p class="mt-3 text-sm text-slate-500 italic">Belum ada foto hasil.</p>`;

                return `
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 bg-slate-50/70 dark:bg-slate-900/40">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                        <h4 class="text-base font-bold text-slate-800 dark:text-slate-100">
                            Item Pemeriksaan #${index + 1}
                        </h4>
                        ${renderStatusBadge(item.status_pemeriksaan)}
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Nama Pemeriksaan</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(item.nama_pemeriksaan)}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Detail ID</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(item.detail_id)}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Tanggal Pemeriksaan</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(item.tanggal_pemeriksaan)}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Jam Pemeriksaan</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(item.jam_pemeriksaan)}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Perawat Input</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(item.perawat_input)}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Path File</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100 break-all">${escapeHtml(item.foto_hasil_path)}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-slate-500 dark:text-slate-400">Keterangan Hasil</p>
                            <div class="mt-1 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-3 text-slate-700 dark:text-slate-100">
                                ${escapeHtml(item.keterangan_hasil)}
                            </div>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Dibuat</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(item.created_at)}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Diupdate</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(item.updated_at)}</p>
                        </div>
                    </div>

                    ${fotoHtml}
                </div>
            `;
            })
            .join("");

        return `
        <div class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                    <h4 class="text-sm font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-4">
                        Informasi Order
                    </h4>

                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Order ID</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(data.id)}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Kunjungan ID</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(data.kunjungan_id)}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Status</p>
                            <div class="mt-1">${renderStatusBadge(data.status)}</div>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Tanggal Order</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(data.tanggal_order)}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Terakhir Update</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(data.updated_order)}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                    <h4 class="text-sm font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-4">
                        Data Pasien
                    </h4>

                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Nama Pasien</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(data.pasien?.nama)}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Jenis Kelamin</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(data.pasien?.jenis_kelamin)}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Tanggal Lahir</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(data.pasien?.tanggal_lahir)}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">No HP</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(data.pasien?.no_hp)}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Alamat</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(data.pasien?.alamat)}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                    <h4 class="text-sm font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-4">
                        Data Dokter
                    </h4>

                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Nama Dokter</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(data.dokter?.nama)}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">Spesialis</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(data.dokter?.spesialis)}</p>
                        </div>
                        <div>
                            <p class="text-slate-500 dark:text-slate-400">No HP</p>
                            <p class="font-semibold text-slate-800 dark:text-slate-100">${escapeHtml(data.dokter?.no_hp)}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h4 class="text-base font-bold text-slate-800 dark:text-slate-100 mb-4">
                    Detail Pemeriksaan Radiologi
                </h4>

                <div class="space-y-4">
                    ${
                        detailItems ||
                        `
                        <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-600 p-6 text-center text-slate-500 dark:text-slate-400">
                            Tidak ada detail pemeriksaan.
                        </div>
                    `
                    }
                </div>
            </div>
        </div>
    `;
    }

    $(document).on("click", ".btn-detail-order-radiologi", function () {
        const id = $(this).data("id");

        openModalDetailRadiologi();

        $("#contentDetailOrderRadiologi").html(`
        <div class="flex items-center justify-center py-16">
            <div class="text-center">
                <div class="animate-spin inline-block w-8 h-8 border-4 border-sky-500 border-t-transparent rounded-full mb-3"></div>
                <p class="text-sm text-slate-500 dark:text-slate-400">Memuat detail order radiologi...</p>
            </div>
        </div>
    `);

        $.ajax({
            url: `/perawat/get-data-detail-order-radiologi/${id}`,
            type: "GET",
            success: function (response) {
                if (!response.success) {
                    $("#contentDetailOrderRadiologi").html(`
                    <div class="rounded-xl bg-red-50 text-red-700 border border-red-200 px-4 py-3">
                        Gagal memuat detail order radiologi.
                    </div>
                `);
                    return;
                }

                $("#contentDetailOrderRadiologi").html(
                    renderDetailOrderRadiologi(response.data),
                );
            },
            error: function (xhr) {
                const message =
                    xhr.responseJSON?.message ||
                    xhr.responseJSON?.error ||
                    "Terjadi kesalahan saat mengambil detail order radiologi.";

                $("#contentDetailOrderRadiologi").html(`
                <div class="rounded-xl bg-red-50 text-red-700 border border-red-200 px-4 py-3">
                    ${escapeHtml(message)}
                </div>
            `);
            },
        });
    });

    $(
        "#btnCloseModalDetailOrderRadiologi, #modalDetailOrderRadiologiOverlay",
    ).on("click", function () {
        closeModalDetailRadiologi();
    });

    $(document).on("keydown", function (e) {
        if (e.key === "Escape") {
            closeModalDetailRadiologi();
        }
    });
});
