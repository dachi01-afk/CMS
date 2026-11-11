import $ from "jquery";
import { initRemoteFilterSelect } from "../../shared/remote-filter-select";

function formatRupiah(value) {
    return "Rp " + new Intl.NumberFormat("id-ID").format(Number(value) || 0);
}

$(function () {
    const $periode = $("#periodeAudit");
    const $bulan = $("#bulanAudit");
    const $tahun = $("#tahunAudit");
    const $poli = $("#poliAudit");
    const $layanan = $("#layananAudit");

    if (!$("#tabelAuditKunjungan").length) return;

    const table = $("#tabelAuditKunjungan").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        // Default: urut tanggal kunjungan terbaru (dipetakan ke kolom asli di bawah).
        order: [[1, "desc"]],
        lengthChange: false,
        info: false,
        ajax: {
            url: "/super-admin/laporan-audit-kunjungan/data",
            type: "GET",
            data: function (d) {
                d.periode = $periode.val();
                d.bulan = $bulan.val();
                d.tahun = $tahun.val();
                d.poli_id = $poli.val();
                d.layanan_id = $layanan.val();
            },
        },
        // `name` memakai kolom asli (tabel.kolom) supaya Yajra tidak salah
        // menerjemahkan alias hasil join. Pencarian teks ditangani controller
        // (applyFilters) sehingga semua kolom di-set searchable: false.
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            {
                data: "tanggal_jam",
                name: "kunjungan.tanggal_kunjungan",
                orderable: true,
                searchable: false,
            },
            {
                data: "no_antrian",
                name: "kunjungan.no_antrian",
                orderable: true,
                searchable: false,
            },
            {
                data: "pasien",
                name: "pasien.nama_pasien",
                orderable: true,
                searchable: false,
            },
            {
                data: "nama_poli",
                name: "poli.nama_poli",
                orderable: true,
                searchable: false,
            },
            {
                data: "nama_dokter",
                name: "dokter.nama_dokter",
                orderable: true,
                searchable: false,
            },
            {
                data: "total_pembayaran_rp",
                name: "total_pembayaran_rp",
                orderable: false,
                searchable: false,
            },
            {
                data: "status_kunjungan",
                name: "kunjungan.status",
                orderable: true,
                searchable: false,
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
                "bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/40",
            );
            $("td", row).addClass(
                "px-4 py-4 align-top text-gray-900 dark:text-white",
            );
        },
    });

    // =========================================================
    // 🔎 Filter Layanan & Poli: TomSelect + remote search
    //    (opsi diambil lewat AJAX, maksimal 20 per pencarian)
    // =========================================================
    // Modul bersama dengan Laporan Transaksi Order Layanan.
    initRemoteFilterSelect("#layananAudit", "Layanan tidak ditemukan");
    initRemoteFilterSelect("#poliAudit", "Poli tidak ditemukan");

    // 🔎 search custom
    $("#audit_searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    // =========================================================
    // Summary cards
    // =========================================================
    table.on("xhr.dt", function (event, settings, json) {
        const summary = json?.summary;
        if (!summary) return;

        $("#auditTotalKunjungan").text(
            new Intl.NumberFormat("id-ID").format(
                Number(summary.total_kunjungan) || 0,
            ),
        );
        $("#auditTotalPasien").text(
            new Intl.NumberFormat("id-ID").format(
                Number(summary.total_pasien) || 0,
            ),
        );
        $("#auditTotalDiperiksa").text(
            new Intl.NumberFormat("id-ID").format(
                Number(summary.sudah_diperiksa) || 0,
            ),
        );
        $("#auditTotalPembayaran").text(formatRupiah(summary.total_pembayaran));
    });

    const $info = $("#audit_customInfo");
    const $pagination = $("#audit_customPagination");
    const $perPage = $("#audit_pageLength");

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        $info.text(
            `Menampilkan ${info.recordsDisplay === 0 ? 0 : info.start + 1}–${
                info.end
            } dari ${info.recordsDisplay} data (Halaman ${currentPage} dari ${totalPages})`,
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

    // =========================================================
    // 🔽 Bulan hanya tampil untuk periode bulanan
    //    Label & input disembunyikan bersama lewat wrapper.
    // =========================================================
    const $bulanWrapper = $("#bulanAuditWrapper");

    function syncBulanVisibility() {
        $bulanWrapper.toggleClass("hidden", $periode.val() !== "bulanan");
    }

    $periode.on("change", function () {
        syncBulanVisibility();
        table.ajax.reload();
    });

    syncBulanVisibility();

    // Sinkronkan ulang bila browser memulihkan pilihan saat back/forward.
    $periode.one("focus", syncBulanVisibility);

    // TomSelect menyinkronkan nilai ke <select> aslinya lalu memicu event change.
    $bulan.add($tahun).add($poli).add($layanan).on("change", function () {
        table.ajax.reload();
    });
});

