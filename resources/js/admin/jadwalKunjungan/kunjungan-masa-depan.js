document.addEventListener("DOMContentLoaded", () => {
    loadWaitingList();

    const menuProses = document.getElementById("menuProsesKunjunganMasaDepan");
    if (menuProses) {
        menuProses.addEventListener("click", (e) => {
            e.preventDefault();
            loadWaitingList();
        });
    }

    async function loadWaitingList() {
        const tbody = document.getElementById("waitingBodyMasaDepan");
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-6 text-gray-500 italic">Memuat data...</td></tr>`;

        const res = await fetch("/jadwal_kunjungan/masa-depan");
        const data = await res.json();

        console.log(data);

        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-6 text-gray-500 italic">Tidak ada kunjungan pending hari ini.</td></tr>`;
            return;
        }

        tbody.innerHTML = "";
        data.forEach((item) => {
            const namaDokter =
                (item.dokter_terpilih && item.dokter_terpilih.nama_dokter) ||
                (item.poli &&
                    item.poli.dokter &&
                    item.poli.dokter.nama_dokter) ||
                "-";

            const row = document.createElement("tr");
            row.className = "border-b hover:bg-indigo-50 transition";

            row.innerHTML = `
    <td class="px-6 py-3 font-semibold">${item.no_antrian}</td>
    <td class="px-6 py-3">${item.pasien.nama_pasien}</td>
    <td class="px-6 py-3">${namaDokter}</td>
    <td class="px-6 py-3">${item.poli?.nama_poli ?? "-"}</td>
    <td class="px-6 py-3">${item.keluhan_awal}</td>
    <td class="px-6 py-3">${new Date(item.tanggal_kunjungan).toLocaleDateString(
        "id-ID",
        {
            day: "2-digit",
            month: "long",
            year: "numeric",
        }
    )}</td>
    <td class="px-6 py-3 text-center">
      <button data-id="${item.id}"
              data-dokter="${namaDokter}"
              class="ubahStatusBtn text-blue-600 hover:text-blue-800 mr-2 text-center items-center">
        <i class="fa-solid fa-circle-info text-lg"></i>
        Lihat Detail
      </button>
    </td>
  `;
            tbody.appendChild(row);
        });

        // === Event listener tombol Lihat Detail ===
        document.querySelectorAll(".ubahStatusBtn").forEach((btn) => {
            btn.addEventListener("click", async () => {
                const id = btn.dataset.id;
                const namaDokter = btn.dataset.dokter;

                // Panggil API detail berdasarkan ID
                const res = await fetch(
                    `/jadwal_kunjungan/get-data-KYAD/${id}`
                );
                const detail = await res.json();

                // console.log(detail); // cek dulu di console datanya muncul

                // lalu tampilkan di modal atau alert dulu
                Swal.fire({
                    title: "Detail Kunjungan",
                    position: "start",
                    html: `
    <div class="grid gap-2 text-left justify-start">
        <p><b>Nama Pasien:</b> ${detail.data.pasien.nama_pasien}</p>
        <p><b>Nama Dokter:</b> ${namaDokter}</p>
        <p><b>Poli:</b> ${detail.data.poli.nama_poli}</p>
        <p><b>Tanggal:</b> ${new Date(
            detail.data.tanggal_kunjungan
        ).toLocaleDateString("id-ID", {
            day: "2-digit",
            month: "long",
            year: "numeric",
        })}</p>
        <p><b>Keluhan Awal:</b> ${detail.data.keluhan_awal}</p>
    </div>
`,

                    confirmButtonText: "Tutup",
                });
            });
        });
    }

    // tombol close modal
    const closeBtn = document.getElementById("closeModalKYAD");
    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            document.getElementById("modalDetailKYAD").classList.add("hidden");
        });
    }
});
