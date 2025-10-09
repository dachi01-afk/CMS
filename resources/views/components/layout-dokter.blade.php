<!-- resources/views/layouts/dokter-layout.blade.php -->
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Panel Dokter' }}</title>
    @vite('resources/css/app.css') <!-- pastikan Tailwind sudah di-setup -->
</head>

<body class="bg-gray-100">

    <div class="flex min-h-screen">
        <x-sidebar-dokter />

        <div class="flex-1 flex flex-col">
            <x-header-dokter> {{ $header }} </x-header-dokter>

            <main class="flex-1 p-6">
                {{ $slot }}
            </main>

            <x-footer-dokter />
        </div>
    </div>

</body>

</html>
