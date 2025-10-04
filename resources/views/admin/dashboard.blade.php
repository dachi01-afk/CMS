<x-mycomponents.layout>

    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">

                <div class="flex flex-col space-y-1">

                    <p class="text-lg font-medium text-gray-500">
                        Selamat Datang Kembali, Admin!
                    </p>

                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-gauge-high fa-2xl text-indigo-600"></i>

                        <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                            Dashboard Utama
                        </h1>
                    </div>
                </div>

                <div class="hidden sm:flex items-center text-gray-600 space-x-2">
                    <i class="fa-regular fa-calendar-days"></i>
                    <span class="font-semibold text-sm">{{ date('d M Y') }}</span>
                </div>
            </div>

            <p class="mt-2 text-sm text-gray-500">
                Ringkasan statistik klinis dan performa sistem secara keseluruhan.
            </p>
        </div>
        <hr class="mb-2 border-gray-200">


        <!-- Konten -->
        <div class="p-2 shadow-lg rounded-lg min-h-screen">

        </div>

    </div>

    @vite(['resources/js/admin/dashboard.js'])
</x-mycomponents.layout>
