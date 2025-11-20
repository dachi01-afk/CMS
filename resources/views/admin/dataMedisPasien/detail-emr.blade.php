<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Riwayat EMR - {{ $pasien->nama_pasien ?? '-' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
    <link href='{{ asset('storage/assets/royal_klinik.svg') }}' rel='shortcut icon'>

    {{-- Font Awesome (kalau belum ada di layout utama) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2Pk6QZ8C2ZKX1LsY5cqK8QxSNaben7nuPhYu95LjAqS4I2o5I1pVdFQmBw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body class="min-h-screen bg-gradient-to-b from-slate-100 to-slate-200">

    <div class="max-w-7xl mx-auto px-4 py-8">

        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
            <div>
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold mb-2">
                    <i class="fa-solid fa-notes-medical"></i>
                    <span>Riwayat Electronic Medical Record</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-800">
                    EMR Pasien {{ $pasien->nama_pasien ?? '-' }}
                </h1>
                <p class="text-sm text-slate-600 mt-1">
                    No EMR:
                    <span class="font-semibold text-slate-800">
                        {{ $pasien->no_emr ?? '-' }}
                    </span>
                </p>
            </div>

            <div class="flex flex-col items-start md:items-end text-xs text-slate-500">
                <span>Jumlah kunjungan dengan EMR:</span>
                <span class="mt-1 text-lg font-semibold text-slate-800">
                    {{ $emrList->count() }} kunjungan
                </span>
            </div>
        </div>

        {{-- SECTION 1 : RINGKASAN DATA PASIEN --}}
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-sm border border-slate-100 mb-6">

            {{-- HEADER DALAM CARD (mirip No.2) --}}
            <div class="px-5 pt-5 pb-3 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-base md:text-lg font-semibold text-slate-800 flex items-center gap-2">
                    <span
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-sky-100 text-sky-700 text-xs font-bold">
                        1
                    </span>
                    Ringkasan Data Pasien
                </h2>
                <span class="text-xs text-slate-500">
                    Terakhir diperbarui: {{ now()->translatedFormat('d F Y') }}
                </span>
            </div>

            {{-- ISI CARD: 3 BOX RINGKASAN --}}
            <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">

                {{-- BOX 1: PASIEN --}}
                <div
                    class="relative flex items-start gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200 shadow-sm">
                    <div class="h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-user text-indigo-700 text-lg">A</i>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase text-slate-500 font-medium">Pasien</p>
                        <p class="font-semibold text-slate-900 text-sm">
                            {{ $pasien->nama_pasien ?? '-' }}
                        </p>
                        <p class="text-xs text-slate-600">
                            NIK: {{ $pasien->nik ?? '-' }}
                        </p>
                    </div>
                </div>

                {{-- BOX 2: JENIS KELAMIN & TGL LAHIR --}}
                <div
                    class="relative flex items-start gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200 shadow-sm">
                    <div class="h-10 w-10 bg-sky-100 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-venus-mars text-sky-700 text-lg">B</i>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase text-slate-500 font-medium">Jenis Kelamin & Tanggal Lahir</p>
                        <p class="font-semibold text-slate-900 text-sm">
                            {{ $pasien->jenis_kelamin ?? '-' }}
                        </p>
                        <p class="text-xs text-slate-600">
                            Tanggal lahir:
                            {{ optional($pasien->tanggal_lahir)->translatedFormat('d F Y') ?? '-' }}
                        </p>
                    </div>
                </div>

                {{-- BOX 3: KONTAK --}}
                <div
                    class="relative flex items-start gap-3 bg-slate-50 p-4 rounded-xl border border-slate-200 shadow-sm">
                    <div class="h-10 w-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                        <i class="fa-solid fa-phone text-emerald-700 text-lg">C</i>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase text-slate-500 font-medium">Kontak</p>
                        <p class="font-semibold text-slate-900 text-sm">
                            {{ $pasien->no_hp_pasien ?? '-' }}
                        </p>
                        <p class="text-xs text-slate-600">
                            Alamat: {{ $pasien->alamat ?? '-' }}
                        </p>
                    </div>
                </div>

            </div>
        </div>


        {{-- TABEL RIWAYAT EMR --}}
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-sm border border-slate-100">
            <div class="px-5 pt-5 pb-3 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-base md:text-lg font-semibold text-slate-800 flex items-center gap-2">
                    <span
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-sky-100 text-sky-700 text-xs font-bold">
                        2
                    </span>
                    Riwayat EMR Pasien
                </h2>
                <span class="text-xs text-slate-500">
                    Total {{ $emrList->count() }} kunjungan
                </span>
            </div>

            <div class="p-4 overflow-x-auto">
                <table class="min-w-full text-sm text-left text-slate-700">
                    <thead class="text-xs uppercase bg-sky-500 text-white">
                        <tr>
                            <th class="px-4 py-3">No</th>
                            <th class="px-4 py-3">Tanggal Kunjungan</th>
                            <th class="px-4 py-3">Poli</th>
                            <th class="px-4 py-3">Dokter</th>
                            <th class="px-4 py-3">Keluhan Utama</th>
                            <th class="px-4 py-3">Diagnosis</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($emrList as $index => $emr)
                            <tr class="bg-white border-b hover:bg-slate-50">
                                <td class="px-4 py-3 align-top">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-4 py-3 align-top whitespace-nowrap">
                                    {{ optional($emr->kunjungan?->tanggal_kunjungan ?? $emr->created_at)->translatedFormat('d F Y') }}
                                </td>
                                <td class="px-4 py-3 align-top whitespace-nowrap">
                                    {{ $emr->poli->nama_poli ?? '-' }}
                                </td>
                                <td class="px-4 py-3 align-top whitespace-nowrap">
                                    {{ $emr->dokter->nama_dokter ?? '-' }}
                                </td>
                                <td class="px-4 py-3 align-top">
                                    {{ \Illuminate\Support\Str::limit($emr->keluhan_utama ?? '-', 50) }}
                                </td>
                                <td class="px-4 py-3 align-top">
                                    {{ \Illuminate\Support\Str::limit($emr->diagnosis ?? '-', 50) }}
                                </td>
                                <td class="px-4 py-3 align-top text-center">
                                    <a href="{{ route('data_medis_pasien.detail.emr.pasien', $emr->pasien->no_emr) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-medium
                                          bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-100">
                                        <i class="fa-solid fa-circle-info text-xs"></i>
                                        <span>Lihat Detail EMR Pasien</span>
                                    </a>
                                    {{-- 
                                    Nanti kamu bisa ganti URL di atas ke route yang kamu mau,
                                    misal: route('data_medis_pasien.show_emr', $emr->id)
                                --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-slate-500">
                                    Belum ada data EMR untuk pasien ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- FOOTER BUTTON --}}
            <div class="flex justify-between items-center px-5 py-4 border-t border-slate-100">
                <a href="{{ route('data_medis_pasien.index') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-400 transition">
                    <i class="fa-solid fa-arrow-left text-xs"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>

    </div>

</body>

</html>
