<x-mycomponents.layout>

    <x-slot:search>
        <x-search_input />
    </x-slot>

    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-6 mt-1">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between space-y-3 sm:space-y-0">
                <!-- Dropdown Pendaftaran -->
                <div class="relative">
                    <button id="dropdownPendaftaranButton" data-dropdown-toggle="dropdownPendaftaran"
                        class="w-full md:w-auto text-gray-900 bg-amber-400 hover:bg-amber-500 focus:ring-4 focus:ring-amber-300 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center justify-center"
                        type="button">
                        Pendaftaran Baru
                        <i class="fa-solid fa-chevron-down ml-2"></i>
                    </button>
                    <div id="dropdownPendaftaran"
                        class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 mt-2">
                        <ul class="py-2 text-sm text-gray-700" aria-labelledby="dropdownPendaftaranButton">
                            <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">Rawat Jalan</a>
                            </li>
                            <li><a href="#" class="block px-4 py-2 hover:bg-gray-100">IGD</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Status Badge -->
                <div class="flex flex-wrap items-center space-x-4 text-sm font-medium">
                    <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-red-500 mr-1"></span>
                        Pending</span>
                    <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-orange-400 mr-1"></span>
                        Confirmed</span>
                    <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-purple-500 mr-1"></span>
                        Waiting</span>
                    <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-blue-500 mr-1"></span>
                        Engaged</span>
                    <span class="flex items-center"><span class="w-2 h-2 rounded-full bg-green-500 mr-1"></span>
                        Succeed</span>
                </div>

                <!-- Kontrol Tanggal -->
                <div class="flex items-center space-x-2">
                    <button class="p-2 text-gray-500 hover:bg-gray-200 rounded-full"><i
                            class="fa-solid fa-chevron-left"></i></button>
                    <div class="text-center">
                        <span class="block text-sm font-semibold text-gray-800">Sabtu</span>
                        <span class="block text-lg font-bold text-gray-700">27 September 2025</span>
                    </div>
                    <button class="p-2 text-gray-500 hover:bg-gray-200 rounded-full"><i
                            class="fa-solid fa-chevron-right"></i></button>
                    <button
                        class="px-4 py-2 text-white bg-blue-500 hover:bg-blue-600 rounded-lg text-sm font-semibold shadow-md">HARI
                        INI</button>
                </div>
            </div>
        </div>

        <!-- Konten -->
        <div class="p-6 bg-white shadow-lg rounded-lg min-h-screen">
            <section id="beranda">
                <h2 class="text-xl font-semibold text-gray-700 mb-2">Judul Artikel 1</h2>
                <p class="text-gray-500">Isi artikel...</p>
            </section>

            <section id="layanan" class="mt-8">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Layanan Kami</h2>
                <!-- Konten layanan -->
            </section>
        </div>

    </div>

</x-mycomponents.layout>
