<section class="space-y-5">

    {{-- HEADER PROSES KUNJUNGAN --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
               bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl px-4 md:px-6 py-4 shadow-sm">
        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl
                       bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-stethoscope text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Proses Kunjungan Hari Ini
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Daftar pasien yang sedang menunggu proses kunjungan dan siap ditangani perawat / dokter.
                </p>
            </div>
        </div>

        <div class="flex flex-col items-end gap-1 text-xs text-slate-500 dark:text-slate-300">
            <span
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-sky-50 text-sky-700
                       dark:bg-sky-900/40 dark:text-sky-200 border border-sky-100 dark:border-sky-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Kunjungan berstatus pending / hari ini
            </span>
            <span class="hidden md:inline text-[11px]">
                Gunakan tombol <span class="font-semibold">Aksi</span> pada baris pasien
                untuk mulai pemeriksaan atau mengubah status kunjungan.
            </span>
        </div>
    </div>

    {{-- CARD PROSES KUNJUNGAN + TABEL --}}
    <div id="prosesKunjungan"
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl shadow-sm">

        {{-- STRIP INFO ATAS --}}
        <div
            class="flex items-center justify-between gap-3 px-4 md:px-6 py-2.5
                   border-b border-slate-200 dark:border-slate-700
                   bg-gradient-to-r from-sky-50 via-teal-50/60 to-white
                   dark:from-slate-800 dark:via-slate-800 dark:to-slate-800 text-[11px] md:text-xs
                   text-slate-500 dark:text-slate-300">
            <div class="flex items-center gap-2">
                <i class="fa-regular fa-clock text-sky-500 text-xs"></i>
                <span>Hanya menampilkan kunjungan dengan status pending pada tanggal hari ini.</span>
            </div>
        </div>

        {{-- WRAPPER TABEL --}}
        <div class="relative overflow-x-auto overflow-y-visible rounded-b-2xl">
            <table class="min-w-full text-sm text-slate-700 dark:text-slate-100 align-middle" id="tabelProses">
                <thead
                    class="text-xs font-semibold uppercase bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600
                           text-white tracking-wide">
                    <tr>
                        <th class="px-6 py-3 text-left">No Antrian</th>
                        <th class="px-6 py-3 text-center">Nama Pasien</th>
                        <th class="px-6 py-3 text-center">Dokter</th>
                        <th class="px-6 py-3 text-center">Poli</th>
                        <th class="px-6 py-3 text-center">Keluhan</th>
                        <th class="px-6 py-3 text-center">Status Kunjungan</th>
                        <th class="px-6 py-3 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody id="waitingBody"
                    class="divide-y divide-slate-100 dark:divide-slate-700 bg-white dark:bg-slate-800">
                    <tr>
                        <td colspan="7" class="text-center text-slate-500 dark:text-slate-300 py-8 italic text-sm">
                            Memuat data...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</section>

{{-- CSS KHUSUS DROPDOWN AKSI (BIAR MELAYANG SEPERTI FLOWBITE) --}}
<style>
    .aksi-dropdown-wrapper {
        position: relative;
        display: inline-flex;
        justify-content: flex-end;
        width: 100%;
    }

    .aksi-dropdown-menu {
        position: absolute;
        right: 0;
        top: 100%;
        margin-top: 0.5rem;
        /* mt-2 */
        z-index: 50;
    }
</style>

{{-- MODAL: Edit Kunjungan --}}
<div id="editKunjunganModal" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 md:p-6
               bg-slate-900/60 backdrop-blur-sm overflow-y-auto overflow-x-hidden">
    <div class="relative w-full max-w-3xl max-h-[90vh]">
        <div
            class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl
                       border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh] overflow-y-auto">

            {{-- HEADER MODAL --}}
            <div
                class="flex items-center justify-between gap-3 px-5 md:px-6 pt-4 pb-3
                           border-b border-slate-100 dark:border-slate-700
                           bg-gradient-to-r from-sky-500 to-teal-500 rounded-t-2xl">
                <div>
                    <h3 class="text-base md:text-lg font-semibold text-slate-50">
                        Edit Kunjungan Pasien
                    </h3>
                    <p class="text-xs text-sky-50/90 mt-0.5">
                        Ubah dokter, poli, dan keluhan awal pasien untuk kunjungan ini.
                    </p>
                </div>
                <button type="button"
                    class="close-edit-kunjungan inline-flex items-center justify-center h-8 w-8
                               rounded-full text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- FORM --}}
            <form id="editKunjunganForm" method="POST" action=""
                class="px-5 md:px-6 py-5 space-y-4 bg-slate-50/60 dark:bg-slate-800">
                @csrf

                {{-- Info dasar (No Antrian & Status) --}}
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-slate-700 dark:text-slate-200">
                            No Antrian
                        </label>
                        <input type="text" id="edit_no_antrian" readonly
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-600
                                       text-slate-900 dark:text-slate-50 rounded-lg px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="block mb-1 text-xs font-semibold text-slate-700 dark:text-slate-200">
                            Status Kunjungan
                        </label>
                        <input type="text" id="edit_status" readonly
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-600
                                       text-slate-900 dark:text-slate-50 rounded-lg px-3 py-2.5 text-sm">
                    </div>
                </div>

                {{-- Nama Pasien --}}
                <div>
                    <label class="block mb-1 text-xs font-semibold text-slate-700 dark:text-slate-200">
                        Nama Pasien
                    </label>
                    <input type="text" id="edit_nama_pasien" readonly
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-600
                                   text-slate-900 dark:text-slate-50 rounded-lg px-3 py-2.5 text-sm">
                </div>

                {{-- Dokter & Poli --}}
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    {{-- Dokter --}}
                    <div>
                        <label for="edit_dokter_select"
                            class="block mb-1 text-xs font-semibold text-slate-700 dark:text-slate-200">
                            Dokter
                        </label>
                        <select id="edit_dokter_select" name="dokter_id"
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-600
                                       text-slate-900 dark:text-slate-50 text-sm rounded-lg px-3 py-2.5
                                       focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-sky-400"
                            placeholder="Cari & pilih dokter…">
                        </select>
                        <div id="edit_dokter_id-error" class="text-[11px] text-red-600 mt-1"></div>
                    </div>

                    {{-- Poli --}}
                    <div id="group_poli_edit" class="hidden">
                        <label for="edit_poli_select"
                            class="block mb-1 text-xs font-semibold text-slate-700 dark:text-slate-200">
                            Poli
                        </label>
                        <select id="edit_poli_select" name="poli_id"
                            class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-600
                                       text-slate-900 dark:text-slate-50 text-sm rounded-lg px-3 py-2.5
                                       focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-sky-400"
                            placeholder="Cari & pilih poli…">
                        </select>
                        <div id="edit_poli_id-error" class="text-[11px] text-red-600 mt-1"></div>
                    </div>
                </div>

                {{-- Keluhan --}}
                <div>
                    <label class="block mb-1 text-xs font-semibold text-slate-700 dark:text-slate-200">
                        Keluhan Awal
                    </label>
                    <textarea id="edit_keluhan_awal" name="keluhan_awal" rows="3" required
                        class="w-full bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-600
                                   text-slate-900 dark:text-slate-50 text-sm rounded-lg px-3 py-2.5
                                   focus:outline-none focus:ring-2 focus:ring-sky-400 focus:border-sky-400"
                        placeholder="Contoh: Demam sejak 2 hari, batuk, pusing..."></textarea>
                </div>

                {{-- Global error --}}
                <div id="edit_error_box"
                    class="hidden text-[11px] text-red-600 bg-red-50 dark:bg-red-900/30
                               border border-red-100 dark:border-red-500/40 rounded-lg px-3 py-2">
                </div>

                {{-- FOOTER --}}
                <div class="flex justify-end gap-2 pt-4 border-t border-slate-200 dark:border-slate-700 mt-2">
                    <button type="button"
                        class="close-edit-kunjungan px-4 py-2 text-xs md:text-sm
                                   bg-slate-200 text-slate-700 rounded-xl hover:bg-slate-300
                                   dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2 text-xs md:text-sm font-semibold text-white rounded-xl
                                   bg-gradient-to-r from-sky-500 to-teal-600 hover:from-sky-600 hover:to-teal-700
                                   shadow-sm hover:shadow focus:ring-2 focus:ring-sky-400">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@vite(['resources/js/admin/jadwalKunjungan/proses_kunjungan.js'])
