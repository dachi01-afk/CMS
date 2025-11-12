document.addEventListener("DOMContentLoaded", () => {
    const tbody = document.getElementById("waitingBody");
    const endpoint = "/perawat/getDataKunjunganHariIni";

    // Escape sederhana untuk cegah XSS dari teks bebas
    const esc = (s) =>
        String(s ?? "-")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");

    function rowTpl(r) {
        return `
      <tr class="border-b hover:bg-gray-50">
        <td class="px-6 py-3">${esc(r.no_antrian)}</td>
        <td class="px-6 py-3">${esc(r.nama_pasien)}</td>
        <td class="px-6 py-3">${esc(r.dokter)}</td>
        <td class="px-6 py-3">${esc(r.poli)}</td>
        <td class="px-6 py-3">${esc(r.keluhan)}</td>
        <td class="px-6 py-3 text-center">
          <button class="btn-proses px-3 py-1 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700"
                  data-id="${esc(r.kunjungan_id)}">
            Proses
          </button>
        </td>
      </tr>`;
    }

    function setLoading() {
        tbody.innerHTML = `
      <tr><td colspan="6" class="text-center text-gray-500 py-6 italic">Memuat data...</td></tr>
    `;
    }

    function setEmpty() {
        tbody.innerHTML = `
      <tr><td colspan="6" class="text-center text-gray-500 py-6 italic">Tidak ada kunjungan hari ini.</td></tr>
    `;
    }

    function setError(msg = "Gagal memuat data.") {
        tbody.innerHTML = `
      <tr><td colspan="6" class="text-center text-red-600 py-6">${esc(
          msg
      )}</td></tr>
    `;
    }

    function loadData() {
        setLoading();

        fetch(endpoint, {
            method: "GET",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                Accept: "application/json",
            },
            credentials: "same-origin", // penting: kirim cookie session untuk Auth::id()
        })
            .then(async (res) => {
                if (!res.ok) {
                    // Tambahkan pesan spesifik untuk 401/419
                    if (res.status === 401)
                        throw new Error("Sesi berakhir. Silakan login ulang.");
                    if (res.status === 419)
                        throw new Error(
                            "Token kedaluwarsa. Muat ulang halaman."
                        );
                    throw new Error(`HTTP ${res.status}`);
                }
                return res.json();
            })
            .then((json) => {
                const data = json?.data || [];
                if (!data.length) return setEmpty();
                tbody.innerHTML = data.map(rowTpl).join("");
            })
            .catch((err) => setError(err.message));
    }

    // Handler tombol "Proses" → konfirmasi → update Waiting → Engaged
    document.addEventListener("click", async (e) => {
        const btn = e.target.closest(".btn-proses");
        if (!btn) return;

        const id = btn.dataset.id;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

        // Modal konfirmasi
        const konfirmasi = await Swal.fire({
            icon: "question",
            title: "Mulai Konsultasi?",
            html: `Status kunjungan akan diubah dari <b>Waiting</b> menjadi <b>Engaged</b>.`,
            showCancelButton: true,
            confirmButtonText: "Ya, lanjut",
            cancelButtonText: "Batal",
        });

        if (!konfirmasi.isConfirmed) return;

        // Disable tombol biar gak dobel klik
        btn.disabled = true;

        try {
            const res = await fetch(
                `/perawat/updateStatusKunjunganKeEngaged/${encodeURIComponent(
                    id
                )}`,
                {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": csrf,
                        Accept: "application/json",
                    },
                    credentials: "same-origin",
                }
            );

            if (!res.ok) {
                let msg = `HTTP ${res.status}`;
                try {
                    const j = await res.json();
                    msg = j?.message || msg;
                } catch {}
                throw new Error(msg);
            }

            const result = await res.json();
            if (result?.success) {
                await Swal.fire({
                    icon: "success",
                    title: "Berhasil!",
                    text: result?.message ?? "Status diubah menjadi Engaged.",
                    timer: 1500,
                    showConfirmButton: false,
                });
                // Refresh tabel
                loadData();
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: result?.message ?? "Gagal mengubah status.",
                });
            }
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: err.message || "Tidak dapat terhubung ke server.",
            });
        } finally {
            btn.disabled = false;
        }
    });

    loadData();

    // Opsional: auto-refresh tiap 30 detik
    // setInterval(loadData, 30000);
});
