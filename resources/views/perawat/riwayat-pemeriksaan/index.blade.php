<x-mycomponents.layout>
    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-clock-rotate-left fa-2xl text-blue-600"></i>
                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Riwayat Pemeriksaan
                    </h1>
                </div>
            </div>

            {{-- optional subtitle kalau mau, tapi kamu sebelumnya ga pakai di kunjungan --}}
            {{-- <p class="mt-1 text-sm text-gray-500">
                Menampilkan hasil pemeriksaan yang sudah tersimpan (Radiologi, EMR, dan Hasil Lab).
            </p> --}}
        </div>
        <hr class="mb-2 border-gray-200">

        {{-- sub menu (TAB) --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center whitespace-nowrap" id="riwayat-tab"
                data-tabs-toggle="#riwayat-tab-content" role="tablist">

                {{-- TAB: Radiologi --}}
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="riwayat-radiologi-tab"
                        data-tabs-target="#riwayat-radiologi" type="button" role="tab"
                        aria-controls="riwayat-radiologi" aria-selected="false">
                        Radiologi
                    </button>
                </li>

                {{-- TAB: EMR --}}
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="riwayat-emr-tab"
                        data-tabs-target="#riwayat-emr" type="button" role="tab" aria-controls="riwayat-emr"
                        aria-selected="false">
                        EMR
                    </button>
                </li>

                {{-- TAB: Hasil Lab --}}
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="riwayat-lab-tab"
                        data-tabs-target="#riwayat-lab" type="button" role="tab" aria-controls="riwayat-lab"
                        aria-selected="false">
                        Laboratorium
                    </button>
                </li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">
            <div id="riwayat-tab-content">

                {{-- Content: Radiologi --}}
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="riwayat-radiologi"
                    role="tabpanel" aria-labelledby="riwayat-radiologi-tab">
                    @include('perawat.riwayat-pemeriksaan.data-radiologi')
                </div>

                {{-- Content: EMR --}}
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="riwayat-emr" role="tabpanel"
                    aria-labelledby="riwayat-emr-tab">
                    @include('perawat.riwayat-pemeriksaan.data-emr')
                </div>

                {{-- Content: Lab --}}
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="riwayat-lab" role="tabpanel"
                    aria-labelledby="riwayat-lab-tab">
                    @include('perawat.riwayat-pemeriksaan.data-lab')
                </div>

            </div>
        </div>

    </div>
</x-mycomponents.layout>


@vite(['resources/js/perawat/riwayat-pemeriksaan/data-radiologi.js'])
