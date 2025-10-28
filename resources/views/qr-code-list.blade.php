<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>QR Code Pasien</title>
</head>

<body style="font-family: Arial; text-align:center;">
    <h2>Daftar QR Code Pasien</h2>

    @php
        use SimpleSoftwareIO\QrCode\Facades\QrCode;
    @endphp
    
    @foreach ($pasienList as $pasien)
        <div style="margin: 20px; border:1px solid #ccc; padding:10px; display:inline-block;">
            <h4>{{ $pasien->nama_pasien }}</h4>
            {!! QrCode::size(200)->generate(route('qr.show', $pasien->id)) !!}
            <p>ID: {{ $pasien->id }}</p>
        </div>
    @endforeach
</body>

</html>
