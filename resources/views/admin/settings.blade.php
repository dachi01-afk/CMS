<x-mycomponents.layout>

    <div class="py-6">

        <!-- HEADER -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="h-11 w-11 rounded-xl bg-indigo-100 flex items-center justify-center shadow-sm">
                    <i class="fa-solid fa-gear text-indigo-600 text-2xl"></i>
                </div>

                <div>
                    <h1 class="text-3xl font-bold text-slate-800">
                        Pengaturan Sistem
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">
                        Sesuaikan informasi akun dan password Anda di sini.
                    </p>
                </div>
            </div>
        </div>

        <!-- TABS MENU -->
        <div class="w-full border-b border-slate-200">
            <ul id="default-tab" data-tabs-toggle="#tab-content" class="flex gap-4 text-sm font-semibold">

                <li class="group" role="presentation">
                    <button id="informasi-profile-tab" data-tabs-target="#data-informasi-profile" type="button"
                        role="tab"
                        class="py-3 px-4 border-b-2 border-transparent group-[.active]:border-indigo-500
                               text-slate-600 group-[.active]:text-indigo-600 transition-all">
                        <i class="fa-regular fa-user mr-1.5"></i>
                        Informasi Profile
                    </button>
                </li>

                <li class="group" role="presentation">
                    <button id="ubah-password-tab" data-tabs-target="#data-ubah-password" type="button" role="tab"
                        class="py-3 px-4 border-b-2 border-transparent group-[.active]:border-indigo-500
                               text-slate-600 group-[.active]:text-indigo-600 transition-all">
                        <i class="fa-solid fa-lock mr-1.5"></i>
                        Ubah Password
                    </button>
                </li>
            </ul>
        </div>

        <!-- CONTENT WRAPPER -->
        <div class="mt-5 bg-white/70 backdrop-blur-sm border border-slate-200 rounded-2xl shadow p-6">

            <div id="tab-content">

                <!-- INFORMASI PROFILE -->
                <div id="data-informasi-profile" role="tabpanel"
                    class="hidden p-4 bg-slate-50 rounded-xl border border-slate-200 shadow-inner">
                    @include('profile.partials.update-profile-information-form')
                </div>

                <!-- UBAH PASSWORD -->
                <div id="data-ubah-password" role="tabpanel"
                    class="hidden p-4 bg-slate-50 rounded-xl border border-slate-200 shadow-inner">
                    @include('profile.partials.update-password-form')
                </div>

            </div>
        </div>

    </div>

</x-mycomponents.layout>
