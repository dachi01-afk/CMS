<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="App Clinic" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CMS-Royal-Klinik</title>
    <link href='{{ asset('storage/assets/royal_klinik.svg') }}' rel='shortcut icon'>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.1/flowbite.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900">
    <section class="py-6 md:py-10">
        <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">

            @php
                $restockObat = $dataHutang->restockObat ?? null;
                $supplier = $dataHutang->supplier ?? null;
                $metodePembayaran = $dataHutang->metodePembayaran ?? null;
                $itemsRestock = $restockObat?->restockObatDetail ?? collect();
            @endphp

            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-gray-800 dark:text-amber-300">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">
                            Detail Hutang
                        </h1>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                                <i class="fa-solid fa-hashtag opacity-70"></i>
                                <span>No Faktur:</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    {{ $dataHutang->no_faktur ?? '-' }}
                                </span>
                            </span>

                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                                <i class="fa-solid fa-wallet opacity-70"></i>
                                <span>Modul Hutang Kasir</span>
                            </span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('kasir.hutang') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-900 shadow-sm hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali
                </a>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

                {{-- KONTEN KIRI --}}
                <div class="space-y-6 lg:col-span-8">

                    {{-- CARD SUPPLIER --}}
                    <div
                        class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-gray-700 dark:text-emerald-300">
                                    <i class="fa-solid fa-truck-field"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ $supplier->nama_supplier ?? '-' }}
                                    </h2>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                        Informasi supplier dan referensi restock untuk kebutuhan pembayaran hutang.
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-gray-50 px-3 py-1 text-xs font-medium text-gray-700 ring-1 ring-gray-200 dark:bg-gray-900/30 dark:text-gray-200 dark:ring-gray-700">
                                    <i class="fa-regular fa-calendar"></i>
                                    @php $tglHutang = $dataHutang->tanggal_hutang; @endphp
                                    {{ $tglHutang ? \Carbon\Carbon::parse($tglHutang)->translatedFormat('l, d F Y') : '-' }}
                                </span>

                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-gray-50 px-3 py-1 text-xs font-medium text-gray-700 ring-1 ring-gray-200 dark:bg-gray-900/30 dark:text-gray-200 dark:ring-gray-700">
                                    <i class="fa-solid fa-hourglass-half"></i>
                                    Jatuh Tempo:
                                    {{ $dataHutang->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($dataHutang->tanggal_jatuh_tempo)->translatedFormat('d F Y') : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- INFORMASI HUTANG --}}
                    <div
                        class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                        <div
                            class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-gray-700 dark:text-amber-300">
                                    <i class="fa-solid fa-file-invoice"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Informasi Hutang
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Data utama hutang supplier</p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">No Faktur</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $dataHutang->no_faktur ?? '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal Hutang</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $dataHutang->tanggal_hutang ? \Carbon\Carbon::parse($dataHutang->tanggal_hutang)->translatedFormat('d F Y') : '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal Jatuh Tempo</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $dataHutang->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($dataHutang->tanggal_jatuh_tempo)->translatedFormat('d F Y') : '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal Pelunasan</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $dataHutang->tanggal_pelunasan ? \Carbon\Carbon::parse($dataHutang->tanggal_pelunasan)->translatedFormat('d F Y') : '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Metode Pembayaran</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $metodePembayaran->nama_metode ?? '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Status Hutang</p>
                                <p class="mt-1">
                                    @if (($dataHutang->status_hutang ?? '') === 'Sudah Lunas')
                                        <span
                                            class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-gray-900/30 dark:text-emerald-300">
                                            <i class="fa-solid fa-circle-check"></i> Sudah Lunas
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-gray-900/30 dark:text-amber-300">
                                            <i class="fa-solid fa-clock"></i> Belum Lunas
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- INFORMASI RESTOCK --}}
                    <div
                        class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                        <div
                            class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-50 text-cyan-700 dark:bg-gray-700 dark:text-cyan-300">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Informasi Restock
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Referensi transaksi restock obat
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">No Faktur Restock</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $restockObat->no_faktur ?? '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal Terima</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $restockObat?->tanggal_terima ? \Carbon\Carbon::parse($restockObat->tanggal_terima)->translatedFormat('d F Y H:i') : '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Tanggal Jatuh Tempo</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $restockObat?->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($restockObat->tanggal_jatuh_tempo)->translatedFormat('d F Y') : '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Status Restock</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $restockObat->status_restock ?? '-' }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Total Tagihan Restock</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    Rp {{ number_format($restockObat->total_tagihan ?? 0, 0, ',', '.') }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Supplier Restock</p>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $restockObat?->supplier?->nama_supplier ?? '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- DAFTAR ITEM RESTOCK --}}
                    <div
                        class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                        <div
                            class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 dark:bg-gray-700 dark:text-indigo-300">
                                    <i class="fa-solid fa-pills"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Daftar Item
                                        Restock</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Sumber: restock_obat_detail</p>
                                </div>
                            </div>

                            <span
                                class="rounded-full bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200 dark:bg-gray-900/30 dark:text-gray-200 dark:ring-gray-700">
                                {{ $itemsRestock?->count() ?? 0 }} item
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead
                                    class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-900/30 dark:text-gray-300">
                                    <tr class="text-center">
                                        <th class="px-5 py-3">Item</th>
                                        <th class="w-24 px-5 py-3">Qty</th>
                                        <th class="w-40 px-5 py-3">Harga Beli</th>
                                        <th class="w-40 px-5 py-3">Subtotal</th>
                                        <th class="w-40 px-5 py-3">Diskon</th>
                                        <th class="w-44 px-5 py-3">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-center">
                                    {{-- @php
                                        dd($itemsRestock);
                                    @endphp --}}
                                    @forelse ($itemsRestock as $item)
                                        <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-900/20">
                                            <td class="px-5 py-4">
                                                <div class="font-medium text-gray-900 dark:text-white">
                                                    {{ $item->obat?->nama_obat ?? '-' }}
                                                </div>
                                                <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $item->obat?->kode_obat ?? '-' }}
                                                </div>
                                            </td>

                                            <td class="px-5 py-4 text-center text-sm text-gray-800 dark:text-gray-200">
                                                x{{ $item->qty ?? 0 }}
                                            </td>

                                            <td
                                                class="px-5 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                                Rp {{ number_format($item->harga_beli ?? 0, 0, ',', '.') }}
                                            </td>

                                            <td
                                                class="px-5 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                                Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}
                                            </td>

                                            <td
                                                class="px-5 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                                Rp {{ number_format($item->diskon_amount ?? 0, 0, ',', '.') }}
                                            </td>

                                            <td class="px-5 py-4 text-right font-bold text-gray-900 dark:text-white">
                                                Rp {{ number_format($item->total_setelah_diskon ?? 0, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="px-5 py-5 text-sm text-gray-500 dark:text-gray-400"
                                                colspan="5">
                                                <span class="italic text-gray-400">Tidak ada item restock</span>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- SIDEBAR KANAN --}}
                <aside class="lg:col-span-4">
                    <div class="space-y-4 lg:sticky lg:top-6">

                        <div
                            class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                            <div class="flex items-center justify-between">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white">Ringkasan Hutang</h4>

                                @if (($dataHutang->status_hutang ?? '') === 'Sudah Lunas')
                                    <span
                                        class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-gray-900/30 dark:text-emerald-300">
                                        <i class="fa-solid fa-circle-check"></i> Lunas
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 dark:bg-gray-900/30 dark:text-amber-300">
                                        <i class="fa-solid fa-hourglass-half"></i> Belum Lunas
                                    </span>
                                @endif
                            </div>

                            <div class="mt-4 space-y-3">
                                <dl class="flex items-center justify-between gap-4">
                                    <dt class="text-sm text-gray-600 dark:text-gray-300">Total Tagihan Restock</dt>
                                    <dd class="text-sm font-semibold text-gray-900 dark:text-white">
                                        Rp {{ number_format($subTotalHutang ?? 0, 0, ',', '.') }}
                                    </dd>
                                </dl>

                                <dl class="flex items-center justify-between gap-4">
                                    <dt class="text-sm text-gray-600 dark:text-gray-300">Total Diskon</dt>
                                    <dd class="text-sm font-semibold text-gray-900 dark:text-white">
                                        Rp {{ number_format($totalDiskon ?? 0, 0, ',', '.') }}
                                    </dd>
                                </dl>

                                <dl class="flex items-center justify-between gap-4">
                                    <dt class="text-sm text-gray-600 dark:text-gray-300">Total Hutang</dt>
                                    <dd class="text-sm font-semibold text-gray-900 dark:text-white">
                                        Rp {{ number_format($totalHutang, 0, ',', '.') }}
                                    </dd>
                                </dl>

                                <dl
                                    class="flex items-center justify-between gap-4 border-t border-gray-200 pt-3 dark:border-gray-700">
                                    <dt class="text-sm font-semibold text-gray-900 dark:text-white">Status Pembayaran
                                    </dt>
                                    <dd class="text-sm font-bold text-gray-900 dark:text-white">
                                        {{ $dataHutang->status_hutang ?? '-' }}
                                    </dd>
                                </dl>
                            </div>
                        </div>

                        <div
                            class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                            <label class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white">
                                Metode Pembayaran
                            </label>

                            <div class="relative">
                                <i
                                    class="fa-solid fa-credit-card absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <select id="pilih-metode-pembayaran-hutang"
                                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-3 text-sm font-semibold text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    <option value="">Pilih metode pembayaran</option>
                                    @foreach ($dataMetodePembayaran as $metode)
                                        <option value="{{ $metode->id }}">
                                            {{ $metode->nama_metode }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @if (($dataHutang->status_hutang ?? '') !== 'Sudah Lunas')
                                <button type="button" id="btnLanjutPembayaranHutang"
                                    class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-primary-800">
                                    <i class="fa-solid fa-arrow-right"></i>
                                    Lanjutkan Pembayaran
                                </button>

                                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                    Pastikan data hutang sudah sesuai sebelum melanjutkan proses pembayaran.
                                </p>
                            @else
                                <div
                                    class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-900/20 dark:text-emerald-300">
                                    Hutang ini sudah dilunasi.
                                </div>
                            @endif
                        </div>

                    </div>
                </aside>
            </div>
        </div>
    </section>

    <div id="modalPembayaranCashHutang" tabindex="-1" aria-hidden="true"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
        <div class="relative w-full max-w-md">
            <div class="relative overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-200 p-5 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-gray-700 dark:text-emerald-300">
                            <i class="fa-solid fa-money-bill-wave"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                Konfirmasi Pembayaran Hutang (Cash)
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Masukkan nominal uang diterima untuk menghitung kembalian.
                            </p>
                        </div>
                    </div>

                    <button type="button" data-close-modal="cash-hutang"
                        class="rounded-xl p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-700 dark:hover:text-white">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form id="formPembayaranCashHutang"
                    action="{{ route('kasir.pembayaran.cash.hutang.obat', $dataHutang->no_faktur) }}" method="POST">
                    @csrf

                    <div class="space-y-4 p-5">
                        <input type="hidden" name="metode_pembayaran_id" id="metode-pembayaran-cash-hutang">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                Total Hutang
                            </label>
                            <div class="relative mt-2">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-300">Rp</span>
                                <input type="text" readonly
                                    value="{{ number_format($totalHutang ?? 0, 0, ',', '.') }}"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 pl-10 text-sm font-semibold text-gray-900 dark:border-gray-700 dark:bg-gray-900/30 dark:text-white">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                Tanggal Pelunasan
                            </label>
                            <input type="text"
                                value="{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}" disabled
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">

                            <input type="hidden" name="tanggal_pelunasan" value="{{ now()->format('Y-m-d') }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                Uang yang Diterima
                            </label>
                            <div class="relative mt-2">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-300">Rp</span>
                                <input type="text" name="uang_yang_diterima" id="uang_diterima_hutang"
                                    placeholder="Contoh: 4.000.000"
                                    class="placeholder:text-gray-500 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 pl-10 text-sm font-semibold text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                Uang Kembalian
                            </label>
                            <input type="text" name="kembalian" id="uang_kembalian_hutang" readonly
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm font-semibold text-gray-900 dark:border-gray-700 dark:bg-gray-900/30 dark:text-white">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-gray-200 p-5 dark:border-gray-700">
                        <button type="button" data-close-modal="cash-hutang"
                            class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-xl bg-primary-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-800">
                            Bayar Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="modalPembayaranTransferHutang" tabindex="-1" aria-hidden="true"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
        <div class="relative w-full max-w-2xl">
            <div class="relative overflow-hidden rounded-2xl bg-white shadow-xl dark:bg-gray-800">
                <div class="flex items-center justify-between border-b border-gray-200 p-5 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-700 dark:bg-gray-700 dark:text-indigo-300">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                Konfirmasi Pembayaran Hutang (Transfer)
                            </h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Unggah bukti transfer untuk pembayaran hutang.
                            </p>
                        </div>
                    </div>

                    <button type="button" data-close-modal="transfer-hutang"
                        class="rounded-xl p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-700 dark:hover:text-white">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form id="formPembayaranTransferHutang"
                    action="{{ route('kasir.pembayaran.transfer.hutang.obat', $dataHutang->no_faktur) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="space-y-4 p-5">
                        <input type="hidden" name="metode_pembayaran_id" id="metode-pembayaran-transfer-hutang">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                Total Hutang
                            </label>
                            <div class="relative mt-2">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-300">Rp</span>
                                <input type="text" readonly
                                    value="{{ number_format($dataHutang->total_hutang ?? 0, 0, ',', '.') }}"
                                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 pl-10 text-sm font-semibold text-gray-900 dark:border-gray-700 dark:bg-gray-900/30 dark:text-white">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                Tanggal Pelunasan
                            </label>
                            <input type="text" name="tanggal_pelunasan"
                                value="{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}" disabled
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-semibold text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                required>

                                <input type="hidden" name="tanggal_pelunasan" value="{{ now()->format('Y-m-d') }}">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                                Bukti Transfer
                            </label>

                            <label for="upload-bukti-hutang"
                                class="mt-2 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 p-4 text-center hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-900/30 dark:hover:bg-gray-700">

                                <div id="preview-bukti-pembayaran-hutang"
                                    class="flex h-56 w-full items-center justify-center overflow-hidden rounded-2xl bg-white dark:bg-gray-800">
                                    <div id="preview-placeholder-hutang"
                                        class="flex flex-col items-center justify-center gap-2 text-center">
                                        <i class="fa-regular fa-image text-3xl text-gray-400"></i>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">
                                            <span class="font-semibold">Klik untuk upload</span> bukti transfer
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            JPG / PNG / JPEG
                                        </p>
                                    </div>

                                    <img id="preview-image-hutang" src="" alt="Preview Bukti Transfer"
                                        class="hidden h-full w-full object-cover" />
                                </div>

                                <input id="upload-bukti-hutang" type="file" name="bukti_pembayaran"
                                    accept="image/*" class="hidden" required>
                            </label>

                            <p id="text-ganti-gambar-hutang"
                                class="mt-2 hidden text-center text-xs text-gray-500 dark:text-gray-400">
                                Klik area di atas untuk ganti gambar
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-gray-200 p-5 dark:border-gray-700">
                        <button type="button" data-close-modal="transfer-hutang"
                            class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-xl bg-primary-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-800">
                            Kirim Bukti Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const totalHutang = {{ (float) ($dataHutang->total_hutang ?? 0) }};
            const redirectUrl = "{{ route('kasir.hutang') }}";
            const csrfToken = $('meta[name="csrf-token"]').attr('content');

            function onlyDigits(value) {
                return value ? value.toString().replace(/[^\d]/g, '') : '';
            }

            function formatRupiah(value) {
                const number = Number(value || 0);
                return new Intl.NumberFormat('id-ID').format(number);
            }

            function hitungKembalian() {
                const diterima = parseFloat(onlyDigits($('#uang_diterima_hutang').val())) || 0;
                const kembalian = diterima - totalHutang;

                $('#uang_kembalian_hutang').val('Rp ' + formatRupiah(Math.max(kembalian, 0)));
            }

            function openModal($modal) {
                $modal.removeClass('hidden').addClass('flex');
                $('html').css('overflow', 'hidden');
            }

            function closeModal($modal) {
                $modal.addClass('hidden').removeClass('flex');
                $('html').css('overflow', '');
            }

            function closeAllModal() {
                closeModal($('#modalPembayaranCashHutang'));
                closeModal($('#modalPembayaranTransferHutang'));
            }

            $('#btnLanjutPembayaranHutang').on('click', function() {
                const metodeId = $('#pilih-metode-pembayaran-hutang').val();
                const metodeText = ($('#pilih-metode-pembayaran-hutang option:selected').text() || '')
                    .toLowerCase();

                if (!metodeId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Metode kosong',
                        text: 'Pilih metode pembayaran dulu.'
                    });
                    return;
                }

                $('#metode-pembayaran-cash-hutang').val(metodeId);
                $('#metode-pembayaran-transfer-hutang').val(metodeId);

                if (metodeText.includes('cash')) {
                    openModal($('#modalPembayaranCashHutang'));
                } else if (metodeText.includes('transfer')) {
                    openModal($('#modalPembayaranTransferHutang'));
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Metode belum dikenali',
                        text: 'Metode pembayaran belum dikenali sistem.'
                    });
                }
            });

            $(document).on('click', '[data-close-modal="cash-hutang"]', function() {
                closeModal($('#modalPembayaranCashHutang'));
            });

            $(document).on('click', '[data-close-modal="transfer-hutang"]', function() {
                closeModal($('#modalPembayaranTransferHutang'));
            });

            $('#modalPembayaranCashHutang, #modalPembayaranTransferHutang').on('click', function(e) {
                if (e.target === this) {
                    closeModal($(this));
                }
            });

            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAllModal();
                }
            });

            $('#uang_diterima_hutang').on('input', function() {
                const angka = onlyDigits($(this).val());
                $(this).val(angka ? formatRupiah(angka) : '');
                hitungKembalian();
            });

            $('#formPembayaranCashHutang').on('submit', function(e) {
                e.preventDefault();

                const $form = $(this);
                const submitBtn = $form.find('button[type="submit"]');
                const uangDiterima = parseFloat(onlyDigits($('#uang_diterima_hutang').val())) || 0;

                if (uangDiterima < totalHutang) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Uang kurang',
                        text: 'Nominal uang yang diterima belum cukup.'
                    });
                    return;
                }

                const formData = $form.serializeArray();
                formData.push({
                    name: '_token',
                    value: csrfToken
                });
                formData.push({
                    name: 'uang_yang_diterima',
                    value: uangDiterima
                });
                formData.push({
                    name: 'kembalian',
                    value: uangDiterima - totalHutang
                });

                submitBtn.prop('disabled', true).addClass('opacity-60 cursor-not-allowed');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: $.param(formData),
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        closeModal($('#modalPembayaranCashHutang'));

                        Swal.fire({
                            icon: 'success',
                            title: 'Pembayaran berhasil',
                            text: response.message ||
                                'Pembayaran hutang berhasil diproses.'
                        }).then(() => {
                            window.location.href = redirectUrl;
                        });
                    },
                    error: function(xhr) {
                        let message = 'Gagal memproses pembayaran.';

                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            } else if (xhr.responseJSON.errors) {
                                const firstKey = Object.keys(xhr.responseJSON.errors)[0];
                                if (firstKey && xhr.responseJSON.errors[firstKey][0]) {
                                    message = xhr.responseJSON.errors[firstKey][0];
                                }
                            }
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: message
                        });
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).removeClass(
                            'opacity-60 cursor-not-allowed');
                    }
                });
            });

            $('#formPembayaranTransferHutang').on('submit', function(e) {
                e.preventDefault();

                const $form = $(this);
                const submitBtn = $form.find('button[type="submit"]');
                const formData = new FormData(this);

                submitBtn.prop('disabled', true).addClass('opacity-60 cursor-not-allowed');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        closeModal($('#modalPembayaranTransferHutang'));

                        Swal.fire({
                            icon: 'success',
                            title: 'Bukti terkirim',
                            text: response.message ||
                                'Bukti pembayaran hutang berhasil dikirim.'
                        }).then(() => {
                            window.location.href = redirectUrl;
                        });
                    },
                    error: function(xhr) {
                        let message = 'Gagal mengirim bukti pembayaran.';

                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            } else if (xhr.responseJSON.errors) {
                                const firstKey = Object.keys(xhr.responseJSON.errors)[0];
                                if (firstKey && xhr.responseJSON.errors[firstKey][0]) {
                                    message = xhr.responseJSON.errors[firstKey][0];
                                }
                            }
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: message
                        });
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).removeClass(
                            'opacity-60 cursor-not-allowed');
                    }
                });
            });

            $('#upload-bukti-hutang').on('change', function(e) {
                const file = e.target.files[0];

                if (!file) {
                    $('#preview-image-hutang').attr('src', '').addClass('hidden');
                    $('#preview-placeholder-hutang').removeClass('hidden');
                    $('#text-ganti-gambar-hutang').addClass('hidden');
                    return;
                }

                if (!file.type.startsWith('image/')) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'File bukan gambar',
                        text: 'Silakan upload file gambar seperti JPG, JPEG, atau PNG.'
                    });

                    $(this).val('');
                    $('#preview-image-hutang').attr('src', '').addClass('hidden');
                    $('#preview-placeholder-hutang').removeClass('hidden');
                    $('#text-ganti-gambar-hutang').addClass('hidden');
                    return;
                }

                const reader = new FileReader();

                reader.onload = function(event) {
                    $('#preview-image-hutang')
                        .attr('src', event.target.result)
                        .removeClass('hidden');

                    $('#preview-placeholder-hutang').addClass('hidden');
                    $('#text-ganti-gambar-hutang').removeClass('hidden');
                };

                reader.readAsDataURL(file);
            });

        });
    </script>
</body>

</html>
