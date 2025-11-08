<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Data Perawat</h2>

    <!-- Modal toggle -->
    <button id="btnAddPerawat"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none">
        + Tambah Data
    </button>
</div>

<!-- Tabel -->
<div class="overflow-hidden rounded-lg shadow-md ">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200">
        <div>
            <select id="perawat_pageLength"
                class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-24 p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>pdata
            </select>
        </div>
        <div class="relative">
            <input type="text" id="kasir_searchInput"
                class="block w-60 p-2 pl-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Cari data...">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="userPerawat" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs uppercase bg-sky-500 text-white">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">Profile</th>
                    <th class="px-6 py-3">Nama Perawat</th>
                    <th class="px-6 py-3">Username</th>
                    <th class="px-6 py-3">Email Akun</th>
                    <th class="px-6 py-3">Role</th>
                    <th class="px-6 py-3">No HP</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-4 py-3 border-t border-gray-200 gap-3">
        <div id="perawat_customInfo" class="text-sm text-gray-700 dark:text-gray-300"></div>
        <ul id="perawat_customPagination" class="inline-flex -space-x-px text-sm"></ul>
    </div>
</div>

{{-- Modal Add Perawat --}}
<div id="addPerawatModal" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 bg-black bg-opacity-50">
    <div class="relative w-full max-w-3xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">

            {{-- Header --}}
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Tambah Data Perawat</h3>
            </div>

            {{-- Form --}}
            <form id="formAddPerawat" class="p-5 flex flex-col gap-4"
                data-url="{{ route('manajemen_pengguna.add_perawat') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Foto --}}
                <div class="col-span-full flex justify-center">
                    <div id="foto_drop_area_kasir"
                        class="relative w-36 aspect-[3/4] rounded-lg border-2 border-dashed border-gray-400 
                        flex items-center justify-center cursor-pointer overflow-hidden bg-gray-50 transition">

                        <!-- Preview -->
                        <img id="preview_foto_perawat" src="" alt="Preview Foto"
                            class="hidden w-full h-full object-cover">

                        <!-- Placeholder -->
                        <div id="placeholder_foto_perawat"
                            class="absolute inset-0 flex items-center justify-center text-gray-400 text-sm">
                            upload profile
                        </div>

                        <!-- Input File -->
                        <input type="file" name="foto_perawat" id="foto_perawat" accept="image/*"
                            class="absolute inset-0 opacity-0 cursor-pointer" required>
                    </div>
                </div>
                <div id="foto_perawat-error" class="text-red-600 text-sm mt-1 text-center"></div>

                <!-- Grid Form -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Username --}}
                    <div>
                        <label for="username_perawat"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Username</label>
                        <input type="text" name="username_perawat" id="username_perawat"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5
                            focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                            placeholder="Username" required>
                        <div id="username_perawat-error" class="text-red-600 text-sm mt-1"></div>
                    </div>

                    {{-- Nama Apoteker --}}
                    <div>
                        <label for="nama_perawat"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Perawat</label>
                        <input type="text" name="nama_perawat" id="nama_perawat"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500
                            w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                            placeholder="Nama Perawat" required>
                        <div id="nama_perawat-error" class="text-red-600 text-sm mt-1"></div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email_perawat"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                        <input type="email" name="email_perawat" id="email_perawat"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500
                            w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                            placeholder="perawat@example.com" required>
                        <div id="email_perawat-error" class="text-red-600 text-sm mt-1"></div>
                    </div>

                    {{-- No HP --}}
                    <div>
                        <label for="no_hp_perawat"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No
                            HP</label>
                        <input type="text" name="no_hp_perawat" id="no_hp_perawat"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500
                            w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                            placeholder="0812xxxxxxxx" required>
                        <div id="no_hp_perawat-error" class="text-red-600 text-sm mt-1"></div>
                    </div>
                </div>

                {{-- Password --}}
                <div class="pt-2 border-t border-gray-200 dark:border-gray-600">
                    {{-- Password Baru --}}
                    <div>
                        <label for="password_perawat"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password
                            </_apotekerlabel>
                            <input type="password" name="password_perawat" id="password_perawat"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5
                            focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                                placeholder="••••••••" required>
                            <div id="password_perawat-error" class="text-red-600 text-sm mt-1"></div>
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div>
                        <label for="password_perawat_confirmation"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Konfirmasi
                            Password</label>
                        <input type="password" name="password_perawat_confirmation"
                            id="password_perawat_confirmation"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5
                            focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                            placeholder="••••••••" required
                            oninput="this.setCustomValidity(this.value !== password_perawat.value ? 'Password tidak sama!' : '')">
                        <div id="password_perawat_confirmation-error" class="text-red-600 text-sm mt-1"></div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 mt-5 border-t border-gray-200 pt-4 dark:border-gray-600">
                    <button type="button" id="closeAddPerawatModal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg
                        hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500">
                        Close
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800
                        focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700
                        dark:focus:ring-blue-800">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- Modal Edit Perawat --}}