// =========================================================
// ✅ MODAL DETAIL: perjalanan pasien per tahap
// =========================================================
$(function () {
    const modal = document.getElementById("modalDetailAuditKunjungan");
    if (!modal) return;

    const errorBox = document.getElementById("auditDetailError");
    const loading = document.getElementById("auditDetailLoading");
    const subtitle = document.getElementById("auditDetailSubtitle");
    const tindakanList = document.getElementById("auditTindakanList");
    const obatList = document.getElementById("auditObatList");

    function formatRupiah(value) {
        return (
            "Rp " + new Intl.NumberFormat("id-ID").format(Number(value) || 0)
        );
    }

    function setText(id, value, fallback = "-") {
        const node = document.getElementById(id);
        if (!node) return;
        node.textContent =
            value === null || value === undefined || value === ""
                ? fallback
                : String(value);
    }

    function renderList(node, items, emptyText) {
        if (!node) return;
        node.innerHTML = "";

        if (!items || items.length === 0) {
            const li = document.createElement("li");
            li.className = "italic text-slate-400";
            li.textContent = emptyText;
            node.appendChild(li);
            return;
        }

        items.forEach((item) => {
            const li = document.createElement("li");
            li.className = "flex items-start justify-between gap-3";

            const nama = document.createElement("span");
            nama.textContent = item.nama || "-";

            const qty = document.createElement("span");
            qty.className =
                "shrink-0 rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-semibold text-sky-700";
            qty.textContent = "x" + (item.qty ?? 0);

            li.append(nama, qty);
            node.appendChild(li);
        });
    }

    function openModal() {
        modal.classList.remove("hidden");
        modal.classList.add("flex");
        document.documentElement.style.overflow = "hidden";
    }

    function closeModal() {
        modal.classList.add("hidden");
        modal.classList.remove("flex");
        document.documentElement.style.overflow = "";
    }

    function setError(message) {
        if (!errorBox) return;

        if (!message) {
            errorBox.classList.add("hidden");
            errorBox.textContent = "";
            return;
        }

        errorBox.textContent = message;
        errorBox.classList.remove("hidden");
    }

    function setLoading(isLoading) {
        if (!loading) return;
        loading.classList.toggle("hidden", !isLoading);
    }

    function fill(data) {
        const pendaftaran = data.pendaftaran || {};
        const pemeriksaan = data.pemeriksaan || {};
        const dokter = data.dokter || {};
        const kasir = data.kasir || {};
        const farmasi = data.farmasi || {};

        if (subtitle) {
            const pasien = pendaftaran.nama_pasien || "Pasien";
            const emr = pendaftaran.no_emr ? ` · ${pendaftaran.no_emr}` : "";
            subtitle.textContent = pasien + emr;
        }

        // 1. Pendaftaran
        setText("auditTanggalKunjungan", pendaftaran.tanggal_kunjungan);
        setText("auditJamKunjungan", pendaftaran.jam_kunjungan);
        setText("auditJamInput", pendaftaran.jam_input, "Tidak tercatat");
        setText("auditAntrian", pendaftaran.no_antrian, "-");
        setText("auditPasien", pendaftaran.nama_pasien);
        setText("auditEmr", pendaftaran.no_emr);
        setText("auditPoli", pendaftaran.nama_poli, "Belum ditentukan");
        setText(
            "auditDokterPendaftaran",
            pendaftaran.nama_dokter,
            "Belum ditentukan",
        );
        setText("auditPenginput", pendaftaran.input_oleh, "Tidak tercatat");
        setText("auditPenginputRole", pendaftaran.input_oleh_role, "Role tidak tercatat");

        // 2. Pemeriksaan awal
        // Bila rekam medis sudah ada tapi akun perawat tidak tercatat, bedakan
        // keterangannya dengan kunjungan yang memang belum diperiksa.
        setText(
            "auditPerawat",
            pemeriksaan.nama_perawat,
            pemeriksaan.waktu ? "Perawat tidak tercatat" : "Belum diperiksa",
        );
        setText("auditJamPeriksa", pemeriksaan.waktu, "Belum diperiksa");

        // 3. Dokter
        setText("auditDokterPeriksa", dokter.nama_dokter, "Belum ditentukan");
        setText("auditDiagnosis", dokter.diagnosis, "Belum ada diagnosis");
        renderList(tindakanList, dokter.tindakan, "Belum ada tindakan");

        // 4. Kasir
        setText("auditKodeBayar", kasir.kode_transaksi, "Belum ada tagihan");
        setText(
            "auditTotalTagihan",
            kasir.total === null || kasir.total === undefined
                ? null
                : formatRupiah(kasir.total),
            "Belum ada tagihan",
        );
        setText("auditMetode", kasir.metode, "-");
        setText("auditStatusBayar", kasir.status, "Belum ada tagihan");
        setText("auditTanggalBayar", kasir.tanggal_bayar, "Belum dibayar");
        setText("auditKasir", kasir.kasir, "Belum ada kasir");

        // 5. Farmasi
        const statusResep =
            farmasi.status_resep === "done"
                ? "Obat sudah diserahkan"
                : farmasi.status_resep === "waiting"
                  ? "Menunggu obat"
                  : null;
        setText("auditStatusResep", statusResep, "Belum ada resep");
        setText("auditFarmasiAkun", farmasi.akun, "Tidak tercatat");
        setText("auditJamSerah", farmasi.waktu, "Belum diserahkan");
        renderList(obatList, farmasi.obat, "Belum ada obat");
    }

    document
        .querySelectorAll('[data-close-modal="detail-audit-kunjungan"]')
        .forEach((button) => {
            button.addEventListener("click", closeModal);
        });

    modal.addEventListener("click", (event) => {
        if (event.target === modal) closeModal();
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") closeModal();
    });

    $("#tabelAuditKunjungan").on(
        "click",
        ".btn-detail-audit-kunjungan",
        async function () {
            const url = $(this).data("detail-url");
            if (!url) return;

            setError(null);
            setLoading(true);
            openModal();

            try {
                const response = await fetch(url, {
                    method: "GET",
                    credentials: "same-origin",
                    headers: {
                        Accept: "application/json",
                        "X-Requested-With": "XMLHttpRequest",
                    },
                });

                const json = await response.json().catch(() => null);

                if (!response.ok || !json?.success) {
                    setError(
                        json?.message || "Gagal mengambil detail kunjungan.",
                    );
                    setLoading(false);
                    return;
                }

                fill(json.data || {});
                setLoading(false);
            } catch (error) {
                console.error(error);
                setError("Tidak dapat terhubung ke server.");
                setLoading(false);
            }
        },
    );
});
