<x-mycomponents.layout>
    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">

                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-calendar-xmark fa-2xl text-blue-600"></i>

                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Kadaluarsa Obat
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
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="data-kadaluarsa-obat-tab"
                        data-tabs-target="#data-kadaluarsa-obat" type="button" role="tab"
                        aria-controls="data-kadaluarsa-obat" aria-selected="true">
                        Data Kadaluarsa Obat
                    </button>
                </li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">

            {{-- Tabs Content --}}
            <div id="tab-content">
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-kadaluarsa-obat"
                    role="tabpanel" aria-labelledby="data-kadaluarsa-obat-tab">
                    @include('farmasi.kadaluarsa-obat.data-kadaluarsa-obat')
                </div>
            </div>
        </div>

    </div>
</x-mycomponents.layout>
