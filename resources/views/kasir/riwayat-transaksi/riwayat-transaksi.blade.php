<x-layout-kasir>

    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">

                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-file-invoice fa-2xl text-blue-600"></i>

                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Riwayat Transaksi
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
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="riwayat-transaksi-tab"
                        data-tabs-target="#data-riwayat-transaksi" type="button" role="tab"
                        aria-controls="data-riwayat-transaksi" aria-selected="true">
                        Riwayat Transaksi
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="riwayat-transaksi-obat-tab"
                        data-tabs-target="#data-riwayat-transaksi-obat" type="button" role="tab"
                        aria-controls="data-riwayat-transaksi-obat" aria-selected="false">
                        Riwayat Transaksi Obat
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="riwayat-transaksi-layanan-tab"
                        data-tabs-target="#data-riwayat-transaksi-layanan" type="button" role="tab"
                        aria-controls="data-riwayat-transaksi-layanan" aria-selected="false">
                        Riwayat Transaksi Layanan
                    </button>
                </li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">

            {{-- Tabs Content --}}
            <div id="tab-content">
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-riwayat-transaksi"
                    role="tabpanel" aria-labelledby="riwayat-transaksi-tab">
                    @include('kasir.riwayat-transaksi.data-riwayat-transaksi')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-riwayat-transaksi-obat"
                    role="tabpanel" aria-labelledby="riwayat-transaksi-obat-tab">
                    @include('kasir.riwayat-transaksi.data-riwayat-transaksi-obat')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-riwayat-transaksi-layanan"
                    role="tabpanel" aria-labelledby="riwayat-transaksi-layanan-tab">
                    @include('kasir.riwayat-transaksi.data-riwayat-transaksi-layanan')
                </div>
            </div>
        </div>

    </div>

</x-layout-kasir>
