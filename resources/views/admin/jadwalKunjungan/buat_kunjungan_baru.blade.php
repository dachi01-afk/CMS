<section class="space-y-5">

    {{-- HEADER JADWAL --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-calendar-check text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Jadwal Dokter Hari Ini
                    <span class="text-sky-600 dark:text-sky-300">({{ $hariIni }})</span>
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Pilih jadwal praktik dokter untuk mendaftarkan kunjungan pasien pada hari ini.
                </p>
            </div>
        </div>

        <div class="flex flex-col items-end gap-1 text-xs text-slate-500 dark:text-slate-300">
            <span
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-sky-50 text-sky-700
                         dark:bg-sky-900/40 dark:text-sky-200 border border-sky-100 dark:border-sky-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Jadwal aktif hari ini
            </span>
            <span class="hidden md:inline text-[11px]">
                Klik <span class="font-semibold">Pilih</span> pada baris dokter untuk membuat kunjungan.
            </span>
        </div>
    </div>

    {{-- CARD TABEL JADWAL --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm">

        {{-- Toolbar atas: info + search --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">
            <p class="text-xs md:text-sm text-slate-500 dark:text-slate-300">
                Menampilkan jadwal dokter berdasarkan pengaturan master jadwal klinik.
            </p>

            {{-- Search --}}
            <div class="w-full md:w-auto">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <i class="fa-solid fa-magnifying-glass text-slate-400 text-xs"></i>
                    </span>
                    <input type="text" id="jadwal_searchInput"
                        class="block w-full md:w-80 pl-8 pr-3 py-2 text-sm text-slate-800 dark:text-slate-100
                           border border-slate-300 dark:border-slate-600 rounded-lg bg-slate-50 dark:bg-slate-700
                           focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari dokter, poli, spesialis, atau jam praktik...">
                </div>
                <p class="mt-1 text-[11px] text-slate-400 dark:text-slate-500">
                    Contoh: <span class="italic">Poli Umum, Sp.PD, 09:00, dr. Andi</span>.
                </p>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="relative overflow-x-auto rounded-b-2xl">
            <table id="jadwalDokterTable"
                class="min-w-full text-sm text-slate-700 dark:text-slate-100 border-t border-slate-100 dark:border-slate-700">
                <thead
                    class="text-xs font-semibold uppercase bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600 text-white tracking-wide">
                    <tr>
                        <th class="px-6 py-3 text-left">Dokter</th>
                        <th class="px-6 py-3 text-left">Poli</th>
                        <th class="px-6 py-3 text-left">Spesialis</th>
                        <th class="px-6 py-3 text-center">Waktu</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse ($jadwalHariIni as $jadwal)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-700/60 transition-colors">
                            <td class="px-6 py-3">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-slate-900 dark:text-slate-50">
                                        {{ $jadwal->dokter->nama_dokter }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <span class="font-medium text-slate-800 dark:text-slate-100">
                                    {{ $jadwal->poli->nama_poli }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-slate-700 dark:text-slate-200">
                                    {{ $jadwal->dokter->jenisSpesialis->nama_spesialis ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <span
                                    class="inline-flex items-center justify-center bg-sky-50 text-sky-700 dark:bg-sky-900/40 dark:text-sky-100
                                       px-3 py-1 rounded-full font-medium text-xs md:text-sm whitespace-nowrap border border-sky-100 dark:border-sky-800">
                                    {{ \Carbon\Carbon::parse($jadwal->jam_awal)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <button type="button"
                                    class="pilih-jadwal-btn inline-flex items-center justify-center gap-1
                                       text-xs md:text-sm font-semibold text-white rounded-full shadow-sm
                                       bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                                       focus:outline-none focus:ring-2 focus:ring-sky-400 px-3 py-1.5"
                                    data-dokter-id="{{ $jadwal->dokter->id }}"
                                    data-dokter-nama="{{ $jadwal->dokter->nama_dokter }}"
                                    data-poli-id="{{ $jadwal->poli->id }}"
                                    data-nama-poli="{{ $jadwal->poli->nama_poli }}"
                                    data-spesialis="{{ $jadwal->dokter->jenisSpesialis->nama_spesialis ?? '-' }}"
                                    data-tanggal-kunjungan="{{ $tanggalHariIni }}"
                                    data-jadwal-id="{{ $jadwal->id }}">
                                    <i class="fa-solid fa-plus text-[11px]"></i>
                                    <span>Pilih</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"
                                class="text-center text-slate-500 dark:text-slate-300 py-8 italic text-sm">
                                Tidak ada jadwal dokter untuk hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


</section>

{{-- MODAL TAMBAH KUNJUNGAN --}}
<div id="addKunjunganModal" tabindex="-1" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex items-start md:items-center justify-center w-full h-full p-4 md:p-6
           bg-slate-900/60 backdrop-blur-sm overflow-y-auto overflow-x-hidden">
    <div class="relative w-full max-w-xl">
        <div
            class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh] overflow-y-auto">

            {{-- HEADER --}}
            <div
                class="flex items-start justify-between gap-3 px-6 pt-5 pb-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-sky-500 to-teal-500 rounded-t-2xl">
                <div>
                    <h3 class="text-base md:text-lg font-semibold text-slate-50">
                        Tambah Kunjungan Pasien
                    </h3>
                    <p class="text-xs text-sky-50/90 mt-0.5">
                        Pilih dokter & poli kemudian hubungkan dengan data pasien yang sudah terdaftar.
                    </p>
                </div>
                <button type="button" id="closeModalBtn"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- FORM --}}
            <form action="{{ route('jadwal_kunjungan.create') }}" method="POST"
                class="px-6 pb-5 pt-4 space-y-4 bg-slate-50/60 dark:bg-slate-800">
                @csrf
                <input type="hidden" name="tanggal_kunjungan" id="tanggal_kunjungan">
                <input type="hidden" name="jadwal_id" id="jadwal_id">

                {{-- DOKTER & POLI --}}
                <div
                    class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/40 p-4 space-y-3">
                    <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                        Informasi Dokter & Poli
                    </h4>

                    <div class="grid grid-cols-1 gap-3">
                        {{-- Dokter --}}
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-200">Dokter</label>
                            <input type="text" id="dokter_nama" name="dokter_nama" readonly
                                class="bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-slate-50 text-sm rounded-lg w-full p-2.5">
                            <input type="hidden" id="dokter_id" name="dokter_id">
                        </div>

                        {{-- Poli --}}
                        <div>
                            <label
                                class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-200">Poli</label>
                            <input type="text" id="nama_poli" readonly
                                class="bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-slate-50 text-sm rounded-lg w-full p-2.5">
                            <input type="hidden" id="poli_id" name="poli_id">
                        </div>
                    </div>
                </div>

                {{-- CARI PASIEN --}}
                <div
                    class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/40 p-4 space-y-3">
                    <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                        Pilih Pasien
                    </h4>

                    <div class="space-y-3">
                        <div>
                            <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-200">Cari
                                Pasien</label>
                            <input type="text" id="search_pasien" name="search_pasien"
                                placeholder="Ketik Nama Pasien "
                                class="bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-slate-50 text-sm rounded-lg w-full p-2.5">
                            <div id="search_results"
                                class="mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg shadow max-h-40 overflow-y-auto hidden text-sm">
                            </div>
                        </div>

                        <div id="pasien_data"
                            class="hidden space-y-1 text-sm text-slate-700 dark:text-slate-200 rounded-lg bg-slate-50 dark:bg-slate-800/70 px-3 py-2 border border-slate-200 dark:border-slate-700">
                            <input type="hidden" name="pasien_id" id="pasien_id">
                            <p><span class="font-semibold">Nama:</span> <span id="nama_pasien"></span></p>
                            <p><span class="font-semibold">No EMR Pasien:</span> <span id="no_emr_pasien"></span></p>
                            <p><span class="font-semibold">Alamat:</span> <span id="alamat_pasien"></span></p>
                            <p><span class="font-semibold">Jenis Kelamin:</span> <span id="jk_pasien"></span></p>
                        </div>
                    </div>
                </div>

                {{-- KELUHAN --}}
                <div
                    class="rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900/40 p-4 space-y-3">
                    <h4 class="text-xs font-semibold tracking-wide text-slate-500 dark:text-slate-400 uppercase">
                        Keluhan Awal
                    </h4>
                    <div>
                        <label class="block mb-1 text-xs font-medium text-slate-700 dark:text-slate-200">Keluhan
                            Awal</label>
                        <textarea name="keluhan_awal" rows="3" required
                            class="bg-slate-50 dark:bg-slate-800 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-slate-50 text-sm rounded-lg w-full p-2.5"
                            placeholder="Contoh: Demam sejak 2 hari, batuk, pusing..."></textarea>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="flex justify-end gap-3 mt-4 border-t border-slate-200 dark:border-slate-700 pt-4">
                    <button type="button" id="closeModalBtn2"
                        class="px-4 py-2.5 text-sm font-medium text-slate-700 bg-slate-200 rounded-xl 
                               hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl 
                               bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                               focus:ring-2 focus:ring-sky-400">
                        Simpan Kunjungan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@vite(['resources/js/admin/jadwalKunjungan/jadwal_kunjungan.js'])
