<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Data Dokter</h2>
    <button id="btnAddDokter"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none">
        + Tambah Data
    </button>
</div>

<!-- Tabel -->
<div class="overflow-hidden rounded-lg shadow-md">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200">
        <div>
            <select id="dokter_pageLength"
                class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-24 p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="relative">
            <input type="text" id="dokter_searchInput"
                class="block w-60 p-2 pl-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Cari data...">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="dokterTable" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs uppercase bg-sky-500 text-white">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">Nama Dokter</th>
                    <th class="px-6 py-3">Username</th>
                    <th class="px-6 py-3">Email Dokter</th>
                    <th class="px-6 py-3">Role</th>
                    <th class="px-6 py-3">Spesialis</th>
                    <th class="px-6 py-3">No HP</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-4 py-3 border-t border-gray-200 gap-3">
        <div id="dokter_customInfo" class="text-sm text-gray-700 dark:text-gray-300"></div>
        <ul id="dokter_customPagination" class="inline-flex -space-x-px text-sm"></ul>
    </div>
</div>


{{-- Modal Add Dokter --}}
<div id="addDokterModal" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4">
    <div class="relative w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">

            {{-- Header --}}
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Tambah Data Dokter</h3>
            </div>

            {{-- Form --}}
            <form id="formAddDokter" class="p-5 flex flex-col gap-4"
                data-url="{{ route('manajemen_pengguna.add_dokter') }}" method="POST">
                @csrf

                {{-- username --}}
                <div>
                    <label for="username_dokter" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Username
                    </label>
                    <input type="text" name="username_dokter" id="username_dokter"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="Username" required>
                    <div id="username_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Nama Dokter --}}
                <div>
                    <label for="nama_dokter" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Nama Dokter
                    </label>
                    <input type="text" name="nama_dokter" id="nama_dokter"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="Nama Dokter" required>
                    <div id="nama_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Email akun --}}
                <div>
                    <label for="email_akun_dokter"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email Akun</label>
                    <input type="email" name="email_akun_dokter" id="email_akun_dokter"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="dokter@example.com" required>
                    <div id="email_akun_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Spesialisasi --}}
                <div>
                    <label for="spesialis_dokter" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Spesialis
                    </label>
                    <select id="spesialis_dokter" name="spesialis_dokter" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white">
                        <option value="" disabled selected>Select Spesialis</option>
                        @foreach ($spesialis as $item)
                            <option value="{{ $item->id }}">{{ $item->nama_spesialis }}</option>
                        @endforeach
                    </select>
                    <div id="spesialis_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Email --}}
                <div>
                    <label for="email_dokter"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                    <input type="email" name="email_dokter" id="email_dokter"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="dokter@example.com" required>
                    <div id="email_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Password --}}
                <div>
                    <label for="password_dokter"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                    <input type="password" name="password_dokter" id="password_dokter"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="••••••••" required>
                    <div id="password_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="password_dokter_confirmation"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Confirm Password</label>
                    <input type="password" name="password_dokter_confirmation" id="password_dokter_confirmation"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="••••••••" required
                        oninput="this.setCustomValidity(this.value !== password_dokter.value ? 'Password tidak sama!' : '')">
                    <div id="password_dokter_confirmation-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- No HP --}}
                <div>
                    <label for="no_hp_dokter" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No
                        HP</label>
                    <input type="text" name="no_hp_dokter" id="no_hp_dokter"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="0812xxxxxxxx" required>
                    <div id="no_hp_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- deskripsi dokter --}}
                <div>
                    <label for="deskripsi_dokter"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Deskripsi
                    </label>
                    <input type="text" name="deskripsi_dokter" id="deskripsi_dokter"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white">
                    <div id="deskripsi_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- pengalaman --}}
                <div>
                    <label for="pengalaman_dokter"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Pengalaman
                    </label>
                    <input type="text" name="pengalaman_dokter" id="pengalaman_dokter"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white">
                    <div id="pengalaman_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 mt-5 border-t border-gray-200 pt-4 dark:border-gray-600">
                    <button type="button" id="closeAddDokterModal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg 
                        hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500">
                        Close
                    </button>
                    <button type="submit" id="saveDokterButton"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-blue-700 rounded-lg 
                        hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 
                        dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


