<section class="space-y-5">

    {{-- HEADER + CTA --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-user-nurse text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Data Perawat
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola akun dan penugasan perawat yang terhubung dengan dokter, poli, dan kunjungan pasien.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-3">
            <button type="button"
                class="hidden md:inline-flex items-center gap-2 px-3 py-2 text-xs md:text-sm rounded-xl border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-600">
                <i class="fa-regular fa-circle-question text-sm"></i>
                <span>Panduan Manajemen Perawat</span>
            </button>

            <button id="btnAddPerawat" type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-xl shadow-md
                       bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                       focus:outline-none focus:ring-2 focus:ring-sky-400">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Data Perawat</span>
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
                <select id="perawat_pageLength"
                    class="border border-slate-300 dark:border-slate-600 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500
                           bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-1 w-28">
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
                    <input type="text" id="perawat_searchInput"
                        class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm text-slate-800 dark:text-slate-100
                               border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700
                               focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari nama perawat, dokter, poli, atau email...">
                </div>
                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Contoh: <span class="italic">Poli Umum, dr. Andi, Perawat B, 0812...</span>.
                </p>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table id="userPerawat"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100 border-t border-slate-100 dark:border-slate-700">
                <thead
                    class="text-xs font-semibold uppercase bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600 text-white tracking-wide">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Profil</th>
                        <th class="px-6 py-3">Poli</th>
                        <th class="px-6 py-3">Nama Dokter</th>
                        <th class="px-6 py-3">Nama Perawat</th>
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
            <div id="perawat_customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>
            <ul id="perawat_customPagination"
                class="inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
            </ul>
        </div>
    </div>
</section>

{{-- Modal Add Perawat --}}
<div id="addPerawatModal" aria-hidden="true"
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
                            Tambah Data Perawat
                        </h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">
                            Lengkapi akun perawat beserta relasi dokter & poli tempat bertugas.
                        </p>
                    </div>
                </div>

                {{-- header close (JS boleh diabaikan) --}}
                <button type="button" id="closeAddPerawatModal_header"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Form --}}
            <form id="formAddPerawat" class="px-6 pb-5 pt-4 flex flex-col gap-4 bg-slate-50/60 dark:bg-slate-800"
                data-url="{{ route('manajemen_pengguna.add_perawat') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Info strip --}}
                <div
                    class="flex items-center gap-2 text-xs rounded-xl px-3 py-2
                           bg-emerald-50 text-emerald-700 border border-emerald-100
                           dark:bg-emerald-900/40 dark:text-emerald-100 dark:border-emerald-800">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Pastikan perawat terhubung ke dokter & poli yang benar, karena akan muncul di jadwal dan
                        EMR.</span>
                </div>

                {{-- BLOK ATAS: FOTO + AKUN --}}
                <div class="grid grid-cols-1 md:grid-cols-[auto,1fr] gap-6 items-start mt-1">

                    {{-- Foto --}}
                    <div class="flex justify-center md:justify-start">
                        <div id="foto_drop_area_perawat"
                            class="relative w-32 md:w-36 aspect-[3/4] rounded-xl border-2 border-dashed border-sky-300/80 
                                   flex items-center justify-center cursor-pointer overflow-hidden bg-slate-50 dark:bg-slate-700 transition">
                            <img id="preview_foto_perawat" src="" alt="Preview Foto"
                                class="hidden w-full h-full object-cover">
                            <div id="placeholder_foto_perawat"
                                class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-[11px] px-2 text-center">
                                <i class="fa-solid fa-user-circle text-xl mb-1"></i>
                                <span>Upload foto profil</span>
                            </div>
                            <input type="file" name="foto_perawat" id="foto_perawat" accept="image/*"
                                class="absolute inset-0 opacity-0 cursor-pointer" required>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                            Akun & Profil Perawat
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Username --}}
                            <div class="space-y-1">
                                <label for="username_perawat"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Username</label>
                                <input type="text" name="username_perawat" id="username_perawat"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Username" required>
                                <div id="username_perawat-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Nama Perawat --}}
                            <div class="space-y-1">
                                <label for="nama_perawat"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Nama
                                    Perawat</label>
                                <input type="text" name="nama_perawat" id="nama_perawat"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Nama Perawat" required>
                                <div id="nama_perawat-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Email --}}
                            <div class="space-y-1">
                                <label for="email_perawat"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Email</label>
                                <input type="email" name="email_perawat" id="email_perawat"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="perawat@example.com" required>
                                <div id="email_perawat-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- No HP --}}
                            <div class="space-y-1">
                                <label for="no_hp_perawat"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">No HP</label>
                                <input type="text" name="no_hp_perawat" id="no_hp_perawat"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="0812xxxxxxxx" required>
                                <div id="no_hp_perawat-error" class="text-red-600 text-xs mt-1"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="foto_perawat-error" class="text-red-600 text-xs mt-1 text-center"></div>

                {{-- Dokter & Poli (TomSelect) --}}
                <div class="mt-4 space-y-3">
                    <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                        Penugasan Perawat
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {{-- Poli --}}
                        <div class="space-y-1">
                            <label for="add_poli_select"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                Poli
                            </label>
                            <select id="add_poli_select"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="Cari & pilih poli…">
                                {{-- TomSelect inject --}}
                            </select>
                            <div id="poli_id-error" class="text-red-600 text-xs mt-1"></div>
                        </div>

                        {{-- Dokter (hasil filter poli, value = dokter_poli_id) --}}
                        <div class="space-y-1">
                            <label for="add_dokter_select"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                Dokter
                            </label>
                            <select id="add_dokter_select" name="dokter_poli_id"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="Cari & pilih dokter…">
                                {{-- TomSelect inject --}}
                            </select>
                            <div id="dokter_poli_id-error" class="text-red-600 text-xs mt-1"></div>
                        </div>

                    </div>
                </div>


                {{-- Password --}}
                <div class="mt-4 space-y-3 border-t border-slate-200 dark:border-slate-700 pt-4">
                    <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                        Keamanan Akun
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="password_perawat"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">Password</label>
                            <input type="password" name="password_perawat" id="password_perawat"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="••••••••" required>
                            <div id="password_perawat-error" class="text-red-600 text-xs mt-1"></div>
                        </div>

                        <div class="space-y-1">
                            <label for="password_perawat_confirmation"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">Konfirmasi
                                Password</label>
                            <input type="password" name="password_perawat_confirmation"
                                id="password_perawat_confirmation"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="••••••••" required
                                oninput="this.setCustomValidity(this.value !== password_perawat.value ? 'Password tidak sama!' : '')">
                            <div id="password_perawat_confirmation-error" class="text-red-600 text-xs mt-1"></div>
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 mt-6 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <button type="button" id="closeAddPerawatModal"
                        class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-slate-200 rounded-xl 
                               hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl 
                               bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                               focus:ring-2 focus:ring-sky-400">
                        Simpan Data Perawat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Perawat --}}
