import { Modal } from "flowbite";

const modalEl = document.getElementById("addKunjunganModal");
const modal = new Modal(modalEl);

const form = modalEl.querySelector("form");
const pasienDataDiv = document.getElementById("pasien_data");
const searchInput = document.getElementById("search_pasien");
const resultsDiv = document.getElementById("search_results");

function resetModalForm() {
    form.reset();
    pasienDataDiv.classList.add("hidden");
    resultsDiv.classList.add("hidden");
    searchInput.value = "";
    document.getElementById("nama_pasien").textContent = "";
    document.getElementById("alamat_pasien").textContent = "";
    document.getElementById("jk_pasien").textContent = "";
}

document.addEventListener("DOMContentLoaded", () => {
    // Tombol "Pilih Jadwal"
    document.querySelectorAll(".pilih-jadwal-btn").forEach((btn) => {
        btn.addEventListener("click", () => {
            document.getElementById("dokter_id").value = btn.dataset.dokterId;
            document.getElementById("dokter_nama").value = `${btn.dataset.dokterNama} (${btn.dataset.spesialis})`;
            document.getElementById("nama_poli").value = btn.dataset.namaPoli;
            document.getElementById("poli_id").value = btn.dataset.poliId;
            document.getElementById("tanggal_kunjungan").value = btn.dataset.tanggalKunjungan;
            document.getElementById("jadwal_id").value = btn.dataset.jadwalId; // ✅ penting
            modal.show();
        });
    });

    // Close modal
    document.getElementById("closeModalBtn").addEventListener("click", () => {
        modal.hide();
        resetModalForm();
    });
    document.getElementById("closeModalBtn2").addEventListener("click", () => {
        modal.hide();
        resetModalForm();
    });
    modalEl.addEventListener("hidden.tw.modal", resetModalForm);

    // Live search pasien
    searchInput.addEventListener("keyup", async () => {
        const query = searchInput.value.trim();
        if (query.length < 2) {
            resultsDiv.classList.add("hidden");
            return;
        }

        const response = await fetch(`/jadwal_kunjungan/search?query=${encodeURIComponent(query)}`);
        const data = await response.json();

        resultsDiv.innerHTML = "";
        resultsDiv.classList.remove("hidden");

        if (Array.isArray(data) && data.length > 0) {
            data.forEach((pasien) => {
                const item = document.createElement("div");
                item.className = "px-4 py-2 hover:bg-indigo-100 cursor-pointer text-sm";
                item.textContent = pasien.nama_pasien;
                item.onclick = () => {
                    document.getElementById("pasien_id").value = pasien.id;
                    document.getElementById("nama_pasien").textContent = pasien.nama_pasien;
                    document.getElementById("alamat_pasien").textContent = pasien.alamat ?? "-";
                    document.getElementById("jk_pasien").textContent = pasien.jenis_kelamin ?? "-";
                    pasienDataDiv.classList.remove("hidden");
                    resultsDiv.classList.add("hidden");
                    searchInput.value = pasien.nama_pasien;
                };
                resultsDiv.appendChild(item);
            });
        } else {
            resultsDiv.innerHTML = `<div class="px-4 py-2 text-gray-500 text-sm">Tidak ditemukan</div>`;
        }
    });

    // Submit form via AJAX
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(form);

        try {
            const response = await fetch(`/jadwal_kunjungan/create`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Accept": "application/json",
                },
                body: formData,
            });

            const result = await response.json();

            if (result?.success) {
                const noAntrian = result.data?.kunjungan?.no_antrian ?? "-";
                const dokterNama = result.data?.dokter_terpilih?.nama_dokter ?? "(tidak ada)";
                Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    html: `Kunjungan berhasil dibuat.<br>Nomor Antrian: <b>${noAntrian}</b><br>Dokter: <b>${dokterNama}</b>`,
                    showConfirmButton: false,
                    timer: 2200,
                }).then(() => {
                    modal.hide();
                    resetModalForm();
                    // Optional: reload list/antrian
                    // location.reload();
                });
            } else {
                const msg = result?.message || "Gagal menyimpan data kunjungan.";
                const errors = result?.errors
                    ? Object.values(result.errors).flat().join("\n")
                    : "";
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: errors || msg,
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
