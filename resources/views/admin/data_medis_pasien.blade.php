<x-mycomponents.layout>

    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">

                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-notes-medical fa-2xl text-blue-600"></i>

                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Data Medis Pasien
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
                {{-- 
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="rme-tab" data-tabs-target="#data-rme"
                        type="button" role="tab" aria-controls="data-rme" aria-selected="true">
                        Rekam Medis Elektronik
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="diagnosa-dan-konsultasi-tab"
                        data-tabs-target="#data-diagnosa-dan-konsultasi" type="button" role="tab"
                        aria-controls="data-diagnosa-dan-konsultasi" aria-selected="false">
                        Data Diagnosa Dan Konsultasi
                    </button>
                </li>


                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="hasil-laboratorium-tab"
                        data-tabs-target="#data-hasil-laboratorium" type="button" role="tab"
                        aria-controls="data-hasil-laboratorium" aria-selected="false">
                        Data Hasil Laboratorium
                    </button>
                </li> --}}

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="emr-tab" data-tabs-target="#data-emr"
                        type="button" role="tab" aria-controls="data-emr" aria-selected="false">
                        EMR
                    </button>
                </li>

            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">

            {{-- Tabs Content --}}
            <div id="tab-content">
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-rme" role="tabpanel"
                    aria-labelledby="rme-tab">
                    @include('admin.dataMedisPasien.rekam_medis_elektronik')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-diagnosa-dan-konsultasi"
                    role="tabpanel" aria-labelledby="diagnosa-dan-konsultasi-tab">
                    @include('admin.dataMedisPasien.data_diagnosa_dan_konsultasi')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-hasil-laboratorium"
                    role="tabpanel" aria-labelledby="hasil-laboratorium-tab">
                    @include('admin.dataMedisPasien.data_hasil_lab')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-emr" role="tabpanel"
                    aria-labelledby="emr-tab">
                    @include('admin.dataMedisPasien.emr')
                </div>

            </div>

        </div>

    </div>

</x-mycomponents.layout>
