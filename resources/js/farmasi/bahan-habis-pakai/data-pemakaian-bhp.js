import $ from "jquery";

$(function () {
    // ==========================================
    // 1. DEFINISI ELEMENT & VARIABLE
    // ==========================================
    const urlGetDataBhp = "/farmasi/pemakaian-bhp/get-data-bhp";
    const urlGetDataDepot = "/farmasi/pemakaian-bhp/get-data-depot";

    const $form = $("#form-pemakaian-bhp");
    const $btnSimpan = $("#btn-simpan");

    // Element Card Info
    const $cardNama = $("#info-nama-barang");
    const $cardStok = $("#info-stok");
    const $cardSatuan = $("#info-satuan");
    const $cardHarga = $("#info-harga");
    const $cardKode = $("#info-kode");

    // Penampung data barang yang terpilih
    let selectedBhpData = null;

    function resetCardInfo() {
        $cardNama.text("Pilih barang & depot terlebih dahulu");
        $cardStok.text("0").removeClass("text-red-500").addClass("text-white");
        $cardSatuan.text("Satuan");
        $cardHarga.text("Rp 0");
        $cardKode.text("-");
        $("#satuan-badge").text("PCS");
    }

    // ==========================================
    // 2. FUNGSI RESET SAAT TAB DIKLIK
    // ==========================================
    $("#data-pemakaian-bhp-tab").on("click", function () {
        if ($form.length > 0) $form[0].reset();
        if (selectBHP) {
            selectBHP.clear();
            selectBHP.refreshOptions(false);
        }
        if (selectDepot) {
            selectDepot.clear();
            selectDepot.clearOptions();
        }
        selectedBhpData = null;
        resetCardInfo();
        $(".error-msg").remove();
        $("input, select, textarea").removeClass("border-red-500");
    });

    // ==========================================
    // 3. INISIALISASI TOMSELECT BARANG (BHP)
    // ==========================================
    const selectBHP = new TomSelect("#select-bhp", {
        valueField: "id",
        labelField: "nama_barang",
        searchField: ["nama_barang", "kode"],
        placeholder: "Cari nama barang atau kode...",
        preload: true,
        load: function (query, callback) {
            $.ajax({
                url: urlGetDataBhp,
                type: "GET",
                data: { q: query },
                success: function (response) {
                    let items = response.data ? response.data : response;
                    callback(items.length === 0 ? [] : items);
                },
                error: function () {
                    callback();
                },
            });
        },
        render: {
            no_results: function (data, escape) {
                return `<div class="no-results py-3 px-4 text-sm text-slate-500">
                            <i class="fa-solid fa-magnifying-glass mr-2"></i> Maaf, barang "${escape(data.query)}" tidak ditemukan.
                        </div>`;
            },
            no_data: function (data, escape) {
                return `<div class="no-results py-3 px-4 text-sm text-amber-600 bg-amber-50">
                            <i class="fa-solid fa-box-open mr-2"></i> Belum ada data barang tersedia.
                        </div>`;
            },
            loading: function (data, escape) {
                return `<div class="py-3 px-4 text-sm text-blue-600">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Mencari barang...
                        </div>`;
            },
            option: function (item, escape) {
                return `<div class="py-2 px-3 border-b border-slate-50">
                    <div class="font-semibold text-slate-700">${escape(item.nama_barang)}</div>
                    <div class="text-xs text-slate-500">Kode: ${escape(item.kode)} | Stok Total: ${item.stok_barang}</div>
                </div>`;
            },
        },
        onChange: function (id) {
            const item = this.options[id];
            if (!id || !item) {
                selectedBhpData = null;
                selectDepot.clear();
                selectDepot.clearOptions();
                resetCardInfo();
                return;
            }

            selectedBhpData = item;

            // RESET & RE-LOAD DEPOT BERDASARKAN BHP_ID
            selectDepot.clear();
            selectDepot.clearOptions();
            selectDepot.load(""); // Memicu fungsi load pada selectDepot

            resetCardInfo();
        },
    });

    // ==========================================
    // 4. INISIALISASI TOMSELECT DEPOT
    // ==========================================
    const selectDepot = new TomSelect("#select-depot", {
        valueField: "id",
        labelField: "nama_depot",
        searchField: ["nama_depot"],
        placeholder: "Pilih depot...",
        load: function (query, callback) {
            // Hanya load jika barang sudah dipilih
            if (!selectedBhpData) return callback();

            $.ajax({
                url: urlGetDataDepot,
                type: "GET",
                data: {
                    q: query,
                    bhp_id: selectedBhpData.id, // Kirim ID barang ke controller
                },
                success: function (response) {
                    let items = response.data ? response.data : response;
                    callback(items);
                },
                error: function () {
                    callback();
                },
            });
        },
        render: {
            no_results: function (data, escape) {
                return `<div class="no-results py-3 px-4 text-sm text-slate-500">Depot tidak ditemukan.</div>`;
            },
            loading: function (data, escape) {
                return `<div class="py-3 px-4 text-sm text-blue-600">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Mengambil stok...
                        </div>`;
            },
            option: function (item, escape) {
                return `<div class="py-2 px-3 border-b border-slate-50">
                    <div class="font-semibold text-slate-700">${escape(item.nama_depot)}</div>
                    <div class="text-xs text-blue-600 font-medium">Stok di sini: ${item.stok_barang}</div>
                </div>`;
            },
        },
        onChange: function (depotId) {
            const itemDepot = this.options[depotId];

            if (depotId && itemDepot && selectedBhpData) {
                // Update tampilan section kanan
                $cardNama.text(selectedBhpData.nama_barang);
                $cardKode.text(selectedBhpData.kode);

                let namaSatuan = selectedBhpData.satuan_b_h_p
                    ? selectedBhpData.satuan_b_h_p.nama_satuan_obat
                    : "PCS";
                $cardSatuan.text(namaSatuan);
                $("#satuan-badge").text(namaSatuan);

                let formatRupiah = new Intl.NumberFormat("id-ID", {
                    style: "currency",
                    currency: "IDR",
                    minimumFractionDigits: 0,
                }).format(selectedBhpData.harga_jual_umum_bhp);
                $cardHarga.text(formatRupiah);

                let stokSpesifik = parseInt(itemDepot.stok_barang || 0);
                $cardStok.text(stokSpesifik);

                if (stokSpesifik <= 0) {
                    $cardStok
                        .addClass("text-red-500")
                        .removeClass("text-white");
                    Swal.fire(
                        "Peringatan",
                        "Stok habis di " + itemDepot.nama_depot,
                        "warning",
                    );
                } else {
                    $cardStok
                        .removeClass("text-red-500")
                        .addClass("text-white");
                }
            } else {
                resetCardInfo();
            }
        },
    });

    // ==========================================
    // 5. EVENT SUBMIT FORM
    // ==========================================
    $form.on("submit", function (e) {
        e.preventDefault();
        const url = $(this).data("url");
        const formData = $(this).serialize();

        if (!selectBHP.getValue() || !selectDepot.getValue()) {
            Swal.fire(
                "Peringatan",
                "Pilih Barang dan Depot terlebih dahulu!",
                "warning",
            );
            return;
        }

        Swal.fire({
            title: "Apakah Anda Sudah Yakin?",
            text: "Pastikan data pemakaian dan depot sudah sesuai.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Ya, Saya Yakin",
            cancelButtonText: "Tidak",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    beforeSend: function () {
                        $btnSimpan
                            .prop("disabled", true)
                            .html(
                                '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Menyimpan...',
                            );
                        $(".error-msg").remove();
                        $("input, select, textarea").removeClass(
                            "border-red-500",
                        );
                    },
                    success: function (response) {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil",
                            text: response.pesan || "Data Berhasil Disimpan",
                            timer: 2500,
                            showConfirmButton: false,
                        });
                        $form[0].reset();
                        selectBHP.clear();
                        if (selectDepot) {
                            selectDepot.clear();
                            selectDepot.clearOptions();
                        }
                        selectedBhpData = null;
                        resetCardInfo();
                        $btnSimpan
                            .prop("disabled", false)
                            .text("Simpan Pemakaian");
                    },
                    error: function (xhr) {
                        $btnSimpan
                            .prop("disabled", false)
                            .text("Simpan Pemakaian");
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            $.each(errors, function (key, messages) {
                                const input = $(`[name="${key}"]`);
                                input.addClass("border-red-500");

                                // CEK STRUKTUR: Jika input dibungkus oleh flex (khusus jumlah_pemakaian)
                                const inputGroup = input.closest(".flex");

                                if (inputGroup.length > 0) {
                                    inputGroup.after(
                                        `<small class="error-msg text-red-500 text-sm mt-1 block">${messages[0]}</small>`,
                                    );
                                } else {
                                    input.after(
                                        `<small class="error-msg text-red-500 text-sm mt-1 block">${messages[0]}</small>`,
                                    );
                                }
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Gagal Menyimpan",
                                text: xhr.responseJSON.message ||"Terjadi kesalahan pada sistem.",
                            });
                        }
                    },
                });
            }
        });
    });
});
