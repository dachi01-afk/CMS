<x-mycomponents.layout>

    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">

                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-chart-line fa-2xl text-blue-600"></i>

                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Laporan
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
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="laporan-kunjungan-tab"
                        data-tabs-target="#data-laporan-kunjungan" type="button" role="tab"
                        aria-controls="data-laporan-kunjungan" aria-selected="true">
                        Laporan Kunjungan
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="laporan-keuangan-tab"
                        data-tabs-target="#data-laporan-keuangan" type="button" role="tab"
                        aria-controls="data-laporan-keuangan" aria-selected="false">
                        Laporan Keuangan
                    </button>
                </li>


                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="laporan-resep-dan-apotek-tab"
                        data-tabs-target="#data-laporan-resep-dan-apotek" type="button" role="tab"
                        aria-controls="data-laporan-resep-dan-apotek" aria-selected="false">
                        Laporan Resep & Transaksi Apotek
                    </button>
                </li>

                <li role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="laporan-administrasi-tab"
                        data-tabs-target="#data-laporan-administrasi" type="button" role="tab"
                        aria-controls="data-laporan-administrasi" aria-selected="false">
                        Laporan Administrasi
                    </button>
                </li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">

            {{-- Tabs Content --}}
            <div id="tab-content">
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-laporan-kunjungan"
                    role="tabpanel" aria-labelledby="laporan-kunjungan-tab">
                    @include('admin.laporan.laporan_kunjungan')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-laporan-keuangan"
                    role="tabpanel" aria-labelledby="laporan-keuangan-tab">
                    @include('admin.laporan.laporan_keuangan')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-laporan-resep-dan-apotek"
                    role="tabpanel" aria-labelledby="laporan-resep-dan-apotek-tab">
                    @include('admin.laporan.laporan_resep_dan_apotek')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-laporan-administrasi"
                    role="tabpanel" aria-labelledby="laporan-administrasi-tab">
                    @include('admin.laporan.laporan_administrasi')
                </div>
            </div>

        </div>

    </div>

</x-mycomponents.layout>
