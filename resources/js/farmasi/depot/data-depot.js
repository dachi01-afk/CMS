import $ from "jquery";
import axios from "axios";
import { Modal } from "flowbite";

$(function () {
    const table = $("#table-depot").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        searching: true,
        ajax: {
            url: "/farmasi/depot/get-data-depot", // sesuaikan route kamu
            type: "GET",
        },
        columns: [
            {
                data: "DT_RowIndex",
                name: "DT_RowIndex",
                orderable: false,
                searchable: false,
            },
            { data: "nama_depot", name: "depot.nama_depot" }, // ini HTML card depot + badge
            { data: "nama_tipe_depot", name: "tipe_depot.nama_tipe_depot" }, // kolom tipe
            {
                data: "jumlah_stok_depot",
                name: "depot.jumlah_stok_depot",
                searchable: false,
            },
            {
                data: "aksi",
                name: "aksi",
                searchable: false,
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

    // custom search (seperti input assist.id)
    $("#customSearch").on("keyup", function () {
        table.search(this.value).draw();
    });

    // Page length
    $("#custom_pageLength").on("change", function () {
        table.page.len(parseInt($(this).val(), 10)).draw();
    });

    // Pagination custom
    const $info = $("#obat-customInfo");
    const $pagination = $("#obat-customPagination");

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
});

$(function () {
    const elementModalShowObat = document.getElementById("modal-show-obat");
    const modalShowObat = elementModalShowObat
        ? new Modal(elementModalShowObat, {
              backdrop: "static",
              closable: false,
          })
        : null;

    // =========================
    // SHOW MODAL + LOAD DATA
    // =========================
    $(document).on("click", "#btn-show-obat", function () {
        const url = $(this).data("url"); // ✅ BENAR

        if (!url) {
            console.error("URL tidak ditemukan pada tombol Show Obat");
            return;
        }

        // tampilkan modal
        modalShowObat.show();

        // reset isi tabel
        $("#modal-obat-body").html(`
            <tr>
                <td colspan="2"
                    class="px-4 py-6 text-center text-slate-400">
                    Memuat data...
                </td>
            </tr>
        `);

        // ajax ambil data obat
        $.ajax({
            url: url,
            type: "GET",
            success: function (res) {
                let html = "";

                if (!res.data || res.data.length === 0) {
                    html = `
                        <tr>
                            <td colspan="2"
                                class="px-4 py-6 text-center text-slate-400">
                                Data tidak tersedia
                            </td>
                        </tr>
                    `;
                } else {
                    res.data.forEach((item) => {
                        html += `
                            <tr class="border-t">
                                <td class="px-4 py-2">
                                    ${item.nama_obat ?? "-"}
                                </td>
                                <td class="px-4 py-2">
                                    ${item.pivot?.stok_obat ?? 0}
                                </td>
                            </tr>
                        `;
                    });
                }

                $("#modal-obat-body").html(html);
            },
            error: function (xhr) {
                console.error(xhr);
                $("#modal-obat-body").html(`
                    <tr>
                        <td colspan="2"
                            class="px-4 py-6 text-center text-red-500">
                            Gagal mengambil data
                        </td>
                    </tr>
                `);
            },
        });
    });

    // =========================
    // CLOSE MODAL
    // =========================
    $(document).on(
        "click",
        "#btn-close-show-modal-obat, #btn-close-footer",
        function () {
            modalShowObat.hide();
        },
    );
});

$(function () {
    const elemetModalRepairObat = document.getElementById("modal-repair-obat");
    const modalRepairObat = elemetModalRepairObat
        ? new Modal(elemetModalRepairObat, {
              backdrop: "static",
              closable: false,
          })
        : null;

    const $formRepairObat = $("#form-modal-repair-obat");
    let tableRepair = null;
    let currentDepotId = null; // Menyimpan ID Depot yang sedang aktif
    let repairDataState = {}; // Menyimpan input fisik user (State Management)

    window.resetModalRepair = function () {
        // 1. Reset input pencarian
        $("#globalSearchObat").val("");

        // 2. Reset State & ID
        repairDataState = {};
        currentDepotId = null;

        // 3. Reset filter DataTable dan kembalikan ke halaman 1
        if (tableRepair) {
            tableRepair.search("").page.len(10).page(0).draw();
        }

        // 4. Bersihkan UI manual
        $(".input-qty-fisik").val("");
        $(".text-selisih").text("0").removeClass("text-red-500 text-green-500");
        $(".input-keterangan")
            .val("Belum Diisi")
            .removeClass("text-red-600 text-green-600");
    };

    window.closeModalRepair = function () {
        if (modalRepairObat) {
            modalRepairObat.hide();
            resetModalRepair();
        }
    };

    $(document).on("click", "#btn-repair-obat", function () {
        const url = $(this).data("url");
        currentDepotId = $(this).data("id"); // Ambil ID depot dari attribute data-id tombol

        if (!url) {
            console.error("URL tidak ditemukan");
            return;
        }

        modalRepairObat.show();

        if ($.fn.DataTable.isDataTable("#table-repair-obat")) {
            tableRepair.ajax.url(url).load();
        } else {
            tableRepair = $("#table-repair-obat").DataTable({
                processing: true,
                responsive: true,
                serverSide: true,
                paging: true,
                searching: true,
                searchDelay: 250,
                ordering: true,
                pageLength: 10,
                lengthChange: false,
                info: false,
                ajax: { url: url },
                columns: [
                    {
                        data: "kode_obat",
                        name: "kode_obat",
                        className: "border border-gray-300 px-3 py-2",
                    },
                    {
                        data: "nama_obat",
                        name: "nama_obat",
                        className:
                            "border border-gray-300 px-3 py-2 text-gray-600",
                    },
                    {
                        data: "pivot.stok_obat",
                        name: "pivot.stok_obat",
                        searchable: false,
                        className:
                            "border border-gray-300 px-3 py-2 text-center col-qty-akhir",
                        render: (data) => data || 0,
                    },
                    {
                        data: null,
                        render: (data, type, row) => {
                            // PASTIKAN MENGGUNAKAN row.obat_id atau row.id sesuai aslinya
                            // Jika row.id adalah ID Obat, maka ini sudah benar.
                            // Tapi jika tidak, gunakan row.obat_id
                            const idObat = row.obat_id ? row.obat_id : row.id;
                            const savedVal = repairDataState[idObat] || "";
                            return `
                <div class="px-2">
                    <input type="number" name="qty_fisik[]" data-id="${idObat}" 
                        class="input-qty-fisik w-full border-b border-gray-400 focus:border-blue-600 outline-none text-center bg-transparent py-1 transition-all" 
                        placeholder="0" value="${savedVal}">
                </div>`;
                        },
                        className: "border border-gray-300 px-3 py-4",
                    },
                    {
                        data: null,
                        render: (data, type, row) => {
                            // Hitung selisih dari state jika ada
                            let displaySelisih = "0";
                            let colorClass = "";
                            if (repairDataState[row.id] !== undefined) {
                                const diff =
                                    (row.pivot.stok_obat || 0) -
                                    parseFloat(repairDataState[row.id]);
                                displaySelisih = isNaN(diff) ? "0" : diff;
                                colorClass =
                                    diff === 0
                                        ? "text-green-500"
                                        : "text-red-500";
                            }
                            return `<span class="text-selisih font-bold ${colorClass}">${displaySelisih}</span>`;
                        },
                        className:
                            "border border-gray-300 px-3 py-2 text-center",
                    },
                    {
                        data: null,
                        render: (data, type, row) => {
                            let ket = "Belum Diisi";
                            let colorClass = "";
                            if (repairDataState[row.id] !== undefined) {
                                const diff =
                                    (row.pivot.stok_obat || 0) -
                                    parseFloat(repairDataState[row.id]);
                                ket = diff === 0 ? "Sesuai" : "Tidak Sesuai";
                                colorClass =
                                    diff === 0
                                        ? "text-green-600"
                                        : "text-red-600";
                            }
                            return `<input type="text" class="input-keterangan w-full border-b border-gray-300 outline-none text-sm bg-transparent text-center ${colorClass}" readonly value="${ket}">`;
                        },
                        className: "border border-gray-300 px-3 py-2",
                    },
                ],
                dom: "t",
                drawCallback: function () {
                    updatePagination();
                },
            });
        }
    });

    // --- LOGIC PERHITUNGAN & STATE ---
    $(document).on("input", ".input-qty-fisik", function () {
        const $row = $(this).closest("tr");
        const obatId = $(this).data("id");
        const qtyAkhir = parseFloat($row.find(".col-qty-akhir").text()) || 0;
        const qtyFisikVal = $(this).val();
        const qtyFisik = parseFloat(qtyFisikVal);
        const $selisihCell = $row.find(".text-selisih");
        const $ketInput = $row.find(".input-keterangan");

        if (qtyFisikVal === "" || isNaN(qtyFisik)) {
            $selisihCell.text("0").removeClass("text-red-500 text-green-500");
            $ketInput
                .val("Belum Diisi")
                .removeClass("text-red-600 text-green-600");
            delete repairDataState[obatId]; // Hapus dari state jika kosong
            return;
        }

        // Simpan ke State
        repairDataState[obatId] = qtyFisikVal;

        const selisih = qtyAkhir - qtyFisik;
        $selisihCell.text(selisih);

        if (selisih === 0) {
            $ketInput
                .val("Sesuai")
                .addClass("text-green-600")
                .removeClass("text-red-600");
            $selisihCell.addClass("text-green-500").removeClass("text-red-500");
        } else {
            $ketInput
                .val("Tidak Sesuai")
                .addClass("text-red-600")
                .removeClass("text-green-600");
            $selisihCell.addClass("text-red-500").removeClass("text-green-500");
        }
    });

    // --- AJAX SUBMIT ---
    $(document).on("click", "#btn-save-repair", function (e) {
        e.preventDefault();
        const $btn = $(this);
        const url = $formRepairObat.data("url");

        // Validasi state tidak boleh kosong
        const items = Object.keys(repairDataState).map((id) => ({
            obat_id: id,
            qty_fisik: repairDataState[id],
        }));

        if (items.length === 0) {
            alert(
                "Silahkan isi minimal satu data fisik obat sebelum menyimpan.",
            );
            return;
        }

        if (!confirm("Apakah Anda yakin ingin memperbarui stok fisik?")) return;

        $btn.prop("disabled", true).text("Menyimpan...");

        $.ajax({
            url: url,
            type: "POST",
            data: {
                _token: $('input[name="_token"]').val(),
                depot_id: currentDepotId,
                items: items,
            },
            success: function (res) {
                alert(res.message);
                closeModalRepair();
                // Reload tabel utama (daftar depot) jika menggunakan DataTables
                if ($.fn.DataTable.isDataTable("#table-depot")) {
                    $("#table-depot").DataTable().ajax.reload(null, false);
                }
            },
            error: function (xhr) {
                const msg =
                    xhr.responseJSON?.message || "Terjadi kesalahan sistem";
                alert("Gagal: " + msg);
            },
            complete: function () {
                $btn.prop("disabled", false).text("Simpan Perubahan");
            },
        });
    });

    // --- GLOBAL SEARCH OBAT ---
    const $globalSearchObat = $("#globalSearchObat");
    let searchTimer = null;
    let lastValue = "";
    let inflightXhr = null;

    if ($globalSearchObat.length) {
        const runSearch = (value) => {
            if (value === lastValue) return;
            lastValue = value;
            tableRepair.search(value).draw();
        };

        $globalSearchObat.on("input", function () {
            const value = $(this).val().trim();
            if (inflightXhr && inflightXhr.readyState !== 4)
                inflightXhr.abort();

            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => runSearch(value), 300);
        });

        $globalSearchObat.on("keydown", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                clearTimeout(searchTimer);
                runSearch($(this).val().trim());
            }
        });
    }

    // --- PAGINATION ---
    const $info = $("#obat-customInfo");
    const $pagination = $("#obat-customPagination");
    const $perPage = $("#obat-pageLength");

    function updatePagination() {
        if (!tableRepair) return;
        const info = tableRepair.page.info();
        const totalRecords = info.recordsDisplay || 0;
        const startNode = totalRecords === 0 ? 0 : info.start + 1;
        const endNode = info.end || 0;
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        $info.text(
            `Menampilkan ${startNode}–${endNode} dari ${totalRecords} data`,
        );
        $pagination.empty();

        const prevDisabled =
            currentPage === 1 ? "opacity-50 pointer-events-none" : "";
        $pagination.append(
            `<li><a href="#" id="btnPrev" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 ${prevDisabled}">Prev</a></li>`,
        );

        for (let i = 1; i <= totalPages; i++) {
            const active =
                i === currentPage
                    ? "text-blue-600 bg-blue-50 border-blue-300 z-10"
                    : "text-gray-500 bg-white border-gray-300 hover:bg-gray-100";
            $pagination.append(
                `<li><a href="#" class="page-number flex items-center justify-center px-4 h-8 border ${active}" data-page="${i}">${i}</a></li>`,
            );
        }

        const nextDisabled =
            currentPage === totalPages || totalPages === 0
                ? "opacity-50 pointer-events-none"
                : "";
        $pagination.append(
            `<li><a href="#" id="btnNext" class="flex items-center justify-center px-3 h-8 text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 ${nextDisabled}">Next</a></li>`,
        );
    }

    $pagination.on("click", "a", function (e) {
        e.preventDefault();
        const id = $(this).attr("id");
        if (id === "btnPrev") tableRepair.page("previous").draw("page");
        else if (id === "btnNext") tableRepair.page("next").draw("page");
        else if ($(this).hasClass("page-number"))
            tableRepair.page(parseInt($(this).data("page")) - 1).draw("page");
    });

    $perPage.on("change", function () {
        tableRepair.page.len(parseInt($(this).val())).draw();
    });
});
