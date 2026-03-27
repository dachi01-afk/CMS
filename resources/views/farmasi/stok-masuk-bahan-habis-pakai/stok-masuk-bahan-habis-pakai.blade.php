<x-mycomponents.layout>
    {{-- main --}}
    <div>
        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-truck-medical fa-2xl text-blue-600"></i>

                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Data Stok Masuk Bahan Habis Pakai
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
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="stok-masuk-bahan-habis-pakai-tab"
                        data-tabs-target="#data-stok-masuk-bahan-habis-pakai" type="button" role="tab"
                        aria-controls="data-stok-masuk-bahan-habis-pakai" aria-selected="false">
                        Data Stok Masuk Bahan Habis Pakai
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="riwayat-stok-masuk-bhp-tab"
                        data-tabs-target="#data-riwayat-stok-masuk-bhp" type="button" role="tab"
                        aria-controls="data-riwayat-stok-masuk-bhp" aria-selected="false">
                        Data Riwayat Stok Masuk Bahan Habis Pakai
                    </button>
                </li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">

            {{-- Tabs Content --}}
            <div id="tab-content">
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2"
                    id="data-stok-masuk-bahan-habis-pakai" role="tabpanel"
                    aria-labelledby="stok-masuk-bahan-habis-pakai-tab">
                    @include('farmasi.stok-masuk-bahan-habis-pakai.data-stok-masuk-bahan-habis-pakai')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-riwayat-stok-masuk-bhp"
                    role="tabpanel" aria-labelledby="riwayat-stok-masuk-bhp-tab">
                    @include('farmasi.stok-masuk-bahan-habis-pakai.data-riwayat-stok-masuk-bahan-habis-pakai')
                </div>
            </div>
        </div>
    </div>
</x-mycomponents.layout>
