import { Modal } from "flowbite";

document.addEventListener("DOMContentLoaded", () => {
    const modalEl = document.getElementById("modalCreateKYAD");
    if (!modalEl) return;

    // Modal TIDAK bisa ditutup oleh klik luar / ESC
    const modal = new Modal(modalEl, {
        backdrop: "dynamic", // boleh dynamic, tapi...
        closable: false, // ...klik luar & ESC TIDAK akan menutup modal
    });

    const form = modalEl.querySelector("form");
    const pasienDataDiv = document.getElementById("pasien_data-kyad");
    const searchInput = document.getElementById("search_pasien-kyad");
    const resultsDiv = document.getElementById("search_results-kyad");

    // =========================
    // RESET MODAL
    // =========================
    function resetModalForm() {
        if (form) form.reset();

        if (pasienDataDiv) pasienDataDiv.classList.add("hidden");
        if (resultsDiv) resultsDiv.classList.add("hidden");
        if (searchInput) searchInput.value = "";

        // Bersihkan data pasien
        const namaPasien = document.getElementById("nama_pasien-kyad");
        const alamatPasien = document.getElementById("alamat_pasien-kyad");
        const jkPasien = document.getElementById("jk_pasien-kyad");

        if (namaPasien) namaPasien.textContent = "";
        if (alamatPasien) alamatPasien.textContent = "";
        if (jkPasien) jkPasien.textContent = "";

        // Hidden input
        const dokterId = document.getElementById("dokter_id-kyad");
        const poliId = document.getElementById("poli_id-kyad");
        const tanggalKunjungan = document.getElementById(
            "tanggal-kunjungan-kyad"
        );
        const pasienId = document.getElementById("pasien_id-kyad");
        const jadwalId = document.getElementById("jadwal_id-kyad");

        if (dokterId) dokterId.value = "";
        if (poliId) poliId.value = "";
        if (tanggalKunjungan) tanggalKunjungan.value = "";
        if (pasienId) pasienId.value = "";
        if (jadwalId) jadwalId.value = "";

        // Section "Jadwal Praktik"
        const dokterNama = document.getElementById("dokter_nama-kyad");
        const namaPoli = document.getElementById("nama_poli-kyad");
        const tanggalDisplay = document.getElementById("tanggal_display-kyad");
        const spesialisDisplay = document.getElementById(
            "spesialis_display-kyad"
        );

        if (dokterNama) dokterNama.textContent = "";
        if (namaPoli) namaPoli.textContent = "";
        if (tanggalDisplay) tanggalDisplay.textContent = "";
        if (spesialisDisplay) spesialisDisplay.textContent = "";
    }

    // =========================
    // SEARCH TABEL "JADWAL DOKTER YANG AKAN DATANG"
    // =========================
    const jadwalKyadSearchInput = document.getElementById(
        "jadwal_kyad_searchInput"
    );
    const jadwalKyadTable = document.getElementById("jadwalKyadTable");

    if (jadwalKyadSearchInput && jadwalKyadTable) {
        const tbody = jadwalKyadTable.querySelector("tbody");
        if (tbody) {
            const dataRows = Array.from(tbody.querySelectorAll("tr"));

            const noResultRow = document.createElement("tr");
            noResultRow.id = "jadwal_kyad_no_result_row";
            noResultRow.className = "bg-amber-50";
            noResultRow.style.display = "none";
            noResultRow.innerHTML = `
                <td colspan="6" class="px-6 py-4 text-center text-sm text-slate-500 italic">
                    Data yang anda cari tidak ada.
                </td>
            `;
            tbody.appendChild(noResultRow);

            jadwalKyadSearchInput.addEventListener("input", function () {
                const query = this.value.toLowerCase().trim();
                let hasVisible = false;

                dataRows.forEach((row) => {
                    const rowText = row.textContent.toLowerCase();

                    if (query === "") {
                        row.style.display = "";
                        hasVisible = true;
                    } else {
                        const match = rowText.includes(query);
                        row.style.display = match ? "" : "none";
                        if (match) hasVisible = true;
                    }
                });

                if (query !== "" && !hasVisible) {
                    noResultRow.style.display = "";
                } else {
                    noResultRow.style.display = "none";
                }
            });
        }
    }

    function formatTanggalIndo(dateString) {
        if (!dateString) return "-";

        const bulanIndo = [
            "Januari",
            "Februari",
            "Maret",
            "April",
            "Mei",
            "Juni",
            "Juli",
            "Agustus",
            "September",
            "Oktober",
            "November",
            "Desember",
        ];

        const date = new Date(dateString + "T00:00:00");
        const hari = date.getDate();
        const bulan = bulanIndo[date.getMonth()];
        const tahun = date.getFullYear();

        return `${hari} ${bulan} ${tahun}`;
    }

    // =========================
    // BUKA MODAL DARI TOMBOL "BUAT KUNJUNGAN"
    // =========================
    document.querySelectorAll(".pilih-kyad-btn").forEach((btn) => {
        btn.addEventListener("click", () => {
            // Hidden input
            const dokterId = document.getElementById("dokter_id-kyad");
            const poliId = document.getElementById("poli_id-kyad");
            const tanggalKunjungan = document.getElementById(
                "tanggal-kunjungan-kyad"
            );
            const jadwalId = document.getElementById("jadwal_id-kyad");

            if (dokterId) dokterId.value = btn.dataset.dokterId || "";
            if (poliId) poliId.value = btn.dataset.poliId || "";
            if (tanggalKunjungan)
                tanggalKunjungan.value = btn.dataset.tanggal || "";
            if (jadwalId) jadwalId.value = btn.dataset.jadwalId || "";

            // Tampilkan info di section "Jadwal Praktik"
            const dokterNama = document.getElementById("dokter_nama-kyad");
            const namaPoli = document.getElementById("nama_poli-kyad");
            const tanggalDisplay = document.getElementById(
                "tanggal_display-kyad"
            );
            const spesialisDisplay = document.getElementById(
                "spesialis_display-kyad"
            );

            if (dokterNama) {
                dokterNama.textContent = `${btn.dataset.dokterNama} (${btn.dataset.spesialis})`;
            }
            if (namaPoli) {
                namaPoli.textContent = btn.dataset.namaPoli || "-";
            }
            if (tanggalDisplay) {
                tanggalDisplay.textContent = formatTanggalIndo(btn.dataset.tanggal || "-");
            }
            if (spesialisDisplay) {
                spesialisDisplay.textContent = btn.dataset.spesialis || "-";
            }

            modal.show();
        });
    });

    // =========================
    // TUTUP MODAL (TOMBOL X & BATAL)
    // =========================
    const closeBtn = document.getElementById("buttonCloseModalCreateKYAD");
    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            modal.hide();
            resetModalForm();
        });
    }

    const cancelBtn = document.getElementById(
        "buttonCloseModalCreateKYAD_footer"
    );
    if (cancelBtn) {
        cancelBtn.addEventListener("click", () => {
            modal.hide();
            resetModalForm();
        });
    }

    // Kalau suatu saat modal di-hide via JS lain, tetap reset
    modalEl.addEventListener("hidden.tw.modal", resetModalForm);

    // =========================
    // LIVE SEARCH PASIEN DI MODAL KYAD
    // =========================
    if (searchInput) {
        searchInput.addEventListener("keyup", async () => {
            const query = searchInput.value.trim();
            if (query.length < 2) {
                resultsDiv.classList.add("hidden");
                return;
            }

            const response = await fetch(
                `/jadwal_kunjungan/search?query=${encodeURIComponent(query)}`
            );
            const data = await response.json();

            resultsDiv.innerHTML = "";
            if (Array.isArray(data) && data.length > 0) {
                resultsDiv.classList.remove("hidden");
                data.forEach((pasien) => {
                    const item = document.createElement("div");
                    item.className =
                        "px-4 py-2 hover:bg-indigo-100 cursor-pointer text-sm";
                    item.textContent = pasien.nama_pasien;
                    item.onclick = () => {
                        const pasienId =
                            document.getElementById("pasien_id-kyad");
                        const namaPasien =
                            document.getElementById("nama_pasien-kyad");
                        const alamatPasien =
                            document.getElementById("alamat_pasien-kyad");
                        const jkPasien =
                            document.getElementById("jk_pasien-kyad");

                        if (pasienId) pasienId.value = pasien.id;
                        if (namaPasien)
                            namaPasien.textContent = pasien.nama_pasien;
                        if (alamatPasien)
                            alamatPasien.textContent = pasien.alamat ?? "-";
                        if (jkPasien)
                            jkPasien.textContent = pasien.jenis_kelamin ?? "-";

                        if (pasienDataDiv)
                            pasienDataDiv.classList.remove("hidden");
                        resultsDiv.classList.add("hidden");
                        searchInput.value = pasien.nama_pasien;
                    };
                    resultsDiv.appendChild(item);
                });
            } else {
                resultsDiv.classList.remove("hidden");
                resultsDiv.innerHTML = `<div class="px-4 py-2 text-gray-500 text-sm">Tidak ditemukan</div>`;
            }
        });
    }

    // =========================
    // SUBMIT FORM VIA AJAX
    // =========================
    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            const formData = new FormData(form);

            try {
                const response = await fetch(`/jadwal_kunjungan/create`, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                        Accept: "application/json",
                    },
                    body: formData,
                });

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Berhasil!",
                        text: `Kunjungan berhasil dibuat.\nNomor Antrian: ${result.data.no_antrian}`,
                        showConfirmButton: false,
                        timer: 2000,
                    }).then(() => {
                        modal.hide();
                        resetModalForm();
                        if (typeof loadWaitingList === "function") {
                            loadWaitingList();
                        } else {
                            window.location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Gagal!",
                        text:
                            result.message || "Gagal menyimpan data kunjungan.",
                        confirmButtonText: "OK",
                    });
                }
            } catch (error) {
                console.error("Error:", error);
                Swal.fire({
                    icon: "error",
                    title: "Terjadi Kesalahan!",
                    text: "Tidak dapat menghubungi server. Periksa koneksi atau log backend.",
                    confirmButtonText: "OK",
                });
            }
        });
    }
});
