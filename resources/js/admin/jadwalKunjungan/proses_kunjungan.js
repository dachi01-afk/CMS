document.addEventListener('DOMContentLoaded', () => {
    loadWaitingList();

     const menuProses = document.getElementById('menuProsesKunjungan');
    if (menuProses) {
        menuProses.addEventListener('click', (e) => {
            e.preventDefault(); // cegah reload halaman
            loadWaitingList(); // refresh tabel
        });
    }

    async function loadWaitingList() {
        const tbody = document.getElementById('waitingBody');
        tbody.innerHTML = `<tr><td colspan="5" class="text-center py-6 text-gray-500 italic">Memuat data...</td></tr>`;

        const res = await fetch('/jadwal_kunjungan/waiting');
        const data = await res.json();

        if (data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-6 text-gray-500 italic">Tidak ada kunjungan pending hari ini.</td></tr>`;
            return;
        }

        tbody.innerHTML = '';
        data.forEach(item => {
            const row = document.createElement('tr');
            row.className = "border-b hover:bg-indigo-50 transition";

            row.innerHTML = `
                <td class="px-6 py-3 font-semibold">${item.no_antrian}</td>
                <td class="px-6 py-3">${item.pasien.nama_pasien}</td>
                <td class="px-6 py-3">${item.dokter.nama_dokter}</td>
                <td class="px-6 py-3">${item.keluhan_awal}</td>
                <td class="px-6 py-3 text-center">
                    <button data-id="${item.id}" class="ubahStatusBtn bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg text-xs">Mulai Konsultasi</button>
                </td>
            `;
            tbody.appendChild(row);
        });

        // event listener untuk tombol ubah status
        document.querySelectorAll('.ubahStatusBtn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const id = btn.dataset.id;

                const confirm = await Swal.fire({
                    icon: 'question',
                    title: 'Mulai konsultasi?',
                    text: 'Status akan diubah menjadi "Engaged".',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, ubah',
                    cancelButtonText: 'Batal',
                });

                if (confirm.isConfirmed) {
                    const res = await fetch(`/jadwal_kunjungan/update-status/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                    
                    const result = await res.json();

                    console.log(result);

                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: result.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        loadWaitingList();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: result.message,
                        });
                    }
                }
            });
        });
    }
});
