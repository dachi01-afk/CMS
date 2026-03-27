<x-mycomponents.layout>
    {{-- main --}}
    <div>
        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-notes-medical fa-2xl text-blue-600"></i>

                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Data Return Bahan Habis Pakai
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
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="return-bhp-tab"
                        data-tabs-target="#data-return-bhp" type="button" role="tab"
                        aria-controls="data-return-bhp" aria-selected="false">
                        Data Return Bahan Habis Pakai
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="riwayat-return-bhp-tab"
                        data-tabs-target="#data-riwayat-return-bhp" type="button" role="tab"
                        aria-controls="data-riwayat-return-bhp" aria-selected="false">
                        Data Riwayat Return Bahan Habis Pakai
                    </button>
                </li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">

            {{-- Tabs Content --}}
            <div id="tab-content">
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-return-bhp" role="tabpanel"
                    aria-labelledby="return-bhp-tab">
                    @include('farmasi.return-bahan-habis-pakai.data-return-bahan-habis-pakai')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-riwayat-return-bhp"
                    role="tabpanel" aria-labelledby="riwayat-return-bhp-tab">
                    @include('farmasi.return-bahan-habis-pakai.data-riwayat-return-bahan-habis-pakai')
                </div>
            </div>

        </div>
    </div>
</x-mycomponents.layout>
