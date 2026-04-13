<x-mycomponents.layout>

    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">

                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-circle-check fa-2xl text-blue-600"></i>
                    <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-extrabold text-gray-900 leading-tight">
                        Approve Diskon Order Obat
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
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="tab-belum-approve"
                        data-tabs-target="#data-belum-approve" type="button" role="tab"
                        aria-controls="data-belum-approve" aria-selected="true">
                        Belum Approve
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="tab-sudah-approve"
                        data-tabs-target="#data-sudah-approve" type="button" role="tab"
                        aria-controls="data-sudah-approve" aria-selected="false">
                        Sudah Approve
                    </button>
                </li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">

            {{-- Tabs Content --}}
            <div id="tab-content">
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-belum-approve"
                    role="tabpanel" aria-labelledby="tab-belum-approve">
                    @include('super-admin.approve-diskon-penjualan-obat.data-belum-approve-diskon-penjualan-obat')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-sudah-approve"
                    role="tabpanel" aria-labelledby="tab-sudah-approve">
                    @include('super-admin.approve-diskon-penjualan-obat.data-sudah-approve-diskon-penjualan-obat')
                </div>
            </div>
        </div>
    </div>

    </div>
</x-mycomponents.layout>
