<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Data Pasien</h2>

    <!-- Modal toggle -->
    <button id="btnAddPasien"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none">
        + Tambah Data
    </button>
</div>

<!-- Tabel -->
<div class="overflow-hidden rounded-lg shadow-md">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200">
        <div>
            <select id="pasien_pageLength"
                class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-24 p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="relative">
            <input type="text" id="pasien_searchInput"
                class="block w-60 p-2 pl-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Cari data...">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="pasienTable" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs uppercase bg-sky-500 text-white">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">Profile</th>
                    <th class="px-6 py-3">Nama Pasien</th>
                    <th class="px-6 py-3">Username</th>
                    <th class="px-6 py-3">Email Akun</th>
                    <th class="px-6 py-3">Role</th>
                    <th class="px-6 py-3">Alamat</th>
                    <th class="px-6 py-3">No Hp Pasien</th>
                    <th class="px-6 py-3">Tanggal Lahir</th>
                    <th class="px-6 py-3">Jenis Kelamin</th>
                    <th class="px-6 py-3">Nomor EMR</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-4 py-3 border-t border-gray-200 gap-3">
        <div id="pasien_customInfo" class="text-sm text-gray-700 dark:text-gray-300"></div>
        <ul id="pasien_customPagination" class="inline-flex -space-x-px text-sm"></ul>
    </div>
</div>

