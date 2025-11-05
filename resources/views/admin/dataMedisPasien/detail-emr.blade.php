<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Detail EMR — {{ $emr->kunjungan?->pasien?->nama_pasien ?? 'Pasien' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="color-scheme" content="light" />
    <style>
        .fade-in {
            animation: fade-in .15s ease-out both
        }

        @keyframes fade-in {
            from {
                opacity: 0;
                transform: scale(.98)
            }

            to {
                opacity: 1;
                transform: scale(1)
            }
        }

        @page {
            size: A4;
            margin: 14mm
        }

        @media print {
            .no-print {
                display: none !important
            }

            body {
                background: #fff !important
            }

            header,
            .actionbar {
                position: static !important;
                box-shadow: none !important
            }

            a[href^="http"]::after {
                content: ""
            }
        }
    </style>
    @php
        use Carbon\Carbon;
        $pasien = $emr->kunjungan?->pasien;
        $dokter = $emr->kunjungan?->poli?->dokter?->first();
        $umur = $pasien?->tanggal_lahir ? Carbon::parse($pasien->tanggal_lahir)->age : null;
    @endphp
</head>

<body class="bg-slate-50 text-slate-800 leading-relaxed antialiased">

    <!-- Actionbar -->
    <div
        class="actionbar sticky top-0 z-40 border-b border-slate-200/60 bg-white/80 backdrop-blur supports-[backdrop-filter]:bg-white/60">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-xl bg-blue-600 text-white grid place-items-center font-bold">EMR</div>
                <div>
                    <h1 class="text-base font-semibold">Detail EMR Pasien</h1>
                    <p class="text-xs text-slate-500">ID EMR: {{ $emr->id }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 no-print">
                <button id="printBtn"
                    class="px-3 py-2 rounded-lg bg-blue-600 text-white text-sm shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400">Print</button>
                <a href="{{ url()->previous() }}"
                    class="px-3 py-2 rounded-lg bg-slate-700 text-white text-sm shadow hover:bg-slate-800">Kembali</a>
            </div>
        </div>
    </div>

    <main class="max-w-5xl mx-auto p-4 md:p-6">

        {{-- Banner Pasien --}}
        <section aria-label="Identitas Pasien"
            class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
            <div class="p-6 grid md:grid-cols-[auto,1fr,auto] gap-6">
                <div class="flex items-center gap-4">
                    <img src="https://i.pravatar.cc/120?u={{ urlencode($pasien?->nama_pasien ?? 'pasien') }}"
                        alt="Foto Pasien" class="w-24 h-24 rounded-xl object-cover shadow-sm">
                    <div>
                        <h2 class="text-xl font-semibold tracking-tight">{{ $pasien?->nama_pasien ?? '-' }}</h2>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">
                                RM <strong class="font-semibold">{{ $pasien?->no_rekam_medis ?? '—' }}</strong>
                            </span>
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-blue-50 text-blue-700 px-2.5 py-1">
                                {{ $pasien?->jenis_kelamin ?? '-' }}
                            </span>
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 px-2.5 py-1">
                                {{ $umur ? $umur . ' tahun' : 'Umur -' }}
                            </span>
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-rose-50 text-rose-700 px-2.5 py-1">
                                Gol. Darah: <strong class="ml-1">{{ $pasien?->golongan_darah ?? '-' }}</strong>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-3 self-center">
                    <div class="p-4 rounded-lg bg-slate-50">
                        <p class="text-xs text-slate-500">Tanggal Lahir</p>
                        <p class="font-medium">{{ $pasien?->tanggal_lahir ?? '-' }}</p>
                        <p class="text-xs text-slate-500 mt-2">Alamat</p>
                        <p class="text-sm">{{ $pasien?->alamat ?? '-' }}</p>
                    </div>
                    <div class="p-4 rounded-lg bg-slate-50">
                        <p class="text-xs text-slate-500">Kontak</p>
                        <p class="font-medium">{{ $pasien?->no_hp ?? '-' }}</p>
                        <p class="text-xs text-slate-500 mt-2">Alergi</p>
                        <p class="text-sm">{{ $pasien?->alergi ?? 'Tidak ada/–' }}</p>
                    </div>
                </div>

                <div class="flex md:flex-col gap-2 justify-end">
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 px-3 py-1.5 text-xs">
                        Status EMR: <strong class="ml-1">{{ $emr->status ?? 'Selesai' }}</strong>
                    </span>
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-indigo-50 text-indigo-700 px-3 py-1.5 text-xs">
                        Poli: <strong class="ml-1">{{ $emr->kunjungan?->poli?->nama_poli ?? '-' }}</strong>
                    </span>
                </div>
            </div>
        </section>

        {{-- Ringkasan Kunjungan + Tanda Vital + Keluhan --}}
        <section class="mt-6 grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <h3 class="text-sm font-medium text-slate-600">Ringkasan Kunjungan</h3>
                <div class="mt-3 space-y-3">
                    <div class="p-3 rounded-lg bg-green-50">
                        <p class="text-xs text-slate-500">Tanggal Kunjungan</p>
                        <p class="font-semibold">{{ $emr->kunjungan?->tanggal_kunjungan ?? '-' }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-50">
                        <p class="text-xs text-slate-500">Dokter Penanggung Jawab</p>
                        <p class="font-medium">{{ $dokter?->nama_dokter ?? '-' }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-50">
                        <p class="text-xs text-slate-500">Keluhan Awal</p>
                        <p class="text-sm">{{ $emr->kunjungan?->keluhan_awal ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-4">
                    <h4 class="text-xs uppercase tracking-wider text-slate-500">Tanda Vital</h4>
                    <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
                        <div class="rounded-md border border-slate-200 p-2">
                            <div class="text-xs text-slate-500">TD</div>
                            <div class="font-medium">{{ $emr->tekanan_darah ?? '-' }}</div>
                        </div>
                        <div class="rounded-md border border-slate-200 p-2">
                            <div class="text-xs text-slate-500">Nadi</div>
                            <div class="font-medium">{{ $emr->nadi ?? '-' }} bpm</div>
                        </div>
                        <div class="rounded-md border border-slate-200 p-2">
                            <div class="text-xs text-slate-500">Suhu</div>
                            <div class="font-medium">{{ $emr->suhu_tubuh ?? '-' }} °C</div>
                        </div>
                        <div class="rounded-md border border-slate-200 p-2">
                            <div class="text-xs text-slate-500">RR</div>
                            <div class="font-medium">{{ $emr->pernapasan ?? '-' }} x/menit</div>
                        </div>
                        <div class="rounded-md border border-slate-200 p-2 col-span-2">
                            <div class="text-xs text-slate-500">SpO₂</div>
                            <div class="font-medium">{{ $emr->saturasi_oksigen ?? '-' }}%</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <h3 class="text-sm font-medium text-slate-600">Keluhan & Pemeriksaan</h3>
                <div class="mt-4 space-y-4">
                    <div class="p-4 rounded-lg bg-slate-50">
                        <p class="text-xs text-slate-500">Keluhan Utama</p>
                        <p class="mt-1">{{ $emr->keluhan_utama ?? '-' }}</p>
                    </div>
                    <div class="p-4 rounded-lg bg-slate-50">
                        <p class="text-xs text-slate-500">Diagnosis</p>
                        <p class="mt-1">{{ $emr->diagnosis ?? '-' }}</p>
                    </div>
                    @if (!empty($emr->catatan_dokter))
                        <div class="p-4 rounded-lg bg-slate-50">
                            <p class="text-xs text-slate-500">Catatan Dokter</p>
                            <p class="mt-1">{{ $emr->catatan_dokter }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- Resep & (opsional) Lab --}}
        <section class="mt-6 grid lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-slate-600">Resep / Obat</h3>
                    <span class="text-xs text-slate-400">Total item: {{ $emr->resep?->obat?->count() ?? 0 }}</span>
                </div>
                <div class="mt-3 overflow-hidden rounded-lg border border-slate-200">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="text-left p-3">Nama Obat</th>
                                <th class="text-left p-3">Dosis</th>
                                <th class="text-left p-3">Aturan Pakai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(($emr->resep?->obat ?? []) as $o)
                                <tr class="border-t border-slate-200">
                                    <td class="p-3">{{ $o->nama_obat ?? '-' }}</td>
                                    <td class="p-3">{{ $o->pivot->dosis ?? ($o->dosis ?? '-') }}</td>
                                    <td class="p-3">{{ $o->pivot->aturan_pakai ?? ($o->aturan_pakai ?? '-') }}</td>
                                </tr>
                            @empty
                                <tr class="border-t border-slate-200">
                                    <td class="p-3" colspan="3">Tidak ada data obat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if (!empty($emr->resep?->catatan))
                    <p class="mt-2 text-xs text-slate-500">Catatan resep: {{ $emr->resep->catatan }}</p>
                @endif
            </div>

            {{-- Jika kamu punya relasi hasil lab, render di sini. Dikosongkan karena controller belum load lab. --}}
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <h3 class="text-sm font-medium text-slate-600">Hasil Laboratorium</h3>
                <div class="mt-3 text-sm text-slate-500">
                    Belum ada data laboratorium pada EMR ini.
                </div>
            </div>
        </section>

        {{-- Riwayat medis & Lampiran (opsional) --}}
        <section class="mt-6 grid lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <h3 class="text-sm font-medium text-slate-600">Riwayat Medis</h3>
                <ul class="mt-3 list-disc ml-5 text-sm leading-7">
                    {{-- Sesuaikan dengan field yang kamu miliki / mapping ke kolom EMR --}}
                    @if (!empty($emr->riwayat_penyakit_dahulu))
                        <li>Penyakit dahulu: {{ $emr->riwayat_penyakit_dahulu }}</li>
                    @endif
                    @if (!empty($emr->riwayat_penyakit_keluarga))
                        <li>Penyakit keluarga: {{ $emr->riwayat_penyakit_keluarga }}</li>
                    @endif
                    @if (empty($emr->riwayat_penyakit_dahulu) && empty($emr->riwayat_penyakit_keluarga))
                        <li>Belum ada riwayat medis yang diisi.</li>
                    @endif
                </ul>
            </div>

            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <h3 class="text-sm font-medium text-slate-600">Lampiran</h3>
                <div class="mt-3 text-sm text-slate-500">
                    {{-- Jika punya relasi lampiran, loop di sini. Untuk demo, tiga placeholder --}}
                    <div class="grid grid-cols-3 gap-2">
                        <img src="https://via.placeholder.com/480x320.png?text=Lampiran"
                            class="rounded-lg shadow-sm object-cover w-full h-24 cursor-zoom-in lightbox-thumb"
                            alt="Lampiran">
                        <img src="https://via.placeholder.com/480x320.png?text=Lampiran"
                            class="rounded-lg shadow-sm object-cover w-full h-24 cursor-zoom-in lightbox-thumb"
                            alt="Lampiran">
                        <img src="https://via.placeholder.com/480x320.png?text=Lampiran"
                            class="rounded-lg shadow-sm object-cover w-full h-24 cursor-zoom-in lightbox-thumb"
                            alt="Lampiran">
                    </div>
                    <p class="text-xs mt-2">Klik gambar untuk memperbesar. Tekan ESC untuk menutup.</p>
                </div>
            </div>
        </section>

        <footer class="mt-6 text-xs text-slate-500">
            <p>Halaman ini menarik data dari controller (Eager Load: kunjungan.pasien, kunjungan.poli.dokter,
                resep.obat).</p>
        </footer>
    </main>

    <script>
        // Print
        document.getElementById('printBtn')?.addEventListener('click', () => window.print());

        // Simple Lightbox
        const openLightbox = (src, alt = '') => {
            const overlay = document.createElement('div');
            overlay.className = 'fixed inset-0 z-[100] bg-black/70 grid place-items-center p-4';
            overlay.style.cursor = 'zoom-out';

            const img = document.createElement('img');
            img.src = src;
            img.alt = alt || 'Lampiran';
            img.className = 'max-w-[90vw] max-h-[90vh] rounded-xl shadow-2xl fade-in';
            overlay.appendChild(img);

            const close = () => overlay.remove();
            overlay.addEventListener('click', close);
            document.addEventListener('keydown', function esc(e) {
                if (e.key === 'Escape') {
                    close();
                    document.removeEventListener('keydown', esc);
                }
            });

            document.body.appendChild(overlay);
        };

        document.querySelectorAll('.lightbox-thumb').forEach(el => {
            el.addEventListener('click', () => openLightbox(el.src, el.alt));
        });
    </script>
</body>

</html>
