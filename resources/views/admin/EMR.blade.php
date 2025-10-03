<x-mycomponents.layout>

    {{-- search --}}
    <x-slot:search>
        <x-search_input />
    </x-slot>

    <!-- Header Halaman -->
    <div class="mb-6 mt-1">
        <div class="flex items-center space-x-10">
            <div class="relative">
                <button href="#"
                    class="w-full md:w-auto text-gray-900 bg-amber-400 hover:bg-amber-500 focus:ring-4 focus:ring-amber-300 font-medium rounded-lg text-sm px-5 py-2.5 flex items-center justify-center"
                    type="button">
                    Advance Search
                </button>

            </div>

            <div class="flex flex-wrap items-center space-x-8 text-sm font-medium">
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

        </div>
    </div>

    {{-- main --}}
    <div>
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
