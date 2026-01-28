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

                <!-- Kunjungan Triage Pasien -->
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="triage-pasien-ini-tab"
                        data-tabs-target="#data-triage-pasien" type="button" role="tab"
                        aria-controls="data-triage-pasien" aria-selected="false">
                        Triage Pasien
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="tes-lab-tab"
                        data-tabs-target="#data-tes-lab" type="button" role="tab" aria-controls="data-tes-lab"
                        aria-selected="false">
                        Tes Laboratorium
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="tes-radiologi-tab"
                        data-tabs-target="#data-tes-radiologi" type="button" role="tab"
                        aria-controls="data-tes-radiologi" aria-selected="false">
                        Tes Radiologi
                    </button>
                </li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">

            <!-- Tabs Content Kunjungan Hari Ini -->
            <div id="tab-content">
                <!-- Tabs Content Kunjungan Hari Ini -->
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-triage-pasien"
                    role="tabpanel" aria-labelledby="triage-pasien-tab">
                    @include('perawat.kunjungan.data-triage-pasien')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-tes-lab" role="tabpanel"
                    aria-labelledby="tes-lab-tab">
                    @include('perawat.kunjungan.data-tes-lab')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-tes-radiologi"
                    role="tabpanel" aria-labelledby="tes-radiologi-tab">
                    @include('perawat.kunjungan.data-tes-radiologi')
                </div>
            </div>
        </div>

    </div>

</x-mycomponents.layout>
