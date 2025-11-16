<section class="space-y-6">
    {{-- HEADER --}}
    <header>
        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            <i class="fa-regular fa-user-circle text-indigo-500 text-xl"></i>
            <span>Informasi Profil Admin</span>
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Kelola informasi akun, identitas admin, dan kontak yang digunakan di dalam sistem.
        </p>
    </header>

    {{-- FORM KIRIM ULANG VERIFIKASI EMAIL --}}
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    {{-- FORM UTAMA --}}
    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-4 space-y-6">
        @csrf

        {{-- BLOK ATAS: AVATAR + DATA AKUN --}}
        <div class="grid grid-cols-1 md:grid-cols-[auto,1fr] gap-6 items-start">

            {{-- FOTO PROFIL --}}
            <div class="flex flex-col items-center">
                <div class="relative h-24 w-24 rounded-full overflow-hidden border-2 border-indigo-100 bg-slate-100">
                    @php
                        $fotoAdmin = $user->admin->foto_admin ?? null;
                    @endphp

                    @if ($fotoAdmin)
                        <img src="{{ asset('storage/' . $fotoAdmin) }}" alt="Foto Admin"
                            class="h-full w-full object-cover">
                    @else
                        <div class="h-full w-full flex items-center justify-center text-slate-400">
                            <i class="fa-regular fa-user text-3xl"></i>
                        </div>
                    @endif
                </div>

                <label for="foto_admin"
                    class="mt-3 inline-flex items-center gap-2 text-xs font-medium text-indigo-600 hover:text-indigo-700 cursor-pointer">
                    <i class="fa-solid fa-camera text-xs"></i>
                    <span>Ubah foto profil</span>
                </label>
                <input id="foto_admin" name="foto_admin" type="file" accept="image/*" class="hidden">
                <p class="text-[11px] text-slate-400 mt-1">
                    Format: JPG, PNG, MAX 2MB.
                </p>

                @error('foto_admin')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- DATA AKUN (USER) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Nama User (dari tabel users) --}}
                <div class="md:col-span-2">
                    <x-input-label for="name" value="Nama Akun (User)" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                        :value="old('name', $user->name)" required autofocus autocomplete="name" />
                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                </div>

                {{-- Username (read only, kalau ada) --}}
                @if (isset($user->username))
                    <div>
                        <x-input-label for="username" value="Username" />
                        <x-text-input id="username" type="text"
                            class="mt-1 block w-full bg-slate-100 border-slate-200 text-slate-700" :value="$user->username"
                            disabled />
                        <p class="mt-1 text-[11px] text-slate-400">
                            Username tidak dapat diubah dari halaman ini.
                        </p>
                    </div>
                @endif

                {{-- Role (read only) --}}
                @if (isset($user->role))
                    <div>
                        <x-input-label for="role" value="Role Sistem" />
                        <x-text-input id="role" type="text"
                            class="mt-1 block w-full bg-slate-100 border-slate-200 text-slate-700" :value="ucfirst($user->role)"
                            disabled />
                    </div>
                @endif

                {{-- Email --}}
                <div class="md:col-span-2">
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
                        :value="old('email', $user->email)" required autocomplete="username" />
                    <x-input-error class="mt-2" :messages="$errors->get('email')" />

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && !$user->hasVerifiedEmail())
                        <div class="mt-2">
                            <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                                Alamat email Anda belum terverifikasi.
                                <button form="send-verification"
                                    class="ml-1 underline text-xs font-semibold text-amber-800 hover:text-amber-900">
                                    Klik di sini untuk mengirim ulang email verifikasi.
                                </button>
                            </p>

                            @if (session('status') === 'verification-link-sent')
                                <p class="mt-2 text-xs font-medium text-emerald-600">
                                    Link verifikasi baru telah dikirim ke email Anda.
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- BLOK BAWAH: DATA ADMIN (TABEL admin) --}}
        <div class="pt-4 border-t border-slate-200">
            <h3 class="text-sm font-semibold text-slate-800 mb-3 flex items-center gap-2">
                <span
                    class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 text-[11px] font-bold">
                    i
                </span>
                Informasi Admin Klinik
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Nama Admin (tabel admin.nama_admin) --}}
                <div>
                    <x-input-label for="nama_admin" value="Nama Admin" />
                    <x-text-input id="nama_admin" name="nama_admin" type="text" class="mt-1 block w-full"
                        :value="old('nama_admin', $user->admin->nama_admin ?? '')" placeholder="Nama admin yang tampil di sistem" />
                    <x-input-error class="mt-2" :messages="$errors->get('nama_admin')" />
                </div>

                {{-- No HP --}}
                <div>
                    <x-input-label for="no_hp" value="No. HP Admin" />
                    <x-text-input id="no_hp" name="no_hp" type="text" class="mt-1 block w-full"
                        :value="old('no_hp', $user->admin->no_hp ?? '')" placeholder="08xxxxxxxxxx" />
                    <x-input-error class="mt-2" :messages="$errors->get('no_hp')" />
                </div>
            </div>
        </div>

        {{-- TOMBOL SIMPAN --}}
        <div class="flex items-center gap-4 pt-4 border-t border-slate-200">
            <x-primary-button>
                <i class="fa-regular fa-floppy-disk mr-2"></i>
                {{ __('Simpan Perubahan') }}
            </x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-emerald-600 flex items-center gap-1">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ __('Perubahan tersimpan.') }}</span>
                </p>
            @endif
        </div>
    </form>
</section>
