document.addEventListener("DOMContentLoaded", () => {
    const tbody = document.getElementById("waitingBody");
    const endpoint = "/perawat/getDataKunjunganHariIni"; // atau route() jika kamu expose via window.routes

    function rowTpl(r) {
        return `
      <tr class="border-b hover:bg-gray-50">
        <td class="px-6 py-3">${r.no_antrian ?? "-"}</td>
        <td class="px-6 py-3">${r.nama_pasien}</td>
        <td class="px-6 py-3">${r.dokter}</td>
        <td class="px-6 py-3">${r.poli}</td>
        <td class="px-6 py-3">${r.keluhan}</td>
        <td class="px-6 py-3 text-center">
          <button class="btn-proses px-3 py-1 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700"
                  data-id="${r.kunjungan_id}">
            Proses
          </button>
        </td>
      </tr>`;
    }

    function loadData() {
        tbody.innerHTML = `
      <tr><td colspan="6" class="text-center text-gray-500 py-6 italic">Memuat data...</td></tr>
    `;

        fetch(endpoint, {
            method: "GET",
            headers: { "X-Requested-With": "XMLHttpRequest" },
        })
            .then((res) => (res.ok ? res.json() : Promise.reject()))
            .then((json) => {
                const data = json?.data || [];
                if (!data.length) {
                    tbody.innerHTML = `
          <tr><td colspan="6" class="text-center text-gray-500 py-6 italic">Tidak ada kunjungan hari ini.</td></tr>
        `;
                    return;
                }
                tbody.innerHTML = data.map(rowTpl).join("");
            })
            .catch(() => {
                tbody.innerHTML = `
        <tr><td colspan="6" class="text-center text-red-600 py-6">Gagal memuat data.</td></tr>
      `;
            });
    }

    // handler tombol "Proses" (sesuaikan aksi yang kamu mau)
    document.addEventListener("click", (e) => {
        const btn = e.target.closest(".btn-proses");
        if (!btn) return;
        const id = btn.dataset.id;
        // TODO: buka modal / redirect / panggil endpoint lain
        console.log("Proses kunjungan:", id);
    });

    loadData();

    // (Opsional) auto-refresh tiap 30 detik
    // setInterval(loadData, 30000);
});
