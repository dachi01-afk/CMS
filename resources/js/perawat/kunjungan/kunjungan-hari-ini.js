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

    // Handler tombol "Proses"
    document.addEventListener("click", (e) => {
        const btn = e.target.closest(".btn-proses");
        if (!btn) return;
        const id = btn.dataset.id;
        // TODO: buka modal / redirect / panggil endpoint lain
        console.log("Proses kunjungan:", id);
    });

    loadData();

    // Opsional: auto-refresh tiap 30 detik
    // setInterval(loadData, 30000);
});
