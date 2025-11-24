<section class="space-y-5">

    {{-- HEADER + CTA --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white border border-slate-200 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-emerald-500 text-white shadow-md">
                <i class="fa-solid fa-pills text-lg"></i>
            </div>
            <div>
                <p class="text-[11px] font-semibold tracking-[0.18em] text-slate-400 uppercase">
                    Pengaturan Klinik
                </p>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800">
                    Daftar Obat
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    Kelola stok, dosis, dan harga obat yang tersedia di klinik untuk mendukung pelayanan farmasi.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 md:gap-3">
            <button type="button"
                class="hidden md:inline-flex items-center gap-2 px-3 py-2 text-xs md:text-sm rounded-xl border border-slate-200 text-slate-600 bg-white hover:bg-slate-50">
                <i class="fa-regular fa-circle-question text-sm"></i>
                <span>Panduan Manajemen Obat</span>
            </button>

            <button id="btnAddObat" type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-xl shadow-md
                       bg-gradient-to-r from-sky-500 to-emerald-600 hover:from-sky-600 hover:to-emerald-700
                       focus:outline-none focus:ring-2 focus:ring-sky-400">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Tambah Obat</span>
            </button>
        </div>
    </div>

    {{-- CARD TABEL --}}
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm">

        {{-- Toolbar --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-b border-slate-200">

            {{-- Page length --}}
            <div class="flex items-center gap-2 text-sm">
                <span class="text-slate-600 hidden sm:inline">Tampil</span>
                <select id="obat_pageLength"
                    class="border border-slate-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500
                           bg-white text-slate-800 px-2 py-1 w-28">
                    <option value="10">10 baris</option>
                    <option value="25">25 baris</option>
                    <option value="50">50 baris</option>
                    <option value="100">100 baris</option>
                </select>
                <span class="text-slate-600 hidden sm:inline">per halaman</span>
            </div>

            {{-- Search --}}
            <div class="w-full md:w-auto">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                    </span>
                    <input type="text" id="obat_searchInput"
                        class="block w-full md:w-72 pl-9 pr-3 py-2 text-sm text-slate-800
                               border border-slate-300 rounded-lg bg-slate-50
                               focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari nama obat, dosis, atau harga...">
                </div>
                <p class="mt-1 text-[11px] text-slate-400">
                    Contoh: <span class="italic">Paracetamol, 500 mg, 10.000</span>.
                </p>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table id="obatTable" class="w-full text-sm text-left text-slate-700 border-t border-slate-100">
                <thead
                    class="text-xs font-semibold uppercase bg-gradient-to-r from-sky-500 to-emerald-500 text-white tracking-wide">
                    <tr>
                        <th class="px-6 py-3">No</th>
                        <th class="px-6 py-3">Nama Obat</th>
                        <th class="px-6 py-3">Jumlah</th>
                        <th class="px-6 py-3">Dosis</th>
                        <th class="px-6 py-3">Harga</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100"></tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between px-4 md:px-6 py-3 border-t border-slate-200 bg-slate-50/70 gap-3 rounded-b-2xl">
            <div id="obat_customInfo" class="text-xs md:text-sm text-slate-600"></div>
            <ul id="obat_customPagination"
                class="inline-flex items-center gap-0 text-sm isolate rounded-lg border border-slate-200 overflow-hidden">
            </ul>
        </div>
    </div>
</section>

{{-- Modal Add Obat --}}
<div id="addObatModal" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center w-full h-full p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="relative w-full max-w-xl">
        <div
            class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 flex flex-col max-h-[90vh] overflow-y-auto">

            {{-- Header --}}
            <div
                class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 border-b border-slate-100 bg-gradient-to-r from-sky-500 to-emerald-500 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div
                        class="h-10 w-10 rounded-xl bg-emerald-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-circle-plus text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-slate-50">
                            Tambah Data Obat
                        </h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">
                            Lengkapi informasi obat untuk memastikan pencatatan stok dan harga yang akurat.
                        </p>
                    </div>
                </div>

                <button type="button" id="closeAddObatModal_header"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Form --}}
            <form id="formAddObat" class="px-6 pb-5 pt-4 flex flex-col gap-4 bg-slate-50/60"
                data-url="{{ route('pengaturan_klinik.add_obat') }}" method="POST">
                @csrf

                {{-- Info strip --}}
                <div
                    class="flex items-center gap-2 text-xs rounded-xl px-3 py-2
                           bg-amber-50 text-amber-700 border border-amber-100">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Pastikan dosis & satuan sesuai dengan aturan farmasi klinik.</span>
                </div>

                {{-- Nama Obat --}}
                <div>
                    <label for="nama_obat" class="block mb-1 text-sm font-medium text-slate-800">Nama Obat</label>
                    <input type="text" name="nama_obat" id="nama_obat"
                        class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg w-full p-2.5
                               focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Contoh: Paracetamol" required>
                    <div id="nama_obat-error" class="text-red-600 text-xs mt-1"></div>
                </div>

                {{-- Jumlah & Dosis --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="jumlah" class="block mb-1 text-sm font-medium text-slate-800">Jumlah</label>
                        <input type="number" name="jumlah" id="jumlah"
                            class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg w-full p-2.5
                                   focus:ring-sky-500 focus:border-sky-500"
                            placeholder="Jumlah stok" required>
                        <div id="jumlah-error" class="text-red-600 text-xs mt-1"></div>
                    </div>

                    <div>
                        <label for="dosis" class="block mb-1 text-sm font-medium text-slate-800">Dosis
                            (mg/ml)</label>
                        <input type="number" step="0.01" name="dosis" id="dosis"
                            class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg w-full p-2.5
                                   focus:ring-sky-500 focus:border-sky-500"
                            placeholder="Contoh: 500" required>
                        <div id="dosis-error" class="text-red-600 text-xs mt-1"></div>
                    </div>
                </div>

                {{-- Harga --}}
                <div>
                    <label for="total_harga" class="block mb-1 text-sm font-medium text-slate-800">Harga</label>
                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-sm">Rp</span>
                        <input type="text" name="total_harga" id="total_harga"
                            class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg w-full p-2.5
                                   focus:ring-sky-500 focus:border-sky-500 pl-9"
                            placeholder="Masukkan harga obat" required>
                    </div>
                    <div id="total_harga-error" class="text-red-600 text-xs mt-1"></div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 mt-6 border-top border-slate-200 pt-4">
                    <button type="button" id="closeAddObatModal"
                        class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-slate-200 rounded-xl 
                               hover:bg-slate-300">
                        Batal
                    </button>
                    <button type="submit" id="saveObatButton"
                        class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl 
                               bg-gradient-to-r from-sky-500 to-emerald-600 hover:from-sky-600 hover:to-emerald-700
                               focus:ring-2 focus:ring-sky-400">
                        Simpan Obat
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit Obat --}}
<div id="editObatModal" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center w-full h-full p-4 md:p-6 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">
    <div class="relative w-full max-w-xl">
        <div
            class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 flex flex-col max-h-[90vh] overflow-y-auto">

            {{-- Header --}}
            <div
                class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 border-b border-slate-100 bg-gradient-to-r from-emerald-500 to-sky-500 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-sky-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-pen-to-square text-lg"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-slate-50">
                            Edit Data Obat
                        </h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">
                            Sesuaikan stok, dosis, atau harga tanpa mengubah identitas obat.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Form --}}
            <form id="formEditObat" class="px-6 pb-5 pt-4 flex flex-col gap-4 bg-slate-50/60"
                data-url="{{ route('pengaturan_klinik.update_obat', ['id' => 0]) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="obat_id" id="obat_id_edit">

                {{-- Nama Obat (readonly) --}}
                <div>
                    <label for="nama_obat_edit" class="block mb-1 text-sm font-medium text-slate-800">Nama
                        Obat</label>
                    <input type="text" name="nama_obat_edit" id="nama_obat_edit" readonly
                        class="bg-slate-100 border border-slate-300 text-slate-900 text-sm rounded-lg w-full p-2.5
                               focus:ring-sky-500 focus:border-sky-500 cursor-not-allowed"
                        placeholder="Nama Obat" required>
                    <div id="nama_obat_edit-error" class="text-red-600 text-xs mt-1"></div>
                </div>

                {{-- Jumlah & Dosis --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="jumlah_edit" class="block mb-1 text-sm font-medium text-slate-800">Jumlah</label>
                        <input type="number" name="jumlah_edit" id="jumlah_edit"
                            class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg w-full p-2.5
                                   focus:ring-sky-500 focus:border-sky-500"
                            placeholder="Jumlah stok" required>
                        <div id="jumlah_edit-error" class="text-red-600 text-xs mt-1"></div>
                    </div>

                    <div>
                        <label for="dosis_edit" class="block mb-1 text-sm font-medium text-slate-800">Dosis
                            (mg/ml)</label>
                        <input type="number" step="0.01" name="dosis_edit" id="dosis_edit"
                            class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg w-full p-2.5
                                   focus:ring-sky-500 focus:border-sky-500"
                            placeholder="Contoh: 500" required>
                        <div id="dosis_edit-error" class="text-red-600 text-xs mt-1"></div>
                    </div>
                </div>

                {{-- Harga --}}
                <div>
                    <label for="total_harga_edit" class="block mb-1 text-sm font-medium text-slate-800">Harga</label>
                    <div class="relative mt-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-sm">Rp</span>
                        <input type="text" name="total_harga_edit" id="total_harga_edit"
                            class="bg-white border border-slate-300 text-slate-900 text-sm rounded-lg w-full p-2.5
                                   focus:ring-sky-500 focus:border-sky-500 pl-9"
                            placeholder="Masukkan harga obat" required>
                    </div>
                    <div id="total_harga_edit-error" class="text-red-600 text-xs mt-1"></div>
                </div>

                {{-- Buttons --}}
                <div class="flex justify-end gap-3 mt-6 border-t border-slate-200 pt-4">
                    <button type="button" id="closeEditObatModal"
                        class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-slate-200 rounded-xl 
                               hover:bg-slate-300">
                        Batal
                    </button>
                    <button type="submit" id="updateObatButton"
                        class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl 
                               bg-gradient-to-r from-emerald-500 to-sky-600 hover:from-emerald-600 hover:to-sky-700
                               focus:ring-2 focus:ring-emerald-400">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@vite(['resources/js/admin/pengaturanKlinik/daftar_obat.js'])
