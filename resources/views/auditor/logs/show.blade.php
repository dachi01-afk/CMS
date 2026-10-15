<x-mycomponents.layout>

    <div class="space-y-5">

        {{-- Header --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
            <div class="flex items-start gap-3">
                <div
                    class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white shadow-md shrink-0">
                    <i class="fa-solid fa-magnifying-glass-chart text-lg"></i>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                        Detail Audit Log #{{ $log->id }}
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        {{ $log->description }}
                    </p>
                </div>
            </div>

            <a href="{{ url()->previous() }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>

        {{-- Informasi log --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-5">
            <h2 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200 mb-4">
                Informasi Aktivitas
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-[11px] font-semibold uppercase text-slate-500">Waktu</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $log->created_at?->format('d-m-Y H:i:s') }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-[11px] font-semibold uppercase text-slate-500">Action</p>
                    <p class="mt-1">@include('auditor.partials.action-badge', ['action' => $log->action])</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-[11px] font-semibold uppercase text-slate-500">Modul</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $log->module }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-[11px] font-semibold uppercase text-slate-500">User</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $log->user_name ?? 'Anonim' }}</p>
                    <p class="text-xs text-slate-500">{{ $log->user_role ?? '-' }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-[11px] font-semibold uppercase text-slate-500">IP Address</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $log->ip_address ?? '-' }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-[11px] font-semibold uppercase text-slate-500">Sumber / HTTP Method</p>
                    <p class="mt-1 text-sm text-slate-800">{{ $log->source_label }}</p>
                    <p class="text-xs text-slate-500">{{ $log->http_method ?? '-' }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-[11px] font-semibold uppercase text-slate-500">Entitas Terkait</p>
                    <p class="mt-1 text-sm text-slate-800 break-all">
                        {{ $log->auditable_type ? $log->auditable_label.' #'.($log->auditable_id ?? '-') : '-' }}
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                    <p class="text-[11px] font-semibold uppercase text-slate-500">Pasien Terkait</p>
                    @if ($log->pasien)
                        <a href="{{ route('auditor.logs.entity', ['no_emr' => $log->pasien->no_emr]) }}"
                            class="mt-1 block text-sm font-semibold text-sky-700 hover:text-sky-900">
                            {{ $log->pasien->nama_pasien }}
                        </a>
                        <p class="text-xs text-slate-500">{{ $log->pasien->no_emr }}</p>
                    @else
                        <p class="mt-1 text-sm text-slate-800">{{ $log->pasien_id ? '#'.$log->pasien_id.' (sudah dihapus)' : '-' }}</p>
                    @endif
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 lg:col-span-4">
                    <p class="text-[11px] font-semibold uppercase text-slate-500">URL</p>
                    <p class="mt-1 text-xs text-slate-700 break-all">{{ $log->url ?? '-' }}</p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-3 lg:col-span-4">
                    <p class="text-[11px] font-semibold uppercase text-slate-500">User Agent</p>
                    <p class="mt-1 text-xs text-slate-700 break-all">{{ $log->user_agent ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Perbandingan nilai lama vs baru --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700">
                <h2 class="text-sm font-bold uppercase tracking-wide text-slate-700 dark:text-slate-200">
                    Perbandingan Nilai Lama vs Baru
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                    Isi field rahasia (password, token, OTP) tidak pernah disimpan; perubahannya tetap terlihat di deskripsi.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-700 dark:text-slate-100">
                    <thead class="bg-slate-100 dark:bg-slate-700 text-xs uppercase text-slate-600 dark:text-slate-300">
                        <tr>
                            <th class="px-4 py-3 w-1/4">Field</th>
                            <th class="px-4 py-3 w-3/8">Nilai Lama</th>
                            <th class="px-4 py-3 w-3/8">Nilai Baru</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($differences as $difference)
                            <tr class="{{ $difference['changed'] ? 'bg-amber-50/50' : '' }}">
                                <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-700">{{ $difference['field'] }}</td>
                                <td class="px-4 py-3 break-all">
                                    <span class="{{ $difference['changed'] ? 'text-rose-700' : 'text-slate-600' }}">
                                        {{ $difference['old'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 break-all">
                                    <span class="{{ $difference['changed'] ? 'text-emerald-700 font-semibold' : 'text-slate-600' }}">
                                        {{ $difference['new'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-slate-500">
                                    Log ini tidak menyimpan perubahan nilai field (mis. aksi baca, login, export).
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($log->old_values || $log->new_values)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-5">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Raw old_values</h3>
                    <pre class="text-xs bg-slate-900 text-slate-100 rounded-xl p-4 overflow-x-auto">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-5">
                    <h3 class="text-xs font-bold uppercase tracking-wide text-slate-500 mb-2">Raw new_values</h3>
                    <pre class="text-xs bg-slate-900 text-slate-100 rounded-xl p-4 overflow-x-auto">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        @endif
    </div>
</x-mycomponents.layout>
