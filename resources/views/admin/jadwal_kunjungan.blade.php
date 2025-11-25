<x-mycomponents.layout>

    @php
        // default tab = tkb (Tambah Kunjungan Baru)
        $activeTab = request('tab', 'tkb');
    @endphp

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

        <!-- Sub Menu -->
        <div class="border-b border-gray-200 dark:border-gray-700 ">
            <!-- Sub Menu -->
            <div class="border-b border-gray-200 dark:border-gray-700 ">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center whitespace-nowrap" id="default-tab"
                    data-tabs-toggle="#tab-content" role="tablist" {{-- Flowbite akan pakai ini utk TAB AKTIF --}}
                    data-tabs-active-classes="border-sky-500 text-sky-600" {{-- Flowbite pakai ini utk TAB NON-AKTIF --}}
                    data-tabs-inactive-classes="border-transparent text-slate-500 hover:text-slate-600 hover:border-slate-300">

                    {{-- TAB 1: Tambah Kunjungan Baru --}}
                    <li class="me-2" role="presentation">
                        <button id="tkb-tab" data-tabs-target="#data-tkb" type="button" role="tab"
                            aria-controls="data-tkb" aria-selected="{{ $activeTab === 'tkb' ? 'true' : 'false' }}"
                            class="inline-block p-4 border-b-2 rounded-t-lg">
                            Tambah Kunjungan Baru
                        </button>
                    </li>

                    {{-- TAB 2: Tambah Kunjungan Yang Akan Datang --}}
                    <li class="me-2" role="presentation">
                        <button id="tkyad-tab" data-tabs-target="#data-tkyad" type="button" role="tab"
                            aria-controls="data-tkyad" aria-selected="{{ $activeTab === 'tkyad' ? 'true' : 'false' }}"
                            class="inline-block p-4 border-b-2 rounded-t-lg">
                            Tambah Kunjungan Yang Akan Datang
                        </button>
                    </li>

                    {{-- TAB 3: Proses Kunjungan Hari Ini --}}
                    <li class="me-2" role="presentation" id="menuProsesKunjungan">
                        <button id="proses_kunjungan-tab" data-tabs-target="#data-proses_kunjungan" type="button"
                            role="tab" aria-controls="data-proses_kunjungan"
                            aria-selected="{{ $activeTab === 'proses_kunjungan' ? 'true' : 'false' }}"
                            class="inline-block p-4 border-b-2 rounded-t-lg">
                            Proses Kunjungan Hari Ini
                        </button>
                    </li>

                    {{-- TAB 4: Kunjungan Yang Akan Datang --}}
                    <li class="me-2" role="presentation" id="menuProsesKunjunganMasaDepan">
                        <button id="proses_kunjungan-masa-depan-tab"
                            data-tabs-target="#data-proses_kunjungan_masa_depan" type="button" role="tab"
                            aria-controls="data-proses_kunjungan_masa_depan"
                            aria-selected="{{ $activeTab === 'kunjungan_masa_depan' ? 'true' : 'false' }}"
                            class="inline-block p-4 border-b-2 rounded-t-lg">
                            Kunjungan Yang Akan Datang
                        </button>
                    </li>

                </ul>
            </div>

        </div>

        <!-- Kontent -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">
            {{-- Tabs Content --}}
            <div id="tab-content">
                {{-- TAB 1 --}}
                <div id="data-tkb" role="tabpanel" aria-labelledby="tkb-tab"
                    class="mt-2 p-4 rounded-lg bg-gray-50 dark:bg-gray-800 {{ $activeTab === 'tkb' ? '' : 'hidden' }}">
                    @include('admin.jadwalKunjungan.buat_kunjungan_baru')
                </div>

                {{-- TAB 2 --}}
                <div id="data-tkyad" role="tabpanel" aria-labelledby="tkyad-tab"
                    class="mt-2 p-4 rounded-lg bg-gray-50 dark:bg-gray-800 {{ $activeTab === 'tkyad' ? '' : 'hidden' }}">
                    @include('admin.jadwalKunjungan.buat-kunjungan-yang-akan-datang')
                </div>

                {{-- TAB 3 --}}
                <div id="data-proses_kunjungan" role="tabpanel" aria-labelledby="proses_kunjungan-tab"
                    class="mt-2 p-4 rounded-lg bg-gray-50 dark:bg-gray-800 {{ $activeTab === 'proses_kunjungan' ? '' : 'hidden' }}">
                    @include('admin.jadwalKunjungan.proses_kunjungan')
                </div>

                {{-- TAB 4 --}}
                <div id="data-proses_kunjungan_masa_depan" role="tabpanel"
                    aria-labelledby="proses_kunjungan-masa-depan-tab"
                    class="mt-2 p-4 rounded-lg bg-gray-50 dark:bg-gray-800 {{ $activeTab === 'kunjungan_masa_depan' ? '' : 'hidden' }}">
                    @include('admin.jadwalKunjungan.kunjungan-masa-depan')
                </div>
            </div>
        </div>
    </div>



</x-mycomponents.layout>
