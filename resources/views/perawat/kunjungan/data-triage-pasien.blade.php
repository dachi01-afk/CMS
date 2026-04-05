<section class="space-y-5">

    {{-- ============== HEADER ============== --}}
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
               bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl px-4 md:px-6 py-4 shadow-sm">

        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl
                       bg-gradient-to-br from-sky-500 to-teal-500 text-white shadow-md">
                <i class="fa-solid fa-heart-pulse text-lg"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Daftar Siap Triage
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Monitoring pasien yang sudah <span class="font-semibold">engaged</span> dan siap dilakukan triage
                    oleh perawat.
                </p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 md:gap-3">
            <div
                class="inline-flex items-center gap-2 rounded-full bg-sky-50 text-sky-700 px-3 py-1
                       dark:bg-sky-900/40 dark:text-sky-100 border border-sky-100 dark:border-sky-700">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-semibold tracking-wide uppercase">Status: Engaged</span>
            </div>
        </div>
    </div>

    {{-- ============== CARD TABEL TRIAGE ============== --}}
    <div
        class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl shadow-sm overflow-hidden">

        {{-- TOP TOOLBAR: page length + search --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                   px-4 md:px-6 py-3 border-b border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/40">

            {{-- Left: page length --}}
            <div class="flex items-center gap-2">
                <span class="text-xs md:text-sm text-slate-600 dark:text-slate-300">
                    Tampilkan
                </span>
                <select id="triage_pageLength"
                    class="border border-slate-300 dark:border-slate-600 text-xs md:text-sm rounded-xl
                           focus:ring-sky-500 focus:border-sky-500 bg-white dark:bg-slate-800
                           px-2 py-1.5 w-24">
                    <option value="10">10 data</option>
                    <option value="25">25 data</option>
                    <option value="50">50 data</option>
                    <option value="100">100 data</option>
                </select>
                <span class="text-xs md:text-sm text-slate-600 dark:text-slate-300 hidden md:inline">
                    per halaman
                </span>
            </div>

            {{-- Right: search --}}
            <div class="w-full md:w-auto">
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </span>
                    <input type="text" id="triage_searchInput"
                        class="block w-full md:w-64 lg:w-72 pl-8 pr-3 py-2 text-sm
                               rounded-xl border border-slate-300 dark:border-slate-600
                               bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-50
                               placeholder:text-slate-400 dark:placeholder:text-slate-500
                               focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                        placeholder="Cari nama pasien, dokter, poli, atau keluhan...">
                </div>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="overflow-x-auto">
            <table id="tabelTriage" data-is-super-admin="{{ auth()->user()->role === 'Super Admin' ? '1' : '0' }}"
                class="w-full text-sm text-left text-slate-700 dark:text-slate-100 min-w-[900px]">
                <thead class="text-xs uppercase bg-gradient-to-r from-sky-500 to-teal-500 text-white">
                    <tr>
                        <th class="px-5 py-3 text-[11px] font-semibold tracking-wide">No</th>
                        <th class="px-5 py-3 text-[11px] font-semibold tracking-wide">No Antrian</th>
                        <th class="px-5 py-3 text-[11px] font-semibold tracking-wide">Nama Pasien</th>
                        <th class="px-5 py-3 text-[11px] font-semibold tracking-wide">Dokter</th>
                        <th class="px-5 py-3 text-[11px] font-semibold tracking-wide">Poli</th>

                        @if (auth()->user()->role === 'Super Admin')
                            <th class="px-5 py-3 text-[11px] font-semibold tracking-wide">Perawat</th>
                        @endif

                        <th class="px-5 py-3 text-[11px] font-semibold tracking-wide">Keluhan Utama</th>
                        <th class="px-5 py-3 text-[11px] font-semibold tracking-wide text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700"></tbody>
            </table>
        </div>

        {{-- FOOTER: info + pagination --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                   px-4 md:px-6 py-3 border-t border-slate-100 dark:border-slate-700 bg-slate-50/60 dark:bg-slate-900/40">
            <div id="triage_customInfo" class="text-xs md:text-sm text-slate-600 dark:text-slate-300">
                {{-- Diisi via JS --}}
            </div>

            <ul id="triage_customPagination" class="inline-flex items-center gap-1 text-xs md:text-sm">
                {{-- Diisi via JS --}}
            </ul>
        </div>
    </div>
</section>

<div id="modalDetailKunjungan" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 px-4">
    <div
        class="w-full max-w-4xl rounded-2xl bg-white dark:bg-slate-800 shadow-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">

        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-700">
            <div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">
                    Detail Kunjungan
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Informasi lengkap kunjungan pasien.
                </p>
            </div>

            <button type="button" id="btnCloseModalDetailKunjungan"
                class="inline-flex items-center justify-center w-9 h-9 rounded-lg
                       bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600
                       text-slate-600 dark:text-slate-200 transition">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="p-5 max-h-[80vh] overflow-y-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                    <h4 class="font-semibold text-slate-800 dark:text-slate-100 mb-3">Informasi Kunjungan</h4>
                    <div class="space-y-2">
                        <div><span class="font-medium">No Antrian:</span> <span id="detail_no_antrian">-</span></div>
                        <div><span class="font-medium">Tanggal Kunjungan:</span> <span
                                id="detail_tanggal_kunjungan">-</span></div>
                        <div><span class="font-medium">Status:</span> <span id="detail_status_kunjungan">-</span></div>
                        <div><span class="font-medium">Dokter:</span> <span id="detail_nama_dokter">-</span></div>
                        <div><span class="font-medium">Poli:</span> <span id="detail_nama_poli">-</span></div>
                        <div><span class="font-medium">Perawat:</span> <span id="detail_nama_perawat">-</span></div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                    <h4 class="font-semibold text-slate-800 dark:text-slate-100 mb-3">Informasi Pasien</h4>
                    <div class="space-y-2">
                        <div><span class="font-medium">Nama Pasien:</span> <span id="detail_nama_pasien">-</span></div>
                        <div><span class="font-medium">Keluhan Awal:</span> <span id="detail_keluhan_awal">-</span>
                        </div>
                        <div><span class="font-medium">Keluhan Utama:</span> <span id="detail_keluhan_utama">-</span>
                        </div>
                        <div><span class="font-medium">Diagnosis:</span> <span id="detail_diagnosis">-</span></div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 md:col-span-2">
                    <h4 class="font-semibold text-slate-800 dark:text-slate-100 mb-3">Vital Sign</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div><span class="font-medium">Tekanan Darah:</span> <span id="detail_tekanan_darah">-</span>
                        </div>
                        <div><span class="font-medium">Suhu Tubuh:</span> <span id="detail_suhu_tubuh">-</span></div>
                        <div><span class="font-medium">Nadi:</span> <span id="detail_nadi">-</span></div>
                        <div><span class="font-medium">Pernapasan:</span> <span id="detail_pernapasan">-</span></div>
                        <div><span class="font-medium">Saturasi Oksigen:</span> <span
                                id="detail_saturasi_oksigen">-</span></div>
                        <div><span class="font-medium">Tinggi Badan:</span> <span id="detail_tinggi_badan">-</span>
                        </div>
                        <div><span class="font-medium">Berat Badan:</span> <span id="detail_berat_badan">-</span>
                        </div>
                        <div><span class="font-medium">IMT:</span> <span id="detail_imt">-</span></div>
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4 md:col-span-2">
                    <h4 class="font-semibold text-slate-800 dark:text-slate-100 mb-3">Riwayat</h4>
                    <div class="space-y-3">
                        <div>
                            <div class="font-medium mb-1">Riwayat Penyakit Dahulu</div>
                            <div id="detail_riwayat_penyakit_dahulu"
                                class="text-slate-600 dark:text-slate-300 leading-relaxed">-</div>
                        </div>
                        <div>
                            <div class="font-medium mb-1">Riwayat Penyakit Keluarga</div>
                            <div id="detail_riwayat_penyakit_keluarga"
                                class="text-slate-600 dark:text-slate-300 leading-relaxed">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end px-5 py-4 border-t border-slate-200 dark:border-slate-700">
            <button type="button" id="btnTutupModalDetailKunjungan"
                class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-600 text-white text-sm font-medium hover:bg-slate-700 transition">
                Tutup
            </button>
        </div>
    </div>
</div>

@vite(['resources/js/perawat/kunjungan/data-triage-pasien.js'])
