<x-mycomponents.layout>

    @php
        $activeTab = request('tab', 'dokter');
    @endphp

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
        </div>
        <hr class="mb-2 border-gray-200">

        {{-- sub menu --}}
        <div class="border-b border-gray-200 dark:border-gray-700 ">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center whitespace-nowrap" id="default-tab"
                data-tabs-toggle="#tab-content" role="tablist" data-tabs-active-classes="border-sky-500 text-sky-600"
                data-tabs-inactive-classes="border-transparent text-slate-500 hover:text-slate-600 hover:border-slate-300">

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="dokter-tab"
                        data-tabs-target="#data-dokter" type="button" role="tab" aria-controls="data-dokter"
                        aria-selected="{{ $activeTab === 'dokter' ? 'true' : 'false' }}">
                        Data Dokter
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="farmasi-tab"
                        data-tabs-target="#data-farmasi" type="button" role="tab" aria-controls="data-farmasi"
                        aria-selected="{{ $activeTab === 'farmasi' ? 'true' : 'false' }}">
                        Data Farmasi
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="perawat-tab"
                        data-tabs-target="#data-perawat" type="button" role="tab" aria-controls="data-perawat"
                        aria-selected="{{ $activeTab === 'perawat' ? 'true' : 'false' }}">
                        Data Perawat
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="kasir-tab"
                        data-tabs-target="#data-kasir" type="button" role="tab" aria-controls="data-kasir"
                        aria-selected="{{ $activeTab === 'kasir' ? 'true' : 'false' }}">
                        Data Kasir
                    </button>
                </li>

                <li role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="pasien-tab"
                        data-tabs-target="#data-pasien" type="button" role="tab" aria-controls="data-pasien"
                        aria-selected="{{ $activeTab === 'pasien' ? 'true' : 'false' }}">
                        Data Pasien
                    </button>
                </li>

                <li role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="admin-tab"
                        data-tabs-target="#data-admin" type="button" role="tab" aria-controls="data-admin"
                        aria-selected="{{ $activeTab === 'admin' ? 'true' : 'false' }}">
                        Data Admin
                    </button>
                </li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">
            <div id="tab-content">
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2 {{ $activeTab === 'dokter' ? '' : 'hidden' }}"
                    id="data-dokter" role="tabpanel" aria-labelledby="dokter-tab">
                    @include('admin.manajemenPengguna.data_dokter')
                </div>

                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2 {{ $activeTab === 'farmasi' ? '' : 'hidden' }}"
                    id="data-farmasi" role="tabpanel" aria-labelledby="farmasi-tab">
                    @include('admin.manajemenPengguna.data_farmasi')
                </div>

                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2 {{ $activeTab === 'perawat' ? '' : 'hidden' }}"
                    id="data-perawat" role="tabpanel" aria-labelledby="perawat-tab">
                    @include('admin.manajemenPengguna.data_perawat')
                </div>

                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2 {{ $activeTab === 'kasir' ? '' : 'hidden' }}"
                    id="data-kasir" role="tabpanel" aria-labelledby="kasir-tab">
                    @include('admin.manajemenPengguna.data_kasir')
                </div>

                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2 {{ $activeTab === 'pasien' ? '' : 'hidden' }}"
                    id="data-pasien" role="tabpanel" aria-labelledby="pasien-tab">
                    @include('admin.manajemenPengguna.data_pasien')
                </div>

                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2 {{ $activeTab === 'admin' ? '' : 'hidden' }}"
                    id="data-admin" role="tabpanel" aria-labelledby="admin-tab">
                    @include('admin.manajemenPengguna.data_admin')
                </div>
            </div>
        </div>

    </div>
</x-mycomponents.layout>
