<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Detail EMR Pasien {{ $emr->pasien->nama_pasien ?? '-' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')

    {{-- Font Awesome (kalau belum dimasukkan di layout utama) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2Pk6QZ8C2ZKX1LsY5cqK8QxSNaben7nuPhYu95LjAqS4I2o5I1pVdFQmBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body class="min-h-screen bg-gradient-to-b from-slate-100 to-slate-200">

    <div class="max-w-5xl mx-auto px-4 py-8">

        {{-- HEADER --}}
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold mb-2">
                    <i class="fa-solid fa-notes-medical"></i>
                    <span>Detail Electronic Medical Record</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-800">
                    EMR Pasien {{ $emr->pasien->nama_pasien ?? '-' }}
                </h1>
                <p class="text-sm text-slate-600 mt-1">
                    No EMR:
                    <span class="font-semibold text-slate-800">
                        {{ $emr->pasien->no_emr ?? '-' }}
                    </span>
                </p>
            </div>

            <div class="hidden md:flex flex-col items-end text-xs text-slate-500">
                <span>Terakhir diperbarui</span>
                <span class="mt-1 font-semibold text-slate-700">
                    {{ optional($emr->updated_at ?? $emr->created_at)->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }}
                </span>
            </div>
        </div>

        {{-- RINGKASAN PASIEN --}}
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-sm border border-slate-100 mb-6">
            <div class="p-4 md:p-5 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="flex items-start gap-3">
                    <div class="h-9 w-9 rounded-full bg-indigo-50 flex items-center justify-center">
                        <i class="fa-regular fa-user text-indigo-600 text-sm"></i>
                    </div>
                    <div>
                        <div class="text-[11px] uppercase tracking-wide text-slate-400">Pasien</div>
                        <div class="font-semibold text-slate-800">
                            {{ $emr->pasien->nama_pasien ?? '-' }}
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            NIK: {{ $emr->pasien->nik ?? '-' }}
                        </div>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="h-9 w-9 rounded-full bg-sky-50 flex items-center justify-center">
                        <i class="fa-solid fa-venus-mars text-sky-600 text-sm"></i>
                    </div>
                    <div>
                        <div class="text-[11px] uppercase tracking-wide text-slate-400">Jenis Kelamin & Umur</div>
                        <div class="font-semibold text-slate-800">
                            {{ $emr->pasien->jenis_kelamin ?? '-' }}
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Tanggal lahir:
                            {{ optional($emr->pasien->tanggal_lahir)->translatedFormat('d F Y') ?? '-' }}
                        </div>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div class="h-9 w-9 rounded-full bg-emerald-50 flex items-center justify-center">
                        <i class="fa-solid fa-stethoscope text-emerald-600 text-sm"></i>
                    </div>
                    <div>
                        <div class="text-[11px] uppercase tracking-wide text-slate-400">Dokter Penanggung Jawab</div>
                        <div class="font-semibold text-slate-800">
                            {{ $emr->dokter->nama_dokter ?? '-' }}
                        </div>
                        <div class="text-xs text-slate-500 mt-0.5">
                            Poli: {{ $emr->poli->nama_poli ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- DETAIL KUNJUNGAN & KELUHAN --}}
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-sm border border-slate-100 mb-6">
            <div class="px-5 pt-5 pb-3 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-base md:text-lg font-semibold text-slate-800 flex items-center gap-2">
                    <span
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-sky-100 text-sky-700 text-xs font-bold">
                        1
                    </span>
                    Informasi Kunjungan & Keluhan
                </h2>
            </div>

            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-[11px] uppercase tracking-wide text-slate-400 mb-1">
                        Tanggal Kunjungan
                    </div>
                    <div class="font-medium text-slate-800">
                        {{ optional($emr->kunjungan?->tanggal_kunjungan ?? $emr->created_at)->translatedFormat('d F Y') }}
                    </div>
                </div>

                <div>
                    <div class="text-[11px] uppercase tracking-wide text-slate-400 mb-1">
                        Keluhan Awal
                    </div>
                    <div class="text-slate-800">
                        {{ $emr->kunjungan->keluhan_awal ?? '-' }}
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div class="text-[11px] uppercase tracking-wide text-slate-400 mb-1">
                        Keluhan Utama
                    </div>
                    <div class="text-slate-800">
                        {{ $emr->keluhan_utama ?? '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-[11px] uppercase tracking-wide text-slate-400 mb-1">
                        Riwayat Penyakit Dahulu
                    </div>
                    <div class="text-slate-800 whitespace-pre-line">
                        {{ $emr->riwayat_penyakit_dahulu ?? '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-[11px] uppercase tracking-wide text-slate-400 mb-1">
                        Riwayat Penyakit Keluarga
                    </div>
                    <div class="text-slate-800 whitespace-pre-line">
                        {{ $emr->riwayat_penyakit_keluarga ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- VITAL SIGN & DIAGNOSIS --}}
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-sm border border-slate-100 mb-6">
            <div class="px-5 pt-5 pb-3 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-base md:text-lg font-semibold text-slate-800 flex items-center gap-2">
                    <span
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold">
                        2
                    </span>
                    Vital Sign & Diagnosis
                </h2>
            </div>

            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-[11px] uppercase tracking-wide text-slate-400 mb-1">
                        Tekanan Darah (mmHg)
                    </div>
                    <div class="font-medium text-slate-800">
                        {{ $emr->tekanan_darah ?? '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-[11px] uppercase tracking-wide text-slate-400 mb-1">
                        Suhu Tubuh (°C)
                    </div>
                    <div class="font-medium text-slate-800">
                        {{ $emr->suhu_tubuh ?? '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-[11px] uppercase tracking-wide text-slate-400 mb-1">
                        Nadi (bpm)
                    </div>
                    <div class="font-medium text-slate-800">
                        {{ $emr->nadi ?? '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-[11px] uppercase tracking-wide text-slate-400 mb-1">
                        Frekuensi Napas (x/menit)
                    </div>
                    <div class="font-medium text-slate-800">
                        {{ $emr->pernapasan ?? '-' }}
                    </div>
                </div>

                <div>
                    <div class="text-[11px] uppercase tracking-wide text-slate-400 mb-1">
                        Saturasi Oksigen (SpO₂, %)
                    </div>
                    <div class="font-medium text-slate-800">
                        {{ $emr->saturasi_oksigen ?? '-' }}
                    </div>
                </div>

                <div class="md:col-span-2">
                    <div class="text-[11px] uppercase tracking-wide text-slate-400 mb-1">
                        Diagnosis
                    </div>
                    <div class="text-slate-800 whitespace-pre-line">
                        {{ $emr->diagnosis ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- FOOTER BUTTONS --}}
        <div class="flex justify-between items-center mt-4">
            <a href="{{ url()->previous() }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-400 transition">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Kembali</span>
            </a>

            {{-- (Opsional) tombol cetak / export --}}
            {{-- <button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 transition">
                <i class="fa-solid fa-print text-xs"></i>
                <span>Cetak EMR</span>
            </button> --}}
        </div>

    </div>

</body>

</html>
