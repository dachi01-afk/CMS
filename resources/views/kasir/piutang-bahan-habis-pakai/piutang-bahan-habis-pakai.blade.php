<x-mycomponents.layout>
    {{-- main --}}
    <div>
        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-box-open fa-2xl text-blue-600"></i>

                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Data Piutang Bahan Habis Pakai
                    </h1>
                </div>
            </div>
        </div>
        <hr class="mb-2 border-gray-200">

        {{-- sub menu --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center whitespace-nowrap" id="piutang-bhp-tab"
                data-tabs-toggle="#piutang-bhp-tab-content" role="tablist">
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="data-piutang-bhp-tab"
                        data-tabs-target="#data-piutang-bhp" type="button" role="tab"
                        aria-controls="data-piutang-bhp" aria-selected="false">
                        Data Piutang Bahan Habis Pakai
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="riwayat-piutang-bhp-tab"
                        data-tabs-target="#riwayat-piutang-bhp" type="button" role="tab"
                        aria-controls="riwayat-piutang-bhp" aria-selected="false">
                        Data Riwayat Piutang Bahan Habis Pakai
                    </button>
                </li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">
            <div id="piutang-bhp-tab-content">
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-piutang-bhp"
                    role="tabpanel" aria-labelledby="data-piutang-bhp-tab">
                    @include('kasir.piutang-bahan-habis-pakai.data-piutang-bahan-habis-pakai')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="riwayat-piutang-bhp"
                    role="tabpanel" aria-labelledby="riwayat-piutang-bhp-tab">
                    @include('kasir.piutang-bahan-habis-pakai.data-riwayat-piutang-bahan-habis-pakai')
                </div>
            </div>
        </div>
    </div>
</x-mycomponents.layout>
