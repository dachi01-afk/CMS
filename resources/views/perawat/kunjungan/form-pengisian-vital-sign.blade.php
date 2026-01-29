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
    <div class="mx-auto max-w-5xl px-4 py-8">

        {{-- HEADER HALAMAN --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-user-nurse"></i>
                    <span>Asesmen Keperawatan</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-slate-800">Pengkajian Awal Pasien</h1>
                <p class="text-slate-500 mt-1 text-sm">Lengkapi data klinis pasien dengan akurat.</p>
            </div>

            {{-- Info Pasien Mini --}}
            <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4">
                <div
                    class="h-10 w-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 font-bold">
                    {{ substr($dataPasien->nama_pasien ?? 'P', 0, 1) }}
                </div>
                <div>
                    <h4 class="font-bold text-sm text-slate-800">{{ $dataPasien->nama_pasien ?? '-' }}</h4>
                    <p class="text-xs text-slate-500">RM: {{ $dataPasien->no_emr ?? '-' }} |
                        {{ $dataPasien->tanggal_lahir ? \Carbon\Carbon::parse($dataPasien->tanggal_lahir)->age . ' Tahun' : '-' }}
                    </p>
                </div>
            </div>
        </div>

        <form id="emr-form" action="{{ route('perawat.submit.data.vital.sign.pasien', $dataIdEMR) }}" method="POST"
            class="space-y-6">
            @csrf

            {{-- SECTION 1: TANDA VITAL & FISIK --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                    <div class="h-8 w-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </div>
                    <h3 class="font-bold text-slate-800">I. Tanda Vital & Antropometri</h3>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                    {{-- TD --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tekanan Darah
                            (mmHg)</label>
                        <input type="text" name="tekanan_darah"
                            value="{{ old('tekanan_darah', $dataEMR->tekanan_darah) }}"
                            class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="120/80">
                    </div>
                    {{-- Nadi --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nadi (x/mnt)</label>
                        <input type="number" name="nadi" value="{{ old('nadi', $dataEMR->nadi) }}"
                            class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="80">
                    </div>
                    {{-- RR --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Pernapasan (x/mnt)</label>
                        <input type="number" name="pernapasan" value="{{ old('pernapasan', $dataEMR->pernapasan) }}"
                            class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="20">
                    </div>
                    {{-- Suhu --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Suhu (°C)</label>
                        <input type="number" step="0.1" name="suhu_tubuh"
                            value="{{ old('suhu_tubuh', $dataEMR->suhu_tubuh) }}"
                            class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="36.5">
                    </div>

                    {{-- SpO2 --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">SpO2 (%)</label>
                        <input type="number" name="saturasi_oksigen"
                            value="{{ old('saturasi_oksigen', $dataEMR->saturasi_oksigen) }}"
                            class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="98">
                    </div>

                    {{-- BB & TB (Auto IMT) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Berat Badan (kg)</label>
                        <input type="number" step="0.1" id="bb" name="berat_badan"
                            value="{{ old('berat_badan', $dataEMR->berat_badan) }}"
                            class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-slate-50"
                            placeholder="0">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Tinggi Badan (cm)</label>
                        <input type="number" id="tb" name="tinggi_badan"
                            value="{{ old('tinggi_badan', $dataEMR->tinggi_badan) }}"
                            class="w-full rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-slate-50"
                            placeholder="0">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">IMT (Auto)</label>
                        <input type="text" id="imt" name="imt" value="{{ old('imt', $dataEMR->imt) }}"
                            readonly
                            class="w-full rounded-lg border-slate-200 bg-slate-100 text-slate-600 sm:text-sm font-semibold cursor-not-allowed">
                    </div>
                </div>
            </div>

            {{-- SECTION 2: STATUS NEUROLOGIS (GCS) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                    <div class="h-8 w-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                        <i class="fa-solid fa-brain"></i>
                    </div>
                    <h3 class="font-bold text-slate-800">II. Status Neurologis (GCS)</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Eye (E)</label>
                        <select id="gcs_e" name="gcs_e"
                            class="w-full rounded-lg border-slate-300 text-sm gcs-input">
                            <option value="4">4 - Spontan</option>
                            <option value="3">3 - Terhadap Suara</option>
                            <option value="2">2 - Terhadap Nyeri</option>
                            <option value="1">1 - Tidak Ada</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Verbal (V)</label>
                        <select id="gcs_v" name="gcs_v"
                            class="w-full rounded-lg border-slate-300 text-sm gcs-input">
                            <option value="5">5 - Orientasi Baik</option>
                            <option value="4">4 - Bingung</option>
                            <option value="3">3 - Kata Tak Tepat</option>
                            <option value="2">2 - Suara Mengerang</option>
                            <option value="1">1 - Tidak Ada</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Motorik (M)</label>
                        <select id="gcs_m" name="gcs_m"
                            class="w-full rounded-lg border-slate-300 text-sm gcs-input">
                            <option value="6">6 - Ikut Perintah</option>
                            <option value="5">5 - Melokalisir Nyeri</option>
                            <option value="4">4 - Menghindar Nyeri</option>
                            <option value="3">3 - Fleksi Abnormal</option>
                            <option value="2">2 - Ekstensi Abnormal</option>
                            <option value="1">1 - Tidak Ada</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Total GCS</label>
                        <input type="text" id="gcs_total" name="gcs_total" readonly
                            class="w-full rounded-lg border-slate-200 bg-purple-50 text-purple-700 font-bold text-center">
                    </div>
                    <div class="col-span-1 md:col-span-4">
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Kesadaran
                            Kualitatif</label>
                        <select name="kesadaran" class="w-full md:w-1/3 rounded-lg border-slate-300 text-sm">
                            <option>Compos Mentis</option>
                            <option>Apatis</option>
                            <option>Delirium</option>
                            <option>Somnolen</option>
                            <option>Sopor</option>
                            <option>Coma</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- SECTION 3: SKRINING NYERI & ALERGI --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="h-8 w-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center">
                                <i class="fa-solid fa-face-frown-open"></i>
                            </div>
                            <h3 class="font-bold text-slate-800">III. Skala Nyeri (NRS)</h3>
                        </div>
                        <span id="nyeri_val" class="font-bold text-2xl text-red-600">0</span>
                    </div>
                    <div class="p-6">
                        <input type="range" name="skala_nyeri" min="0" max="10" value="0"
                            id="nyeri_range"
                            class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-red-600">
                        <div class="flex justify-between text-xs text-slate-400 mt-2 font-semibold">
                            <span>0 (Tidak Nyeri)</span>
                            <span>5 (Sedang)</span>
                            <span>10 (Tak Tertahankan)</span>
                        </div>
                        <div class="mt-4">
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Lokasi Nyeri</label>
                            <input type="text" name="lokasi_nyeri"
                                class="w-full rounded-lg border-slate-300 text-sm"
                                placeholder="Misal: Perut kanan bawah">
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                            <i class="fa-solid fa-shield-virus"></i>
                        </div>
                        <h3 class="font-bold text-slate-800">IV. Keamanan Pasien</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Riwayat Alergi</label>
                            <div class="flex items-center gap-4 mb-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="has_alergi" value="0"
                                        class="text-indigo-600 focus:ring-indigo-500" checked
                                        onclick="document.getElementById('ket_alergi').classList.add('hidden')">
                                    <span class="text-sm font-medium">Tidak Ada</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="has_alergi" value="1"
                                        class="text-indigo-600 focus:ring-indigo-500"
                                        onclick="document.getElementById('ket_alergi').classList.remove('hidden')">
                                    <span class="text-sm font-medium">Ada</span>
                                </label>
                            </div>
                            <input type="text" id="ket_alergi" name="keterangan_alergi"
                                class="hidden w-full rounded-lg border-red-300 focus:border-red-500 focus:ring-red-500 text-sm placeholder-red-300"
                                placeholder="Sebutkan alergi (Obat/Makanan)...">
                        </div>
                        <hr class="border-slate-100">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Risiko Jatuh
                                (Morse/Humpty)</label>
                            <select name="risiko_jatuh" class="w-full rounded-lg border-slate-300 text-sm">
                                <option value="Rendah">Risiko Rendah</option>
                                <option value="Sedang">Risiko Sedang</option>
                                <option value="Tinggi" class="text-red-600 font-bold">Risiko Tinggi (Pasang Gelang
                                    Kuning)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 4: RIWAYAT KESEHATAN (Diperbaiki) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                    <div class="h-8 w-8 rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center">
                        <i class="fa-solid fa-file-medical"></i>
                    </div>
                    <h3 class="font-bold text-slate-800">V. Riwayat Kesehatan</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Keluhan Utama (Saat
                            Ini)</label>
                        <textarea name="keluhan_utama" rows="3"
                            class="w-full rounded-lg border-slate-300 text-sm focus:ring-teal-500 focus:border-teal-500"
                            placeholder="Apa yang dirasakan pasien saat ini?">{{ $dataEMR->keluhan_utama }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Riwayat Penyakit
                            Dahulu</label>
                        <textarea name="riwayat_penyakit_dahulu" rows="3"
                            class="w-full rounded-lg border-slate-300 text-sm focus:ring-teal-500 focus:border-teal-500"
                            placeholder="{{ old('riwayat_penyakit_dahulu', $dataEMR->riwayat_penyakit_dahulu ?? '-') }}">{{ $riwayatPenyakitDahulu }}</textarea>
                    </div>
                </div>
            </div>

            {{-- SECTION 5: PLANNING & MASALAH KEPERAWATAN --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center gap-3">
                    <div class="h-8 w-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                    <h3 class="font-bold text-slate-800">VI. Masalah Keperawatan (A) & Rencana (P)</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div class="border border-slate-200 rounded-lg p-4">
                            <h4 class="text-sm font-bold text-slate-700 mb-3 border-b pb-2">Diagnosa Keperawatan Umum
                            </h4>
                            <div class="space-y-2 text-sm text-slate-600">
                                <label class="flex items-center gap-2"><input type="checkbox" name="diagnosa[]"
                                        value="Hipertermia" class="rounded text-indigo-600"> Hipertermia</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="diagnosa[]"
                                        value="Nyeri Akut" class="rounded text-indigo-600"> Nyeri Akut</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="diagnosa[]"
                                        value="Ketidakefektifan Pola Napas" class="rounded text-indigo-600"> Pola
                                    Napas Tidak Efektif</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="diagnosa[]"
                                        value="Risiko Infeksi" class="rounded text-indigo-600"> Risiko Infeksi</label>
                                <label class="flex items-center gap-2"><input type="checkbox" name="diagnosa[]"
                                        value="Intoleransi Aktivitas" class="rounded text-indigo-600"> Intoleransi
                                    Aktivitas</label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Intervensi
                                Keperawatan</label>
                            <textarea name="intervensi" rows="5"
                                class="w-full rounded-lg border-slate-300 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="1. Monitor TTV per jam&#10;2. Berikan posisi semi fowler&#10;3. Kolaborasi pemberian analgesik..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex items-center justify-end gap-4 pt-6 pb-12">
                <a href="{{ $urlBack }}"
                    class="px-6 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-semibold hover:bg-slate-50 transition">Batal</a>
                <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:scale-[1.02] transition-all flex items-center gap-2">
                    <i class="fa-solid fa-save"></i>
                    Simpan Pengkajian
                </button>
            </div>
        </form>
    </div>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>
{{-- JS khusus halaman ini --}}
@vite(['resources/js/perawat/kunjungan/form-pengisian-vital-sign-pasien.js'])

</html>
