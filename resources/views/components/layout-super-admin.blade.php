<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="App Clinic">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CMS-Royal-Klinik</title>
    <link href='{{ asset('storage/assets/royal_klinik.svg') }}' rel='shortcut icon'>

    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font-Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Flowbite JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.1/flowbite.min.js" defer></script>

    <!-- Tom Select -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>
</head>

<body class="bg-gray-50">

    <!-- Toast Container -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 space-y-2"></div>

    <!-- HEADER -->
    <header class="fixed top-0 w-full z-50">
        <div class="h-1 bg-blue-900 w-full"></div>

        <nav class="h-19 bg-white border-b border-gray-200 shadow-md">
            <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 py-2">

                <!-- Left -->
                <div class="flex items-center space-x-2 sm:space-x-4">

                    <!-- Sidebar toggle -->
                    <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar"
                        aria-controls="logo-sidebar" type="button"
                        class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none">
                        <span class="sr-only">Open sidebar</span>
                        <i class="fa-solid fa-bars fa-lg"></i>
                    </button>

                    <!-- Logo -->
                    <a href="#" class="flex items-center space-x-2">
                        <img src="/storage/assets/royal_klinik.svg" alt="Logo Royal Klinik" class="h-9 w-auto">
                        <h1 class="hidden sm:block text-xl font-bold text-gray-800 whitespace-nowrap">
                            Royal Klinik.id
                        </h1>
                    </a>
                </div>

                <!-- Middle: Search -->
                <div class="flex-1 flex justify-center px-2 md:px-6">
                    {{ $search ?? '' }}
                </div>

                <!-- Right: Account -->
                <div class="flex items-center space-x-3">
                    <button type="button" id="dropdownAccountButton" data-dropdown-toggle="dropdownAccount"
                        class="text-gray-500 hover:text-gray-700 p-2 rounded-full">
                        <i class="fa-solid fa-circle-user fa-2xl"></i>
                    </button>

                    <div id="dropdownAccount"
                        class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 mt-2 absolute right-4 sm:right-6 lg:right-8">
                        <ul class="py-2 text-sm text-gray-700">

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

    <!-- BODY WRAPPER -->
    <div class="flex">

        <!-- SIDEBAR -->
        <aside id="logo-sidebar"
            class="fixed top-0 left-0 z-40 w-64 h-screen pt-16 sm:pt-20 transition-transform -translate-x-full sm:translate-x-0 bg-white text-slate-700 border-r border-gray-200">

            <div class="flex flex-col h-full">

                <!-- BRANDING -->
                <div class="px-4 pb-4 border-b border-gray-200 bg-white">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-100 border border-sky-300">
                            <i class="fa-solid fa-clinic-medical text-sky-600 text-lg"></i>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-[11px] font-semibold tracking-wide text-gray-400 uppercase">CMS Royal
                                Klinik</span>
                            <span class="text-sm font-semibold text-gray-700">Super Admin Panel</span>
                        </div>
                    </div>
                </div>

                <!-- NAVIGATION -->
                <div class="flex-1 px-3 pb-4 overflow-y-auto">
                    <nav class="mt-4 space-y-6 text-sm">

                        <!-- OVERVIEW -->
                        <div>
                            <p class="px-3 mb-2 text-[11px] font-semibold tracking-wide text-gray-400 uppercase">
                                Overview</p>
                            <ul class="space-y-1">
                                <x-mycomponents.sidebar_link href="admin.index" class="fa-solid fa-house"
                                    :active="Request::routeIs('admin.index')">
                                    Dashboard
                                </x-mycomponents.sidebar_link>
                            </ul>
                        </div>

                        <!-- MASTER DATA -->
                        <div>
                            <p class="px-3 mb-2 text-[11px] font-semibold tracking-wide text-gray-400 uppercase">Master
                                Data</p>
                            <ul class="space-y-1">
                                <x-mycomponents.sidebar_link href="jenis.spesialis.index"
                                    class="fa-solid fa-user-doctor">
                                    Jenis Spesialis Dokter
                                </x-mycomponents.sidebar_link>

                                <x-mycomponents.sidebar_link href="poli.index" class="fa-solid fa-stethoscope">
                                    Poli
                                </x-mycomponents.sidebar_link>

                                <x-mycomponents.sidebar_link href="kategori.layanan.index"
                                    class="fa-solid fa-folder-open">
                                    Kategori Layanan
                                </x-mycomponents.sidebar_link>

                                <x-mycomponents.sidebar_link href="layanan.index" class="fa-solid fa-clipboard-list">
                                    Layanan
                                </x-mycomponents.sidebar_link>

                                <x-mycomponents.sidebar_link href="manajemen_pengguna.index" class="fa-solid fa-users">
                                    Manajemen Pengguna
                                </x-mycomponents.sidebar_link>

                                <x-mycomponents.sidebar_link href="pengaturan_klinik.index"
                                    class="fa-solid fa-hospital-user">
                                    Pengaturan Klinik
                                </x-mycomponents.sidebar_link>
                            </ul>
                        </div>

                        <!-- OPERASIONAL KLINIK -->
                        <div>
                            <p class="px-3 mb-2 text-[11px] font-semibold tracking-wide text-gray-400 uppercase">
                                Operasional Klinik</p>
                            <ul class="space-y-1">
                                <x-mycomponents.sidebar_link href="jadwal_kunjungan.index"
                                    class="fa-solid fa-calendar-plus">
                                    Jadwal Kunjungan
                                </x-mycomponents.sidebar_link>

                                <x-mycomponents.sidebar_link href="order.layanan.index"
                                    class="fa-solid fa-clipboard-list" :active="Request::routeIs('order.layanan.index')">
                                    Order Layanan
                                </x-mycomponents.sidebar_link>

                                <x-mycomponents.sidebar_link href="data_medis_pasien.index"
                                    class="fa-solid fa-notes-medical">
                                    Data Medis Pasien
                                </x-mycomponents.sidebar_link>
                            </ul>
                        </div>

                        <!-- LAPORAN -->
                        <div>
                            <p class="px-3 mb-2 text-[11px] font-semibold tracking-wide text-gray-400 uppercase">Laporan
                                & Pengaturan</p>
                            <ul class="space-y-1">
                                <x-mycomponents.sidebar_link href="laporan.index" class="fa-solid fa-chart-line">
                                    Laporan
                                </x-mycomponents.sidebar_link>

                                <x-mycomponents.sidebar_link href="settings.index" class="fa-solid fa-gear">
                                    Settings
                                </x-mycomponents.sidebar_link>
                            </ul>
                        </div>

                    </nav>
                </div>

                <!-- FOOTER -->
                <div class="px-4 py-3 border-t border-gray-200 text-[11px] text-gray-500">
                    <p class="font-semibold text-gray-700">Admin</p>
                    <p>CMS-Royal-Klinik</p>
                </div>

            </div>
        </aside>

        <!-- MAIN CONTENT (KONTEN HALAMAN) -->
        <main class="flex-1 pt-24 sm:ml-64 p-4">
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

    <!-- ChartJS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>
