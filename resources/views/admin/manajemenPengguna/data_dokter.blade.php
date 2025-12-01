<section class="space-y-5">

    <!-- HEADER ATAS + CTA -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-user-doctor text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Data Dokter
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola akun dan profil dokter yang terhubung dengan jadwal, poli, kunjungan, dan rekam medis.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-3">
            <button type="button"
                class="hidden md:inline-flex items-center gap-2 px-3 py-2 text-xs md:text-sm rounded-xl border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-600">
                <i class="fa-regular fa-circle-question text-sm"></i>
                <span>Panduan Manajemen Dokter</span>
            </button>

            <button id="btnAddDokter" type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-xl shadow-md
                       bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                       focus:outline-none focus:ring-2 focus:ring-sky-400">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Data Dokter</span>
            </button>
        </div>
    </div>

    <!-- CARD TABEL -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm">

        <!-- Toolbar -->
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">

            <!-- Page length -->
            <div class="flex items-center gap-2 text-sm">
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>
                <select id="dokter_pageLength"
                    class="border border-slate-300 dark:border-slate-600 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500
                           bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-2 py-1 w-28">
                    <option value="10">10 baris</option>
                    <option value="25">25 baris</option>
                    <option value="50">50 baris</option>
                    <option value="100">100 baris</option>
                </select>
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">per halaman</span>
            </div>

            <!-- Search -->
            <div class="w-full md:w-auto">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                    </span>
                    <input type="text" id="dokter_searchInput"
                        class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm text-slate-800 dark:text-slate-100
                               border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700
                               focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari nama dokter, spesialis, poli, atau email...">
                </div>
                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Contoh: <span class="italic">dr. Andi, Sp.PD, Poli Umum, drg., email, atau no HP</span>.
                </p>
            </div>
        </div>

        <!-- Tabel -->
        <div class="overflow-x-auto">
            <table id="dokterTable"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100 border-t border-slate-100 dark:border-slate-700">
                <thead
                    class="text-xs font-semibold uppercase bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600 text-white tracking-wide">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Profil</th>
                        <th class="px-6 py-3">Nama Dokter</th>
                        <th class="px-6 py-3">Username</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Role</th>
                        <th class="px-6 py-3">Spesialis</th>
                        <th class="px-6 py-3">Poli</th>
                        <th class="px-6 py-3">No HP</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
            </table>
        </div>

        <!-- Footer -->
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">
            <div id="dokter_customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>

            <ul id="dokter_customPagination"
                class="inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 dark:border-slate-600 overflow-hidden">
            </ul>
        </div>
    </div>

</section>


