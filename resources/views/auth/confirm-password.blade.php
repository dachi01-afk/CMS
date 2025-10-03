<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Konfirmasi Password - Klinik Sehat</title>
    <link href='{{ asset('storage/assets/royal_klinik.svg') }}' rel='shortcut icon'>
    <!-- Asumsi Anda sudah menjalankan npm run dev atau npm run build -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Gaya kustom (Diambil dari halaman Login dan Register) */
        .clinic-bg {
            /* Warna latar belakang cerah dan menenangkan (Sky Blue Light) */
            background-color: #f0f9ff;
            /* Light Sky Blue */
        }

        .clinic-card {
            /* Bayangan lembut untuk kesan profesional */
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 0 10px -5px rgba(0, 0, 0, 0.04);
            transition: transform 0.3s ease;
        }

        .clinic-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>

<body class="font-sans antialiased clinic-bg min-h-screen flex items-center justify-center p-4 sm:p-6">
    <!-- Kontainer Confirm Password -->
    <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white clinic-card overflow-hidden rounded-xl">

        <!-- Area Logo -->
        <div class="flex flex-col items-center justify-center mb-6">
            <img src="{{ asset('storage/assets/royal_klinik.svg') }}" alt="Logo Royal Klinik" class="h-20 w-auto mb-4" />

            <h1 class="text-xl font-semibold text-gray-700">Konfirmasi Akses</h1>
        </div>

        <!-- Deskripsi Keamanan -->
        <div class="mb-6 text-sm text-gray-600 text-center border-l-4 border-sky-500 bg-sky-50 p-3 rounded-md">
            Ini adalah area aplikasi yang aman. Mohon konfirmasi password Anda sebelum melanjutkan.
        </div>

        <!-- Form Konfirmasi -->
        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <!-- Password Input -->
            <div class="mb-6">
                <label for="password" class="block font-medium text-sm text-gray-700 mb-1">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="w-full border-gray-300 focus:border-sky-500 focus:ring-sky-500 rounded-lg shadow-sm p-3"
                    placeholder="Masukkan password Anda">
                @error('password')
                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Button Confirm -->
            <div class="flex justify-end mt-4">
                <button type="submit"
                    class="w-full bg-sky-600 hover:bg-sky-700 text-white font-bold py-3 px-4 rounded-lg transition duration-150 ease-in-out shadow-lg shadow-sky-200 focus:outline-none focus:ring-4 focus:ring-sky-500 focus:ring-opacity-50">
                    Konfirmasi
                </button>
            </div>
        </form>
    </div>
</body>

</html>
