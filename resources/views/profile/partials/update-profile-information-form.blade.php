@php
    $profileConfig = [
        'Super Admin' => [
            'relation' => 'superAdmin',
            'nama_field' => 'nama_super_admin',
            'foto_field' => 'foto_super_admin',
            'no_hp_field' => 'no_hp_super_admin',
            'label_nama' => 'Nama Super Admin',
            'label_hp' => 'No. HP Super Admin',
            'placeholder_nama' => 'Nama super admin yang tampil di sistem',
            'title_detail' => 'Informasi Super Admin Klinik',
        ],
        'Admin' => [
            'relation' => 'admin',
            'nama_field' => 'nama_admin',
            'foto_field' => 'foto_admin',
            'no_hp_field' => 'no_hp',
            'label_nama' => 'Nama Admin',
            'label_hp' => 'No. HP Admin',
            'placeholder_nama' => 'Nama admin yang tampil di sistem',
            'title_detail' => 'Informasi Admin Klinik',
        ],
        'Farmasi' => [
            'relation' => 'farmasi',
            'nama_field' => 'nama_farmasi',
            'foto_field' => 'foto_farmasi',
            'no_hp_field' => 'no_hp_farmasi',
            'label_nama' => 'Nama Farmasi',
            'label_hp' => 'No. HP Farmasi',
            'placeholder_nama' => 'Nama farmasi yang tampil di sistem',
            'title_detail' => 'Informasi Farmasi',
        ],
        'Kasir' => [
            'relation' => 'kasir',
            'nama_field' => 'nama_kasir',
            'foto_field' => 'foto_kasir',
            'no_hp_field' => 'no_hp_kasir',
            'label_nama' => 'Nama Kasir',
            'label_hp' => 'No. HP Kasir',
            'placeholder_nama' => 'Nama kasir yang tampil di sistem',
            'title_detail' => 'Informasi Kasir',
        ],

        /*
        'Dokter' => [
            'relation' => 'dokter',
            'nama_field' => 'nama_dokter',
            'foto_field' => 'foto_dokter',
            'no_hp_field' => 'no_hp_dokter',
            'label_nama' => 'Nama Dokter',
            'label_hp' => 'No. HP Dokter',
            'placeholder_nama' => 'Nama dokter yang tampil di sistem',
            'title_detail' => 'Informasi Dokter',
        ],
        */
    ];

    $config = $profileConfig[$user->role] ?? null;
    $detail = $config ? $user->{$config['relation']} : null;
@endphp

@if ($config)
    <section class="space-y-6">
        <header>
            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-regular fa-user-circle text-indigo-500 text-xl"></i>
                <span>Informasi Profil {{ $user->role }}</span>
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Kelola informasi akun dan identitas profil yang digunakan di dalam sistem.
            </p>
        </header>

        @if (session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <form method="post" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data"
            class="mt-4 space-y-6">
            @csrf
            @method('put')

            <div class="grid grid-cols-1 md:grid-cols-[auto,1fr] gap-6 items-start">
                <div class="flex flex-col items-center">
                    <div id="preview-foto-profil"
                        class="relative h-24 w-24 rounded-full overflow-hidden border-2 border-indigo-100 bg-slate-100">
                        @if ($detail && $detail->{$config['foto_field']})
                            <img id="foto-preview-image"
                                src="{{ asset('storage/' . $detail->{$config['foto_field']}) }}" alt="Foto Profil"
                                class="h-full w-full object-cover">
                        @else
                            <img id="foto-preview-image" src="" alt="Preview Foto"
                                class="h-full w-full object-cover hidden">
                            <div id="foto-preview-placeholder"
                                class="h-full w-full flex items-center justify-center text-slate-400">
                                <i class="fa-regular fa-user text-3xl"></i>
                            </div>
                        @endif
                    </div>

                    <label for="foto_profil"
                        class="mt-3 inline-flex items-center gap-2 text-xs font-medium text-indigo-600 hover:text-indigo-700 cursor-pointer">
                        <i class="fa-solid fa-camera text-xs"></i>
                        <span>Ubah foto profil</span>
                    </label>

                    <input id="foto_profil" name="foto_profil" type="file" accept=".jpg,.jpeg,.png,image/*"
                        class="hidden">

                    <p class="text-[11px] text-slate-400 mt-1">
                        Format: JPG, PNG, maksimal 2MB.
                    </p>

                    <x-input-error class="mt-2" :messages="$errors->get('foto_profil')" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="username" value="Username" />
                        <x-text-input id="username" name="username" type="text" class="mt-1 block w-full"
                            :value="old('username', $user->username)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('username')" />
                    </div>

                    <div>
                        <x-input-label for="role" value="Role Sistem" />
                        <x-text-input id="role" type="text"
                            class="mt-1 block w-full bg-slate-100 border-slate-200 text-slate-700" :value="$user->role"
                            disabled />
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                            :value="old('email', $user->email)" required autocomplete="username" />
                        <x-input-error class="mt-2" :messages="$errors->get('email')" />
                    </div>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-200">
                <h3 class="text-sm font-semibold text-slate-800 mb-3 flex items-center gap-2">
                    <span
                        class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 text-[11px] font-bold">
                        i
                    </span>
                    {{ $config['title_detail'] }}
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="nama" :value="$config['label_nama']" />
                        <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full"
                            :value="old('nama', $detail->{$config['nama_field']} ?? '')" :placeholder="$config['placeholder_nama']" required />
                        <x-input-error class="mt-2" :messages="$errors->get('nama')" />
                    </div>

                    <div>
                        <x-input-label for="no_hp" :value="$config['label_hp']" />
                        <x-text-input id="no_hp" name="no_hp" type="text" class="mt-1 block w-full"
                            :value="old('no_hp', $detail->{$config['no_hp_field']} ?? '')" placeholder="08xxxxxxxxxx" />
                        <x-input-error class="mt-2" :messages="$errors->get('no_hp')" />
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-slate-200">
                <x-primary-button>
                    <i class="fa-regular fa-floppy-disk mr-2"></i>
                    Simpan Perubahan
                </x-primary-button>

                @if (session('status') === 'profile-updated')
                    <p class="text-sm text-emerald-600 flex items-center gap-1">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>Perubahan tersimpan.</span>
                    </p>
                @endif
            </div>
        </form>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputFoto = document.getElementById('foto_profil');
            const previewImage = document.getElementById('foto-preview-image');
            const previewPlaceholder = document.getElementById('foto-preview-placeholder');

            if (inputFoto) {
                inputFoto.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (previewImage) {
                            previewImage.src = e.target.result;
                            previewImage.classList.remove('hidden');
                        }

                        if (previewPlaceholder) {
                            previewPlaceholder.classList.add('hidden');
                        }
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
    </script>
@else
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        Role <strong>{{ $user->role }}</strong> belum didukung pada form profil.
    </div>
@endif
