document.addEventListener("DOMContentLoaded", () => {
    const tbody = document.getElementById("waitingBody");
    const menuProses = document.getElementById("menuProsesKunjungan");
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    if (menuProses) {
        menuProses.addEventListener("click", (e) => {
            e.preventDefault();
            loadWaitingList();
        });
    }

    const esc = (s) =>
        String(s ?? "-")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");

    async function loadWaitingList() {
        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-6 text-gray-500 italic">
                    Memuat data...
                </td>
            </tr>`;

        try {
            const res = await fetch("/jadwal_kunjungan/waiting", {
                headers: { Accept: "application/json" },
                credentials: "same-origin",
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const json = await res.json();

            const payload = Array.isArray(json?.data)
                ? json.data
                : Array.isArray(json)
                ? json
                : [];

            console.log(payload);

            if (!payload.length) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-6 text-gray-500 italic">
                            Tidak ada kunjungan pending hari ini.
                        </td>
                    </tr>`;
                return;
            }

            tbody.innerHTML = payload
                .map((item) => {
                    const noAntrian = esc(item.no_antrian ?? "-");
                    const namaPasien = esc(item.pasien?.nama_pasien ?? "-");

                    // 🔹 Dokter: ambil langsung dari relasi 'dokter'
                    // fallback: item.dokter_terpilih / item._nama_dokter
                    const namaDokter = esc(
                        item.dokter?.nama_dokter ??
                            item.dokter_terpilih?.nama_dokter ??
                            item._nama_dokter ??
                            "-"
                    );

                    const namaPoli = esc(item.poli?.nama_poli ?? "-");
                    const keluhan = esc(item.keluhan_awal ?? "-");

                    return `
                        <tr class="border-b hover:bg-indigo-50 transition">
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
                        </tr>`;
                })
                .join("");
        } catch (err) {
            console.error("Gagal memuat waiting list:", err);
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-6 text-red-600">
                        ${esc(err.message ?? "Gagal memuat data. Coba lagi.")}
                    </td>
                </tr>`;
        }
    }

    // 🔹 Event Delegation (lebih aman, tidak hilang setelah reload table)
    document.addEventListener("click", async (e) => {
        const startBtn = e.target.closest(".ubahStatusBtn");
        const cancelBtn = e.target.closest(".batalkanKunjunganBtn");

        // ========== MULAI KONSULTASI ==========
        if (startBtn) {
            const id = startBtn.dataset.id;
            const konfirmasi = await Swal.fire({
                icon: "question",
                title: "Mulai konsultasi?",
                text: 'Status akan diubah menjadi "Waiting".',
                showCancelButton: true,
                confirmButtonText: "Ya, ubah",
                cancelButtonText: "Batal",
            });
            if (!konfirmasi.isConfirmed) return;

            startBtn.disabled = true;

            try {
                const res = await fetch(
                    `/jadwal_kunjungan/update-status/${id}`,
                    {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": csrf,
                            Accept: "application/json",
                        },
                        credentials: "same-origin",
                    }
                );
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
            } finally {
                startBtn.disabled = false;
            }
        }

        // ========== BATALKAN KUNJUNGAN ==========
        if (cancelBtn) {
            const id = cancelBtn.dataset.id;
            const konfirmasi = await Swal.fire({
                icon: "question",
                title: "Batalkan Kunjungan?",
                text: "Apakah Anda yakin ingin membatalkan kunjungan?",
                showCancelButton: true,
                confirmButtonText: "Ya, batalkan",
                cancelButtonText: "Tidak",
            });
            if (!konfirmasi.isConfirmed) return;

            cancelBtn.disabled = true;

            try {
                const res = await fetch(
                    `/jadwal_kunjungan/batalkan-kunjungan/${id}`,
                    {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": csrf,
                            Accept: "application/json",
                        },
                        credentials: "same-origin",
                    }
                );
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
            } finally {
                cancelBtn.disabled = false;
            }
        }
    });

    // 🔹 Load awal
    loadWaitingList();
});
