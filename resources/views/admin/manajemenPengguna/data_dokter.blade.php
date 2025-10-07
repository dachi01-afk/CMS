<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Data Dokter</h2>
    <button id="btnAddDokter"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none">
        + Tambah Data
    </button>
</div>



{{-- Modal Add Dokter --}}
<div id="addDokterModal" aria-hidden="true"
    class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4">
    <div class="relative w-full max-w-2xl max-h-full">
        <div class="relative bg-white rounded-lg shadow-xl dark:bg-gray-700">

            {{-- Header --}}
            <div class="flex items-center justify-between p-5 border-b border-gray-200 dark:border-gray-600">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Tambah Data Dokter</h3>
            </div>

            {{-- Form --}}
            <form id="formAddDokter" class="p-5 flex flex-col gap-4"
                data-url="{{ route('manajemen_pengguna.add_dokter') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Username --}}
                <div>
                    <label for="username" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Username
                    </label>
                    <input type="text" name="username" id="username"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="Masukkan username" required>
                    <div id="username-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Password
                    </label>
                    <input type="password" name="password" id="password"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="Minimal 8 karakter" required>
                    <div id="password-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Konfirmasi Password --}}
                <div>
                    <label for="password_confirmation"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Konfirmasi Password
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="Ulangi password" required>
                    <div id="password_confirmation-error" class="text-red-600 text-sm mt-1"></div>
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

                {{-- Jenis Spesialis --}}
                <div>
                    <label for="jenis_spesialis_id"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Jenis Spesialis
                    </label>
                    <select id="jenis_spesialis_id" name="jenis_spesialis_id" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white">
                        <option value="" disabled selected>Pilih Spesialis</option>
                        @foreach ($spesialis as $item)
                            <option value="{{ $item->id }}">{{ $item->nama_spesialis }}</option>
                        @endforeach
                    </select>
                    <div id="jenis_spesialis_id-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Email --}}
                <div>
                    <label for="email"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                    <input type="email" name="email" id="email"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="dokter@example.com" required>
                    <div id="email-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- No HP --}}
                <div>
                    <label for="no_hp" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        No HP
                    </label>
                    <input type="text" name="no_hp" id="no_hp"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="0812xxxxxxxx">
                    <div id="no_hp-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Foto --}}
                <div>
                    <label for="foto" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Foto Dokter (opsional)
                    </label>
                    <input type="file" name="foto" id="foto"
                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400">
                    <div id="foto-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label for="deskripsi_dokter" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Deskripsi
                    </label>
                    <textarea name="deskripsi_dokter" id="deskripsi_dokter" rows="3"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="Masukkan deskripsi dokter..."></textarea>
                    <div id="deskripsi_dokter-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Pengalaman --}}
                <div>
                    <label for="pengalaman" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Pengalaman
                    </label>
                    <input type="text" name="pengalaman" id="pengalaman"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="Contoh: 5 tahun di bidang kardiologi">
                    <div id="pengalaman-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Tombol --}}
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
                <input type="hidden" id="edit_dokter_id" name="edit_dokter_id">

                {{-- Username --}}
                <div>
                    <label for="edit_username" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
                        Username
                    </label>
                    <input type="text" id="edit_username" name="edit_username"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white"
                        placeholder="Username Dokter" required>
                    <div id="edit_username-error" class="text-red-600 text-sm mt-1"></div>
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

                {{-- Spesialisasi --}}
                <div>
                    <label for="edit_spesialisasi"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Spesialisasi</label>
                    <select id="edit_spesialisasi" name="edit_spesialisasi" required
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg 
                        focus:ring-blue-500 focus:border-blue-500 w-full p-2.5 dark:bg-gray-600 
                        dark:border-gray-500 dark:text-white">
                        <option value="" disabled selected>Pilih Spesialisasi</option>
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
                        placeholder="081234567890">
                    <div id="edit_no_hp-error" class="text-red-600 text-sm mt-1"></div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 mt-5 border-t border-gray-200 pt-4 dark:border-gray-600">
                    <button type="button" id="closeEditDokterModal"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg 
                        hover:bg-gray-300 dark:bg-gray-600 dark:text-white dark:hover:bg-gray-500">
                        Close
                    </button>
                    <button type="submit" id="updateDokterButton"
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






@vite(['resources/js/admin/manajemenPengguna/data_dokter.js'])