{{-- Modal Add Dokter (CREATE) --}}
<div id="addDokterModal" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center w-full h-full p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="relative w-full max-w-5xl">
        <div
            class="relative bg-white rounded-2xl shadow-2xl dark:bg-slate-800 border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh] overflow-y-auto">

            {{-- HEADER --}}
            <div
                class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-sky-500 to-teal-500 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-sky-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-user-plus text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-slate-50">
                            Tambah Data Dokter
                        </h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">
                            Lengkapi data dokter yang akan digunakan untuk login, jadwal, dan layanan klinik.
                        </p>
                    </div>
                </div>

                <button type="button" id="closeAddDokterModal"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- FORM --}}
            <form id="formAddDokter" class="px-6 pb-5 pt-4 flex flex-col gap-4 bg-slate-50/60 dark:bg-slate-800"
                data-url="{{ route('manajemen_pengguna.add_dokter') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- INFO STRIP --}}
                <div
                    class="flex items-center gap-2 text-xs rounded-xl px-3 py-2
                           bg-emerald-50 text-emerald-700 border border-emerald-100
                           dark:bg-emerald-900/40 dark:text-emerald-100 dark:border-emerald-800">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Foto, username, email, dan spesialis akan muncul di modul jadwal & rekam medis.</span>
                </div>

                {{-- BLOK ATAS: FOTO + AKUN & LOGIN --}}
                <div class="grid grid-cols-1 md:grid-cols-[auto,1fr] gap-6 items-start mt-1">
                    {{-- FOTO --}}
                    <div class="flex flex-col justify-center md:justify-start">
                        <div id="foto_drop_area"
                            class="relative w-32 md:w-36 aspect-[3/4] rounded-xl border-2 border-dashed border-sky-300/80 
                                   flex items-center justify-center cursor-pointer overflow-hidden
                                   bg-slate-50 dark:bg-slate-700 transition">
                            <img id="preview_foto_dokter" src="" alt="Preview Foto"
                                class="hidden w-full h-full object-cover">
                            <div id="placeholder_foto_dokter"
                                class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-[11px] px-2 text-center">
                                <i class="fa-solid fa-user-circle text-xl mb-1"></i>
                                <span>Upload foto profil dokter</span>
                            </div>
                            <input type="file" name="foto_dokter" id="foto_dokter" accept="image/*"
                                class="absolute inset-0 opacity-0 cursor-pointer">
                        </div>
                        <div id="foto_dokter-error" class="text-red-600 text-xs mt-1"></div>
                    </div>
                    <div class="space-y-4">
                        <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                            Akun & Login
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Username --}}
                            <div class="space-y-1">
                                <label for="username_dokter"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Username</label>
                                <input type="text" name="username_dokter" id="username_dokter"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Username login" required>
                                <div id="username_dokter-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Email Akun --}}
                            <div class="space-y-1">
                                <label for="email_akun_dokter"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Email
                                    Akun</label>
                                <input type="email" name="email_akun_dokter" id="email_akun_dokter"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="dokter@example.com" required>
                                <div id="email_akun_dokter-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Password --}}
                            <div class="space-y-1">
                                <label for="password_dokter"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Password</label>
                                <input type="password" name="password_dokter" id="password_dokter"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="••••••••" required>
                                <div id="password_dokter-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Konfirmasi Password --}}
                            <div class="space-y-1">
                                <label for="password_dokter_confirmation"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Konfirmasi Password
                                </label>
                                <input type="password" name="password_dokter_confirmation"
                                    id="password_dokter_confirmation"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="••••••••" required
                                    oninput="this.setCustomValidity(this.value !== password_dokter.value ? 'Password tidak sama!' : '')">
                                <div id="password_dokter_confirmation-error" class="text-red-600 text-xs mt-1"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BLOK PROFIL DOKTER --}}
                <div class="mt-4 space-y-3">
                    <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                        Profil Dokter
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Nama Dokter --}}
                        <div class="space-y-1">
                            <label for="nama_dokter"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">Nama
                                Dokter</label>
                            <input type="text" name="nama_dokter" id="nama_dokter"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="Contoh: dr. Andi, Sp.PD" required>
                            <div id="nama_dokter-error" class="text-red-600 text-xs mt-1"></div>
                        </div>

                        {{-- Spesialis --}}
                        <div class="space-y-1">
                            <label for="spesialis_dokter"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">Spesialis</label>
                            <select id="spesialis_dokter" name="spesialis_dokter" required
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50">
                                <option value="" disabled selected>Pilih Spesialis</option>
                                @foreach ($spesialis as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama_spesialis }}</option>
                                @endforeach
                            </select>
                            <div id="spesialis_dokter-error" class="text-red-600 text-xs mt-1"></div>
                        </div>

                        {{-- Poli (multi) --}}
                        <div class="space-y-1">
                            <label for="poli_id"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                Poli <span class="text-slate-400 text-xs">(bisa pilih lebih dari satu)</span>
                            </label>
                            <select id="poli_id" name="poli_id[]" multiple required
                                placeholder="Cari & pilih poli…"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50">
                                @foreach ($dataPoli as $poli)
                                    <option value="{{ $poli->id }}">{{ $poli->nama_poli }}</option>
                                @endforeach
                            </select>
                            <div id="poli_id-error" class="text-red-600 text-xs mt-1"></div>
                        </div>

                        {{-- Pengalaman --}}
                        <div class="space-y-1">
                            <label for="pengalaman_dokter"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">Pengalaman</label>
                            <input type="text" name="pengalaman_dokter" id="pengalaman_dokter"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="Contoh: 5 tahun di bidang penyakit dalam">
                            <div id="pengalaman_dokter-error" class="text-red-600 text-xs mt-1"></div>
                        </div>
                    </div>
                </div>

                {{-- BLOK KONTAK & DESKRIPSI --}}
                <div class="mt-4 space-y-3">
                    <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                        Kontak & Deskripsi
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- No HP --}}
                        <div class="space-y-1">
                            <label for="no_hp_dokter"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">No HP</label>
                            <input type="text" name="no_hp_dokter" id="no_hp_dokter"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="0812xxxxxxxx" required>
                            <div id="no_hp_dokter-error" class="text-red-600 text-xs mt-1"></div>
                        </div>

                        {{-- Deskripsi (full width) --}}
                        <div class="space-y-1 md:col-span-2">
                            <label for="deskripsi_dokter"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">Deskripsi</label>
                            <textarea name="deskripsi_dokter" id="deskripsi_dokter" rows="2"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="Deskripsi singkat dokter, minat khusus, atau pendekatan pelayanan..."></textarea>
                            <div id="deskripsi_dokter-error" class="text-red-600 text-xs mt-1"></div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="flex justify-end gap-3 mt-6 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <button type="button" id="closeAddDokterModal_footer"
                        class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-slate-200 rounded-xl 
                               hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit" id="saveDokterButton"
                        class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl 
                               bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                               focus:ring-2 focus:ring-sky-400">
                        Simpan Data Dokter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Dokter (UPDATE) --}}
