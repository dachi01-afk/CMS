<x-mycomponents.layout>
    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center justify-between">

                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-gear fa-2xl text-gray-700"></i>

                    <h1 class="text-3xl font-extrabold text-gray-900 leading-tight">
                        Pengaturan Sistem
                    </h1>
                </div>
            </div>
        </div>
        <hr class="mb-2 border-gray-200">

        {{-- sub menu --}}
        <div class="border-b border-gray-200 dark:border-gray-700 ">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center whitespace-nowrap" id="default-tab"
                data-tabs-toggle="#tab-content" role="tablist">

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="informasi-profile-tab"
                        data-tabs-target="#data-informasi-profile" type="button" role="tab"
                        aria-controls="data-informasi-profile" aria-selected="true">
                        Informasi Profile
                    </button>
                </li>

                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="ubah-password-tab"
                        data-tabs-target="#data-ubah-password" type="button" role="tab"
                        aria-controls="data-ubah-password" aria-selected="false">
                        Ubah Password
                    </button>
                </li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">

            {{-- Tabs Content --}}
            <div id="tab-content">
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-informasi-profile"
                    role="tabpanel" aria-labelledby="informasi-profile-tab">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-ubah-password"
                    role="tabpanel" aria-labelledby="ubah-password-tab">
                    @include('profile.partials.update-password-form')
                </div>

                {{-- <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-laporan-resep-dan-apotek"
                    role="tabpanel" aria-labelledby="laporan-resep-dan-apotek-tab">
                    @include('profile.partials.delete-user-form')
                </div> --}}
            </div>

        </div>

    </div>

</x-mycomponents.layout>
