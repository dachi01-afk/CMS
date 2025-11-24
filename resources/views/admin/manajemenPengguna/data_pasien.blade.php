<section class="space-y-5">

    {{-- HEADER ATAS + CTA --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 
               bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 
               rounded-2xl px-4 md:px-6 py-4 shadow-sm">

        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl
                       bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-hospital-user"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Data Pasien
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kelola akun, identitas, kontak, dan informasi medis dasar seluruh pasien klinik.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-3">
            <button type="button"
                class="hidden md:inline-flex items-center gap-2 px-3 py-2 text-xs md:text-sm rounded-xl 
                       border border-slate-200 text-slate-600 bg-white hover:bg-slate-50
                       dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-600">
                <i class="fa-regular fa-circle-question text-sm"></i>
                <span>Panduan Manajemen Pasien</span>
            </button>

            <button id="btnAddPasien" type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-xl shadow-md
                       bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                       focus:outline-none focus:ring-2 focus:ring-sky-400">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Data Pasien</span>
            </button>
        </div>
    </div>

    {{-- CARD TABEL PASIEN --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm">

        {{-- Toolbar --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 
                   px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">

            {{-- Page length --}}
            <div class="flex items-center gap-2 text-sm">
                <span class="text-slate-600 dark:text-slate-300 hidden sm:inline">Tampil</span>
                <select id="pasien_pageLength"
                    class="border border-slate-300 dark:border-slate-600 text-sm rounded-lg
                           focus:ring-sky-500 focus:border-sky-500
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
                    <input type="text" id="pasien_searchInput"
                        class="block w-full md:w-80 pl-9 pr-3 py-2 text-sm 
                               text-slate-800 dark:text-slate-100
                               border border-slate-300 dark:border-slate-600 rounded-lg 
                               bg-slate-50 dark:bg-slate-700
                               focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari nama pasien, NIK, EMR, atau no HP...">
                </div>
                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Contoh: <span class="italic">Budi Santoso, 16 digit NIK, nomor EMR, atau nomor HP</span>.
                </p>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table id="pasienTable"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100
                       border-t border-slate-100 dark:border-slate-700">
                <thead
                    class="text-xs font-semibold uppercase tracking-wide
                           bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600 text-white">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Profil</th>
                        <th class="px-6 py-3">Nama Pasien</th>
                        <th class="px-6 py-3">Username</th>
                        <th class="px-6 py-3">Email Akun</th>
                        <th class="px-6 py-3">Role</th>
                        <th class="px-6 py-3">Alamat</th>
                        <th class="px-6 py-3">No HP</th>
                        <th class="px-6 py-3">Tanggal Lahir</th>
                        <th class="px-6 py-3">Jenis Kelamin</th>
                        <th class="px-6 py-3">Nomor EMR</th>
                        <th class="px-6 py-3 text-center">Aksi</th> {{-- tombol aksi tetap, tidak diubah --}}
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 
                   px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700
                   bg-slate-50/70 dark:bg-slate-800/60 rounded-b-2xl">
            <div id="pasien_customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300"></div>

            <ul id="pasien_customPagination"
                class="inline-flex items-center gap-0 text-sm isolate rounded-lg 
                       border border-slate-200 dark:border-slate-600 overflow-hidden">
                {{-- tombol pagination diinject via JS --}}
            </ul>
        </div>
    </div>

</section>

{{-- ================= MODAL ADD PASIEN (CREATE) ================= --}}
<div id="addPasienModal" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center 
           w-full h-full p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="relative w-full max-w-5xl">
        <div
            class="relative bg-white rounded-2xl shadow-2xl dark:bg-slate-800 
                   border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh] overflow-y-auto">

            {{-- HEADER --}}
            <div
                class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 
                       border-b border-slate-100 dark:border-slate-700
                       bg-gradient-to-r from-sky-500 to-teal-500 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-sky-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-user-plus text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-slate-50">
                            Tambah Data Pasien
                        </h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">
                            Lengkapi data pasien yang akan digunakan untuk akun, kunjungan, dan rekam medis.
                        </p>
                    </div>
                </div>

                <button type="button" id="closeAddPasienModal"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full 
                           text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- FORM --}}
            <form id="formAddPasien" class="px-6 pb-5 pt-4 flex flex-col gap-4 bg-slate-50/60 dark:bg-slate-800"
                data-url="{{ route('manajemen_pengguna.add_pasien') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- INFO STRIP --}}
                <div
                    class="flex items-center gap-2 text-xs rounded-xl px-3 py-2
                           bg-emerald-50 text-emerald-700 border border-emerald-100
                           dark:bg-emerald-900/40 dark:text-emerald-100 dark:border-emerald-800">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Gunakan data identitas sesuai KTP/BPJS agar sinkron dengan klaim asuransi &amp; rekam
                        medis.</span>
                </div>

                {{-- CONTENT SECTIONS --}}
                <div class="space-y-6 mt-2">

                    {{-- SECTION: AKUN & FOTO --}}
                    <div
                        class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-900/40 px-4 md:px-5 py-4">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <div class="flex items-center gap-2">
                                <div
                                    class="h-8 w-8 flex items-center justify-center rounded-xl bg-sky-100 text-sky-600 dark:bg-sky-900/40 dark:text-sky-200">
                                    <i class="fa-solid fa-id-card-clip text-sm"></i>
                                </div>
                                <div>
                                    <p
                                        class="text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">
                                        Akun &amp; Login Pasien
                                    </p>
                                    <p class="text-[11px] text-slate-400 dark:text-slate-500">
                                        Digunakan pasien untuk akses aplikasi &amp; riwayat kunjungan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-[auto,1fr] gap-6 items-start mt-1">

                            {{-- FOTO --}}
                            <div class="flex justify-center md:justify-start">
                                <div id="foto_drop_area_pasien"
                                    class="relative w-32 md:w-36 aspect-[3/4] rounded-xl border-2 border-dashed border-sky-300/80 
                                           flex items-center justify-center cursor-pointer overflow-hidden
                                           bg-slate-50 dark:bg-slate-700 transition">
                                    <img id="preview_foto_pasien" src="" alt="Preview Foto"
                                        class="hidden w-full h-full object-cover">
                                    <div id="placeholder_foto_pasien"
                                        class="absolute inset-0 flex flex-col items-center justify-center 
                                               text-slate-400 text-[11px] px-2 text-center">
                                        <i class="fa-solid fa-user-circle text-xl mb-1"></i>
                                        <span>Upload foto profil pasien</span>
                                        <span class="text-[10px]">JPG/PNG, max ±2MB</span>
                                    </div>
                                    <input type="file" name="foto_pasien" id="foto_pasien" accept="image/*"
                                        class="absolute inset-0 opacity-0 cursor-pointer">
                                </div>
                            </div>

                            {{-- DATA AKUN --}}
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                    {{-- Username --}}
                                    <div class="space-y-1">
                                        <label for="username_pasien"
                                            class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                            Username
                                        </label>
                                        <input type="text" name="username_pasien" id="username_pasien"
                                            class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                                   focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                                   dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                            placeholder="Username login" required>
                                        <div id="username_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                    </div>

                                    {{-- Email --}}
                                    <div class="space-y-1">
                                        <label for="email_pasien"
                                            class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                            Email
                                        </label>
                                        <input type="email" name="email_pasien" id="email_pasien"
                                            class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                                   focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                                   dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                            placeholder="pasien@example.com" required>
                                        <div id="email_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                    </div>

                                    {{-- Password --}}
                                    <div class="space-y-1">
                                        <label for="password_pasien"
                                            class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                            Password
                                        </label>
                                        <input type="password" name="password_pasien" id="password_pasien"
                                            class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                                   focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                                   dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                            placeholder="••••••••" required>
                                        <div id="password_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                    </div>

                                    {{-- Konfirmasi Password --}}
                                    <div class="space-y-1">
                                        <label for="password_pasien_confirmation"
                                            class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                            Konfirmasi Password
                                        </label>
                                        <input type="password" name="password_pasien_confirmation"
                                            id="password_pasien_confirmation"
                                            class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                                   focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                                   dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                            placeholder="••••••••" required
                                            oninput="this.setCustomValidity(this.value !== password_pasien.value ? 'Password tidak sama!' : '')">
                                        <div id="password_pasien_confirmation-error"
                                            class="text-red-600 text-xs mt-1"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="foto_pasien-error" class="text-red-600 text-xs mt-2 text-center w-full"></div>
                    </div>

                    {{-- SECTION: IDENTITAS PASIEN --}}
                    <div
                        class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-900/40 px-4 md:px-5 py-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div
                                class="h-8 w-8 flex items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-200">
                                <i class="fa-solid fa-id-badge text-sm"></i>
                            </div>
                            <div>
                                <p
                                    class="text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">
                                    Identitas Pasien
                                </p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">
                                    Data utama pasien untuk administrasi dan rekam medis.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            {{-- Nama --}}
                            <div class="space-y-1 md:col-span-2">
                                <label for="nama_pasien"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Nama Pasien
                                </label>
                                <input type="text" id="nama_pasien" name="nama_pasien"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Nama lengkap pasien" required>
                                <div id="nama_pasien-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- NIK --}}
                            <div class="space-y-1">
                                <label for="nik"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    NIK (KTP)
                                </label>
                                <input type="text" id="nik" name="nik"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="16 digit NIK">
                                <div id="nik-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- No BPJS --}}
                            <div class="space-y-1">
                                <label for="no_bpjs"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    No. BPJS
                                </label>
                                <input type="text" id="no_bpjs" name="no_bpjs"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Opsional jika ada">
                                <div id="no_bpjs-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- No EMR --}}
                            <div class="space-y-1">
                                <label for="no_emr"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Nomor EMR
                                </label>
                                <input type="text" id="no_emr" name="no_emr"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Bisa diisi otomatis / manual">
                                <div id="no_emr-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Tanggal lahir --}}
                            <div class="space-y-1">
                                <label for="tanggal_lahir_pasien"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Tanggal Lahir
                                </label>
                                <input type="date" id="tanggal_lahir_pasien" name="tanggal_lahir_pasien"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    required>
                                <div id="tanggal_lahir_pasien-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Jenis kelamin --}}
                            <div class="space-y-1">
                                <label for="jenis_kelamin_pasien"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Jenis Kelamin
                                </label>
                                <select id="jenis_kelamin_pasien" name="jenis_kelamin_pasien"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50">
                                    <option value="" disabled selected>Pilih Jenis Kelamin</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                                <div id="jenis_kelamin_pasien-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Golongan darah --}}
                            <div class="space-y-1">
                                <label for="golongan_darah"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Golongan Darah
                                </label>
                                <select id="golongan_darah" name="golongan_darah"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50">
                                    <option value="" selected>Belum diketahui</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="AB">AB</option>
                                    <option value="O">O</option>
                                </select>
                                <div id="golongan_darah-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Status perkawinan --}}
                            <div class="space-y-1">
                                <label for="status_perkawinan"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Status Perkawinan
                                </label>
                                <select id="status_perkawinan" name="status_perkawinan"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50">
                                    <option value="" selected>Belum diisi</option>
                                    <option value="Belum Menikah">Belum Menikah</option>
                                    <option value="Menikah">Menikah</option>
                                    <option value="Cerai">Cerai</option>
                                </select>
                                <div id="status_perkawinan-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Pekerjaan --}}
                            <div class="space-y-1">
                                <label for="pekerjaan"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Pekerjaan
                                </label>
                                <input type="text" id="pekerjaan" name="pekerjaan"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Contoh: Karyawan, Ibu Rumah Tangga">
                                <div id="pekerjaan-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- No HP --}}
                            <div class="space-y-1">
                                <label for="no_hp_pasien"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    No HP Pasien
                                </label>
                                <input type="text" name="no_hp_pasien" id="no_hp_pasien"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="0812xxxxxxxx" required>
                                <div id="no_hp_pasien-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Alamat --}}
                            <div class="space-y-1 md:col-span-2">
                                <label for="alamat_pasien"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Alamat
                                </label>
                                <input type="text" id="alamat_pasien" name="alamat_pasien"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Alamat lengkap pasien" required>
                                <div id="alamat_pasien-error" class="text-red-600 text-xs mt-1"></div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION: PENANGGUNG JAWAB & CATATAN --}}
                    <div
                        class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-900/40 px-4 md:px-5 py-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div
                                class="h-8 w-8 flex items-center justify-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-200">
                                <i class="fa-solid fa-notes-medical text-sm"></i>
                            </div>
                            <div>
                                <p
                                    class="text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">
                                    Penanggung Jawab &amp; Catatan Medis
                                </p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">
                                    Informasi tambahan untuk keamanan dan keselamatan pasien.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            {{-- Nama PJ --}}
                            <div class="space-y-1">
                                <label for="nama_penanggung_jawab"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Nama Penanggung Jawab
                                </label>
                                <input type="text" id="nama_penanggung_jawab" name="nama_penanggung_jawab"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Opsional">
                                <div id="nama_penanggung_jawab-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- No HP PJ --}}
                            <div class="space-y-1">
                                <label for="no_hp_penanggung_jawab"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    No HP Penanggung Jawab
                                </label>
                                <input type="text" id="no_hp_penanggung_jawab" name="no_hp_penanggung_jawab"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Opsional">
                                <div id="no_hp_penanggung_jawab-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Alergi --}}
                            <div class="space-y-1 md:col-span-2">
                                <label for="alergi"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Alergi (Obat / Makanan)
                                </label>
                                <textarea id="alergi" name="alergi" rows="2"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Contoh: Alergi Penisilin, Alergi Udang (boleh dikosongkan)"></textarea>
                                <div id="alergi-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Catatan Medis --}}
                            <div class="space-y-1 md:col-span-2">
                                <label for="catatan_medis"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Catatan Medis Umum (Opsional)
                                </label>
                                <textarea id="catatan_medis" name="catatan_medis" rows="2"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Contoh: Pasien mudah pingsan saat diambil darah, harap pendekatan khusus."></textarea>
                                <div id="catatan_medis-error" class="text-red-600 text-xs mt-1"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER BUTTONS --}}
                <div class="flex justify-end gap-3 mt-6 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <button type="button" id="closeAddPasienModalFooter"
                        class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-slate-200 rounded-xl 
                               hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit" id="savePasienButton"
                        class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl 
                               bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                               focus:ring-2 focus:ring-sky-400">
                        Simpan Data Pasien
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================= MODAL EDIT PASIEN (UPDATE) ================= --}}
<div id="editPasienModal" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center 
           w-full h-full p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="relative w-full max-w-5xl">
        <div
            class="relative bg-white rounded-2xl shadow-2xl dark:bg-slate-800 
                   border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh] overflow-y-auto">

            {{-- HEADER --}}
            <div
                class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 
                       border-b border-slate-100 dark:border-slate-700
                       bg-gradient-to-r from-sky-500 to-teal-500 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div
                        class="h-10 w-10 rounded-xl bg-teal-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-user-pen text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-slate-50">
                            Edit Data Pasien
                        </h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">
                            Perbarui informasi akun, identitas, dan catatan medis pasien.
                        </p>
                    </div>
                </div>

                <button type="button" id="closeEditPasienModal"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full 
                           text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- FORM --}}
            <form id="formEditPasien" class="px-6 pb-5 pt-4 flex flex-col gap-4 bg-slate-50/60 dark:bg-slate-800"
                data-url="{{ route('manajemen_pengguna.update_pasien', ['id' => 0]) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_pasien_id" name="edit_pasien_id">

                {{-- INFO STRIP --}}
                <div
                    class="flex items-center gap-2 text-xs rounded-xl px-3 py-2
                           bg-emerald-50 text-emerald-700 border border-emerald-100
                           dark:bg-emerald-900/40 dark:text-emerald-100 dark:border-emerald-800">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Perubahan email, NIK, dan nomor EMR sebaiknya dikonfirmasi dengan pasien untuk menghindari
                        duplikasi data.</span>
                </div>

                <div class="space-y-6 mt-2">

                    {{-- SECTION: AKUN & FOTO --}}
                    <div
                        class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-900/40 px-4 md:px-5 py-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div
                                class="h-8 w-8 flex items-center justify-center rounded-xl bg-sky-100 text-sky-600 dark:bg-sky-900/40 dark:text-sky-200">
                                <i class="fa-solid fa-id-card-clip text-sm"></i>
                            </div>
                            <div>
                                <p
                                    class="text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">
                                    Akun &amp; Login Pasien
                                </p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">
                                    Atur ulang data akun bila ada perubahan kontak pasien.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-[auto,1fr] gap-6 items-start mt-1">

                            {{-- FOTO --}}
                            <div class="flex justify-center md:justify-start">
                                <div id="edit_foto_drop_area_pasien"
                                    class="relative w-32 md:w-36 aspect-[3/4] rounded-xl border-2 border-dashed border-sky-300/80 
                                           flex items-center justify-center cursor-pointer overflow-hidden
                                           bg-slate-50 dark:bg-slate-700 transition">
                                    <img id="edit_preview_foto_pasien" src="" alt="Preview Foto"
                                        class="hidden w-full h-full object-cover">
                                    <div id="edit_placeholder_foto_pasien"
                                        class="absolute inset-0 flex flex-col items-center justify-center 
                                               text-slate-400 text-[11px] px-2 text-center">
                                        <i class="fa-solid fa-user-circle text-xl mb-1"></i>
                                        <span>Upload foto baru (opsional)</span>
                                    </div>
                                    <input type="file" name="foto_pasien" id="edit_foto_pasien" accept="image/*"
                                        class="absolute inset-0 opacity-0 cursor-pointer">
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                    {{-- Username --}}
                                    <div class="space-y-1">
                                        <label for="edit_username_pasien"
                                            class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                            Username
                                        </label>
                                        <input type="text" name="username_pasien" id="edit_username_pasien"
                                            class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                                   focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                                   dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                            placeholder="Username" required>
                                        <div id="username_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                    </div>

                                    {{-- Email --}}
                                    <div class="space-y-1">
                                        <label for="edit_email_pasien"
                                            class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                            Email
                                        </label>
                                        <input type="email" name="email_pasien" id="edit_email_pasien"
                                            class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                                   focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                                   dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                            placeholder="pasien@example.com" required>
                                        <div id="email_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                    </div>

                                    {{-- Password (opsional) --}}
                                    <div class="space-y-1">
                                        <label for="edit_password_pasien"
                                            class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                            Password Baru (Opsional)
                                        </label>
                                        <input type="password" name="password_pasien" id="edit_password_pasien"
                                            class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                                   focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                                   dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                            placeholder="Biarkan kosong jika tidak diubah">
                                        <div id="password_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                    </div>

                                    {{-- Konfirmasi password --}}
                                    <div class="space-y-1">
                                        <label for="edit_password_pasien_confirmation"
                                            class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                            Konfirmasi Password Baru
                                        </label>
                                        <input type="password" name="password_pasien_confirmation"
                                            id="edit_password_pasien_confirmation"
                                            class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                                   focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                                   dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                            placeholder="Ulangi password baru"
                                            oninput="this.setCustomValidity(this.value !== edit_password_pasien.value ? 'Password tidak sama!' : '')">
                                        <div id="password_pasien_confirmation-error"
                                            class="text-red-600 text-xs mt-1"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="foto_pasien-error" class="text-red-600 text-xs mt-2 text-center w-full"></div>
                    </div>

                    {{-- SECTION: IDENTITAS PASIEN --}}
                    <div
                        class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-900/40 px-4 md:px-5 py-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div
                                class="h-8 w-8 flex items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-200">
                                <i class="fa-solid fa-id-badge text-sm"></i>
                            </div>
                            <div>
                                <p
                                    class="text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">
                                    Identitas Pasien
                                </p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">
                                    Sesuaikan data identitas jika ada perubahan resmi.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            {{-- Nama --}}
                            <div class="space-y-1 md:col-span-2">
                                <label for="edit_nama_pasien"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Nama Pasien
                                </label>
                                <input type="text" id="edit_nama_pasien" name="nama_pasien"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    required>
                                <div id="nama_pasien-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- NIK --}}
                            <div class="space-y-1">
                                <label for="edit_nik"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    NIK (KTP)
                                </label>
                                <input type="text" id="edit_nik" name="nik"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="16 digit NIK">
                                <div id="nik-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- No BPJS --}}
                            <div class="space-y-1">
                                <label for="edit_no_bpjs"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    No. BPJS
                                </label>
                                <input type="text" id="edit_no_bpjs" name="no_bpjs"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Opsional jika ada">
                                <div id="no_bpjs-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- No EMR (readonly) --}}
                            <div class="space-y-1">
                                <label for="edit_no_emr"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Nomor EMR
                                </label>
                                <input type="text" id="edit_no_emr" name="no_emr"
                                    class="w-full bg-slate-100 dark:bg-slate-700 border border-slate-300 dark:border-slate-600
                                           text-slate-900 dark:text-slate-50 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5"
                                    readonly>
                                <div id="no_emr-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Tanggal lahir --}}
                            <div class="space-y-1">
                                <label for="edit_tanggal_lahir_pasien"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Tanggal Lahir
                                </label>
                                <input type="date" id="edit_tanggal_lahir_pasien" name="tanggal_lahir_pasien"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50">
                                <div id="tanggal_lahir_pasien-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Jenis kelamin --}}
                            <div class="space-y-1">
                                <label for="edit_jenis_kelamin_pasien"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Jenis Kelamin
                                </label>
                                <select id="edit_jenis_kelamin_pasien" name="jenis_kelamin_pasien"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50">
                                    <option value="" disabled selected>Pilih Jenis Kelamin</option>
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                                <div id="jenis_kelamin_pasien-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Golongan darah --}}
                            <div class="space-y-1">
                                <label for="edit_golongan_darah"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Golongan Darah
                                </label>
                                <select id="edit_golongan_darah" name="golongan_darah"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50">
                                    <option value="" selected>Belum diketahui</option>
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="AB">AB</option>
                                    <option value="O">O</option>
                                </select>
                                <div id="golongan_darah-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Status perkawinan --}}
                            <div class="space-y-1">
                                <label for="edit_status_perkawinan"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Status Perkawinan
                                </label>
                                <select id="edit_status_perkawinan" name="status_perkawinan"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50">
                                    <option value="" selected>Belum diisi</option>
                                    <option value="Belum Menikah">Belum Menikah</option>
                                    <option value="Menikah">Menikah</option>
                                    <option value="Cerai">Cerai</option>
                                </select>
                                <div id="status_perkawinan-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Pekerjaan --}}
                            <div class="space-y-1">
                                <label for="edit_pekerjaan"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Pekerjaan
                                </label>
                                <input type="text" id="edit_pekerjaan" name="pekerjaan"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Contoh: Karyawan, Ibu Rumah Tangga">
                                <div id="pekerjaan-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- No HP --}}
                            <div class="space-y-1">
                                <label for="edit_no_hp_pasien"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    No HP Pasien
                                </label>
                                <input type="text" name="no_hp_pasien" id="edit_no_hp_pasien"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="0812xxxxxxxx">
                                <div id="no_hp_pasien-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Alamat --}}
                            <div class="space-y-1 md:col-span-2">
                                <label for="edit_alamat_pasien"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Alamat
                                </label>
                                <input type="text" id="edit_alamat_pasien" name="alamat_pasien"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    required>
                                <div id="alamat_pasien-error" class="text-red-600 text-xs mt-1"></div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION: PENANGGUNG JAWAB & CATATAN --}}
                    <div
                        class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white/90 dark:bg-slate-900/40 px-4 md:px-5 py-4">
                        <div class="flex items-center gap-2 mb-3">
                            <div
                                class="h-8 w-8 flex items-center justify-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-200">
                                <i class="fa-solid fa-notes-medical text-sm"></i>
                            </div>
                            <div>
                                <p
                                    class="text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">
                                    Penanggung Jawab &amp; Catatan Medis
                                </p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500">
                                    Update informasi penting terkait penanggung jawab &amp; kondisi khusus pasien.
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            {{-- Nama PJ --}}
                            <div class="space-y-1">
                                <label for="edit_nama_penanggung_jawab"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Nama Penanggung Jawab
                                </label>
                                <input type="text" id="edit_nama_penanggung_jawab" name="nama_penanggung_jawab"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Opsional">
                                <div id="nama_penanggung_jawab-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- No HP PJ --}}
                            <div class="space-y-1">
                                <label for="edit_no_hp_penanggung_jawab"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    No HP Penanggung Jawab
                                </label>
                                <input type="text" id="edit_no_hp_penanggung_jawab" name="no_hp_penanggung_jawab"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Opsional">
                                <div id="no_hp_penanggung_jawab-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Alergi --}}
                            <div class="space-y-1 md:col-span-2">
                                <label for="edit_alergi"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Alergi (Obat / Makanan)
                                </label>
                                <textarea id="edit_alergi" name="alergi" rows="2"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Contoh: Alergi Penisilin, Alergi Udang (boleh dikosongkan)"></textarea>
                                <div id="alergi-error" class="text-red-600 text-xs mt-1"></div>
                            </div>

                            {{-- Catatan Medis --}}
                            <div class="space-y-1 md:col-span-2">
                                <label for="edit_catatan_medis"
                                    class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                                    Catatan Medis Umum (Opsional)
                                </label>
                                <textarea id="edit_catatan_medis" name="catatan_medis" rows="2"
                                    class="w-full bg-white border border-slate-300 text-slate-900 text-sm rounded-xl
                                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2.5
                                           dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                                    placeholder="Contoh: Pasien mudah pingsan saat diambil darah, harap pendekatan khusus."></textarea>
                                <div id="catatan_medis-error" class="text-red-600 text-xs mt-1"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FOOTER BUTTONS --}}
                <div class="flex justify-end gap-3 mt-6 border-t border-slate-200 pt-4 dark:border-slate-700">
                    <button type="button" id="closeEditPasienModalFooter"
                        class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-slate-200 rounded-xl 
                               hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit" id="updatePasienButton"
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


@vite(['resources/js/admin/manajemenPengguna/data_pasien.js'])
