<section class="space-y-4">

    {{-- HEADER JADWAL YANG AKAN DATANG --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-3.5 shadow-sm">
        <div class="flex items-start gap-3">
            <div
                class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-calendar-days text-lg"></i>
            </div>
            <div class="space-y-0.5">
                <h2 class="text-lg md:text-xl font-bold text-slate-800 dark:text-slate-50">
                    Jadwal Dokter Yang Akan Datang
                </h2>
                <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400">
                    Pilih jadwal praktik dokter pada tanggal berikutnya untuk membuat kunjungan pasien baru.
                </p>
            </div>
        </div>

        <div class="flex flex-col items-end gap-0.5 text-[11px] md:text-xs text-slate-500 dark:text-slate-300">
            <span
                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-sky-50 text-sky-700
                       dark:bg-sky-900/40 dark:text-sky-200 border border-sky-100 dark:border-sky-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                {{ $jadwalYangAkanDatang->total() }} jadwal ditemukan
            </span>
            <span class="hidden md:inline">
                Klik <span class="font-semibold">Buat Kunjungan</span> pada baris dokter.
            </span>
        </div>
    </div>

    {{-- CARD TABEL JADWAL YANG AKAN DATANG --}}
    <div
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">

        {{-- Toolbar atas: info + search --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-2.5 border-b border-slate-200 dark:border-slate-700">
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-300">
                Menampilkan jadwal praktik dokter pada tanggal mendatang berdasarkan pengaturan master jadwal klinik.
            </p>

            <div class="w-full md:w-auto">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                    </span>
                    <input type="text" id="jadwal_kyad_searchInput"
                        class="block w-full md:w-72 pl-8 pr-3 py-1.5 text-sm text-slate-800 dark:text-slate-100
                               border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700
                               focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Filter dokter, poli, spesialis, tanggal...">
                </div>
                <p class="mt-0.5 text-[11px] text-slate-400 dark:text-slate-500">
                    Contoh: <span class="italic">Senin, Poli Anak, Sp.A, 10:00</span>.
                </p>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="relative overflow-x-auto">
            <table id="jadwalKyadTable"
                class="min-w-full text-sm text-slate-700 dark:text-slate-100 border-t border-slate-100 dark:border-slate-700">
                <thead
                    class="text-xs font-semibold uppercase bg-gradient-to-r from-sky-500 to-teal-500 text-white tracking-wide">
                    <tr>
                        <th class="px-5 py-2.5 text-left">Dokter</th>
                        <th class="px-5 py-2.5 text-left">Poli</th>
                        <th class="px-5 py-2.5 text-left">Spesialis</th>
                        <th class="px-5 py-2.5 text-center">Hari &amp; Tanggal</th>
                        <th class="px-5 py-2.5 text-center">Waktu</th>
                        <th class="px-5 py-2.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($jadwalYangAkanDatang as $jadwal)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/60 transition-colors">

                            {{-- Dokter --}}
                            <td class="px-5 py-2.5">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-8 w-8 flex items-center justify-center rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-200">
                                        <i class="fa-solid fa-user-doctor text-[13px]"></i>
                                    </div>
                                    <div class="flex flex-col leading-tight">
                                        <span class="font-semibold text-slate-900 dark:text-slate-50 text-sm">
                                            {{ $jadwal->dokter->nama_dokter }}
                                        </span>
                                        <span class="text-[11px] text-slate-400 dark:text-slate-500">
                                            ID: {{ $jadwal->dokter->id }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- Poli --}}
                            <td class="px-5 py-2.5">
                                <span class="font-medium text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                    {{ $jadwal->poli->nama_poli }}
                                </span>
                            </td>

                            {{-- Spesialis --}}
                            <td class="px-5 py-2.5">
                                <span class="text-slate-700 dark:text-slate-200">
                                    {{ $jadwal->dokter->jenisSpesialis->nama_spesialis ?? '-' }}
                                </span>
                            </td>

                            {{-- Hari & Tanggal --}}
                            <td class="px-5 py-2.5 text-center">
                                <div class="flex flex-col items-center leading-tight">
                                    <span class="font-semibold text-slate-900 dark:text-slate-50 text-sm">
                                        {{ $jadwal->hari }}
                                    </span>
                                    <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                        {{ \Carbon\Carbon::parse($jadwal->tanggal_berikutnya)->translatedFormat('d F Y') }}
                                    </span>
                                </div>
                            </td>

                            {{-- Waktu --}}
                            <td class="px-5 py-2.5 text-center">
                                <span
                                    class="inline-flex items-center justify-center bg-sky-50 text-sky-700 dark:bg-sky-900/40 dark:text-sky-100
                                           px-3 py-0.5 rounded-full font-medium text-xs whitespace-nowrap border border-sky-100 dark:border-sky-800">
                                    <i class="fa-regular fa-clock text-[11px] mr-1"></i>
                                    {{ \Carbon\Carbon::parse($jadwal->jam_awal)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-2.5 text-center">
                                <button type="button"
                                    class="pilih-kyad-btn inline-flex items-center justify-center gap-1.5
               text-xs md:text-sm font-semibold text-white rounded-full shadow-sm
               bg-gradient-to-r from-sky-500 to-teal-500 hover:from-sky-600 hover:to-indigo-700
               focus:outline-none focus:ring-2 focus:ring-sky-400 px-3 py-1.5"
                                    {{-- DATA UNTUK JS --}} data-dokter-id="{{ $jadwal->dokter->id }}"
                                    data-dokter-nama="{{ $jadwal->dokter->nama_dokter }}"
                                    data-spesialis="{{ $jadwal->dokter->jenisSpesialis->nama_spesialis ?? '-' }}"
                                    data-poli-id="{{ $jadwal->poli->id }}"
                                    data-nama-poli="{{ $jadwal->poli->nama_poli }}"
                                    data-tanggal="{{ \Carbon\Carbon::parse($jadwal->tanggal_berikutnya)->toDateString() }}"
                                    data-jadwal-id="{{ $jadwal->id }}">
                                    <i class="fa-solid fa-plus text-[11px]"></i>
                                    <span>Buat Kunjungan</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"
                                class="text-center text-slate-500 dark:text-slate-300 py-6 italic text-sm">
                                Tidak ada jadwal dokter yang akan datang.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer: info + pagination --}}
        @if ($jadwalYangAkanDatang->count())
            <div
                class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 px-4 md:px-6 py-2.5 border-t border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60">

                <div class="text-xs md:text-sm text-slate-500 dark:text-slate-300">
                    Menampilkan
                    <span class="font-semibold text-slate-700 dark:text-slate-100">
                        {{ $jadwalYangAkanDatang->firstItem() }}
                    </span>
                    –
                    <span class="font-semibold text-slate-700 dark:text-slate-100">
                        {{ $jadwalYangAkanDatang->lastItem() }}
                    </span>
                    dari
                    <span class="font-semibold text-slate-700 dark:text-slate-100">
                        {{ $jadwalYangAkanDatang->total() }}
                    </span>
                    jadwal.
                </div>

                <div class="text-xs">
                    {{ $jadwalYangAkanDatang->appends(['tab' => 'tkyad'])->links() }}
                </div>
            </div>
        @endif
    </div>

</section>

{{-- MODAL: TAMBAH KUNJUNGAN (KYAD) --}}
<div id="modalCreateKYAD" data-modal-backdrop="static"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center w-full h-full p-4 md:p-6
           bg-slate-900/60 backdrop-blur-sm overflow-y-auto overflow-x-hidden">
    <div class="relative w-full max-w-3xl">
        <div
            class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh] overflow-y-auto">

            {{-- Header --}}
            <div
                class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-sky-500 to-teal-500 rounded-t-2xl">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-xl bg-sky-500 flex items-center justify-center shadow-md text-white">
                        <i class="fa-solid fa-user-plus text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-base md:text-lg font-semibold text-slate-50">
                            Tambah Kunjungan Pasien
                        </h3>
                        <p class="text-xs text-sky-50/90 mt-0.5">
                            Pastikan jadwal dokter & data pasien sudah sesuai sebelum menyimpan kunjungan.
                        </p>
                    </div>
                </div>

                <button type="button" id="buttonCloseModalCreateKYAD"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- Form --}}
            <form action="{{ route('jadwal_kunjungan.create') }}" method="POST"
                class="px-6 pb-5 pt-4 flex flex-col gap-4 bg-slate-50/60 dark:bg-slate-800">
                @csrf

                <input type="hidden" id="tanggal-kunjungan-kyad" name="tanggal_kunjungan">
                <input type="hidden" id="jadwal_id-kyad" name="jadwal_id">
                <input type="hidden" id="dokter_id-kyad" name="dokter_id">
                <input type="hidden" id="poli_id-kyad" name="poli_id">
                <input type="hidden" id="pasien_id-kyad" name="pasien_id">

                {{-- Info strip --}}
                <div
                    class="flex items-center gap-2 text-xs rounded-xl px-3 py-2 bg-emerald-50 text-emerald-700 border border-emerald-100 dark:bg-emerald-900/40 dark:text-emerald-100 dark:border-emerald-800">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Kunjungan akan terhubung ke jadwal dokter yang dipilih pada tabel jadwal di atas.</span>
                </div>

                {{-- Section: info jadwal --}}
                <div
                    class="mt-1 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/40 px-4 py-3 space-y-3">
                    <p class="text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">
                        Jadwal Praktik
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm text-slate-700 dark:text-slate-100">
                        <div>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">Dokter</p>
                            <p id="dokter_nama-kyad" class="font-semibold text-slate-900 dark:text-slate-50"></p>
                        </div>
                        <div>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">Poli</p>
                            <p id="nama_poli-kyad" class="font-medium text-slate-900 dark:text-slate-50"></p>
                        </div>
                        <div>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">Tanggal Kunjungan</p>
                            <p id="tanggal_display-kyad" class="font-medium text-slate-900 dark:text-slate-50"></p>
                        </div>
                        <div>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">Spesialis</p>
                            <p id="spesialis_display-kyad" class="font-medium text-slate-900 dark:text-slate-50"></p>
                        </div>
                    </div>
                </div>

                {{-- Section: Data pasien --}}
                <div
                    class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/40 px-4 py-4 space-y-4">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <div
                                class="h-7 w-7 flex items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-200">
                                <i class="fa-solid fa-user text-xs"></i>
                            </div>
                            <p
                                class="text-xs font-semibold tracking-wide uppercase text-slate-500 dark:text-slate-400">
                                Data Pasien
                            </p>
                        </div>
                    </div>

                    {{-- Search Pasien --}}
                    <div>
                        <label class="block mb-1.5 text-sm font-medium text-slate-800 dark:text-slate-100">
                            Cari Pasien
                        </label>
                        <input type="text" id="search_pasien-kyad" name="search_pasien"
                            placeholder="Ketik nama atau NIK pasien..."
                            class="bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl w-full px-3 py-2.5 focus:ring-sky-500 focus:border-sky-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50">
                        <div id="search_results-kyad"
                            class="mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow max-h-40 overflow-y-auto hidden text-sm">
                        </div>
                    </div>

                    {{-- Selected pasien --}}
                    <div id="pasien_data-kyad"
                        class="hidden rounded-xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 px-3 py-3 text-xs text-slate-700 dark:text-slate-100 space-y-1.5">
                        <p class="font-semibold text-sm">
                            <span class="text-slate-400">Nama:</span>
                            <span id="nama_pasien-kyad" class="ml-1"></span>
                        </p>
                        <p>
                            <span class="text-slate-400">Alamat:</span>
                            <span id="alamat_pasien-kyad" class="ml-1"></span>
                        </p>
                        <p>
                            <span class="text-slate-400">Jenis Kelamin:</span>
                            <span id="jk_pasien-kyad" class="ml-1"></span>
                        </p>
                    </div>
                </div>

                {{-- Section: Keluhan awal --}}
                <div
                    class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/40 px-4 py-4">
                    <label class="block mb-1.5 text-sm font-medium text-slate-800 dark:text-slate-100">
                        Keluhan Awal
                    </label>
                    <textarea name="keluhan_awal" rows="5" required
                        class="w-full bg-slate-50 border border-slate-300 text-slate-900 text-sm rounded-xl px-3 py-2.5 focus:ring-sky-500 focus:border-sky-500 dark:bg-slate-700 dark:border-slate-600 dark:text-slate-50"
                        placeholder="Contoh: Demam sejak 2 hari, batuk kering, sakit kepala."></textarea>
                </div>

                {{-- Footer --}}
                <div class="flex justify-end gap-3 mt-4 border-t border-slate-200 dark:border-slate-700 pt-4">
                    <button type="button" id="buttonCloseModalCreateKYAD_footer"
                        class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-slate-200 rounded-xl hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2.5 text-sm font-semibold text-white rounded-xl bg-gradient-to-r from-sky-500 to-teal-500 hover:from-sky-600 hover:to-indigo-700 focus:ring-2 focus:ring-sky-400">
                        Simpan Kunjungan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@vite(['resources/js/admin/jadwalKunjungan/jadwal-dokter-yang-akan-datang.js'])
