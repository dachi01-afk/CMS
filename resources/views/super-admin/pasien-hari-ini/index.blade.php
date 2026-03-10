<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="App Clinic">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-authenticated" content="{{ auth()->check() ? 'true' : 'false' }}">
    <title>CMS-Royal-Klinik | Pasien Berkunjung Hari Ini</title>
    <link href="{{ asset('storage/assets/royal_klinik.svg') }}" rel="shortcut icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.1/flowbite.min.js" defer></script>

    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

    <style>
        body {
            font-family: inherit;
        }

        .glass-blur {
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .custom-scrollbar::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
    </style>
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="mx-auto w-full max-w-[1600px] p-4 md:p-6">
        <div class="space-y-6">

            {{-- Header Hero --}}
            <section
                class="relative overflow-hidden rounded-[30px] bg-gradient-to-r from-slate-950 via-emerald-950 to-emerald-700 p-6 shadow-xl md:p-8">
                <div class="absolute -right-16 -top-16 h-52 w-52 rounded-full bg-white/10 blur-3xl"></div>
                <div class="absolute bottom-0 right-24 h-28 w-28 rounded-full bg-emerald-300/10 blur-2xl"></div>

                <div class="relative z-10 flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                    <div class="max-w-3xl">
                        <span
                            class="inline-flex items-center rounded-full border border-white/15 bg-white/10 px-4 py-1.5 text-[11px] font-semibold uppercase tracking-[0.24em] text-white/90 glass-blur">
                            Super Admin Panel
                        </span>

                        <h1 class="mt-4 text-3xl font-bold leading-tight text-white md:text-4xl">
                            Pasien Berkunjung Hari Ini
                        </h1>

                        <p class="mt-3 max-w-2xl text-sm leading-6 text-emerald-50/80 md:text-base">
                            Pantau daftar kunjungan pasien operasional hari ini secara cepat, rapi, dan terpusat
                            dari halaman super admin.
                        </p>

                        <div class="mt-5 flex flex-wrap items-center gap-3">
                            <div
                                class="inline-flex items-center gap-2 rounded-2xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-medium text-white glass-blur">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2Z" />
                                </svg>
                                {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
                            </div>

                            <div
                                class="inline-flex items-center gap-2 rounded-2xl border border-lime-300/20 bg-lime-400/10 px-4 py-2 text-sm font-medium text-lime-100 glass-blur">
                                <span class="h-2.5 w-2.5 rounded-full bg-lime-300"></span>
                                Monitoring aktif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <a href="{{ route('super.admin.index') }}"
                            class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mr-2 h-4 w-4" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                            </svg>
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </section>

            {{-- Statistik --}}
            <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div
                    class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Pasien Hari Ini</p>
                            <h2 class="mt-3 text-3xl font-bold text-slate-800">{{ $totalPasienHariIni }}</h2>
                            <p class="mt-2 text-xs text-slate-500">Total kunjungan operasional hari ini</p>
                        </div>

                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 20h5V4H2v16h5m10 0v-4a3 3 0 0 0-3-3H10a3 3 0 0 0-3 3v4m10 0H7m10-10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-8 2a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Masih Berjalan</p>
                            <h2 class="mt-3 text-3xl font-bold text-amber-600">{{ $totalMenunggu }}</h2>
                            <p class="mt-2 text-xs text-slate-500">Status Pending, Waiting, Engaged, Payment</p>
                        </div>

                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6v6l4 2m6-2A10 10 0 1 1 2 12a10 10 0 0 1 20 0Z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div
                    class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-lg">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Selesai</p>
                            <h2 class="mt-3 text-3xl font-bold text-emerald-600">{{ $totalSelesai }}</h2>
                            <p class="mt-2 text-xs text-slate-500">Kunjungan dengan status Succeed</p>
                        </div>

                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </div>
                    </div>
                </div>
            </section>

            {{-- Search + Table --}}
            <section class="overflow-hidden rounded-[28px] border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 bg-slate-50/80 p-4 md:p-5">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">Daftar Pasien Hari Ini</h2>
                            <p class="mt-1 text-sm text-slate-500">
                                Cari berdasarkan nama pasien, nomor rekam medis, nomor HP, atau nomor antrian.
                            </p>
                        </div>

                        <form method="GET" action="{{ route('super.admin.pasien.hari.ini.index') }}"
                            class="flex w-full flex-col gap-3 md:flex-row xl:w-auto">
                            <div class="relative w-full md:min-w-[360px]">
                                <span
                                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                                    </svg>
                                </span>

                                <input type="text" name="search" value="{{ request('search') }}"
                                    placeholder="Cari nama pasien / no RM / no HP / no antrian"
                                    class="w-full rounded-2xl border border-slate-300 bg-white py-3 pl-11 pr-4 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100">
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="submit"
                                    class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                    Cari
                                </button>

                                <a href="{{ route('super.admin.pasien.hari.ini.index') }}"
                                    class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                                    Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="custom-scrollbar overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-slate-100">
                            <tr>
                                <th
                                    class="whitespace-nowrap px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    No
                                </th>
                                <th
                                    class="whitespace-nowrap px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Pasien
                                </th>
                                <th
                                    class="whitespace-nowrap px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    No RM
                                </th>
                                <th
                                    class="whitespace-nowrap px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    No HP
                                </th>
                                <th
                                    class="whitespace-nowrap px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    No Antrian
                                </th>
                                <th
                                    class="whitespace-nowrap px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Waktu Kunjungan
                                </th>
                                <th
                                    class="whitespace-nowrap px-4 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Status
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($pasienHariIni as $index => $item)
                                <tr class="transition hover:bg-emerald-50/40">
                                    <td class="px-4 py-4 text-sm font-medium text-slate-600">
                                        {{ $pasienHariIni->firstItem() + $index }}
                                    </td>

                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700">
                                                {{ strtoupper(substr($item->nama_pasien, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-slate-800">{{ $item->nama_pasien }}</p>
                                                <p class="text-xs text-slate-500">Pasien kunjungan hari ini</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-4 py-4 text-sm text-slate-600">
                                        {{ $item->no_rm ?: '-' }}
                                    </td>

                                    <td class="px-4 py-4 text-sm text-slate-600">
                                        {{ $item->no_hp ?: '-' }}
                                    </td>

                                    <td class="px-4 py-4 text-sm text-slate-600">
                                        <span
                                            class="inline-flex rounded-xl bg-slate-100 px-3 py-1 font-semibold text-slate-700">
                                            {{ $item->no_antrian ?: '-' }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-4 text-sm text-slate-600">
                                        {{ \Illuminate\Support\Carbon::parse($item->waktu_kunjungan)->format('d M Y, H:i') }}
                                    </td>

                                    <td class="px-4 py-4">
                                        @if ($item->status === 'Pending')
                                            <span
                                                class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                                Pending
                                            </span>
                                        @elseif ($item->status === 'Waiting')
                                            <span
                                                class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                                Waiting
                                            </span>
                                        @elseif ($item->status === 'Engaged')
                                            <span
                                                class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                                                Engaged
                                            </span>
                                        @elseif ($item->status === 'Payment')
                                            <span
                                                class="inline-flex rounded-full bg-purple-100 px-3 py-1 text-xs font-semibold text-purple-700">
                                                Payment
                                            </span>
                                        @elseif ($item->status === 'Succeed')
                                            <span
                                                class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                                Succeed
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                                {{ $item->status }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-16">
                                        <div class="flex flex-col items-center justify-center text-center">
                                            <div
                                                class="flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50 text-emerald-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M17 20h5V4H2v16h5m10 0v-4a3 3 0 0 0-3-3H10a3 3 0 0 0-3 3v4m10 0H7m10-10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-8 2a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z" />
                                                </svg>
                                            </div>

                                            <h3 class="mt-4 text-base font-semibold text-slate-700">
                                                Belum ada pasien yang berkunjung hari ini
                                            </h3>
                                            <p class="mt-1 max-w-md text-sm text-slate-500">
                                                Data kunjungan pasien untuk hari ini masih kosong. Nanti ketika ada
                                                aktivitas kunjungan, daftar pasien akan muncul di halaman ini.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($pasienHariIni->hasPages())
                    <div class="border-t border-slate-200 bg-white px-4 py-4">
                        {{ $pasienHariIni->links() }}
                    </div>
                @endif
            </section>
        </div>
    </main>
</body>

</html>