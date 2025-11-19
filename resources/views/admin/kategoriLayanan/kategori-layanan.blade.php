<x-mycomponents.layout>
    {{-- main --}}
    <div>

        <!-- Header Halaman -->
        <div class="mb-4 mt-2">
            <div class="flex items-center space-x-3">
                <i class="fa-solid fa-folder-open fa-2xl text-blue-600"></i>
                <h1 class="text-xl sm:text-2xl md:text-3xl lg:text-4xl font-extrabold text-gray-900 leading-tight">
                    Kategori Layanan
                </h1>
            </div>
        </div>

        <hr class="mb-2 border-gray-200">

        {{-- sub menu --}}
        <div class="border-b border-gray-200 dark:border-gray-700 ">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center whitespace-nowrap" id="default-tab"
                data-tabs-toggle="#tab-content" role="tablist">
                <li class="me-2" role="presentation">
                    <button class="inline-block p-4 border-b-2 rounded-t-lg" id="kategori-layanan-tab"
                        data-tabs-target="#data-kategori-layanan" type="button" role="tab"
                        aria-controls="data-kategori-layanan" aria-selected="true">
                        Kategori Layanan
                    </button>
                </li>
            </ul>
        </div>

        <!-- Konten -->
        <div class="p-2 bg-white shadow-lg rounded-lg min-h-screen">

            {{-- Tabs Content --}}
            <div id="tab-content">
                <div class="hidden p-4 rounded-lg bg-gray-50 dark:bg-gray-800 mt-2" id="data-kategori-layanan"
                    role="tabpanel" aria-labelledby="kategori-layanan-tab">
                    @include('admin.kategoriLayanan.data-kategori-layanan')
                </div>
            </div>

        </div>

    </div>

    </div>
</x-mycomponents.layout>
