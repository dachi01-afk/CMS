<x-mycomponents.layout>
    {{-- main --}}
    <div>
        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-truck-medical fa-2xl text-blue-600"></i>

                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Data Stok Masuk Obat
                    </h1>
                </div>
            </div>
        </div>
        <hr class="mb-2 border-gray-200">

        {{-- sub menu --}}
        <div class="border-b border-gray-200 dark:border-gray-700 ">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center whitespace-nowrap" id="default-tab"
                data-tabs-toggle="#tab-content" role="tablist">
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="stok-masuk-obat-tab"
                        data-tabs-target="#data-stok-masuk-obat" type="button" role="tab"
                        aria-controls="data-stok-masuk-obat" aria-selected="false">
                        Data Stok Masuk Obat
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="riwayat-stok-masuk-obat-tab"
                        data-tabs-target="#data-riwayat-stok-masuk-obat" type="button" role="tab"
                        aria-controls="data-riwayat-stok-masuk-obat" aria-selected="false">
                        Data Riwayat Stok Masuk Obat
                    </button>
                </li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">

            {{-- Tabs Content --}}
            <div id="tab-content">
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-stok-masuk-obat"
                    role="tabpanel" aria-labelledby="stok-masuk-obat-tab">
                    @include('farmasi.stok-masuk-obat.data-stok-masuk-obat')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-riwayat-stok-masuk-obat"
                    role="tabpanel" aria-labelledby="riwayat-stok-masuk-obat-tab">
                    @include('farmasi.stok-masuk-obat.data-riwayat-stok-masuk-obat')
                </div>
            </div>
        </div>
    </div>
</x-mycomponents.layout>
