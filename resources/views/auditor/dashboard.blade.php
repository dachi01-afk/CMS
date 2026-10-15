<x-mycomponents.layout>

    <div class="space-y-5">

        {{-- Header --}}
        <div
            class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-4 md:px-6 py-4 shadow-sm">
            <div class="flex items-start gap-3">
                <div
                    class="mt-1 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white shadow-md shrink-0">
                    <i class="fa-solid fa-chart-line text-lg"></i>
                </div>
                <div class="min-w-0">
                    <h1 class="text-lg sm:text-xl md:text-2xl font-bold text-slate-800 dark:text-slate-50">
                        Dashboard Ringkasan Aktivitas
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">
                        Ringkasan aktivitas {{ $from->format('d M Y') }} – {{ now()->format('d M Y') }}.
                        Jam operasional {{ $jamOperasional['start'] }}–{{ $jamOperasional['end'] }}.
                    </p>
                </div>
            </div>

            <form method="GET" action="{{ route('auditor.dashboard') }}">
                <select name="days" onchange="this.form.submit()"
                    class="border border-slate-300 dark:border-slate-600 text-sm rounded-lg bg-white dark:bg-slate-700 text-slate-800 dark:text-slate-100 px-3 py-2 focus:ring-sky-500 focus:border-sky-500">
                    @foreach ([7, 14, 30, 90] as $option)
                        <option value="{{ $option }}" @selected($days === $option)>{{ $option }} hari terakhir</option>
                    @endforeach
                </select>
            </form>
        </div>

        {{-- Stat cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-sky-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Aktivitas</p>
                    <i class="fa-solid fa-list-check text-sky-500"></i>
                </div>
                <p class="mt-2 text-3xl font-bold text-slate-800">{{ number_format($totalAktivitas, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $days }} hari terakhir</p>
            </div>

            <div class="rounded-2xl border border-indigo-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Aktivitas Hari Ini</p>
                    <i class="fa-solid fa-calendar-day text-indigo-500"></i>
                </div>
                <p class="mt-2 text-3xl font-bold text-slate-800">{{ number_format($totalHariIni, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ now()->translatedFormat('d F Y') }}</p>
            </div>

            <div class="rounded-2xl border border-red-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Login Gagal</p>
                    <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
                </div>
                <p class="mt-2 text-3xl font-bold text-slate-800">{{ number_format($loginGagal, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500 mt-1">{{ $loginGagalHariIni }} kejadian hari ini</p>
            </div>

            <div class="rounded-2xl border border-amber-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Luar Jam Operasional</p>
                    <i class="fa-solid fa-moon text-amber-500"></i>
                </div>
                <p class="mt-2 text-3xl font-bold text-slate-800">{{ number_format($luarJamOperasional, 0, ',', '.') }}</p>
                <p class="text-xs text-slate-500 mt-1">Di luar {{ $jamOperasional['start'] }}–{{ $jamOperasional['end'] }}</p>
            </div>
        </div>

        {{-- Chart aktivitas per hari --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-4 md:p-6">
            <h2 class="text-base font-bold text-slate-800 dark:text-slate-50 mb-1">Aktivitas per Hari</h2>
            <p class="text-xs text-slate-500 mb-4">Jumlah baris audit log per hari pada periode terpilih.</p>
            <div class="h-72">
                <canvas id="chart-aktivitas-harian"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- User paling aktif --}}
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700">
                    <h2 class="text-base font-bold text-slate-800 dark:text-slate-50">User Paling Aktif</h2>
                    <p class="text-xs text-slate-500 mt-1">10 user dengan jumlah aktivitas terbanyak.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-slate-700 dark:text-slate-100">
                        <thead class="bg-slate-100 dark:bg-slate-700 text-xs uppercase text-slate-600 dark:text-slate-300">
                            <tr>
                                <th class="px-4 py-3">User</th>
                                <th class="px-4 py-3">Role</th>
                                <th class="px-4 py-3 text-right">Aktivitas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            @forelse ($topUsers as $user)
                                <tr>
                                    <td class="px-4 py-3 font-medium">{{ $user->user_name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ $user->user_role ?? '-' }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">{{ number_format($user->total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-slate-500">Belum ada aktivitas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Modul & action --}}
            <div class="space-y-4">
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-5">
                    <h2 class="text-base font-bold text-slate-800 dark:text-slate-50 mb-3">Modul Paling Sering Diakses</h2>
                    @forelse ($moduleTotals as $module => $total)
                        @php
                            $max = $moduleTotals->max() ?: 1;
                            $percent = round(($total / $max) * 100);
                        @endphp
                        <div class="mb-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-700 dark:text-slate-200">{{ $module }}</span>
                                <span class="font-semibold text-slate-600 dark:text-slate-300">{{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                            <div class="mt-1 h-2 w-full rounded-full bg-slate-100 dark:bg-slate-700">
                                <div class="h-2 rounded-full bg-gradient-to-r from-sky-500 to-indigo-500" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada aktivitas.</p>
                    @endforelse
                </div>

                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm p-5">
                    <h2 class="text-base font-bold text-slate-800 dark:text-slate-50 mb-3">Jenis Aksi</h2>
                    <div class="flex flex-wrap gap-2">
                        @forelse ($actionTotals as $action => $total)
                            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-3 py-1 text-xs text-slate-600">
                                @include('auditor.partials.action-badge', ['action' => $action])
                                <span class="font-semibold">{{ number_format($total, 0, ',', '.') }}</span>
                            </span>
                        @empty
                            <p class="text-sm text-slate-500">Belum ada aktivitas.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Aktivitas di luar jam operasional --}}
        <div class="bg-white dark:bg-slate-800 border border-amber-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-amber-100 flex items-center gap-2">
                <i class="fa-solid fa-moon text-amber-500"></i>
                <div>
                    <h2 class="text-base font-bold text-slate-800 dark:text-slate-50">Aktivitas di Luar Jam Operasional</h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Aktivitas di luar {{ $jamOperasional['start'] }}–{{ $jamOperasional['end'] }} atau pada hari
                        non-operasional.
                    </p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-700 dark:text-slate-100">
                    <thead class="bg-amber-50 text-xs uppercase text-amber-800">
                        <tr>
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Action</th>
                            <th class="px-4 py-3">Modul</th>
                            <th class="px-4 py-3">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @forelse ($luarJamOperasionalTerbaru as $log)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap">{{ $log->created_at?->format('d-m-Y H:i:s') }}</td>
                                <td class="px-4 py-3">{{ $log->user_name ?? '-' }}</td>
                                <td class="px-4 py-3">@include('auditor.partials.action-badge', ['action' => $log->action])</td>
                                <td class="px-4 py-3">{{ $log->module }}</td>
                                <td class="px-4 py-3">{{ $log->description }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-slate-500">
                                    Tidak ada aktivitas di luar jam operasional.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Aktivitas terbaru --}}
        <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-800 dark:text-slate-50">Aktivitas Terbaru</h2>
                <a href="{{ route('auditor.logs.index') }}" class="text-sm font-semibold text-sky-600 hover:underline">
                    Lihat semua log <i class="fa-solid fa-arrow-right text-xs"></i>
                </a>
            </div>
            <ul class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse ($terbaru as $log)
                    <li class="px-5 py-3 flex items-start gap-3">
                        @include('auditor.partials.action-badge', ['action' => $log->action])
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-slate-700 dark:text-slate-200">{{ $log->description }}</p>
                            <p class="text-xs text-slate-400 mt-1">
                                {{ $log->user_name ?? 'Anonim' }} · {{ $log->user_role ?? '-' }} ·
                                {{ $log->created_at?->format('d-m-Y H:i:s') }}
                            </p>
                        </div>
                        <a href="{{ route('auditor.logs.show', $log->id) }}"
                            class="text-xs font-semibold text-sky-600 hover:underline whitespace-nowrap">Detail</a>
                    </li>
                @empty
                    <li class="px-5 py-6 text-center text-slate-500 text-sm">Belum ada aktivitas tercatat.</li>
                @endforelse
            </ul>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const canvas = document.getElementById('chart-aktivitas-harian');

            if (!canvas || typeof Chart === 'undefined') {
                return;
            }

            new Chart(canvas, {
                type: 'line',
                data: {
                    labels: @json($dailyLabels),
                    datasets: [{
                        label: 'Jumlah aktivitas',
                        data: @json($dailyTotals),
                        borderColor: '#0284c7',
                        backgroundColor: 'rgba(2, 132, 199, 0.12)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                            },
                        },
                    },
                },
            });
        });
    </script>
</x-mycomponents.layout>
