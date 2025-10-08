<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div class="max-w-7xl mx-auto m-5 px-8 py-4 bg-gray-200 rounded-md gap-8">
        <Form action="{{ route('testing.create.kunjungan') }}" method="post">
            @csrf
            <label class="text-xl font-bold">Form Create Kunjungan</label>
            <div class="mt-5 mb-10">
                <div class="grid mb-4 gap-1">
                    <label>Nama Pasien</label>
                    <select name="pasien_id">
                        @foreach ($dataPasien as $pasien)
                            <option value="{{ $pasien->id }}">{{ $pasien->nama_pasien }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid mb-4 gap-1">
                    <label>Nama Dokter</label>
                    <select name="dokter_id">
                        @foreach ($dataDokter as $dokter)
                            <option value="{{ $dokter->id }}">{{ $dokter->nama_dokter }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid mb-4 gap-1">
                    <label>Tanggal Kunjungan</label>
                    <input type="date" name="tanggal_kunjungan"></input>
                </div>
                <div class="grid mb-4 gap-1">
                    <label>Keluhan Awal</label>
                    <textarea name="keluhan_awal" rows="4"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Tuliskan Keluhan Anda"></textarea>
                </div>
            </div>
            <div class="flex items-center justify-end mx-6">
                <button class="bg-blue-500 rounded-md text-white text-lg hover:bg-blue-600 focus:bg-blue-700 px-4 py-2">
                    Simpan
                </button>
            </div>
        </Form>
    </div>
</body>

</html>
