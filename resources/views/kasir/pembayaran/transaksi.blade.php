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

    <!-- Flowbite JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/1.8.1/flowbite.min.js" defer></script>

    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <section class="bg-white py-8 antialiased dark:bg-gray-900 md:py-16">
        <div class="mx-auto max-w-screen-xl px-4 2xl:px-0">
            <div class="mx-auto max-w-3xl">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white sm:text-2xl">Detail Transaksi</h2>

                <div class="mt-6 space-y-4 border-b border-t border-gray-200 py-8 dark:border-gray-700 sm:mt-8">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $dataPembayaran->emr->kunjungan->pasien->nama_pasien }}
                    </h4>

                    <dl>
                        <dt class="text-base font-medium text-gray-900 dark:text-white">Tanggal Kunjungan</dt>
                        <dd class="mt-1 text-base font-normal text-gray-500 dark:text-gray-400">
                            {{ \Carbon\Carbon::parse($dataPembayaran->emr->kunjungan->tanggal_kunjungan)->timezone('Asia/Jakarta')->translatedFormat('l, d F Y') }}
                        </dd>
                    </dl>
                </div>

                <div class="mt-6 sm:mt-8">
                    <div class="relative overflow-x-auto border-b border-gray-200 dark:border-gray-800">
                        <table class="w-full text-left font-medium text-gray-900 dark:text-white md:table-fixed">
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                {{-- Resep Obat (optional) --}}
                                @foreach (data_get($dataPembayaran, 'emr.resep.obat', []) as $o)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 md:w-[384px]">
                                            <label>{{ $o->nama_obat }}</label>
                                        </td>
                                        <td class="p-4 text-base font-normal text-gray-900 dark:text-white">
                                            x{{ $o->pivot->jumlah ?? 1 }}
                                        </td>
                                        <td class="p-4 text-right text-base font-bold text-gray-900 dark:text-white">
                                            Rp{{ number_format(($o->total_harga ?? 0) * ($o->pivot->jumlah ?? 1), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- Jika tidak ada resep, bisa tampilkan baris info (opsional) --}}
                                @if (empty(data_get($dataPembayaran, 'emr.resep.obat')))
                                    <tr>
                                        <td class="py-4 text-sm text-gray-500 dark:text-gray-400" colspan="3">
                                            Tidak ada resep untuk kunjungan ini.
                                        </td>
                                    </tr>
                                @endif

                                {{-- Layanan (punya data) --}}
                                @foreach ($dataPembayaran->emr->kunjungan->layanan as $l)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 md:w-[384px]">
                                            <label>{{ $l->nama_layanan }}</label>
                                        </td>
                                        <td class="p-4 text-base font-normal text-gray-900 dark:text-white">
                                            x{{ $l->pivot->jumlah ?? 1 }}
                                        </td>
                                        <td class="p-4 text-right text-base font-bold text-gray-900 dark:text-white">
                                            Rp{{ number_format(($l->harga_layanan ?? 0) * ($l->pivot->jumlah ?? 1), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 space-y-6">
                        <h4 class="text-xl font-semibold text-gray-900 dark:text-white">Ringkasan Transaksi</h4>

                        <div class="space-y-4">
                            {{-- total awal (sebelum diskon) --}}
                            <dl class="flex items-center justify-between gap-4">
                                <dt class="text-gray-500 dark:text-gray-400">Total Harga</dt>
                                <dd class="text-base font-medium text-gray-900 dark:text-white">
                                    Rp{{ number_format($dataPembayaran->total_tagihan, 0, ',', '.') }}
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
                                    Rp{{ number_format($dataPembayaran->total_tagihan, 0, ',', '.') }}
                                </dd>
                            </dl>

                            {{-- hidden untuk dipakai JS & dikirim ke backend --}}
                            <input type="hidden" id="total_setelah_diskon"
                                value="{{ $dataPembayaran->total_tagihan }}">
                        </div>

                        <div class="space-y-4 mt-4">
                            {{-- Metode Pembayar --}}
                            <dl
                                class="flex items-center justify-between gap-4 border-t border-gray-200 pt-2 dark:border-gray-700">
                                <dt class="text-lg font-bold text-gray-900 dark:text-white">Metode Pembayaran</dt>
                                <select class="text-lg font-bold text-gray-900 dark:text-white rounded-md"
                                    id="pilih-metode-pembayaran">
                                    @foreach ($dataMetodePembayaran as $metodePembayaran)
                                        <option value="{{ $metodePembayaran->id }}">
                                            {{ $metodePembayaran->nama_metode }}</option>
                                    @endforeach
                                </select>
                            </dl>

                            {{-- total tagihan akhir (ikut subtotal setelah diskon) --}}
                            <dl
                                class="flex items-center justify-between gap-4 border-t border-gray-200 pt-2 dark:border-gray-700">
                                <dt class="text-lg font-bold text-gray-900 dark:text-white">Total Tagihan</dt>
                                <dd class="text-lg font-bold text-gray-900 dark:text-white" id="total_tagihan_display">
                                    Rp{{ number_format($dataPembayaran->total_tagihan, 0, ',', '.') }}
                                </dd>
                            </dl>
                        </div>

                        <div class="gap-4 sm:flex sm:items-center">
                            <a href="{{ route('kasir.pembayaran') }}"
                                class="w-full rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                Kembali ke halaman kasir
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
    <input type="hidden" id="total_tagihan_awal" value="{{ $dataPembayaran->total_tagihan }}">

    <!-- Modal Pembayaran CASH -->
    <div id="pembayaranCash" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-md p-4">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800">
                <!-- Header -->
                <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Konfirmasi Pembayaran</h3>
                    <button type="button" data-modal-hide="pembayaranModal"
                        class="text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg text-sm p-1.5">✖</button>
                </div>

                <!-- Body -->
                <form id="formPembayaranCash" action="{{ route('kasir.pembayaran.cash') }}" method="POST">
                    @csrf
                    <div class="p-4 space-y-4">
                        <input type="hidden" name="id" value="{{ $dataPembayaran->id }}">
                        <input type="hidden" name="metode_pembayaran_id" id="metode-pembayaran-cash"
                            value="{{ $dataPembayaran->id }}">

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total
                                Tagihan</label>
                            <div class="relative mt-1">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-300">Rp</span>
                                <!-- NOTE: simpan format ribuan di tampilan, JS akan membersihkannya -->
                                <input type="text" id="total_tagihan" readonly
                                    value="{{ number_format($dataPembayaran->total_tagihan, 0, ',', '.') }}"
                                    class="w-full pl-10 rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Uang yang
                                Diterima</label>
                            <div class="relative mt-1">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-300">Rp</span>
                                <input type="text" name="uang_yang_diterima" id="uang_diterima"
                                    placeholder="Masukkan nominal"
                                    class="w-full pl-10 rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white" />
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Uang
                                Kembalian</label>
                            <div class="relative mt-1">
                                <input type="text" name="kembalian" id="uang_kembalian" readonly
                                    class="w-full mt-1 rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white" />
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex justify-end items-center p-4 border-t dark:border-gray-700">
                        <button data-modal-hide="pembayaranModal" type="button"
                            class="text-gray-500 bg-white hover:bg-gray-100 border border-gray-200 rounded-lg px-5 py-2.5 text-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">
                            Batal
                        </button>
                        <button id="btnSubmitPembayaran" type="submit"
                            class="ms-2 text-white bg-primary-700 hover:bg-primary-800 font-medium rounded-lg text-sm px-5 py-2.5">
                            Bayar Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal TRANSFER -->
    <div id="pembayaranTransfer" tabindex="-1" aria-hidden="true"
        class="hidden fixed inset-0 z-50 items-center justify-center bg-black bg-opacity-50">
        <div class="relative w-full max-w-2xl p-4">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800">
                <!-- Header -->
                <div class="flex justify-between items-center p-4 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Konfirmasi Pembayar Metode Transfer
                    </h3>
                    <button type="button" data-modal-hide="transferModal"
                        class="text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg text-sm p-1.5">✖</button>
                </div>

                <!-- Body -->
                <form id="formPembayaranTransfer" action="{{ route('kasir.pembayaran.transfer') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="p-4 space-y-4">
                        <input type="hidden" name="id" value="{{ $dataPembayaran->id }}">
                        <input type="hidden" name="metode_pembayaran_id" id="metode-pembayaran-transfer"
                            value="">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total
                                Tagihan</label>
                            <div class="relative mt-1">
                                <span
                                    class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-300">Rp</span>
                                <!-- NOTE: simpan format ribuan di tampilan, JS akan membersihkannya -->
                                <input type="text" id="total_tagihan" readonly
                                    value="{{ number_format($dataPembayaran->total_tagihan, 0, ',', '.') }}"
                                    class="w-full pl-10 rounded-lg border-gray-300 dark:bg-gray-700 dark:text-white" />
                            </div>
                        </div>

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
                                            SVG, PNG, JPG or GIF (MAX. 800x400px)
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

                    <!-- Footer -->
                    <div class="flex justify-end items-center p-4 border-t dark:border-gray-700">
                        <button data-modal-hide="transferModal" type="button"
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

    <!-- JS Section -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uangDiterimaInput = document.getElementById('uang_diterima');
            const uangKembalianInput = document.getElementById('uang_kembalian');

            const pilihMetode = document.getElementById("pilih-metode-pembayaran");
            const btnLanjut = document.getElementById("btnLanjutPembayaran");

            const modalCash = document.getElementById('pembayaranCash');
            const modalTransfer = document.getElementById('pembayaranTransfer');

            // === DISKON & TOTAL ===
            const totalAwalInput = document.getElementById('total_tagihan_awal');
            const diskonInput = document.getElementById('diskon_persen');
            const subtotalDisplay = document.getElementById('subtotal_setelah_diskon_display');
            const totalSetelahDiskonHidden = document.getElementById('total_setelah_diskon');
            const totalTagihanDisplay = document.getElementById('total_tagihan_display');
            const totalInputsAll = document.querySelectorAll('input#total_tagihan');

            const totalAwal = parseFloat(totalAwalInput?.value || '0') || 0;

            function onlyDigits(value) {
                return value ? String(value).replace(/[^\d]/g, '') : '';
            }

            function formatRupiah(value) {
                return new Intl.NumberFormat("id-ID").format(value);
            }

            // Hitung total setelah diskon & update ke semua tampilan
            function updateTotalSetelahDiskon() {
                const persen = parseFloat(diskonInput?.value) || 0;

                let potongan = totalAwal * (persen / 100);
                if (potongan > totalAwal) potongan = totalAwal;

                const totalSetelah = totalAwal - potongan;

                if (subtotalDisplay) {
                    subtotalDisplay.textContent = "Rp" + formatRupiah(totalSetelah);
                }

                if (totalTagihanDisplay) {
                    totalTagihanDisplay.textContent = "Rp" + formatRupiah(totalSetelah);
                }

                if (totalSetelahDiskonHidden) {
                    totalSetelahDiskonHidden.value = totalSetelah;
                }

                // update semua input total_tagihan di modal cash & transfer
                totalInputsAll.forEach(inp => {
                    inp.value = formatRupiah(totalSetelah);
                });

                // setiap total berubah, kembalian harus dihitung ulang
                hitungKembalian();
            }

            if (diskonInput) {
                diskonInput.addEventListener('input', updateTotalSetelahDiskon);
            }

            // inisialisasi awal (tanpa diskon)
            updateTotalSetelahDiskon();

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

            // === OPEN MODAL SESUAI METODE ===
            if (btnLanjut) {
                btnLanjut.addEventListener("click", (e) => {
                    e.preventDefault();
                    const selected = pilihMetode?.options[pilihMetode.selectedIndex];
                    if (!selected) return alert("Pilih metode pembayaran dulu.");

                    const metodeID = selected.value;
                    const metodeText = selected.textContent.toLowerCase();

                    // Masukkan ID ke input hidden
                    const cashInput = document.getElementById("metode-pembayaran-cash");
                    const transferInput = document.getElementById("metode-pembayaran-transfer");
                    if (cashInput) cashInput.value = metodeID;
                    if (transferInput) transferInput.value = metodeID;

                    closeAll();
                    if (metodeText.includes("cash")) openModal(modalCash);
                    else if (metodeText.includes("transfer")) openModal(modalTransfer);
                    else alert("Metode pembayaran belum dikenali: " + selected.textContent);
                });
            }

            // === TOMBOL CLOSE SAJA YANG NGE-TUTUP MODAL (bukan semua button!) ===
            document.querySelectorAll("#pembayaranCash [data-modal-hide], #pembayaranTransfer [data-modal-hide]")
                .forEach(btn => {
                    btn.addEventListener("click", () => {
                        const modal = btn.closest("#pembayaranCash") || btn.closest(
                            "#pembayaranTransfer");

                        // reset form & preview hanya saat explicit close
                        if (modal) {
                            const forms = modal.querySelectorAll("form");
                            forms.forEach(form => form.reset());

                            const preview = modal.querySelector("#preview-bukti-pembayaran");
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
              <p class="text-xs text-gray-500 dark:text-gray-400">SVG, PNG, JPG or GIF (MAX. 800x400px)</p>`;
                            }

                            const textGanti = modal.querySelector("#text-ganti-gambar");
                            if (textGanti) textGanti.classList.add("hidden");

                            // ⬅️ PENTING: setelah form di-reset, apply lagi total setelah diskon ke semua input total_tagihan
                            if (typeof updateTotalSetelahDiskon === 'function') {
                                updateTotalSetelahDiskon();
                            }

                            closeModal(modal);
                        }
                    });
                });

            // === TUTUP MODAL LEWAT KLIK OVERLAY ===
            [modalCash, modalTransfer].forEach(modal => {
                if (!modal) return;
                modal.addEventListener("click", (ev) => {
                    if (ev.target === modal) closeModal(modal);
                });
            });

            // === ESC ngetutup semua modal ===
            document.addEventListener("keydown", (ev) => {
                if (ev.key === "Escape") closeAll();
            });

            // === FORMAT UANG CASH ===
            if (uangKembalianInput) uangKembalianInput.classList.add('pl-3');

            function hitungKembalian() {
                const totalSesudahDiskon = parseFloat(totalSetelahDiskonHidden?.value || totalAwal) || 0;
                const diterima = parseFloat(onlyDigits(uangDiterimaInput?.value)) || 0;
                const kembalian = diterima - totalSesudahDiskon;
                if (uangKembalianInput) {
                    uangKembalianInput.value = (kembalian >= 0) ? "Rp " + formatRupiah(kembalian) : "Rp 0";
                }
            }

            if (uangDiterimaInput) {
                uangDiterimaInput.addEventListener("input", (e) => {
                    let angka = onlyDigits(e.target.value);
                    e.target.value = angka ? formatRupiah(angka) : "";
                    hitungKembalian();
                });
            }

            // === PREVIEW GAMBAR (TRANSFER) ===
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
               class="object-cover w-full h-64 rounded-lg shadow-md" />`;
                        textGantiGambar.classList.remove("hidden");
                    };
                    reader.readAsDataURL(file);
                });
            }

            // === SUBMIT CASH ===
            const formPembayaranCash = document.getElementById('formPembayaranCash');
            if (formPembayaranCash) {
                formPembayaranCash.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const totalSesudahDiskon = parseFloat(totalSetelahDiskonHidden?.value ||
                        totalAwal) || 0;
                    const uangDiterimaClean = parseFloat(onlyDigits(uangDiterimaInput?.value)) || 0;
                    const kembalianClean = uangDiterimaClean - totalSesudahDiskon;
                    const metodeCash = document.getElementById('metode-pembayaran-cash');
                    const diskonPersen = parseFloat(diskonInput?.value) || 0;

                    if (uangDiterimaClean === 0 || uangDiterimaClean < totalSesudahDiskon) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Uang Kurang',
                            text: 'Nominal uang yang diterima belum cukup.'
                        });
                        return;
                    }

                    const formData = new FormData(formPembayaranCash);
                    formData.set('uang_yang_diterima', uangDiterimaClean);
                    formData.set('kembalian', kembalianClean);
                    formData.set('total_tagihan', totalAwal); // sebelum diskon
                    formData.set('total_setelah_diskon', totalSesudahDiskon);
                    formData.set('diskon_tipe', diskonPersen > 0 ? 'persen' : '');
                    formData.set('diskon_nilai', diskonPersen);
                    formData.set('metode_pembayaran_id', metodeCash?.value);

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
                        const data = await response.json();
                        if (data.success) {
                            await Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
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
                        console.error('Fetch error:', err);
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

            // === SUBMIT TRANSFER ===
            const formPembayaranTransfer = document.getElementById('formPembayaranTransfer');
            if (formPembayaranTransfer) {
                formPembayaranTransfer.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const metodeInput = document.getElementById('metode-pembayaran-transfer');
                    const formData = new FormData(this);
                    if (metodeInput) formData.set('metode_pembayaran_id', metodeInput.value);

                    const totalSesudahDiskon = parseFloat(totalSetelahDiskonHidden?.value ||
                        totalAwal) || 0;
                    const diskonPersen = parseFloat(diskonInput?.value) || 0;

                    formData.set('total_tagihan', totalAwal);
                    formData.set('total_setelah_diskon', totalSesudahDiskon);
                    formData.set('diskon_tipe', diskonPersen > 0 ? 'persen' : '');
                    formData.set('diskon_nilai', diskonPersen);

                    // === FIX UNTUK FILE ===
                    const fileInput = document.getElementById('upload');
                    if (fileInput && fileInput.files.length > 0) {
                        formData.set('bukti_pembayaran', fileInput.files[0]);
                    }

                    // === VALIDASI FILE ===
                    const bukti = formData.get('bukti_pembayaran');
                    if (!(bukti instanceof File) || bukti.size === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Bukti Transfer Belum Diupload',
                            text: 'Silakan unggah bukti pembayaran sebelum mengirim.'
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
                            text: `Maksimal ukuran ${MAX_MB} MB.`
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

                        let data = null;
                        const ct = response.headers.get('Content-Type') || '';
                        data = ct.includes('application/json') ? await response.json() : {
                            success: response.ok,
                            message: response.ok ? 'OK' : 'Gagal'
                        };

                        if (data && data.success) {
                            await Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: data.message || 'Bukti transfer terkirim.'
                            });
                            window.location.href = "{{ route('kasir.pembayaran') }}";
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: (data && data.message) || 'Gagal mengirim data.'
                            });
                        }
                    } catch (err) {
                        console.error('Fetch error:', err);
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
        });
    </script>

</body>

</html>
