<x-mycomponents.layout>
    {{-- main --}}
    <div>
        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-file-invoice-dollar fa-2xl text-blue-600"></i>

                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Data Hutang Obat
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
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="hutang-tab"
                        data-tabs-target="#data-hutang" type="button" role="tab" aria-controls="data-hutang"
                        aria-selected="false">
                        Data Hutang Obat
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="riwayat-hutang-tab"
                        data-tabs-target="#data-riwayat-hutang" type="button" role="tab"
                        aria-controls="data-riwayat-hutang" aria-selected="false">
                        Data Riwayat Hutang Obat
                    </button>
                </li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">

            {{-- Tabs Content --}}
            <div id="tab-content">
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-hutang" role="tabpanel"
                    aria-labelledby="hutang-tab">
                    @include('kasir.hutang.data-hutang')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-riwayat-hutang"
                    role="tabpanel" aria-labelledby="riwayat-hutang-tab">
                    @include('kasir.hutang.data-riwayat-hutang')
                </div>
            </div>
        </div>
    </div>
</x-mycomponents.layout>
