import $ from "jquery";
import { Modal } from "flowbite";

// TomSelect & Swal diasumsikan sudah tersedia global (seperti di project kamu)
// window.TomSelect, window.Swal

// =====================================================
// 1) DATATABLES
// =====================================================
$(function () {
    $.ajaxSetup({
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    const table = $("#table-restock-return").DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        searching: true,
        ajax: {
            url: "/farmasi/restock-return/get-data-restock-dan-return-barang-dan-obat",
            type: "GET",
        },
        columns: [
            { data: "kode_transaksi", name: "kode_transaksi" },
            { data: "nomor_faktur", name: "nomor_faktur", defaultContent: "-" },
            {
                data: "jenis_transaksi",
                name: "jenis_transaksi",
                orderable: false,
                searchable: false,
            },
            {
                data: "tanggal_pengiriman",
                name: "tanggal_pengiriman",
                defaultContent: "-",
            },
            { data: "tanggal_pembuatan", name: "tanggal_pembuatan" },
            { data: "supplier_nama", name: "supplier_nama" },
            { data: "nama_item", name: "nama_item", defaultContent: "-" },
            { data: "total_jumlah", name: "total_jumlah", searchable: false },
            {
                data: "approved_by_nama",
                name: "approved_by_nama",
                defaultContent: "-",
            },
            { data: "total_harga", name: "total_harga", searchable: false },
            { data: "tempo", name: "tempo", defaultContent: "-" },
            {
                data: "aksi",
                orderable: false,
                searchable: false,
                className: "text-right",
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

    $("#customSearch").on("keyup", function () {
        table.search(this.value).draw();
    });

    $("#restock_pageLength").on("change", function () {
        table.page.len(parseInt($(this).val(), 10)).draw();
    });

    const $info = $("#custom_customInfo");
    const $pagination = $("#custom_Pagination");

    function updatePagination() {
        const info = table.page.info();
        const currentPage = info.page + 1;
        const totalPages = info.pages || 1;

        $info.text(
            `Menampilkan ${info.start + 1}–${info.end} dari ${info.recordsDisplay} data (Halaman ${currentPage} dari ${totalPages})`,
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

// =====================================================
// 2) MODAL + FORM + SIDEBAR HITUNG
// =====================================================
$(function () {
    const elModal = document.getElementById("modalCreateRestockReturn");
    const modalCreate = elModal
        ? new Modal(elModal, { backdrop: "static", closable: false })
        : null;

    const $form = $("#formCreateRestockReturn");
    const $btnSubmit = $("#btn-submit-create");

    // -----------------------------
    // Helpers format angka
    // -----------------------------

    // Ia mengubah string seperti "Rp. 10.000" menjadi angka murni 10000 agar bisa dipakai berhitung.
    function toNumber(v) {
        if (!v) return 0;
        if (typeof v === "number") return v;

        // jika string ada Rp
        const s = String(v);
        if (s.includes("Rp")) {
            return parseFloat(s.replace(/[^\d]/g, "")) || 0;
        }
        return parseFloat(v) || 0;
    }

    // Kebalikan dari toNumber. Ia mengubah angka murni menjadi format cantik "Rp. 10.000" menggunakan Regular Expression.
    function rupiah(n) {
        const x = Math.round(toNumber(n));
        return "Rp. " + x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Setiap kali user mengetik di field rupiah, script otomatis menambahkan "Rp." dan titik ribuan secara real-time.
    $(document).on("input", ".input-rupiah", function () {
        let val = $(this).val();
        $(this).val(rupiah(val));
        // setiap rupiah berubah => recalc sidebar
        recalcSummary();
    });

    // Menjaga agar kursor user tidak masuk ke dalam tulisan "Rp. ", jadi user dipaksa mengetik setelah simbol mata uang.
    $(document).on("click focus keyup", ".input-rupiah", function () {
        if (this.selectionStart < 4) this.setSelectionRange(4, 4);
    });

    function resetErrors() {
        $form.find("[data-error]").text("");
    }

    // -----------------------------
    // Sidebar Summary
    // -----------------------------
    let activeTab = "obat";

    function getActiveSubtotal() {
        if (activeTab === "obat")
            return toNumber($("#harga_total_awal_obat").val());
        return toNumber($("#harga_total_awal_bhp").val());
    }

    // mengambil subtotal (dari obat atau BHP)
    // menghitung nominal pajak (%)
    // menambahkan biaya lainnya
    // lalu menampilkan Total Akhir di sidebar secara otomatis.
    function recalcSummary() {
        const subtotal = getActiveSubtotal();
        const pajakPct = parseFloat($("#sum-pajak").val() || "0") || 0;
        const biayaLainnya = toNumber($("#sum-biaya-lainnya").val());

        const pajakNominal = (subtotal * pajakPct) / 100;
        const total = subtotal + pajakNominal + biayaLainnya;

        $("#sum-subtotal").text(rupiah(subtotal));
        $("#sum-total").text(rupiah(total));
    }

    $("#sum-pajak").on("input", recalcSummary);
    $("#sum-biaya-lainnya").on("input", recalcSummary);

    // -----------------------------
    // Kalkulasi total awal OBAT
    // -----------------------------
    function calcTotalObat() {
        const hargaSatuanObatBaru =
            $("#harga_satuan_obat_baru").val() || "Rp. 0";
        $("#harga_total_awal_obat").val(hargaSatuanObatBaru);
        recalcSummary();
    }
    $("#harga_satuan_obat_baru").on("input keyup", calcTotalObat);

    // -----------------------------
    // Kalkulasi total awal BHP
    // -----------------------------
    const jumlahBhp = document.getElementById("jumlah_bhp");
    const hargaSatuanBhp = document.getElementById("harga_satuan_bhp");
    const hargaTotalAwalBhp = document.getElementById("harga_total_awal_bhp");

    // Khusus untuk tab BHP.
    // Ia mengalikan Jumlah BHP * Harga Satuan dan memasukkan hasilnya ke field Total Awal BHP.
    function recalcBhpTotal() {
        const qty = Number(jumlahBhp?.value || 0);
        const price = toNumber(hargaSatuanBhp?.value);
        const total = qty * price;
        if (hargaTotalAwalBhp) hargaTotalAwalBhp.value = rupiah(total);
        recalcSummary();
    }
    jumlahBhp?.addEventListener("input", recalcBhpTotal);
    hargaSatuanBhp?.addEventListener("input", recalcBhpTotal);

    // -----------------------------
    // Tabs logic (SINGLE SOURCE OF TRUTH)
    // -----------------------------
    // Mengubah teks tombol utama di bagian bawah/header mengikuti tab yang sedang dibuka
    // (contoh: "Tambah Rincian Obat" berubah jadi "Tambah Rincian BHP").
    function updateTambahRincianButton() {
        const $btn = $("#btn-tambah-rincian");
        if (activeTab === "obat") {
            $btn.html(
                `Tambah Rincian Obat <i class="fa-solid fa-angle-right text-[10px]"></i>`,
            );
        } else {
            $btn.html(
                `Tambah Rincian BHP <i class="fa-solid fa-angle-right text-[10px]"></i>`,
            );
        }
    }

    // Mengatur tampilan.
    // Jika klik tab "Obat", maka panel BHP disembunyikan dan panel Obat ditampilkan.
    // Script ini juga mengganti warna border tab agar user tahu mana yang aktif.
    function setActiveTab(tab) {
        activeTab = tab;

        const tabs = [
            {
                btn: document.getElementById("tab-obat"),
                panel: document.getElementById("panel-obat"),
                key: "obat",
            },
            {
                btn: document.getElementById("tab-bhp"),
                panel: document.getElementById("panel-bhp"),
                key: "bhp",
            },
        ];

        tabs.forEach((t) => {
            const isActive = t.key === tab;

            t.btn?.classList.toggle("border-pink-500", isActive);
            t.btn?.classList.toggle("text-gray-900", isActive);
            t.btn?.classList.toggle("dark:text-white", isActive);

            t.btn?.classList.toggle("border-transparent", !isActive);
            t.btn?.classList.toggle("text-gray-500", !isActive);
            t.btn?.classList.toggle("dark:text-gray-400", !isActive);

            if (t.panel) t.panel.classList.toggle("hidden", !isActive);
        });

        updateTambahRincianButton();
        recalcSummary();
    }

    $("#tab-obat").on("click", () => setActiveTab("obat"));
    $("#tab-bhp").on("click", () => setActiveTab("bhp"));

    // tombol global trigger tombol panel lama
    $("#btn-tambah-rincian").on("click", function () {
        if (activeTab === "obat")
            $("#btn-tambah-rincian-obat").trigger("click");
        else $("#btn-tambah-rincian-bhp").trigger("click");
    });

    // hide tombol bawah panel biar gak dobel (kamu minta tombol di header)
    function hideDuplicatePanelButtons() {
        $("#btn-tambah-rincian-obat").closest("div").addClass("hidden");
        $("#btn-tambah-rincian-bhp").closest("div").addClass("hidden");
    }

    // -----------------------------
    // Jenis transaksi toggle stok return
    // -----------------------------

    // Jika user memilih jenis transaksi "Return", maka field "Total Stok Sekarang" akan muncul.
    // Jika pilih "Restock", field stok tersebut akan disembunyikan karena dianggap menambah barang baru.
    function toggleTransactionMode() {
        const jenisTransaksi = $("#jenis_transaksi").val() || "";
        const isReturn = jenisTransaksi.toLowerCase().includes("return");
        const $wrapperStok = $("#total_stok_item").closest("div");
        const $labelJumlah = $("label[for='jumlah_obat']");
        const $selectEDReturn = $("#expired_date_obat_return");
        const $selectEDRestock = $("#expired_date_obat_restock");

        if (isReturn) {
            $wrapperStok.removeClass("hidden").addClass('md:col-span-2');
            $selectEDReturn.removeClass("hidden");
            $selectEDRestock.addClass("hidden");
            $("#total_stok_item")
                .prop("readonly", true)
                .prop("disabled", false);
            $labelJumlah.text("Jumlah Return *");
        } else {
            $wrapperStok.addClass("hidden");
            $selectEDRestock.removeClass("hidden");
            $("#total_stok_item").val("");
            $labelJumlah.text("Jumlah Obat / Restock *");
        }
    }

    $("#jenis_transaksi").on("change", function () {
        toggleTransactionMode();
        const obatId = $("#obat_id").val();
        if (obatId) fillObatMeta(obatId);
    });

    // -----------------------------
    // Load Meta (jenis transaksi, satuan, default depot)
    // -----------------------------
    let DEFAULT_DEPOT_ID = null;

    function loadFormMeta(done) {
        $.get("/farmasi/restock-return/form-meta")
            .done(function (meta) {
                DEFAULT_DEPOT_ID = meta.default_depot_id || null;

                const $jtSelect = $("#jenis_transaksi");
                $jtSelect
                    .empty()
                    .append('<option value="">-- Pilih --</option>');
                (meta.jenis_transaksi || []).forEach((item) => {
                    $jtSelect.append(
                        `<option value="${item.value}">${item.label}</option>`,
                    );
                });

                const satuanOpt = [`<option value="">Pilih satuan...</option>`]
                    .concat(
                        (meta.satuan || []).map(
                            (s) => `<option value="${s.id}">${s.nama}</option>`,
                        ),
                    )
                    .join("");
                $("#satuan_obat_id").html(satuanOpt);

                done && done();
            })
            .fail(function (xhr) {
                console.error("Gagal load meta", xhr);
                done && done();
            });
    }

    // -----------------------------
    // TomSelect Obat + Depot
    // -----------------------------
    let obatSelect = null;
    let depotSelect = null;
    let bhpSelect = null;

    // Mengubah dropdown biasa menjadi kolom pencarian yang mengambil data dari server secara live.
    function initObatSelect() {
        if (obatSelect) return;

        obatSelect = new TomSelect("#obat_id", {
            valueField: "id",
            labelField: "nama_obat",
            searchField: "nama_obat",
            maxItems: 1,
            preload: true,
            load: function (query, callback) {
                $.get("/testing-tom-select/data-obat", { q: query || "" })
                    .done((res) => callback(res))
                    .fail(() => callback());
            },
            onChange: function (value) {
                if (!value) {
                    $(
                        "#kategori_obat_id, #satuan_obat_id, #total_stok_item",
                    ).val("");
                    $(
                        "#harga_beli_satuan_obat_lama, #harga_jual_lama_obat, #harga_jual_otc_lama_obat",
                    ).val("");
                    $("#batch_obat, #expired_date_obat").html(
                        `<option value="">Pilih...</option>`,
                    );
                    $("#harga_total_awal_obat").val("Rp. 0");
                    recalcSummary();
                    return;
                }
                fillObatMeta(value);
            },
        });
    }

    // Mengubah dropdown biasa menjadi kolom pencarian yang mengambil data dari server secara live.
    function initDepotSelect() {
        if (depotSelect) return;

        depotSelect = new TomSelect("#depot_id", {
            valueField: "id",
            labelField: "nama_depot",
            searchField: "nama_depot",
            maxItems: 1,
            preload: true,
            load: function (query, callback) {
                const obatId = $("#obat_id").val();
                $.get("/farmasi/restock-return/get-data-depot", {
                    q: query || "",
                    obat_id: obatId,
                })
                    .done((res) => callback(res))
                    .fail(() => callback());
            },
            onChange: function (value) {
                const data = this.options[value];
                if (data) {
                    $("#info-stok-depot").removeClass("hidden");
                    $("#nilai-stok").text(data.stok_obat);
                } else {
                    $("#info-stok-depot").addClass("hidden");
                }
            },
        });
    }

    // Mengubah dropdown biasa menjadi kolom pencarian yang mengambil data dari server secara live.
    function initBhpSelect() {
        if (bhpSelect) return;

        bhpSelect = new TomSelect("#bhp_id", {
            valueField: "id",
            labelField: "nama_barang",
            searchField: "nama_barang",
            maxItems: 1,
            preload: true,
            load: function (query, callback) {
                $.get("/farmasi/restock-return/get-data-depot-bhp", { q: query || "" })
                    .done((res) => callback(res))
                    .fail(() => callback());
            },
            onChange: function (value) {
                if (!value) {
                    $(
                        "#kategori_obat_id, #satuan_obat_id, #total_stok_item",
                    ).val("");
                    $(
                        "#harga_beli_satuan_obat_lama, #harga_jual_lama_obat, #harga_jual_otc_lama_obat",
                    ).val("");
                    $("#batch_obat, #expired_date_obat").html(
                        `<option value="">Pilih...</option>`,
                    );
                    $("#harga_total_awal_obat").val("Rp. 0");
                    recalcSummary();
                    return;
                }
                fillObatMeta(value);
            },
        });
    }

    // Saat user memilih satu obat,
    // fungsi ini langsung menarik data
    // harga beli lama, harga jual, stok, nomor batch, dan tanggal kadaluarsa obat tersebut dari database untuk mengisi form secara otomatis.
    function fillObatMeta(id) {
        if (!id) return;

        const depotId = $("#depot_id").val() || DEFAULT_DEPOT_ID;
        const jenisTransaksi = $("#jenis_transaksi").val() || "";
        const isReturn = jenisTransaksi.toLowerCase().includes("return");

        $.get(`/farmasi/restock-return/obat/${id}/meta`, { depot_id: depotId })
            .done(function (res) {
                $("#kategori_obat_id").val(res.nama_kategori || "");
                $("#satuan_obat_id")
                    .val(res.nama_satuan || "")
                    .trigger("change");

                $("#harga_beli_satuan_obat_lama").val(
                    rupiah(res.harga_beli_satuan_obat_lama || 0),
                );
                $("#harga_jual_lama_obat").val(
                    rupiah(res.harga_jual_lama || 0),
                );
                $("#harga_jual_otc_lama_obat").val(
                    rupiah(res.harga_jual_otc_obat_lama || 0),
                );

                $("#batch_obat")
                    .empty()
                    .append(`<option value="">Pilih batch...</option>`);
                if (res.batch_lama)
                    $("#batch_obat").append(
                        `<option value="${res.batch_lama}" selected>${res.batch_lama}</option>`,
                    );

                $("#expired_date_obat")
                    .empty()
                    .append(`<option value="">Pilih expired...</option>`);
                if (res.expired_lama)
                    $("#expired_date_obat").append(
                        `<option value="${res.expired_lama}" selected>${res.expired_lama}</option>`,
                    );

                if (isReturn) {
                    const stok = res.stok_sekarang || res.jumlah || 0;
                    $("#total_stok_item").val(stok);
                } else {
                    $("#total_stok_item").val("");
                }

                calcTotalObat();
                recalcSummary();
            })
            .fail((xhr) => console.error("Gagal ambil meta obat", xhr));
    }

    // -----------------------------
    // Supplier TomSelect (create / update detail)
    // -----------------------------
    let supplierSelect = null;
    let supplierJustCreatedId = null;

    function getSupplierDataset() {
        const el = document.getElementById("supplier_id");
        return {
            el,
            urlIndex: el?.dataset.urlIndex,
            urlStore: el?.dataset.urlStore,
            urlUpdate: el?.dataset.urlUpdate,
            urlShowTpl: el?.dataset.urlShow,
        };
    }

    function showSupplierDetailCreate(data) {
        $("#supplier-detail").removeClass("hidden");
        $("#btn-clear-supplier").removeClass("hidden");
        $("#supplier_kontak_person").val(data.kontak_person || "");
        $("#supplier_no_hp").val(data.no_hp || "");
        $("#supplier_email").val(data.email || "");
        $("#supplier_alamat").val(data.alamat || "");
        $("#supplier_keterangan").val(data.keterangan || "");
        $("#supplier-detail input, #supplier-detail textarea")
            .prop("readonly", false)
            .prop("disabled", false);
    }

    function clearSupplierDetailCreate() {
        $("#supplier-detail").addClass("hidden");
        $("#btn-clear-supplier").addClass("hidden");
        $("#supplier-detail input, #supplier-detail textarea")
            .val("")
            .prop("readonly", false)
            .prop("disabled", false);
    }

    function initSupplierSelectCreate() {
        const { el, urlIndex, urlStore, urlShowTpl } = getSupplierDataset();
        if (!el || supplierSelect) return;

        supplierSelect = new TomSelect(el, {
            valueField: "id",
            labelField: "nama_supplier",
            searchField: "nama_supplier",
            preload: true,
            maxOptions: 10,
            create: function (input, callback) {
                $.post(urlStore, { nama_supplier: input })
                    .done(function (res) {
                        supplierJustCreatedId = String(res.id);
                        callback(res);
                        showSupplierDetailCreate(res);
                    })
                    .fail(function () {
                        alert("Gagal menambahkan supplier");
                        callback();
                    });
            },
            load: function (query, callback) {
                $.get(urlIndex, { q: query })
                    .done((res) => callback(res))
                    .fail(() => callback());
            },
            onChange: function (value) {
                if (!value) {
                    supplierJustCreatedId = null;
                    clearSupplierDetailCreate();
                    return;
                }

                if (
                    supplierJustCreatedId &&
                    String(value) === String(supplierJustCreatedId)
                )
                    return;

                const urlShow = urlShowTpl.replace("__ID__", value);
                $.get(urlShow)
                    .done((res) => {
                        supplierJustCreatedId = null;
                        showSupplierDetailCreate(res);
                    })
                    .fail(clearSupplierDetailCreate);
            },
        });

        $("#btn-clear-supplier").on("click", function () {
            supplierSelect.clear(true);
            supplierJustCreatedId = null;
            clearSupplierDetailCreate();
        });
    }

    // -----------------------------
    // Reset form + open/close modal
    // -----------------------------

    // Membersihkan semua isi input
    // mengosongkan pilihan supplier/obat
    // dan mengembalikan tampilan ke kondisi awal (default tab Obat).
    function resetForm() {
        $form[0].reset();
        resetErrors();

        if (supplierSelect) supplierSelect.clear(true);
        supplierJustCreatedId = null;
        clearSupplierDetailCreate();

        // reset totals
        $("#sum-pajak").val("0");
        $("#sum-biaya-lainnya").val("Rp. 0");
        $("#harga_total_awal_obat").val("Rp. 0");
        $("#harga_total_awal_bhp").val("Rp. 0");

        $("#batch_obat, #expired_date_obat").html(
            `<option value="">Pilih...</option>`,
        );
        $(
            "#harga_beli_satuan_obat_lama, #harga_jual_lama_obat, #harga_jual_otc_lama_obat, #total_stok_item",
        ).val("");

        toggleTransactionMode();
        setActiveTab("obat");
        hideDuplicatePanelButtons();
        recalcSummary();
    }

    // Saat tombol buka modal diklik,
    // script memicu resetForm dulu,
    // baru kemudian menjalankan TomSelect (pencarian otomatis) dan memuat meta data dari server.
    $("#btn-open-modal-create").on("click", function () {
        resetForm();
        modalCreate?.show();

        setTimeout(() => {
            initSupplierSelectCreate();
            loadFormMeta(() => initObatSelect());
            loadFormMeta(() => initDepotSelect());
            loadFormMeta(() => initBhpSelect());
        }, 50);
    });

    $("#btn-close-modal-create, #btn-cancel-modal-create").on(
        "click",
        function () {
            modalCreate?.hide();
            resetForm();
        },
    );

    $("#btn-tambah-rincian-obat").on("click", function () {
        // 1. Ambil Data dari Input
        const namaObat =
            document.getElementById("obat_id").options[
                document.getElementById("obat_id").selectedIndex
            ].text;
        const jumlah = document.getElementById("jumlah_obat").value;
        const satuan = document.getElementById("satuan_obat_id").value;
        const hargaBeli = document.getElementById(
            "harga_satuan_obat_baru",
        ).value;
        const totalAwal = document.getElementById(
            "harga_total_awal_obat",
        ).value;
        const expDate = document.getElementById("expired_date_obat").value;
        const jenisTransaksi =
            document.getElementById("jenis_transaksi")?.value || "Restock"; // Contoh ambil dari header

        // Validasi sederhana
        if (!jumlah || jumlah == 0) {
            alert("Jumlah obat harus diisi!");
            return;
        }

        // 2. Buat Template HTML untuk Rincian (Sesuai Gambar Kamu)
        const rincianHTML = `
        <div class="rincian-item bg-white dark:bg-gray-800 p-3 rounded-lg border border-gray-100 dark:border-gray-700 shadow-sm relative mb-3">
            <button type="button" class="btn-hapus-rincian absolute top-2 right-2 text-pink-500 hover:text-pink-700">
                <i class="fa-solid fa-circle-xmark text-lg"></i>
            </button>
            
            <div class="flex justify-between items-start">
                <div>
                    <h4 class="text-blue-500 font-bold text-sm">${namaObat}</h4>
                    <p class="text-xs text-gray-700 dark:text-gray-300 font-medium">${jenisTransaksi}</p>
                    <div class="mt-2">
                        <p class="text-[10px] text-gray-500 italic">Harga beli rata-rata</p>
                        <p class="text-[10px] text-gray-500 italic">Exp. ${expDate}</p>
                    </div>
                </div>
                
                <div class="text-right pt-4">
                    <p class="text-xs text-gray-600 dark:text-gray-400">${jumlah} ${satuan} &nbsp; @ Rp. ${hargaBeli}</p>
                    <p class="text-sm font-bold text-gray-800 dark:text-white">Rp. ${totalAwal}</p>
                    <p class="text-[10px] text-gray-500">@ Rp. ${hargaBeli}</p>
                </div>
            </div>
        </div>
    `;

        // 3. Masukkan ke Container
        $("#container-rincian").append(rincianHTML);

        // 4. Reset Form Input di dalam panel obat
        // Cari semua input & select di dalam panel-obat lalu kosongkan
        $("#panel-obat")
            .find("input, select")
            .each(function () {
                $(this).val("");
            });

        // Khusus untuk yang tipenya angka/number, set ke 0
        $("#jumlah_obat, #harga_total_awal_obat").val(0);

        $("#depot_id").val("").trigger("change");

        // Sembunyikan info stok jika ada
        $("#info-stok-depot").addClass("hidden");
    });

    // Fungsi untuk menghapus rincian jika salah input
    $(document).on("click", ".btn-hapus-rincian", function () {
        $(this).closest(".rincian-item").remove();
    });

    // -----------------------------
    // Submit
    // -----------------------------
    $form.on("submit", function (e) {
        // Mencegah halaman refresh
        e.preventDefault();

        resetErrors();

        const urlTransaksi = $form.data("url");
        const supplierId = $("#supplier_id").val();
        const depotId = $("#depot_id").val() || DEFAULT_DEPOT_ID;
        const { urlUpdate } = getSupplierDataset();

        $btnSubmit.prop("disabled", true).text("Menyimpan...");

        const items = [];
        const tabAktif = activeTab;

        if (tabAktif === "obat") {
            items.push({
                type: "obat",
                obat_id: $("#obat_id").val(),
                batch: $("#batch_obat").val() || null,
                expired_date: $("#expired_date_obat").val() || null,
                jumlah: parseInt($("#jumlah_obat").val() || "0", 10),
                satuan_id: $("#satuan_obat_id").val() || null,
                harga_beli: toNumber($("#harga_satuan_obat_baru").val()),
                depot_id: depotId,
                keterangan: $("#keterangan_item_obat").val() || null,
            });
        } else {
            // kalau BHP sudah siap endpointnya, tinggal isi mapping di sini
            items.push({
                type: "bhp",
                bhp_id: $("#bhp_id").val(),
                batch: $("#batch_bhp").val() || null,
                expired_date: $("#expired_date_bhp").val() || null,
                jumlah: parseInt($("#jumlah_bhp").val() || "0", 10),
                harga_beli: toNumber($("#harga_satuan_bhp").val()),
                depot_id: $("#depot_id_bhp").val() || null,
                keterangan: $("#keterangan_bhp").val() || null,
            });
        }

        // Mengumpulkan semua data (Header + Item Obat/BHP + Summary) ke dalam satu objek besar bernama payload.
        const payload = {
            tanggal_transaksi: $form.find('[name="tanggal_transaksi"]').val(),
            jenis_transaksi: $form.find('[name="jenis_transaksi"]').val(),
            supplier_id: supplierId || null,
            nomor_faktur: $form.find('[name="nomor_faktur"]').val() || null,
            keterangan: $form.find('[name="keterangan"]').val() || null,

            // optional: kalau mau kirim summary:
            pajak_persen: parseFloat($("#sum-pajak").val() || "0") || 0,
            biaya_lainnya: toNumber($("#sum-biaya-lainnya").val()),

            items,
        };

        // Mengirimkan data tersebut ke backend menggunakan AJAX POST dalam format JSON.
        const doCreateTransaksi = function () {
            $.ajax({
                url: urlTransaksi,
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify(payload),
            })
                .done(function (res) {
                    modalCreate?.hide();
                    resetForm();

                    if ($.fn.DataTable.isDataTable("#table-restock-return")) {
                        $("#table-restock-return")
                            .DataTable()
                            .ajax.reload(null, false);
                    }
                    if (res?.redirect_url)
                        window.location.href = res.redirect_url;
                })
                // Jika server menolak (error 422/validasi gagal),
                // script akan mencari elemen [data-error] dan menampilkan pesan kesalahan tepat di bawah input yang bermasalah
                // (misal: "Jumlah tidak boleh kosong").
                .fail(function (xhr) {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON?.errors || {};
                        Object.keys(errors).forEach((key) => {
                            $form
                                .find(`[data-error="${key}"]`)
                                .text(errors[key][0] ?? "Tidak valid");
                        });
                    } else {
                        alert("Terjadi kesalahan saat menyimpan transaksi.");
                        console.error(xhr);
                    }
                })
                .always(function () {
                    $btnSubmit.prop("disabled", false).text("Simpan Transaksi");
                });
        };

        // Jika user mengisi detail supplier baru
        // script akan menyimpan data supplier dulu ($.post(urlUpdate)), baru kemudian menyimpan transaksi utamanya.
        if (supplierId) {
            const payloadSupplier = {
                id: supplierId,
                kontak_person: $("#supplier_kontak_person").val() || null,
                no_hp: $("#supplier_no_hp").val() || null,
                email: $("#supplier_email").val() || null,
                alamat: $("#supplier_alamat").val() || null,
                keterangan: $("#supplier_keterangan").val() || null,
            };

            $.post(urlUpdate, payloadSupplier)
                .done(() => doCreateTransaksi())
                .fail(() => doCreateTransaksi());
        } else {
            doCreateTransaksi();
        }
    });

    // default
    setActiveTab("obat");
    hideDuplicatePanelButtons();
    recalcSummary();
});

$(function () {
    const $depotContainer = $("#depot-container-restock");
    if (!$depotContainer.length) return;

    // clone template row pertama
    const $depotTemplate = $depotContainer
        .find(".depot-row")
        .first()
        .clone(false);

    function initNamaDepotSelect($row) {
        const el = $row.find(".select-nama-depot")[0];
        const btnClear = $row.find(".btn-clear-depot")[0];
        if (!el) return;

        const urlIndex = el.dataset.urlIndex;
        const urlStore = el.dataset.urlStore;
        const urlDelete = el.dataset.urlDelete;

        if (el.tomselect) el.tomselect.destroy();

        const ts = new TomSelect(el, {
            valueField: "id",
            labelField: "nama_depot",
            searchField: "nama_depot",
            preload: true,
            maxItems: 1,
            placeholder: "Pilih / ketik nama depot",

            load: function (query, callback) {
                axios
                    .get(urlIndex, { params: { q: query } })
                    .then((res) => callback(res.data || []))
                    .catch(() => callback([]));
            },

            create: function (input, callback) {
                axios
                    .post(urlStore, { nama_depot: input })
                    .then((res) => callback(res.data))
                    .catch(() => callback());
            },

            onChange: function (value) {
                if (!btnClear) return;
                btnClear.classList.toggle("hidden", !value);
            },
        });

        if (btnClear) {
            btnClear.onclick = function () {
                const value = ts.getValue();
                if (!value) return btnClear.classList.add("hidden");

                // kalau kamu mau delete dari DB, aktifkan axios delete di sini.
                // kalau gak mau delete DB, cukup clear doang.
                ts.clear();
                btnClear.classList.add("hidden");
            };
        }
    }

    function initTipeDepotSelect($row) {
        const el = $row.find(".select-tipe-depot")[0];
        const btnClear = $row.find(".btn-clear-tipe-depot")[0];
        if (!el) return;

        const urlIndex = el.dataset.urlIndex;
        const urlStore = el.dataset.urlStore;
        const urlDelete = el.dataset.urlDelete;

        if (el.tomselect) el.tomselect.destroy();

        const ts = new TomSelect(el, {
            valueField: "id",
            labelField: "nama_tipe_depot",
            searchField: "nama_tipe_depot",
            preload: true,
            maxItems: 1,
            placeholder: "Pilih / ketik tipe depot",

            load: function (query, callback) {
                axios
                    .get(urlIndex, { params: { q: query } })
                    .then((res) => callback(res.data || []))
                    .catch(() => callback([]));
            },

            create: function (input, callback) {
                axios
                    .post(urlStore, { nama_tipe_depot: input })
                    .then((res) => callback(res.data))
                    .catch(() => callback());
            },

            onChange: function (value) {
                if (!btnClear) return;
                btnClear.classList.toggle("hidden", !value);
            },
        });

        if (btnClear) {
            btnClear.onclick = function () {
                const value = ts.getValue();
                if (!value) return btnClear.classList.add("hidden");

                ts.clear();
                btnClear.classList.add("hidden");
            };
        }
    }

    // init row pertama
    const $firstRow = $depotContainer.find(".depot-row").first();
    initNamaDepotSelect($firstRow);
    initTipeDepotSelect($firstRow);

    // tambah depot
    $("#btn-add-depot-restock").on("click", function () {
        const $newRow = $depotTemplate.clone(false);

        // reset value
        $newRow.find(".select-nama-depot").val("");
        $newRow.find(".btn-clear-depot").addClass("hidden");
        $newRow.find(".select-tipe-depot").val("");
        $newRow.find(".btn-clear-tipe-depot").addClass("hidden");
        $newRow.find(".input-stok-depot").val(0);

        $depotContainer.append($newRow);

        initNamaDepotSelect($newRow);
        initTipeDepotSelect($newRow);
    });

    // hapus row
    $(document).on(
        "click",
        "#depot-container-restock .btn-remove-depot",
        function () {
            const $rows = $depotContainer.find(".depot-row");
            if ($rows.length <= 1) {
                // kalau cuma satu, reset aja (biar gak kosong total)
                const $row = $rows.first();

                // clear tomselect
                const depotEl = $row.find(".select-nama-depot")[0];
                if (depotEl?.tomselect) depotEl.tomselect.clear();

                const tipeEl = $row.find(".select-tipe-depot")[0];
                if (tipeEl?.tomselect) tipeEl.tomselect.clear();

                $row.find(".btn-clear-depot").addClass("hidden");
                $row.find(".btn-clear-tipe-depot").addClass("hidden");
                $row.find(".input-stok-depot").val(0);
                return;
            }

            $(this).closest(".depot-row").remove();
        },
    );
});

$(function () {
    $("#togglePurchaseOrder").on("change", function () {
        const isTrue = $(this).is(":checked");
        const $label = $("#labelPurchaseOrder");

        if (isTrue) {
            $label.addClass("text-blue-600").removeClass("text-gray-400");
        } else {
            $label.addClass("text-gray-400").removeClass("text-blue-600");
        }
    });
});
