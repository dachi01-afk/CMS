<x-mycomponents.layout>

    {{-- main --}}
    <div>
        <div class="mb-2 mt-2">
            <div class="flex items-center justify-between">

                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-user-doctor fa-2xl text-blue-600"></i>

                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Manajemen Dokter
                    </h1>
                </div>

            </div>

            <p class="mt-1 text-sm text-gray-500">
                Kelola data, jadwal, dan informasi kontak seluruh tenaga medis.
            </p>
        </div>
        <hr class="mb-2 border-gray-200">

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">
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
