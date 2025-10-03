<x-mycomponents.layout>

    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-6 mt-1">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between space-y-3 sm:space-y-0">

                <div class="flex items-center space-x-2 ml-auto">
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
