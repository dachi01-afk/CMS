<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Detail EMR Pasien</title>
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
                background: white !important
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
</head>

<body class="bg-slate-50 text-slate-800 leading-relaxed antialiased">

    {{-- Top Actionbar --}}
    <div
        class="actionbar sticky top-0 z-40 border-b border-slate-200/60 bg-white/80 backdrop-blur supports-[backdrop-filter]:bg-white/60">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-xl bg-blue-600 text-white grid place-items-center font-bold">EMR</div>
                <div>
                    <h1 class="text-base font-semibold">Detail EMR Pasien</h1>
                    <p class="text-xs text-slate-500">No. EMR: <span class="font-medium">{{ $emr->id }}</span></p>
                </div>
            </div>
            <div class="flex items-center gap-2 no-print">
                <button id="printBtn"
                    class="px-3 py-2 rounded-lg bg-blue-600 text-white text-sm shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400">Print</button>
                {{-- contoh tombol edit (opsional) --}}
                {{-- <a href="{{ route('data_medis_pasien.edit_emr', $emr->id) }}" class="px-3 py-2 rounded-lg bg-amber-500 text-white text-sm shadow hover:bg-amber-600">Edit</a> --}}
            </div>
        </div>
    </div>

    <main class="max-w-5xl mx-auto p-4 md:p-6">

        {{-- Patient Banner --}}
        <section aria-label="Identitas Pasien"
            class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 overflow-hidden">
            <div class="p-6 grid md:grid-cols-[auto,1fr,auto] gap-6">
                <div class="flex items-center gap-4">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($pasien->nama_pasien ?? 'Pasien') }}&size=120&background=0D8ABC&color=fff"
                        alt="Foto Pasien" class="w-24 h-24 rounded-xl object-cover shadow-sm">
                    <div>
                        <h2 class="text-xl font-semibold tracking-tight">{{ $pasien->nama_pasien ?? '-' }}</h2>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">
                                RM <strong class="font-semibold ml-1">{{ $pasien->no_rm ?? '—' }}</strong>
                            </span>
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-blue-50 text-blue-700 px-2.5 py-1">
                                {{ $pasien->jenis_kelamin ?? '-' }}
                            </span>
                            @php
                                $umur = isset($pasien->tanggal_lahir)
                                    ? \Carbon\Carbon::parse($pasien->tanggal_lahir)->age . ' th'
                                    : '-';
                            @endphp
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 px-2.5 py-1">
                                {{ $umur }}
                            </span>
                            <span
                                class="inline-flex items-center gap-1 rounded-full bg-rose-50 text-rose-700 px-2.5 py-1">
                                Gol. Darah: <strong class="ml-1">{{ $pasien->gol_darah ?? '-' }}</strong>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-3 self-center">
                    <div class="p-4 rounded-lg bg-slate-50">
                        <p class="text-xs text-slate-500">Tanggal Lahir</p>
                        <p class="font-medium">{{ $pasien->tanggal_lahir ?? '-' }}</p>
                        <p class="text-xs text-slate-500 mt-2">Alamat</p>
                        <p class="text-sm">{{ $pasien->alamat ?? '-' }}</p>
                    </div>
                    <div class="p-4 rounded-lg bg-slate-50">
                        <p class="text-xs text-slate-500">Kontak</p>
                        <p class="font-medium">{{ $pasien->no_hp ?? '-' }}</p>
                        <p class="text-xs text-slate-500 mt-2">Alergi</p>
                        <p class="text-sm">{{ $pasien->alergi ?? '-' }}</p>
                    </div>
                </div>

                <div class="flex md:flex-col gap-2 justify-end">
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-indigo-50 text-indigo-700 px-3 py-1.5 text-xs">
                        Poli: <strong class="ml-1">{{ $poli->nama_poli ?? '-' }}</strong>
                    </span>
                    <span
                        class="inline-flex items-center gap-1 rounded-full bg-emerald-50 text-emerald-700 px-3 py-1.5 text-xs">
                        Dokter: <strong class="ml-1">{{ $dokter->nama_dokter ?? '-' }}</strong>
                    </span>
                </div>
            </div>
        </section>

        {{-- Encounter Summary --}}
        <section class="mt-6 grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <h3 class="text-sm font-medium text-slate-600">Ringkasan Kunjungan</h3>
                <div class="mt-3 space-y-3">
                    <div class="p-3 rounded-lg bg-green-50">
                        <p class="text-xs text-slate-500">Tanggal</p>
                        <p class="font-semibold">
                            {{ \Carbon\Carbon::parse($kunjungan->tanggal_kunjungan ?? now())->translatedFormat('d F Y') }}
                        </p>
                    </div>
                    <div class="p-3 rounded-lg bg-slate-50">
                        <p class="text-xs text-slate-500">Nomor Antrian</p>
                        <p class="font-medium">{{ $kunjungan->no_antrian ?? '-' }}</p>
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
                            <div class="font-medium">
                                @if (!is_null($emr->suhu_tubuh))
                                    {{ number_format($emr->suhu_tubuh, 1) }} °C
                                @else
                                    -
                                @endif
                            </div>
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
                        <p class="text-xs text-slate-500">Keluhan Awal</p>
                        <p class="mt-1">{{ $kunjungan->keluhan_awal ?? '-' }}</p>
                    </div>
                    <div class="p-4 rounded-lg bg-slate-50">
                        <p class="text-xs text-slate-500">Keluhan Utama</p>
                        <p class="mt-1">{{ $emr->keluhan_utama ?? '-' }}</p>
                    </div>
                    <div class="p-4 rounded-lg bg-slate-50">
                        <p class="text-xs text-slate-500">Diagnosis</p>
                        <p class="mt-1 whitespace-pre-line">{{ $emr->diagnosis ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Rx & Labs --}}
        <section class="mt-6 grid lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-slate-600">Resep / Obat</h3>
                    <span class="text-xs text-slate-400">Total item: {{ count($obatItems) }}</span>
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
                            @forelse($obatItems as $o)
                                <tr class="border-t border-slate-200">
                                    <td class="p-3">{{ $o['nama'] }}</td>
                                    <td class="p-3">{{ $o['dosis'] }}</td>
                                    <td class="p-3">{{ $o['aturan_pakai'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="p-3 text-slate-500" colspan="3">Tidak ada obat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-sm ring-1 ring-slate-200 p-5">
                <h3 class="text-sm font-medium text-slate-600">Riwayat & Catatan</h3>
                <div class="mt-3 space-y-3">
                    <div class="p-4 rounded-lg bg-slate-50">
                        <p class="text-xs text-slate-500">Riwayat Penyakit Dahulu</p>
                        <p class="mt-1 whitespace-pre-line">{{ $emr->riwayat_penyakit_dahulu ?? '-' }}</p>
                    </div>
                    <div class="p-4 rounded-lg bg-slate-50">
                        <p class="text-xs text-slate-500">Riwayat Penyakit Keluarga</p>
                        <p class="mt-1 whitespace-pre-line">{{ $emr->riwayat_penyakit_keluarga ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <footer class="mt-6 text-xs text-slate-500">
            <p>Dicetak pada {{ now('Asia/Jakarta')->translatedFormat('d F Y H.i') }} WIB</p>
        </footer>
    </main>

    <script>
        document.getElementById('printBtn')?.addEventListener('click', () => window.print());
    </script>
</body>

</html>