<div id="editPerawatModal" aria-hidden="true"
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
                            Edit Data Perawat
                        </h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">
                            Perbarui informasi perawat serta penempatan dokter & poli.
                        </p>
                    </div>
                </div>

                <button type="button" id="closeEditPerawatModal_header"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Form --}}
            <form id="formEditPerawat" class="px-6 pb-5 pt-4 flex flex-col gap-4 bg-slate-50/60 dark:bg-slate-800"
                data-url="{{ route('manajemen_pengguna.update_perawat', ['id' => 0]) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="perawat_id" id="edit_perawat_id">

                {{-- Info strip --}}
                <div
                    class="flex items-center gap-2 text-xs rounded-xl px-3 py-2
                           bg-emerald-50 text-emerald-700 border border-emerald-100
                           dark:bg-emerald-900/40 dark:text-emerald-100 dark:border-emerald-800">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Perubahan dokter & poli akan mengubah jadwal tugas perawat di modul lain.</span>
                </div>

                {{-- BLOK ATAS: FOTO + AKUN --}}
                <div class="grid grid-cols-1 md:grid-cols-[auto,1fr] gap-6 items-start mt-1">

                    {{-- Foto --}}
                    <div class="flex justify-center md:justify-start">
                        <div id="edit_foto_drop_area_perawat"
                            class="relative w-32 md:w-36 aspect-[3/4] rounded-xl border-2 border-dashed border-sky-300/80 
                                   flex items-center justify-center cursor-pointer overflow-hidden bg-slate-50 dark:bg-slate-700 transition">
                            <img id="edit_preview_foto_perawat" src="" alt="Preview Foto"
                                class="hidden w-full h-full object-cover">
                            <div id="edit_placeholder_foto_perawat"
                                class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-[11px] px-2 text-center">
                                <i class="fa-solid fa-user-circle text-xl mb-1"></i>
                                <span>Upload foto baru (opsional)</span>
                            </div>
                            <input type="file" name="edit_foto_perawat" id="edit_foto_perawat" accept="image/*"
                                class="absolute inset-0 opacity-0 cursor-pointer">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                            Akun & Profil Perawat
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Username --}}
                            <div class="space-y-1">
                                <label for="edit_username_perawat"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Username</label>
                                <input type="text" name="edit_username_perawat" id="edit_username_perawat"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Username" required>
                                <div id="edit_username_perawat-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Nama Perawat --}}
                            <div class="space-y-1">
                                <label for="edit_nama_perawat"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Nama
                                    Perawat</label>
                                <input type="text" name="edit_nama_perawat" id="edit_nama_perawat"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Nama Perawat" required>
                                <div id="edit_nama_perawat-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Email --}}
                            <div class="space-y-1">
                                <label for="edit_email_perawat"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Email</label>
                                <input type="email" name="edit_email_perawat" id="edit_email_perawat"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="perawat@example.com" required>
                                <div id="edit_email_perawat-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- No HP --}}
                            <div class="space-y-1">
                                <label for="edit_no_hp_perawat"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">No HP</label>
                                <input type="text" name="edit_no_hp_perawat" id="edit_no_hp_perawat"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="0812xxxxxxxx" required>
                                <div id="edit_no_hp_perawat-error" class="text-red-600 text-xs mt-1"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="edit_foto_perawat-error" class="text-red-600 text-xs mt-1 text-center"></div>

                {{-- Dokter & Poli --}}
                <div class="mt-4 space-y-3">
                    <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                        Penugasan Perawat
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Dokter --}}
                        <div class="space-y-1">
                            <label for="edit_dokter_select"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                Dokter
                            </label>
                            <select id="edit_dokter_select" name="edit_dokter_id"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="Cari & pilih dokter…">
                                {{-- TomSelect async --}}
                            </select>
                            <div id="edit_dokter_id-error" class="text-red-600 text-xs mt-1"></div>
                        </div>

                        {{-- Poli --}}
                        <div id="group_poli_edit" class="space-y-1 hidden">
                            <label for="edit_poli_select"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                Poli
                            </label>
                            <select id="edit_poli_select" name="edit_poli_id"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="Cari & pilih poli…">
                                {{-- TomSelect async --}}
                            </select>
                            <div id="edit_poli_id-error" class="text-red-600 text-xs mt-1"></div>
                        </div>
                    </div>
                </div>

                {{-- Password (Opsional) --}}
                <div class="mt-4 space-y-3 border-t border-slate-200 dark:border-slate-700 pt-4">
                    <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                        Keamanan Akun
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label for="edit_password_perawat"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">Password
                                Baru</label>
                            <input type="password" name="edit_password_perawat" id="edit_password_perawat"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="••••••••">
                            <div id="edit_password_perawat-error" class="text-red-600 text-xs mt-1"></div>
                        </div>

                        <div class="space-y-1">
                            <label for="edit_password_perawat_confirmation"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">Konfirmasi
                                Password</label>
                            <input type="password" name="edit_password_perawat_confirmation"
                                id="edit_password_perawat_confirmation"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="••••••••">
                            <div id="edit_password_perawat_confirmation-error" class="text-red-600 text-xs mt-1">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 mt-6 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <button type="button" id="closeEditPerawatModal"
                        class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-slate-200 rounded-xl 
                               hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit"
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

@vite(['resources/js/admin/manajemenPengguna/data_perawat.js'])
