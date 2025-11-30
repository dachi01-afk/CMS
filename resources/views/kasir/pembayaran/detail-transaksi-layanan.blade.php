<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Detail Transaksi Layanan</title>

    {{-- Tailwind / asset utama project --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Font Awesome kalau perlu ikon --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2Pk8vE0m9QWwCqZ6M7DpiRnbV0cwweAV2Xp1S8MaqkfvxY6FQ4N3txF0A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body class="bg-slate-50">

    <div class="min-h-screen flex flex-col">

        {{-- Top bar sederhana --}}
        <header class="bg-white border-b border-slate-200 shadow-sm">
            <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] uppercase tracking-[0.15em] text-slate-400">
                        Kasir • Pembayaran • <span class="text-sky-500">Detail Transaksi Layanan</span>
                    </p>
                    <h1 class="text-xl md:text-2xl font-bold text-slate-800">
                        Detail Transaksi Layanan
                    </h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-1">
                        Kode Transaksi:
                        <span class="font-semibold text-sky-600">
                            {{ $summary->kode_transaksi ?? '-' }}
                        </span>
                    </p>
                </div>

                <div class="flex flex-col items-end gap-2">
                    @php
                        $status = $summary->status ?? 'Belum Bayar';
                        $statusClass = match ($status) {
                            'Sudah Bayar' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            'Dibatalkan' => 'bg-rose-100 text-rose-700 border-rose-200',
                            default => 'bg-amber-100 text-amber-700 border-amber-200',
                        };
                    @endphp

                    <span class="px-3 py-1 text-[11px] font-semibold rounded-full border {{ $statusClass }}">
                        {{ $status }}
                    </span>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="window.history.back()"
                            class="inline-flex items-center gap-2 px-3 py-1.5 text-xs md:text-sm font-medium
                                       text-slate-700 bg-slate-100 border border-slate-200 rounded-lg
                                       hover:bg-slate-200">
                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                            Kembali
                        </button>

                        @if ($status === 'Belum Bayar')
                            <a href="{{ route('kasir.proses.pembayaran.layanan', $summary->kode_transaksi) }}"
                                class="inline-flex items-center gap-2 px-3 py-1.5 text-xs md:text-sm font-semibold
              text-white bg-gradient-to-r from-sky-500 to-indigo-600 rounded-lg shadow
              hover:from-sky-600 hover:to-indigo-700">
                                <i class="fa-solid fa-credit-card text-[11px]"></i>
                                Proses Pembayaran
                            </a>
                        @endif

                    </div>
                </div>
            </div>
        </header>

        {{-- MAIN CONTENT --}}
        <main class="flex-1">
            <div class="max-w-6xl mx-auto px-4 py-6 space-y-6">

                {{-- Ringkasan 3 kartu --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Total Tagihan --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-sky-50 p-4 flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-sky-100 flex items-center justify-center">
                            <i class="fa-solid fa-receipt text-sky-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-[11px] uppercase text-slate-400 font-semibold">
                                Total Tagihan
                            </p>
                            <p class="text-lg font-bold text-slate-900">
                                Rp {{ number_format($summary->total_tagihan ?? 0, 0, ',', '.') }}
                            </p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Jumlah:
                                <span class="font-semibold">{{ $summary->jumlah_total ?? 1 }} x</span>
                            </p>
                        </div>
                    </div>

                    {{-- Metode Pembayaran --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-emerald-50 p-4 flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-emerald-100 flex items-center justify-center">
                            <i class="fa-solid fa-wallet text-emerald-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-[11px] uppercase text-slate-400 font-semibold">
                                Metode Pembayaran
                            </p>
                            <p class="text-base font-semibold text-slate-900">
                                {{ $summary->metode_pembayaran ?? '-' }}
                            </p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Kode:
                                <span class="font-mono text-slate-700">
                                    {{ $summary->kode_transaksi ?? '-' }}
                                </span>
                            </p>
                        </div>
                    </div>

                    {{-- Tanggal Transaksi --}}
                    <div class="bg-white rounded-2xl shadow-sm border border-purple-50 p-4 flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-purple-100 flex items-center justify-center">
                            <i class="fa-solid fa-calendar-day text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="text-[11px] uppercase text-slate-400 font-semibold">
                                Tanggal Transaksi
                            </p>
                            <p class="text-sm font-semibold text-slate-900">
                                @if ($summary->tanggal_transaksi)
                                    {{ \Carbon\Carbon::parse($summary->tanggal_transaksi)->translatedFormat('d F Y, H:i') }}
                                @else
                                    -
                                @endif
                            </p>
                            @php
                                $user = auth()->user();
                                $kasir = $user->kasir->nama_kasir ??= null;
                            @endphp
                            <p class="text-xs text-slate-500 mt-0.5">
                                Kasir:
                                <span class="font-semibold">
                                    {{ $kasir ?? (auth()->user()->username ?? '-') }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- 2 Kolom besar --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Kiri: Pasien + Kunjungan --}}
                    <div class="space-y-4 lg:col-span-1">
                        {{-- Data Pasien --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <div
                                    class="h-10 w-10 rounded-full bg-sky-100 flex items-center justify-center text-sky-600">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                                <div>
                                    <p class="text-[11px] uppercase text-slate-400 font-semibold">
                                        Data Pasien
                                    </p>
                                    <p class="text-base font-semibold text-slate-900">
                                        {{ $summary->pasien->nama_pasien ?? '-' }}
                                    </p>
                                </div>
                            </div>

                            <dl class="space-y-2 text-sm text-slate-700">
                                <div class="flex justify-between gap-2">
                                    <dt class="text-slate-500">No EMR</dt>
                                    <dd class="font-medium">
                                        {{ $summary->pasien->no_emr ?? '-' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-2">
                                    <dt class="text-slate-500">No RM / NIK</dt>
                                    <dd class="font-medium">
                                        {{ $summary->pasien->no_rm ?? ($summary->pasien->nik ?? '-') }}
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-2">
                                    <dt class="text-slate-500">Jenis Kelamin</dt>
                                    <dd class="font-medium">
                                        {{ $summary->pasien->jenis_kelamin ?? '-' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-2">
                                    <dt class="text-slate-500">Tanggal Lahir</dt>
                                    <dd class="font-medium">
                                        @if (!empty($summary->pasien->tanggal_lahir))
                                            {{ \Carbon\Carbon::parse($summary->pasien->tanggal_lahir)->translatedFormat('d F Y') }}
                                        @else
                                            -
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <dt class="text-slate-500">Alamat</dt>
                                    <dd class="font-medium text-right">
                                        {{ $summary->pasien->alamat ?? '-' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Info Kunjungan --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <div
                                    class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                                    <i class="fa-solid fa-stethoscope"></i>
                                </div>
                                <div>
                                    <p class="text-[11px] uppercase text-slate-400 font-semibold">
                                        Info Kunjungan
                                    </p>
                                    <p class="text-sm font-semibold text-slate-900">
                                        Nomor Antrian:
                                        <span class="font-mono">
                                            {{ $summary->kunjungan->nomor_antrian ?? '-' }}
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <dl class="space-y-2 text-sm text-slate-700">
                                <div class="flex justify-between gap-2">
                                    <dt class="text-slate-500">Tanggal Kunjungan</dt>
                                    <dd class="font-medium">
                                        @if (!empty($summary->kunjungan->tanggal_kunjungan))
                                            {{ \Carbon\Carbon::parse($summary->kunjungan->tanggal_kunjungan)->translatedFormat('d F Y') }}
                                        @else
                                            -
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-2">
                                    <dt class="text-slate-500">Poli</dt>
                                    <dd class="font-medium">
                                        {{ $summary->kunjungan->poli->nama_poli ?? '-' }}
                                    </dd>
                                </div>
                                <div class="flex justify-between gap-2">
                                    <dt class="text-slate-500">Dokter</dt>
                                    <dd class="font-medium">
                                        {{ $summary->kunjungan->dokter->nama_dokter ?? '-' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    {{-- Kanan: Detail Layanan + Bukti Pembayaran --}}
                    <div class="space-y-4 lg:col-span-2">
                        {{-- Detail Layanan --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center text-green-600">
                                        <i class="fa-solid fa-clipboard-list"></i>
                                    </div>
                                    <div>
                                        <p class="text-[11px] uppercase text-slate-400 font-semibold">
                                            Detail Layanan
                                        </p>
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ $summary->layanan->nama_layanan ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-x-auto rounded-xl border border-slate-100">
                                <table class="min-w-full text-sm text-slate-700">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-2 text-left">Layanan</th>
                                            <th class="px-4 py-2 text-left">Kategori</th>
                                            <th class="px-4 py-2 text-right">Harga Satuan</th>
                                            <th class="px-4 py-2 text-center">Jumlah</th>
                                            <th class="px-4 py-2 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        @foreach ($items as $item)
                                            @php
                                                $hargaSatuan = $item->layanan->harga_layanan ?? null;
                                                $jumlah = $item->jumlah ?? 1;
                                                $subtotal =
                                                    $item->total_tagihan ?? ($hargaSatuan ? $hargaSatuan * $jumlah : 0);
                                            @endphp
                                            <tr class="border-t border-slate-100">
                                                <td class="px-4 py-2">
                                                    {{ $item->layanan->nama_layanan ?? '-' }}
                                                </td>
                                                <td class="px-4 py-2">
                                                    {{ $item->layanan->kategoriLayanan->nama_kategori ?? '-' }}
                                                </td>
                                                <td class="px-4 py-2 text-right">
                                                    @if ($hargaSatuan)
                                                        Rp {{ number_format($hargaSatuan, 0, ',', '.') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td class="px-4 py-2 text-center">
                                                    {{ $jumlah }}
                                                </td>
                                                <td class="px-4 py-2 text-right font-semibold">
                                                    Rp {{ number_format($subtotal, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                    <tfoot class="bg-slate-50">
                                        <tr>
                                            <th colspan="4"
                                                class="px-4 py-3 text-right text-sm font-semibold text-slate-600">
                                                Grand Total
                                            </th>
                                            <th class="px-4 py-3 text-right text-lg font-bold text-sky-600">
                                                Rp {{ number_format($summary->total_tagihan ?? 0, 0, ',', '.') }}
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        {{-- Bukti Pembayaran --}}
                        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="h-10 w-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                                        <i class="fa-solid fa-file-invoice"></i>
                                    </div>
                                    <div>
                                        <p class="text-[11px] uppercase text-slate-400 font-semibold">
                                            Bukti Pembayaran
                                        </p>
                                        <p class="text-sm text-slate-700">
                                            @if ($summary->bukti_pembayaran)
                                                Bukti pembayaran telah diunggah.
                                            @else
                                                Belum ada bukti pembayaran yang diunggah.
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            @if ($summary->bukti_pembayaran)
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                    <div class="max-w-xs">
                                        <img src="{{ asset('storage/' . $summary->bukti_pembayaran) }}"
                                            alt="Bukti Pembayaran"
                                            class="rounded-xl border border-slate-200 shadow-sm">
                                    </div>
                                    <div class="flex flex-col gap-2 text-sm text-slate-600">
                                        <p>
                                            Verifikasi bukti pembayaran bila diperlukan atau simpan sebagai arsip.
                                        </p>
                                        <a href="{{ asset('storage/' . $summary->bukti_pembayaran) }}"
                                            target="_blank"
                                            class="inline-flex items-center gap-2 px-4 py-2 text-xs font-semibold
                                                  text-sky-700 bg-sky-50 rounded-lg border border-sky-100
                                                  hover:bg-sky-100">
                                            <i class="fa-solid fa-up-right-from-square text-[11px]"></i>
                                            Buka di Tab Baru
                                        </a>
                                    </div>
                                </div>
                            @else
                                <p class="text-sm text-slate-500">
                                    Pembayaran masih bisa diproses di halaman kasir.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </main>

        {{-- Footer kecil --}}
        <footer class="border-t border-slate-200 bg-white">
            <div class="max-w-6xl mx-auto px-4 py-3 text-[11px] text-slate-400 flex items-center justify-between">
                <span>Royal Klinik.id &mdash; Modul Kasir</span>
                <span>Generated by Sistem</span>
            </div>
        </footer>
    </div>

</body>

</html>
