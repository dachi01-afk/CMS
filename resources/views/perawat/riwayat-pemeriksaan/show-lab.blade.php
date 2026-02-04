<x-mycomponents.layout>
    <div class="p-4 md:p-6 space-y-6">

        {{-- ========================= HEADER ========================== --}}
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div class="flex items-start gap-3">

                <a href="{{ route('riwayat-pemeriksaan.index') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white shadow-sm hover:shadow transition text-sm font-semibold text-slate-700">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali
                </a>

                <div class="space-y-1">
                    <h1 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight">
                        Detail Hasil Lab
                    </h1>

                    @php
                        $tgl = $data->tanggal_pemeriksaan ?? ($data->tanggal_order ?? null);
                        $jam = $data->jam_pemeriksaan ?? null;

                        // hitung berapa item pemeriksaan & berapa yang sudah ada hasilnya
                        $totalDetail = $data->orderLabDetail?->count() ?? 0;
                        $totalHasil = $data->orderLabDetail?->filter(fn($d) => !empty($d->hasilLab))->count() ?? 0;
                    @endphp

                    <div class="text-sm text-slate-600 flex flex-wrap items-center gap-2">
                        <span class="font-semibold">No. Order:</span>
                        <span class="font-extrabold text-slate-900">{{ $data->no_order_lab ?? '-' }}</span>
                        <span class="text-slate-300">•</span>

                        <span class="inline-flex items-center gap-2">
                            <i class="fa-regular fa-calendar text-slate-400"></i>
                            <span class="font-semibold">
                                {{ $tgl ? \Carbon\Carbon::parse($tgl)->format('d M Y') : '-' }}
                                @if ($jam)
                                    • {{ $jam }}
                                @endif
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-2xl bg-emerald-50 text-emerald-700 text-sm font-semibold">
                    <i class="fa-solid fa-circle-check"></i>
                    {{ $totalHasil }} hasil • {{ $totalDetail }} pemeriksaan
                </span>
            </div>
        </div>

        {{-- ========================= SUMMARY ========================== --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-white shadow-sm p-4">
                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pasien</div>
                <div class="mt-1 text-base font-extrabold text-slate-900 truncate">
                    {{ $data->pasien->nama_pasien ?? '-' }}
                </div>
            </div>

            <div class="rounded-2xl bg-white shadow-sm p-4">
                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Dokter</div>
                <div class="mt-1 text-base font-extrabold text-slate-900 truncate">
                    {{ $data->dokter->nama_dokter ?? '-' }}
                </div>
            </div>

            <div class="rounded-2xl bg-white shadow-sm p-4">
                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Catatan</div>
                <div class="mt-1 text-sm text-slate-700">
                    Yang tampil di sini <span class="font-semibold">hanya hasil yang sudah tersimpan</span>.
                    Jika ada pemeriksaan tanpa hasil, berarti belum diinput.
                </div>
            </div>
        </div>

        {{-- ========================= CONTENT ========================== --}}
        <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
            <div class="px-4 md:px-6 py-4 bg-slate-50/70">
                <div>
                    <div class="text-base font-extrabold text-slate-900">Daftar Hasil</div>
                    <div class="text-sm text-slate-600">
                        Ringkasan hasil lab per pemeriksaan.
                    </div>
                </div>
            </div>

            <div class="p-4 md:p-6">
                @if ($totalHasil === 0)
                    <div class="rounded-2xl bg-slate-50 p-6 text-center">
                        <div class="text-slate-900 font-extrabold">Belum ada hasil</div>
                        <div class="text-sm text-slate-600 mt-1">
                            Untuk order ini, belum ada hasil lab yang tersimpan.
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-2xl border border-slate-100">
                        <table class="w-full text-sm text-left text-slate-700 min-w-[900px]">
                            <thead class="text-xs uppercase bg-slate-900 text-white">
                                <tr>
                                    <th class="px-5 py-3 text-[11px] font-semibold tracking-wide">Pemeriksaan</th>
                                    <th class="px-5 py-3 text-[11px] font-semibold tracking-wide">Nilai</th>
                                    <th class="px-5 py-3 text-[11px] font-semibold tracking-wide">Rujukan</th>
                                    <th class="px-5 py-3 text-[11px] font-semibold tracking-wide">Satuan</th>
                                    <th class="px-5 py-3 text-[11px] font-semibold tracking-wide">Catatan</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-slate-100">
                                @foreach ($data->orderLabDetail ?? collect() as $detail)
                                    @php
                                        $hasil = $detail->hasilLab;
                                        if (!$hasil) {
                                            continue;
                                        }

                                        $nama = $detail->jenisPemeriksaanLab->nama_pemeriksaan ?? '-';
                                        $satuan = $detail->jenisPemeriksaanLab->satuanLab->nama_satuan ?? '-';
                                    @endphp

                                    <tr class="bg-white hover:bg-slate-50 transition-colors">
                                        <td class="px-5 py-3 font-extrabold text-slate-900">
                                            {{ $nama }}
                                        </td>
                                        <td class="px-5 py-3 font-semibold">
                                            {{ $hasil->nilai_hasil ?? '-' }}
                                        </td>
                                        <td class="px-5 py-3">
                                            {{ $hasil->nilai_rujukan ?? '-' }}
                                        </td>
                                        <td class="px-5 py-3">
                                            {{ $satuan }}
                                        </td>
                                        <td class="px-5 py-3 text-slate-700 whitespace-pre-line">
                                            {{ $hasil->catatan ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-mycomponents.layout>
