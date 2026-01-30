<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Klinik Sehat</title>
    <link href='{{ asset('storage/assets/royal_klinik.svg') }}' rel='shortcut icon'>
    <!-- Asumsi Anda sudah menjalankan npm run dev atau npm run build -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Gaya kustom untuk memastikan tampilan modern dan responsif */
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
    <!-- Kontainer Login -->
    <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white clinic-card overflow-hidden rounded-xl">

        <!-- Area Logo (Siap ditambahkan logo klinik Anda) -->
        <div class="flex flex-col items-center justify-center mb-6">
            <!-- LOGO KLINIK DITAMBAHKAN DI SINI -->
            <img src="{{ asset('storage/assets/royal_klinik.svg') }}" alt="Logo Royal Klinik" class="h-20 w-auto mb-4" />
            <!-- Placeholder [LOGO KLINIK ANDA] Dihapus -->

            <h1 class="text-xl font-semibold text-gray-700">Selamat Datang, Dokter!</h1>
            <p class="text-sm text-gray-400">Silakan masuk ke sistem manajemen.</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-4 font-medium text-sm text-sky-600 bg-sky-100 p-3 rounded-lg">
                {{ session('status') }}
            </div>
        @endif

        <!-- Form Login -->
        <form method="POST" action="{{ route('dokter.proses.login') }}">
            @csrf

            <!-- Email Address -->
            <div class="mb-4">
                <label for="email" class="block font-medium text-sm text-gray-700 mb-1">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    autocomplete="username"
                    class="w-full border-gray-300 focus:border-sky-500 focus:ring-sky-500 rounded-lg shadow-sm p-3"
                    placeholder="nama@kliniksehat.com">
                @error('email')
                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-6">
                <label for="password" class="block font-medium text-sm text-gray-700 mb-1">Password</label>

                <div class="relative">
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                        class="w-full border-gray-300 focus:border-sky-500 focus:ring-sky-500 rounded-lg shadow-sm p-3 pr-12"
                        placeholder="Masukkan password Anda">

                    <button type="button" id="togglePassword"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-sky-600 focus:outline-none"
                        aria-label="Tampilkan password" aria-pressed="false">
                        <!-- icon mata (simple SVG) -->
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round">
                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>

                        <svg id="eyeOffIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-6.5 0-10-7-10-7a21.6 21.6 0 0 1 5.06-6.88">
                            </path>
                            <path d="M1 1l22 22"></path>
                            <path d="M9.9 4.24A10.94 10.94 0 0 1 12 5c6.5 0 10 7 10 7a21.6 21.6 0 0 1-4.2 5.94"></path>
                            <path d="M14.12 14.12A3 3 0 0 1 9.88 9.88"></path>
                        </svg>
                    </button>
                </div>

                @error('password')
                    <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me & Actions -->
            <div class="flex items-center justify-between mt-4">

                <!-- Remember Me -->
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox"
                        class="rounded border-gray-300 text-sky-600 shadow-sm focus:ring-sky-500" name="remember">
                    <span class="ms-2 text-sm text-gray-600">Ingat Saya</span>
                </label>

                <!-- Forgot Password -->
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-sky-600 hover:text-sky-800 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500"
                        href="{{ route('password.request') }}">
                        Lupa Password?
                    </a>
                @endif
            </div>

            <!-- Button Login -->
            <div class="mt-6">
                <button type="submit"
                    class="w-full bg-sky-600 hover:bg-sky-700 text-white font-bold py-3 px-4 rounded-lg transition duration-150 ease-in-out shadow-lg shadow-sky-200 focus:outline-none focus:ring-4 focus:ring-sky-500 focus:ring-opacity-50">
                    Masuk
                </button>
            </div>

        </form>
    </div>
</body>

</html>
