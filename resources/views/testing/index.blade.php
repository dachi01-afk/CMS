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
    <div class="max-w-7xl mx-auto m-5 px-8 py-4 flex items-center justify-center bg-gray-200 rounded-md">
        <div class="grid gap-4 items-start justify-start w-full  ">
            @foreach ($dataJadwalDokter as $jadwalDokter)
                <div class="flex gap-4">
                    <lable>{{ $jadwalDokter->dokter->nama_dokter }}</lable>
                    <lable>{{ $jadwalDokter->dokter->spesialisasi }}</lable>
                    <lable>{{ $jadwalDokter->dokter->email }}</lable>
                    <lable>{{ $jadwalDokter->dokter->no_hp }}</lable>

                    <label>{{ $jadwalDokter->hari }}</label>
                    <label>{{ $jadwalDokter->jam_awal }}</label>
                    <label>{{ $jadwalDokter->jam_selesai }}</label>
                </div>
            @endforeach
        </div>
    </div>
</body>

</html>
