<section class="space-y-5">

    @if (session('error'))
        <div
            class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700
               dark:border-red-700/60 dark:bg-red-900/40 dark:text-red-100">
            <div class="flex items-start gap-2">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <p>{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- HEADER + FILTER / EXPORT --}}
    <div
        class="flex flex-col gap-4 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700
               rounded-2xl px-4 md:px-6 py-4 shadow-sm">

        <div class="flex items-start gap-3">
            <div
                class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl
                       bg-gradient-to-br from-sky-500 to-indigo-500 text-white shadow-md">
                <i class="fa-solid fa-clipboard-check text-lg"></i>
            </div>

            <div>
                <h2 class="text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                    Audit Perjalanan Pasien
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Data lengkap mulai dari akun yang menginput kunjungan, poli &amp; dokter, jam, tindakan dokter,
                    kasir yang melayani pembayaran, sampai akun farmasi yang menyerahkan obat.
                </p>
            </div>
        </div>

        {{-- Filter: Layanan, Poli, Periode tahunan/bulanan + Export --}}
        <form action="{{ route('super.admin.laporan.audit.kunjungan.export') }}" method="GET"
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-12 gap-2 md:gap-3 items-end w-full">

            <div class="sm:col-span-2 xl:col-span-3 remote-filter-select">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">
                    Layanan (Tindakan)
                </label>
                <select name="layanan_id" id="layananAudit"
                    data-search-url="{{ route('super.admin.laporan.audit.kunjungan.search.layanan') }}"
                    data-placeholder="Semua Layanan">
                    <option value=""></option>
                </select>
            </div>

            <div class="sm:col-span-1 xl:col-span-3 remote-filter-select">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">
                    Poli
                </label>
                <select name="poli_id" id="poliAudit"
                    data-search-url="{{ route('super.admin.laporan.audit.kunjungan.search.poli') }}"
                    data-placeholder="Semua Poli">
                    <option value=""></option>
                </select>
            </div>

            <div class="sm:col-span-1 xl:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">
                    Periode
                </label>
                <select name="periode" id="periodeAudit"
                    class="w-full border border-slate-300 dark:border-slate-600 rounded-lg
                           bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100
                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2 text-sm">
                    <option value="bulanan">Bulanan</option>
                    <option value="tahunan">Tahunan</option>
                </select>
            </div>

            {{-- Label + input Bulan dibungkus satu wrapper agar bisa disembunyikan bersama --}}
            <div id="bulanAuditWrapper" class="sm:col-span-1 xl:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">
                    Bulan
                </label>
                <select name="bulan" id="bulanAudit"
                    class="w-full border border-slate-300 dark:border-slate-600 rounded-lg
                           bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100
                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2 text-sm">
                    <option value="01">Januari</option>
                    <option value="02">Februari</option>
                    <option value="03">Maret</option>
                    <option value="04">April</option>
                    <option value="05">Mei</option>
                    <option value="06">Juni</option>
                    <option value="07">Juli</option>
                    <option value="08">Agustus</option>
                    <option value="09">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>
            </div>

            <div class="sm:col-span-2 xl:col-span-2">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-400 mb-1">
                    Tahun
                </label>
                <select name="tahun" id="tahunAudit"
                    class="w-full border border-slate-300 dark:border-slate-600 rounded-lg
                           bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100
                           focus:ring-sky-500 focus:border-sky-500 px-3 py-2 text-sm">
                    @for ($year = now()->year; $year >= now()->year - 4; $year--)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endfor
                </select>
            </div>

            <div class="sm:col-span-2 xl:col-span-12 flex justify-end">
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-xl shadow-md
                           bg-gradient-to-r from-sky-500 to-indigo-600 hover:from-sky-600 hover:to-indigo-700
                           focus:outline-none focus:ring-2 focus:ring-sky-400">
                    <i class="fa-solid fa-file-excel text-xs"></i>
                    <span>Export Excel</span>
                </button>
            </div>
        </form>

    </div>

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">

        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Kunjungan</p>
            <p id="auditTotalKunjungan" class="mt-2 text-2xl font-bold text-slate-800 dark:text-slate-50">0</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Pada periode terpilih</p>
        </div>

        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 px-4 py-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Pasien</p>
            <p id="auditTotalPasien" class="mt-2 text-2xl font-bold text-slate-800 dark:text-slate-50">0</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Pasien unik</p>
        </div>

        <div class="rounded-2xl border border-sky-200 dark:border-sky-800 bg-sky-50 dark:bg-sky-900/30 px-4 py-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-sky-600 dark:text-sky-300">Sudah Diperiksa</p>
            <p id="auditTotalDiperiksa" class="mt-2 text-2xl font-bold text-sky-700 dark:text-sky-200">0</p>
            <p class="mt-1 text-xs text-sky-600/80 dark:text-sky-300/80">Kunjungan dengan rekam medis</p>
        </div>

        <div class="rounded-2xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/30 px-4 py-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600 dark:text-emerald-300">
                Total Nilai Pembayaran
            </p>
            <p id="auditTotalPembayaran" class="mt-2 text-2xl font-bold text-emerald-700 dark:text-emerald-200">Rp 0</p>
            <p class="mt-1 text-xs text-emerald-600/80 dark:text-emerald-300/80">Akumulasi tagihan pasien</p>
        </div>

    </div>

    {{-- CARD TABEL --}}
    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">

        {{-- CONTROL BAR --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                   px-4 md:px-6 py-3 border-b border-slate-200 dark:border-slate-700">

            <div class="flex items-center gap-2">
                <label for="audit_pageLength" class="text-sm text-slate-600 dark:text-slate-300 whitespace-nowrap">
                    Tampilkan
                </label>
                <select id="audit_pageLength"
                    class="border border-slate-300 dark:border-slate-600 text-sm rounded-lg
                           bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200
                           focus:ring-sky-500 focus:border-sky-500 px-2 py-1 w-24">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span class="text-sm text-slate-500 dark:text-slate-400 hidden sm:inline">entri per halaman</span>
            </div>

            <div class="relative w-full md:w-72">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </span>
                <input type="text" id="audit_searchInput"
                    class="block w-full pl-8 pr-3 py-2 text-sm
                           border border-slate-300 dark:border-slate-600 rounded-lg
                           bg-slate-50 dark:bg-slate-900
                           text-slate-800 dark:text-slate-100
                           placeholder-slate-400
                           focus:ring-sky-500 focus:border-sky-500"
                    placeholder="Cari pasien, No. EMR, dokter, poli...">
            </div>
        </div>

        {{-- TABEL --}}
        <div class="overflow-x-auto">
            <table id="tabelAuditKunjungan" class="min-w-full text-sm text-left text-slate-700 dark:text-slate-100">
                <thead class="text-xs uppercase bg-sky-500 dark:bg-sky-600 text-white tracking-wide">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Tanggal / Jam</th>
                        <th class="px-4 py-3">Antrian</th>
                        <th class="px-4 py-3">Pasien</th>
                        <th class="px-4 py-3">Poli</th>
                        <th class="px-4 py-3">Dokter</th>
                        <th class="px-4 py-3">Total Pembayaran</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Diisi via DataTable --}}
                </tbody>
            </table>
        </div>

        {{-- FOOTER --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3
                   px-4 md:px-6 py-3 border-t border-slate-200 dark:border-slate-700">

            <div id="audit_customInfo" class="text-sm text-slate-600 dark:text-slate-300"></div>

            <ul id="audit_customPagination" class="inline-flex items-center gap-px text-sm"></ul>
        </div>
    </div>

    <p class="text-xs text-slate-400 dark:text-slate-500">
        Catatan: kolom pelaku (input kunjungan, kasir, farmasi) dibaca dari audit log sistem. Bila aksi belum
        tercatat pada audit log, kolom akan menampilkan keterangan tidak tercatat.
    </p>

</section>

<!-- ✅ MODAL DETAIL AUDIT KUNJUNGAN (perjalanan pasien per tahap) -->
<div id="modalDetailAuditKunjungan" class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50 px-4">
    <div class="relative w-full max-w-4xl">
        <div class="relative overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-slate-800">

            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-200 p-5 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-50 text-sky-700 ring-1 ring-sky-200 dark:bg-sky-900/30 dark:text-sky-200 dark:ring-sky-800">
                        <i class="fa-solid fa-route"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                            Perjalanan Pasien
                        </h3>
                        <p id="auditDetailSubtitle" class="text-xs text-slate-500 dark:text-slate-400">-</p>
                    </div>
                </div>

                <button type="button" data-close-modal="detail-audit-kunjungan"
                    class="rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-700 dark:hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Body -->
            <div class="max-h-[70vh] space-y-4 overflow-y-auto p-5">

                <div id="auditDetailError"
                    class="hidden rounded-xl bg-rose-50 p-3 text-sm text-rose-800 ring-1 ring-rose-200 dark:bg-rose-900/30 dark:text-rose-200 dark:ring-rose-800">
                </div>

                <div id="auditDetailLoading"
                    class="hidden rounded-xl bg-slate-50 p-3 text-sm text-slate-600 ring-1 ring-slate-200 dark:bg-slate-900/30 dark:text-slate-300 dark:ring-slate-700">
                    Memuat perjalanan pasien...
                </div>

                {{-- 1. Pendaftaran --}}
                <section class="overflow-hidden rounded-2xl ring-1 ring-slate-200 dark:ring-slate-700">
                    <header class="flex items-center gap-2 bg-slate-50 px-4 py-3 dark:bg-slate-900/30">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-sky-500 text-[11px] font-bold text-white">1</span>
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-100">Pendaftaran</h4>
                    </header>
                    <div class="grid grid-cols-1 gap-3 p-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tanggal Kunjungan</div>
                            <div id="auditTanggalKunjungan" class="mt-1 text-slate-700 dark:text-slate-200">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Jam Kunjungan</div>
                            <div id="auditJamKunjungan" class="mt-1 text-slate-700 dark:text-slate-200">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Jam Input</div>
                            <div id="auditJamInput" class="mt-1 text-slate-700 dark:text-slate-200">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">No. Antrian</div>
                            <div id="auditAntrian" class="mt-1 font-semibold text-slate-800 dark:text-slate-100">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Pasien</div>
                            <div id="auditPasien" class="mt-1 font-semibold text-slate-800 dark:text-slate-100">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">No. EMR</div>
                            <div id="auditEmr" class="mt-1 text-slate-700 dark:text-slate-200">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Poli</div>
                            <div id="auditPoli" class="mt-1 text-slate-700 dark:text-slate-200">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Dokter</div>
                            <div id="auditDokterPendaftaran" class="mt-1 text-slate-700 dark:text-slate-200">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Diinput Oleh</div>
                            <div id="auditPenginput" class="mt-1 font-semibold text-slate-800 dark:text-slate-100">-</div>
                            <div id="auditPenginputRole" class="text-xs text-slate-500 dark:text-slate-400">-</div>
                        </div>
                    </div>
                </section>

                {{-- 2. Pemeriksaan Awal --}}
                <section class="overflow-hidden rounded-2xl ring-1 ring-slate-200 dark:ring-slate-700">
                    <header class="flex items-center gap-2 bg-slate-50 px-4 py-3 dark:bg-slate-900/30">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-sky-500 text-[11px] font-bold text-white">2</span>
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-100">Pemeriksaan Awal</h4>
                    </header>
                    <div class="grid grid-cols-1 gap-3 p-4 text-sm sm:grid-cols-2">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Perawat</div>
                            <div id="auditPerawat" class="mt-1 font-semibold text-slate-800 dark:text-slate-100">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Jam Pemeriksaan</div>
                            <div id="auditJamPeriksa" class="mt-1 text-slate-700 dark:text-slate-200">-</div>
                        </div>
                    </div>
                </section>

                {{-- 3. Dokter --}}
                <section class="overflow-hidden rounded-2xl ring-1 ring-slate-200 dark:ring-slate-700">
                    <header class="flex items-center gap-2 bg-slate-50 px-4 py-3 dark:bg-slate-900/30">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-sky-500 text-[11px] font-bold text-white">3</span>
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-100">Pemeriksaan Dokter</h4>
                    </header>
                    <div class="grid grid-cols-1 gap-3 p-4 text-sm sm:grid-cols-2">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Dokter Pemeriksa</div>
                            <div id="auditDokterPeriksa" class="mt-1 font-semibold text-slate-800 dark:text-slate-100">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Diagnosis</div>
                            <div id="auditDiagnosis" class="mt-1 text-slate-700 dark:text-slate-200">-</div>
                        </div>
                        <div class="sm:col-span-2">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tindakan (Layanan)</div>
                            <ul id="auditTindakanList" class="mt-1 space-y-1 text-slate-700 dark:text-slate-200">
                                <li class="italic text-slate-400">Belum ada tindakan</li>
                            </ul>
                        </div>
                    </div>
                </section>

                {{-- 4. Kasir --}}
                <section class="overflow-hidden rounded-2xl ring-1 ring-slate-200 dark:ring-slate-700">
                    <header class="flex items-center gap-2 bg-slate-50 px-4 py-3 dark:bg-slate-900/30">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-sky-500 text-[11px] font-bold text-white">4</span>
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-100">Pembayaran (Kasir)</h4>
                    </header>
                    <div class="grid grid-cols-1 gap-3 p-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kode Transaksi</div>
                            <div id="auditKodeBayar" class="mt-1 font-semibold text-slate-800 dark:text-slate-100">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Tagihan</div>
                            <div id="auditTotalTagihan" class="mt-1 text-slate-700 dark:text-slate-200">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Metode Bayar</div>
                            <div id="auditMetode" class="mt-1 text-slate-700 dark:text-slate-200">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Status Bayar</div>
                            <div id="auditStatusBayar" class="mt-1 text-slate-700 dark:text-slate-200">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tanggal Bayar</div>
                            <div id="auditTanggalBayar" class="mt-1 text-slate-700 dark:text-slate-200">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kasir</div>
                            <div id="auditKasir" class="mt-1 font-semibold text-slate-800 dark:text-slate-100">-</div>
                        </div>
                    </div>
                </section>

                {{-- 5. Farmasi --}}
                <section class="overflow-hidden rounded-2xl ring-1 ring-slate-200 dark:ring-slate-700">
                    <header class="flex items-center gap-2 bg-slate-50 px-4 py-3 dark:bg-slate-900/30">
                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-sky-500 text-[11px] font-bold text-white">5</span>
                        <h4 class="text-sm font-semibold text-slate-700 dark:text-slate-100">Farmasi</h4>
                    </header>
                    <div class="grid grid-cols-1 gap-3 p-4 text-sm sm:grid-cols-2 lg:grid-cols-3">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Status Resep</div>
                            <div id="auditStatusResep" class="mt-1 text-slate-700 dark:text-slate-200">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Akun Farmasi</div>
                            <div id="auditFarmasiAkun" class="mt-1 font-semibold text-slate-800 dark:text-slate-100">-</div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Jam Serah Obat</div>
                            <div id="auditJamSerah" class="mt-1 text-slate-700 dark:text-slate-200">-</div>
                        </div>
                        <div class="sm:col-span-2 lg:col-span-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Daftar Obat</div>
                            <ul id="auditObatList" class="mt-1 space-y-1 text-slate-700 dark:text-slate-200">
                                <li class="italic text-slate-400">Belum ada obat</li>
                            </ul>
                        </div>
                    </div>
                </section>

            </div>

            <!-- Footer -->
            <div class="flex items-center justify-end gap-2 border-t border-slate-200 p-5 dark:border-slate-700">
                <button type="button" data-close-modal="detail-audit-kunjungan"
                    class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-700">
                    Tutup
                </button>
            </div>

        </div>
    </div>
</div>

@vite(['resources/js/super-admin/laporan-audit-kunjungan/data-laporan-audit-kunjungan.js'])
