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
                    <th class="px-6 py-3">Nama Pasien</th>
                    <th class="px-6 py-3">Username</th>
                    <th class="px-6 py-3">Email Akun</th>
                    <th class="px-6 py-3">Role</th>
                    <th class="px-6 py-3">Alamat</th>
                    <th class="px-6 py-3">Tanggal Lahir</th>
                    <th class="px-6 py-3">Jenis Kelamin</th>
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
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4">
    <div class="relative w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">

            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Tambah Data Pasien</h3>
            </div>

            <form id="formAddPasien" class="p-5 flex flex-col gap-4"
                data-url="{{ route('manajemen_pengguna.add_pasien') }}" method="POST">
                @csrf

                {{-- Username --}}
                <div>
                    <label for="username_pasien"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Username</label>
                    <input type="text" name="username_pasien" id="username_pasien"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Username" required>
                    <div id="username_pasien-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <div>
                    <label for="nama_pasien" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Nama Pasien
                    </label>
                    <input type="text" id="nama_pasien" name="nama_pasien"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Nama Pasien" required>
                    <div id="nama_pasien-error" class="text-red-600 text-sm mt-1"></div>
                </div>


                {{-- Email pasien --}}
                <div>
                    <label for="email_pasien"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                    <input type="email" name="email_pasien" id="email_pasien"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500
                        focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="pasien@example.com" required>
                    <div id="email_pasien-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <div>
                    <label for="alamat_pasien" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Alamat
                    </label>
                    <input type="text" id="alamat_pasien" name="alamat_pasien"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Alamat Lengkap" required>
                    <div id="alamat_pasien-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Password --}}
                <div>
                    <label for="password_pasien"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                    <input type="password" name="password_pasien" id="password_pasien"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="••••••••" required>
                    <div id="password_pasien-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_pasien_confirmation"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Confirm Password</label>
                    <input type="password" name="password_pasien_confirmation" id="password_pasien_confirmation"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="••••••••" required
                        oninput="this.setCustomValidity(this.value !== password_pasien.value ? 'Password tidak sama!' : '')">
                    <div id="password_pasien_confirmation-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <div>
                    <label for="tanggal_lahir" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Tanggal Lahir
                    </label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        required>
                    <div id="tanggal_lahir-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <div>
                    <label for="jenis_kelamin" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Jenis Kelamin
                    </label>
                    <select id="jenis_kelamin" name="jenis_kelamin"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        <option value="" disabled selected>Pilih Jenis Kelamin</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                    <div id="jenis_kelamin-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <div class="flex justify-end gap-3 mt-5 border-t border-gray-200 pt-4 dark:border-gray-600">
                    <button type="button" id="closeAddPasienModal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500">
                        Close
                    </button>
                    <button type="submit" id="savePasienButton"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Pasien -->
<div id="editPasienModal" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4">
    <div class="relative w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">

            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Data Pasien</h3>
            </div>

            <form id="formEditPasien" class="p-5 flex flex-col gap-4"
                data-url="{{ route('manajemen_pengguna.update_pasien', ['id' => 0]) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_pasien_id" name="edit_pasien_id">

                {{-- Username --}}
                <div>
                    <label for="edit_username_pasien"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Username</label>
                    <input type="text" name="edit_username_pasien" id="edit_username_pasien"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Username" required>
                    <div id="edit_username_pasien-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <div>
                    <label for="edit_nama_pasien"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Pasien</label>
                    <input type="text" id="edit_nama_pasien" name="edit_nama_pasien"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        required>
                    <div id="edit_nama_pasien-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Email pasien --}}
                <div>
                    <label for="edit_email_pasien"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                    <input type="email" name="edit_email_pasien" id="edit_email_pasien"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500
                        focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="pasien@example.com" required>
                    <div id="edit_email_pasien-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <div>
                    <label for="edit_alamat_pasien"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Alamat</label>
                    <input type="text" id="edit_alamat_pasien" name="edit_alamat_pasien"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        required>
                    <div id="edit_alamat-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Password --}}
                <div>
                    <label for="edit_password_pasien"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                    <input type="password" name="edit_password_pasien" id="edit_password_pasien"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="••••••••">
                    <div id="edit_password_pasien-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="edit_password_pasien_confirmation"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Confirm Password</label>
                    <input type="password" name="edit_password_pasien_confirmation"
                        id="edit_password_pasien_confirmation"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="••••••••"
                        oninput="this.setCustomValidity(this.value !== edit_password_pasien.value ? 'Password tidak sama!' : '')">
                    <div id="edit_password_pasien_confirmation-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <div>
                    <label for="edit_tanggal_lahir"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tanggal Lahir</label>
                    <input type="date" id="edit_tanggal_lahir" name="edit_tanggal_lahir"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        required>
                    <div id="edit_tanggal_lahir-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <div>
                    <label for="edit_jenis_kelamin"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Jenis Kelamin</label>
                    <select id="edit_jenis_kelamin" name="edit_jenis_kelamin"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        <option value="" disabled selected>Pilih Jenis Kelamin</option>
                        <option value="Laki-laki">Laki-laki</option>
                        <option value="Perempuan">Perempuan</option>
                    </select>
                    <div id="edit_jenis_kelamin-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                <div class="flex justify-end gap-3 mt-5 border-t border-gray-200 pt-4 dark:border-gray-600">
                    <button type="button" id="closeEditPasienModal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500">
                        Close
                    </button>
                    <button type="submit" id="updatePasienButton"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>




@vite(['resources/js/admin/manajemenPengguna/data_pasien.js'])
