<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lupa Password - Klinik Sehat</title>
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
    <!-- Kontainer Forgot Password -->
    <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white clinic-card overflow-hidden rounded-xl">

        <!-- Area Logo (Sama seperti halaman Login dan Register) -->
        <div class="flex flex-col items-center justify-center mb-6">
            <img src="{{ asset('storage/assets/royal_klinik.svg') }}" alt="Logo Royal Klinik" class="h-20 w-auto mb-4" />

            <h1 class="text-xl font-semibold text-gray-700">Setel Ulang Password</h1>
        </div>

        <!-- Deskripsi -->
        <div class="mb-4 text-sm text-gray-600 text-center">
            Lupa password? Tidak masalah. Cukup berikan alamat email Anda, dan kami akan mengirimkan tautan *reset*
            password melalui email.
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-sky-600 bg-sky-100 p-3 rounded-lg">
                {{ session('status') }}
            </div>
        @endif

        <!-- Form Forgot Password -->
        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email Address -->
            <div class="mb-6">
                <label for="email" class="block font-medium text-sm text-gray-700 mb-1">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full border-gray-300 focus:border-sky-500 focus:ring-sky-500 rounded-lg shadow-sm p-3"
                    placeholder="Masukkan email terdaftar Anda">
                @error('email')
                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Button Submit -->
            <div class="flex items-center justify-end">
                <button type="submit"
                    class="w-full bg-sky-600 hover:bg-sky-700 text-white font-bold py-3 px-4 rounded-lg transition duration-150 ease-in-out shadow-lg shadow-sky-200 focus:outline-none focus:ring-4 focus:ring-sky-500 focus:ring-opacity-50">
                    Kirim Tautan Reset Password
                </button>
            </div>

            <!-- Link Kembali ke Login -->
            <div class="flex items-center justify-center mt-6">
                <a class="underline text-sm text-gray-600 hover:text-sky-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500"
                    href="{{ route('login') }}">
                    &larr; Kembali ke Halaman Login
                </a>
            </div>
        </form>
    </div>
</body>

</html>
