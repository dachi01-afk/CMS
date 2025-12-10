{{-- resources/views/kasir/pembayaran/proses-pembayaran-layanan.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="App Clinic" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CMS-Royal-Klinik - Pembayaran Layanan</title>
    <link href="{{ asset('storage/assets/royal_klinik.svg') }}" rel="shortcut icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Font-Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Flowbite JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.1/flowbite.min.js" defer></script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-white dark:bg-gray-900">

    <section class="py-8 md:py-16">
        <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
            <div class="mx-auto max-w-3xl">

                <h2 class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">
                    Detail Transaksi Layanan
                </h2>

                {{-- INFORMASI PASIEN & KUNJUNGAN / TRANSAKSI --}}
                <div class="mt-6 space-y-4 border-y border-gray-200 py-8 dark:border-gray-700 sm:mt-8">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $summary->pasien->nama_pasien ?? '-' }}
                    </h4>

                    <dl class="space-y-2">
                        <div>
                            <dt class="text-base font-medium text-gray-900 dark:text-white">
                                Tanggal Kunjungan
                            </dt>
                            <dd class="mt-1 text-base font-normal text-gray-500 dark:text-gray-400">
                                {{-- Tanggal Kunjungan --}}
                                @if (optional($summary->kunjungan)->tanggal_kunjungan)
                                    {{ \Carbon\Carbon::parse($summary->kunjungan->tanggal_kunjungan)->timezone('Asia/Jakarta')->translatedFormat('l, d F Y') }}
                                @else
                                    -
                                @endif

                            </dd>
                        </div>

                        <div>
                            <dt class="text-base font-medium text-gray-900 dark:text-white">
                                Kode Transaksi Layanan
                            </dt>
                            <dd class="mt-1 text-base font-normal text-gray-500 dark:text-gray-400">
                                {{ $summary->kode_transaksi ?? '-' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- DAFTAR LAYANAN (SATU LAYANAN PER BARIS PENJUALAN_LAYANAN) --}}
                <div class="mt-6 sm:mt-8">
                    <div class="relative overflow-x-auto border-b border-gray-200 dark:border-gray-800">
                        <table class="w-full text-left font-medium text-gray-900 dark:text-white md:table-fixed">
                            <thead
                                class="bg-gray-50 dark:bg-gray-800 text-xs uppercase text-gray-500 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Layanan</th>
                                    <th class="px-4 py-3 text-center">Jumlah</th>
                                    <th class="px-4 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                @forelse ($items as $item)
                                    @php
                                        $hargaSatuan = $item->layanan->harga_layanan ?? null;
                                        $jumlah = $item->jumlah ?? 1;
                                        $subtotal =
                                            $item->sub_total ??
                                            ($item->total_tagihan ?? ($hargaSatuan ? $hargaSatuan * $jumlah : 0));
                                    @endphp
                                    <tr>
                                        <td class="whitespace-nowrap py-4 md:w-[320px] px-4">
                                            {{ $item->layanan->nama_layanan ?? '-' }}
                                            @if (optional($item->layanan->kategoriLayanan)->nama_kategori)
                                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                                    Kategori: {{ $item->layanan->kategoriLayanan->nama_kategori }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-center text-base font-normal">
                                            x{{ $jumlah }}
                                        </td>
                                        <td class="px-4 py-4 text-right text-base font-bold">
                                            Rp{{ number_format($subtotal, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            Data layanan tidak ditemukan pada transaksi ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- RINGKASAN & METODE PEMBAYARAN --}}
                    <div class="mt-4 space-y-6">
                        <h4 class="text-xl font-semibold text-gray-900 dark:text-white">
                            Ringkasan Transaksi
                        </h4>

                        <div class="space-y-4">
                            {{-- total awal (sebelum diskon) --}}
                            <dl class="flex items-center justify-between gap-4">
                                <dt class="text-gray-500 dark:text-gray-400">Total Harga Layanan</dt>
                                <dd class="text-base font-medium text-gray-900 dark:text-white">
                                    Rp{{ number_format($summary->total_tagihan ?? 0, 0, ',', '.') }}
                                </dd>
                            </dl>

                            {{-- input diskon persen --}}
                            <dl class="flex items-center justify-between gap-4">
                                <dt class="text-gray-500 dark:text-gray-400">Diskon (%)</dt>
                                <dd class="flex-1 text-right">
                                    <input type="number" id="diskon_persen" min="0" max="100"
                                        value="0"
                                        class="w-32 text-right rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white" />
                                </dd>
                            </dl>

                            {{-- subtotal setelah diskon (tampil realtime) --}}
                            <dl class="flex items-center justify-between gap-4">
                                <dt class="text-gray-500 dark:text-gray-400">Subtotal Setelah Diskon</dt>
                                <dd id="subtotal_setelah_diskon_display"
                                    class="text-base font-semibold text-gray-900 dark:text-white">
                                    Rp{{ number_format($summary->total_tagihan ?? 0, 0, ',', '.') }}
                                </dd>
                            </dl>

                            {{-- hidden untuk dipakai JS & dikirim ke backend --}}
                            <input type="hidden" id="total_setelah_diskon" value="{{ $summary->total_tagihan ?? 0 }}">
                        </div>

                        <div class="space-y-4 mt-4">
                            {{-- Metode Pembayaran --}}
                            <dl
                                class="flex items-center justify-between gap-4 border-t border-gray-200 pt-3 dark:border-gray-700">
                                <dt class="text-lg font-bold text-gray-900 dark:text-white">
                                    Metode Pembayaran
                                </dt>
                                <dd>
                                    <select id="pilih-metode-pembayaran"
                                        class="text-sm md:text-base font-medium text-gray-900 dark:text-white rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800">
                                        @foreach ($dataMetodePembayaran as $metodePembayaran)
                                            <option value="{{ $metodePembayaran->id }}">
                                                {{ $metodePembayaran->nama_metode }}
                                            </option>
                                        @endforeach
                                    </select>
                                </dd>
                            </dl>

                            {{-- total tagihan akhir (ikut subtotal setelah diskon) --}}
                            <dl
                                class="flex items-center justify-between gap-4 border-t border-gray-200 pt-3 dark:border-gray-700">
                                <dt class="text-lg font-bold text-gray-900 dark:text-white">Total Tagihan</dt>
                                <dd class="text-lg font-bold text-gray-900 dark:text-white" id="total_tagihan_display">
                                    Rp{{ number_format($summary->total_tagihan ?? 0, 0, ',', '.') }}
                                </dd>
                            </dl>
                        </div>

                        <div class="gap-4 sm:flex sm:items-center">
                            <a href="{{ route('kasir.pembayaran') }}"
                                class="w-full rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                Kembali ke daftar transaksi layanan
                            </a>

                            <button type="button" id="btnLanjutPembayaran"
                                class="mt-4 flex w-full items-center justify-center rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-800 sm:mt-0">
                                Lanjutkan Pembayaran
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- total awal (hidden) --}}
    <input type="hidden" id="total_tagihan_awal" value="{{ $summary->total_tagihan ?? 0 }}">

    {{-- ===================== MODAL CASH ===================== --}}
    <div id="pembayaranCash" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-md p-4">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800">

                <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Konfirmasi Pembayaran (Cash)
                    </h3>
                    <button type="button" data-modal-hide="pembayaranCash"
                        class="text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg text-sm p-1.5">✖</button>
                </div>

                <form id="formPembayaranCash" action="{{ route('kasir.layanan.pembayaran.cash') }}" method="POST">
                    @csrf
                    <div class="p-4 space-y-4">
                        <input type="hidden" name="id" value="{{ $summary->id_utama }}">
                        <input type="hidden" name="metode_pembayaran_id" id="metode-pembayaran-cash" value="">
                        <input type="hidden" name="kode_transaksi" value="{{ $summary->kode_transaksi }}">

                        {{-- Total Tagihan (akan diisi total setelah diskon) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Total Tagihan
                            </label>
                            <div class="relative mt-1">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-300">
                                    Rp
                                </span>
                                <input type="text" id="total_tagihan_cash" readonly
                                    value="{{ number_format($summary->total_tagihan ?? 0, 0, ',', '.') }}"
                                    class="w-full pl-10 rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white" />
                            </div>
                        </div>

                        {{-- Uang Diterima --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Uang yang Diterima
                            </label>
                            <div class="relative mt-1">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-300">
                                    Rp
                                </span>
                                <input type="text" name="uang_yang_diterima" id="uang_diterima"
                                    placeholder="Masukkan nominal"
                                    class="w-full pl-10 rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white" />
                            </div>
                        </div>

                        {{-- Kembalian --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Uang Kembalian
                            </label>
                            <input type="text" name="kembalian" id="uang_kembalian" readonly
                                class="w-full mt-1 rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white pl-3" />
                        </div>
                    </div>

                    <div class="flex justify-end items-center p-4 border-t dark:border-gray-700">
                        <button data-modal-hide="pembayaranCash" type="button"
                            class="text-gray-500 bg-white hover:bg-gray-100 border border-gray-200 rounded-lg px-5 py-2.5 text-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                            Batal
                        </button>
                        <button id="btnSubmitPembayaranCash" type="submit"
                            class="ms-2 text-white bg-primary-700 hover:bg-primary-800 font-medium rounded-lg text-sm px-5 py-2.5">
                            Bayar Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== MODAL TRANSFER ===================== --}}
    <div id="pembayaranTransfer" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-2xl p-4">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800">

                <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Konfirmasi Pembayaran (Transfer)
                    </h3>
                    <button type="button" data-modal-hide="pembayaranTransfer"
                        class="text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg text-sm p-1.5">✖</button>
                </div>

                <form id="formPembayaranTransfer" action="{{ route('kasir.layanan.pembayaran.transfer') }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="p-4 space-y-4">
                        <input type="hidden" name="id" value="{{ $summary->id_utama }}">
                        <input type="hidden" name="kode_transaksi" value="{{ $summary->kode_transaksi }}">
                        <input type="hidden" name="metode_pembayaran_id" id="metode-pembayaran-transfer"
                            value="">

                        {{-- Total Tagihan (akan diisi total setelah diskon) --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Total Tagihan
                            </label>
                            <div class="relative mt-1">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-300">
                                    Rp
                                </span>
                                <input type="text" id="total_tagihan_transfer" readonly
                                    value="{{ number_format($summary->total_tagihan ?? 0, 0, ',', '.') }}"
                                    class="w-full pl-10 rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white" />
                            </div>
                        </div>

                        {{-- Upload Bukti Transfer --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Upload Bukti Transfer
                            </label>
                            <div class="flex items-center justify-center w-full px-5 py-3">
                                <label for="upload"
                                    class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 dark:hover:bg-gray-800 dark:bg-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500">
                                    <div class="flex flex-col items-center justify-center w-full h-full pt-5 pb-6"
                                        id="preview-bukti-pembayaran">
                                        <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                            <path stroke="currentColor" stroke-linecap="round"
                                                stroke-linejoin="round" stroke-width="2"
                                                d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                                        </svg>
                                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                            <span class="font-semibold">Click to upload</span> or drag and drop
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            PNG, JPG atau GIF (maks. 5MB)
                                        </p>
                                    </div>
                                    <input id="upload" type="file" class="hidden" accept="image/*"
                                        name="bukti_pembayaran" />
                                </label>
                            </div>
                            <p id="text-ganti-gambar"
                                class="mt-2 text-sm text-gray-500 dark:text-gray-400 text-center hidden">
                                Klik untuk ganti gambar
                            </p>
                        </div>
                    </div>

                    <div class="flex justify-end items-center p-4 border-t dark:border-gray-700">
                        <button data-modal-hide="pembayaranTransfer" type="button"
                            class="text-gray-500 bg-white hover:bg-gray-100 border border-gray-200 rounded-lg px-5 py-2.5 text-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                            Batal
                        </button>
                        <button type="submit"
                            class="ms-2 text-white bg-primary-700 hover:bg-primary-800 font-medium rounded-lg text-sm px-5 py-2.5">
                            Kirim Bukti Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ===================== JS LOGIC (DISKON PERSEN + CASH/TFR) ===================== --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uangDiterimaInput = document.getElementById('uang_diterima');
            const uangKembalianInput = document.getElementById('uang_kembalian');

            const pilihMetode = document.getElementById('pilih-metode-pembayaran');
            const btnLanjut = document.getElementById('btnLanjutPembayaran');

            const modalCash = document.getElementById('pembayaranCash');
            const modalTransfer = document.getElementById('pembayaranTransfer');

            // === DISKON & TOTAL ===
            const totalAwalInput = document.getElementById('total_tagihan_awal');
            const diskonInput = document.getElementById('diskon_persen');
            const subtotalDisplay = document.getElementById('subtotal_setelah_diskon_display');
            const totalSetelahDiskonInput = document.getElementById('total_setelah_diskon');
            const totalTagihanDisplay = document.getElementById('total_tagihan_display');

            const totalCashInput = document.getElementById('total_tagihan_cash');
            const totalTransferInput = document.getElementById('total_tagihan_transfer');

            const totalAwal = parseFloat(totalAwalInput?.value || '0') || 0;

            const metodeCashInput = document.getElementById('metode-pembayaran-cash');
            const metodeTransferInput = document.getElementById('metode-pembayaran-transfer');

            function onlyDigits(value) {
                return value ? String(value).replace(/[^\d]/g, '') : '';
            }

            function formatRupiah(value) {
                return new Intl.NumberFormat('id-ID').format(value);
            }

            // Hitung total setelah diskon & update ke semua tampilan
            function updateTotalSetelahDiskon() {
                const persen = parseFloat(diskonInput?.value || '0') || 0;

                let potongan = totalAwal * (persen / 100);
                if (potongan > totalAwal) potongan = totalAwal;

                const totalSetelah = totalAwal - potongan;

                if (subtotalDisplay) {
                    subtotalDisplay.textContent = 'Rp' + formatRupiah(totalSetelah);
                }

                if (totalTagihanDisplay) {
                    totalTagihanDisplay.textContent = 'Rp' + formatRupiah(totalSetelah);
                }

                if (totalSetelahDiskonInput) {
                    totalSetelahDiskonInput.value = totalSetelah;
                }

                if (totalCashInput) {
                    totalCashInput.value = formatRupiah(totalSetelah);
                }

                if (totalTransferInput) {
                    totalTransferInput.value = formatRupiah(totalSetelah);
                }

                // setiap kali total berubah, kembalian di-recalc
                hitungKembalian();
            }

            if (diskonInput) {
                diskonInput.addEventListener('input', updateTotalSetelahDiskon);
            }

            // inisialisasi awal
            updateTotalSetelahDiskon();

            function openModal(modal) {
                if (!modal) return;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.documentElement.style.overflow = 'hidden';
            }

            function closeModal(modal) {
                if (!modal) return;
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.documentElement.style.overflow = '';
            }

            function closeAll() {
                closeModal(modalCash);
                closeModal(modalTransfer);
            }

            // === PILIH METODE & BUKA MODAL ===
            if (btnLanjut) {
                btnLanjut.addEventListener('click', function(e) {
                    e.preventDefault();
                    const selected = pilihMetode?.options[pilihMetode.selectedIndex];
                    if (!selected) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Peringatan',
                            text: 'Pilih metode pembayaran terlebih dahulu.'
                        });
                        return;
                    }

                    const metodeID = selected.value;
                    const metodeText = selected.textContent.toLowerCase();

                    if (metodeCashInput) metodeCashInput.value = metodeID;
                    if (metodeTransferInput) metodeTransferInput.value = metodeID;

                    closeAll();

                    if (metodeText.includes('cash')) {
                        openModal(modalCash);
                    } else if (metodeText.includes('transfer')) {
                        openModal(modalTransfer);
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Metode belum didukung',
                            text: 'Metode pembayaran ini belum di-handle di front-end.'
                        });
                    }
                });
            }

            // TOMBOL CLOSE
            document.querySelectorAll('#pembayaranCash [data-modal-hide], #pembayaranTransfer [data-modal-hide]')
                .forEach(btn => {
                    btn.addEventListener('click', () => {
                        const modal = btn.closest('#pembayaranCash') || btn.closest(
                            '#pembayaranTransfer');
                        if (modal) {
                            const forms = modal.querySelectorAll('form');
                            forms.forEach(f => f.reset());

                            const preview = modal.querySelector('#preview-bukti-pembayaran');
                            if (preview) {
                                preview.innerHTML = `
                                <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true"
                                     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2" />
                                </svg>
                                <p class="mb-2 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="font-semibold">Click to upload</span> or drag and drop
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    PNG, JPG atau GIF (maks. 5MB)
                                </p>`;
                            }

                            const textGanti = modal.querySelector('#text-ganti-gambar');
                            if (textGanti) textGanti.classList.add('hidden');

                            updateTotalSetelahDiskon();
                            closeModal(modal);
                        }
                    });
                });

            // TUTUP MODAL LEWAT OVERLAY
            [modalCash, modalTransfer].forEach(modal => {
                if (!modal) return;
                modal.addEventListener('click', ev => {
                    if (ev.target === modal) closeModal(modal);
                });
            });

            // ESC
            document.addEventListener('keydown', ev => {
                if (ev.key === 'Escape') closeAll();
            });

            // === HITUNG KEMBALIAN (CASH) ===
            function hitungKembalian() {
                const totalSesudahDiskon = parseFloat(totalSetelahDiskonInput?.value || totalAwal) || 0;
                const diterima = parseFloat(onlyDigits(uangDiterimaInput?.value)) || 0;
                const kembali = diterima - totalSesudahDiskon;

                if (uangKembalianInput) {
                    uangKembalianInput.value =
                        'Rp ' + (kembali >= 0 ? formatRupiah(kembali) : '0');
                }
            }

            if (uangDiterimaInput) {
                uangDiterimaInput.addEventListener('input', e => {
                    let angka = onlyDigits(e.target.value);
                    e.target.value = angka ? formatRupiah(angka) : '';
                    hitungKembalian();
                });
            }

            // === PREVIEW GAMBAR (TRANSFER) ===
            const fileInput = document.getElementById('upload');
            const previewContainer = document.getElementById('preview-bukti-pembayaran');
            const textGantiGambar = document.getElementById('text-ganti-gambar');

            if (fileInput) {
                fileInput.addEventListener('change', event => {
                    const file = event.target.files[0];
                    if (!file) return;

                    if (!file.type.startsWith('image/')) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'File bukan gambar',
                            text: 'Unggah file gambar saja.'
                        });
                        fileInput.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = ev => {
                        previewContainer.innerHTML = `
                        <img src="${ev.target.result}" alt="Preview Bukti Pembayaran"
                             class="object-cover w-full h-64 rounded-lg shadow-md" />`;
                        textGantiGambar.classList.remove('hidden');
                    };
                    reader.readAsDataURL(file);
                });
            }

            // === SUBMIT CASH ===
            const formPembayaranCash = document.getElementById('formPembayaranCash');
            if (formPembayaranCash) {
                formPembayaranCash.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const totalSesudahDiskon = parseFloat(totalSetelahDiskonInput?.value ||
                        totalAwal) || 0;
                    const uangDiterimaClean = parseFloat(onlyDigits(uangDiterimaInput?.value)) || 0;
                    const kembalianClean = uangDiterimaClean - totalSesudahDiskon;
                    const diskonPersen = parseFloat(diskonInput?.value || '0') || 0;

                    if (uangDiterimaClean === 0 || uangDiterimaClean < totalSesudahDiskon) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Uang kurang',
                            text: 'Nominal uang yang diterima belum cukup.'
                        });
                        return;
                    }

                    const formData = new FormData(this);
                    formData.set('uang_yang_diterima', uangDiterimaClean);
                    formData.set('kembalian', kembalianClean);
                    formData.set('total_tagihan', totalAwal); // sebelum diskon
                    formData.set('total_setelah_diskon', totalSesudahDiskon); // setelah diskon
                    formData.set('diskon_tipe', diskonPersen > 0 ? 'persen' : '');
                    formData.set('diskon_nilai', diskonPersen);

                    const submitBtn = document.getElementById('btnSubmitPembayaranCash');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
                    }

                    try {
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                        const response = await fetch(this.action, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const data = await response.json();
                        if (data.success) {
                            await Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message || 'Pembayaran berhasil.'
                            });
                            window.location.href = "{{ route('kasir.pembayaran') }}";
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: data.message || 'Gagal memproses pembayaran.'
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
                        const submitBtn = document.getElementById('btnSubmitPembayaranCash');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                        }
                    }
                });
            }

            // === SUBMIT TRANSFER ===
            const formPembayaranTransfer = document.getElementById('formPembayaranTransfer');
            if (formPembayaranTransfer) {
                formPembayaranTransfer.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);

                    const totalSesudahDiskon = parseFloat(totalSetelahDiskonInput?.value ||
                        totalAwal) || 0;
                    const diskonPersen = parseFloat(diskonInput?.value || '0') || 0;

                    formData.set('total_tagihan', totalAwal);
                    formData.set('total_setelah_diskon', totalSesudahDiskon);
                    formData.set('diskon_tipe', diskonPersen > 0 ? 'persen' : '');
                    formData.set('diskon_nilai', diskonPersen);

                    const bukti = formData.get('bukti_pembayaran');
                    if (!(bukti instanceof File) || bukti.size === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Bukti belum diupload',
                            text: 'Silakan unggah bukti transfer terlebih dahulu.'
                        });
                        return;
                    }
                    if (bukti.type && !bukti.type.startsWith('image/')) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Format tidak didukung',
                            text: 'File harus berupa gambar.'
                        });
                        return;
                    }
                    const MAX_MB = 5;
                    if (bukti.size > MAX_MB * 1024 * 1024) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'File terlalu besar',
                            text: `Ukuran maksimal ${MAX_MB} MB.`
                        });
                        return;
                    }

                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
                    }

                    try {
                        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
                        const response = await fetch(this.action, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': csrf,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });

                        const ct = response.headers.get('Content-Type') || '';
                        const data = ct.includes('application/json') ?
                            await response.json() : {
                                success: response.ok,
                                message: response.ok ? 'OK' : 'Gagal'
                            };

                        if (data.success) {
                            await Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message || 'Bukti transfer terkirim.'
                            });
                            window.location.href = "{{ route('kasir.pembayaran') }}";
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: data.message || 'Gagal memproses pembayaran.'
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
                        const submitBtn = this.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                        }
                    }
                });
            }
        });
    </script>

</body>

</html>
