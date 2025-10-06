<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Data Dokter</h2>
    <button id="btnAddDokter"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none">
        + Tambah Data
    </button>
</div>

<!-- Tabel Dokter -->
<div class="overflow-hidden rounded-lg shadow-md ">
    <!-- Header control: search + page length -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200">
        <!-- Entries per page -->
        <div>
            <select id="dokter_pageLength"
                class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-24 p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>

        <!-- Search -->
        <div class="relative">
            <input type="text" id="dokter_searchInput"
                class="block w-60 p-2 pl-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Cari data...">
        </div>
    </div>

    <!-- Tabel -->
    <div class="overflow-x-auto">
        <table id="dokterTable" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs uppercase bg-sky-500 text-white">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">Nama Dokter</th>
                    <th class="px-6 py-3">Spesialisasi</th>
                    <th class="px-6 py-3">Email</th>
                    <th class="px-6 py-3">No HP</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data otomatis dari AJAX -->
            </tbody>
        </table>
    </div>

    <!-- Footer: Info + Pagination -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-4 py-3 border-t border-gray-200 gap-3">
        <!-- Custom Info -->
        <div id="dokter_customInfo" class="text-sm text-gray-700 dark:text-gray-300"></div>

        <!-- Pagination -->
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

                {{-- Spesialisasi --}}
                <div>
                    <label for="spesialisasi" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Spesialisasi
                    </label>
                    <select id="spesialisasi" name="spesialisasi" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white">
                        <option value="" disabled selected>Select Spesialis</option>
                        <option value="Determatologi">Determatologi</option>
                        <option value="Psikiatri">Psikiatri</option>
                        <option value="Onkologi">Onkologi</option>
                        <option value="Kardiologi">Kardiologi</option>
                    </select>
                    <div id="spesialisasi-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Email --}}
                <div>
                    <label for="email_dokter"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                    <input type="email" name="email" id="email_dokter"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="dokter@example.com" required>
                    <div id="email_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- No HP --}}
                <div>
                    <label for="no_hp" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No
                        HP</label>
                    <input type="text" name="no_hp" id="no_hp"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="0812xxxxxxxx" required>
                    <div id="no_hp-error" class="text-red-600 text-sm mt-1"></div>
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

                {{-- Spesialisasi --}}
                <div>
                    <label for="edit_spesialisasi"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Spesialisasi</label>
                    <select id="edit_spesialisasi" name="edit_spesialisasi" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white">
                        <option value="" disabled selected>Select Spesialis</option>
                        <option value="Determatologi">Determatologi</option>
                        <option value="Psikiatri">Psikiatri</option>
                        <option value="Onkologi">Onkologi</option>
                        <option value="Kardiologi">Kardiologi</option>
                    </select>
                    <div id="edit_spesialisasi-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Email --}}
                <div>
                    <label for="edit_email_dokter"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                    <input type="email" id="edit_email_dokter" name="edit_email_dokter"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="dokter@example.com" required>
                    <div id="edit_email_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- No HP --}}
                <div>
                    <label for="edit_no_hp" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">No
                        HP</label>
                    <input type="text" id="edit_no_hp" name="edit_no_hp"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="081234567890" required>
                    <div id="edit_no_hp-error" class="text-red-600 text-sm mt-1"></div>
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