<!-- Modal Add Pasien -->
<div id="addPasienModal" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 bg-black/50">
    <div class="relative w-full max-w-5xl max-h-full">
        <div class="relative bg-white rounded-2xl shadow-2xl dark:bg-gray-800 flex flex-col h-full">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white">
                        Tambah Data Pasien
                    </h3>
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">
                        Lengkapi informasi akun, identitas, dan data medis dasar pasien.
                    </p>
                </div>
                <button type="button" id="closeAddPasienModal"
                    class="inline-flex items-center justify-center w-8 h-8 text-gray-500 bg-gray-100 rounded-full
                           hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-700 dark:text-gray-300
                           dark:hover:bg-gray-600">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Form --}}
            <form id="formAddPasien"
                class="flex-1 overflow-y-auto px-6 pb-6 pt-4 space-y-6"
                data-url="{{ route('manajemen_pengguna.add_pasien') }}"
                method="POST" enctype="multipart/form-data">
                @csrf

                <div class="grid gap-6 lg:grid-cols-[1fr,2fr]">

                    {{-- Kiri: Foto & Info --}}
                    <div class="space-y-4">

                        {{-- Foto --}}
                        <div
                            class="bg-gray-50 dark:bg-gray-900/40 border border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-4 flex flex-col items-center gap-3">
                            <div id="foto_drop_area_pasien"
                                class="relative w-32 md:w-36 aspect-[3/4] rounded-lg border-2 border-dashed border-gray-400 
                                       flex items-center justify-center cursor-pointer overflow-hidden bg-white dark:bg-gray-800 transition">
                                <img id="preview_foto_pasien" src="" alt="Preview Foto"
                                    class="hidden w-full h-full object-cover">
                                <div id="placeholder_foto_pasien"
                                    class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 text-xs text-center px-2">
                                    <i class="fa-solid fa-user-circle text-3xl mb-1"></i>
                                    <span>Upload foto profil</span>
                                    <span class="text-[10px]">Format: JPG/PNG, max ±2MB</span>
                                </div>
                                <input type="file" name="foto_pasien" id="foto_pasien" accept="image/*"
                                    class="absolute inset-0 opacity-0 cursor-pointer">
                            </div>
                            <div id="foto_pasien-error" class="text-red-600 text-xs text-center"></div>
                        </div>

                        {{-- Card info kecil --}}
                        <div
                            class="bg-blue-50 dark:bg-blue-900/30 border border-blue-100 dark:border-blue-800 rounded-xl p-3">
                            <p class="text-[11px] text-blue-700 dark:text-blue-200">
                                <span class="font-semibold">Tips:</span> Gunakan data identitas sesuai KTP/BPJS agar
                                sinkron dengan sistem rekam medis dan klaim asuransi.
                            </p>
                        </div>
                    </div>

                    {{-- Kanan: Data Akun, Identitas, Medis --}}
                    <div class="space-y-5">

                        {{-- SECTION: Data Akun --}}
                        <div
                            class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 p-4 space-y-3">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    Data Akun Pasien
                                </h4>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-100 text-[11px] font-medium text-blue-700 dark:bg-blue-900/50 dark:text-blue-200">
                                    Akun Login
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                                {{-- Username --}}
                                <div>
                                    <label for="username_pasien"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Username
                                    </label>
                                    <input type="text" name="username_pasien" id="username_pasien"
                                        class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Username" required>
                                    <div id="username_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label for="email_pasien"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Email
                                    </label>
                                    <input type="email" name="email_pasien" id="email_pasien"
                                        class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="pasien@example.com" required>
                                    <div id="email_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Password --}}
                                <div>
                                    <label for="password_pasien"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Password
                                    </label>
                                    <input type="password" name="password_pasien" id="password_pasien"
                                        class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="••••••••" required>
                                    <div id="password_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Konfirmasi Password --}}
                                <div>
                                    <label for="password_pasien_confirmation"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Konfirmasi Password
                                    </label>
                                    <input type="password" name="password_pasien_confirmation"
                                        id="password_pasien_confirmation"
                                        class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="••••••••" required
                                        oninput="this.setCustomValidity(this.value !== password_pasien.value ? 'Password tidak sama!' : '')">
                                    <div id="password_pasien_confirmation-error" class="text-red-600 text-xs mt-1"></div>
                                </div>
                            </div>
                        </div>

                        {{-- SECTION: Identitas Pasien --}}
                        <div
                            class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/40 p-4 space-y-3">
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-1">
                                Identitas Pasien
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                                {{-- Nama Pasien --}}
                                <div class="md:col-span-2">
                                    <label for="nama_pasien"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Nama Pasien
                                    </label>
                                    <input type="text" id="nama_pasien" name="nama_pasien"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Nama lengkap pasien" required>
                                    <div id="nama_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- NIK --}}
                                <div>
                                    <label for="nik"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        NIK (KTP)
                                    </label>
                                    <input type="text" id="nik" name="nik"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="16 digit NIK">
                                    <div id="nik-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- No BPJS --}}
                                <div>
                                    <label for="no_bpjs"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        No. BPJS
                                    </label>
                                    <input type="text" id="no_bpjs" name="no_bpjs"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Opsional jika ada">
                                    <div id="no_bpjs-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- No EMR --}}
                                <div>
                                    <label for="no_emr"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Nomor EMR
                                    </label>
                                    <input type="text" id="no_emr" name="no_emr"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Bisa diisi otomatis / manual">
                                    <div id="no_emr-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Tanggal Lahir --}}
                                <div>
                                    <label for="tanggal_lahir_pasien"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Tanggal Lahir
                                    </label>
                                    <input type="date" id="tanggal_lahir_pasien" name="tanggal_lahir_pasien"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        required>
                                    <div id="tanggal_lahir_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Jenis Kelamin --}}
                                <div>
                                    <label for="jenis_kelamin_pasien"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Jenis Kelamin
                                    </label>
                                    <select id="jenis_kelamin_pasien" name="jenis_kelamin_pasien"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500">
                                        <option value="" disabled selected>Pilih Jenis Kelamin</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                    <div id="jenis_kelamin_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Golongan Darah --}}
                                <div>
                                    <label for="golongan_darah"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Golongan Darah
                                    </label>
                                    <select id="golongan_darah" name="golongan_darah"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500">
                                        <option value="" selected>Belum diketahui</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="AB">AB</option>
                                        <option value="O">O</option>
                                    </select>
                                    <div id="golongan_darah-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Status Perkawinan --}}
                                <div>
                                    <label for="status_perkawinan"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Status Perkawinan
                                    </label>
                                    <select id="status_perkawinan" name="status_perkawinan"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500">
                                        <option value="" selected>Belum diisi</option>
                                        <option value="Belum Menikah">Belum Menikah</option>
                                        <option value="Menikah">Menikah</option>
                                        <option value="Cerai">Cerai</option>
                                    </select>
                                    <div id="status_perkawinan-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Pekerjaan --}}
                                <div>
                                    <label for="pekerjaan"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Pekerjaan
                                    </label>
                                    <input type="text" id="pekerjaan" name="pekerjaan"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Contoh: Karyawan, Ibu Rumah Tangga">
                                    <div id="pekerjaan-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- No HP --}}
                                <div>
                                    <label for="no_hp_pasien"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        No HP Pasien
                                    </label>
                                    <input type="text" name="no_hp_pasien" id="no_hp_pasien"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="0812xxxxxxxx" required>
                                    <div id="no_hp_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Alamat --}}
                                <div class="md:col-span-2">
                                    <label for="alamat_pasien"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Alamat
                                    </label>
                                    <input type="text" id="alamat_pasien" name="alamat_pasien"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Alamat lengkap pasien" required>
                                    <div id="alamat_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                </div>
                            </div>
                        </div>

                        {{-- SECTION: Penanggung Jawab & Catatan Medis --}}
                        <div
                            class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 p-4 space-y-3">
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-1">
                                Penanggung Jawab & Catatan Medis
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                                {{-- Nama Penanggung Jawab --}}
                                <div>
                                    <label for="nama_penanggung_jawab"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Nama Penanggung Jawab
                                    </label>
                                    <input type="text" id="nama_penanggung_jawab" name="nama_penanggung_jawab"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Opsional">
                                    <div id="nama_penanggung_jawab-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- No HP Penanggung Jawab --}}
                                <div>
                                    <label for="no_hp_penanggung_jawab"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        No HP Penanggung Jawab
                                    </label>
                                    <input type="text" id="no_hp_penanggung_jawab" name="no_hp_penanggung_jawab"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Opsional">
                                    <div id="no_hp_penanggung_jawab-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Alergi --}}
                                <div class="md:col-span-2">
                                    <label for="alergi"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Alergi (Obat / Makanan)
                                    </label>
                                    <textarea id="alergi" name="alergi" rows="2"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Contoh: Alergi Penisilin, Alergi Udang (boleh dikosongkan)"></textarea>
                                    <div id="alergi-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Catatan Medis Umum --}}
                                <div class="md:col-span-2">
                                    <label for="catatan_medis"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Catatan Medis Umum (Opsional)
                                    </label>
                                    <textarea id="catatan_medis" name="catatan_medis" rows="2"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Contoh: Pasien mudah pingsan saat diambil darah, harap pendekatan khusus."></textarea>
                                    <div id="catatan_medis-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                {{-- Sticky Footer --}}
                <div
                    class="flex justify-end gap-3 pt-4 mt-2 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 sticky bottom-0">
                    <button type="button" id="closeAddPasienModalFooter"
                        class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 
                               dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                        Batal
                    </button>
                    <button type="submit" form="formAddPasien" id="savePasienButton"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow 
                               hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 
                               dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-700">
                        Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pasien -->
