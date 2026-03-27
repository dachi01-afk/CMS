<x-mycomponents.layout>
    {{-- main --}}
    <div>
        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-boxes-stacked fa-2xl text-blue-600"></i>

                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Data Restock Bahan Habis Pakai
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
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="restock-bhp-tab"
                        data-tabs-target="#data-restock-bhp" type="button" role="tab"
                        aria-controls="data-restock-bhp" aria-selected="false">
                        Data Restock Bahan Habis Pakai
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="riwayat-restock-bhp-tab"
                        data-tabs-target="#data-riwayat-restock-bhp" type="button" role="tab"
                        aria-controls="data-riwayat-restock-bhp" aria-selected="false">
                        Data Riwayat Restock Bahan Habis Pakai
                    </button>
                </li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">

            {{-- Tabs Content --}}
            <div id="tab-content">
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-restock-bhp"
                    role="tabpanel" aria-labelledby="restock-bhp-tab">
                    @include('farmasi.restock-bahan-habis-pakai.data-restock-bahan-habis-pakai')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-riwayat-restock-bhp"
                    role="tabpanel" aria-labelledby="riwayat-restock-bhp-tab">
                    @include('farmasi.restock-bahan-habis-pakai.data-riwayat-restock-bahan-habis-pakai')
                </div>
            </div>

        </div>
    </div>
</x-mycomponents.layout>
