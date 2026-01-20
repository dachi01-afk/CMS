import $ from "jquery";

$(function () {
    // ==========================================
    // 1. DEFINISI ELEMENT & VARIABLE
    // ==========================================
    const urlGetData = "/farmasi/pemakaian-bhp/get-data-bhp";

    // REVISI: Sesuaikan ID dengan yang ada di file Blade
    const $form = $("#form-pemakaian-bhp");
    const $btnSimpan = $("#btn-simpan");
    const $containerRiwayat = $("#data-pemakaian-bhp");

    // Element Card Info
    const $cardNama = $("#info-nama-barang");
    const $cardStok = $("#info-stok");
    const $cardSatuan = $("#info-satuan");
    const $cardHarga = $("#info-harga");
    const $cardKode = $("#info-kode");

    function resetCardInfo() {
        $cardNama.text("Pilih barang terlebih dahulu");
        $cardStok.text("0").removeClass("text-red-500").addClass("text-white");
        $cardSatuan.text("Satuan");
        $cardHarga.text("Rp 0");
        $cardKode.text("-");
        $("#satuan-badge").text("PCS");
    }

    // ==========================================
    // 2. FUNGSI RENDER / RESET SAAT TAB DIKLIK
    // ==========================================
    // Fungsi ini dijalankan setiap kali menu "Pemakaian Bahan Habis Pakai" diklik
    $("#data-pemakaian-bhp-tab").on("click", function () {
        // Reset Form HTML
        if ($form.length > 0) $form[0].reset();

        // Reset TomSelect
        if (selectBHP) {
            selectBHP.clear();
            selectBHP.refreshOptions(false);
        }

        // Reset Tampilan Card & Error
        resetCardInfo();
        $(".error-msg").remove();
        $("input, select, textarea").removeClass("border-red-500");
    });

    // ==========================================
    // 3. INISIALISASI TOMSELECT
    // ==========================================
    const selectBHP = new TomSelect("#select-bhp", {
        valueField: "id",
        labelField: "nama_barang",
        searchField: ["nama_barang", "kode"],
        placeholder: "Cari nama barang atau kode...",
        preload: true,
        load: function (query, callback) {
            $.ajax({
                url: urlGetData,
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
                return `<div class="no-results py-3 px-4 text-sm text-slate-500">Maaf, barang "${escape(data.query)}" tidak ditemukan.</div>`;
            },
            option: function (item, escape) {
                return `<div class="py-2 px-3 border-b border-slate-50">
                    <div class="font-semibold text-slate-700">${escape(item.nama_barang)}</div>
                    <div class="text-xs text-slate-500">Kode: ${escape(item.kode)} | Stok: ${item.stok_barang}</div>
                </div>`;
            },
        },
        onChange: function (id) {
            const item = this.options[id];
            if (!id || !item) {
                resetCardInfo();
                return;
            }

            // Update Card Info
            $cardNama.text(item.nama_barang);
            $cardStok.text(item.stok_barang);
            $cardKode.text(item.kode);

            // Update Satuan
            let namaSatuan = item.satuan ? item.satuan.nama_satuan : "PCS";
            $cardSatuan.text(namaSatuan);
            $("#satuan-badge").text(namaSatuan);

            // Format Harga
            let formatRupiah = new Intl.NumberFormat("id-ID", {
                style: "currency",
                currency: "IDR",
                minimumFractionDigits: 0,
            }).format(item.harga_jual_umum_bhp);
            $cardHarga.text(formatRupiah);

            // Cek Stok
            if (parseInt(item.stok_barang) <= 0) {
                $cardStok.addClass("text-red-500").removeClass("text-white");
                Swal.fire("Peringatan", "Stok barang ini habis!", "warning");
            } else {
                $cardStok.removeClass("text-red-500").addClass("text-white");
            }
        },
    });

    // ==========================================
    // 4. EVENT SUBMIT FORM
    // ==========================================
    $form.on("submit", function (e) {
        e.preventDefault();
        const url = $(this).data("url");
        const formData = $(this).serialize();

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
                $("input, select, textarea").removeClass("border-red-500");
            },
            success: function (response) {
                Swal.fire({
                    icon: "success",
                    title: "Berhasil",
                    text:
                        response.message ||
                        "Data pemakaian BHP berhasil disimpan.",
                    timer: 2000,
                    showConfirmButton: false,
                });

                $form[0].reset();
                selectBHP.clear();
                resetCardInfo();
                $btnSimpan.prop("disabled", false).text("Simpan Pemakaian");
            },
            error: function (xhr) {
                $btnSimpan.prop("disabled", false).text("Simpan Pemakaian");

                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function (key, messages) {
                        const input = $(`[name="${key}"]`);
                        input.addClass("border-red-500");
                        input.after(
                            `<small class="error-msg text-red-500 text-xs mt-1 block">${messages[0]}</small>`,
                        );
                    });
                } else {
                    Swal.fire(
                        "Error",
                        xhr.responseJSON.message || "Terjadi kesalahan sistem",
                        "error",
                    );
                }
            },
        });
    });
});
