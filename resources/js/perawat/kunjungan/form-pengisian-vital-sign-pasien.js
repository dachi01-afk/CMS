document.addEventListener("DOMContentLoaded", () => {
    // Cari form dengan ID baru (emr-form) atau ID lama (vital-emr-form) sebagai fallback
    const form =
        document.getElementById("emr-form") ||
        document.getElementById("vital-emr-form");
    if (!form) return;

    // ==========================================
    // 1. KONFIGURASI FIELD VALIDASI
    // ==========================================
    // Pastikan elemen input di HTML memiliki atribut id="" yang sesuai dengan daftar ini
    const numericFields = [
        { id: "suhu_tubuh", label: "Suhu Tubuh", min: 30, max: 45 }, // Pastikan input punya id="suhu_tubuh"
        { id: "tb", label: "Tinggi Badan", min: 30, max: 250 }, // ID HTML baru: tb
        { id: "bb", label: "Berat Badan", min: 2, max: 300 }, // ID HTML baru: bb
        { id: "nadi", label: "Nadi", min: 30, max: 220 }, // Pastikan input punya id="nadi"
        { id: "pernapasan", label: "Pernapasan", min: 5, max: 60 }, // Pastikan input punya id="pernapasan"
        { id: "saturasi_oksigen", label: "SpO2", min: 50, max: 100 }, // Pastikan input punya id="saturasi_oksigen"
    ];

    const tekananInput = document.querySelector('input[name="tekanan_darah"]');

    // ==========================================
    // 2. FITUR OTOMATIS (INTERAKTIF)
    // ==========================================

    // A. Logic Kalkulasi IMT Real-time
    const bbInput = document.getElementById("bb"); // id="bb" dari HTML baru
    const tbInput = document.getElementById("tb"); // id="tb" dari HTML baru
    const imtInput = document.getElementById("imt");

    function calcIMT() {
        if (!bbInput || !tbInput || !imtInput) return;

        const bb = parseFloat(bbInput.value);
        const tb = parseFloat(tbInput.value) / 100; // cm to meter

        if (bb > 0 && tb > 0) {
            const imt = (bb / (tb * tb)).toFixed(2);
            imtInput.value = imt;
        } else {
            imtInput.value = "";
        }
    }
    if (bbInput) bbInput.addEventListener("input", calcIMT);
    if (tbInput) tbInput.addEventListener("input", calcIMT);

    // B. Logic Slider Skala Nyeri
    const nyeriRange = document.getElementById("nyeri_range");
    const nyeriVal = document.getElementById("nyeri_val");

    if (nyeriRange && nyeriVal) {
        nyeriRange.addEventListener("input", function () {
            nyeriVal.innerText = this.value;
            // Ubah warna text visual
            nyeriVal.className =
                "font-bold text-2xl " +
                (this.value < 4
                    ? "text-green-600"
                    : this.value < 7
                      ? "text-yellow-500"
                      : "text-red-600");
        });
    }

    // C. Logic Toggle Alergi
    const radioAlergi = document.querySelectorAll('input[name="has_alergi"]');
    const inputKetAlergi = document.getElementById("ket_alergi");

    if (inputKetAlergi) {
        radioAlergi.forEach((radio) => {
            radio.addEventListener("change", (e) => {
                if (e.target.value === "1") {
                    inputKetAlergi.classList.remove("hidden");
                    inputKetAlergi.focus();
                } else {
                    inputKetAlergi.classList.add("hidden");
                    inputKetAlergi.value = ""; // Reset jika tidak ada
                }
            });
        });
    }

    // D. Logic Kalkulator GCS (Glasgow Coma Scale)
    const gcsInputs = document.querySelectorAll(".gcs-input");
    const gcsTotal = document.getElementById("gcs_total");

    function calcGCS() {
        if (!gcsTotal) return;
        let total = 0;
        gcsInputs.forEach((input) => {
            total += parseInt(input.value || 0);
        });
        gcsTotal.value = total;
    }
    // Hitung saat berubah dan saat pertama load
    gcsInputs.forEach((input) => input.addEventListener("change", calcGCS));
    calcGCS();

    // ==========================================
    // 3. FUNGSI VALIDASI (DARI KODE LAMA)
    // ==========================================

    function clearError(el) {
        el.setCustomValidity("");
        el.classList.remove("border-red-500", "ring-1", "ring-red-500");
    }

    // Pasang listener clearError ke field numerik
    numericFields.forEach((f) => {
        const el = document.getElementById(f.id);
        if (el) el.addEventListener("input", () => clearError(el));
    });

    if (tekananInput) {
        tekananInput.addEventListener("input", () => clearError(tekananInput));
    }

    function validateTekananDarah() {
        if (!tekananInput) return true; // Skip jika field tidak ada
        const val = tekananInput.value.trim();
        // Regex sedikit dilonggarkan spasi (opsional)
        const regex = /^\d{2,3}\s?\/\s?\d{2,3}$/;

        clearError(tekananInput);

        if (val === "") {
            tekananInput.setCustomValidity("Tekanan Darah wajib diisi.");
        } else if (!regex.test(val)) {
            tekananInput.setCustomValidity("Format salah. Contoh: 120/80");
        }

        if (tekananInput.validationMessage) {
            tekananInput.classList.add(
                "border-red-500",
                "ring-1",
                "ring-red-500",
            );
            return false;
        }
        return true;
    }

    function validateNumericFields() {
        let firstInvalid = null;

        numericFields.forEach((f) => {
            const el = document.getElementById(f.id);
            if (!el) return; // Skip jika elemen tidak ditemukan di HTML

            const raw = el.value.trim();
            const val = raw === "" ? null : Number(raw);

            clearError(el);

            // Validasi wajib isi (kecuali IMT karena auto)
            if (f.id !== "imt" && (val === null || Number.isNaN(val))) {
                el.setCustomValidity(`${f.label} wajib diisi.`);
            } else if (val < f.min) {
                el.setCustomValidity(`${f.label} minimal ${f.min}.`);
            } else if (val > f.max) {
                el.setCustomValidity(`${f.label} maksimal ${f.max}.`);
            }

            if (el.validationMessage) {
                el.classList.add("border-red-500", "ring-1", "ring-red-500");
                if (!firstInvalid) firstInvalid = el;
            }
        });

        return firstInvalid;
    }

    // ==========================================
    // 4. SUBMISSION LOGIC (AJAX)
    // ==========================================

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        // 1. Jalankan Validasi
        const tekananOk = validateTekananDarah();
        const firstInvalidNumeric = validateNumericFields();

        if (!tekananOk) {
            tekananInput.reportValidity();
            return;
        }

        if (firstInvalidNumeric) {
            firstInvalidNumeric.reportValidity();
            firstInvalidNumeric.focus();
            return;
        }

        // 2. Konfirmasi SweetAlert
        const result = await Swal.fire({
            title: "Simpan Pengkajian?",
            text: "Pastikan data tanda vital, nyeri, dan risiko jatuh sudah benar.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#4f46e5", // Indigo-600
            cancelButtonColor: "#94a3b8", // Slate-400
            confirmButtonText: "Ya, Simpan",
            cancelButtonText: "Cek Lagi",
        });

        if (!result.isConfirmed) return;

        // 3. Persiapan Kirim Data
        const formData = new FormData(form);
        const actionUrl = form.action;
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");

        // Tampilkan Loading
        Swal.fire({
            title: "Menyimpan...",
            text: "Mohon tunggu sebentar",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            },
        });

        try {
            const response = await fetch(actionUrl, {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
                body: formData,
            });

            const data = await response.json().catch(() => null);

            if (!response.ok) {
                // Error Handling
                let message = "Terjadi kesalahan server.";
                if (data?.message) message = data.message;
                else if (data?.errors) {
                    const firstField = Object.keys(data.errors)[0];
                    message = data.errors[firstField][0];
                }

                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: message,
                });
                return;
            }

            // Sukses
            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: data.message || "Pengkajian berhasil disimpan.",
                showConfirmButton: false,
                timer: 1500,
            }).then(() => {
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    window.location.reload();
                }
            });
        } catch (error) {
            console.error("AJAX Error:", error);
            Swal.fire({
                icon: "error",
                title: "Error Jaringan",
                text: "Gagal menghubungi server. Periksa koneksi internet Anda.",
            });
        }
    });
});