<div id="editPasienModal" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 bg-black/50">
    <div class="relative w-full max-w-5xl max-h-full">
        <div class="relative bg-white rounded-2xl shadow-2xl dark:bg-gray-800 flex flex-col h-full">

            {{-- Header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white">
                        Edit Data Pasien
                    </h3>
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400">
                        Perbarui informasi akun, identitas, dan data medis dasar pasien.
                    </p>
                </div>
                <button type="button" id="closeEditPasienModal"
                    class="inline-flex items-center justify-center w-8 h-8 text-gray-500 bg-gray-100 rounded-full
                           hover:bg-gray-200 hover:text-gray-700 dark:bg-gray-700 dark:text-gray-300
                           dark:hover:bg-gray-600">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Form --}}
            <form id="formEditPasien"
                class="flex-1 overflow-y-auto px-6 pb-6 pt-4 space-y-6"
                data-url="{{ route('manajemen_pengguna.update_pasien', ['id' => 0]) }}"
                method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_pasien_id" name="edit_pasien_id">

                <div class="grid gap-6 lg:grid-cols-[1fr,2fr]">

                    {{-- KIRI: Foto --}}
                    <div class="space-y-4">

                        {{-- Foto --}}
                        <div
                            class="bg-gray-50 dark:bg-gray-900/40 border border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-4 flex flex-col items-center gap-3">
                            <div id="edit_foto_drop_area_pasien"
                                class="relative w-32 md:w-36 aspect-[3/4] rounded-lg border-2 border-dashed border-gray-400 
                                       flex items-center justify-center cursor-pointer overflow-hidden bg-white dark:bg-gray-800 transition">
                                <img id="edit_preview_foto_pasien" src="" alt="Preview Foto"
                                    class="hidden w-full h-full object-cover">
                                <div id="edit_placeholder_foto_pasien"
                                    class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 text-xs text-center px-2">
                                    <i class="fa-solid fa-user-circle text-3xl mb-1"></i>
                                    <span>Upload foto profil</span>
                                    <span class="text-[10px]">Format: JPG/PNG, max ±2MB</span>
                                </div>
                                <input type="file" name="foto_pasien" id="edit_foto_pasien" accept="image/*"
                                    class="absolute inset-0 opacity-0 cursor-pointer">
                            </div>
                            <div id="foto_pasien-error" class="text-red-600 text-xs text-center"></div>
                        </div>

                        {{-- Info kecil --}}
                        <div
                            class="bg-amber-50 dark:bg-amber-900/30 border border-amber-100 dark:border-amber-800 rounded-xl p-3">
                            <p class="text-[11px] text-amber-700 dark:text-amber-200">
                                <span class="font-semibold">Catatan:</span> Perubahan email dan NIK sebaiknya
                                dikonfirmasi dengan pasien untuk menghindari duplikasi data.
                            </p>
                        </div>
                    </div>

                    {{-- KANAN: Data Akun + Identitas + Medis --}}
                    <div class="space-y-5">

                        {{-- SECTION: Data Akun --}}
                        <div
                            class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/40 p-4 space-y-3">
                            <div class="flex items-center justify-between gap-2 mb-1">
                                <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100">
                                    Data Akun Pasien
                                </h4>
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-100 text-[11px] font-medium text-blue-700 dark:bg-blue-900/50 dark:text-blue-200">
                                    Akun Login
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                                {{-- Username --}}
                                <div>
                                    <label for="edit_username_pasien"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Username
                                    </label>
                                    <input type="text" name="username_pasien" id="edit_username_pasien"
                                        class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Username" required>
                                    <div id="username_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label for="edit_email_pasien"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Email
                                    </label>
                                    <input type="email" name="email_pasien" id="edit_email_pasien"
                                        class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="pasien@example.com" required>
                                    <div id="email_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Password (opsional) --}}
                                <div>
                                    <label for="edit_password_pasien"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Password Baru (Opsional)
                                    </label>
                                    <input type="password" name="password_pasien" id="edit_password_pasien"
                                        class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Biarkan kosong jika tidak diubah">
                                    <div id="password_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Konfirmasi password --}}
                                <div>
                                    <label for="edit_password_pasien_confirmation"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Konfirmasi Password Baru
                                    </label>
                                    <input type="password" name="password_pasien_confirmation"
                                        id="edit_password_pasien_confirmation"
                                        class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Ulangi password baru"
                                        oninput="this.setCustomValidity(this.value !== edit_password_pasien.value ? 'Password tidak sama!' : '')">
                                    <div id="password_pasien_confirmation-error" class="text-red-600 text-xs mt-1"></div>
                                </div>
                            </div>
                        </div>

                        {{-- SECTION: Identitas Pasien --}}
                        <div
                            class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/40 p-4 space-y-3">
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-1">
                                Identitas Pasien
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                                {{-- Nama Pasien --}}
                                <div class="md:col-span-2">
                                    <label for="edit_nama_pasien"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Nama Pasien
                                    </label>
                                    <input type="text" id="edit_nama_pasien" name="nama_pasien"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        required>
                                    <div id="nama_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- NIK --}}
                                <div>
                                    <label for="edit_nik"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        NIK (KTP)
                                    </label>
                                    <input type="text" id="edit_nik" name="nik"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="16 digit NIK">
                                    <div id="nik-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- No BPJS --}}
                                <div>
                                    <label for="edit_no_bpjs"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        No. BPJS
                                    </label>
                                    <input type="text" id="edit_no_bpjs" name="no_bpjs"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Opsional jika ada">
                                    <div id="no_bpjs-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- No EMR (readonly) --}}
                                <div>
                                    <label for="edit_no_emr"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Nomor EMR
                                    </label>
                                    <input type="text" id="edit_no_emr" name="no_emr"
                                        class="bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        readonly>
                                    <div id="no_emr-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Tanggal Lahir --}}
                                <div>
                                    <label for="edit_tanggal_lahir_pasien"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Tanggal Lahir
                                    </label>
                                    <input type="date" id="edit_tanggal_lahir_pasien" name="tanggal_lahir_pasien"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500">
                                    <div id="tanggal_lahir_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Jenis Kelamin --}}
                                <div>
                                    <label for="edit_jenis_kelamin_pasien"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Jenis Kelamin
                                    </label>
                                    <select id="edit_jenis_kelamin_pasien" name="jenis_kelamin_pasien"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500">
                                        <option value="" disabled selected>Pilih Jenis Kelamin</option>
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                    <div id="jenis_kelamin_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Golongan Darah --}}
                                <div>
                                    <label for="edit_golongan_darah"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Golongan Darah
                                    </label>
                                    <select id="edit_golongan_darah" name="golongan_darah"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500">
                                        <option value="" selected>Belum diketahui</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="AB">AB</option>
                                        <option value="O">O</option>
                                    </select>
                                    <div id="golongan_darah-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Status Perkawinan --}}
                                <div>
                                    <label for="edit_status_perkawinan"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Status Perkawinan
                                    </label>
                                    <select id="edit_status_perkawinan" name="status_perkawinan"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500">
                                        <option value="" selected>Belum diisi</option>
                                        <option value="Belum Menikah">Belum Menikah</option>
                                        <option value="Menikah">Menikah</option>
                                        <option value="Cerai">Cerai</option>
                                    </select>
                                    <div id="status_perkawinan-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Pekerjaan --}}
                                <div>
                                    <label for="edit_pekerjaan"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Pekerjaan
                                    </label>
                                    <input type="text" id="edit_pekerjaan" name="pekerjaan"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Contoh: Karyawan, Ibu Rumah Tangga">
                                    <div id="pekerjaan-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- No HP --}}
                                <div>
                                    <label for="edit_no_hp_pasien"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        No HP Pasien
                                    </label>
                                    <input type="text" name="no_hp_pasien" id="edit_no_hp_pasien"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="0812xxxxxxxx">
                                    <div id="no_hp_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Alamat --}}
                                <div class="md:col-span-2">
                                    <label for="edit_alamat_pasien"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Alamat
                                    </label>
                                    <input type="text" id="edit_alamat_pasien" name="alamat_pasien"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        required>
                                    <div id="alamat_pasien-error" class="text-red-600 text-xs mt-1"></div>
                                </div>
                            </div>
                        </div>

                        {{-- SECTION: Penanggung Jawab & Catatan Medis --}}
                        <div
                            class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-900/40 p-4 space-y-3">
                            <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-1">
                                Penanggung Jawab & Catatan Medis
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                                {{-- Nama Penanggung Jawab --}}
                                <div>
                                    <label for="edit_nama_penanggung_jawab"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Nama Penanggung Jawab
                                    </label>
                                    <input type="text" id="edit_nama_penanggung_jawab" name="nama_penanggung_jawab"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Opsional">
                                    <div id="nama_penanggung_jawab-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- No HP Penanggung Jawab --}}
                                <div>
                                    <label for="edit_no_hp_penanggung_jawab"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        No HP Penanggung Jawab
                                    </label>
                                    <input type="text" id="edit_no_hp_penanggung_jawab" name="no_hp_penanggung_jawab"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Opsional">
                                    <div id="no_hp_penanggung_jawab-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Alergi --}}
                                <div class="md:col-span-2">
                                    <label for="edit_alergi"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Alergi (Obat / Makanan)
                                    </label>
                                    <textarea id="edit_alergi" name="alergi" rows="2"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Contoh: Alergi Penisilin, Alergi Udang (boleh dikosongkan)"></textarea>
                                    <div id="alergi-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                                {{-- Catatan Medis --}}
                                <div class="md:col-span-2">
                                    <label for="edit_catatan_medis"
                                        class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                        Catatan Medis Umum (Opsional)
                                    </label>
                                    <textarea id="edit_catatan_medis" name="catatan_medis" rows="2"
                                        class="bg-gray-50 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 
                                               text-gray-900 dark:text-white text-sm rounded-lg w-full p-2.5 
                                               focus:ring-blue-500 focus:border-blue-500"
                                        placeholder="Contoh: Pasien mudah pingsan saat diambil darah, harap pendekatan khusus."></textarea>
                                    <div id="catatan_medis-error" class="text-red-600 text-xs mt-1"></div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                {{-- Sticky Footer --}}
                <div
                    class="flex justify-end gap-3 pt-4 mt-2 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 sticky bottom-0">
                    <button type="button" id="closeEditPasienModalFooter"
                        class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 
                               dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600">
                        Batal
                    </button>
                    <button type="submit" form="formEditPasien" id="updatePasienButton"
                        class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 rounded-lg shadow 
                               hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 
                               dark:bg-blue-500 dark:hover:bg-blue-600 dark:focus:ring-blue-700">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@vite(['resources/js/admin/manajemenPengguna/data_pasien.js'])
