<!-- Header -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 gap-2">
    <h2 class="text-2xl font-bold text-gray-700">Data Jenis Spesialis Dokter</h2>

    <!-- Modal toggle -->
    <button id="btnAddJenisSpesialisDokter"
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg shadow hover:bg-blue-700 focus:outline-none">
        + Tambah Data Jenis Spesialis Dokter
    </button>
</div>

<!-- Tabel -->
<div class="overflow-hidden rounded-lg shadow-md ">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 p-4 border-b border-gray-200">
        <div>
            <select id="jenis-spesialis-dokter_pageLength"
                class="border border-gray-300 text-sm rounded-lg focus:ring-sky-500 focus:border-sky-500 block w-24 p-1">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div class="relative">
            <input type="text" id="jenis-spesialis-dokter_searchInput"
                class="block w-60 p-2 pl-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 focus:ring-sky-500 focus:border-sky-500"
                placeholder="Cari data...">
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="jenisSpesialisDokter" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs uppercase bg-sky-500 text-white">
                <tr>
                    <th class="px-6 py-3">No</th>
                    <th class="px-6 py-3">Nama Spesialis</th>
                    <th class="px-6 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between px-4 py-3 border-t border-gray-200 gap-3">
        <div id="jenis-spesialis-dokter_customInfo" class="text-sm text-gray-700 dark:text-gray-300"></div>
        <ul id="jenis-spesialis-dokter_customPagination" class="inline-flex -space-x-px text-sm"></ul>
    </div>

    {{-- Modal Add Data Jenis Spesialis Dokter --}}
    <div id="addJenisSpesialisDokterModal" aria-hidden="true"
        class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto overflow-x-hidden">

        <div class="relative w-full max-w-lg">
            <div
                class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 dark:bg-slate-800 dark:border-slate-700">

                {{-- HEADER --}}
                <div
                    class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-sky-50 to-indigo-50 dark:from-slate-800 dark:to-slate-800/80 rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <div
                            class="h-10 w-10 rounded-xl bg-sky-500 flex items-center justify-center shadow-md text-white">
                            <i class="fa-solid fa-user-doctor text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base md:text-lg font-semibold text-slate-900 dark:text-slate-50">
                                Tambah Data Jenis Spesialis Dokter
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Lengkapi informasi jenis spesialis dokter untuk digunakan di seluruh modul klinik.
                            </p>
                        </div>
                    </div>

                    <button type="button" id="closeAddJenisSpesialisDokterModal"
                        class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-400 hover:text-slate-600 hover:bg-white/70 dark:hover:bg-slate-700 transition">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                {{-- FORM --}}
                <form id="formAddJenisSpesialisDokter" class="px-6 pb-5 space-y-5"
                    data-url="{{ route('create.data.jenis.spesialis.dokter') }}" method="POST">
                    @csrf

                    {{-- Nama Spesialis --}}
                    <div class="space-y-1.5">
                        <label for="nama_spesialis"
                            class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                            Nama Spesialis <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama_spesialis" id="nama_spesialis"
                            class="block w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 py-2.5 text-sm text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500
                               dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                            placeholder="Contoh: Spesialis Penyakit Dalam" required>
                        <p class="text-[11px] text-slate-500">
                            Gunakan nama spesialis yang jelas, misalnya:
                            <span class="italic">Spesialis Anak, Spesialis Gigi, Spesialis Saraf</span>.
                        </p>
                        <div id="nama_spesialis-error" class="text-red-600 text-xs mt-1"></div>
                    </div>

                    {{-- FOOTER BUTTONS --}}
                    <div
                        class="flex items-center justify-end gap-3 pt-4 mt-2 border-t border-slate-100 dark:border-slate-700">
                        <button type="button" id="closeAddJenisSpesialisDokterModal_footer"
                            class="px-4 py-2.5 text-sm font-medium rounded-xl border border-slate-200 bg-white text-slate-700
                               hover:bg-slate-50 hover:border-slate-300 transition
                               dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-600">
                            Batal
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-xl
                               bg-gradient-to-r from-sky-500 to-indigo-600 text-white shadow-md
                               hover:from-sky-600 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-sky-400">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            <span>Simpan Data</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Update Jenis Spesialis --}}
    <div id="modalUpdateJenisSpesialis" aria-hidden="true"
        class="hidden fixed inset-0 z-50 flex items-center justify-center w-full h-full p-4 bg-slate-900/60 backdrop-blur-sm overflow-y-auto">

        <div class="relative w-full max-w-lg">
            <div
                class="relative bg-white rounded-2xl shadow-2xl border border-slate-100 dark:bg-slate-800 dark:border-slate-700">

                {{-- HEADER --}}
                <div
                    class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-amber-50 to-yellow-50 dark:from-slate-800 dark:to-slate-800/80 rounded-t-2xl">
                    <div class="flex items-center gap-3">
                        <div
                            class="h-10 w-10 rounded-xl bg-amber-500 flex items-center justify-center shadow-md text-white">
                            <i class="fa-solid fa-pen-to-square text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-base md:text-lg font-semibold text-slate-900 dark:text-slate-50">
                                Edit Jenis Spesialis Dokter
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Perbarui informasi jenis spesialis dokter dengan benar.
                            </p>
                        </div>
                    </div>

                    <button type="button" id="buttonCloseModalUpdateJenisSpesialis_Header"
                        class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-400 hover:text-slate-600 hover:bg-white/70 dark:hover:bg-slate-700 transition">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                {{-- FORM --}}
                <form id="formUpdateModalJenisSpesialis" method="POST"
                    data-url="{{ route('update.data.jenis.spesialis.dokter') }}" class="px-6 pb-4 space-y-5">
                    @csrf
                    <input type="hidden" id="id_update" name="id">

                    {{-- Nama Spesialis --}}
                    <div class="space-y-1.5">
                        <label for="nama_spesialis_update"
                            class="block text-sm font-medium text-slate-800 dark:text-slate-100">
                            Nama Spesialis <span class="text-red-500">*</span>
                        </label>

                        <input type="text" name="nama_spesialis" id="nama_spesialis_update"
                            class="block w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm text-slate-900
                               focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500
                               dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                            placeholder="Contoh: Spesialis Penyakit Dalam" required>

                        <div id="nama_spesialis_update-error" class="text-red-600 text-xs mt-1"></div>
                    </div>

                    {{-- FOOTER BUTTONS --}}
                    <div
                        class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                        <button type="button" id="buttonCloseModalUpdateJenisSpesialis"
                            class="px-4 py-2.5 text-sm font-medium rounded-xl border border-slate-200 bg-white text-slate-700
                               hover:bg-slate-50 hover:border-slate-300 transition
                               dark:bg-slate-700 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-600">
                            Batal
                        </button>

                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-xl
                               bg-gradient-to-r from-amber-500 to-yellow-600 text-white shadow-md
                               hover:from-amber-600 hover:to-yellow-700 focus:outline-none focus:ring-2 focus:ring-amber-400">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            <span>Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>




@vite(['resources/js/admin/jenisSpesialisDokter/data-jenis-spesialis-dokter.js'])
