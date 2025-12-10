<section class="space-y-4">

    {{-- HEADER + CARD WRAPPER --}}
    <div id="prosesKunjungan"
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm">

        {{-- Header dalam card --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-4 md:px-6 py-4 border-b border-slate-200 dark:border-slate-700">
            <div class="flex items-start gap-3">
                <div
                    class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                    <i class="fa-solid fa-stethoscope text-lg"></i>
                </div>
                <div class="space-y-0.5">
                    <h2 class="text-lg md:text-xl font-bold text-slate-800 dark:text-slate-50">
                        Proses Kunjungan Hari Ini
                    </h2>
                    <p class="text-xs md:text-sm text-slate-500 dark:text-slate-400">
                        Daftar pasien yang terjadwal hari ini dan siap diproses oleh perawat/dokter.
                    </p>
                </div>
            </div>

            <div class="flex flex-col items-end gap-1 text-[11px] md:text-xs text-slate-500 dark:text-slate-300">
                <span
                    class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-sky-50 text-sky-700
                           dark:bg-sky-900/40 dark:text-sky-200 border border-sky-100 dark:border-sky-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    Kunjungan hari ini dimuat otomatis
                </span>
                <span class="hidden md:inline">
                    Klik tombol <span class="font-semibold">Aksi</span> untuk mulai pemeriksaan atau mengubah status.
                </span>
            </div>
        </div>

        {{-- TABEL PROSES KUNJUNGAN --}}
        <div class="relative overflow-x-auto">
            <table id="tabelProses"
                class="min-w-full text-sm text-slate-700 dark:text-slate-100 border-t border-slate-100 dark:border-slate-700">
                <thead
                    class="text-xs font-semibold uppercase bg-gradient-to-r from-sky-500 to-teal-500 text-white tracking-wide">
                    <tr>
                        <th class="px-5 py-2.5 text-left">No Antrian</th>
                        <th class="px-5 py-2.5 text-left">Nama Pasien</th>
                        <th class="px-5 py-2.5 text-left">Dokter</th>
                        <th class="px-5 py-2.5 text-left">Poli</th>
                        <th class="px-5 py-2.5 text-left">Keluhan</th>
                        <th class="px-5 py-2.5 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="waitingBody" class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr>
                        <td colspan="6"
                            class="text-center text-slate-500 dark:text-slate-300 py-6 italic text-sm">
                            Memuat data kunjungan hari ini...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</section>

@vite(['resources/js/perawat/kunjungan/data-kunjungan-hari-ini.js'])
