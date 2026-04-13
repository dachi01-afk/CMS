import $ from "jquery";

$(function () {
    const isSuperAdmin =
        String($("#tabelTriage").data("is-super-admin")) === "1";

    const columns = [
        {
            data: "DT_RowIndex",
            name: "DT_RowIndex",
            orderable: false,
            searchable: false,
        },
        {
            data: "no_antrian",
            name: "no_antrian",
        },
        {
            data: "nama_pasien",
            name: "nama_pasien",
        },
        {
            data: "nama_dokter",
            name: "nama_dokter",
        },
        {
            data: "nama_poli",
            name: "nama_poli",
        },
    ];

    if (isSuperAdmin) {
        columns.push({
            data: "nama_perawat",
            name: "nama_perawat",
            defaultContent: "-",
        });
    }

    columns.push(
        {
            data: "keluhan_utama",
            name: "keluhan_utama",
        },
        {
            data: "action",
            name: "action",
            orderable: false,
            searchable: false,
            className: "text-center whitespace-nowrap",
        },
    );

    const table = $("#tabelTriage").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        lengthChange: false,
        info: false,
        ajax: "/perawat/getDataKunjunganDenganStatusEngaged",
        columns: columns,
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

    // Search
    $("#triage_searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    // Pagination custom
    const $info = $("#triage_customInfo");
    const $pagination = $("#triage_customPagination");
    const $perPage = $("#triage_pageLength");

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
            `<li><a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700 ${prevDisabled}">Previous</a></li>`,
        );

        const maxVisible = 5;
        let start = Math.max(currentPage - Math.floor(maxVisible / 2), 1);
        let end = Math.min(start + maxVisible - 1, totalPages);

        if (end - start < maxVisible - 1) {
            start = Math.max(end - maxVisible + 1, 1);
        }

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

        if ($link.attr("id") === "btnPrev") {
            table.page("previous").draw("page");
        } else if ($link.attr("id") === "btnNext") {
            table.page("next").draw("page");
        } else if ($link.hasClass("page-number")) {
            table.page(parseInt($link.data("page")) - 1).draw("page");
        }
    });

    $perPage.on("change", function () {
        table.page.len(parseInt($(this).val())).draw();
    });

    table.on("draw", updatePagination);
    updatePagination();

    // =========================
    // MODAL DETAIL KUNJUNGAN
    // =========================
    const $modal = $("#modalDetailKunjungan");

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

    function openModal() {
        $modal.removeClass("hidden").addClass("flex");
        lockBodyScroll();
    }

    function closeModal() {
        $modal.addClass("hidden").removeClass("flex");
        unlockBodyScroll();
    }

    function setText(id, value) {
        $(id).text(value ?? "-");
    }

    function resetModalToLoading() {
        setText("#detail_no_antrian", "Memuat...");
        setText("#detail_tanggal_kunjungan", "Memuat...");
        setText("#detail_status_kunjungan", "Memuat...");
        setText("#detail_nama_pasien", "Memuat...");
        setText("#detail_nama_dokter", "Memuat...");
        setText("#detail_nama_poli", "Memuat...");
        setText("#detail_nama_perawat", "Memuat...");
        setText("#detail_keluhan_awal", "Memuat...");
        setText("#detail_keluhan_utama", "Memuat...");
        setText("#detail_tekanan_darah", "Memuat...");
        setText("#detail_suhu_tubuh", "Memuat...");
        setText("#detail_tinggi_badan", "Memuat...");
        setText("#detail_berat_badan", "Memuat...");
        setText("#detail_imt", "Memuat...");
        setText("#detail_nadi", "Memuat...");
        setText("#detail_pernapasan", "Memuat...");
        setText("#detail_saturasi_oksigen", "Memuat...");
        setText("#detail_riwayat_penyakit_dahulu", "Memuat...");
        setText("#detail_riwayat_penyakit_keluarga", "Memuat...");
        setText("#detail_diagnosis", "Memuat...");
    }

    $("#btnCloseModalDetailKunjungan, #btnTutupModalDetailKunjungan").on(
        "click",
        function () {
            closeModal();
        },
    );

    $modal.on("click", function (e) {
        if ($(e.target).is("#modalDetailKunjungan")) {
            closeModal();
        }
    });

    $(document).on("keydown", function (e) {
        if (e.key === "Escape") {
            closeModal();
        }
    });

    // Khusus Super Admin: tombol detail
    $(document).on("click", ".btn-detail-kunjungan", function () {
        if (!isSuperAdmin) return;

        const emrId = $(this).data("id");

        resetModalToLoading();
        openModal();

        $.ajax({
            url: `/perawat/get-data-detail-kunjungan-engaged/${emrId}`,
            type: "GET",
            success: function (response) {
                const d = response.data || {};

                setText("#detail_no_antrian", d.no_antrian);
                setText("#detail_tanggal_kunjungan", d.tanggal_kunjungan);
                setText("#detail_status_kunjungan", d.status_kunjungan);
                setText("#detail_nama_pasien", d.nama_pasien);
                setText("#detail_nama_dokter", d.nama_dokter);
                setText("#detail_nama_poli", d.nama_poli);
                setText("#detail_nama_perawat", d.nama_perawat);
                setText("#detail_keluhan_awal", d.keluhan_awal);
                setText("#detail_keluhan_utama", d.keluhan_utama);
                setText("#detail_tekanan_darah", d.tekanan_darah);
                setText("#detail_suhu_tubuh", d.suhu_tubuh);
                setText("#detail_tinggi_badan", d.tinggi_badan);
                setText("#detail_berat_badan", d.berat_badan);
                setText("#detail_imt", d.imt);
                setText("#detail_nadi", d.nadi);
                setText("#detail_pernapasan", d.pernapasan);
                setText("#detail_saturasi_oksigen", d.saturasi_oksigen);
                setText(
                    "#detail_riwayat_penyakit_dahulu",
                    d.riwayat_penyakit_dahulu,
                );
                setText(
                    "#detail_riwayat_penyakit_keluarga",
                    d.riwayat_penyakit_keluarga,
                );
                setText("#detail_diagnosis", d.diagnosis);
            },
            error: function (xhr) {
                const message =
                    xhr.responseJSON?.message ||
                    "Gagal mengambil detail kunjungan.";

                setText("#detail_no_antrian", "-");
                setText("#detail_tanggal_kunjungan", "-");
                setText("#detail_status_kunjungan", "-");
                setText("#detail_nama_pasien", message);
                setText("#detail_nama_dokter", "-");
                setText("#detail_nama_poli", "-");
                setText("#detail_nama_perawat", "-");
                setText("#detail_keluhan_awal", "-");
                setText("#detail_keluhan_utama", "-");
                setText("#detail_tekanan_darah", "-");
                setText("#detail_suhu_tubuh", "-");
                setText("#detail_tinggi_badan", "-");
                setText("#detail_berat_badan", "-");
                setText("#detail_imt", "-");
                setText("#detail_nadi", "-");
                setText("#detail_pernapasan", "-");
                setText("#detail_saturasi_oksigen", "-");
                setText("#detail_riwayat_penyakit_dahulu", "-");
                setText("#detail_riwayat_penyakit_keluarga", "-");
                setText("#detail_diagnosis", "-");
            },
        });
    });
});
