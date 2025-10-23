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
                                @foreach ($dataPembayaran->emr->resep->obat as $o)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 md:w-[384px]">
                                            <label>{{ $o->nama_obat }}</label>
                                        </td>
                                        <td class="p-4 text-base font-normal text-gray-900 dark:text-white">
                                            x{{ $o->pivot->jumlah }}
                                        </td>
                                        <td class="p-4 text-right text-base font-bold text-gray-900 dark:text-white">
                                            Rp{{ number_format(($o->total_harga ?? 0) * ($o->pivot->jumlah ?? 1), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach

                                @foreach ($dataPembayaran->emr->kunjungan->layanan as $l)
                                    <tr>
                                        <td class="whitespace-nowrap py-4 md:w-[384px]">
                                            <label>{{ $l->nama_layanan }}</label>
                                        </td>
                                        <td class="p-4 text-base font-normal text-gray-900 dark:text-white">
                                            x{{ $l->pivot->jumlah }}
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
                            <dl class="flex items-center justify-between gap-4">
                                <dt class="text-gray-500 dark:text-gray-400">Total Harga</dt>
                                <dd class="text-base font-medium text-gray-900 dark:text-white">
                                    Rp{{ number_format($dataPembayaran->total_tagihan, 0, ',', '.') }}
                                </dd>
                            </dl>

                            {{-- Metode Pembayar --}}
                            <dl
                                class="flex items-center justify-between gap-4 border-t border-gray-200 pt-2 dark:border-gray-700">
                                <dt class="text-lg font-bold text-gray-900 dark:text-white">Metode Pembayaran</dt>
                                <select class="text-lg font-bold text-gray-900 dark:text-white rounded-md"
                                    id="select_metode_pembayaran">
                                    @foreach ($dataMetodePembayaran as $metodePembayaran)
                                        <option value="{{ $metodePembayaran->id }}">
                                            {{ $metodePembayaran->nama_metode }}</option>
                                    @endforeach
                                </select>
                            </dl>
                            <dl
                                class="flex items-center justify-between gap-4 border-t border-gray-200 pt-2 dark:border-gray-700">
                                <dt class="text-lg font-bold text-gray-900 dark:text-white">Total Tagihan</dt>
                                <dd class="text-lg font-bold text-gray-900 dark:text-white">
                                    Rp{{ number_format($dataPembayaran->total_tagihan, 0, ',', '.') }}
                                </dd>
                            </dl>
                        </div>

                        <div class="gap-4 sm:flex sm:items-center">
                            <a href="{{ route('kasir.index') }}"
                                class="w-full rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-900 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white">
                                Kembali ke halaman kasir
                            </a>

                            <button type="button" data-modal-target="pembayaranModal"
                                data-modal-toggle="pembayaranModal"
                                class="mt-4 flex w-full items-center justify-center rounded-lg bg-primary-700 px-5 py-2.5 text-sm font-medium text-white hover:bg-primary-800 sm:mt-0">
                                Lanjutkan Pembayaran
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Pembayaran -->
    <div id="pembayaranModal" tabindex="-1" aria-hidden="true"
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
                <form id="formPembayaran" action="{{ route('kasir.pembayaran.cash') }}" method="POST">
                    @csrf
                    <div class="p-4 space-y-4">
                        <input type="hidden" name="id" value="{{ $dataPembayaran->id }}">
                        <input type="hidden" name="metode_pembayaran_id" id="metode_pembayaran_id"
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
                                <input type="number" name="uang_yang_diterima" id="uang_diterima"
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

    <!-- JS Section -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const totalInput = document.getElementById('total_tagihan');
            const uangDiterimaInput = document.getElementById('uang_diterima');
            const uangKembalianInput = document.getElementById('uang_kembalian');
            const form = document.getElementById('formPembayaran');
            const submitBtn = document.getElementById('btnSubmitPembayaran');

            const selectMetode = document.getElementById("select_metode_pembayaran");
            const inputHidden = document.getElementById("metode_pembayaran_id");

            // Saat user mengganti pilihan
            selectMetode.addEventListener("change", function() {
                const selectedValue = this.value; // ambil value dari option terpilih
                inputHidden.value = selectedValue; // masukkan ke input hidden
                console.log("Metode Pembayaran dipilih:", selectedValue); // debug di console
            });

            // tambahkan class pl-3 ke uang_kembalian
            uangKembalianInput.classList.add('pl-3');

            // Fungsi bantu: hapus semua karakter non-digit
            function onlyDigits(value) {
                return value ? String(value).replace(/[^\d]/g, '') : '';
            }

            // Hitung otomatis uang kembalian
            uangDiterimaInput.addEventListener('input', function() {
                const total = parseFloat(onlyDigits(totalInput.value)) || 0;
                const diterima = parseFloat(onlyDigits(this.value)) || 0;
                const kembalian = diterima - total;

                uangKembalianInput.value = (kembalian >= 0) ?
                    'Rp ' + kembalian.toLocaleString('id-ID') :
                    'Rp 0';
            });

            // Submit form pembayaran
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const totalClean = parseFloat(onlyDigits(totalInput.value)) || 0;
                const uangDiterimaClean = parseFloat(onlyDigits(uangDiterimaInput.value)) || 0;
                const kembalianClean = uangDiterimaClean - totalClean;
                const namaMetode = inputHidden.value;

                if (uangDiterimaClean === 0 || uangDiterimaClean < totalClean) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Uang Kurang',
                        text: 'Nominal uang yang diterima belum cukup untuk membayar tagihan.'
                    });
                    return;
                }

                // buat salinan FormData dan isi ulang nilai bersih
                const formData = new FormData(form);
                formData.set('uang_yang_diterima', uangDiterimaClean);
                formData.set('kembalian', kembalianClean);
                formData.set('total_tagihan', totalClean);
                formData.set('metode_pembayaran_id', namaMetode);

                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-60', 'cursor-not-allowed');

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .content,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    if (response.redirected || response.status === 302) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Ter-redirect',
                            text: 'Server meredirect permintaan. Kemungkinan Anda belum login atau session berakhir.'
                        });
                        return;
                    }

                    if (response.status === 401) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Tidak Terautentikasi',
                            text: 'Silakan login ulang.'
                        });
                        return;
                    }

                    if (response.status === 419) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Token CSRF Tidak Valid',
                            text: 'Session kadaluwarsa, silakan muat ulang halaman dan coba lagi.'
                        });
                        return;
                    }

                    const data = await response.json();

                    if (data.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: data.message || 'Pembayaran berhasil diproses.',
                            confirmButtonText: 'OK'
                        });
                        window.location.href = "{{ route('kasir.index') }}";
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: data.message || 'Terjadi kesalahan saat memproses pembayaran.'
                        });
                    }
                } catch (err) {
                    console.error('Fetch error:', err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Tidak dapat terhubung ke server. Periksa koneksi atau lihat log server.'
                    });
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                }
            });
        });
    </script>

</body>

</html>
