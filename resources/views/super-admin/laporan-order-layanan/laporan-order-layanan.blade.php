<x-mycomponents.layout>

    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">

                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-file-invoice-dollar fa-2xl text-blue-600"></i>

                    <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-extrabold text-gray-900 leading-tight">
                        Laporan Transaksi Order Layanan
                    </h1>
                </div>
            </div>
        </div>
        <hr class="mb-2 border-gray-200">

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">
            @include('super-admin.laporan-order-layanan.data-laporan-order-layanan')
        </div>

    </div>

</x-mycomponents.layout>
