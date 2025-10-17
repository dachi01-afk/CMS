<x-mycomponents.layout>

    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">

                <div class="flex flex-col space-y-1">

                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-gauge-high fa-2xl text-indigo-600"></i>

                        <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                            Jadwal Kunjungan
                        </h1>
                    </div>
                </div>

            </div>
        </div>
        <hr class="mb-2 border-gray-200">

        {{-- sub menu --}}
        <div class="border-b border-gray-200 dark:border-gray-700 ">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center whitespace-nowrap" id="default-tab"
                data-tabs-toggle="#tab-content" role="tablist">

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="tkb-tab" data-tabs-target="#data-tkb"
                        type="button" role="tab" aria-controls="data-tkb" aria-selected="true">
                        Tambah Kunjungan baru
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="tkyad-tab"
                        data-tabs-target="#data-tkyad" type="button" role="tab" aria-controls="data-tkyad">
                        Tambah Kunjungan Yang Akan Datang
                    </button>
                </li>

                <li class="me-2" role="presentation" id="menuProsesKunjungan">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="proses_kunjungan-tab"
                        data-tabs-target="#data-proses_kunjungan" type="button" role="tab"
                        aria-controls="data-proses_kunjungan" aria-selected="false">
                        Proses Kunjungan Hari Ini
                    </button>
                </li>

                <li class="me-2" role="presentation" id="menuProsesKunjunganMasaDepan">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="proses_kunjungan--masa-depan-tab"
                        data-tabs-target="#data-proses_kunjungan_masa_depan" type="button" role="tab"
                        aria-controls="data-proses_kunjungan_masa_depan" aria-selected="false">
                        Kunjungan Yang Akan Datang
                    </button>
                </li>

            </ul>
        </div>


        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">

            {{-- Tabs Content --}}
            <div id="tab-content">
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-tkb" role="tabpanel"
                    aria-labelledby="tkb-tab">
                    @include('admin.jadwalKunjungan.buat_kunjungan_baru')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-tkyad" role="tabpanel"
                    aria-labelledby="tkb-tkyad">
                    @include('admin.jadwalKunjungan.buat-kunjungan-yang-akan-datang')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-proses_kunjungan"
                    role="tabpanel" aria-labelledby="proses_kunjungan-tab">
                    @include('admin.jadwalKunjungan.proses_kunjungan')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2"
                    id="data-proses_kunjungan_masa_depan" role="tabpanel"
                    aria-labelledby="proses_kunjungan-masa-depan-tab">
                    @include('admin.jadwalKunjungan.kunjungan-masa-depan')
                </div>

            </div>

        </div>
    </div>



</x-mycomponents.layout>
