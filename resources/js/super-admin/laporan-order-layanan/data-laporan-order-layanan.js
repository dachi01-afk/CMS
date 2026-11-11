import $ from "jquery";
import { initRemoteFilterSelect } from "../../shared/remote-filter-select";

function formatRupiah(value) {
    return "Rp " + new Intl.NumberFormat("id-ID").format(Number(value) || 0);
}

$(function () {
    const $periode = $("#periodeOrderLayanan");
    const $bulan = $("#bulanOrderLayanan");
    const $tahun = $("#tahunOrderLayanan");
    const $status = $("#statusOrderLayanan");
    const $layanan = $("#layananOrderLayanan");

    if (!$("#tabelLaporanOrderLayanan").length) return;

    // Filter Layanan: TomSelect + remote search (modul bersama Laporan Audit)
    initRemoteFilterSelect("#layananOrderLayanan", "Layanan tidak ditemukan");

    const table = $("#tabelLaporanOrderLayanan").DataTable({
        processing: true,
        serverSide: true,
        paging: true,
        searching: true,
        ordering: true,
        pageLength: 10,
        // Default: urut tanggal order terbaru (dipetakan ke kolom asli di bawah).
        order: [[2, "desc"]],
        lengthChange: false,
        info: false,
        ajax: {
            url: "/super-admin/laporan-order-layanan/data",
            type: "GET",
            data: function (d) {
                d.periode = $periode.val();
                d.bulan = $bulan.val();
                d.tahun = $tahun.val();
                d.status = $status.val();
                d.layanan_id = $layanan.val();
            },
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            {
                data: "kode_transaksi",
                name: "order_layanan.kode_transaksi",
                searchable: false,
            },
            {
                data: "tanggal_order",
                name: "order_layanan.tanggal_order",
                searchable: false,
            },
            {
                data: "nama_pasien",
                name: "nama_pasien",
                orderable: false,
                searchable: false,
            },
            {
                data: "no_emr",
                name: "no_emr",
                orderable: false,
                searchable: false,
            },
            {
                data: "metode_pembayaran",
                name: "metode_pembayaran",
                orderable: false,
                searchable: false,
            },
            {
                data: "total_qty",
                name: "total_qty",
                orderable: false,
                searchable: false,
            },
            {
                data: "subtotal_rp",
                name: "subtotal_rp",
                orderable: false,
                searchable: false,
            },
            {
                data: "potongan_rp",
                name: "potongan_rp",
                orderable: false,
                searchable: false,
            },
            {
                data: "total_bayar_rp",
                name: "total_bayar_rp",
                orderable: false,
                searchable: false,
            },
            {
                data: "status_order_layanan",
                name: "status_order_layanan",
                orderable: false,
                searchable: false,
            },
            {
                data: "tanggal_pembayaran",
                name: "tanggal_pembayaran",
                orderable: false,
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
            $("td", row).addClass("px-6 py-4 text-gray-900 dark:text-white");
        },
    });

    // 🔎 search custom
    $("#orderLayanan_searchInput").on("keyup", function () {
        table.search(this.value).draw();
    });

    // =========================================================
    // Summary cards (dikirim oleh endpoint data)
    // =========================================================
    table.on("xhr.dt", function (event, settings, json) {
        const summary = json?.summary;
        if (!summary) return;

        $("#laporanOrderLayananTotalTransaksi").text(
            new Intl.NumberFormat("id-ID").format(
                Number(summary.total_transaksi) || 0,
            ),
        );
        $("#laporanOrderLayananTotalPasien").text(
            new Intl.NumberFormat("id-ID").format(
                Number(summary.total_pasien) || 0,
            ),
        );
        $("#laporanOrderLayananSudahBayar").text(
            formatRupiah(summary.total_sudah_bayar),
        );
        $("#laporanOrderLayananBelumBayar").text(
            formatRupiah(summary.total_belum_bayar),
        );
    });

    const $info = $("#orderLayanan_customInfo");
    const $pagination = $("#orderLayanan_customPagination");
    const $perPage = $("#orderLayanan_pageLength");

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
    // 🔽 Tampilkan bulan/tahun sesuai periode
    // =========================================================
    const $bulanWrapper = $("#bulanOrderLayananWrapper");
    const $tahunWrapper = $("#tahunOrderLayananWrapper");

    // Label & input disembunyikan bersama lewat wrapper-nya.
    function syncPeriodeFields() {
        const val = $periode.val();

        $bulanWrapper.toggleClass("hidden", val !== "bulan");
        $tahunWrapper.toggleClass("hidden", val !== "bulan" && val !== "tahun");
    }

    $periode.on("change", function () {
        syncPeriodeFields();
        table.ajax.reload();
    });

    syncPeriodeFields();

    $bulan.on("change", function () {
        if ($periode.val() === "bulan") {
            table.ajax.reload();
        }
    });

    $tahun.on("change", function () {
        const val = $periode.val();
        if (val === "bulan" || val === "tahun") {
            table.ajax.reload();
        }
    });

    $status.on("change", function () {
        table.ajax.reload();
    });

    // TomSelect menyinkronkan nilai ke <select> aslinya lalu memicu event change,
    // jadi handler ini yang membuat tabel + kartu ringkasan ikut tersaring.
    $layanan.on("change", function () {
        table.ajax.reload();
    });
});

// =========================================================
// ✅ MODAL DETAIL TRANSAKSI
// =========================================================
$(function () {
    const modal = document.getElementById("modalDetailOrderLayanan");
    if (!modal) return;

    const tbody = document.getElementById("detailItemsBody");
    const errorBox = document.getElementById("detailOrderLayananError");
    const loading = document.getElementById("detailOrderLayananLoading");

    function formatRupiah(value) {
        return (
            "Rp " +
            new Intl.NumberFormat("id-ID").format(Number(value) || 0)
        );
    }

    function setText(id, value) {
        const node = document.getElementById(id);
        if (!node) return;
        node.textContent =
            value === null || value === undefined || value === ""
                ? "-"
                : String(value);
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

    function emptyItems() {
        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                    Belum ada data.
                </td>
            </tr>
        `;
    }

    function renderItems(items) {
        if (!tbody) return;

        if (!items || items.length === 0) {
            emptyItems();
            return;
        }

        tbody.innerHTML = "";

        items.forEach((item, index) => {
            const tr = document.createElement("tr");
            tr.className = "border-b border-slate-200 dark:border-slate-700";

            const cells = [
                {
                    value: item.no ?? index + 1,
                    className: "px-4 py-3 text-center",
                },
                {
                    value: item.nama_layanan || "-",
                    className:
                        "px-4 py-3 font-medium text-slate-800 dark:text-slate-100",
                },
                {
                    value: formatRupiah(item.harga_satuan),
                    className: "px-4 py-3 text-right",
                },
                {
                    value: `x${item.qty ?? 0}`,
                    className: "px-4 py-3 text-right",
                },
                {
                    value: formatRupiah(item.total_item),
                    className:
                        "px-4 py-3 text-right font-semibold text-slate-900 dark:text-white",
                },
            ];

            cells.forEach((cell) => {
                const td = document.createElement("td");
                td.className = cell.className;
                td.textContent = cell.value;
                tr.appendChild(td);
            });

            tbody.appendChild(tr);
        });
    }

    function setStatus(status) {
        const node = document.getElementById("detailStatus");
        if (!node) return;

        node.textContent = status || "-";
        node.className =
            "mt-1 inline-flex rounded-full px-3 py-1 text-xs font-semibold " +
            (status === "Sudah Bayar"
                ? "bg-emerald-100 text-emerald-700"
                : "bg-amber-100 text-amber-700");
    }

    function fill(data) {
        setText("detailKodeTransaksi", data.kode_transaksi);
        setText("detailTanggalOrder", data.tanggal_order);
        setText("detailNamaPasien", data.nama_pasien);
        setText("detailNoEmr", data.no_emr);
        setText("detailMetode", data.metode_pembayaran);
        setText("detailTanggalPembayaran", data.tanggal_pembayaran);
        setStatus(data.status);

        setText("detailSubtotal", formatRupiah(data.subtotal));
        setText("detailPotongan", formatRupiah(data.potongan));
        setText("detailTotalBayar", formatRupiah(data.total_bayar));

        renderItems(data.items || []);
    }

    document
        .querySelectorAll('[data-close-modal="detail-order-layanan"]')
        .forEach((button) => {
            button.addEventListener("click", closeModal);
        });

    modal.addEventListener("click", (event) => {
        if (event.target === modal) closeModal();
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") closeModal();
    });

    $("#tabelLaporanOrderLayanan").on(
        "click",
        ".btn-detail-order-layanan",
        async function () {
            const url = $(this).data("detail-url");
            if (!url) return;

            setError(null);
            setLoading(true);
            emptyItems();
            setText("detailSubtotal", formatRupiah(0));
            setText("detailPotongan", formatRupiah(0));
            setText("detailTotalBayar", formatRupiah(0));
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
                        json?.message || "Gagal mengambil detail transaksi.",
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
