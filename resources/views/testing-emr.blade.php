<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Daftar Nomor Rekam Medis (EMR)</title>
    @vite(['resources/css/app.css'])
</head>

<body class="bg-gray-100 text-gray-800 font-sans">

    <div class="container mx-auto px-6 py-10">
        <h1 class="text-3xl font-bold text-center text-blue-700 mb-8">
            🏥 Daftar Nomor Rekam Medis (EMR)
        </h1>

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-5">
                {{ session('error') }}
            </div>
        @endif

        @if (isset($emrList) && $emrList->count() > 0)
            <div class="overflow-x-auto bg-white shadow-md rounded-lg">
                <table class="min-w-full border-collapse border border-gray-200">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="py-3 px-4 text-left">#</th>
                            <th class="py-3 px-4 text-left">ID EMR</th>
                            <th class="py-3 px-4 text-left">No RM</th>
                            <th class="py-3 px-4 text-left">ID Pasien</th>
                            <th class="py-3 px-4 text-left">Diagnosis</th>
                            <th class="py-3 px-4 text-left">Tanggal Dibuat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($emrList as $index => $emr)
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="py-2 px-4">{{ $index + 1 }}</td>
                                <td class="py-2 px-4 font-semibold text-gray-700">#{{ $emr->id }}</td>
                                <td class="py-2 px-4">
                                    <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-sm">
                                        {{ $emr->no_rm ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-2 px-4">
                                    {{ $emr->kunjungan->pasien_id ?? '-' }}
                                </td>
                                <td class="py-2 px-4">{{ $emr->diagnosis ?? '-' }}</td>
                                <td class="py-2 px-4">{{ $emr->created_at ? $emr->created_at->format('d/m/Y') : '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-center text-gray-500 mt-8">Tidak ada data EMR ditemukan.</p>
        @endif

        <div class="text-center mt-8">
            <a href="{{ route('emr.generate') }}"
                class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-lg shadow-md transition">
                🔄 Generate Nomor RM
            </a>
        </div>
    </div>

</body>

</html>
