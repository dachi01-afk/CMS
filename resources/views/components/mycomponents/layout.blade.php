<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="App Clinic">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">
    <title>CMS-Royal-Klinik</title>
    <link href='{{ asset('storage/assets/royal_klinik.svg') }}' rel='shortcut icon'>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.1/flowbite.min.js" defer></script>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>

    <style>
        .app-sidebar,
        .app-main,
        .app-footer,
        .sidebar-label,
        .sidebar-heading,
        .sidebar-brand-text,
        .sidebar-footer-text {
            transition: all 0.25s ease;
        }

        /* =========================
       DESKTOP ONLY
    ========================= */
        @media (min-width: 640px) {
            .sidebar-expanded .app-sidebar {
                width: 16rem !important;
            }

            .sidebar-collapsed .app-sidebar {
                width: 5rem !important;
            }

            .sidebar-expanded .app-main {
                margin-left: 16rem !important;
            }

            .sidebar-collapsed .app-main {
                margin-left: 5rem !important;
            }

            .sidebar-expanded .app-footer {
                margin-left: 16rem !important;
            }

            .sidebar-collapsed .app-footer {
                margin-left: 5rem !important;
            }

            .sidebar-collapsed .sidebar-label,
            .sidebar-collapsed .sidebar-heading,
            .sidebar-collapsed .sidebar-brand-text,
            .sidebar-collapsed .sidebar-footer-text {
                opacity: 0;
                width: 0;
                overflow: hidden;
                white-space: nowrap;
                pointer-events: none;
                margin: 0 !important;
            }

            .sidebar-collapsed .sidebar-link {
                justify-content: center;
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            .sidebar-collapsed .sidebar-icon {
                margin-right: 0 !important;
            }

            .sidebar-collapsed .app-sidebar:hover .sidebar-label,
            .sidebar-collapsed .app-sidebar:hover .sidebar-heading,
            .sidebar-collapsed .app-sidebar:hover .sidebar-brand-text,
            .sidebar-collapsed .app-sidebar:hover .sidebar-footer-text {
                opacity: 0;
                width: 0;
                overflow: hidden;
                pointer-events: none;
            }
        }

        /* =========================
       MOBILE ONLY
    ========================= */
        @media (max-width: 639px) {
            .app-sidebar {
                width: 16rem !important;
            }

            .app-main,
            .app-footer {
                margin-left: 0 !important;
                width: 100% !important;
            }

            .sidebar-label,
            .sidebar-heading,
            .sidebar-brand-text,
            .sidebar-footer-text {
                opacity: 1 !important;
                width: auto !important;
                overflow: visible !important;
                white-space: normal !important;
                pointer-events: auto !important;
            }

            .sidebar-link {
                justify-content: flex-start !important;
                padding-left: 0.5rem !important;
                padding-right: 0.5rem !important;
            }

            .sidebar-icon {
                margin-right: 0 !important;
            }

            #global-sidebar-tooltip {
                display: none !important;
            }
        }
    </style>
</head>

<div id="global-sidebar-tooltip"
    class="pointer-events-none fixed z-[99999] hidden whitespace-nowrap rounded-md bg-slate-900 px-3 py-2 text-xs font-medium text-white shadow-lg">
</div>

