<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Input Vital Sign EMR #{{ $emr->id ?? '-' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css') {{-- pastikan Tailwind dibuild --}}
</head>

<body class="bg-gray-50">
    <div class="mx-auto max-w-3xl px-4 py-8">
        <h1 class="text-2xl font-bold mb-2">Input Vital Sign</h1>
        <p class="text-sm text-gray-600 mb-6">No EMR Pasien: <span
                class="font-semibold">{{ $emr->pasien->no_emr ?? '-' }}</span>
        </p>

        {{-- Ringkasan pasien/poli/dokter (opsional, kalau ada relasi yg di-load) --}}
        <div class="bg-white rounded-2xl shadow mb-6">
            <div class="p-4 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <div class="text-gray-500">Pasien</div>
                    <div class="font-semibold">
                        {{ $emr->kunjungan->pasien->nama_pasien ?? '-' }}
                    </div>
                </div>
                <div>
                    <div class="text-gray-500">Poli</div>
                    <div class="font-semibold">
                        {{ $emr->kunjungan->poli->nama_poli ?? '-' }}
                    </div>
                </div>
                <div>
                    <div class="text-gray-500">Dokter</div>
                    <div class="font-semibold">
                        {{ $emr->kunjungan->jadwalDokter->dokter->nama_dokter ?? '-' }}
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow p-6">
            {{-- 
        NOTE:
        - Action route SENGAJA placeholder. Kamu bisa ganti nanti, contoh:
          action="{{ route('perawat.emr.vitals.store', $emr->id) }}"
        - Untuk saat ini, kalau belum ada backend, biarkan "#" supaya nggak error.
      --}}
            <form id="vital-emr-form" action="#" method="POST" class="space-y-5">
                @csrf
                <input type="hidden" name="emr_id" value="{{ $emr->id ?? '' }}">
                {{-- hidden yang akan diisi "120/80" sebelum submit --}}
                <input type="hidden" name="tekanan_darah" id="tekanan_darah">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- TD: sistolik/diastolik → digabung ke tekanan_darah --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">TD Sistolik</label>
                        <input type="number" min="50" max="260" id="td_sistolik"
                            class="w-full border rounded-xl px-3 py-2" placeholder="120">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">TD Diastolik</label>
                        <input type="number" min="30" max="180" id="td_diastolik"
                            class="w-full border rounded-xl px-3 py-2" placeholder="80">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Suhu Tubuh (°C)</label>
                        <input type="number" step="0.1" min="30" max="45" name="suhu_tubuh"
                            id="suhu_tubuh" class="w-full border rounded-xl px-3 py-2" placeholder="36.7">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Nadi (bpm)</label>
                        <input type="number" min="20" max="240" name="nadi" id="nadi"
                            class="w-full border rounded-xl px-3 py-2" placeholder="80">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Pernapasan (x/menit)</label>
                        <input type="number" min="5" max="80" name="pernapasan" id="pernapasan"
                            class="w-full border rounded-xl px-3 py-2" placeholder="18">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Saturasi Oksigen (SpO₂, %)</label>
                        <input type="number" min="50" max="100" name="saturasi_oksigen"
                            id="saturasi_oksigen" class="w-full border rounded-xl px-3 py-2" placeholder="98">
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700">
                        Simpan Vital Sign
                    </button>
                    <a href="{{ url()->previous() }}" class="px-4 py-2 rounded-xl border">Kembali</a>
                </div>

                <p id="form-errors" class="text-sm text-red-600 mt-2 hidden"></p>
                <p id="form-success" class="text-sm text-green-600 mt-2 hidden"></p>
            </form>
        </div>
    </div>

    <script>
        // Gabungkan sistolik/diastolik → tekanan_darah sebelum submit
        document.getElementById('vital-emr-form').addEventListener('submit', function(e) {
            const sys = document.getElementById('td_sistolik').value;
            const dia = document.getElementById('td_diastolik').value;

            const hasSys = sys !== '' && !isNaN(Number(sys));
            const hasDia = dia !== '' && !isNaN(Number(dia));

            if (hasSys && hasDia) {
                document.getElementById('tekanan_darah').value = `${Number(sys)}/${Number(dia)}`;
            } else {
                // kalau salah satu kosong, biarkan hidden kosong (nullable di DB)
                document.getElementById('tekanan_darah').value = '';
            }

            // NOTE:
            // Karena kamu minta belum buat fungsi update/store,
            // form ini default akan submit ke "#" (tidak kemana-mana).
            // Nanti tinggal ganti action + tambahkan handler (POST) di controller.
            // Jika mau cegah reload skrinsut, uncomment 2 baris di bawah agar cuma "preview".
            // e.preventDefault();
            // alert('Preview submit: ' + new FormData(this).get('tekanan_darah'));
        });
    </script>
</body>

</html>
