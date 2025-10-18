<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Detail EMR Pasien — Contoh Statis</title>
    <!-- Tailwind CDN untuk contoh cepat -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50 text-slate-800 leading-relaxed">
    <div class="max-w-4xl mx-auto p-6">
        <header class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Detail EMR Pasien</h1>
                <p class="text-sm text-slate-500">Halaman contoh statis — data hanya demo</p>
            </div>
            <div class="flex items-center gap-2">
                <button id="printBtn" class="px-3 py-1 rounded-md bg-blue-600 text-white text-sm shadow">Print</button>
                <button id="editBtn" class="px-3 py-1 rounded-md bg-amber-500 text-white text-sm shadow">Edit</button>
            </div>
        </header>

        <main class="mt-6 bg-white rounded-2xl shadow p-6">
            <!-- Top: Identitas Pasien -->
            <section class="grid grid-cols-3 gap-6 items-center">
                <div class="col-span-1 flex items-center gap-4">
                    <img src="https://i.pravatar.cc/100?img=47" alt="Foto Pasien"
                        class="w-24 h-24 rounded-lg object-cover shadow" />
                    <div>
                        <h2 class="text-lg font-medium">Ahmad Sulaiman</h2>
                        <p class="text-sm text-slate-600">No. Rekam Medis: <span
                                class="font-semibold">RM-2025-00123</span></p>
                        <p class="text-sm text-slate-600">Jenis Kelamin: <span class="font-semibold">Laki-laki</span>
                        </p>
                    </div>
                </div>

                <div class="col-span-2 grid grid-cols-2 gap-4">
                    <div class="p-4 bg-slate-50 rounded-lg">
                        <p class="text-xs text-slate-500">Tanggal Lahir</p>
                        <p class="font-medium">1985-06-12 (40 tahun)</p>
                        <p class="text-xs text-slate-500 mt-2">Alamat</p>
                        <p>Jl. Merpati No. 12, Jakarta Selatan</p>
                    </div>

                    <div class="p-4 bg-slate-50 rounded-lg">
                        <p class="text-xs text-slate-500">Kontak</p>
                        <p class="font-medium">0812-3456-7890</p>
                        <p class="text-xs text-slate-500 mt-2">Golongan Darah</p>
                        <p>B+</p>
                    </div>
                </div>
            </section>

            <!-- Divider -->
            <div class="my-6 border-t border-slate-100"></div>

            <!-- Ringkasan Kunjungan -->
            <section class="grid grid-cols-3 gap-6">
                <div class="col-span-1">
                    <h3 class="text-sm text-slate-500 mb-2">Kunjungan</h3>
                    <div class="space-y-2">
                        <div class="p-3 bg-green-50 rounded-lg">
                            <p class="text-xs text-slate-500">Tanggal</p>
                            <p class="font-semibold">2025-10-16</p>
                            <p class="text-xs text-slate-500 mt-1">Poli</p>
                            <p>Poli Umum</p>
                        </div>

                        <div class="p-3 bg-slate-50 rounded-lg">
                            <p class="text-xs text-slate-500">Dokter</p>
                            <p>Dr. Siti Nurhayati, Sp.OG</p>
                            <p class="text-xs text-slate-500 mt-1">Status</p>
                            <p class="text-sm font-medium text-emerald-600">Selesai</p>
                        </div>
                    </div>
                </div>

                <div class="col-span-2">
                    <h3 class="text-sm text-slate-500 mb-2">Keluhan & Pemeriksaan</h3>
                    <div class="p-4 bg-slate-50 rounded-lg space-y-3">
                        <div>
                            <p class="text-xs text-slate-500">Keluhan Utama</p>
                            <p>Demam, nyeri kepala, dan mual sejak 2 hari yang lalu.</p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-500">Pemeriksaan Fisik</p>
                            <ul class="list-disc ml-5 text-sm">
                                <li>Tekanan darah: <strong>130/85 mmHg</strong></li>
                                <li>Nadi: <strong>86 bpm</strong></li>
                                <li>Suhu: <strong>38.2 °C</strong></li>
                                <li>Pernafasan: <strong>20 x/menit</strong></li>
                            </ul>
                        </div>

                        <div>
                            <p class="text-xs text-slate-500">Catatan Dokter</p>
                            <p>Diduga infeksi saluran pernapasan atas. Rekomendasi: pemeriksaan laboratorium dan
                                observasi 24 jam.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Divider -->
            <div class="my-6 border-t border-slate-100"></div>

            <!-- Obat & Resep, Hasil Lab -->
            <section class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm text-slate-500 mb-2">Resep / Obat</h3>
                    <div class="p-4 bg-slate-50 rounded-lg">
                        <table class="w-full text-sm">
                            <thead class="text-left text-slate-600">
                                <tr>
                                    <th class="pb-2">Nama Obat</th>
                                    <th class="pb-2">Dosis</th>
                                    <th class="pb-2">Aturan Pakai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Paracetamol 500 mg</td>
                                    <td>1 tablet</td>
                                    <td>3x sehari jika diperlukan</td>
                                </tr>
                                <tr class="bg-white">
                                    <td>Amoxicillin 500 mg</td>
                                    <td>1 kapsul</td>
                                    <td>3x sehari selama 7 hari</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm text-slate-500 mb-2">Hasil Laboratorium</h3>
                    <div class="p-4 bg-slate-50 rounded-lg">
                        <table class="w-full text-sm">
                            <tbody>
                                <tr>
                                    <td class="text-xs text-slate-500">Hemoglobin</td>
                                    <td class="text-right font-medium">13.4 g/dL</td>
                                </tr>
                                <tr>
                                    <td class="text-xs text-slate-500">Leukosit</td>
                                    <td class="text-right font-medium">11.2 x10^3/uL</td>
                                </tr>
                                <tr>
                                    <td class="text-xs text-slate-500">Trombosit</td>
                                    <td class="text-right font-medium">250 x10^3/uL</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Divider -->
            <div class="my-6 border-t border-slate-100"></div>

            <!-- Riwayat medis & Lampiran -->
            <section class="grid grid-cols-2 gap-6">
                <div>
                    <h3 class="text-sm text-slate-500 mb-2">Riwayat Medis</h3>
                    <div class="p-4 bg-slate-50 rounded-lg">
                        <ul class="list-disc ml-5 text-sm">
                            <li>Diabetes Mellitus — terkontrol</li>
                            <li>Hipertensi ringan</li>
                            <li>Tidak ada riwayat alergi obat</li>
                        </ul>
                    </div>
                </div>

                <div>
                    <h3 class="text-sm text-slate-500 mb-2">Lampiran (Foto / Hasil Imaging)</h3>
                    <div class="p-4 bg-slate-50 rounded-lg space-y-2">
                        <div class="flex gap-2">
                            <img src="https://via.placeholder.com/120x80.png?text=Foto1" alt="lampiran1"
                                class="rounded-md shadow-sm object-cover w-32 h-20" />
                            <img src="https://via.placeholder.com/120x80.png?text=Foto2" alt="lampiran2"
                                class="rounded-md shadow-sm object-cover w-32 h-20" />
                            <img src="https://via.placeholder.com/120x80.png?text=Foto3" alt="lampiran3"
                                class="rounded-md shadow-sm object-cover w-32 h-20" />
                        </div>
                        <p class="text-xs text-slate-500">Klik gambar untuk memperbesar (demo statis)</p>
                    </div>
                </div>
            </section>

            <!-- Footer kecil -->
            <footer class="mt-6 text-xs text-slate-500">
                <p>Catatan: Halaman ini adalah contoh statis untuk tujuan demonstrasi UI — tidak terkoneksi ke backend.
                </p>
            </footer>
        </main>
    </div>

    <script>
        // Print button
        document.getElementById('printBtn').addEventListener('click', function() {
            window.print();
        });

        // Edit button (demo)
        document.getElementById('editBtn').addEventListener('click', function() {
            alert('Tombol Edit ditekan — untuk demo statis ini belum terhubung ke backend.');
        });

        // Klik gambar untuk memperbesar (lightbox sederhana)
        document.querySelectorAll('img').forEach(img => {
            img.style.cursor = 'pointer';
            img.addEventListener('click', function() {
                const src = this.src;
                const overlay = document.createElement('div');
                overlay.style =
                    `position:fixed; inset:0; background:rgba(0,0,0,0.6); display:flex; align-items:center; justify-content:center; z-index:9999;`;
                const big = document.createElement('img');
                big.src = src;
                big.style =
                    'max-width:90%; max-height:90%; border-radius:8px; box-shadow:0 8px 30px rgba(2,6,23,0.6)';
                overlay.appendChild(big);
                overlay.addEventListener('click', () => overlay.remove());
                document.body.appendChild(overlay);
            });
        });
    </script>
</body>

</html>