{{-- Modal Edit Dokter --}}
<div id="editDokterModal" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4">
    <div class="relative w-full max-w-xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">

            {{-- Header --}}
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Edit Data Dokter</h3>
            </div>

            {{-- Form --}}
            <form id="formEditDokter" class="p-5 flex flex-col gap-4"
                data-url="{{ route('manajemen_pengguna.update_dokter', ['id' => 0]) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="edit_dokter_id" id="edit_dokter_id">

                <div>
                    <label for="edit_username_dokter"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Username
                    </label>
                    <input type="text" name="edit_username_dokter" id="edit_username_dokter"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="Username" required>
                    <div id="edit_username_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Nama Dokter --}}
                <div>
                    <label for="edit_nama_dokter"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Nama Dokter
                    </label>
                    <input type="text" id="edit_nama_dokter" name="edit_nama_dokter"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="Nama Dokter" required>
                    <div id="edit_nama_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Email akun --}}
                <div>
                    <label for="edit_email_akun_dokter"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email Akun</label>
                    <input type="email" name="edit_email_akun_dokter" id="edit_email_akun_dokter"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="dokter@example.com" required>
                    <div id="edit_email_akun_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Spesialisasi --}}
                <div>
                    <label for="edit_spesialis_dokter"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Spesialis
                    </label>
                    <select id="edit_spesialis_dokter" name="edit_spesialis_dokter" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white">
                        <option value="" disabled selected>Select Spesialis</option>
                        @foreach ($spesialis as $item)
                            <option value="{{ $item->id }}">{{ $item->nama_spesialis }}</option>
                        @endforeach
                    </select>
                    <div id="edit_spesialis_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Email --}}
                <div>
                    <label for="edit_email_dokter"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                    <input type="email" name="edit_email_dokter" id="edit_email_dokter"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="dokter@example.com" required>
                    <div id="edit_email_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Password --}}
                <div>
                    <label for="edit_password_dokter"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Password</label>
                    <input type="password" name="edit_password_dokter" id="edit_password_dokter"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="••••••••">
                    <div id="edit_password_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label for="edit_password_dokter_confirmation"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Confirm Password</label>
                    <input type="password" name="edit_password_dokter_confirmation"
                        id="edit_password_dokter_confirmation"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:text-white"
                        placeholder="••••••••"
                        oninput="this.setCustomValidity(this.value !== password_dokter.value ? 'Password tidak sama!' : '')">
                    <div id="edit_password_dokter_confirmation-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- No HP --}}
                <div>
                    <label for="edit_no_hp_dokter"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No
                        HP</label>
                    <input type="text" name="edit_no_hp_dokter" id="edit_no_hp_dokter"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="0812xxxxxxxx" required>
                    <div id="edit_no_hp_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- deskripsi dokter --}}
                <div>
                    <label for="edit_deskripsi_dokter"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Deskripsi
                    </label>
                    <input type="text" name="edit_deskripsi_dokter" id="edit_deskripsi_dokter"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white">
                    <div id="edit_deskripsi_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- pengalaman --}}
                <div>
                    <label for="edit_pengalaman_dokter"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Pengalaman
                    </label>
                    <input type="text" name="edit_pengalaman_dokter" id="edit_pengalaman_dokter"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white">
                    <div id="edit_pengalaman_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 mt-5 border-t border-gray-200 pt-4 dark:border-gray-600">
                    <button type="button" id="closeEditDokterModal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500">
                        Close
                    </button>
                    <button type="submit" id="updateDokterButton"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>






@vite(['resources/js/admin/manajemenPengguna/data_dokter.js'])