<div id="editDokterModal" aria-hidden="true"
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
                            Edit Data Dokter
                        </h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">
                            Perbarui informasi dokter sesuai kebutuhan operasional klinik.
                        </p>
                    </div>
                </div>

                <button type="button" id="closeEditDokterModal"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Form --}}
            <form id="formEditDokter" class="px-6 pb-5 pt-4 flex flex-col gap-4 bg-slate-50/60 dark:bg-slate-800"
                data-url="{{ route('manajemen_pengguna.update_dokter') }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="edit_dokter_id" id="edit_dokter_id">

                {{-- info strip --}}
                <div
                    class="flex items-center gap-2 text-xs rounded-xl px-3 py-2
                           bg-emerald-50 text-emerald-700 border border-emerald-100
                           dark:bg-emerald-900/40 dark:text-emerald-100 dark:border-emerald-800">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Perubahan email, spesialis, dan poli akan memengaruhi jadwal dan tampilan di modul
                        lain.</span>
                </div>

                {{-- BLOK ATAS: FOTO + AKUN & LOGIN --}}
                <div class="grid grid-cols-1 md:grid-cols-[auto,1fr] gap-6 items-start mt-1">
                    {{-- Foto --}}
                    <div class="flex justify-center md:justify-start">
                        <div id="foto_drop_area_edit"
                            class="relative w-32 md:w-36 aspect-[3/4] rounded-xl border-2 border-dashed border-sky-300/80 
                                   flex items-center justify-center cursor-pointer overflow-hidden bg-slate-50 dark:bg-slate-700 transition">
                            <img id="preview_edit_foto_dokter" src="" alt="Preview Foto"
                                class="hidden w-full h-full object-cover">
                            <div id="placeholder_edit_foto_dokter"
                                class="absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-[11px] px-2 text-center">
                                <i class="fa-solid fa-user-circle text-xl mb-1"></i>
                                <span>Upload foto baru (opsional)</span>
                            </div>
                            <input type="file" name="edit_foto_dokter" id="edit_foto_dokter" accept="image/*"
                                class="absolute inset-0 opacity-0 cursor-pointer">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                            Akun & Login
                        </h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Username --}}
                            <div class="space-y-1">
                                <label for="edit_username_dokter"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Username</label>
                                <input type="text" name="edit_username_dokter" id="edit_username_dokter"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Username" required>
                                <div id="edit_username_dokter-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Email Akun --}}
                            <div class="space-y-1">
                                <label for="edit_email_akun_dokter"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">Email
                                    Akun</label>
                                <input type="email" name="edit_email_akun_dokter" id="edit_email_akun_dokter"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="dokter@example.com" required>
                                <div id="edit_email_akun_dokter-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Password (opsional) --}}
                            <div class="space-y-1">
                                <label for="edit_password_dokter"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Password (opsional)
                                </label>
                                <input type="password" name="edit_password_dokter" id="edit_password_dokter"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Isi jika ingin mengubah">
                                <div id="edit_password_dokter-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Konfirmasi Password --}}
                            <div class="space-y-1">
                                <label for="edit_password_dokter_confirmation"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Konfirmasi Password
                                </label>
                                <input type="password" name="edit_password_dokter_confirmation"
                                    id="edit_password_dokter_confirmation"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Ulangi password baru"
                                    oninput="this.setCustomValidity(this.value !== edit_password_dokter.value ? 'Password tidak sama!' : '')">
                                <div id="edit_password_dokter_confirmation-error" class="text-red-600 text-xs mt-1">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- BLOK PROFIL DOKTER --}}
                <div class="mt-4 space-y-3">
                    <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                        Profil Dokter
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Nama Dokter --}}
                        <div class="space-y-1">
                            <label for="edit_nama_dokter"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">Nama
                                Dokter</label>
                            <input type="text" name="edit_nama_dokter" id="edit_nama_dokter"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="Nama Dokter" required>
                            <div id="edit_nama_dokter-error" class="text-red-600 text-xs mt-1"></div>
                        </div>

                        {{-- Spesialis --}}
                        <div class="space-y-1">
                            <label for="edit_spesialis_dokter"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">Spesialis</label>
                            <select id="edit_spesialis_dokter" name="edit_spesialis_dokter" required
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50">
                                <option value="" disabled selected>Pilih Spesialis</option>
                                @foreach ($spesialis as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama_spesialis }}</option>
                                @endforeach
                            </select>
                            <div id="edit_spesialis_dokter-error" class="text-red-600 text-xs mt-1"></div>
                        </div>

                        {{-- Poli (multi) --}}
                        <div class="space-y-1">
                            <label for="edit_poli_id"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                Poli <span class="text-slate-400 text-xs">(bisa pilih lebih dari satu)</span>
                            </label>
                            <select id="edit_poli_id" name="poli_id[]" multiple required
                                placeholder="Cari & pilih poli…"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50">
                                @foreach ($dataPoli as $poli)
                                    <option value="{{ $poli->id }}">{{ $poli->nama_poli }}</option>
                                @endforeach
                            </select>
                            <div id="edit_poli_id-error" class="text-red-600 text-xs mt-1"></div>
                        </div>

                        {{-- Pengalaman --}}
                        <div class="space-y-1">
                            <label for="edit_pengalaman_dokter"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">Pengalaman</label>
                            <input type="text" name="edit_pengalaman_dokter" id="edit_pengalaman_dokter"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="Contoh: 5 tahun di bidang ...">
                            <div id="edit_pengalaman_dokter-error" class="text-red-600 text-xs mt-1"></div>
                        </div>
                    </div>
                </div>

                {{-- BLOK KONTAK & DESKRIPSI --}}
                <div class="mt-4 space-y-3">
                    <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                        Kontak & Deskripsi
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- No HP --}}
                        <div class="space-y-1">
                            <label for="edit_no_hp_dokter"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">No HP</label>
                            <input type="text" name="edit_no_hp_dokter" id="edit_no_hp_dokter"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="0812xxxxxxxx" required>
                            <div id="edit_no_hp_dokter-error" class="text-red-600 text-xs mt-1"></div>
                        </div>

                        {{-- Deskripsi (full width) --}}
                        <div class="space-y-1 md:col-span-2">
                            <label for="edit_deskripsi_dokter"
                                class="block text-sm font-medium text-slate-800 dark:text-slate-100">Deskripsi</label>
                            <textarea name="edit_deskripsi_dokter" id="edit_deskripsi_dokter" rows="2"
                                class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                       focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                placeholder="Deskripsi singkat dokter..."></textarea>
                            <div id="edit_deskripsi_dokter-error" class="text-red-600 text-xs mt-1"></div>
                        </div>
                    </div>
                </div>

                {{-- Footer Buttons --}}
                <div class="flex justify-end gap-3 mt-6 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <button type="button" id="closeEditDokterModal_footer"
                        class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-slate-200 rounded-xl 
                               hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit" id="updateDokterButton"
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

@vite(['resources/js/admin/manajemenPengguna/data_dokter.js'])
