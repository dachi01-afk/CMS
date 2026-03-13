<x-mycomponents.layout>
    <div class="py-6">

        {{-- HEADER --}}
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

        @if (session('error'))
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <i class="fa-solid fa-circle-exclamation mr-2"></i>
                {{ session('error') }}
            </div>
        @endif

        {{-- TAB MENU --}}
        <div class="w-full border-b border-slate-200">
            <ul class="flex gap-6 text-sm font-semibold">
                <li>
                    <button type="button" id="tab-btn-profile"
                        class="inline-flex items-center gap-2 py-3 px-2 border-b-2 border-indigo-500 text-indigo-600 transition-all">
                        <i class="fa-regular fa-user"></i>
                        Informasi Profile
                    </button>
                </li>

                <li>
                    <button type="button" id="tab-btn-password"
                        class="inline-flex items-center gap-2 py-3 px-2 border-b-2 border-transparent text-slate-600 hover:text-indigo-600 transition-all">
                        <i class="fa-solid fa-lock"></i>
                        Ubah Password
                    </button>
                </li>
            </ul>
        </div>

        {{-- CONTENT --}}
        <div class="mt-5 bg-white/70 backdrop-blur-sm border border-slate-200 rounded-2xl shadow p-6">
            <div id="panel-profile">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div id="panel-password" class="hidden">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profileBtn = document.getElementById('tab-btn-profile');
            const passwordBtn = document.getElementById('tab-btn-password');
            const profilePanel = document.getElementById('panel-profile');
            const passwordPanel = document.getElementById('panel-password');
            const fotoInput = document.getElementById('foto_admin');
            const fotoPreview = document.getElementById('preview-foto-admin');

            function setActiveTab(tabName) {
                if (tabName === 'password') {
                    profilePanel.classList.add('hidden');
                    passwordPanel.classList.remove('hidden');

                    profileBtn.classList.remove('border-indigo-500', 'text-indigo-600');
                    profileBtn.classList.add('border-transparent', 'text-slate-600');

                    passwordBtn.classList.remove('border-transparent', 'text-slate-600');
                    passwordBtn.classList.add('border-indigo-500', 'text-indigo-600');
                } else {
                    passwordPanel.classList.add('hidden');
                    profilePanel.classList.remove('hidden');

                    passwordBtn.classList.remove('border-indigo-500', 'text-indigo-600');
                    passwordBtn.classList.add('border-transparent', 'text-slate-600');

                    profileBtn.classList.remove('border-transparent', 'text-slate-600');
                    profileBtn.classList.add('border-indigo-500', 'text-indigo-600');
                }
            }

            if (profileBtn) {
                profileBtn.addEventListener('click', function() {
                    setActiveTab('profile');
                });
            }

            if (passwordBtn) {
                passwordBtn.addEventListener('click', function() {
                    setActiveTab('password');
                });
            }

            @if ($errors->updatePassword->any() || session('status') === 'password-updated')
                setActiveTab('password');
            @else
                setActiveTab('profile');
            @endif

            if (fotoInput && fotoPreview) {
                fotoInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = function(event) {
                        fotoPreview.innerHTML =
                            `<img src="${event.target.result}" alt="Preview Foto Admin" class="h-full w-full object-cover">`;
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
    </script>
</x-mycomponents.layout>
