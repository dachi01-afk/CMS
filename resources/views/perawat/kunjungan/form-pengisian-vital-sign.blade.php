<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Input Vital Sign EMR #{{ $emr->id ?? '-' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-gray-50">
    <div class="mx-auto max-w-3xl px-4 py-8">
        <h1 class="text-2xl font-bold mb-2">Input Vital Sign</h1>
        <p class="text-sm text-gray-600 mb-6">
            No EMR Pasien:
            <span class="font-semibold">{{ $dataPasien->no_emr ?? '-' }}</span>
        </p>

        {{-- Ringkasan pasien/poli/dokter --}}
        <div class="bg-white rounded-2xl shadow mb-6">
            <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <div class="text-gray-500">Pasien</div>
                    <div class="font-semibold">
                        {{ $dataPasien->nama_pasien ?? '-' }}
                    </div>
                </div>
                <div>
                    <div class="text-gray-500">Poli</div>
                    <div class="font-semibold">
                        {{ $dataPoliPasien->nama_poli ?? '-' }}
                    </div>
                </div>
                <div>
                    <div class="text-gray-500">Dokter</div>
                    <div class="font-semibold">
                        {{ $dataDokterPasien->nama_dokter ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- FORM VITAL SIGN --}}
        <div class="bg-white rounded-2xl shadow p-6">
            <form id="vital-emr-form" action="{{ route('perawat.submit.data.vital.sign.pasien', $dataIdEMR) }}"
                method="POST" class="space-y-5">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Tekanan darah --}}
                    <div>
                        <label for="tekanan_darah" class="block text-sm font-medium mb-1">
                            Tekanan Darah (mmHg)
                        </label>
                        <input type="text" name="tekanan_darah" id="tekanan_darah" required
                            value="{{ old('tekanan_darah', $dataEMR->tekanan_darah) }}"
                            class="w-full border rounded-xl px-3 py-2 @error('tekanan_darah') border-red-500 @enderror"
                            placeholder="120/80">
                        @error('tekanan_darah')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Suhu tubuh --}}
                    <div>
                        <label for="suhu_tubuh" class="block text-sm font-medium mb-1">
                            Suhu Tubuh (°C)
                        </label>
                        <input type="number" step="0.1" min="30" max="45" name="suhu_tubuh"
                            id="suhu_tubuh" required value="{{ old('suhu_tubuh', $dataEMR->suhu_tubuh) }}"
                            class="w-full border rounded-xl px-3 py-2 @error('suhu_tubuh') border-red-500 @enderror"
                            placeholder="36.7">
                        @error('suhu_tubuh')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Nadi --}}
                    <div>
                        <label for="nadi" class="block text-sm font-medium mb-1">
                            Nadi (bpm)
                        </label>
                        <input type="number" min="30" max="220" name="nadi" id="nadi" required
                            value="{{ old('nadi', $dataEMR->nadi) }}"
                            class="w-full border rounded-xl px-3 py-2 @error('nadi') border-red-500 @enderror"
                            placeholder="80">
                        @error('nadi')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Pernapasan --}}
                    <div>
                        <label for="pernapasan" class="block text-sm font-medium mb-1">
                            Pernapasan (x/menit)
                        </label>
                        <input type="number" min="5" max="60" name="pernapasan" id="pernapasan" required
                            value="{{ old('pernapasan', $dataEMR->pernapasan) }}"
                            class="w-full border rounded-xl px-3 py-2 @error('pernapasan') border-red-500 @enderror"
                            placeholder="18">
                        @error('pernapasan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- SpO2 --}}
                    <div>
                        <label for="saturasi_oksigen" class="block text-sm font-medium mb-1">
                            Saturasi Oksigen (SpO₂, %)
                        </label>
                        <input type="number" min="50" max="100" name="saturasi_oksigen"
                            id="saturasi_oksigen" required
                            value="{{ old('saturasi_oksigen', $dataEMR->saturasi_oksigen) }}"
                            class="w-full border rounded-xl px-3 py-2 @error('saturasi_oksigen') border-red-500 @enderror"
                            placeholder="98">
                        @error('saturasi_oksigen')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">
                        Simpan Vital Sign
                    </button>
                    <a href="{{ $urlBack }}" class="px-4 py-2 rounded-xl border">
                        Kembali
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Pastikan path dan nama file JS sama dengan yang ada di resources/js --}}
    @vite(['resources/js/perawat/kunjungan/form-pengisian-vital-sign-pasien.js'])

</body>

</html>
