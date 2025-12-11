// resources/js/perawat/kunjungan/form-pengisian-vital-sign-pasien.js

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("vital-emr-form");
    if (!form) return;

    const numericFields = [
        { id: "suhu_tubuh", label: "Suhu Tubuh", min: 30, max: 45 },
        { id: "tinggi_badan", label: "Tinggi Badan", min: 50, max: 250 }, // 🔹 baru
        { id: "berat_badan", label: "Berat Badan", min: 2, max: 300 }, // 🔹 baru
        { id: "imt", label: "IMT", min: 5, max: 80 }, // 🔹 baru
        { id: "nadi", label: "Nadi", min: 30, max: 220 },
        { id: "pernapasan", label: "Pernapasan", min: 5, max: 60 },
        {
            id: "saturasi_oksigen",
            label: "Saturasi Oksigen",
            min: 50,
            max: 100,
        },
    ];

    // Field riwayat (opsional, hanya cek panjang maksimal)
    const historyFields = [
        { id: "riwayat_penyakit_dahulu", label: "Riwayat Penyakit Dahulu" },
        { id: "riwayat_penyakit_keluarga", label: "Riwayat Penyakit Keluarga" },
    ];

    const tekananInput = document.getElementById("tekanan_darah");

    function clearError(el) {
        el.setCustomValidity("");
        el.classList.remove("border-red-500", "ring-1", "ring-red-500");
    }

    if (tekananInput) {
        tekananInput.addEventListener("input", () => clearError(tekananInput));
    }

    numericFields.forEach((f) => {
        const el = document.getElementById(f.id);
        if (!el) return;
        el.addEventListener("input", () => clearError(el));
    });

    // auto-clear untuk textarea riwayat
    historyFields.forEach((f) => {
        const el = document.getElementById(f.id);
        if (!el) return;
        el.addEventListener("input", () => clearError(el));
    });

    function validateTekananDarah() {
        if (!tekananInput) return true;

        const val = tekananInput.value.trim();
        const regex = /^\d{2,3}\/\d{2,3}$/; // contoh: 120/80

        clearError(tekananInput);

        if (val === "") {
            tekananInput.setCustomValidity("Tekanan Darah wajib diisi.");
        } else if (!regex.test(val)) {
            tekananInput.setCustomValidity(
                "Format Tekanan Darah harus seperti 120/80."
            );
        }

        if (tekananInput.validationMessage) {
            tekananInput.classList.add(
                "border-red-500",
                "ring-1",
                "ring-red-500"
            );
            return false;
        }

        return true;
    }

    function validateNumericFields() {
        let firstInvalid = null;

        numericFields.forEach((f) => {
            const el = document.getElementById(f.id);
            if (!el) return;

            const raw = el.value.trim();
            const val = raw === "" ? null : Number(raw);

            clearError(el);

            if (val === null || Number.isNaN(val)) {
                el.setCustomValidity(`${f.label} wajib diisi.`);
            } else if (val < f.min) {
                el.setCustomValidity(
                    `${f.label} tidak boleh kurang dari ${f.min}.`
                );
            } else if (val > f.max) {
                el.setCustomValidity(
                    `${f.label} tidak boleh lebih dari ${f.max}.`
                );
            }

            if (el.validationMessage && !firstInvalid) {
                el.classList.add("border-red-500", "ring-1", "ring-red-500");
                firstInvalid = el;
            }
        });

        return firstInvalid;
    }

    // validasi sederhana untuk riwayat (optional, max 1000 karakter)
    function validateHistoryFields() {
        let firstInvalid = null;

        historyFields.forEach((f) => {
            const el = document.getElementById(f.id);
            if (!el) return;

            const val = el.value.trim();
            clearError(el);

            if (val.length > 1000) {
                el.setCustomValidity(`${f.label} maksimal 1000 karakter.`);
            }

            if (el.validationMessage && !firstInvalid) {
                el.classList.add("border-red-500", "ring-1", "ring-red-500");
                firstInvalid = el;
            }
        });

        return firstInvalid;
    }

    form.addEventListener("submit", async (e) => {
        e.preventDefault(); // cegah submit default

        const tekananOk = validateTekananDarah();
        const firstInvalidNumeric = validateNumericFields();
        const firstInvalidHistory = validateHistoryFields();

        if (!tekananOk) {
            tekananInput.reportValidity();
            tekananInput.focus();
            return;
        }

        if (firstInvalidNumeric) {
            firstInvalidNumeric.reportValidity();
            firstInvalidNumeric.focus();
            return;
        }

        if (firstInvalidHistory) {
            firstInvalidHistory.reportValidity();
            firstInvalidHistory.focus();
            return;
        }

        // Konfirmasi sebelum kirim ke server
        const result = await Swal.fire({
            title: "Simpan Data?",
            text: "Apakah Anda yakin data vital sign & riwayat sudah benar?",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Ya, simpan",
            cancelButtonText: "Periksa lagi",
        });

        if (!result.isConfirmed) {
            return;
        }

        // --- Kirim ke backend via AJAX (fetch) ---
        const formData = new FormData(form);
        const actionUrl = form.action;
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content");

        try {
            const response = await fetch(actionUrl, {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: formData,
            });

            const data = await response.json().catch(() => null);

            if (!response.ok) {
                // Kalau error validasi / server, tampilkan Swal error
                let message =
                    "Terjadi kesalahan. Silakan periksa kembali data Anda.";

                if (data && data.message) {
                    message = data.message;
                } else if (data && data.errors) {
                    // Ambil pesan error pertama
                    const firstField = Object.keys(data.errors)[0];
                    if (firstField && data.errors[firstField][0]) {
                        message = data.errors[firstField][0];
                    }
                }

                Swal.fire({
                    icon: "error",
                    title: "Gagal menyimpan!",
                    text: message,
                });

                return;
            }

            // Kalau success dari backend
            Swal.fire({
                icon: "success",
                title: "Berhasil!",
                text: data.message || "Data vital sign berhasil disimpan.",
                showConfirmButton: false,
                timer: 1500,
            }).then(() => {
                // redirect otomatis setelah 1.5 detik
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    // fallback: reload halaman ini
                    window.location.reload();
                }
            });
        } catch (error) {
            console.error("AJAX ERROR:", error);
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: "Terjadi kesalahan jaringan / server. Silakan coba lagi.",
            });
        }
    });
});
