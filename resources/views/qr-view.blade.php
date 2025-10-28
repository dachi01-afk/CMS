<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Data Pasien</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background-color: #f9fafb;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: auto;
        }

        .foto {
            width: 120px;
            height: 120px;
            border-radius: 100%;
            object-fit: cover;
            display: block;
            margin: 10px auto;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .info {
            margin-top: 10px;
        }

        .info p {
            font-size: 15px;
            color: #555;
        }
    </style>
</head>

<body>
    <div class="card">
        <h2>Data Pasien</h2>
        @if ($pasien->foto_pasien)
            <img src="{{ asset('storage/' . $pasien->foto_pasien) }}" alt="Foto Pasien" class="foto">
        @else
            <img src="https://via.placeholder.com/120" alt="Foto Default" class="foto">
        @endif
        <div class="info">
            <p><strong>Nama:</strong> {{ $pasien->nama_pasien }}</p>
            <p><strong>Alamat:</strong> {{ $pasien->alamat }}</p>
            <p><strong>Tanggal Lahir:</strong> {{ $pasien->tanggal_lahir }}</p>
            <p><strong>Jenis Kelamin:</strong> {{ $pasien->jenis_kelamin }}</p>
        </div>
    </div>
</body>

</html>
