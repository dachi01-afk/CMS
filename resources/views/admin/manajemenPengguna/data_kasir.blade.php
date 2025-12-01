<section class="space-y-5">

    {{-- HEADER + CTA --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-cash-register text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Data Kasir
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola akun kasir yang bertanggung jawab atas transaksi pembayaran layanan dan obat di klinik.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-3">
            <button type="button"
                class="hidden md:inline-flex items-center gap-2 px-3 py-2 text-xs md:text-sm rounded-xl border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-600">
                <i class="fa-regular fa-circle-question text-sm"></i>
                <span>Panduan Manajemen Kasir</span>
            </button>

            <button id="btnAddKasir" type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-xl shadow-md
                       bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                       focus:outline-none focus:ring-2 focus:ring-sky-400">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Data Kasir</span>
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
                <select id="kasir_pageLength"
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
                    <input type="text" id="kasir_searchInput"
                        class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm text-slate-800 dark:text-slate-100
                               border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700
                               focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari nama kasir, email, atau no HP...">
                </div>
                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Contoh: <span class="italic">Kasir 1, kasir@example.com, 0812...</span>.
                </p>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table id="userKasir"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100 border-t border-slate-100 dark:border-slate-700">
                <thead
                    class="text-xs font-semibold uppercase bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600 text-white tracking-wide">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Profil</th>
                        <th class="px-6 py-3">Nama Kasir</th>
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
            <div id="kasir_customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>
            <ul id="kasir_customPagination"
                class="inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
            </ul>
        </div>
    </div>
</section>

{{-- Modal Add Kasir (CREATE) --}}
<div id="addKasirModal" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center w-full h-full p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="relative w-full max-w-3xl">
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
                            Tambah Data Kasir
                        </h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">
                            Lengkapi data akun kasir untuk mengelola transaksi pembayaran di klinik.
                        </p>
                    </div>
                </div>

                <button type="button" id="closeAddKasirModal_header"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Form --}}
            <form id="formAddKasir" class="px-6 pb-5 pt-4 flex flex-col gap-4 bg-slate-50/60 dark:bg-slate-800"
                data-url="{{ route('manajemen_pengguna.add_kasir') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Info strip --}}
                <div
                    class="flex items-center gap-2 text-xs rounded-xl px-3 py-2
                           bg-emerald-50 text-emerald-700 border border-emerald-100
                           dark:bg-emerald-900/40 dark:text-emerald-100 dark:border-emerald-800">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Akun kasir digunakan untuk mengelola pembayaran layanan &amp; obat di modul kasir.</span>
                </div>

                {{-- BLOK ATAS: FOTO + AKUN --}}
                <div class="grid grid-cols-1 md:grid-cols-[auto,1fr] gap-6 items-start mt-1">

                    {{-- Foto --}}
                    <div class="flex justify-center md:justify-start">
                        <div id="foto_drop_area_kasir"
                            class="relative w-32 md:w-36 aspect-[3/4] rounded-xl border-2 border-dashed border-sky-300/80 
                                   flex items-center justify-center cursor-pointer overflow-hidden bg-slate-50 dark:bg-slate-700 transition">

                            {{-- Preview --}}
                            <img id="preview_foto_kasir" src="" alt="Preview Foto"
                                class="hidden w-full h-full object-cover">

                            {{-- Placeholder --}}
                            <div id="placeholder_foto_kasir"
                                class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-[11px] px-2 text-center">
                                <i class="fa-solid fa-user-circle text-xl mb-1"></i>
                                <span>Upload foto profil kasir</span>
                            </div>

                            {{-- Input File --}}
                            <input type="file" name="foto_kasir" id="foto_kasir" accept="image/*"
                                class="absolute inset-0 opacity-0 cursor-pointer" required>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                            Akun Kasir
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            {{-- Username --}}
                            <div class="space-y-1">
                                <label for="username_kasir"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Username</label>
                                <input type="text" name="username_kasir" id="username_kasir"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Username" required>
                                <div id="username_kasir-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Nama Kasir --}}
                            <div class="space-y-1">
                                <label for="nama_kasir"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Nama
                                    Kasir</label>
                                <input type="text" name="nama_kasir" id="nama_kasir"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Nama Kasir" required>
                                <div id="nama_kasir-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Email --}}
                            <div class="space-y-1">
                                <label for="email_kasir"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Email</label>
                                <input type="email" name="email_kasir" id="email_kasir"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="kasir@example.com" required>
                                <div id="email_kasir-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- No HP --}}
                            <div class="space-y-1">
                                <label for="no_hp_kasir"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">No HP</label>
                                <input type="text" name="no_hp_kasir" id="no_hp_kasir"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="0812xxxxxxxx" required>
                                <div id="no_hp_kasir-error" class="text-red-600 text-xs mt-1"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="foto_kasir-error" class="text-red-600 text-xs mt-1 text-center"></div>

                {{-- Password --}}
                <div class="mt-4 space-y-3 border-t border-slate-200 dark:border-slate-700 pt-4">
                    <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                        Keamanan Akun
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Password Baru --}}
                        <div class="space-y-1">
                            <label for="password_kasir"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">Password</label>
                            <input type="password" name="password_kasir" id="password_kasir"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="••••••••" required>
                            <div id="password_kasir-error" class="text-red-600 text-xs mt-1"></div>
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div class="space-y-1">
                            <label for="password_kasir_confirmation"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">Konfirmasi
                                Password</label>
                            <input type="password" name="password_kasir_confirmation"
                                id="password_kasir_confirmation"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="••••••••" required
                                oninput="this.setCustomValidity(this.value !== password_kasir.value ? 'Password tidak sama!' : '')">
                            <div id="password_kasir_confirmation-error" class="text-red-600 text-xs mt-1"></div>
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 mt-6 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <button type="button" id="closeAddKasirModal"
                        class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-slate-200 rounded-xl 
                               hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit" id="saveApotekerButton"
                        class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl 
                               bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                               focus:ring-2 focus:ring-sky-400">
                        Simpan Data Kasir
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Kasir (UPDATE) --}}
<div id="editKasirModal" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center w-full h-full p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="relative w-full max-w-3xl">
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
                            Edit Data Kasir
                        </h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">
                            Perbarui informasi akun kasir jika ada pergantian petugas atau perubahan kontak.
                        </p>
                    </div>
                </div>

                <button type="button" id="closeEditKasirModal_header"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Form --}}
            <form id="formEditKasir"
                class="px-6 pb-5 pt-4 flex flex-col gap-4 bg-slate-50/60 dark:bg-slate-800"
                data-url="{{ route('manajemen_pengguna.update_kasir', ['id' => 0]) }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="kasir_id" id="edit_kasir_id">

                {{-- Info strip --}}
                <div
                    class="flex items-center gap-2 text-xs rounded-xl px-3 py-2
                           bg-emerald-50 text-emerald-700 border border-emerald-100
                           dark:bg-emerald-900/40 dark:text-emerald-100 dark:border-emerald-800">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Perubahan data kasir akan tetap mempertahankan riwayat transaksi yang sudah tercatat.</span>
                </div>

                {{-- BLOK ATAS: FOTO + AKUN (SAMA STRUKTUR DENGAN CREATE) --}}
                <div class="grid grid-cols-1 md:grid-cols-[auto,1fr] gap-6 items-start mt-1">

                    {{-- Foto --}}
                    <div class="flex justify-center md:justify-start">
                        <div id="edit_foto_drop_area_kasir"
                            class="relative w-32 md:w-36 aspect-[3/4] rounded-xl border-2 border-dashed border-sky-300/80 
                                   flex items-center justify-center cursor-pointer overflow-hidden bg-slate-50 dark:bg-slate-700 transition">

                            {{-- Preview --}}
                            <img id="edit_preview_foto_kasir" src="" alt="Preview Foto"
                                class="hidden w-full h-full object-cover">

                            {{-- Placeholder --}}
                            <div id="edit_placeholder_foto_kasir"
                                class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-[11px] px-2 text-center">
                                <i class="fa-solid fa-user-circle text-xl mb-1"></i>
                                <span>Upload foto baru (opsional)</span>
                            </div>

                            {{-- Input File --}}
                            <input type="file" name="edit_foto_kasir" id="edit_foto_kasir" accept="image/*"
                                class="absolute inset-0 opacity-0 cursor-pointer">
                        </div>
                    </div>

                    {{-- Form akun --}}
                    <div class="space-y-4">
                        <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                            Akun Kasir
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            {{-- Username --}}
                            <div class="space-y-1">
                                <label for="edit_username_kasir"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Username
                                </label>
                                <input type="text" name="edit_username_kasir" id="edit_username_kasir"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Username" required>
                                <div id="edit_username-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Nama Kasir --}}
                            <div class="space-y-1">
                                <label for="edit_nama_kasir"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Nama Kasir
                                </label>
                                <input type="text" name="edit_nama_kasir" id="edit_nama_kasir"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Nama Kasir" required>
                                <div id="edit_nama_kasir-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Email --}}
                            <div class="space-y-1">
                                <label for="edit_email_kasir"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Email
                                </label>
                                <input type="email" name="edit_email_kasir" id="edit_email_kasir"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="kasir@example.com" required>
                                <div id="edit_email_kasir-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- No HP --}}
                            <div class="space-y-1">
                                <label for="edit_no_hp_kasir"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    No HP
                                </label>
                                <input type="text" name="edit_no_hp_kasir" id="edit_no_hp_kasir"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="0812xxxxxxxx" required>
                                <div id="edit_no_hp_kasir-error" class="text-red-600 text-xs mt-1"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- error foto DIPINDAH ke luar grid, sama seperti modal create --}}
                <div id="edit_foto_kasir-error" class="text-red-600 text-xs mt-1 text-center"></div>

                {{-- Password (Opsional) --}}
                <div class="mt-4 space-y-3 border-t border-slate-200 dark:border-slate-700 pt-4">
                    <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                        Keamanan Akun
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- Password baru --}}
                        <div class="space-y-1">
                            <label for="edit_password_kasir"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                Password Baru
                            </label>
                            <input type="password" name="edit_password_kasir" id="edit_password_kasir"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="(opsional) isi jika ingin mengubah">
                            <div id="edit_password_kasir-error" class="text-red-600 text-xs mt-1"></div>
                        </div>

                        {{-- Konfirmasi Password --}}
                        <div class="space-y-1">
                            <label for="edit_password_kasir_confirmation"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                Konfirmasi Password
                            </label>
                            <input type="password" name="edit_password_kasir_confirmation"
                                id="edit_password_kasir_confirmation"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="Ulangi password baru (opsional)">
                            <div id="edit_password_kasir_confirmation-error" class="text-red-600 text-xs mt-1"></div>
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 mt-6 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <button type="button" id="closeEditKasirrModal"
                        class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-slate-200 rounded-xl 
                               hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit" id="updateApotekerButton"
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

@vite(['resources/js/admin/manajemenPengguna/data_kasir.js'])
