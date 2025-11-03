document.addEventListener("DOMContentLoaded", () => {
    loadWaitingList();

    const menuProses = document.getElementById("menuProsesKunjungan");
    if (menuProses) {
        menuProses.addEventListener("click", (e) => {
            e.preventDefault();
            loadWaitingList();
        });
    }

    async function loadWaitingList() {
        const tbody = document.getElementById("waitingBody");
        if (!tbody) return;

        // tampilkan loading row
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-6 text-gray-500 italic">
                    Memuat data...
                </td>
            </tr>`;

        let payload = [];
        try {
            const res = await fetch("/jadwal_kunjungan/waiting", {
                headers: { Accept: "application/json" },
            });

            if (!res.ok) {
                throw new Error(`HTTP ${res.status}`);
            }

            const json = await res.json();

            // Bentuk response baru: { success, date, data: [...] }
            if (json && json.success === true && Array.isArray(json.data)) {
                payload = json.data;
            } else if (Array.isArray(json)) {
                // fallback kalau masih pakai array lama
                payload = json;
            } else {
                payload = [];
            }
        } catch (err) {
            console.error("Gagal memuat waiting list:", err);
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-6 text-red-600">
                        Gagal memuat data. Coba lagi.
                    </td>
                </tr>`;
            return;
        }

        if (payload.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-6 text-gray-500 italic">
                        Tidak ada kunjungan pending hari ini.
                    </td>
                </tr>`;
            return;
        }

        // Render rows
        tbody.innerHTML = "";
        payload.forEach((item) => {
            const noAntrian   = item.no_antrian ?? "-";
            const namaPasien  = item.pasien?.nama_pasien ?? "-";
            // Prioritas dokter_terpilih → fallback ke relasi poli.dokter (kalau ada) → "-"
            const namaDokter  =
                item.dokter_terpilih?.nama_dokter ??
                item.poli?.dokter?.nama_dokter ??
                "-";
            const namaPoli    = item.poli?.nama_poli ?? "-";
            const keluhan     = item.keluhan_awal ?? "-";

            const row = document.createElement("tr");
            row.className = "border-b hover:bg-indigo-50 transition";

            row.innerHTML = `
                <td class="px-6 py-3 font-semibold">${noAntrian}</td>
                <td class="px-6 py-3">${namaPasien}</td>
                <td class="px-6 py-3">${namaDokter}</td>
                <td class="px-6 py-3">${namaPoli}</td>
                <td class="px-6 py-3">${keluhan}</td>
                <td class="px-6 py-3 text-center">
                    <div class="grid gap-3 place-items-center">
                        <button data-id="${item.id}"
                                class="ubahStatusBtn bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg text-md">
                            Mulai Konsultasi
                        </button>
                        <button data-id="${item.id}"
                                class="batalkanKunjunganBtn bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-md">
                            Batalkan Kunjungan
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });

        // Event: Ubah status → Engaged
        document.querySelectorAll(".ubahStatusBtn").forEach((btn) => {
            btn.addEventListener("click", async () => {
                const id = btn.dataset.id;

                const konfirmasi = await Swal.fire({
                    icon: "question",
                    title: "Mulai konsultasi?",
                    text: 'Status akan diubah menjadi "Engaged".',
                    showCancelButton: true,
                    confirmButtonText: "Ya, ubah",
                    cancelButtonText: "Batal",
                });

                if (!konfirmasi.isConfirmed) return;

                try {
                    const res = await fetch(`/jadwal_kunjungan/update-status/${id}`, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                            Accept: "application/json",
                        },
                    });
                    const result = await res.json();

                    if (result?.success) {
                        await Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: result.message ?? "Status diubah.",
                            timer: 1500,
                            showConfirmButton: false,
                        });
                        loadWaitingList();
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Gagal!",
                            text: result?.message ?? "Gagal mengubah status.",
                        });
                    }
                } catch (e) {
                    console.error(e);
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: "Tidak dapat terhubung ke server.",
                    });
                }
            });
        });

        // Event: Batalkan kunjungan
        document.querySelectorAll(".batalkanKunjunganBtn").forEach((btn) => {
            btn.addEventListener("click", async () => {
                const id = btn.dataset.id;

                const konfirmasi = await Swal.fire({
                    icon: "question",
                    title: "Batalkan Kunjungan?",
                    text: "Apakah Anda yakin ingin membatalkan kunjungan?",
                    showCancelButton: true,
                    confirmButtonText: "Ya, batalkan",
                    cancelButtonText: "Tidak",
                });

                if (!konfirmasi.isConfirmed) return;

                try {
                    const res = await fetch(`/jadwal_kunjungan/batalkan-kunjungan/${id}`, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                            Accept: "application/json",
                        },
                    });
                    const result = await res.json();

                    if (result?.success) {
                        await Swal.fire({
                            icon: "success",
                            title: "Berhasil!",
                            text: result.message ?? "Kunjungan dibatalkan.",
                            timer: 1500,
                            showConfirmButton: false,
                        });
                        loadWaitingList();
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Gagal!",
                            text: result?.message ?? "Gagal membatalkan kunjungan.",
                        });
                    }
                } catch (e) {
                    console.error(e);
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: "Tidak dapat terhubung ke server.",
                    });
                }
            });
        });
    }
});
