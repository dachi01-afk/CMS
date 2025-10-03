<x-mycomponents.layout>

    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">

                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-users fa-2xl text-red-600"></i>

                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Manajemen Pasien
                    </h1>
                </div>

                <button type="button"
                    class="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50 transition duration-150 ease-in-out">
                    <i class="fa-solid fa-plus mr-2"></i>
                    Tambah Pasien Baru
                </button>
            </div>

            <p class="mt-1 text-sm text-gray-500">
                Kelola data dasar dan riwayat kunjungan seluruh pasien klinik.
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
