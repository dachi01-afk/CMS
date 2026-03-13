<section class="space-y-6">
    <header>
        <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-lock text-indigo-500 text-xl"></i>
            <span>Ubah Password</span>
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Gunakan password yang kuat agar akun tetap aman.
        </p>
    </header>

    @if (session('success_password'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            {{ session('success_password') }}
        </div>
    @endif

    <form method="post" action="{{ route('settings.password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div class="space-y-5">
            <div>
                <x-input-label for="update_password_current_password" value="Password Saat Ini" />
                <x-text-input id="update_password_current_password" name="current_password" type="password"
                    class="mt-1 block w-full" autocomplete="current-password"
                    placeholder="Masukkan password saat ini" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="update_password_password" value="Password Baru" />
                <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full"
                    autocomplete="new-password" placeholder="Masukkan password baru" />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />

                <p class="mt-2 text-xs text-slate-500">
                    Password minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol.
                </p>
            </div>

            <div>
                <x-input-label for="update_password_password_confirmation" value="Konfirmasi Password Baru" />
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password"
                    class="mt-1 block w-full" autocomplete="new-password" placeholder="Ulangi password baru" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center gap-4 pt-4 border-t border-slate-200">
            <x-primary-button>
                <i class="fa-regular fa-floppy-disk mr-2"></i>
                Simpan Password
            </x-primary-button>

            @if (session('status') === 'password-updated')
                <p class="text-sm text-emerald-600 flex items-center gap-1">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Password berhasil diperbarui.</span>
                </p>
            @endif
        </div>
    </form>
</section>
