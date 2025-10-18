import { Modal } from "flowbite";

// === Ambil elemen modal dan inisialisasi Flowbite Modal ===
const modalEl = document.getElementById("modalCreateKYAD");
const modal = new Modal(modalEl);

// === Ambil elemen penting ===
const form = modalEl.querySelector("form");
const pasienDataDiv = document.getElementById("pasien_data-kyad");
const searchInput = document.getElementById("search_pasien-kyad");
const resultsDiv = document.getElementById("search_results-kyad");

// === Fungsi reset form modal ===
function resetModalForm() {
    form.reset();
    pasienDataDiv.classList.add("hidden");
    resultsDiv.classList.add("hidden");
    searchInput.value = "";
    document.getElementById("nama_pasien").textContent = "";
    document.getElementById("alamat_pasien").textContent = "";
    document.getElementById("jk_pasien").textContent = "";
}

// === Saat halaman siap ===
document.addEventListener("DOMContentLoaded", () => {
    // === Tombol "Pilih Jadwal" ===
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
            document.getElementById("tanggal-kunjungan-kyad").value = btn.dataset.tanggal;
            modal.show();
        });
    });

    // === Tutup modal tombol X / batal / klik luar modal ===
    document.getElementById("closeModalBtn").addEventListener("click", () => {
        modal.hide();
        resetModalForm();
    });

    const cancelBtn = form.querySelector('button[type="button"]');
    cancelBtn.addEventListener("click", () => {
        modal.hide();
        resetModalForm();
    });

    modalEl.addEventListener("hidden.tw.modal", resetModalForm);

    // === Live search pasien ===
    searchInput.addEventListener("keyup", async () => {
        const query = searchInput.value.trim();
        if (query.length < 2) {
            resultsDiv.classList.add("hidden");
            return;
        }

        const response = await fetch(`/jadwal_kunjungan/search?query=${query}`);
        const data = await response.json();

        resultsDiv.innerHTML = "";
        if (data.length > 0) {
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
                        pasien.alamat;
                    document.getElementById("jk_pasien-kyad").textContent =
                        pasien.jenis_kelamin;
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

    // === Submit form via AJAX ===
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
                    loadWaitingList();
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
