<x-mycomponents.layout>

    <div class="space-y-5">

        {{-- Header --}}
        <div
            class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
            <div class="flex items-start gap-3">
                <div
                    class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white shadow-md shrink-0">
                    <i class="fa-solid fa-clock-rotate-left text-lg"></i>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                        Riwayat per Entitas
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Pilih satu pasien untuk melihat SEMUA aktivitas yang berkaitan dengannya: data diri,
                        kunjungan, rekam medis, resep, lab, radiologi, pembayaran, serta siapa saja yang pernah
                        membuka datanya, dari web maupun aplikasi mobile.
                    </p>
                </div>
            </div>
        </div>

        {{-- Form pilih pasien --}}
        <form method="GET" action="{{ route('auditor.logs.entity') }}"
            class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-end gap-3">
                <div class="flex-1">
                    <label for="no_emr" class="block mb-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        Pasien
                    </label>
                    <select id="no_emr" name="no_emr" required
                        class="w-full border border-slate-300 dark:border-slate-600 text-sm rounded-lg px-2 py-2 bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 focus:ring-sky-500 focus:border-sky-500">
                        <option value="">-- Pilih pasien --</option>
                        @foreach ($pasienOptions as $option)
                            <option value="{{ $option->no_emr }}" @selected($noEmr === $option->no_emr)>
                                {{ $option->no_emr }} — {{ $option->nama_pasien }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700">
                    <i class="fa-solid fa-magnifying-glass"></i> Tampilkan Riwayat
                </button>
            </div>
        </form>

        @if ($noEmr && ! $pasien)
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                Pasien dengan nomor EMR <strong>{{ $noEmr }}</strong> tidak ditemukan.
            </div>
        @endif

        @if ($pasien)
            {{-- Ringkasan entitas --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Entitas</p>
                    <p class="mt-1 text-lg font-bold text-slate-800">{{ $pasien->nama_pasien }}</p>
                    <p class="text-sm text-slate-500">{{ $pasien->no_emr }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Rekam Medis Terkait</p>
                    <p class="mt-1 text-2xl font-bold text-slate-800">{{ $emrCount }}</p>
                    <p class="text-xs text-slate-500">{{ number_format($moduleSummary->sum()) }} aktivitas tercatat</p>
                </div>
            </div>

            {{-- Ringkasan per modul (klik untuk menyaring) --}}
            @if ($moduleSummary->isNotEmpty())
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('auditor.logs.entity', ['no_emr' => $pasien->no_emr]) }}"
                        class="rounded-full border px-3 py-1 text-xs font-semibold {{ $selectedModule ? 'border-slate-300 text-slate-600 bg-white' : 'border-sky-600 bg-sky-600 text-white' }}">
                        Semua ({{ $moduleSummary->sum() }})
                    </a>
                    @foreach ($moduleSummary as $module => $total)
                        <a href="{{ route('auditor.logs.entity', ['no_emr' => $pasien->no_emr, 'module' => $module]) }}"
                            class="rounded-full border px-3 py-1 text-xs font-semibold {{ $selectedModule === $module ? 'border-sky-600 bg-sky-600 text-white' : 'border-slate-300 text-slate-600 bg-white hover:bg-slate-50' }}">
                            {{ $module }} ({{ $total }})
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Siapa saja yang mengakses --}}
            <div
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-base font-bold text-slate-800 dark:text-slate-50">
                        Siapa Saja yang Mengakses Data Ini
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Dihitung dari aksi membuka, mencetak, dan mengekspor data yang berkaitan dengan pasien ini.
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-700 dark:text-slate-100">
                        <thead class="bg-slate-100 dark:bg-slate-700 text-xs uppercase text-slate-600 dark:text-slate-300">
                            <tr>
                                <th class="px-4 py-3">User</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3 text-right">Jumlah Akses</th>
                                <th class="px-4 py-3">Akses Terakhir</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse ($accessors as $accessor)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ $accessor->user_name ?? 'Anonim' }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $accessor->user_role ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">{{ $accessor->total }}×</td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        {{ \Illuminate\Support\Carbon::parse($accessor->terakhir)->format('d-m-Y H:i:s') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-slate-500">
                                        Belum ada log akses baca yang tercatat untuk entitas ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Timeline seluruh aktivitas entitas --}}
            <div
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-base font-bold text-slate-800 dark:text-slate-50">Semua Aktivitas Entitas Ini</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Seluruh perubahan dan akses terhadap data yang berkaitan dengan pasien ini.
                    </p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-700 dark:text-slate-100">
                        <thead class="bg-slate-100 dark:bg-slate-700 text-xs uppercase text-slate-600 dark:text-slate-300">
                            <tr>
                                <th class="px-4 py-3">Waktu</th>
                                <th class="px-4 py-3">User</th>
                                <th class="px-4 py-3">Action</th>
                                <th class="px-4 py-3">Modul</th>
                                <th class="px-4 py-3">Deskripsi</th>
                                <th class="px-4 py-3">Sumber</th>
                                <th class="px-4 py-3 text-center">Detail</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse ($logs as $log)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap">{{ $log->created_at?->format('d-m-Y H:i:s') }}</td>
                                    <td class="px-4 py-3">
                                        {{ $log->user_name ?? 'Anonim' }}
                                        <span class="block text-xs text-slate-400">{{ $log->user_role ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-3">@include('auditor.partials.action-badge', ['action' => $log->action])</td>
                                    <td class="px-4 py-3">{{ $log->module }}</td>
                                    <td class="px-4 py-3">{{ $log->description }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-slate-500">{{ $log->source_label }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('auditor.logs.show', $log->id) }}"
                                            class="text-sky-600 hover:text-sky-800"><i class="fa-regular fa-eye"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-6 text-center text-slate-500">
                                        Belum ada aktivitas tercatat untuk entitas ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($logs && $logs->hasPages())
                    <div class="px-5 py-4 border-t border-slate-200 dark:border-slate-700">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        @endif
    </div>
</x-mycomponents.layout>
