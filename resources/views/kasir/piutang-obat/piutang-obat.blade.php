<x-mycomponents.layout>
    {{-- main --}}
    <div>
        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-pills fa-2xl text-blue-600"></i>

                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Data Piutang Obat
                    </h1>
                </div>
            </div>
        </div>
        <hr class="mb-2 border-gray-200">

        {{-- sub menu --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center whitespace-nowrap" id="piutang-obat-tab"
                data-tabs-toggle="#piutang-obat-tab-content" role="tablist">
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="data-piutang-obat-tab"
                        data-tabs-target="#data-piutang-obat" type="button" role="tab"
                        aria-controls="data-piutang-obat" aria-selected="false">
                        Data Piutang Obat
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="riwayat-piutang-obat-tab"
                        data-tabs-target="#riwayat-piutang-obat" type="button" role="tab"
                        aria-controls="riwayat-piutang-obat" aria-selected="false">
                        Data Riwayat Piutang Obat
                    </button>
                </li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">
            <div id="piutang-obat-tab-content">
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-piutang-obat"
                    role="tabpanel" aria-labelledby="data-piutang-obat-tab">
                    @include('kasir.piutang-obat.data-piutang-obat')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="riwayat-piutang-obat"
                    role="tabpanel" aria-labelledby="riwayat-piutang-obat-tab">
                    @include('kasir.piutang-obat.data-riwayat-piutang-obat')
                </div>
            </div>
        </div>
    </div>
</x-mycomponents.layout>
