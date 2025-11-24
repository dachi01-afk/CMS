import { Modal } from "flowbite";

const modalEl = document.getElementById("modalCreateKYAD");
const modal = new Modal(modalEl);

const form = modalEl.querySelector("form");
const pasienDataDiv = document.getElementById("pasien_data-kyad");
const searchInput = document.getElementById("search_pasien-kyad");
const resultsDiv = document.getElementById("search_results-kyad");

// Reset modal
function resetModalForm() {
    form.reset();
    pasienDataDiv.classList.add("hidden");
    resultsDiv.classList.add("hidden");
    searchInput.value = "";

    document.getElementById("nama_pasien-kyad").textContent = "";
    document.getElementById("alamat_pasien-kyad").textContent = "";
    document.getElementById("jk_pasien-kyad").textContent = "";

    document.getElementById("dokter_id-kyad").value = "";
    document.getElementById("dokter_nama-kyad").value = "";
    document.getElementById("poli_id-kyad").value = "";
    document.getElementById("nama_poli-kyad").value = "";
    document.getElementById("tanggal-kunjungan-kyad").value = "";
    const pasienId = document.getElementById("pasien_id-kyad");
    if (pasienId) pasienId.value = "";
    const jadwalId = document.getElementById("jadwal_id-kyad");
    if (jadwalId) jadwalId.value = "";
}

document.addEventListener("DOMContentLoaded", () => {
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
            // Simpan semua baris data awal
            const dataRows = Array.from(tbody.querySelectorAll("tr"));

            // Buat row khusus untuk pesan "data tidak ada"
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
                        // Jika input kosong: tampilkan semua row data
                        row.style.display = "";
                        hasVisible = true;
                    } else {
                        const match = rowText.includes(query);
                        row.style.display = match ? "" : "none";
                        if (match) hasVisible = true;
                    }
                });

                // Tampilkan / sembunyikan row "data tidak ada"
                if (query !== "" && !hasVisible) {
                    noResultRow.style.display = "";
                } else {
                    noResultRow.style.display = "none";
                }
            });
        }
    }

    // =========================
    // Buka modal dari tombol "Buat Kunjungan"
    // =========================
    document.querySelectorAll(".pilih-kyad-btn").forEach((btn) => {
        btn.addEventListener("click", () => {
            document.getElementById("dokter_id-kyad").value =
                btn.dataset.dokterId;
            document.getElementById(
                "dokter_nama-kyad"
            ).value = `${btn.dataset.dokterNama} (${btn.dataset.spesialis})`;

            document.getElementById("nama_poli-kyad").value =
                btn.dataset.namaPoli;
            document.getElementById("poli_id-kyad").value = btn.dataset.poliId;

            document.getElementById("tanggal-kunjungan-kyad").value =
                btn.dataset.tanggal;
            document.getElementById("jadwal_id-kyad").value =
                btn.dataset.jadwalId || "";

            // Tampilkan info di section "Jadwal Praktik"
            const tanggalDisplay = document.getElementById(
                "tanggal_display-kyad"
            );
            const spesialisDisplay = document.getElementById(
                "spesialis_display-kyad"
            );
            if (tanggalDisplay)
                tanggalDisplay.textContent = btn.dataset.tanggal || "-";
            if (spesialisDisplay)
                spesialisDisplay.textContent = btn.dataset.spesialis || "-";

            modal.show();
        });
    });

    // =========================
    // Tutup modal
    // =========================
    document.getElementById("closeModalBtn").addEventListener("click", () => {
        modal.hide();
        resetModalForm();
    });
    const cancelBtn = document.getElementById("closeModalBtn2");
    if (cancelBtn) {
        cancelBtn.addEventListener("click", () => {
            modal.hide();
            resetModalForm();
        });
    }
    modalEl.addEventListener("hidden.tw.modal", resetModalForm);

    // =========================
    // Live search pasien di modal KYAD
    // =========================
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
                    document.getElementById("pasien_id-kyad").value = pasien.id;
                    document.getElementById("nama_pasien-kyad").textContent =
                        pasien.nama_pasien;
                    document.getElementById("alamat_pasien-kyad").textContent =
                        pasien.alamat ?? "-";
                    document.getElementById("jk_pasien-kyad").textContent =
                        pasien.jenis_kelamin ?? "-";

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

    // =========================
    // Submit form via AJAX
    // =========================
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
                    text: result.message || "Gagal menyimpan data kunjungan.",
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
});
