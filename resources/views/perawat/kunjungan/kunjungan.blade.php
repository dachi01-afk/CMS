<x-mycomponents.layout>
    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-user-check fa-2xl text-blue-600"></i>
                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Kunjungan
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

                <!-- Kunjungan Hari Ini -->
                {{-- <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="kunjungan-hari-ini-tab"
                        data-tabs-target="#data-kunjungan-hari-ini" type="button" role="tab"
                        aria-controls="data-kunjungan-hari-ini" aria-selected="true">
                        Kunjungan Hari Ini
                    </button>
                </li> --}}

                <!-- Kunjungan Triage Pasien -->
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="triage-pasien-ini-tab"
                        data-tabs-target="#data-triage-pasien" type="button" role="tab"
                        aria-controls="data-triage-pasien" aria-selected="false">
                        Triage Pasien
                    </button>
                </li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">

            <!-- Tabs Content Kunjungan Hari Ini -->
            <div id="tab-content">
                {{-- <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-kunjungan-hari-ini"
                    role="tabpanel" aria-labelledby="kunjungan-hari-ini-tab">
                    @include('perawat.kunjungan.data-kunjungan-hari-ini')
                </div> --}}

                <!-- Tabs Content Kunjungan Hari Ini -->
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-triage-pasien"
                    role="tabpanel" aria-labelledby="triage-pasien-tab">
                    @include('perawat.kunjungan.data-triage-pasien')
                </div>
            </div>
        </div>

    </div>

</x-mycomponents.layout>
