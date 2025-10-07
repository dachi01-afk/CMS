<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Data Apoteker</h2>

    <!-- Modal toggle -->
    <button id="btnAddApoteker"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none">
        + Tambah Data
    </button>
</div>

<!-- Tabel -->
<div class="overflow-hidden rounded-lg shadow-md ">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200">
        <div>
            <select id="apoteker_pageLength"
                class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-24 p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="relative">
            <input type="text" id="apoteker_searchInput"
                class="block w-60 p-2 pl-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Cari data...">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="userApoteker" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs uppercase bg-sky-500 text-white">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">Nama Apoteker</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">No HP</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-4 py-3 border-t border-gray-200 gap-3">
        <div id="apoteker_customInfo" class="text-sm text-gray-700 dark:text-gray-300"></div>
        <ul id="apoteker_customPagination" class="inline-flex -space-x-px text-sm"></ul>
    </div>
</div>

{{-- Modal Add Apoteker --}}
<div id="addApotekerModal" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4">
    <div class="relative w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">

            {{-- Header --}}
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Tambah Data Apoteker</h3>
            </div>

            {{-- Form --}}
            <form id="formAddApoteker" class="p-5 flex flex-col gap-4"
                data-url="{{ route('manajemen_pengguna.add_apoteker') }}" method="POST">
                @csrf

                {{-- Username --}}
                <div>
                    <label for="username"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Username</label>
                    <input type="text" name="username" id="username_apoteker"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Nama Apoteker" required>
                    <div id="username-error_apoteker" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Nama Apoteker --}}
                <div>
                    <label for="nama" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Nama Apoteker
                    </label>
                    <input type="text" name="nama" id="nama_apoteker"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500
                        focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Nama Apoteker" required>
                    <div id="nama_apoteker-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Role --}}
                {{-- <div>
                    <label for="role_apoteker"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Role</label>
                    <select id="role_apoteker" name="role_apoteker" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        <option value="" disabled selected>Select role</option>
                        <option value="admin">Admin</option>
                        <option value="dokter">Dokter</option>
                        <option value="apoteker">Apoteker</option>
                        <option value="pasien">Pasien</option>
                    </select>
                    <div id="role_apoteker-error" class="text-red-600 text-sm mt-1"></div>
                </div> --}}

                {{-- Password --}}
                <div>
                    <label for="password"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                    <input type="password" name="password" id="password"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="••••••••" required>
                    <div id="password-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_confirmation"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="••••••••" required
                        oninput="this.setCustomValidity(this.value !== password.value ? 'Password tidak sama!' : '')">
                    <div id="password_confirmation-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Email Apoteker --}}
                <div>
                    <label for="email"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                    <input type="email" name="email" id="email_apoteker"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500
                        focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="apoteker@example.com" required>
                    <div id="email_apoteker-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- No HP --}}
                <div>
                    <label for="no_hp_apoteker" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No
                        HP</label>
                    <input type="text" name="no_hp_apoteker" id="no_hp_apoteker"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500
                        focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="0812xxxxxxxx" required>
                    <div id="no_hp_apoteker-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 mt-5 border-t border-gray-200 pt-4 dark:border-gray-600">
                    <button type="button" id="closeAddApotekerModal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg
                        hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500">
                        Close
                    </button>
                    <button type="submit" id="saveApotekerButton"
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

{{-- Modal Edit Apoteker --}}
<div id="editApotekerModal" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4">
    <div class="relative w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">

            {{-- Header --}}
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Data Apoteker</h3>
            </div>

            {{-- Form --}}
            <form id="formEditApoteker" class="p-5 flex flex-col gap-4"
                data-url="{{ route('manajemen_pengguna.update_apoteker', ['id' => 0]) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="apoteker_id" id="edit_apoteker_id">

                {{-- Nama --}}
                <div>
                    <label for="edit_nama_apoteker"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nama Apoteker</label>
                    <input type="text" name="edit_nama_apoteker" id="edit_nama_apoteker"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500
                        w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="Nama Apoteker" required>
                    <div id="edit_nama_apoteker-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Email --}}
                <div>
                    <label for="edit_email_apoteker"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                    <input type="email" name="edit_email_apoteker" id="edit_email_apoteker"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500
                        w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="apoteker@example.com" required>
                    <div id="edit_email_apoteker-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- No HP --}}
                <div>
                    <label for="edit_no_hp_apoteker"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No HP</label>
                    <input type="text" name="edit_no_hp_apoteker" id="edit_no_hp_apoteker"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500
                        w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="081234567890" required>
                    <div id="edit_no_hp_apoteker-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 mt-5 border-t border-gray-200 pt-4 dark:border-gray-600">
                    <button type="button" id="closeEditApotekerModal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg
                        hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500">
                        Close
                    </button>
                    <button type="submit" id="updateApotekerButton"
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






@vite(['resources/js/admin/manajemenPengguna/data_apoteker.js'])
