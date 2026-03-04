<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="App Clinic" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CMS-Royal-Klinik</title>
    <link href='{{ asset('storage/assets/royal_klinik.svg') }}' rel='shortcut icon'>

    {{-- vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font-Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Flowbite JS (boleh tetap ada, tapi kita TIDAK pakai flowbite modal) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.1/flowbite.min.js" defer></script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-50 dark:bg-gray-900">
    <section class="py-6 md:py-10">
        <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">

            <!-- Top Bar -->
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

            <!-- Content Layout -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

                <!-- LEFT: Detail -->
                <div class="lg:col-span-8 space-y-6">

                    <!-- Patient Card -->
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

                    <!-- Items: OBAT -->
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
                                        <th class="px-5 py-3 w-24 text-center">Qty</th>
                                        <th class="px-5 py-3 w-28 text-center">Diskon (%)</th>
                                        <th class="px-5 py-3 w-40 text-right">Subtotal</th>
                                        <th class="px-5 py-3 w-44 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($itemsObat as $item)
                                        <tr class="item-row hover:bg-gray-50/70 dark:hover:bg-gray-900/20"
                                            data-detail-id="{{ $item->id }}"
                                            data-subtotal="{{ (float) ($item->subtotal ?? 0) }}">
                                            <td class="px-5 py-4">
                                                <div class="font-medium text-gray-900 dark:text-white">
                                                    {{ $item->nama_item }}</div>
                                                <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                    Rp {{ number_format($item->harga ?? 0, 0, ',', '.') }} / item
                                                </div>
                                            </td>

                                            <td class="px-5 py-4 text-center text-sm text-gray-800 dark:text-gray-200">
                                                x{{ $item->qty ?? 1 }}
                                            </td>

                                            <td class="px-5 py-4 text-center">
                                                <input type="number" min="0" max="100" value="0"
                                                    class="diskon-item w-20 rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-right text-sm font-semibold text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
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
                                                    class="row-discount-note mt-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-300 hidden">
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

                    <!-- Items: LAYANAN -->
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
                                        <th class="px-5 py-3 w-24 text-center">Qty</th>
                                        <th class="px-5 py-3 w-28 text-center">Diskon (%)</th>
                                        <th class="px-5 py-3 w-40 text-right">Subtotal</th>
                                        <th class="px-5 py-3 w-44 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($itemsLayanan as $item)
                                        <tr class="item-row hover:bg-gray-50/70 dark:hover:bg-gray-900/20"
                                            data-detail-id="{{ $item->id }}"
                                            data-subtotal="{{ (float) ($item->subtotal ?? 0) }}">
                                            <td class="px-5 py-4">
                                                <div class="font-medium text-gray-900 dark:text-white">
                                                    {{ $item->nama_item }}</div>
                                                <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                    Rp {{ number_format($item->harga ?? 0, 0, ',', '.') }} / item
                                                </div>
                                            </td>

                                            <td class="px-5 py-4 text-center text-sm text-gray-800 dark:text-gray-200">
                                                x{{ $item->qty ?? 1 }}
                                            </td>

                                            <td class="px-5 py-4 text-center">
                                                <input type="number" min="0" max="100" value="0"
                                                    class="diskon-item w-20 rounded-lg border border-gray-200 bg-white px-2 py-1.5 text-right text-sm font-semibold text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
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
                                                    class="row-discount-note mt-0.5 text-xs font-medium text-emerald-700 dark:text-emerald-300 hidden">
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

                    {{-- LAB & RADIOLOGI kamu biarkan sama seperti punya kamu (tidak perlu diubah) --}}
                    @if (($itemsLab?->count() ?? 0) > 0)
                        {{-- ... (tidak diubah) ... --}}
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
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Order Lab
                                        </h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Opsional</p>
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
                                            <th class="px-5 py-3 w-24 text-center">Qty</th>
                                            <th class="px-5 py-3 w-40 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($itemsLab as $item)
                                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-900/20">
                                                <td class="px-5 py-4">
                                                    <div class="font-medium text-gray-900 dark:text-white">
                                                        {{ $item->nama_item }}</div>
                                                    <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                        Rp {{ number_format($item->harga ?? 0, 0, ',', '.') }} / item
                                                    </div>
                                                </td>
                                                <td
                                                    class="px-5 py-4 text-center text-sm text-gray-800 dark:text-gray-200">
                                                    x{{ $item->qty ?? 1 }}
                                                </td>
                                                <td
                                                    class="px-5 py-4 text-right font-semibold text-gray-900 dark:text-white">
                                                    Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    @if (($itemsRadiologi?->count() ?? 0) > 0)
                        {{-- ... (tidak diubah) ... --}}
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
                                        <h3 class="text-base font-semibold text-gray-900 dark:text-white">Order
                                            Radiologi</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Opsional</p>
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
                                            <th class="px-5 py-3 w-24 text-center">Qty</th>
                                            <th class="px-5 py-3 w-40 text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($itemsRadiologi as $item)
                                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-900/20">
                                                <td class="px-5 py-4">
                                                    <div class="font-medium text-gray-900 dark:text-white">
                                                        {{ $item->nama_item }}</div>
                                                    <div class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                        Rp {{ number_format($item->harga ?? 0, 0, ',', '.') }} / item
                                                    </div>
                                                </td>
                                                <td
                                                    class="px-5 py-4 text-center text-sm text-gray-800 dark:text-gray-200">
                                                    x{{ $item->qty ?? 1 }}
                                                </td>
                                                <td
                                                    class="px-5 py-4 text-right font-semibold text-gray-900 dark:text-white">
                                                    Rp {{ number_format($item->subtotal ?? 0, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- RIGHT: Summary (Sticky) -->
                <aside class="lg:col-span-4">
                    <div class="lg:sticky lg:top-6 space-y-4">
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
                                <i
                                    class="fa-solid fa-credit-card absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <select id="pilih-metode-pembayaran"
                                    class="w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-3 text-sm font-semibold text-gray-900 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                    @foreach ($dataMetodePembayaran as $metodePembayaran)
                                        <option value="{{ $metodePembayaran->id }}">
                                            {{ $metodePembayaran->nama_metode }}
                                        </option>
                                    @endforeach
                                </select>
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

    <!-- Modal CASH -->
    <div id="pembayaranCash" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50 px-4">
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

                    {{-- ✅ GANTI: jangan pakai data-modal-hide (flowbite) --}}
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
                        {{-- ✅ GANTI: jangan pakai data-modal-hide --}}
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

    <!-- Modal TRANSFER -->
    <div id="pembayaranTransfer" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 items-center justify-center bg-black/50 px-4">
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

                    {{-- ✅ GANTI --}}
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
        document.addEventListener('DOMContentLoaded', function() {
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

            const totalAwal = parseFloat(totalAwalInput?.value || '0') || 0;

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

            function hitungKembalian() {
                const totalBayar = parseFloat(totalSetelahDiskonHidden?.value || 0) || 0;
                const diterima = parseFloat(onlyDigits(uangDiterimaInput?.value)) || 0;
                const kembalian = diterima - totalBayar;

                if (uangKembalianInput) {
                    uangKembalianInput.value = "Rp " + formatRupiah(Math.max(kembalian, 0));
                }
            }

            // ✅ Hitung diskon per item + simpan payload diskon item berdasarkan pembayaran_detail.id
            function recalcAll() {
                const rows = document.querySelectorAll('.item-row');
                let totalBase = 0;
                let totalAfter = 0;

                const diskonItems = [];

                rows.forEach(row => {
                    const detailId = row.dataset.detailId; // ✅ pembayaran_detail.id
                    const baseSubtotal = parseFloat(row.dataset.subtotal || '0') || 0;
                    totalBase += baseSubtotal;

                    const input = row.querySelector('.diskon-item');
                    let persen = parseFloat(input?.value || '0') || 0;
                    persen = clamp(persen, 0, 100);
                    if (input && Number(input.value) !== persen) input.value = persen;

                    const diskonNominal = baseSubtotal * (persen / 100);
                    const after = Math.max(baseSubtotal - diskonNominal, 0);
                    totalAfter += after;

                    // update row UI
                    const totalDisplay = row.querySelector('.row-total-display');
                    if (totalDisplay) totalDisplay.textContent = "Rp " + formatRupiah(after);

                    const note = row.querySelector('.row-discount-note');
                    if (note) {
                        if (diskonNominal > 0) {
                            note.classList.remove('hidden');
                            note.textContent = "Hemat Rp " + formatRupiah(diskonNominal);
                        } else {
                            note.classList.add('hidden');
                        }
                    }

                    // ✅ payload backend (id detail + persen diskon)
                    if (detailId) {
                        diskonItems.push({
                            id: Number(detailId),
                            persen
                        });
                    }
                });

                const potongan = Math.max(totalBase - totalAfter, 0);

                if (totalHargaDisplay) totalHargaDisplay.textContent = "Rp " + formatRupiah(totalBase || totalAwal);
                if (potonganDisplay) potonganDisplay.textContent = "Rp " + formatRupiah(potongan);
                if (totalTagihanDisplay) totalTagihanDisplay.textContent = "Rp " + formatRupiah(totalAfter);

                if (totalSetelahDiskonHidden) totalSetelahDiskonHidden.value = totalAfter;
                totalInputsAll.forEach(inp => inp.value = formatRupiah(totalAfter));

                // ✅ store globally for submit
                window.__DISKON_ITEMS__ = diskonItems;

                hitungKembalian();
            }

            document.querySelectorAll('.diskon-item').forEach(inp => {
                inp.addEventListener('input', recalcAll);
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

            // close modal custom
            document.querySelectorAll('[data-close-modal="cash"]').forEach(btn => {
                btn.addEventListener('click', () => closeModal(modalCash));
            });
            document.querySelectorAll('[data-close-modal="transfer"]').forEach(btn => {
                btn.addEventListener('click', () => closeModal(modalTransfer));
            });

            // overlay click close
            [modalCash, modalTransfer].forEach(modal => {
                if (!modal) return;
                modal.addEventListener("click", (ev) => {
                    if (ev.target === modal) closeModal(modal);
                });
            });

            // esc close
            document.addEventListener("keydown", (ev) => {
                if (ev.key === "Escape") closeAll();
            });

            // open modal sesuai metode
            if (btnLanjut) {
                btnLanjut.addEventListener("click", (e) => {
                    e.preventDefault();
                    const selected = pilihMetode?.options[pilihMetode.selectedIndex];
                    if (!selected) return alert("Pilih metode pembayaran dulu.");

                    const metodeID = selected.value;
                    const metodeText = (selected.textContent || "").toLowerCase();

                    const cashInput = document.getElementById("metode-pembayaran-cash");
                    const transferInput = document.getElementById("metode-pembayaran-transfer");
                    if (cashInput) cashInput.value = metodeID;
                    if (transferInput) transferInput.value = metodeID;

                    recalcAll();
                    closeAll();

                    if (metodeText.includes("cash")) openModal(modalCash);
                    else if (metodeText.includes("transfer")) openModal(modalTransfer);
                    else alert("Metode pembayaran belum dikenali: " + (selected.textContent || "-"));
                });
            }

            // format uang cash
            if (uangDiterimaInput) {
                uangDiterimaInput.addEventListener("input", (e) => {
                    let angka = onlyDigits(e.target.value);
                    e.target.value = angka ? formatRupiah(angka) : "";
                    hitungKembalian();
                });
            }

            // preview gambar transfer (biarkan sama)
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

            // ✅ SUBMIT CASH: kirim diskon per item
            const formCash = document.getElementById('formPembayaranCash');
            if (formCash) {
                formCash.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    const metodeCash = document.getElementById('metode-pembayaran-cash');

                    const totalSesudahDiskon = parseFloat(totalSetelahDiskonHidden?.value ||
                        totalAwal) || 0;
                    const uangDiterima = parseFloat(onlyDigits(uangDiterimaInput?.value)) || 0;
                    const kembalianClean = uangDiterima - totalSesudahDiskon;

                    // totalBase dari tampilan summary
                    const totalBaseText = (totalHargaDisplay?.textContent || '').replace(/[^\d]/g, '');
                    const totalBase = parseFloat(totalBaseText || totalAwal) || totalAwal;

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

                    // ✅ payload diskon per item
                    const diskonItems = window.__DISKON_ITEMS__ || [];

                    const formData = new FormData(formCash);
                    formData.set('uang_yang_diterima', uangDiterima);
                    formData.set('kembalian', kembalianClean);
                    formData.set('metode_pembayaran_id', metodeCash.value);

                    // server akan hitung ulang, tapi tetap kita kirim untuk record
                    formData.set('total_tagihan', totalBase);
                    formData.set('total_setelah_diskon', totalSesudahDiskon);

                    // ✅ per-item diskon
                    formData.set('diskon_items', JSON.stringify(diskonItems));

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
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const ct = res.headers.get('Content-Type') || '';
                        const data = ct.includes('application/json') ?
                            await res.json() : {
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

            // ✅ SUBMIT TRANSFER: fetch + sweetalert + redirect (KIRIM diskon_items)
            const formTransfer = document.getElementById('formPembayaranTransfer');
            if (formTransfer) {
                formTransfer.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

                    const metodeTransfer = document.getElementById('metode-pembayaran-transfer');
                    if (!metodeTransfer?.value) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Metode belum dipilih',
                            text: 'Pilih metode pembayaran dulu.'
                        });
                        return;
                    }

                    // ✅ payload diskon per item (sama seperti cash)
                    const diskonItems = window.__DISKON_ITEMS__ || [];

                    const formData = new FormData(formTransfer);
                    formData.set('metode_pembayaran_id', metodeTransfer.value);

                    // ✅ PENTING: kirim diskon_items
                    formData.set('diskon_items', JSON.stringify(diskonItems));

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
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const ct = res.headers.get('Content-Type') || '';
                        const data = ct.includes('application/json') ?
                            await res.json() :
                            {
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

            // initial compute
            recalcAll();
        });
    </script>

</body>

</html>
