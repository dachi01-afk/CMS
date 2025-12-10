<x-mycomponents.layout>

    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">

                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-hospital fa-2xl text-blue-600"></i>

                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Pengaturan Klinik
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
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="jadwal-dokter-tab"
                        data-tabs-target="#data-jadwal-dokter" type="button" role="tab"
                        aria-controls="data-jadwal-dokter" aria-selected="true">
                        Jadwal Dokter
                    </button>
                </li>

                {{-- <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="daftar-obat-tab"
                        data-tabs-target="#data-daftar-obat" type="button" role="tab"
                        aria-controls="data-daftar-obat" aria-selected="false">
                        Daftar Obat
                    </button>
                </li> --}}


                {{-- <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="daftar-layanan-tab"
                        data-tabs-target="#data-daftar-layanan" type="button" role="tab"
                        aria-controls="data-daftar-layanan" aria-selected="false">
                        Daftar Layanan
                    </button>
                </li> --}}

                {{-- <li role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="jenis-tes-lab-tab"
                        data-tabs-target="#data-jenis-tes-lab" type="button" role="tab"
                        aria-controls="data-jenis-tes-lab" aria-selected="false">
                        Jenis Tes Lab
                    </button>
                </li> --}}
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">

            {{-- Tabs Content --}}
            <div id="tab-content">
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-jadwal-dokter"
                    role="tabpanel" aria-labelledby="jadwal-dokter-tab">
                    @include('admin.pengaturanKlinik.jadwal_dokter')
                </div>

                {{-- <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-daftar-obat"
                    role="tabpanel" aria-labelledby="daftar-obat-tab">
                    @include('admin.pengaturanKlinik.daftar_obat')
                </div> --}}

                {{-- 
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-daftar-layanan"
                    role="tabpanel" aria-labelledby="daftar-layanan-tab">
                    @include('admin.pengaturanKlinik.daftar_layanan')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-jenis-tes-lab"
                    role="tabpanel" aria-labelledby="jenis-tes-lab-tab">
                    @include('admin.pengaturanKlinik.jenis_tes_lab')
                </div> --}}
            </div>

        </div>

    </div>

</x-mycomponents.layout>
