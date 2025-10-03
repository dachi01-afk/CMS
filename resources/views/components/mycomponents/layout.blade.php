<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="App Clinic">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CMS-Royal-Klinik</title>
    <link href='{{ asset('storage/assets/royal_klinik.svg') }}' rel='shortcut icon'>

    {{-- datatebels --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.tailwindcss.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.1/css/responsive.dataTables.css">

    {{-- vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js']);

    <!-- Font-Awesome -->
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Flowbite JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.1/flowbite.min.js" defer></script>

</head>

<body class="bg-gray-50">
    <!-- HEADER -->
    <header class="fixed top-0 w-full z-50">
        <!-- Branding Bar -->
        <div class="h-1 bg-blue-900 w-full"></div>

        <!-- Navbar -->
        <nav class="h-19 bg-white border-b border-gray-200 shadow-md">
            <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 py-2">

                <!-- Kiri: Sidebar Toggle (Mobile) -->
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar"
                        aria-controls="logo-sidebar" type="button"
                        class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none">
                        <span class="sr-only">Open sidebar</span>
                        <i class="fa-solid fa-bars fa-lg"></i>
                    </button>

                    <!-- Logo -->
                    <a href="#" class="hidden sm:flex items-center space-x-2 rtl:space-x-reverse">
                        <img src="/storage/assets/royal_klinik.svg" alt="Logo Royal Klinik" class="h-9 w-auto">
                        <h1 class="text-xl font-bold text-gray-800 whitespace-nowrap">
                            Royal Klinik.id
                        </h1>
                    </a>
                </div>



                <!-- Tengah: Search -->
                <div class="flex-1 flex justify-center px-2 md:px-6 search-area">
                    {{ $search ?? '' }}
                </div>




                <!-- Kanan: Ikon & Akun -->
                <div class="flex items-center space-x-3">
                    <a href="#" class="hidden sm:block text-gray-500 hover:text-gray-700 p-2 rounded-full">
                        <i class="fa-solid fa-circle-question fa-lg"></i>
                    </a>
                    <a href="#" class="hidden sm:block text-gray-500 hover:text-gray-700 p-2 rounded-full">
                        <i class="fa-solid fa-bell fa-lg"></i>
                    </a>
                    <!-- Akun Dropdown -->
                    <button type="button" id="dropdownAccountButton" data-dropdown-toggle="dropdownAccount"
                        class="text-gray-500 hover:text-gray-700 p-2 rounded-full">
                        <i class="fa-solid fa-circle-user fa-lg"></i>
                    </button>
                    <div id="dropdownAccount"
                        class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 mt-2 right-0 absolute">
                        <ul class="py-2 text-sm text-gray-700" aria-labelledby="dropdownAccountButton">
                            <!-- Pengaturan Akun -->
                            <li>
                                <!-- Pastikan rute 'profile.edit' tersedia di file `routes/web.php` -->
                                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-100">
                                    Pengaturan Akun
                                </a>
                            </li>
                            <!-- Tombol Logout -->
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 hover:bg-gray-100">
                                        Keluar
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <div class="flex">
        <!-- SIDEBAR -->
        <aside id="logo-sidebar"
            class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0"
            aria-label="Sidebar">
            <div class="h-full px-3 pb-4 overflow-y-auto bg-white">
                <ul class="space-y-2 font-medium">
                    <x-mycomponents.sidebar_link href="dashboard.index" class="fa-solid fa-house" :active="Request::routeIs('admin.index')">
                        Dashboard
                    </x-mycomponents.sidebar_link>

                    <x-mycomponents.sidebar_link href="pasien.index" class="fa-solid fa-calendar-plus">
                        Pasien
                    </x-mycomponents.sidebar_link>

                    <x-mycomponents.sidebar_link href="dokter.index" class="fa-solid fa-id-badge">
                        Dokter
                    </x-mycomponents.sidebar_link>

                    <x-mycomponents.sidebar_link href="apotek.index" class="fa-solid fa-pills">
                        Apotek
                    </x-mycomponents.sidebar_link>

                    <x-mycomponents.sidebar_link href="report.index" class="fa-solid fa-cash-register">
                        Report
                    </x-mycomponents.sidebar_link>

                    <x-mycomponents.sidebar_link href="history_kunjungan.index" class="fa-solid fa-cash-register">
                        History Kunjungan
                    </x-mycomponents.sidebar_link>

                    <hr class="my-4 border-gray-300">
                    <x-mycomponents.sidebar_link href="settings.index" class="fa-solid fa-gear">
                        Settings
                    </x-mycomponents.sidebar_link>
                </ul>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="p-4 sm:ml-64 w-full pt-16 ">

            {{ $slot }}

        </main>
    </div>

    <!-- FOOTER -->
    <footer class="sm:ml-64 bg-white border-t border-gray-200 mt-8">
        <div class="w-full mx-auto p-4 md:flex md:items-center md:justify-between">
            <p class="text-center text-sm text-gray-500">&copy; 2024 Royal Klinik.id. Hak Cipta Dilindungi.</p>
            <nav class="flex flex-wrap justify-center mt-3 text-sm font-medium text-gray-500 sm:mt-0">
                <a href="#" class="hover:underline me-4 md:me-6">Kebijakan Privasi</a>
                <a href="#" class="hover:underline">Syarat dan Ketentuan</a>
            </nav>
        </div>
    </footer>





    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.1/js/dataTables.responsive.js"></script>
    {{-- <script src="https://cdn.datatables.net/2.3.4/js/dataTables.tailwindcss.js"></script> --}}
</body>

</html>
