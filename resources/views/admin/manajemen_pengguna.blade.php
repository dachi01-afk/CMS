<x-mycomponents.layout>

    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">

                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-users fa-2xl text-blue-600"></i>
                    <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-extrabold text-gray-900 leading-tight">
                        Manajemen Pengguna
                    </h1>
                </div>
            </div>

            <p class="mt-1 text-sm text-gray-500">
                Kelola data dasar dan riwayat kunjungan seluruh pasien klinik.
            </p>
        </div>
        <hr class="mb-2 border-gray-200">

        {{-- sub menu --}}
        <div class="border-b border-gray-200 dark:border-gray-700 ">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center whitespace-nowrap" id="default-tab"
                data-tabs-toggle="#tab-content" role="tablist">

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="pengguna-tab"
                        data-tabs-target="#data-pengguna" type="button" role="tab" aria-controls="data-pengguna"
                        aria-selected="true">
                        Data Pengguna
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="dokter-tab"
                        data-tabs-target="#data-dokter" type="button" role="tab" aria-controls="data-dokter"
                        aria-selected="false">
                        Data Dokter
                    </button>
                </li>

                {{-- <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="staf-tab"
                        data-tabs-target="#data-staf" type="button" role="tab" aria-controls="data-staf"
                        aria-selected="false">
                        Data Staf Administrasi
                    </button>
                </li> --}}

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="apoteker-tab"
                        data-tabs-target="#data-apoteker" type="button" role="tab" aria-controls="data-apoteker"
                        aria-selected="false">
                        Data Apoteker
                    </button>
                </li>

                <li role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="pasien-tab"
                        data-tabs-target="#data-pasien" type="button" role="tab" aria-controls="data-pasien"
                        aria-selected="false">
                        Data Pasien
                    </button>
                </li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">

            {{-- Tabs Content --}}
            <div id="tab-content">
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-pengguna" role="tabpanel"
                    aria-labelledby="pengguna-tab">
                    @include('admin.manajemenPengguna.data_pengguna')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-dokter" role="tabpanel"
                    aria-labelledby="dokter-tab">
                    @include('admin.manajemenPengguna.data_dokter')
                </div>

                {{-- <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-staf" role="tabpanel"
                    aria-labelledby="staf-tab">
                    @include('admin.manajemenPengguna.data_staf_administrasi')
                </div> --}}

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-apoteker" role="tabpanel"
                    aria-labelledby="apoteker-tab">
                    @include('admin.manajemenPengguna.data_apoteker')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-pasien" role="tabpanel"
                    aria-labelledby="pasien-tab">
                    @include('admin.manajemenPengguna.data_pasien')
                </div>
            </div>

        </div>

    </div>

    </div>

</x-mycomponents.layout>