<div id="editPerawatModal" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 bg-black bg-opacity-50">
    <div class="relative w-full max-w-3xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">

            {{-- Header --}}
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Data Perawat</h3>
            </div>

            {{-- Form --}}
            <form id="formEditPerawat" class="p-5 flex flex-col gap-4"
                data-url="{{ route('manajemen_pengguna.update_perawat', ['id' => 0]) }}" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="perawat_id" id="edit_perawat_id">

                {{-- Foto --}}
                <div class="col-span-full flex justify-center">
                    <div id="edit_foto_drop_area_perawat"
                        class="relative w-36 aspect-[3/4] rounded-lg border-2 border-dashed border-gray-400 
                        flex items-center justify-center cursor-pointer overflow-hidden bg-gray-50 transition">

                        <!-- Preview -->
                        <img id="edit_preview_foto_perawat" src="" alt="Preview Foto"
                            class="hidden w-full h-full object-cover">

                        <!-- Placeholder -->
                        <div id="edit_placeholder_foto_perawat"
                            class="absolute inset-0 flex items-center justify-center text-gray-400 text-sm">
                            upload profile
                        </div>

                        <!-- Input File -->
                        <input type="file" name="edit_foto_perawat" id="edit_foto_perawat" accept="image/*"
                            class="absolute inset-0 opacity-0 cursor-pointer">
                    </div>
                </div>
                <div id="edit_foto_perawat-error" class="text-red-600 text-sm mt-1 text-center"></div>

                <!-- Grid Form -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    {{-- Username --}}
                    <div>
                        <label for="edit_username_perawat"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Username</label>
                        <input type="text" name="edit_username_perawat" id="edit_username_perawat"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5
                            focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                            placeholder="Username" required>
                        <div id="edit_username-error" class="text-red-600 text-sm mt-1"></div>
                    </div>

                    {{-- Nama --}}
                    <div>
                        <label for="edit_nama_perawat"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Perawat</label>
                        <input type="text" name="edit_nama_perawat" id="edit_nama_perawat"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500
                            w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                            placeholder="Nama Perawat" required>
                        <div id="edit_nama_perawat-error" class="text-red-600 text-sm mt-1"></div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="edit_email_perawat"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                        <input type="email" name="edit_email_perawat" id="edit_email_perawat"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500
                            w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                            placeholder="perawat@example.com" required>
                        <div id="edit_email_perawat-error" class="text-red-600 text-sm mt-1"></div>
                    </div>

                    {{-- No HP --}}
                    <div>
                        <label for="edit_no_hp_perawat"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No HP</label>
                        <input type="text" name="edit_no_hp_perawat" id="edit_no_hp_perawat"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500
                            w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                            placeholder="081234567890" required>
                        <div id="edit_no_hp_perawat-error" class="text-red-600 text-sm mt-1"></div>
                    </div>
                </div>

                {{-- Password (Opsional) --}}
                <div class="pt-2 border-t border-gray-200 dark:border-gray-600">

                    {{-- Password baru --}}
                    <div>
                        <label for="edit_password_perawat"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password Baru</label>
                        <input type="password" name="edit_password_perawat" id="edit_password_perawat"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5
                            focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                            placeholder="••••••••">
                        <div id="edit_password_perawat-error" class="text-red-600 text-sm mt-1"></div>
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div>
                        <label for="edit_password_perawat_confirmation"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Konfirmasi
                            Password</label>
                        <input type="password" name="edit_password_perawat_confirmation"
                            id="edit_password_perawat_confirmation"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-2.5
                            focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                            placeholder="••••••••">
                        <div id="edit_password_perawat_confirmation-error" class="text-red-600 text-sm mt-1"></div>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 mt-5 border-t border-gray-200 pt-4 dark:border-gray-600">
                    <button type="button" id="closeEditPerawatModal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg
                        hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500">
                        Close
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800
                        focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700
                        dark:focus:ring-blue-800">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@vite(['resources/js/admin/manajemenPengguna/data_perawat.js'])
