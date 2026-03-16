<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="App Clinic">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CMS-Royal-Klinik | Insight Transaksi Obat</title>
    <link href="{{ asset('storage/assets/royal_klinik.svg') }}" rel="shortcut icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(16, 185, 129, 0.10), transparent 20%),
                radial-gradient(circle at top right, rgba(5, 150, 105, 0.08), transparent 18%),
                linear-gradient(to bottom, #f7fffb, #f8fafc);
        }
    </style>
</head>

<body class="text-slate-900">
    <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="space-y-6">

            <section
                class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-emerald-900 to-emerald-700 px-6 py-7 shadow-xl">
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute -top-10 -right-10 h-40 w-40 rounded-full bg-white blur-2xl"></div>
                    <div class="absolute bottom-0 left-0 h-32 w-32 rounded-full bg-emerald-300 blur-2xl"></div>
                </div>

                <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-2xl">
                        <div
                            class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-emerald-100">
                            <i class="fa-solid fa-pills"></i>
                            Detail Transaksi Obat
                        </div>

                        <h1 class="mt-4 text-3xl font-black tracking-tight text-white md:text-4xl">
                            Insight Penjualan Obat
                        </h1>

                        <p class="mt-2 text-sm leading-6 text-emerald-100 md:text-base">
                            Monitoring transaksi obat langsung tanpa melalui alur poli.
                        </p>
                    </div>

                    <a href="{{ route('kasir.dashboard') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20">
                        <i class="fa-solid fa-arrow-left"></i>
                        Kembali ke Dashboard
                    </a>
                </div>
            </section>

            <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Transaksi</p>
                            <h3 class="mt-2 text-3xl font-extrabold text-slate-900">
                                {{ number_format($summary['total'], 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="rounded-2xl bg-emerald-100 p-3 text-emerald-600">
                            <i class="fa-solid fa-capsules text-lg"></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Transaksi Hari Ini</p>
                            <h3 class="mt-2 text-3xl font-extrabold text-slate-900">
                                {{ number_format($summary['hari_ini'], 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="rounded-2xl bg-blue-100 p-3 text-blue-600">
                            <i class="fa-solid fa-calendar-day text-lg"></i>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-slate-500">Total Pendapatan</p>
                            <h3 class="mt-2 text-3xl font-extrabold text-slate-900">
                                Rp {{ number_format($summary['pendapatan'], 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="rounded-2xl bg-amber-100 p-3 text-amber-600">
                            <i class="fa-solid fa-sack-dollar text-lg"></i>
                        </div>
                    </div>
                </div>
            </section>

            <section id="chartSection" data-chart-url="{{ route('kasir.insight.obat.chart') }}"
                class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Grafik Transaksi Obat</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Jumlah transaksi dan pendapatan penjualan obat berdasarkan periode.
                        </p>
                    </div>

                    <select id="filterChart"
                        class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="harian" {{ $filter === 'harian' ? 'selected' : '' }}>Harian</option>
                        <option value="mingguan" {{ $filter === 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                        <option value="bulanan" {{ $filter === 'bulanan' ? 'selected' : '' }}>Bulanan</option>
                        <option value="tahunan" {{ $filter === 'tahunan' ? 'selected' : '' }}>Tahunan</option>
                    </select>
                </div>

                <div class="mb-4">
                    <span
                        class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                        Periode:
                        <span id="chartRange" class="ml-1">{{ $chartData['range_text'] }}</span>
                    </span>
                </div>

                <script id="chartInitialData" type="application/json">@json($chartData)</script>

                <div class="overflow-hidden rounded-2xl bg-slate-50 p-4">
                    <div class="relative h-[380px] w-full md:h-[430px]">
                        <canvas id="transaksiChart"></canvas>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Daftar Transaksi Obat</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Cari transaksi berdasarkan kode, pasien, status, atau metode pembayaran.
                        </p>
                    </div>

                    <form method="GET" class="flex w-full max-w-md items-center gap-2">
                        <div class="relative flex-1">
                            <input type="text" name="search" value="{{ $search }}"
                                placeholder="Cari transaksi..."
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 pr-10 text-sm text-slate-700 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                            <i
                                class="fa-solid fa-magnifying-glass absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        </div>

                        <button type="submit"
                            class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                            Search
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Kode</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Pasien</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Tanggal</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Status</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($rows as $row)
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-3 text-sm font-semibold text-slate-800">
                                        {{ $row->kode_transaksi }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-600">
                                        {{ $row->nama_pasien ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-600">
                                        {{ \Carbon\Carbon::parse($row->tanggal_transaksi)->format('d-m-Y H:i') }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span
                                            class="rounded-full px-3 py-1 text-xs font-bold {{ $row->status === 'Sudah Bayar' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ $row->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold text-slate-800">
                                        Rp {{ number_format($row->total_setelah_diskon, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">
                                        Data transaksi tidak ditemukan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5">
                    {{ $rows->links() }}
                </div>
            </section>

        </div>
    </main>

    @vite(['resources/js/kasir/insight-transaksi.js'])
</body>

</html>
