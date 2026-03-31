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
</head>

<body class="bg-gray-50 dark:bg-gray-900">
    <section class="py-6 md:py-10">
        <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">

            <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-3">
                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-50 text-primary-700 dark:bg-gray-800 dark:text-primary-300">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">
                            Detail Transaksi
                        </h1>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                                <i class="fa-solid fa-hashtag opacity-70"></i>
                                <span>Kode:</span>
                                <span class="font-semibold text-gray-900 dark:text-white">
                                    {{ $dataPembayaran->kode_transaksi ?? '-' }}
                                </span>
                            </span>
                            <span
                                class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-1 ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                                <i class="fa-solid fa-shield-heart opacity-70"></i>
                                <span>Modul Kasir Klinik</span>
                            </span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('kasir.pembayaran') }}"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-900 shadow-sm hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    <i class="fa-solid fa-arrow-left"></i>
                    Kembali
                </a>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

                <div class="space-y-6 lg:col-span-8">

                    <div
                        class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 dark:bg-gray-700 dark:text-emerald-300">
                                    <i class="fa-solid fa-user-injured"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ data_get($dataPembayaran, 'emr.kunjungan.pasien.nama_pasien', '-') }}
                                    </h2>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                        Informasi kunjungan & poli untuk kebutuhan pembayaran kasir.
                                    </p>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-gray-50 px-3 py-1 text-xs font-medium text-gray-700 ring-1 ring-gray-200 dark:bg-gray-900/30 dark:text-gray-200 dark:ring-gray-700">
                                    <i class="fa-regular fa-calendar"></i>
                                    @php $tgl = data_get($dataPembayaran, 'emr.kunjungan.tanggal_kunjungan'); @endphp
                                    {{ $tgl ? \Carbon\Carbon::parse($tgl)->timezone('Asia/Jakarta')->translatedFormat('l, d F Y') : '-' }}
                                </span>

                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-gray-50 px-3 py-1 text-xs font-medium text-gray-700 ring-1 ring-gray-200 dark:bg-gray-900/30 dark:text-gray-200 dark:ring-gray-700">
                                    <i class="fa-solid fa-stethoscope"></i>
                                    {{ data_get($dataPembayaran, 'emr.kunjungan.poli.nama_poli', '-') }}
                                </span>
                            </div>
                        </div>
                    </div>

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
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Daftar Obat</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Sumber: pembayaran_detail</p>
                                </div>
                            </div>
                            <span
                                class="rounded-full bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200 dark:bg-gray-900/30 dark:text-gray-200 dark:ring-gray-700">
                                {{ $itemsObat?->count() ?? 0 }} item
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead
                                    class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-900/30 dark:text-gray-300">
                                    <tr>
                                        <th class="px-5 py-3">Item</th>
                                        <th class="w-24 px-5 py-3 text-center">Qty</th>
                                        <th class="w-28 px-5 py-3 text-center">Diskon (%)</th>
                                        <th class="w-40 px-5 py-3 text-right">Subtotal</th>
                                        <th class="w-44 px-5 py-3 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($itemsObat as $item)
                                        <tr class="item-row hover:bg-gray-50/70 dark:hover:bg-gray-900/20"
                                            data-detail-id="{{ $item->id }}"
                                            data-subtotal="{{ (float) ($item->subtotal ?? 0) }}">
                                            <td class="px-5 py-4">
                                                <div class="font-medium text-gray-900 dark:text-white">
                                                    {{ $item->nama_item }}
                                                </div>
                                                <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                    Rp {{ number_format($item->harga ?? 0, 0, ',', '.') }} / item
                                                </div>
                                            </td>

                                            <td class="px-5 py-4 text-center text-sm text-gray-800 dark:text-gray-200">
                                                x{{ $item->qty ?? 1 }}
                                            </td>

                                            <td class="px-5 py-4 text-center">
                                                <input type="number" min="0" max="100"
                                                    value="{{ $item->diskon_input ?? 0 }}"
                                                    {{ $diskonLocked ?? false ? 'disabled' : '' }}
                                                    class="diskon-item w-20 rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-right text-sm font-semibold text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white {{ $diskonLocked ?? false ? 'opacity-60 cursor-not-allowed' : '' }}" />
                                            </td>

                                            <td
                                                class="px-5 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                                Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}
                                            </td>

                                            <td class="px-5 py-4 text-right font-bold text-gray-900 dark:text-white">
                                                <span class="row-total-display">
                                                    Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}
                                                </span>
                                                <div
                                                    class="row-discount-note mt-0.5 hidden text-xs font-medium text-emerald-700 dark:text-emerald-300">
                                                    Hemat Rp 0
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="px-5 py-5 text-sm text-gray-500 dark:text-gray-400"
                                                colspan="5">
                                                <span class="italic text-gray-400">Tidak ada obat</span>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div
                        class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                        <div
                            class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 text-amber-700 dark:bg-gray-700 dark:text-amber-300">
                                    <i class="fa-solid fa-hand-holding-medical"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Daftar Layanan
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Sumber: pembayaran_detail</p>
                                </div>
                            </div>
                            <span
                                class="rounded-full bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200 dark:bg-gray-900/30 dark:text-gray-200 dark:ring-gray-700">
                                {{ $itemsLayanan?->count() ?? 0 }} item
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead
                                    class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-900/30 dark:text-gray-300">
                                    <tr>
                                        <th class="px-5 py-3">Item</th>
                                        <th class="w-24 px-5 py-3 text-center">Qty</th>
                                        <th class="w-28 px-5 py-3 text-center">Diskon (%)</th>
                                        <th class="w-40 px-5 py-3 text-right">Subtotal</th>
                                        <th class="w-44 px-5 py-3 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($itemsLayanan as $item)
                                        <tr class="item-row hover:bg-gray-50/70 dark:hover:bg-gray-900/20"
                                            data-detail-id="{{ $item->id }}"
                                            data-subtotal="{{ (float) ($item->subtotal ?? 0) }}">
                                            <td class="px-5 py-4">
                                                <div class="font-medium text-gray-900 dark:text-white">
                                                    {{ $item->nama_item }}
                                                </div>
                                                <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                    Rp {{ number_format($item->harga ?? 0, 0, ',', '.') }} / item
                                                </div>
                                            </td>

                                            <td class="px-5 py-4 text-center text-sm text-gray-800 dark:text-gray-200">
                                                x{{ $item->qty ?? 1 }}
                                            </td>

                                            <td class="px-5 py-4 text-center">
                                                <input type="number" min="0" max="100"
                                                    value="{{ $item->diskon_input ?? 0 }}"
                                                    {{ $diskonLocked ?? false ? 'disabled' : '' }}
                                                    class="diskon-item w-20 rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-right text-sm font-semibold text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white {{ $diskonLocked ?? false ? 'opacity-60 cursor-not-allowed' : '' }}" />
                                            </td>

                                            <td
                                                class="px-5 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                                Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}
                                            </td>

                                            <td class="px-5 py-4 text-right font-bold text-gray-900 dark:text-white">
                                                <span class="row-total-display">
                                                    Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}
                                                </span>
                                                <div
                                                    class="row-discount-note mt-0.5 hidden text-xs font-medium text-emerald-700 dark:text-emerald-300">
                                                    Hemat Rp 0
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="px-5 py-5 text-sm text-gray-500 dark:text-gray-400"
                                                colspan="5">
                                                <span class="italic text-gray-400">Tidak ada layanan</span>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div
                        class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                        <div
                            class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-cyan-50 text-cyan-700 dark:bg-gray-700 dark:text-cyan-300">
                                    <i class="fa-solid fa-flask"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Order Lab</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Sumber: pembayaran_detail</p>
                                </div>
                            </div>
                            <span
                                class="rounded-full bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200 dark:bg-gray-900/30 dark:text-gray-200 dark:ring-gray-700">
                                {{ $itemsLab?->count() ?? 0 }} item
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead
                                    class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-900/30 dark:text-gray-300">
                                    <tr>
                                        <th class="px-5 py-3">Item</th>
                                        <th class="w-24 px-5 py-3 text-center">Qty</th>
                                        <th class="w-28 px-5 py-3 text-center">Diskon (%)</th>
                                        <th class="w-40 px-5 py-3 text-right">Subtotal</th>
                                        <th class="w-44 px-5 py-3 text-right">Total</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($itemsLab as $item)
                                        <tr class="item-row hover:bg-gray-50/70 dark:hover:bg-gray-900/20"
                                            data-detail-id="{{ $item->id }}"
                                            data-subtotal="{{ (float) ($item->subtotal ?? 0) }}">
                                            <td class="px-5 py-4">
                                                <div class="font-medium text-gray-900 dark:text-white">
                                                    {{ $item->nama_item }}
                                                </div>
                                                <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                    Rp {{ number_format($item->harga ?? 0, 0, ',', '.') }} / item
                                                </div>
                                            </td>

                                            <td class="px-5 py-4 text-center text-sm text-gray-800 dark:text-gray-200">
                                                x{{ $item->qty ?? 1 }}
                                            </td>

                                            <td class="px-5 py-4 text-center">
                                                <input type="number" min="0" max="100"
                                                    value="{{ $item->diskon_input ?? 0 }}"
                                                    {{ $diskonLocked ?? false ? 'disabled' : '' }}
                                                    class="diskon-item w-20 rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-right text-sm font-semibold text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white {{ $diskonLocked ?? false ? 'opacity-60 cursor-not-allowed' : '' }}" />
                                            </td>

                                            <td
                                                class="px-5 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                                Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}
                                            </td>

                                            <td class="px-5 py-4 text-right font-bold text-gray-900 dark:text-white">
                                                <span class="row-total-display">
                                                    Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}
                                                </span>
                                                <div
                                                    class="row-discount-note mt-0.5 hidden text-xs font-medium text-emerald-700 dark:text-emerald-300">
                                                    Hemat Rp 0
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="px-5 py-5 text-sm text-gray-500 dark:text-gray-400"
                                                colspan="5">
                                                <span class="italic text-gray-400">Tidak ada order lab</span>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div
                        class="rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                        <div
                            class="flex items-center justify-between border-b border-gray-200 px-5 py-4 dark:border-gray-700">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-700 dark:bg-gray-700 dark:text-rose-300">
                                    <i class="fa-solid fa-x-ray"></i>
                                </div>
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-white">Order Radiologi
                                    </h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Sumber: pembayaran_detail</p>
                                </div>
                            </div>
                            <span
                                class="rounded-full bg-gray-50 px-3 py-1 text-xs font-semibold text-gray-700 ring-1 ring-gray-200 dark:bg-gray-900/30 dark:text-gray-200 dark:ring-gray-700">
                                {{ $itemsRadiologi?->count() ?? 0 }} item
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead
                                    class="bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-900/30 dark:text-gray-300">
                                    <tr>
                                        <th class="px-5 py-3">Item</th>
                                        <th class="w-24 px-5 py-3 text-center">Qty</th>
                                        <th class="w-28 px-5 py-3 text-center">Diskon (%)</th>
                                        <th class="w-40 px-5 py-3 text-right">Subtotal</th>
                                        <th class="w-44 px-5 py-3 text-right">Total</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($itemsRadiologi as $item)
                                        <tr class="item-row hover:bg-gray-50/70 dark:hover:bg-gray-900/20"
                                            data-detail-id="{{ $item->id }}"
                                            data-subtotal="{{ (float) ($item->subtotal ?? 0) }}">
                                            <td class="px-5 py-4">
                                                <div class="font-medium text-gray-900 dark:text-white">
                                                    {{ $item->nama_item }}
                                                </div>
                                                <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                    Rp {{ number_format($item->harga ?? 0, 0, ',', '.') }} / item
                                                </div>
                                            </td>

                                            <td class="px-5 py-4 text-center text-sm text-gray-800 dark:text-gray-200">
                                                x{{ $item->qty ?? 1 }}
                                            </td>

                                            <td class="px-5 py-4 text-center">
                                                <input type="number" min="0" max="100"
                                                    value="{{ $item->diskon_input ?? 0 }}"
                                                    {{ $diskonLocked ?? false ? 'disabled' : '' }}
                                                    class="diskon-item w-20 rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-right text-sm font-semibold text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white {{ $diskonLocked ?? false ? 'opacity-60 cursor-not-allowed' : '' }}" />
                                            </td>

                                            <td
                                                class="px-5 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                                Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}
                                            </td>

                                            <td class="px-5 py-4 text-right font-bold text-gray-900 dark:text-white">
                                                <span class="row-total-display">
                                                    Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}
                                                </span>
                                                <div
                                                    class="row-discount-note mt-0.5 hidden text-xs font-medium text-emerald-700 dark:text-emerald-300">
                                                    Hemat Rp 0
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="px-5 py-5 text-sm text-gray-500 dark:text-gray-400"
                                                colspan="5">
                                                <span class="italic text-gray-400">Tidak ada order radiologi</span>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <aside class="lg:col-span-4">
                    <div class="space-y-4 lg:sticky lg:top-6">
                        <div
                            class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                            <div class="flex items-center justify-between">
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white">Ringkasan Transaksi
                                </h4>
                                <span
                                    class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-gray-900/30 dark:text-emerald-300 dark:ring-gray-700">
                                    <i class="fa-solid fa-circle-check"></i> Siap dibayar
                                </span>
                            </div>

                            <div class="mt-4 space-y-3">
                                <dl class="flex items-center justify-between gap-4">
                                    <dt class="text-sm text-gray-600 dark:text-gray-300">Total Harga</dt>
                                    <dd id="total_harga_display"
                                        class="text-sm font-semibold text-gray-900 dark:text-white">
                                        Rp {{ number_format($totalAwal ?? 0, 0, ',', '.') }}
                                    </dd>
                                </dl>

                                <dl class="flex items-center justify-between gap-4">
                                    <dt class="text-sm text-gray-600 dark:text-gray-300">Total Diskon</dt>
                                    <dd id="potongan_display"
                                        class="text-sm font-semibold text-gray-900 dark:text-white">
                                        Rp 0
                                    </dd>
                                </dl>

                                <dl
                                    class="flex items-center justify-between gap-4 border-t border-gray-200 pt-3 dark:border-gray-700">
                                    <dt class="text-sm font-semibold text-gray-900 dark:text-white">Total Bayar</dt>
                                    <dd id="total_tagihan_display"
                                        class="text-lg font-bold text-gray-900 dark:text-white">
                                        Rp {{ number_format($totalAwal ?? 0, 0, ',', '.') }}
                                    </dd>
                                </dl>

                                <input type="hidden" id="total_setelah_diskon" value="{{ $totalAwal ?? 0 }}">
                                <input type="hidden" id="total_tagihan_awal" value="{{ $totalAwal ?? 0 }}">
                            </div>
                        </div>

                        <div
                            class="rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                            <label class="mb-2 block text-sm font-semibold text-gray-900 dark:text-white">
                                Metode Pembayaran
                            </label>

                            <div class="relative">
                                <i class="fa-solid fa-credit-card absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <select id="pilih-metode-pembayaran"
                                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-3 text-sm font-semibold text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    @foreach ($dataMetodePembayaran as $metodePembayaran)
                                        <option value="{{ $metodePembayaran->id }}">
                                            {{ $metodePembayaran->nama_metode }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="diskon_approval_box"
                                class="mt-4 rounded-2xl border border-gray-200 bg-gray-50 p-4 text-sm text-gray-700 dark:border-gray-700 dark:bg-gray-900/30 dark:text-gray-200">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold">Status Diskon</span>
                                    <span id="diskon_status_badge"
                                        class="rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-800 dark:bg-gray-700 dark:text-gray-100">
                                        Normal
                                    </span>
                                </div>

                                <p id="diskon_status_desc" class="mt-2 text-xs text-gray-600 dark:text-gray-300">
                                    Jika ada diskon per item, wajib minta approval Manager (Super Admin) dulu.
                                </p>

                                <button type="button" id="btnMintaApprovalDiskon"
                                    class="mt-3 hidden w-full rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-700">
                                    <i class="fa-solid fa-user-check mr-2"></i>
                                    Minta Approval Diskon
                                </button>
                            </div>

                            <button type="button" id="btnLanjutPembayaran"
                                class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-700 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-primary-800">
                                <i class="fa-solid fa-arrow-right"></i>
                                Lanjutkan Pembayaran
                            </button>

                            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                Pastikan total dan diskon sudah sesuai sebelum melanjutkan.
                            </p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <div id="pembayaranCash" tabindex="-1" aria-hidden="true"
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
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Konfirmasi Pembayaran
                                (Cash)</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Masukkan uang diterima untuk hitung
                                kembalian.</p>
                        </div>
                    </div>

                    <button type="button" data-close-modal="cash"
                        class="rounded-xl p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-700 dark:hover:text-white">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form id="formPembayaranCash" action="{{ route('kasir.pembayaran.cash') }}" method="POST">
                    @csrf
                    <div class="space-y-4 p-5">
                        <input type="hidden" name="id" value="{{ $dataPembayaran->id }}">
                        <input type="hidden" name="metode_pembayaran_id" id="metode-pembayaran-cash"
                            value="">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Total
                                Tagihan</label>
                            <div class="relative mt-2">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-300">Rp</span>
                                <input type="text" readonly
                                    value="{{ number_format($totalAwal ?? 0, 0, ',', '.') }}"
                                    class="total_tagihan_input w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 pl-10 text-sm font-semibold text-gray-900 dark:border-gray-700 dark:bg-gray-900/30 dark:text-white" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Uang yang
                                Diterima</label>
                            <div class="relative mt-2">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-300">Rp</span>
                                <input type="text" name="uang_yang_diterima" id="uang_diterima"
                                    placeholder="Contoh: 100.000"
                                    class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 pl-10 text-sm font-semibold text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            </div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Input otomatis diformat rupiah.
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Uang
                                Kembalian</label>
                            <input type="text" name="kembalian" id="uang_kembalian" readonly
                                class="mt-2 w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm font-semibold text-gray-900 dark:border-gray-700 dark:bg-gray-900/30 dark:text-white" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-gray-200 p-5 dark:border-gray-700">
                        <button type="button" data-close-modal="cash"
                            class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                            Batal
                        </button>
                        <button id="btnSubmitPembayaran" type="submit"
                            class="rounded-xl bg-primary-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-800">
                            Bayar Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="pembayaranTransfer" tabindex="-1" aria-hidden="true"
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
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Konfirmasi Pembayaran
                                (Transfer)</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Unggah bukti transfer untuk verifikasi.
                            </p>
                        </div>
                    </div>

                    <button type="button" data-close-modal="transfer"
                        class="rounded-xl p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-900 dark:hover:bg-gray-700 dark:hover:text-white">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form id="formPembayaranTransfer" action="{{ route('kasir.pembayaran.transfer') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-4 p-5">
                        <input type="hidden" name="id" value="{{ $dataPembayaran->id }}">
                        <input type="hidden" name="metode_pembayaran_id" id="metode-pembayaran-transfer"
                            value="">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Total
                                Tagihan</label>
                            <div class="relative mt-2">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-300">Rp</span>
                                <input type="text" readonly
                                    value="{{ number_format($totalAwal ?? 0, 0, ',', '.') }}"
                                    class="total_tagihan_input w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 pl-10 text-sm font-semibold text-gray-900 dark:border-gray-700 dark:bg-gray-900/30 dark:text-white" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Bukti
                                Transfer</label>

                            <label for="upload"
                                class="mt-2 flex cursor-pointer flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 bg-gray-50 p-6 text-center hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-900/30 dark:hover:bg-gray-700">
                                <div id="preview-bukti-pembayaran"
                                    class="flex w-full flex-col items-center justify-center gap-2">
                                    <i class="fa-regular fa-image text-3xl text-gray-400"></i>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                        <span class="font-semibold">Klik untuk upload</span> atau drag & drop
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">JPG/PNG/GIF • disarankan jelas
                                        & terbaca</p>
                                </div>
                                <input id="upload" type="file" class="hidden" accept="image/*"
                                    name="bukti_pembayaran" />
                            </label>

                            <p id="text-ganti-gambar"
                                class="mt-2 hidden text-center text-xs text-gray-500 dark:text-gray-400">
                                Klik area di atas untuk ganti gambar
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 border-t border-gray-200 p-5 dark:border-gray-700">
                        <button type="button" data-close-modal="transfer"
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
        window.__SERVER_APPROVAL_STATUS__ = @json($approvalStatus ?? null);
        window.__SERVER_APPROVAL_ITEMS__ = @json($approvalItemsRaw ?? []);
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

            const uangDiterimaInput = document.getElementById('uang_diterima');
            const uangKembalianInput = document.getElementById('uang_kembalian');

            const pilihMetode = document.getElementById("pilih-metode-pembayaran");
            const btnLanjut = document.getElementById("btnLanjutPembayaran");

            const modalCash = document.getElementById('pembayaranCash');
            const modalTransfer = document.getElementById('pembayaranTransfer');

            const totalAwalInput = document.getElementById('total_tagihan_awal');
            const totalHargaDisplay = document.getElementById('total_harga_display');
            const potonganDisplay = document.getElementById('potongan_display');
            const totalTagihanDisplay = document.getElementById('total_tagihan_display');
            const totalSetelahDiskonHidden = document.getElementById('total_setelah_diskon');
            const totalInputsAll = document.querySelectorAll('.total_tagihan_input');

            const btnMintaApproval = document.getElementById('btnMintaApprovalDiskon');
            const badge = document.getElementById('diskon_status_badge');
            const desc = document.getElementById('diskon_status_desc');

            const urlStatus = "{{ route('kasir.pembayaran.diskon.status', $dataPembayaran->id) }}";
            const urlRequest = "{{ route('kasir.pembayaran.diskon.request', $dataPembayaran->id) }}";

            const totalAwal = parseFloat(totalAwalInput?.value || '0') || 0;

            let approvalStatus = window.__SERVER_APPROVAL_STATUS__ || null;
            let approvalSnapshotStr = JSON.stringify(normalizeDiskonItems(window.__SERVER_APPROVAL_ITEMS__ || []));

            function onlyDigits(value) {
                return value ? String(value).replace(/[^\d]/g, '') : '';
            }

            function formatRupiah(value) {
                const n = Number(value) || 0;
                return new Intl.NumberFormat("id-ID").format(n);
            }

            function clamp(n, min, max) {
                return Math.min(Math.max(n, min), max);
            }

            function getFirstErrorMessage(data) {
                if (!data) return null;
                if (data.message) return data.message;
                if (data.errors && typeof data.errors === 'object') {
                    const firstKey = Object.keys(data.errors)[0];
                    if (firstKey && Array.isArray(data.errors[firstKey]) && data.errors[firstKey][0]) {
                        return data.errors[firstKey][0];
                    }
                }
                return null;
            }

            function normalizeDiskonItems(items) {
                const out = (items || [])
                    .map(x => ({
                        id: Number(x.id) || 0,
                        persen: clamp(Number(x.persen) || 0, 0, 100),
                    }))
                    .filter(x => x.id > 0 && x.persen > 0)
                    .sort((a, b) => a.id - b.id);

                return out;
            }

            function getCurrentNormalizedDiskonItems() {
                return normalizeDiskonItems(window.__DISKON_ITEMS__ || []);
            }

            function hitungKembalian() {
                const totalBayar = parseFloat(totalSetelahDiskonHidden?.value || 0) || 0;
                const diterima = parseFloat(onlyDigits(uangDiterimaInput?.value)) || 0;
                const kembalian = diterima - totalBayar;

                if (uangKembalianInput) {
                    uangKembalianInput.value = "Rp " + formatRupiah(Math.max(kembalian, 0));
                }
            }

            function setDiskonInputsDisabled(disabled) {
                document.querySelectorAll('.diskon-item').forEach(inp => {
                    inp.disabled = !!disabled;
                    if (disabled) {
                        inp.classList.add('opacity-60', 'cursor-not-allowed');
                    } else {
                        inp.classList.remove('opacity-60', 'cursor-not-allowed');
                    }
                });
            }

            function setBadge(status) {
                if (!badge || !desc) return;

                if (!status) {
                    badge.textContent = 'Normal';
                    badge.className =
                        'rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-800 dark:bg-gray-700 dark:text-gray-100';
                    desc.textContent = 'Jika ada diskon per item, wajib minta approval Manager dulu.';
                    return;
                }

                if (status === 'pending') {
                    badge.textContent = 'Menunggu Approve';
                    badge.className =
                        'rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/30 dark:text-amber-200';
                    desc.textContent = 'Diskon sudah diminta. Menunggu Manager approve.';
                } else if (status === 'approved') {
                    badge.textContent = 'Approved';
                    badge.className =
                        'rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200';
                    desc.textContent = 'Diskon sudah disetujui Manager. Kasir bisa lanjut pembayaran.';
                } else if (status === 'rejected') {
                    badge.textContent = 'Pengajuan Ditolak';
                    badge.className =
                        'rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800 dark:bg-rose-900/30 dark:text-rose-200';
                    desc.textContent = 'Pengajuan diskon ditolak Manager. Semua diskon dikembalikan ke 0.';
                }
            }

            function applyDiskonItems(items = []) {
                const map = {};

                normalizeDiskonItems(items).forEach(item => {
                    map[String(item.id)] = item.persen;
                });

                document.querySelectorAll('.item-row').forEach(row => {
                    const detailId = String(row.dataset.detailId || '');
                    const input = row.querySelector('.diskon-item');
                    if (!input) return;
                    input.value = map[detailId] ?? 0;
                });

                recalcAll(false);
            }

            function recalcAll(manageUi = true) {
                const rows = document.querySelectorAll('.item-row');
                let totalBase = 0;
                let totalAfter = 0;
                const diskonItems = [];

                rows.forEach(row => {
                    const detailId = row.dataset.detailId;
                    const baseSubtotal = parseFloat(row.dataset.subtotal || '0') || 0;
                    totalBase += baseSubtotal;

                    const input = row.querySelector('.diskon-item');
                    let persen = parseFloat(input?.value || '0') || 0;
                    persen = clamp(persen, 0, 100);
                    if (input && Number(input.value) !== persen) input.value = persen;

                    const diskonNominal = baseSubtotal * (persen / 100);
                    const after = Math.max(baseSubtotal - diskonNominal, 0);
                    totalAfter += after;

                    const totalDisplay = row.querySelector('.row-total-display');
                    if (totalDisplay) {
                        totalDisplay.textContent = "Rp " + formatRupiah(after);
                    }

                    const note = row.querySelector('.row-discount-note');
                    if (note) {
                        if (diskonNominal > 0) {
                            note.classList.remove('hidden');
                            note.textContent = "Hemat Rp " + formatRupiah(diskonNominal);
                        } else {
                            note.classList.add('hidden');
                            note.textContent = "Hemat Rp 0";
                        }
                    }

                    if (detailId) {
                        diskonItems.push({
                            id: Number(detailId),
                            persen: persen
                        });
                    }
                });

                const potongan = Math.max(totalBase - totalAfter, 0);

                if (totalHargaDisplay) totalHargaDisplay.textContent = "Rp " + formatRupiah(totalBase || totalAwal);
                if (potonganDisplay) potonganDisplay.textContent = "Rp " + formatRupiah(potongan);
                if (totalTagihanDisplay) totalTagihanDisplay.textContent = "Rp " + formatRupiah(totalAfter);
                if (totalSetelahDiskonHidden) totalSetelahDiskonHidden.value = totalAfter;
                totalInputsAll.forEach(inp => inp.value = formatRupiah(totalAfter));

                window.__DISKON_ITEMS__ = diskonItems;

                if (manageUi) {
                    const normalized = getCurrentNormalizedDiskonItems();

                    if (approvalStatus === 'pending') {
                        setBadge('pending');
                        setDiskonInputsDisabled(true);
                        if (btnMintaApproval) btnMintaApproval.classList.add('hidden');
                    } else if (approvalStatus === 'approved') {
                        setBadge('approved');
                        setDiskonInputsDisabled(true);
                        if (btnMintaApproval) btnMintaApproval.classList.add('hidden');
                    } else if (approvalStatus === 'rejected') {
                        setBadge('rejected');
                        setDiskonInputsDisabled(false);
                        if (btnMintaApproval) {
                            if (normalized.length > 0) btnMintaApproval.classList.remove('hidden');
                            else btnMintaApproval.classList.add('hidden');
                        }
                    } else {
                        setBadge(null);
                        setDiskonInputsDisabled(false);
                        if (btnMintaApproval) {
                            if (normalized.length > 0) btnMintaApproval.classList.remove('hidden');
                            else btnMintaApproval.classList.add('hidden');
                        }
                    }
                }

                hitungKembalian();
            }

            function applyApprovalState(status, items = []) {
                approvalStatus = status || null;
                approvalSnapshotStr = JSON.stringify(normalizeDiskonItems(items));

                if (approvalStatus === 'pending') {
                    applyDiskonItems(items);
                    setBadge('pending');
                    setDiskonInputsDisabled(true);
                    if (btnMintaApproval) btnMintaApproval.classList.add('hidden');
                    return;
                }

                if (approvalStatus === 'approved') {
                    applyDiskonItems(items);
                    setBadge('approved');
                    setDiskonInputsDisabled(true);
                    if (btnMintaApproval) btnMintaApproval.classList.add('hidden');
                    return;
                }

                if (approvalStatus === 'rejected') {
                    applyDiskonItems([]);
                    setBadge('rejected');
                    setDiskonInputsDisabled(false);
                    if (btnMintaApproval) btnMintaApproval.classList.add('hidden');
                    return;
                }

                recalcAll(true);
            }

            async function fetchApprovalStatus() {
                try {
                    const res = await fetch(urlStatus, {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const ct = res.headers.get('Content-Type') || '';
                    const data = ct.includes('application/json') ? await res.json() : null;

                    const status = data?.data?.status || null;
                    const serverItems = normalizeDiskonItems(data?.data?.diskon_items || []);

                    applyApprovalState(status, serverItems);

                    return status;
                } catch (e) {
                    console.error(e);
                    return approvalStatus;
                }
            }

            async function requestApprovalDiskon() {
                recalcAll(true);

                const normalized = getCurrentNormalizedDiskonItems();

                if (normalized.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Tidak ada diskon',
                        text: 'Diskon 0% tidak butuh approval.'
                    });
                    return;
                }

                if (approvalStatus === 'pending') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Masih menunggu approve',
                        text: 'Pengajuan diskon ini masih menunggu keputusan Manager.'
                    });
                    return;
                }

                const {
                    value: reason
                } = await Swal.fire({
                    title: 'Minta Approval Diskon',
                    input: 'textarea',
                    inputLabel: 'Alasan diskon (wajib)',
                    inputPlaceholder: 'Contoh: Diskon pelanggan tetap / promo / koreksi harga...',
                    showCancelButton: true,
                    confirmButtonText: 'Kirim',
                    cancelButtonText: 'Batal',
                    inputValidator: (value) => {
                        if (!value || value.trim().length < 3) return 'Alasan minimal 3 karakter.';
                        return null;
                    }
                });

                if (!reason) return;

                try {
                    const res = await fetch(urlRequest, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            reason: reason.trim(),
                            diskon_items: JSON.stringify(normalized),
                        })
                    });

                    const ct = res.headers.get('Content-Type') || '';
                    const data = ct.includes('application/json') ? await res.json() : {
                        success: res.ok,
                        message: res.ok ? 'OK' : 'Gagal'
                    };

                    if (!data.success) {
                        const msg = getFirstErrorMessage(data) || 'Gagal kirim request approval.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: msg
                        });
                        return;
                    }

                    await fetchApprovalStatus();

                    Swal.fire({
                        icon: 'success',
                        title: 'Terkirim',
                        text: data.message || 'Menunggu approval manager.'
                    });
                } catch (e) {
                    console.error(e);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Tidak dapat terhubung ke server.'
                    });
                }
            }

            if (btnMintaApproval) {
                btnMintaApproval.addEventListener('click', requestApprovalDiskon);
            }

            document.querySelectorAll('.diskon-item').forEach(inp => {
                inp.addEventListener('input', function() {
                    if (approvalStatus === 'approved' || approvalStatus === 'pending') {
                        return;
                    }
                    recalcAll(true);
                });
            });

            function openModal(modal) {
                if (!modal) return;
                modal.classList.remove("hidden");
                modal.classList.add("flex");
                document.documentElement.style.overflow = "hidden";
            }

            function closeModal(modal) {
                if (!modal) return;
                modal.classList.add("hidden");
                modal.classList.remove("flex");
                document.documentElement.style.overflow = "";
            }

            function closeAll() {
                closeModal(modalCash);
                closeModal(modalTransfer);
            }

            document.querySelectorAll('[data-close-modal="cash"]').forEach(btn => {
                btn.addEventListener('click', () => closeModal(modalCash));
            });

            document.querySelectorAll('[data-close-modal="transfer"]').forEach(btn => {
                btn.addEventListener('click', () => closeModal(modalTransfer));
            });

            [modalCash, modalTransfer].forEach(modal => {
                if (!modal) return;
                modal.addEventListener("click", (ev) => {
                    if (ev.target === modal) closeModal(modal);
                });
            });

            document.addEventListener("keydown", (ev) => {
                if (ev.key === "Escape") closeAll();
            });

            if (btnLanjut) {
                btnLanjut.addEventListener("click", async (e) => {
                    e.preventDefault();

                    recalcAll(true);

                    const selected = pilihMetode?.options[pilihMetode.selectedIndex];
                    if (!selected) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Metode kosong',
                            text: 'Pilih metode pembayaran dulu.'
                        });
                        return;
                    }

                    const latestStatus = await fetchApprovalStatus();
                    const normalized = getCurrentNormalizedDiskonItems();

                    if (latestStatus === 'pending') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Pengajuan Masih Diproses',
                            text: 'Pembayaran ini sedang diajukan untuk diskonnya. Tunggu keputusan Manager terlebih dahulu.'
                        });
                        return;
                    }

                    if (normalized.length > 0 && latestStatus !== 'approved') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Butuh Approval Manager',
                            text: 'Ada diskon per item. Klik "Minta Approval Diskon" dulu, lalu tunggu Manager approve.'
                        });
                        if (btnMintaApproval) btnMintaApproval.classList.remove('hidden');
                        return;
                    }

                    if (latestStatus === 'approved') {
                        const currentStr = JSON.stringify(normalized);
                        if (currentStr !== approvalSnapshotStr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Data Diskon Berubah',
                                text: 'Data diskon tidak sesuai approval. Silakan refresh halaman.'
                            });
                            return;
                        }
                    }

                    const metodeID = selected.value;
                    const metodeText = (selected.textContent || "").toLowerCase();

                    const cashInput = document.getElementById("metode-pembayaran-cash");
                    const transferInput = document.getElementById("metode-pembayaran-transfer");

                    if (cashInput) cashInput.value = metodeID;
                    if (transferInput) transferInput.value = metodeID;

                    closeAll();

                    if (metodeText.includes("cash")) {
                        openModal(modalCash);
                    } else if (metodeText.includes("transfer")) {
                        openModal(modalTransfer);
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Metode belum dikenali',
                            text: 'Metode pembayaran belum dikenali sistem.'
                        });
                    }
                });
            }

            if (uangDiterimaInput) {
                uangDiterimaInput.addEventListener("input", (e) => {
                    let angka = onlyDigits(e.target.value);
                    e.target.value = angka ? formatRupiah(angka) : "";
                    hitungKembalian();
                });
            }

            const fileInput = document.getElementById("upload");
            const previewContainer = document.getElementById("preview-bukti-pembayaran");
            const textGantiGambar = document.getElementById("text-ganti-gambar");

            if (fileInput) {
                fileInput.addEventListener("change", (event) => {
                    const file = event.target.files[0];
                    if (!file) return;

                    if (!file.type.startsWith("image/")) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'File bukan gambar',
                            text: 'Unggah file gambar (jpg/png/gif/dll).'
                        });
                        fileInput.value = "";
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = (ev) => {
                        previewContainer.innerHTML = `
                            <img src="${ev.target.result}" alt="Preview Bukti Pembayaran"
                                 class="h-56 w-full rounded-2xl object-cover shadow" />
                        `;
                        if (textGantiGambar) textGantiGambar.classList.remove("hidden");
                    };
                    reader.readAsDataURL(file);
                });
            }

            const formCash = document.getElementById('formPembayaranCash');
            if (formCash) {
                formCash.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const metodeCash = document.getElementById('metode-pembayaran-cash');
                    const selectedStatus = await fetchApprovalStatus();
                    const normalized = getCurrentNormalizedDiskonItems();

                    if (selectedStatus === 'pending') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Masih Menunggu Approval',
                            text: 'Pembayaran ini sedang diajukan untuk diskon. Belum bisa diproses.'
                        });
                        return;
                    }

                    if (normalized.length > 0 && selectedStatus !== 'approved') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Diskon Belum Disetujui',
                            text: 'Ada diskon per item yang belum disetujui Manager.'
                        });
                        return;
                    }

                    if (selectedStatus === 'approved') {
                        const currentStr = JSON.stringify(normalized);
                        if (currentStr !== approvalSnapshotStr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Data Diskon Berubah',
                                text: 'Data diskon tidak sesuai approval. Silakan refresh halaman.'
                            });
                            return;
                        }
                    }

                    const totalSesudahDiskon = parseFloat(totalSetelahDiskonHidden?.value ||
                        totalAwal) || 0;
                    const uangDiterima = parseFloat(onlyDigits(uangDiterimaInput?.value)) || 0;
                    const kembalianClean = uangDiterima - totalSesudahDiskon;

                    if (!metodeCash?.value) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Metode belum dipilih',
                            text: 'Pilih metode pembayaran dulu.'
                        });
                        return;
                    }

                    if (uangDiterima < totalSesudahDiskon) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Uang kurang',
                            text: 'Nominal uang yang diterima belum cukup.'
                        });
                        return;
                    }

                    const formData = new FormData(formCash);
                    formData.set('uang_yang_diterima', uangDiterima);
                    formData.set('kembalian', kembalianClean);
                    formData.set('metode_pembayaran_id', metodeCash.value);
                    formData.set('diskon_items', JSON.stringify(normalized));

                    const submitBtn = formCash.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
                    }

                    try {
                        const res = await fetch(formCash.action, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const ct = res.headers.get('Content-Type') || '';
                        const data = ct.includes('application/json') ? await res.json() : {
                            success: res.ok,
                            message: res.ok ? 'OK' : 'Gagal'
                        };

                        if (data.success) {
                            closeModal(modalCash);
                            await Swal.fire({
                                icon: 'success',
                                title: 'Pembayaran berhasil',
                                text: data.message || 'Pembayaran cash berhasil diproses.'
                            });
                            window.location.href = "{{ route('kasir.pembayaran') }}";
                        } else {
                            const msg = getFirstErrorMessage(data) || 'Gagal memproses pembayaran.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: msg
                            });
                        }
                    } catch (err) {
                        console.error(err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Tidak dapat terhubung ke server.'
                        });
                    } finally {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                        }
                    }
                });
            }

            const formTransfer = document.getElementById('formPembayaranTransfer');
            if (formTransfer) {
                formTransfer.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const metodeTransfer = document.getElementById('metode-pembayaran-transfer');
                    const selectedStatus = await fetchApprovalStatus();
                    const normalized = getCurrentNormalizedDiskonItems();

                    if (selectedStatus === 'pending') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Masih Menunggu Approval',
                            text: 'Pembayaran ini sedang diajukan untuk diskon. Belum bisa diproses.'
                        });
                        return;
                    }

                    if (normalized.length > 0 && selectedStatus !== 'approved') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Diskon Belum Disetujui',
                            text: 'Ada diskon per item yang belum disetujui Manager.'
                        });
                        return;
                    }

                    if (selectedStatus === 'approved') {
                        const currentStr = JSON.stringify(normalized);
                        if (currentStr !== approvalSnapshotStr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Data Diskon Berubah',
                                text: 'Data diskon tidak sesuai approval. Silakan refresh halaman.'
                            });
                            return;
                        }
                    }

                    if (!metodeTransfer?.value) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Metode belum dipilih',
                            text: 'Pilih metode pembayaran dulu.'
                        });
                        return;
                    }

                    const formData = new FormData(formTransfer);
                    formData.set('metode_pembayaran_id', metodeTransfer.value);
                    formData.set('diskon_items', JSON.stringify(normalized));

                    const submitBtn = formTransfer.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
                    }

                    try {
                        const res = await fetch(formTransfer.action, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const ct = res.headers.get('Content-Type') || '';
                        const data = ct.includes('application/json') ? await res.json() : {
                            success: res.ok,
                            message: res.ok ? 'OK' : 'Gagal'
                        };

                        if (data.success) {
                            closeModal(modalTransfer);
                            await Swal.fire({
                                icon: 'success',
                                title: 'Bukti terkirim',
                                text: data.message || 'Bukti transfer berhasil dikirim.'
                            });
                            window.location.href = "{{ route('kasir.pembayaran') }}";
                        } else {
                            const msg = getFirstErrorMessage(data) || 'Gagal mengirim bukti.';
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: msg
                            });
                        }
                    } catch (err) {
                        console.error(err);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Tidak dapat terhubung ke server.'
                        });
                    } finally {
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                        }
                    }
                });
            }

            applyApprovalState(window.__SERVER_APPROVAL_STATUS__, window.__SERVER_APPROVAL_ITEMS__ || []);
            recalcAll(true);

            setTimeout(() => {
                fetchApprovalStatus();
            }, 300);

            setInterval(async () => {
                if (approvalStatus === 'pending') {
                    await fetchApprovalStatus();
                }
            }, 3000);
        });
    </script>

</body>

</html>
