<x-mycomponents.layout>

    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">

                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-notes-medical fa-2xl text-blue-600"></i>

                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Transaksi Pembayaran
                    </h1>
                </div>
            </div>

            {{-- <p class="mt-1 text-sm text-gray-500">
                Kelola data dasar dan riwayat kunjungan seluruh pasien klinik.
            </p> --}}
        </div>
        <hr class="mb-2 border-gray-200">

        {{-- sub menu --}}
        <div class="border-b border-gray-200 dark:border-gray-700 ">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center whitespace-nowrap" id="default-tab"
                data-tabs-toggle="#tab-content" role="tablist">
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="transaksi-menunggu-tab"
                        data-tabs-target="#data-transaksi-menunggu" type="button" role="tab"
                        aria-controls="data-transaksi-menunggu" aria-selected="false">
                        Transaksi Menunggu
                    </button>
                </li>
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="riwayat-transaksi-tab"
                        data-tabs-target="#data-riwayat-transaksi" type="button" role="tab"
                        aria-controls="data-riwayat-transaksi" aria-selected="false">
                        Riwayat Transaksi
                    </button>
                </li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">

            {{-- Tabs Content --}}
            <div id="tab-content">
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-transaksi-menunggu"
                    role="tabpanel" aria-labelledby="transaksi-menunggu-tab">
                    @include('admin.pembayaran.transaksi-menungggu')
                </div>
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-riwayat-transaksi"
                    role="tabpanel" aria-labelledby="riwayat-transaksi-tab">
                    @include('admin.pembayaran.riwayat-transaksi')
                </div>
            </div>
        </div>

    </div>

</x-mycomponents.layout>
