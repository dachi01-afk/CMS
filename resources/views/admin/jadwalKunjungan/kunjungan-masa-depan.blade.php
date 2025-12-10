<section class="space-y-5">

    {{-- HEADER KUNJUNGAN MENDATANG --}}
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
                    Daftar Kunjungan yang Akan Datang
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Menampilkan kunjungan pasien yang sudah terjadwal untuk tanggal mendatang.
                </p>
            </div>
        </div>

        <div class="flex flex-col items-end gap-1 text-xs text-slate-500 dark:text-slate-300">
            <span
                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-sky-50 text-sky-700
                       dark:bg-sky-900/40 dark:text-sky-200 border border-sky-100 dark:border-sky-800">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                Kunjungan terjadwal ke depan (≥ hari ini)
            </span>
            <span class="hidden md:inline text-[11px]">
                Klik <span class="font-semibold">Aksi</span> untuk melihat detail atau mengelola jadwal kunjungan.
            </span>
        </div>
    </div>

    {{-- CARD TABEL KUNJUNGAN MASA DEPAN --}}
    <div id="prosesKunjungan"
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl shadow-sm">

        {{-- STRIP INFO ATAS --}}
        <div
            class="flex items-center justify-between gap-3 px-4 md:px-6 py-2.5
                   border-b border-slate-200 dark:border-slate-700
                   bg-gradient-to-r from-sky-50 via-teal-50/60 to-white
                   dark:from-slate-800 dark:via-slate-800 dark:to-slate-800
                   text-[11px] md:text-xs text-slate-500 dark:text-slate-300">
            <div class="flex items-center gap-2">
                <i class="fa-regular fa-calendar-days text-sky-500 text-xs"></i>
                <span>Hanya menampilkan kunjungan yang dijadwalkan untuk tanggal hari ini dan seterusnya.</span>
            </div>
        </div>

        {{-- TABEL --}}
        <div class="relative overflow-x-auto rounded-b-2xl">
            <table class="min-w-full text-sm text-slate-700 dark:text-slate-100" id="tabelProses">
                <thead
                    class="text-xs font-semibold uppercase bg-gradient-to-r from-sky-500 via-teal-500 to-teal-600
                           text-white tracking-wide">
                    <tr>
                        <th class="px-6 py-3 text-left">No Antrian</th>
                        <th class="px-6 py-3 text-left">Nama Pasien</th>
                        <th class="px-6 py-3 text-left">Dokter</th>
                        <th class="px-6 py-3 text-left">Poli</th>
                        <th class="px-6 py-3 text-left">Keluhan</th>
                        <th class="px-6 py-3 text-left">Tanggal Kunjungan</th>
                        <th class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="waitingBodyMasaDepan"
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

{{-- MODAL DETAIL KUNJUNGAN YANG AKAN DATANG --}}
<div id="modalDetailKYAD" aria-hidden="true"
    class="hidden fixed inset-0 z-50 flex justify-center items-center w-full h-full p-4 md:p-6
               bg-slate-900/60 backdrop-blur-sm overflow-y-auto overflow-x-hidden">
    <div class="relative w-full max-w-lg max-h-[90vh]">
        <div
            class="relative bg-white dark:bg-slate-800 rounded-2xl shadow-2xl
                       border border-slate-100 dark:border-slate-700 flex flex-col max-h-[90vh] overflow-y-auto">

            {{-- HEADER MODAL --}}
            <div
                class="flex items-center justify-between gap-3 px-5 md:px-6 pt-4 pb-3
                           border-b border-slate-100 dark:border-slate-700
                           bg-gradient-to-r from-sky-500 to-teal-500 rounded-t-2xl">
                <div>
                    <h2 class="text-base md:text-lg font-semibold text-slate-50">
                        Detail Kunjungan
                    </h2>
                    <p class="text-xs text-sky-50/90 mt-0.5">
                        Informasi lengkap kunjungan pasien yang telah dijadwalkan.
                    </p>
                </div>
                <button id="closeModalKYAD"
                    class="inline-flex items-center justify-center h-8 w-8 rounded-full
                               text-slate-100 hover:text-white hover:bg-white/10 transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            {{-- BODY MODAL --}}
            <div id="detailKYADContent" class="px-5 md:px-6 py-4 space-y-2 text-sm text-slate-700 dark:text-slate-100">
                {{-- detailnya diisi via JS --}}
            </div>

            {{-- FOOTER --}}
            <div class="flex justify-end gap-2 px-5 md:px-6 pt-3 pb-4 border-t border-slate-200 dark:border-slate-700">
                <button id="closeModalKYADFooter"
                    class="px-4 py-2 text-xs md:text-sm bg-slate-200 text-slate-700 rounded-xl
                               hover:bg-slate-300 dark:bg-slate-700 dark:text-slate-100 dark:hover:bg-slate-600">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@vite(['resources/js/admin/jadwalKunjungan/kunjungan-masa-depan.js'])