<body id="app-body" class="bg-gray-50 sidebar-expanded">

    <!-- Toast Container -->
    <div id="toast-container" class="fixed bottom-5 right-5 z-50 space-y-2"></div>

    <!-- HEADER -->
    <header class="fixed top-0 w-full z-50">
        <div class="h-1 bg-blue-900 w-full"></div>

        <nav class="h-19 bg-white border-b border-gray-200 shadow-md">
            <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 py-2">

                <!-- Left -->
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <!-- Mobile Sidebar toggle -->
                    <button data-drawer-target="logo-sidebar" data-drawer-toggle="logo-sidebar"
                        aria-controls="logo-sidebar" type="button"
                        class="inline-flex items-center p-2 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none">
                        <span class="sr-only">Open sidebar</span>
                        <i class="fa-solid fa-bars fa-lg"></i>
                    </button>

                    <!-- Desktop Sidebar toggle -->
                    <button id="desktop-sidebar-toggle" type="button"
                        class="hidden sm:inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition">
                        <span class="sr-only">Toggle sidebar desktop</span>
                        <i class="fa-solid fa-bars"></i>
                    </button>

                    <!-- Logo -->
                    <a href="#" class="flex items-center space-x-2">
                        <img src="/storage/assets/royal_klinik.svg" alt="Logo Royal Klinik" class="h-9 w-auto">
                        <h1 class="hidden sm:block text-xl font-bold text-gray-800 whitespace-nowrap">
                            Royal Klinik.id
                        </h1>
                    </a>
                </div>

                <!-- Middle -->
                <div class="flex-1 flex justify-center px-2 md:px-6">
                    {{ $search ?? '' }}
                </div>

                <!-- Right -->
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

    <!-- SUPER ADMIN -->
    @superAdmin
        <div class="flex min-h-screen bg-slate-50 dark:bg-slate-900">
            <!-- SIDEBAR -->
            <aside id="logo-sidebar"
                class="app-sidebar fixed top-0 left-0 z-40 w-64 h-screen pt-20 sm:pt-20 transition-all duration-300 -translate-x-full sm:translate-x-0
       bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm border-r border-slate-200 dark:border-slate-800 shadow-sm"
                aria-label="Sidebar">

                <div class="flex flex-col h-full">
                    <!-- BRANDING -->
                    <div class="px-4 pb-4 border-b border-slate-200 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-100 dark:bg-slate-800 border border-sky-300 dark:border-slate-700 shrink-0">
                                <i class="fa-solid fa-clinic-medical text-sky-600 dark:text-sky-300 text-lg"></i>
                            </div>

                            <div class="flex flex-col min-w-0 sidebar-brand-text">
                                <span
                                    class="text-[11px] font-semibold tracking-wide text-slate-400 dark:text-slate-500 uppercase">
                                    CMS Royal Klinik
                                </span>
                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                    Super Admin Panel
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- NAVIGATION -->
                    <div class="flex-1 px-3 pb-4 overflow-y-auto overflow-x-visible">
                        <nav class="mt-4 space-y-6 text-sm">

                            <div>
                                <p
                                    class="sidebar-heading px-3 mb-2 text-[11px] font-semibold tracking-wide text-slate-400 dark:text-slate-500 uppercase">
                                    Overview
                                </p>
                                <ul class="space-y-1">
                                    <x-mycomponents.sidebar_link href="super.admin.index" class="fa-solid fa-house"
                                        :active="Request::routeIs('super.admin.index')">
                                        Dashboard Utama
                                    </x-mycomponents.sidebar_link>
                                </ul>
                            </div>

                            <div>
                                <p
                                    class="sidebar-heading px-3 mb-2 text-[11px] font-semibold tracking-wide text-slate-400 dark:text-slate-500 uppercase">
                                    Admin & Klinik
                                </p>

                                <ul class="space-y-1 mb-2">
                                    <x-mycomponents.sidebar_link href="jenis.spesialis.index"
                                        class="fa-solid fa-user-doctor" :active="Request::routeIs('jenis.spesialis.index')">
                                        Jenis Spesialis Dokter
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="poli.index" class="fa-solid fa-stethoscope"
                                        :active="Request::routeIs('poli.index')">
                                        Poli
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="kategori.layanan.index"
                                        class="fa-solid fa-folder-open" :active="Request::routeIs('kategori.layanan.index')">
                                        Kategori Layanan
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="layanan.index" class="fa-solid fa-clipboard-list"
                                        :active="Request::routeIs('layanan.index')">
                                        Layanan
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="manajemen_pengguna.index" class="fa-solid fa-users"
                                        :active="Request::routeIs('manajemen_pengguna.index')">
                                        Manajemen Pengguna
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="pengaturan_klinik.index"
                                        class="fa-solid fa-hospital-user" :active="Request::routeIs('pengaturan_klinik.index')">
                                        Pengaturan Klinik
                                    </x-mycomponents.sidebar_link>
                                </ul>

                                <ul class="space-y-1 mb-2">
                                    <x-mycomponents.sidebar_link href="jadwal_kunjungan.index"
                                        class="fa-solid fa-calendar-plus" :active="Request::routeIs('jadwal_kunjungan.index')">
                                        Jadwal Kunjungan
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="order.layanan.index"
                                        class="fa-solid fa-clipboard-list" :active="Request::routeIs('order.layanan.index')">
                                        Order Layanan
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="data_medis_pasien.index"
                                        class="fa-solid fa-notes-medical" :active="Request::routeIs('data_medis_pasien.index')">
                                        Data Medis Pasien
                                    </x-mycomponents.sidebar_link>
                                </ul>

                                <ul class="space-y-1">
                                    <x-mycomponents.sidebar_link href="laporan.index" class="fa-solid fa-chart-line"
                                        :active="Request::routeIs('laporan.index')">
                                        Laporan Klinik
                                    </x-mycomponents.sidebar_link>
                                </ul>
                            </div>

                            <div>
                                <p
                                    class="sidebar-heading px-3 mb-2 text-[11px] font-semibold tracking-wide text-slate-400 dark:text-slate-500 uppercase">
                                    Apotek
                                </p>
                                <ul class="space-y-1">
                                    <x-mycomponents.sidebar_link href="farmasi.dashboard" class="fa-solid fa-house"
                                        :active="Request::routeIs('farmasi.dashboard')">
                                        Dashboard Apotek
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="pengambilan.obat" class="fas fa-list-ol"
                                        :active="Request::routeIs('pengambilan.obat')">
                                        Antrian Hari Ini
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="kategori.obat.index" class="fa-solid fa-capsules"
                                        :active="Request::routeIs('kategori.obat.index')">
                                        Kategori Obat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="obat.index" class="fa-solid fa-pills"
                                        :active="Request::routeIs('obat.index')">
                                        Obat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="penggunaan.obat"
                                        class="fa-solid fa-prescription-bottle-alt" :active="Request::routeIs('penggunaan.obat')">
                                        Penggunaan Obat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="kadaluarsa.obat" class="fa-solid fa-calendar-xmark"
                                        :active="Request::routeIs('kadaluarsa.obat')">
                                        Kadaluarsa Obat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="bahan.habis.pakai" class="fa-solid fa-boxes"
                                        :active="Request::routeIs('bahan.habis.pakai')">
                                        Bahan Habis Pakai
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="penggunaan.bhp"
                                        class="fa-solid fa-hand-holding-medical" :active="Request::routeIs('penggunaan.bhp')">
                                        Penggunaan BHP
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="kadaluarsa.bhp" class="fa-solid fa-calendar-xmark"
                                        :active="Request::routeIs('kadaluarsa.bhp')">
                                        Kadaluarsa BHP
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="cetak.resep.obat" class="fa-solid fa-print"
                                        :active="Request::routeIs('cetak.resep.obat')">
                                        Cetak Resep Obat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="farmasi.restock.obat"
                                        class="fa-solid fa-arrows-rotate" :active="Request::routeIs('farmasi.restock.obat')">
                                        Restock Obat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="farmasi.stok.masuk.obat"
                                        class="fa-solid fa-truck-medical" :active="Request::routeIs('farmasi.stok.masuk.obat')">
                                        Stok Masuk Obat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="farmasi.restock.bahan-habis-pakai"
                                        class="fa-solid fa-arrows-rotate" :active="Request::routeIs('farmasi.restock.bahan-habis-pakai')">
                                        Restock Bahan Habis Pakai
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="farmasi.restock.bahan-habis-pakai"
                                        class="fa-solid fa-arrows-rotate" :active="Request::routeIs('farmasi.restock.bahan-habis-pakai')">
                                        Stok Masuk Bahan Habis Pakai
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="farmasi.return.obat" class="fa-solid fa-undo"
                                        :active="Request::routeIs('farmasi.return.obat')">
                                        Return Obat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="farmasi.return.bahan.habis.pakai"
                                        class="fa-solid fa-undo" :active="Request::routeIs('farmasi.return.bahan.habis.pakai')">
                                        Return Bahan Habis Pakai
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="depot.index" class="fa-solid fa-capsules"
                                        :active="Request::routeIs('depot.index')">
                                        Depot
                                    </x-mycomponents.sidebar_link>
                                </ul>
                            </div>

                            <div>
                                <p
                                    class="sidebar-heading px-3 mb-2 text-[11px] font-semibold tracking-wide text-slate-400 dark:text-slate-500 uppercase">
                                    Kasir
                                </p>
                                <ul class="space-y-1">
                                    <x-mycomponents.sidebar_link href="kasir.dashboard" class="fa-solid fa-house"
                                        :active="Request::routeIs('kasir.dashboard')">
                                        Dashboard Kasir
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="kasir.pembayaran"
                                        class="fa-solid fa-hand-holding-dollar" :active="Request::routeIs('kasir.pembayaran')">
                                        Kasir
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="super.admin.diskon.index"
                                        class="fa-solid fa-circle-check" :active="Request::routeIs('super.admin.diskon.index')">
                                        Approve Diskon
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="super.admin.approve.diskon.penjualan.obat"
                                        class="fa-solid fa-circle-check" :active="Request::routeIs('super.admin.approve.diskon.penjualan.obat')">
                                        Approve Diskon Order Obat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="super.admin.approve.diskon.order.layanan.index"
                                        class="fa-solid fa-circle-check" :active="Request::routeIs('super.admin.approve.diskon.order.layanan.index')">
                                        Approve Diskon Order Layanan
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="kasir.metode.pembayaran" class="fa-solid fa-wallet"
                                        :active="Request::routeIs('kasir.metode.pembayaran')">
                                        Metode Pembayaran
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="kasir.riwayat.transaksi"
                                        class="fa-solid fa-file-invoice" :active="Request::routeIs('kasir.riwayat.transaksi')">
                                        Riwayat Transaksi
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="kasir.hutang" class="fa-solid fa-file-invoice"
                                        :active="Request::routeIs('kasir.hutang')">
                                        Hutang Obat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="kasir.hutang.bahan.habis.pakai"
                                        class="fa-solid fa-file-invoice" :active="Request::routeIs('kasir.hutang.bahan.habis.pakai')">
                                        Hutang Bahan Habis Pakai
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="kasir.piutang.obat"
                                        class="fa-solid fa-file-invoice" :active="Request::routeIs('kasir.piutang.obat')">
                                        Piutang Obat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="kasir.piutang.bahan.habis.pakai"
                                        class="fa-solid fa-file-invoice" :active="Request::routeIs('kasir.piutang.bahan.habis.pakai')">
                                        Piutang Bahan Habis Pakai
                                    </x-mycomponents.sidebar_link>
                                </ul>
                            </div>

                            <div>
                                <p
                                    class="sidebar-heading px-3 mb-2 text-[11px] font-semibold tracking-wide text-slate-400 dark:text-slate-500 uppercase">
                                    Perawat
                                </p>
                                <ul class="space-y-1">
                                    <x-mycomponents.sidebar_link href="perawat.dashboard" class="fa-solid fa-house"
                                        :active="Request::routeIs('perawat.dashboard')">
                                        Dashboard Perawat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="perawat.kunjungan" class="fa-solid fa-user-check"
                                        :active="Request::routeIs('perawat.kunjungan')">
                                        Kunjungan
                                    </x-mycomponents.sidebar_link>
                                </ul>
                            </div>

                            <div>
                                <p
                                    class="sidebar-heading px-3 mb-2 text-[11px] font-semibold tracking-wide text-slate-400 dark:text-slate-500 uppercase">
                                    Pengaturan Sistem
                                </p>
                                <ul class="space-y-1">
                                    <x-mycomponents.sidebar_link href="settings.index" class="fa-solid fa-gear"
                                        :active="Request::routeIs('settings.index')">
                                        Settings
                                    </x-mycomponents.sidebar_link>
                                </ul>
                            </div>
                        </nav>
                    </div>

                    <div
                        class="px-4 py-3 border-t border-slate-200 dark:border-slate-800 text-[11px] text-slate-500 dark:text-slate-400">
                        <div class="sidebar-footer-text">
                            <p class="font-semibold text-slate-700 dark:text-slate-200">Super Admin</p>
                            <p>CMS-Royal-Klinik</p>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="app-main flex-1 pt-24 p-4 sm:ml-64 transition-all duration-300">
                {{ $slot }}
            </main>
        </div>
    @else
        @auth
            @php($role = auth()->user()->role ?? null)

            @if ($role === 'Admin')
                <div class="flex">
                    <aside id="logo-sidebar"
                        class="app-sidebar fixed top-0 left-0 z-40 h-screen pt-20 transition-all duration-300 -translate-x-full sm:translate-x-0
                            bg-white text-slate-700 border-r border-gray-200 w-64">
                        <div class="flex flex-col h-full">
                            <div class="px-4 pb-4 border-b border-gray-200 bg-white">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-100 border border-sky-300 shrink-0">
                                        <i class="fa-solid fa-clinic-medical text-sky-600 text-lg"></i>
                                    </div>

                                    <div class="flex flex-col sidebar-brand-text">
                                        <span class="text-[11px] font-semibold tracking-wide text-gray-400 uppercase">
                                            CMS Royal Klinik
                                        </span>
                                        <span class="text-sm font-semibold text-gray-700">Admin Panel</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-1 px-3 pb-4 overflow-y-auto overflow-x-visible">
                                <nav class="mt-4 space-y-6 text-sm">
                                    <div>
                                        <p
                                            class="sidebar-heading px-3 mb-2 text-[11px] font-semibold tracking-wide text-gray-400 uppercase">
                                            Overview
                                        </p>
                                        <ul class="space-y-1">
                                            <x-mycomponents.sidebar_link href="admin.index" class="fa-solid fa-house"
                                                :active="Request::routeIs('admin.index')">
                                                Dashboard
                                            </x-mycomponents.sidebar_link>
                                        </ul>
                                    </div>

                                    <div>
                                        <p
                                            class="sidebar-heading px-3 mb-2 text-[11px] font-semibold tracking-wide text-gray-400 uppercase">
                                            Master Data
                                        </p>
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
                                            <x-mycomponents.sidebar_link href="layanan.index"
                                                class="fa-solid fa-clipboard-list">
                                                Layanan
                                            </x-mycomponents.sidebar_link>
                                            <x-mycomponents.sidebar_link href="manajemen_pengguna.index"
                                                class="fa-solid fa-users">
                                                Manajemen Pengguna
                                            </x-mycomponents.sidebar_link>
                                            <x-mycomponents.sidebar_link href="pengaturan_klinik.index"
                                                class="fa-solid fa-hospital-user">
                                                Pengaturan Klinik
                                            </x-mycomponents.sidebar_link>
                                        </ul>
                                    </div>

                                    <div>
                                        <p
                                            class="sidebar-heading px-3 mb-2 text-[11px] font-semibold tracking-wide text-gray-400 uppercase">
                                            Operasional Klinik
                                        </p>
                                        <ul class="space-y-1">
                                            <x-mycomponents.sidebar_link href="jadwal_kunjungan.index"
                                                class="fa-solid fa-calendar-plus">
                                                Jadwal Kunjungan
                                            </x-mycomponents.sidebar_link>
                                            <x-mycomponents.sidebar_link href="order.layanan.index"
                                                class="fa-solid fa-clipboard-list" :active="Request::routeIs('order.layanan.index')">
                                                Order Layanan
                                            </x-mycomponents.sidebar_link>
                                        </ul>
                                    </div>

                                    <div>
                                        <p
                                            class="sidebar-heading px-3 mb-2 text-[11px] font-semibold tracking-wide text-gray-400 uppercase">
                                            Laporan & Pengaturan
                                        </p>
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

                            <div class="px-4 py-3 border-t border-gray-200 text-[11px] text-gray-500">
                                <div class="sidebar-footer-text">
                                    <p class="font-semibold text-gray-700">Admin</p>
                                    <p>CMS-Royal-Klinik</p>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <main class="app-main flex-1 pt-24 p-4 sm:ml-64 transition-all duration-300">
                        {{ $slot }}
                    </main>
                </div>
            @elseif($role === 'Farmasi')
                <div class="flex min-h-screen bg-slate-50 dark:bg-slate-900">
                    <aside id="logo-sidebar"
                        class="app-sidebar fixed top-0 left-0 z-40 h-screen pt-20 transition-all duration-300 -translate-x-full sm:translate-x-0
                            bg-white/90 dark:bg-slate-900/90 border-r border-slate-200 dark:border-slate-700 shadow-sm backdrop-blur-sm w-64"
                        aria-label="Sidebar">
                        <div class="h-full flex flex-col px-3 pb-4 overflow-y-auto overflow-x-visible">
                            <div class="mb-4 flex items-center gap-3 px-2">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-cyan-500 text-white shadow-md shrink-0">
                                    <i class="fa-solid fa-prescription-bottle-medical text-lg"></i>
                                </div>
                                <div class="min-w-0 sidebar-brand-text">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-50 truncate">
                                        Panel Farmasi
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        CMS Royal Klinik
                                    </p>
                                </div>
                            </div>

                            <hr class="border-slate-200 dark:border-slate-700 mb-3">

                            <nav class="flex-1">
                                <ul class="space-y-1 text-sm font-medium">
                                    <p
                                        class="sidebar-heading px-2 text-[11px] font-semibold tracking-wide text-slate-400 dark:text-slate-500 uppercase">
                                        Menu Utama
                                    </p>

                                    <x-mycomponents.sidebar_link href="farmasi.dashboard" class="fa-solid fa-house"
                                        :active="Request::routeIs('farmasi.dashboard')">
                                        Dashboard Apotek
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="pengambilan.obat" class="fas fa-list-ol"
                                        :active="Request::routeIs('pengambilan.obat')">
                                        Antrian Hari Ini
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="kategori.obat.index" class="fa-solid fa-capsules"
                                        :active="Request::routeIs('kategori.obat.index')">
                                        Kategori Obat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="obat.index" class="fa-solid fa-pills"
                                        :active="Request::routeIs('obat.index')">
                                        Obat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="penggunaan.obat"
                                        class="fa-solid fa-prescription-bottle-alt" :active="Request::routeIs('penggunaan.obat')">
                                        Penggunaan Obat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="kadaluarsa.obat" class="fa-solid fa-calendar-xmark"
                                        :active="Request::routeIs('kadaluarsa.obat')">
                                        Kadaluarsa Obat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="bahan.habis.pakai" class="fa-solid fa-boxes"
                                        :active="Request::routeIs('bahan.habis.pakai')">
                                        Bahan Habis Pakai
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="penggunaan.bhp"
                                        class="fa-solid fa-hand-holding-medical" :active="Request::routeIs('penggunaan.bhp')">
                                        Penggunaan BHP
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="kadaluarsa.bhp" class="fa-solid fa-calendar-xmark"
                                        :active="Request::routeIs('kadaluarsa.bhp')">
                                        Kadaluarsa BHP
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="cetak.resep.obat" class="fa-solid fa-print"
                                        :active="Request::routeIs('cetak.resep.obat')">
                                        Cetak Resep Obat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="farmasi.restock.obat" class="fa-solid fa-arrows-rotate"
                                        :active="Request::routeIs('farmasi.restock.obat')">
                                        Restock Obat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="farmasi.stok.masuk.obat"
                                        class="fa-solid fa-truck-medical" :active="Request::routeIs('farmasi.stok.masuk.obat')">
                                        Stok Masuk Obat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="farmasi.restock.bahan-habis-pakai"
                                        class="fa-solid fa-arrows-rotate" :active="Request::routeIs('farmasi.restock.bahan-habis-pakai')">
                                        Restock Bahan Habis Pakai
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="farmasi.stok.masuk.bahan.habis.pakai"
                                        class="fa-solid fa-truck-medical" :active="Request::routeIs('farmasi.stok.masuk.bahan.habis.pakai')">
                                        Stok Masuk Bahan Habis Pakai
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="farmasi.return.obat" class="fa-solid fa-undo"
                                        :active="Request::routeIs('farmasi.return.obat')">
                                        Return Obat
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="farmasi.return.bahan.habis.pakai"
                                        class="fa-solid fa-undo" :active="Request::routeIs('farmasi.return.bahan.habis.pakai')">
                                        Return Bahan Habis Pakai
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="depot.index" class="fa-solid fa-capsules"
                                        :active="Request::routeIs('depot.index')">
                                        Depot
                                    </x-mycomponents.sidebar_link>

                                    <hr class="my-3 border-slate-200 dark:border-slate-700">

                                    <x-mycomponents.sidebar_link href="settings.index" class="fa-solid fa-gear"
                                        :active="Request::routeIs('settings.index')">
                                        Settings
                                    </x-mycomponents.sidebar_link>
                                </ul>
                            </nav>

                            <div class="mt-4 px-2">
                                <div class="sidebar-footer-text">
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500">
                                        © {{ date('Y') }} Royal Klinik · Farmasi
                                    </p>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <main class="app-main w-full pt-20 px-4 sm:pt-24 sm:ml-64 transition-all duration-300">
                        {{ $slot }}
                    </main>
                </div>
            @elseif ($role === 'Kasir')
                <div class="flex min-h-screen bg-slate-50 dark:bg-slate-900">
                    <aside id="logo-sidebar"
                        class="app-sidebar fixed top-0 left-0 z-40 h-screen pt-20 transition-all duration-300 -translate-x-full sm:translate-x-0
                            bg-white/95 dark:bg-slate-900/95 backdrop-blur-sm border-r border-slate-200 dark:border-slate-800 shadow-sm w-64"
                        aria-label="Sidebar">

                        <div class="h-full flex flex-col px-3 pb-4 overflow-y-auto overflow-x-visible">
                            <div
                                class="px-4 pb-4 border-b border-slate-200 dark:border-slate-800 bg-white/0 dark:bg-transparent">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-100 dark:bg-slate-800 border border-sky-300 dark:border-slate-700 shrink-0">
                                        <i class="fa-solid fa-cash-register text-sky-600 dark:text-sky-300 text-lg"></i>
                                    </div>

                                    <div class="flex flex-col min-w-0 w-[160px] sidebar-brand-text">
                                        <span
                                            class="text-[11px] font-semibold tracking-wide text-slate-400 dark:text-slate-500 uppercase">
                                            CMS Royal Klinik
                                        </span>
                                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                            Panel Kasir
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <nav class="flex-1 space-y-4 text-sm font-medium">
                                <div class="space-y-1">
                                    <p
                                        class="sidebar-heading px-2 text-[11px] font-semibold tracking-wide uppercase text-slate-400 dark:text-slate-500">
                                        Utama
                                    </p>
                                    <ul class="space-y-1">
                                        <x-mycomponents.sidebar_link href="kasir.dashboard" class="fa-solid fa-house"
                                            :active="Request::routeIs('kasir.dashboard')">
                                            Dashboard
                                        </x-mycomponents.sidebar_link>

                                        <x-mycomponents.sidebar_link href="kasir.pembayaran"
                                            class="fa-solid fa-hand-holding-dollar" :active="Request::routeIs('kasir.pembayaran')">
                                            Kasir
                                        </x-mycomponents.sidebar_link>

                                        <x-mycomponents.sidebar_link href="kasir.metode.pembayaran" class="fa-solid fa-wallet"
                                            :active="Request::routeIs('kasir.metode.pembayaran')">
                                            Metode Pembayaran
                                        </x-mycomponents.sidebar_link>

                                        <x-mycomponents.sidebar_link href="kasir.riwayat.transaksi"
                                            class="fa-solid fa-file-invoice" :active="Request::routeIs('kasir.riwayat.transaksi')">
                                            Riwayat Transaksi
                                        </x-mycomponents.sidebar_link>

                                        <x-mycomponents.sidebar_link href="kasir.hutang" class="fa-solid fa-file-invoice"
                                            :active="Request::routeIs('kasir.hutang')">
                                            Hutang Obat
                                        </x-mycomponents.sidebar_link>

                                        <x-mycomponents.sidebar_link href="kasir.hutang.bahan.habis.pakai"
                                            class="fa-solid fa-file-invoice" :active="Request::routeIs('kasir.hutang.bahan.habis.pakai')">
                                            Hutang Bahan Habis Pakai
                                        </x-mycomponents.sidebar_link>

                                        <x-mycomponents.sidebar_link href="kasir.piutang.obat" class="fa-solid fa-pills"
                                            :active="Request::routeIs('kasir.piutang.obat')">
                                            Piutang Obat
                                        </x-mycomponents.sidebar_link>

                                        <x-mycomponents.sidebar_link href="kasir.piutang.bahan.habis.pakai"
                                            class="fa-solid fa-box-open" :active="Request::routeIs('kasir.piutang.bahan.habis.pakai')">
                                            Piutang Bahan Habis Pakai
                                        </x-mycomponents.sidebar_link>
                                    </ul>
                                </div>

                                <div class="border-t border-slate-200 dark:border-slate-800"></div>

                                <div class="space-y-1">
                                    <p
                                        class="sidebar-heading px-2 text-[11px] font-semibold tracking-wide uppercase text-slate-400 dark:text-slate-500">
                                        Pengaturan
                                    </p>
                                    <ul class="space-y-1">
                                        <x-mycomponents.sidebar_link href="settings.index" class="fa-solid fa-gear"
                                            :active="Request::routeIs('settings.index')">
                                            Settings
                                        </x-mycomponents.sidebar_link>
                                    </ul>
                                </div>
                            </nav>

                            <div class="mt-6 pt-3 border-t border-slate-200 dark:border-slate-800 px-2">
                                <div class="sidebar-footer-text">
                                    <p class="text-[11px] text-slate-400 dark:text-slate-600">
                                        © {{ now()->year }} CMS-Royal-Klinik · Kasir
                                    </p>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <main class="app-main flex-1 w-full pt-16 p-4 sm:pt-24 sm:ml-64 transition-all duration-300">
                        {{ $slot }}
                    </main>
                </div>
            @elseif ($role === 'Perawat')
                <div class="flex min-h-screen bg-slate-50 dark:bg-slate-900">
                    <aside id="logo-sidebar"
                        class="app-sidebar fixed top-0 left-0 z-40 h-screen pt-20 transition-all duration-300 -translate-x-full sm:translate-x-0
                            bg-white/90 dark:bg-slate-900/90 border-r border-slate-200 dark:border-slate-700 shadow-sm backdrop-blur-sm w-64"
                        aria-label="Sidebar">

                        <div class="h-full flex flex-col px-3 pb-4 overflow-y-auto overflow-x-visible">
                            <div class="mb-4 flex items-center gap-3 px-2">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-sky-500 text-white shadow-md shrink-0">
                                    <i class="fa-solid fa-user-nurse text-lg"></i>
                                </div>
                                <div class="sidebar-brand-text">
                                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-50">
                                        Panel Perawat
                                    </p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">
                                        CMS Royal Klinik
                                    </p>
                                </div>
                            </div>

                            <hr class="border-slate-200 dark:border-slate-700 mb-3">

                            <nav class="flex-1">
                                <ul class="space-y-1 text-sm font-medium">
                                    <p
                                        class="sidebar-heading px-2 text-[11px] font-semibold tracking-wide text-slate-400 dark:text-slate-500 uppercase">
                                        Menu Utama
                                    </p>

                                    <x-mycomponents.sidebar_link href="perawat.dashboard" class="fa-solid fa-house"
                                        :active="Request::routeIs('perawat.dashboard')">
                                        Dashboard
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="perawat.kunjungan" class="fa-solid fa-user-check"
                                        :active="Request::routeIs('perawat.kunjungan')">
                                        Kunjungan
                                    </x-mycomponents.sidebar_link>

                                    <x-mycomponents.sidebar_link href="riwayat-pemeriksaan.index"
                                        class="fa-solid fa-clock-rotate-left" :active="Request::routeIs('riwayat-pemeriksaan.*')">
                                        Riwayat Pemeriksaan
                                    </x-mycomponents.sidebar_link>

                                    <hr class="my-3 border-slate-200 dark:border-slate-700">

                                    <p
                                        class="sidebar-heading px-2 text-[11px] font-semibold tracking-wide text-slate-400 dark:text-slate-500 uppercase">
                                        Pengaturan
                                    </p>

                                    <x-mycomponents.sidebar_link href="settings.index" class="fa-solid fa-gear"
                                        :active="Request::routeIs('settings.index')">
                                        Settings
                                    </x-mycomponents.sidebar_link>
                                </ul>
                            </nav>

                            <div class="mt-4 px-2">
                                <div class="sidebar-footer-text">
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500">
                                        © {{ date('Y') }} Royal Klinik · Perawat
                                    </p>
                                </div>
                            </div>
                        </div>
                    </aside>

                    <main class="app-main w-full pt-20 px-4 sm:pt-24 sm:ml-64 transition-all duration-300">
                        {{ $slot }}
                    </main>
                </div>
            @endif
        @endauth
    @endsuperAdmin

    <!-- FOOTER -->
    <footer class="app-footer sm:ml-64 bg-white border-t border-gray-200 mt-8 transition-all duration-300">
        <div class="w-full mx-auto p-4 md:flex md:items-center md:justify-between">
            <p class="text-center text-sm text-gray-500">&copy; 2024 Royal Klinik.id. Hak Cipta Dilindungi.</p>
            <nav class="flex flex-wrap justify-center mt-3 text-sm font-medium text-gray-500 sm:mt-0">
                <a href="#" class="hover:underline me-4 md:me-6">Kebijakan Privasi</a>
                <a href="#" class="hover:underline">Syarat dan Ketentuan</a>
            </nav>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const body = document.getElementById("app-body");
            const toggleBtn = document.getElementById("desktop-sidebar-toggle");
            const tooltip = document.getElementById("global-sidebar-tooltip");

            function hideTooltip() {
                if (!tooltip) return;
                tooltip.classList.add("hidden");
                tooltip.textContent = "";
            }

            function applySidebarState() {
                if (window.innerWidth < 640) {
                    body.classList.remove("sidebar-collapsed");
                    body.classList.add("sidebar-expanded");
                    hideTooltip();
                    return;
                }

                const state = localStorage.getItem("sidebar-state");

                if (state === "collapsed") {
                    body.classList.remove("sidebar-expanded");
                    body.classList.add("sidebar-collapsed");
                } else {
                    body.classList.remove("sidebar-collapsed");
                    body.classList.add("sidebar-expanded");
                }
            }

            applySidebarState();

            if (toggleBtn) {
                toggleBtn.addEventListener("click", function() {
                    if (window.innerWidth < 640) return;

                    const isCollapsed = body.classList.contains("sidebar-collapsed");

                    if (isCollapsed) {
                        body.classList.remove("sidebar-collapsed");
                        body.classList.add("sidebar-expanded");
                        localStorage.setItem("sidebar-state", "expanded");
                        hideTooltip();
                    } else {
                        body.classList.remove("sidebar-expanded");
                        body.classList.add("sidebar-collapsed");
                        localStorage.setItem("sidebar-state", "collapsed");
                    }
                });
            }

            window.addEventListener("resize", function() {
                applySidebarState();
            });

            if (tooltip) {
                function showTooltip(target) {
                    if (window.innerWidth < 640) return;
                    if (!body.classList.contains("sidebar-collapsed")) return;

                    const text = target.getAttribute("data-sidebar-tooltip");
                    if (!text) return;

                    const rect = target.getBoundingClientRect();

                    tooltip.textContent = text;
                    tooltip.classList.remove("hidden");
                    tooltip.style.top = `${rect.top + rect.height / 2}px`;
                    tooltip.style.left = `${rect.right + 12}px`;
                    tooltip.style.transform = "translateY(-50%)";
                }

                document.querySelectorAll(".sidebar-link").forEach((link) => {
                    link.addEventListener("mouseenter", function() {
                        showTooltip(link);
                    });

                    link.addEventListener("mouseleave", function() {
                        hideTooltip();
                    });

                    link.addEventListener("click", function() {
                        hideTooltip();
                    });
                });
            }
        });
    </script>
</body>

</html>
