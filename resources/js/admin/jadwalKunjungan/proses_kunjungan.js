document.addEventListener("DOMContentLoaded", () => {
    loadWaitingList();

    const menuProses = document.getElementById("menuProsesKunjungan");
    if (menuProses) {
        menuProses.addEventListener("click", (e) => {
            e.preventDefault(); // cegah reload halaman
            loadWaitingList(); // refresh tabel
        });
    }

    async function loadWaitingList() {
        const tbody = document.getElementById("waitingBody");
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-6 text-gray-500 italic">Memuat data...</td></tr>`;

        const res = await fetch("/jadwal_kunjungan/waiting");
        const data = await res.json();

        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-6 text-gray-500 italic">Tidak ada kunjungan pending hari ini.</td></tr>`;
            return;
        }

        tbody.innerHTML = "";
        data.forEach((item) => {
            const row = document.createElement("tr");
            row.className = "border-b hover:bg-indigo-50 transition";

            row.innerHTML = `
                <td class="px-6 py-3 font-semibold">${item.no_antrian}</td>
                <td class="px-6 py-3">${item.pasien.nama_pasien}</td>
                <td class="px-6 py-3">${item.dokter.nama_dokter}</td>
                <td class="px-6 py-3">${item.poli.nama_poli}</td>
                <td class="px-6 py-3">${item.keluhan_awal}</td>
                <td class="px-6 py-3 text-center">
                <div class="grid gap-4 place-items-center">
                    <button data-id="${item.id}" class="ubahStatusBtn bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg text-md">Mulai Konsultasi</button>
                    <button data-id="${item.id}" id="btn-batalkan-kunjungan" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-md">Batalkan Kunjungan</button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });

        // event listener untuk tombol ubah status
        document.querySelectorAll(".ubahStatusBtn").forEach((btn) => {
            btn.addEventListener("click", async () => {
                const id = btn.dataset.id;

                const confirm = await Swal.fire({
                    icon: "question",
                    title: "Mulai konsultasi?",
                    text: 'Status akan diubah menjadi "Engaged".',
                    showCancelButton: true,
                    confirmButtonText: "Ya, ubah",
                    cancelButtonText: "Batal",
                });

                if (confirm.isConfirmed) {
                    const res = await fetch(
                        `/jadwal_kunjungan/update-status/${id}`,
                        {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector(
                                    'meta[name="csrf-token"]'
                                ).content,
                                Accept: "application/json",
                            },
                        }
                    );

                    const result = await res.json();

                    console.log(result);

                    if (result.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: result.message,
                            timer: 1500,
                            showConfirmButton: false,
                        });
                        loadWaitingList();
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Gagal!",
                            text: result.message,
                        });
                    }
                }
            });
        });

        document.querySelectorAll("#btn-batalkan-kunjungan").forEach((btn) => {
            btn.addEventListener("click", async () => {
                const id = btn.dataset.id;

                const konfirmasi = await Swal.fire({
                    icon: "question",
                    title: "Batalkan Kunjungan?",
                    text: "Apakah anda yakin untuk membatalkan kunjungan?",
                    showCancelButton: true,
                    confirmButtonText: "Ya, batalkan",
                    cancelButtonText: "Tidak",
                });

                if (konfirmasi.isConfirmed) {
                    const data = await fetch(
                        `/jadwal_kunjungan/batalkan-kunjungan/${id}`,
                        {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector(
                                    'meta[name="csrf-token"]'
                                ).content,
                                Accept: "application/json",
                            },
                        }
                    );

                    const dataKunjungan = await data.json();

                    console.log(dataKunjungan);

                    if (dataKunjungan.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: dataKunjungan.message,
                            timer: 1500,
                            showConfirmButton: false,
                        });
                        loadWaitingList();
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Gagal!",
                            text: dataKunjungan.message,
                        });
                    }
                }
            });
        });
    }
});
