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

<body class="bg-slate-50 dark:bg-slate-950">
    <section class="min-h-screen py-8 md:py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Header --}}
            <div
                class="mb-6 rounded-3xl bg-gradient-to-r from-sky-600 via-cyan-600 to-teal-600 p-6 text-white shadow-lg">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 backdrop-blur">
                            <i class="fa-solid fa-capsules text-2xl"></i>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold md:text-3xl">Detail Transaksi Obat</h1>
                            <p class="mt-1 text-sm text-sky-50/90 md:text-base">
                                Periksa rincian order obat pasien dan lanjutkan proses pembayaran.
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex flex-col items-start gap-2 rounded-2xl bg-white/10 px-4 py-3 backdrop-blur sm:items-end">
                        <p class="text-xs uppercase tracking-[0.2em] text-sky-100/80">Kode Transaksi</p>
                        <p class="text-lg font-semibold">{{ $kodeTransaksi }}</p>
                        <span
                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold
                            {{ $transaksi->status === 'Sudah Bayar' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $transaksi->status }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="xl:col-span-2 space-y-6">

                    {{-- Informasi Pasien --}}
                    <div
                        class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="mb-5 flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-800 dark:text-white">Informasi Pasien</h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Data pasien dan informasi
                                    transaksi utama.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800">
                                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Nama
                                    Pasien</p>
                                <p class="mt-1 text-base font-semibold text-slate-800 dark:text-white">
                                    {{ $dataPasien->nama_pasien ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800">
                                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Tanggal
                                    Transaksi</p>
                                <p class="mt-1 text-base font-semibold text-slate-800 dark:text-white">
                                    {{ $tanggalTransaksi ? \Carbon\Carbon::parse($tanggalTransaksi)->timezone('Asia/Jakarta')->translatedFormat('l, d F Y H:i') : '-' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800">
                                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Alamat</p>
                                <p class="mt-1 text-base font-semibold text-slate-800 dark:text-white">
                                    {{ $dataPasien->alamat ?? '-' }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800">
                                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Jenis
                                    Kelamin</p>
                                <p class="mt-1 text-base font-semibold text-slate-800 dark:text-white">
                                    {{ $dataPasien->jenis_kelamin ?? '-' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Daftar Obat --}}
                    <div
                        class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="mb-5 flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300">
                                <i class="fa-solid fa-pills"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-800 dark:text-white">Daftar Obat</h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400">
                                    Diskon dibuat per item. Jika ada diskon, wajib diajukan dulu sebelum pembayaran.
                                </p>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-2xl border border-slate-200 dark:border-slate-700">
                            <div class="overflow-x-auto">
                                <table class="min-w-full text-left text-sm text-slate-700 dark:text-slate-200">
                                    <thead
                                        class="bg-slate-100 text-xs uppercase tracking-wide text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                        <tr>
                                            <th class="px-4 py-3">Obat</th>
                                            <th class="px-4 py-3">Qty</th>
                                            <th class="px-4 py-3">Harga</th>
                                            <th class="px-4 py-3 text-right">Subtotal</th>
                                            <th class="px-4 py-3 text-center">Diskon (%)</th>
                                            <th class="px-4 py-3 text-right">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                                        @forelse ($transaksi->penjualanObatDetail as $detail)
                                            @php
                                                $subtotalItem = (float) ($detail->sub_total ?? 0);
                                                $diskonItem = (float) ($detail->diskon_nilai ?? 0);
                                                $afterItem =
                                                    $detail->total_setelah_diskon !== null
                                                        ? (float) $detail->total_setelah_diskon
                                                        : $subtotalItem;
                                            @endphp
                                            <tr class="item-row bg-white hover:bg-slate-50 dark:bg-slate-900 dark:hover:bg-slate-800/60"
                                                data-detail-id="{{ $detail->id }}"
                                                data-subtotal="{{ $subtotalItem }}">
                                                <td class="px-4 py-4 align-top">
                                                    <div class="font-semibold text-slate-800 dark:text-white">
                                                        {{ $detail->obat->nama_obat ?? '-' }}
                                                    </div>
                                                    <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                                        Dosis: {{ $detail->obat->dosis ?? '-' }}
                                                    </div>
                                                </td>

                                                <td class="px-4 py-4 align-top">
                                                    <span
                                                        class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
                                                        x{{ $detail->jumlah }}
                                                    </span>
                                                </td>

                                                <td class="px-4 py-4 align-top">
                                                    Rp{{ number_format($detail->harga_satuan, 0, ',', '.') }}
                                                </td>

                                                <td
                                                    class="px-4 py-4 text-right align-top font-semibold text-slate-800 dark:text-white">
                                                    Rp{{ number_format($subtotalItem, 0, ',', '.') }}
                                                </td>

                                                <td class="px-4 py-4 text-center align-top">
                                                    <input type="number" min="0" max="100" step="0.01"
                                                        value="{{ $diskonItem }}"
                                                        class="diskon-item w-24 rounded-xl border border-slate-300 bg-white px-3 py-2 text-right text-sm font-semibold text-slate-800 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                                                </td>

                                                <td
                                                    class="px-4 py-4 text-right align-top font-bold text-slate-800 dark:text-white">
                                                    <span class="row-total-display">
                                                        Rp{{ number_format($afterItem, 0, ',', '.') }}
                                                    </span>
                                                    <div
                                                        class="row-discount-note mt-1 hidden text-xs font-medium text-emerald-700 dark:text-emerald-300">
                                                        Hemat Rp 0
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6"
                                                    class="px-4 py-8 text-center text-slate-500 dark:text-slate-400">
                                                    Tidak ada detail obat.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Sidebar --}}
                <div class="space-y-6">
                    <div
                        class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="mb-5 flex items-center gap-3">
                            <div
                                class="flex h-11 w-11 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-slate-800 dark:text-white">Ringkasan Transaksi</h2>
                                <p class="text-sm text-slate-500 dark:text-slate-400">Diskon item & pembayaran.</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-800">
                                <p class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Total
                                    Harga</p>
                                <p id="total_harga_display"
                                    class="mt-1 text-2xl font-bold text-slate-800 dark:text-white">
                                    Rp{{ number_format($subTotal, 0, ',', '.') }}
                                </p>
                            </div>

                            <div class="rounded-2xl bg-rose-50 p-4 dark:bg-rose-900/20">
                                <p class="text-xs uppercase tracking-wide text-rose-700 dark:text-rose-300">Total
                                    Diskon</p>
                                <p id="potongan_display"
                                    class="mt-1 text-2xl font-bold text-rose-700 dark:text-rose-300">
                                    Rp0
                                </p>
                            </div>

                            <div class="rounded-2xl bg-emerald-50 p-4 dark:bg-emerald-900/20">
                                <p class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Total
                                    Bayar</p>
                                <p id="total_tagihan_display"
                                    class="mt-1 text-2xl font-bold text-emerald-700 dark:text-emerald-300">
                                    Rp{{ number_format($totalSetelahDiskon ?? $subTotal, 0, ',', '.') }}
                                </p>
                            </div>

                            <input type="hidden" id="total_setelah_diskon"
                                value="{{ $totalSetelahDiskon ?? $subTotal }}">
                            <input type="hidden" id="total_tagihan_awal" value="{{ $subTotal }}">
                            <input type="hidden" id="pasien-id" value="{{ $dataPasien->id ?? '' }}">

                            <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
                                <label for="pilih-metode-pembayaran"
                                    class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                    Metode Pembayaran
                                </label>
                                <select id="pilih-metode-pembayaran"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-800 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                    <option value="">-- Pilih Metode Pembayaran --</option>
                                    @foreach ($dataMetodePembayaran as $metodePembayaran)
                                        <option value="{{ $metodePembayaran->id }}">
                                            {{ $metodePembayaran->nama_metode }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div id="diskon_approval_box"
                                class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-200">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold">Status Diskon</span>
                                    <span id="diskon_status_badge"
                                        class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-800 dark:bg-slate-700 dark:text-slate-100">
                                        Normal
                                    </span>
                                </div>

                                <p id="diskon_status_desc" class="mt-2 text-xs text-slate-600 dark:text-slate-300">
                                    Jika ada diskon per item, wajib ajukan approval dulu.
                                </p>

                                <button type="button" id="btnAjukanDiskon"
                                    class="mt-3 hidden w-full rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-700">
                                    <i class="fa-solid fa-user-check mr-2"></i>
                                    Ajukan Diskon
                                </button>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col gap-3">
                            <button type="button" id="btnLanjutPembayaran"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-300">
                                <i class="fa-solid fa-credit-card"></i>
                                Lanjutkan Pembayaran
                            </button>

                            <a href="{{ route('kasir.pembayaran') }}"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                                <i class="fa-solid fa-arrow-left"></i>
                                Kembali ke halaman kasir
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Modal Cash --}}
    <div id="pembayaranCash" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4">
        <div class="relative w-full max-w-md">
            <div class="overflow-hidden rounded-3xl bg-white shadow-2xl dark:bg-slate-900">
                <div
                    class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Pembayaran Cash</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Masukkan nominal uang dari pasien.</p>
                    </div>
                    <button type="button" data-close-modal="cash"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form id="formPembayaranCash" action="{{ route('kasir.transaksi.obat.cash') }}" method="POST">
                    @csrf
                    <div class="space-y-4 p-6">
                        <input type="hidden" name="id" value="{{ $id }}">
                        <input type="hidden" name="metode_pembayaran_id" id="metode-pembayaran-cash"
                            value="">
                        <input type="hidden" name="kode_transaksi" value="{{ $kodeTransaksi }}">

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Total
                                Tagihan</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500">Rp</span>
                                <input type="text" id="total_tagihan_cash_display" readonly
                                    value="{{ number_format($totalSetelahDiskon ?? $subTotal, 0, ',', '.') }}"
                                    class="w-full rounded-2xl border border-slate-300 py-3 pl-10 pr-4 text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Uang
                                Diterima</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500">Rp</span>
                                <input type="text" name="uang_yang_diterima" id="uang_diterima"
                                    placeholder="Masukkan nominal"
                                    class="w-full rounded-2xl border border-slate-300 py-3 pl-10 pr-4 text-slate-800 focus:border-sky-500 focus:outline-none focus:ring-2 focus:ring-sky-100 dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                            </div>
                        </div>

                        <div>
                            <label
                                class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Kembalian</label>
                            <input type="text" name="kembalian" id="uang_kembalian" readonly
                                class="w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                        <button data-close-modal="cash" type="button"
                            class="rounded-2xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-2xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                            Bayar Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Transfer --}}
    <div id="pembayaranTransfer" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 items-center justify-center bg-slate-950/70 backdrop-blur-sm p-4">
        <div class="relative w-full max-w-2xl">
            <div class="overflow-hidden rounded-3xl bg-white shadow-2xl dark:bg-slate-900">
                <div
                    class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-800">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Pembayaran Transfer</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Upload bukti transfer untuk menyelesaikan
                            transaksi.</p>
                    </div>
                    <button type="button" data-close-modal="transfer"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form id="formPembayaranTransfer" action="{{ route('kasir.transaksi.obat.transfer') }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="space-y-5 p-6">
                        <input type="hidden" name="id" value="{{ $id }}">
                        <input type="hidden" name="metode_pembayaran_id" id="metode-pembayaran-transfer"
                            value="">
                        <input type="hidden" name="kode_transaksi" value="{{ $kodeTransaksi }}">
                        <input type="hidden" name="total_tagihan" id="total_tagihan_hidden"
                            value="{{ $totalSetelahDiskon ?? $subTotal }}">

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Total
                                Tagihan</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500">Rp</span>
                                <input type="text" id="total_tagihan_transfer_display" readonly
                                    value="{{ number_format($totalSetelahDiskon ?? $subTotal, 0, ',', '.') }}"
                                    class="w-full rounded-2xl border border-slate-300 py-3 pl-10 pr-4 text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-white" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">
                                Upload Bukti Transfer
                            </label>
                            <label for="upload"
                                class="flex min-h-[260px] w-full cursor-pointer flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-300 bg-slate-50 p-6 text-center transition hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:hover:bg-slate-800/70">
                                <div id="preview-bukti-pembayaran"
                                    class="flex h-full w-full flex-col items-center justify-center">
                                    <div
                                        class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">
                                        <i class="fa-solid fa-cloud-arrow-up text-xl"></i>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-700 dark:text-slate-200">
                                        Klik untuk upload bukti transfer
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                                        Format gambar: JPG, PNG, GIF, SVG, WEBP
                                    </p>
                                </div>
                                <input id="upload" type="file" class="hidden" accept="image/*"
                                    name="bukti_pembayaran" />
                            </label>
                            <p id="text-ganti-gambar"
                                class="mt-2 hidden text-center text-sm text-slate-500 dark:text-slate-400">
                                Klik area gambar untuk mengganti file
                            </p>
                        </div>
                    </div>

                    <div
                        class="flex items-center justify-end gap-3 border-t border-slate-200 px-6 py-4 dark:border-slate-800">
                        <button data-close-modal="transfer" type="button"
                            class="rounded-2xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                            Batal
                        </button>
                        <button type="submit"
                            class="rounded-2xl bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-sky-700">
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
            const totalTagihanCashDisplay = document.getElementById('total_tagihan_cash_display');
            const totalTagihanTransferDisplay = document.getElementById('total_tagihan_transfer_display');
            const totalTagihanHiddenTransfer = document.getElementById('total_tagihan_hidden');

            const btnAjukanDiskon = document.getElementById('btnAjukanDiskon');
            const badge = document.getElementById('diskon_status_badge');
            const desc = document.getElementById('diskon_status_desc');

            const totalAwal = parseFloat(totalAwalInput?.value || '0') || 0;

            const urlStatus = "{{ route('kasir.transaksi.obat.diskon.status', $id) }}";
            const urlRequest = "{{ route('kasir.transaksi.obat.diskon.request', $id) }}";

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
                return (items || [])
                    .map(item => ({
                        id: Number(item.id) || 0,
                        persen: clamp(Number(item.persen) || 0, 0, 100),
                    }))
                    .filter(item => item.id > 0 && item.persen > 0)
                    .sort((a, b) => a.id - b.id);
            }

            function getCurrentNormalizedDiskonItems() {
                return normalizeDiskonItems(window.__DISKON_ITEMS__ || []);
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
                        'rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-800 dark:bg-slate-700 dark:text-slate-100';
                    desc.textContent = 'Jika ada diskon per item, wajib ajukan approval dulu.';
                    return;
                }

                if (status === 'pending') {
                    badge.textContent = 'Menunggu Approval';
                    badge.className =
                        'rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-900/30 dark:text-amber-200';
                    desc.textContent = 'Diskon sudah diajukan. Menunggu approval manager.';
                } else if (status === 'approved') {
                    badge.textContent = 'Approved';
                    badge.className =
                        'rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200';
                    desc.textContent = 'Diskon sudah disetujui manager. Kasir bisa lanjut pembayaran.';
                } else if (status === 'rejected') {
                    badge.textContent = 'Ditolak';
                    badge.className =
                        'rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-800 dark:bg-rose-900/30 dark:text-rose-200';
                    desc.textContent = 'Pengajuan diskon ditolak. Silakan input ulang diskon.';
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
                        totalDisplay.textContent = "Rp" + formatRupiah(after);
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

                if (totalHargaDisplay) totalHargaDisplay.textContent = "Rp" + formatRupiah(totalBase || totalAwal);
                if (potonganDisplay) potonganDisplay.textContent = "Rp" + formatRupiah(potongan);
                if (totalTagihanDisplay) totalTagihanDisplay.textContent = "Rp" + formatRupiah(totalAfter);
                if (totalSetelahDiskonHidden) totalSetelahDiskonHidden.value = totalAfter;
                if (totalTagihanCashDisplay) totalTagihanCashDisplay.value = formatRupiah(totalAfter);
                if (totalTagihanTransferDisplay) totalTagihanTransferDisplay.value = formatRupiah(totalAfter);
                if (totalTagihanHiddenTransfer) totalTagihanHiddenTransfer.value = totalAfter;

                window.__DISKON_ITEMS__ = diskonItems;

                if (manageUi) {
                    const normalized = getCurrentNormalizedDiskonItems();

                    if (approvalStatus === 'pending') {
                        setBadge('pending');
                        setDiskonInputsDisabled(true);
                        btnAjukanDiskon?.classList.add('hidden');
                    } else if (approvalStatus === 'approved') {
                        setBadge('approved');
                        setDiskonInputsDisabled(true);
                        btnAjukanDiskon?.classList.add('hidden');
                    } else if (approvalStatus === 'rejected') {
                        setBadge('rejected');
                        setDiskonInputsDisabled(false);
                        if (normalized.length > 0) btnAjukanDiskon?.classList.remove('hidden');
                        else btnAjukanDiskon?.classList.add('hidden');
                    } else {
                        setBadge(null);
                        setDiskonInputsDisabled(false);
                        if (normalized.length > 0) btnAjukanDiskon?.classList.remove('hidden');
                        else btnAjukanDiskon?.classList.add('hidden');
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
                    btnAjukanDiskon?.classList.add('hidden');
                    return;
                }

                if (approvalStatus === 'approved') {
                    applyDiskonItems(items);
                    setBadge('approved');
                    setDiskonInputsDisabled(true);
                    btnAjukanDiskon?.classList.add('hidden');
                    return;
                }

                if (approvalStatus === 'rejected') {
                    setBadge('rejected');
                    setDiskonInputsDisabled(false);
                    recalcAll(true);
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
                        text: 'Diskon 0% tidak perlu diajukan.'
                    });
                    return;
                }

                if (approvalStatus === 'pending') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Masih menunggu approval',
                        text: 'Pengajuan diskon ini masih menunggu keputusan manager.'
                    });
                    return;
                }

                const {
                    value: reason
                } = await Swal.fire({
                    title: 'Ajukan Diskon',
                    input: 'textarea',
                    inputLabel: 'Alasan diskon (wajib)',
                    inputPlaceholder: 'Contoh: pelanggan tetap / promo / koreksi harga...',
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
                        const msg = getFirstErrorMessage(data) || 'Gagal mengajukan diskon.';
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
                        title: 'Berhasil',
                        text: data.message || 'Diskon berhasil diajukan.'
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

            function hitungKembalian() {
                const totalBayar = parseFloat(totalSetelahDiskonHidden?.value || 0) || 0;
                const diterima = parseFloat(onlyDigits(uangDiterimaInput?.value)) || 0;
                const kembalian = diterima - totalBayar;

                if (uangKembalianInput) {
                    uangKembalianInput.value = "Rp " + formatRupiah(Math.max(kembalian, 0));
                }
            }

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

            btnAjukanDiskon?.addEventListener('click', requestApprovalDiskon);

            document.querySelectorAll('.diskon-item').forEach(inp => {
                inp.addEventListener('input', function() {
                    if (approvalStatus === 'approved' || approvalStatus === 'pending') return;
                    recalcAll(true);
                });
            });

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

            btnLanjut?.addEventListener("click", async (e) => {
                e.preventDefault();

                recalcAll(true);

                const selected = pilihMetode?.options[pilihMetode.selectedIndex];
                if (!selected || !selected.value) {
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
                        title: 'Pengajuan masih diproses',
                        text: 'Diskon item sedang menunggu approval manager.'
                    });
                    return;
                }

                if (normalized.length > 0 && latestStatus !== 'approved') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Diskon belum diajukan / disetujui',
                        text: 'Ada item yang diberi diskon. Silakan klik "Ajukan Diskon" dulu dan tunggu approval.'
                    });
                    btnAjukanDiskon?.classList.remove('hidden');
                    return;
                }

                if (latestStatus === 'approved') {
                    const currentStr = JSON.stringify(normalized);
                    if (currentStr !== approvalSnapshotStr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Data diskon berubah',
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
                            text: 'Unggah file gambar yang valid.'
                        });
                        fileInput.value = "";
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = (ev) => {
                        previewContainer.innerHTML = `
                            <img src="${ev.target.result}" alt="Preview Bukti Pembayaran"
                                 class="h-[260px] w-full rounded-2xl object-cover shadow-md" />
                        `;
                        textGantiGambar?.classList.remove("hidden");
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
                            title: 'Masih menunggu approval',
                            text: 'Diskon item sedang menunggu approval manager.'
                        });
                        return;
                    }

                    if (normalized.length > 0 && selectedStatus !== 'approved') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Diskon belum disetujui',
                            text: 'Ada diskon item yang belum disetujui manager.'
                        });
                        return;
                    }

                    if (selectedStatus === 'approved') {
                        const currentStr = JSON.stringify(normalized);
                        if (currentStr !== approvalSnapshotStr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Data diskon berubah',
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
                    formData.set('total_tagihan', totalAwal);
                    formData.set('total_setelah_diskon', totalSesudahDiskon);
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
                            title: 'Masih menunggu approval',
                            text: 'Diskon item sedang menunggu approval manager.'
                        });
                        return;
                    }

                    if (normalized.length > 0 && selectedStatus !== 'approved') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Diskon belum disetujui',
                            text: 'Ada diskon item yang belum disetujui manager.'
                        });
                        return;
                    }

                    if (selectedStatus === 'approved') {
                        const currentStr = JSON.stringify(normalized);
                        if (currentStr !== approvalSnapshotStr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Data diskon berubah',
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
                    formData.set('total_setelah_diskon', totalSetelahDiskonHidden?.value || totalAwal);
                    formData.set('diskon_items', JSON.stringify(normalized));

                    const bukti = formData.get('bukti_pembayaran');
                    if (!(bukti instanceof File) || bukti.size === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Bukti transfer belum dipilih',
                            text: 'Silakan upload bukti transfer.'
                        });
                        return;
                    }

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
