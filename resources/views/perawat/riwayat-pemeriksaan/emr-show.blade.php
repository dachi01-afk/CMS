<x-mycomponents.layout>
    <div class="p-4 md:p-6 space-y-6">

        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div class="flex items-start gap-3">
                <a href="{{ route('riwayat-pemeriksaan.index') }}"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-white shadow-sm hover:shadow transition text-sm font-semibold text-slate-700">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali
                </a>

                <div class="space-y-1">
                    <h1 class="text-xl md:text-2xl font-extrabold text-slate-900 tracking-tight">
                        Detail EMR
                    </h1>
                    <div class="text-sm text-slate-600 flex flex-wrap items-center gap-2">
                        <span class="font-semibold">ID EMR:</span>
                        <span
                            class="font-extrabold text-slate-900">EMR-{{ str_pad($emr->id, 6, '0', STR_PAD_LEFT) }}</span>
                        <span class="text-slate-300">•</span>
                        <span class="inline-flex items-center gap-2">
                            <i class="fa-regular fa-calendar text-slate-400"></i>
                            <span
                                class="font-semibold">{{ optional($emr->created_at)->format('d M Y H:i') ?? '-' }}</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-2xl bg-emerald-50 text-emerald-700 text-sm font-semibold">
                    <i class="fa-solid fa-circle-check"></i>
                    Tersimpan
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-white shadow-sm p-4">
                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Pasien</div>
                <div class="mt-1 text-base font-extrabold text-slate-900 truncate">
                    {{ $emr->pasien->nama_pasien ?? '-' }}
                </div>
            </div>

            <div class="rounded-2xl bg-white shadow-sm p-4">
                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Dokter</div>
                <div class="mt-1 text-base font-extrabold text-slate-900 truncate">
                    {{ $emr->dokter->nama_dokter ?? '-' }}
                </div>
            </div>

            <div class="rounded-2xl bg-white shadow-sm p-4">
                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Poli</div>
                <div class="mt-1 text-base font-extrabold text-slate-900 truncate">
                    {{ $emr->poli->nama_poli ?? '-' }}
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white shadow-sm overflow-hidden">
            <div class="px-4 md:px-6 py-4 bg-slate-50/70">
                <div class="text-base font-extrabold text-slate-900">Ringkasan EMR</div>
                <div class="text-sm text-slate-600">Keluhan, riwayat, dan diagnosis.</div>
            </div>

            <div class="p-4 md:p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-2xl bg-slate-50/60 p-4">
                        <div class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Tekanan Darah</div>
                        <div class="mt-1 text-lg font-extrabold text-slate-900">{{ $emr->tekanan_darah ?? '-' }}</div>
                    </div>
                    <div class="rounded-2xl bg-slate-50/60 p-4">
                        <div class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Suhu</div>
                        <div class="mt-1 text-lg font-extrabold text-slate-900">{{ $emr->suhu_tubuh ?? '-' }}</div>
                    </div>
                    <div class="rounded-2xl bg-slate-50/60 p-4">
                        <div class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">IMT</div>
                        <div class="mt-1 text-lg font-extrabold text-slate-900">{{ $emr->imt ?? '-' }}</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-2xl bg-slate-50/60 p-4">
                        <div class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Keluhan Utama</div>
                        <div class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $emr->keluhan_utama ?? '-' }}
                        </div>
                    </div>

                    <div class="rounded-2xl bg-slate-50/60 p-4">
                        <div class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Diagnosis</div>
                        <div class="mt-2 text-sm text-slate-700 whitespace-pre-line">{{ $emr->diagnosis ?? '-' }}</div>
                    </div>

                    <div class="rounded-2xl bg-slate-50/60 p-4">
                        <div class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Riwayat Penyakit
                            Dahulu</div>
                        <div class="mt-2 text-sm text-slate-700 whitespace-pre-line">
                            {{ $emr->riwayat_penyakit_dahulu ?? '-' }}</div>
                    </div>

                    <div class="rounded-2xl bg-slate-50/60 p-4">
                        <div class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Riwayat Penyakit
                            Keluarga</div>
                        <div class="mt-2 text-sm text-slate-700 whitespace-pre-line">
                            {{ $emr->riwayat_penyakit_keluarga ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-mycomponents.layout>
