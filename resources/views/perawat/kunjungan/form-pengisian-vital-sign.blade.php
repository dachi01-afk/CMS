<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Input Vital Sign EMR #{{ $emr->id ?? '-' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="min-h-screen bg-gradient-to-b from-slate-100 to-slate-200">
    <div class="mx-auto max-w-4xl px-4 py-8">

        {{-- HEADER HALAMAN --}}
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-semibold mb-2">
                    <i class="fa-solid fa-heart-pulse"></i>
                    <span>EMR Vital Sign</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-800">Input Vital Sign Pasien</h1>
                <p class="text-sm text-slate-600 mt-1">
                    No EMR:
                    <span class="font-semibold text-slate-800">
                        {{ $dataPasien->no_emr ?? '-' }}
                    </span>
                </p>
            </div>

            {{-- Badge status kecil (kalau mau dikembangkan nanti) --}}
            <div class="hidden md:flex flex-col items-end text-right text-xs text-slate-500">
                <span>Dientri oleh Perawat</span>
                <span class="mt-1 px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                    Form Aktif
                </span>
            </div>
        </div>

        {{-- RINGKASAN PASIEN / POLI / DOKTER --}}
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-sm border border-slate-100 mb-6">
            <div class="p-4 md:p-5 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div class="flex items-start gap-3">
                    <div>
                        <div class="text-[11px] uppercase tracking-wide text-slate-400">Pasien</div>
                        <div class="font-semibold text-slate-800">
                            {{ $dataPasien->nama_pasien ?? '-' }}
                        </div>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div>
                        <div class="text-[11px] uppercase tracking-wide text-slate-400">Poli</div>
                        <div class="font-semibold text-slate-800">
                            {{ $dataPoliPasien->nama_poli ?? '-' }}
                        </div>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <div>
                        <div class="text-[11px] uppercase tracking-wide text-slate-400">Dokter Penanggung Jawab</div>
                        <div class="font-semibold text-slate-800">
                            {{ $dataDokterPasien->nama_dokter ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- FORM VITAL SIGN --}}
        <div class="bg-white/95 backdrop-blur-sm rounded-2xl shadow-sm border border-slate-100">
            <div class="px-5 pt-5 pb-2 border-b border-slate-100">
                <h2 class="text-base md:text-lg font-semibold text-slate-800 flex items-center gap-2">
                    <span
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold">1</span>
                    Parameter Vital Sign
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Isi nilai vital sign pasien sesuai hasil pengukuran aktual.
                </p>
            </div>

            <form id="vital-emr-form" action="{{ route('perawat.submit.data.vital.sign.pasien', $dataIdEMR) }}"
                method="POST" class="p-5 space-y-6">
                @csrf

                {{-- GRID INPUT --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">

                    {{-- Tekanan darah --}}
                    <div>
                        <label for="tekanan_darah"
                            class="block mb-1.5 text-xs font-semibold tracking-wide text-slate-700 uppercase">
                            Tekanan Darah <span class="text-[11px] font-normal text-slate-400">(mmHg)</span>
                        </label>
                        <div class="relative">
                            <input type="text" id="tekanan_darah" name="tekanan_darah"
                                value="{{ old('tekanan_darah', $dataEMR->tekanan_darah) }}" placeholder="120/80"
                                required
                                class="peer bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-4 pl-3 py-2.5
                          placeholder:text-slate-400
                          @error('tekanan_darah') border-red-500 focus:ring-red-400 focus:border-red-500 @enderror">
                        </div>
                        @error('tekanan_darah')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[11px] text-slate-400">
                            Contoh: <span class="font-mono">120/80</span> untuk tekanan darah normal dewasa.
                        </p>
                    </div>

                    {{-- Suhu tubuh --}}
                    <div>
                        <label for="suhu_tubuh"
                            class="block mb-1.5 text-xs font-semibold tracking-wide text-slate-700 uppercase">
                            Suhu Tubuh <span class="text-[11px] font-normal text-slate-400">(°C)</span>
                        </label>
                        <div class="relative">
                            <input id="suhu_tubuh" name="suhu_tubuh" type="number" step="0.1" min="30"
                                max="45" value="{{ old('suhu_tubuh', $dataEMR->suhu_tubuh) }}" placeholder="36.7"
                                required
                                class="peer bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-4 pl-3 py-2.5
                          placeholder:text-slate-400
                          @error('suhu_tubuh') border-red-500 focus:ring-red-400 focus:border-red-500 @enderror">
                        </div>
                        @error('suhu_tubuh')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[11px] text-slate-400">
                            Rentang normal sekitar <span class="font-mono">36–37.5°C</span>.
                        </p>
                    </div>

                    {{-- 🔹 Tinggi Badan --}}
                    <div>
                        <label for="tinggi_badan"
                            class="block mb-1.5 text-xs font-semibold tracking-wide text-slate-700 uppercase">
                            Tinggi Badan <span class="text-[11px] font-normal text-slate-400">(cm)</span>
                        </label>
                        <div class="relative">
                            <input id="tinggi_badan" name="tinggi_badan" type="number" step="0.1" min="50"
                                max="250" value="{{ old('tinggi_badan', $dataEMR->tinggi_badan) }}"
                                placeholder="170" required
                                class="peer bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-4 pl-3 py-2.5
                          placeholder:text-slate-400
                          @error('tinggi_badan') border-red-500 focus:ring-red-400 focus:border-red-500 @enderror">
                        </div>
                        @error('tinggi_badan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[11px] text-slate-400">
                            Isi dalam satuan <span class="font-mono">cm</span>, contoh: <span
                                class="font-mono">170</span>.
                        </p>
                    </div>

                    {{-- 🔹 Berat Badan --}}
                    <div>
                        <label for="berat_badan"
                            class="block mb-1.5 text-xs font-semibold tracking-wide text-slate-700 uppercase">
                            Berat Badan <span class="text-[11px] font-normal text-slate-400">(kg)</span>
                        </label>
                        <div class="relative">
                            <input id="berat_badan" name="berat_badan" type="number" step="0.1" min="2"
                                max="300" value="{{ old('berat_badan', $dataEMR->berat_badan) }}" placeholder="65"
                                required
                                class="peer bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-4 pl-3 py-2.5
                          placeholder:text-slate-400
                          @error('berat_badan') border-red-500 focus:ring-red-400 focus:border-red-500 @enderror">
                        </div>
                        @error('berat_badan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[11px] text-slate-400">
                            Isi dalam satuan <span class="font-mono">kg</span>, contoh: <span
                                class="font-mono">65</span>.
                        </p>
                    </div>

                    {{-- 🔹 IMT --}}
                    <div>
                        <label for="imt"
                            class="block mb-1.5 text-xs font-semibold tracking-wide text-slate-700 uppercase">
                            Indeks Massa Tubuh (IMT) <span
                                class="text-[11px] font-normal text-slate-400">(kg/m²)</span>
                        </label>
                        <div class="relative">
                            <input id="imt" name="imt" type="number" step="0.1" min="5"
                                max="80" value="{{ old('imt', $dataEMR->imt) }}" placeholder="22.5" required
                                class="peer bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-4 pl-3 py-2.5
                          placeholder:text-slate-400
                          @error('imt') border-red-500 focus:ring-red-400 focus:border-red-500 @enderror">
                        </div>
                        @error('imt')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[11px] text-slate-400">
                            Contoh nilai normal sekitar <span class="font-mono">18.5–24.9</span>.
                        </p>
                    </div>

                    {{-- Nadi --}}
                    <div>
                        <label for="nadi"
                            class="block mb-1.5 text-xs font-semibold tracking-wide text-slate-700 uppercase">
                            Nadi <span class="text-[11px] font-normal text-slate-400">(bpm)</span>
                        </label>
                        <div class="relative">
                            <input id="nadi" name="nadi" type="number" min="30" max="220"
                                value="{{ old('nadi', $dataEMR->nadi) }}" placeholder="80" required
                                class="peer bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-4 pl-3 py-2.5
                          placeholder:text-slate-400
                          @error('nadi') border-red-500 focus:ring-red-400 focus:border-red-500 @enderror">
                        </div>
                        @error('nadi')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[11px] text-slate-400">
                            Dewasa normal: sekitar <span class="font-mono">60–100 bpm</span>.
                        </p>
                    </div>

                    {{-- Pernapasan --}}
                    <div>
                        <label for="pernapasan"
                            class="block mb-1.5 text-xs font-semibold tracking-wide text-slate-700 uppercase">
                            Frekuensi Napas <span class="text-[11px] font-normal text-slate-400">(x/menit)</span>
                        </label>
                        <div class="relative">
                            <input id="pernapasan" name="pernapasan" type="number" min="5" max="60"
                                value="{{ old('pernapasan', $dataEMR->pernapasan) }}" placeholder="18" required
                                class="peer bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-4 pl-3 py-2.5
                          placeholder:text-slate-400
                          @error('pernapasan') border-red-500 focus:ring-red-400 focus:border-red-500 @enderror">
                        </div>
                        @error('pernapasan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[11px] text-slate-400">
                            Normal dewasa: sekitar <span class="font-mono">12–20 x/menit</span>.
                        </p>
                    </div>

                    {{-- SpO2 --}}
                    <div>
                        <label for="saturasi_oksigen"
                            class="block mb-1.5 text-xs font-semibold tracking-wide text-slate-700 uppercase">
                            Saturasi Oksigen <span class="text-[11px] font-normal text-slate-400">(SpO₂, %)</span>
                        </label>
                        <div class="relative">
                            <input id="saturasi_oksigen" name="saturasi_oksigen" type="number" min="50"
                                max="100" value="{{ old('saturasi_oksigen', $dataEMR->saturasi_oksigen) }}"
                                placeholder="98" required
                                class="peer bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl
                          focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-4 pl-3 py-2.5
                          placeholder:text-slate-400
                          @error('saturasi_oksigen') border-red-500 focus:ring-red-400 focus:border-red-500 @enderror">
                        </div>
                        @error('saturasi_oksigen')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[11px] text-slate-400">
                            Nilai normal umumnya <span class="font-mono">&ge; 95%</span>.
                        </p>
                    </div>
                </div>



                {{-- RIWAYAT KESEHATAN --}}
                <div class="mt-2 p-4 border border-slate-100 rounded-2xl bg-slate-50/80 space-y-4">
                    <h3 class="text-sm font-semibold text-slate-800 flex items-center gap-2">
                        <span
                            class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-[11px] font-bold">2</span>
                        Riwayat Kesehatan
                    </h3>

                    {{-- Riwayat Penyakit Dahulu --}}
                    <div>
                        <label for="riwayat_penyakit_dahulu"
                            class="block mb-1.5 text-xs font-semibold tracking-wide text-slate-700 uppercase">
                            Riwayat Penyakit Dahulu
                            <span class="text-[11px] font-normal text-slate-400">(opsional)</span>
                        </label>
                        <textarea id="riwayat_penyakit_dahulu" name="riwayat_penyakit_dahulu" rows="3"
                            placeholder="Contoh: Hipertensi sejak 2018, DM tipe 2 terkontrol, riwayat asma masa kecil."
                            class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full px-3 py-2.5
                                   placeholder:text-slate-400 resize-y
                                   @error('riwayat_penyakit_dahulu') border-red-500 focus:ring-red-400 focus:border-red-500 @enderror">{{ old('riwayat_penyakit_dahulu', $dataEMR->riwayat_penyakit_dahulu ?? '') }}</textarea>
                        @error('riwayat_penyakit_dahulu')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[11px] text-slate-400">
                            Isi ringkas riwayat penyakit utama yang pernah diderita pasien sebelumnya.
                        </p>
                    </div>

                    {{-- Riwayat Penyakit Keluarga --}}
                    <div>
                        <label for="riwayat_penyakit_keluarga"
                            class="block mb-1.5 text-xs font-semibold tracking-wide text-slate-700 uppercase">
                            Riwayat Penyakit Keluarga
                            <span class="text-[11px] font-normal text-slate-400">(opsional)</span>
                        </label>
                        <textarea id="riwayat_penyakit_keluarga" name="riwayat_penyakit_keluarga" rows="3"
                            placeholder="Contoh: Ayah DM tipe 2, ibu hipertensi, kakek stroke."
                            class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl
                                   focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-full px-3 py-2.5
                                   placeholder:text-slate-400 resize-y
                                   @error('riwayat_penyakit_keluarga') border-red-500 focus:ring-red-400 focus:border-red-500 @enderror">{{ old('riwayat_penyakit_keluarga', $dataEMR->riwayat_penyakit_keluarga ?? '') }}</textarea>
                        @error('riwayat_penyakit_keluarga')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-[11px] text-slate-400">
                            Cantumkan riwayat penyakit yang sering muncul di keluarga inti (orang tua, saudara kandung).
                        </p>
                    </div>
                </div>


                {{-- FOOTER FORM --}}
                <div
                    class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 pt-4 border-t border-slate-100">
                    <div class="text-[11px] text-slate-400">
                        Pastikan data sesuai hasil pengukuran sebelum menekan tombol simpan.
                    </div>
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ $urlBack }}"
                            class="px-4 py-2.5 rounded-xl border border-slate-300 text-sm font-medium text-slate-700 
                                  bg-white hover:bg-slate-50 hover:border-slate-400 transition">
                            Kembali
                        </a>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white 
                                       text-sm font-semibold shadow-sm hover:bg-indigo-700 focus:outline-none 
                                       focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500 transition">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            <span>Simpan Vital Sign</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
{{-- JS khusus halaman ini --}}
@vite(['resources/js/perawat/kunjungan/form-pengisian-vital-sign-pasien.js'])

</html>
