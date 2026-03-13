<section class="space-y-5">

    {{-- HEADER + CTA --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-user-shield text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Data Admin
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola akun admin klinik.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-3">
            <button type="button"
                class="hidden md:inline-flex items-center gap-2 px-3 py-2 text-xs md:text-sm rounded-xl border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-600">
                <i class="fa-regular fa-circle-question text-sm"></i>
                <span>Panduan Manajemen Admin</span>
            </button>

            <button id="btnAddAdmin" type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-xl shadow-md
                       bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                       focus:outline-none focus:ring-2 focus:ring-sky-400">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Data Admin</span>
            </button>
        </div>
    </div>

    {{-- CARD TABEL --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm">

        {{-- Toolbar --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">

            {{-- Page length --}}
            <div class="flex items-center gap-2 text-sm">
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>
                <select id="admin_pageLength"
                    class="border border-slate-300 dark:border-slate-600 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500
                           bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-1 w-24">
                    <option value="10">10 baris</option>
                    <option value="25">25 baris</option>
                    <option value="50">50 baris</option>
                    <option value="100">100 baris</option>
                </select>
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">per halaman</span>
            </div>

            {{-- Search --}}
            <div class="w-full md:w-auto">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                    </span>
                    <input type="text" id="admin_searchInput"
                        class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm text-slate-800 dark:text-slate-100
                               border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700
                               focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari nama admin, username, email, atau no HP...">
                </div>
                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Contoh: <span class="italic">Admin Klinik</span>.
                </p>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table id="adminTable" data-url="{{ route('manajemen_pengguna.data_admin') }}"
                data-show-url="{{ url('/manajemen_pengguna/get_admin_by_id') }}"
                data-delete-url="{{ url('/manajemen_pengguna/delete_admin') }}"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100 border-t border-slate-100 dark:border-slate-700">
                <thead
                    class="text-xs font-semibold uppercase bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600 text-white tracking-wide">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Profil</th>
                        <th class="px-6 py-3">Nama Admin</th>
                        <th class="px-6 py-3">Username</th>
                        <th class="px-6 py-3">Email Akun</th>
                        <th class="px-6 py-3">Role</th>
                        <th class="px-6 py-3">No HP</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 gap-3 rounded-b-2xl">
            <div id="admin_customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>
            <ul id="admin_customPagination"
                class="inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
            </ul>
        </div>
    </div>
</section>

{{-- Modal Add Admin --}}
<div id="addAdminModal" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center w-full h-full p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="relative w-full max-w-5xl">
        <div
            class="relative bg-white rounded-2xl shadow-2xl dark:bg-slate-800 border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh] overflow-y-auto">

            {{-- Header --}}
            <div
                class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-sky-500 to-teal-500 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-sky-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-user-plus text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-slate-50">
                            Tambah Data Admin
                        </h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">
                            Lengkapi data akun admin untuk pengelolaan operasional klinik.
                        </p>
                    </div>
                </div>

                <button type="button" id="closeAddAdminModal_header"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <form id="formAddAdmin" class="px-6 pb-5 pt-4 flex flex-col gap-4 bg-slate-50/60 dark:bg-slate-800"
                data-url="{{ route('manajemen_pengguna.add_admin') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div
                    class="flex items-center gap-2 text-xs rounded-xl px-3 py-2
                           bg-emerald-50 text-emerald-700 border border-emerald-100
                           dark:bg-emerald-900/40 dark:text-emerald-100 dark:border-emerald-800">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Akun admin digunakan untuk mengelola modul admin pada sistem klinik.</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-[auto,1fr] gap-6 items-start mt-1">

                    {{-- Foto --}}
                    <div class="flex justify-center md:justify-start">
                        <div id="foto_drop_area_admin"
                            class="relative w-32 md:w-36 aspect-[3/4] rounded-xl border-2 border-dashed border-sky-300/80 
                                   flex items-center justify-center cursor-pointer overflow-hidden bg-slate-50 dark:bg-slate-700 transition">

                            <img id="preview_foto_admin" src="" alt="Preview Foto"
                                class="hidden w-full h-full object-cover">

                            <div id="placeholder_foto_admin"
                                class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-[11px] px-2 text-center">
                                <i class="fa-solid fa-user-circle text-xl mb-1"></i>
                                <span>Upload foto profil admin (opsional)</span>
                            </div>

                            <input type="file" name="foto_admin" id="foto_admin" accept="image/*"
                                class="absolute inset-0 opacity-0 cursor-pointer">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                            Akun Admin
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label for="username"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Username</label>
                                <input type="text" name="username" id="username"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Username admin" required>
                                <div class="field-error text-red-600 text-xs mt-1" data-error-for="username"></div>
                            </div>

                            <div class="space-y-1">
                                <label for="nama_admin"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Nama
                                    Admin</label>
                                <input type="text" name="nama_admin" id="nama_admin"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Nama Admin" required>
                                <div class="field-error text-red-600 text-xs mt-1" data-error-for="nama_admin"></div>
                            </div>

                            <div class="space-y-1">
                                <label for="email"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Email</label>
                                <input type="email" name="email" id="email"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="admin@example.com" required>
                                <div class="field-error text-red-600 text-xs mt-1" data-error-for="email"></div>
                            </div>

                            <div class="space-y-1">
                                <label for="no_hp"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">No HP</label>
                                <input type="text" name="no_hp" id="no_hp"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="0812xxxxxxxx">
                                <div class="field-error text-red-600 text-xs mt-1" data-error-for="no_hp"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="field-error text-red-600 text-xs mt-1 text-center" data-error-for="foto_admin"></div>

                <div class="mt-4 space-y-3 border-t border-slate-200 dark:border-slate-700 pt-4">
                    <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                        Keamanan Akun
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="password"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">Password</label>
                            <input type="password" name="password" id="password"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="••••••••" required>
                            <div class="field-error text-red-600 text-xs mt-1" data-error-for="password"></div>
                        </div>

                        <div class="space-y-1">
                            <label for="password_confirmation"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">Konfirmasi
                                Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="••••••••" required>
                            <div class="field-error text-red-600 text-xs mt-1" data-error-for="password_confirmation">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <button type="button" id="closeAddAdminModal_footer"
                        class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-slate-200 rounded-xl 
                               hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit" id="saveAdminButton"
                        class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl 
                               bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                               focus:ring-2 focus:ring-sky-400">
                        Simpan Data Admin
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Admin --}}
<div id="editAdminModal" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center w-full h-full p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="relative w-full max-w-5xl">
        <div
            class="relative bg-white rounded-2xl shadow-2xl dark:bg-slate-800 border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh] overflow-y-auto">

            {{-- Header --}}
            <div
                class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-sky-500 to-teal-500 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div
                        class="h-10 w-10 rounded-xl bg-teal-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-user-pen text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-slate-50">
                            Edit Data Admin
                        </h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">
                            Perbarui informasi akun admin jika ada perubahan data atau kontak.
                        </p>
                    </div>
                </div>

                <button type="button" id="closeEditAdminModal_header"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <form id="formEditAdmin" class="px-6 pb-5 pt-4 flex flex-col gap-4 bg-slate-50/60 dark:bg-slate-800"
                data-url="{{ route('manajemen_pengguna.update_admin', ['id' => '__ID__']) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <input type="hidden" name="admin_id" id="edit_admin_id">

                <div
                    class="flex items-center gap-2 text-xs rounded-xl px-3 py-2
                           bg-emerald-50 text-emerald-700 border border-emerald-100
                           dark:bg-emerald-900/40 dark:text-emerald-100 dark:border-emerald-800">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Perubahan data admin tidak akan menghapus riwayat aktivitas yang sudah tercatat.</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-[auto,1fr] gap-6 items-start mt-1">

                    {{-- Foto --}}
                    <div class="flex justify-center md:justify-start">
                        <div id="edit_foto_drop_area_admin"
                            class="relative w-32 md:w-36 aspect-[3/4] rounded-xl border-2 border-dashed border-sky-300/80 
                                   flex items-center justify-center cursor-pointer overflow-hidden bg-slate-50 dark:bg-slate-700 transition">

                            <img id="edit_preview_foto_admin" src="" alt="Preview Foto"
                                class="hidden w-full h-full object-cover">

                            <div id="edit_placeholder_foto_admin"
                                class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-[11px] px-2 text-center">
                                <i class="fa-solid fa-user-circle text-xl mb-1"></i>
                                <span>Upload foto baru (opsional)</span>
                            </div>

                            <input type="file" name="foto_admin" id="edit_foto_admin" accept="image/*"
                                class="absolute inset-0 opacity-0 cursor-pointer">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                            Akun Admin
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label for="edit_username"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Username</label>
                                <input type="text" name="username" id="edit_username"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Username admin" required>
                                <div class="field-error text-red-600 text-xs mt-1" data-error-for="username"></div>
                            </div>

                            <div class="space-y-1">
                                <label for="edit_nama_admin"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Nama
                                    Admin</label>
                                <input type="text" name="nama_admin" id="edit_nama_admin"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Nama Admin" required>
                                <div class="field-error text-red-600 text-xs mt-1" data-error-for="nama_admin"></div>
                            </div>

                            <div class="space-y-1">
                                <label for="edit_email"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Email</label>
                                <input type="email" name="email" id="edit_email"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="admin@example.com" required>
                                <div class="field-error text-red-600 text-xs mt-1" data-error-for="email"></div>
                            </div>

                            <div class="space-y-1">
                                <label for="edit_no_hp"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">No HP</label>
                                <input type="text" name="no_hp" id="edit_no_hp"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="0812xxxxxxxx">
                                <div class="field-error text-red-600 text-xs mt-1" data-error-for="no_hp"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="field-error text-red-600 text-xs mt-1 text-center" data-error-for="foto_admin"></div>

                <div class="mt-4 space-y-3 border-t border-slate-200 dark:border-slate-700 pt-4">
                    <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                        Keamanan Akun
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="edit_password"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">Password
                                Baru</label>
                            <input type="password" name="password" id="edit_password"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="Kosongkan jika tidak ingin diubah">
                            <div class="field-error text-red-600 text-xs mt-1" data-error-for="password"></div>
                        </div>

                        <div class="space-y-1">
                            <label for="edit_password_confirmation"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">Konfirmasi
                                Password</label>
                            <input type="password" name="password_confirmation" id="edit_password_confirmation"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="Ulangi password baru">
                            <div class="field-error text-red-600 text-xs mt-1" data-error-for="password_confirmation">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <button type="button" id="closeEditAdminModal_footer"
                        class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-slate-200 rounded-xl 
                               hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit" id="updateAdminButton"
                        class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl 
                               bg-gradient-to-r from-teal-500 to-sky-600 hover:from-teal-600 hover:to-sky-700
                               focus:ring-2 focus:ring-teal-400">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@vite(['resources/js/admin/manajemenPengguna/data_admin.js'])
