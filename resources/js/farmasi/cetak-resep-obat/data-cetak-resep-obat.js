import $ from "jquery";

$(function () {
    // ==========================================
    // TOMSELECT - PASIEN (AJAX search)
    // ==========================================
    const $selectPasien = $("#selectPasien");
    const pasienSearchUrl =
        $selectPasien.data("url") ||
        "/farmasi/cetak-resep-obat/search-data-pasien";

    const tsPasien = new TomSelect("#selectPasien", {
        create: false,
        placeholder: "Ketik nama / No RM / NIK...",
        valueField: "id",
        labelField: "text",
        searchField: ["text"],
        preload: false,
        minChars: 2,
        loadThrottle: 300,

        // optional, biar bisa clear pilihan
        plugins: ["clear_button"],

        render: {
            option: function (item, escape) {
                return `
                <div class="py-1">
                    <div class="font-semibold">${escape(item.text ?? "")}</div>
                    ${
                        item.sub
                            ? `<div class="text-xs text-gray-500">${escape(
                                  item.sub
                              )}</div>`
                            : ``
                    }
                </div>
            `;
            },
            item: function (item, escape) {
                return `<div>${escape(item.text ?? "")}</div>`;
            },
        },

        load: function (query, callback) {
            if (!query || query.length < 2) return callback();

            $.ajax({
                url: pasienSearchUrl,
                method: "GET",
                dataType: "json",
                data: { q: query },
                success: function (res) {
                    // format backend kita: { success: true, data: [...] }
                    const items = Array.isArray(res?.data) ? res.data : [];
                    callback(items);
                },
                error: function () {
                    callback();
                },
            });
        },
    });

    // ==========================================
    // Saat pasien dipilih -> isi field form
    // Backend item: umur (string "xx th"), alamat, dll.
    // ==========================================
    tsPasien.on("change", function () {
        const val = tsPasien.getValue();

        // kalau di-clear
        if (!val) {
            $("#nama_pasien").val(""); // ✅ kosongkan
            if ($("#umur").length) $("#umur").val("");
            if ($("#alamat").length) $("#alamat").val("");
            if ($("#beratBadan").length) $("#beratBadan").val("");
            return;
        }

        const item = tsPasien.options[val];
        if (!item) return;

        // ✅ ini yang paling penting: kirim nama pasien ke print-preview
        $("#nama_pasien").val(item.text ?? "");

        if ($("#umur").length) $("#umur").val(item.umur ?? "");
        if ($("#alamat").length) $("#alamat").val(item.alamat ?? "");
        if ($("#beratBadan").length)
            $("#beratBadan").val(item.berat_badan ?? "");
    });

    // ==========================================
    // TOMSELECT - DOKTER (AJAX search)
    // ==========================================
    const $selectDokter = $("#selectDokter");
    const dokterSearchUrl =
        $selectDokter.data("url") ||
        "/farmasi/cetak-resep-obat/search-data-dokter";

    const tsDokter = new TomSelect("#selectDokter", {
        create: false,
        placeholder: "Ketik nama dokter / SIP / poli...",
        valueField: "id",
        labelField: "text",
        searchField: ["text"],
        preload: false,
        minChars: 2,
        loadThrottle: 300,

        plugins: ["clear_button"],

        render: {
            option: function (item, escape) {
                return `
                <div class="py-1">
                    <div class="font-semibold">${escape(item.text ?? "")}</div>
                    ${
                        item.sub
                            ? `<div class="text-xs text-gray-500">${escape(
                                  item.sub
                              )}</div>`
                            : ``
                    }
                </div>
            `;
            },
            item: function (item, escape) {
                return `<div>${escape(item.text ?? "")}</div>`;
            },
        },

        load: function (query, callback) {
            if (!query || query.length < 2) return callback();

            $.ajax({
                url: dokterSearchUrl,
                method: "GET",
                dataType: "json",
                data: { q: query },
                success: function (res) {
                    const items = Array.isArray(res?.data) ? res.data : [];
                    callback(items);
                },
                error: function () {
                    callback();
                },
            });
        },
    });

    // ==========================================
    // OPTIONAL: Saat dokter dipilih
    // (misalnya mau simpan poli / SIP / spesialisasi)
    // ==========================================
    tsDokter.on("change", function () {
        const val = tsDokter.getValue();

        if (!val) {
            $("#nama_dokter").val("");
            $("#nama_poli").val("");
            return;
        }

        const item = tsDokter.options[val];
        if (!item) return;

        $("#nama_dokter").val(item.text ?? "");
        $("#nama_poli").val(item.poli ?? item.sub ?? ""); // tergantung backend kamu kirim apa
    });

    const $obatContainer = $("#obatContainer");
    const $tpl = $("#tplObatRow");
    const $btnTambah = $("#btnTambahObat");

    // =========================
    // Init TomSelect untuk 1 select obat
    // =========================
    function initTomSelectObat(selectEl) {
        const $el = $(selectEl);
        const url = $el.data("url") || "/farmasi/obat/search";

        // hindari double init
        if (selectEl.tomselect) {
            selectEl.tomselect.destroy();
        }

        const ts = new TomSelect(selectEl, {
            create: false,
            placeholder: "Ketik nama / kode obat...",
            valueField: "id",
            labelField: "text",
            searchField: ["text"],
            preload: false,
            minChars: 2,
            loadThrottle: 300,
            plugins: ["clear_button"],

            render: {
                option: function (item, escape) {
                    return `
                        <div class="py-1">
                            <div class="font-semibold">${escape(
                                item.text ?? ""
                            )}</div>
                            ${
                                item.sub
                                    ? `<div class="text-xs text-gray-500">${escape(
                                          item.sub
                                      )}</div>`
                                    : ``
                            }
                        </div>
                    `;
                },
                item: function (item, escape) {
                    return `<div>${escape(item.text ?? "")}</div>`;
                },
            },

            load: function (query, callback) {
                if (!query || query.length < 2) return callback();

                $.ajax({
                    url: url,
                    method: "GET",
                    dataType: "json",
                    data: { q: query },
                    success: function (res) {
                        const items = Array.isArray(res?.data) ? res.data : [];
                        callback(items);
                    },
                    error: function () {
                        callback();
                    },
                });
            },
        });

        // OPTIONAL: kalau kamu mau autofill satuan/harga per baris
        ts.on("change", function () {
            const val = ts.getValue();
            const item = val ? ts.options[val] : null;

            const $row = $el.closest(".obat-row");
            if (!$row.length) return;

            if (item?.text) {
                $row.find(".obat-nama").val(item.text);
            } else {
                $row.find(".obat-nama").val("");
            }

            // contoh: isi hidden satuan kalau backend mengirim "satuan"
            if (item?.satuan) {
                $row.find(".obat-satuan").val(item.satuan);
            } else {
                $row.find(".obat-satuan").val("");
            }
        });

        return ts;
    }

    // =========================
    // Tambah row + init TomSelect untuk obat di row itu
    // =========================
    function addObatRow() {
        const $row = $($tpl.html());
        $obatContainer.append($row);

        // init TomSelect khusus untuk select obat di row ini
        $row.find("select.selectObat").each(function () {
            initTomSelectObat(this);
        });
    }

    // default 1 row
    addObatRow();

    // tambah row
    $btnTambah.on("click", function () {
        addObatRow();
    });

    // hapus 1 row saja + destroy tomselect
    $obatContainer.on("click", ".btn-hapus-obat", function () {
        const $row = $(this).closest(".obat-row");

        // destroy tomselect di row ini biar aman
        $row.find("select.selectObat").each(function () {
            if (this.tomselect) this.tomselect.destroy();
        });

        $row.remove();

        if ($obatContainer.find(".obat-row").length === 0) {
            addObatRow();
        }
    });

    // iter checkbox enable/disable (kalau kamu pakai)
    $obatContainer.on("change", ".chk-iter", function () {
        const $row = $(this).closest(".obat-row");
        const $iterInput = $row.find(".inp-iter");

        if ($(this).is(":checked")) {
            $iterInput.prop("disabled", false).focus();
            if (($iterInput.val() ?? "0") === "0") $iterInput.val(1);
        } else {
            $iterInput.val(0).prop("disabled", true);
        }
    });

    $("#btnPrintResep").on("click", function () {
        const $form = $("#formCetakResep");

        const tipe = $("#tipeResep").val();
        if (tipe === "resep_dokter" && !$("#selectDokter").val()) {
            alert("Tipe Resep Dokter: dokter wajib dipilih.");
            return;
        }

        // bikin form POST sementara untuk print (buka tab baru)
        const $tmp = $("<form>", {
            method: "POST",
            action: "/farmasi/cetak-resep-obat/print-preview",
            target: "_blank",
        });

        // CSRF wajib untuk POST
        $tmp.append($('input[name="_token"]').first().clone());

        // copy semua input/select/textarea dari form utama
        $form.find("input, select, textarea").each(function () {
            const $el = $(this);
            const name = $el.attr("name");
            if (!name) return;

            if ($el.is(":checkbox")) {
                if ($el.is(":checked")) {
                    $tmp.append(
                        $("<input>", { type: "hidden", name, value: $el.val() })
                    );
                }
            } else if ($el.is(":radio")) {
                if ($el.is(":checked")) {
                    $tmp.append(
                        $("<input>", { type: "hidden", name, value: $el.val() })
                    );
                }
            } else {
                $tmp.append(
                    $("<input>", { type: "hidden", name, value: $el.val() })
                );
            }
        });

        $("body").append($tmp);
        $tmp.trigger("submit");
        $tmp.remove();
    });

    // ================================
    // RESET FORM (atas & bawah)
    // ================================
    $("#btnResetForm, #btnResetHeader").on("click", function () {
        if (!confirm("Reset semua isian form?")) return;

        const $form = $("#formCetakResep");
        $form[0].reset();

        // reset tomselect
        if (window.tsPasien) tsPasien.clear();
        if (window.tsDokter) tsDokter.clear();

        // reset obat rows
        $("#obatContainer").empty();
        if (typeof addObatRow === "function") {
            addObatRow();
        }
    });

    // ================================
    // PREVIEW / CETAK (atas & bawah)
    // ================================
    $("#btnPreviewHeader, #btnSubmitPrint").on("click", function () {
        const $form = $("#formCetakResep");

        const tipe = $("#tipeResep").val();
        const dokter = $("#selectDokter").val();

        // validasi ringan
        if (tipe === "resep_dokter" && !dokter) {
            alert("Tipe Resep Dokter: dokter wajib dipilih.");
            return;
        }

        // buat form POST sementara
        const $tmp = $("<form>", {
            method: "POST",
            action: "/farmasi/cetak-resep-obat/print-preview",
            target: "_blank",
        });

        // CSRF
        $tmp.append($('input[name="_token"]').first().clone());

        // copy semua field
        $form.find("input, select, textarea").each(function () {
            const $el = $(this);
            const name = $el.attr("name");
            if (!name) return;

            if ($el.is(":checkbox")) {
                if ($el.is(":checked")) {
                    $tmp.append(
                        $("<input>", { type: "hidden", name, value: $el.val() })
                    );
                }
            } else {
                $tmp.append(
                    $("<input>", { type: "hidden", name, value: $el.val() })
                );
            }
        });

        $("body").append($tmp);
        $tmp.trigger("submit");
        $tmp.remove();
    });
});
