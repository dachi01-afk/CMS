<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kuitansi Pembayaran</title>
    <link href='{{ asset('storage/assets/royal_klinik.svg') }}' rel='shortcut icon'>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            @page {
                size: A5 portrait;
                /* bisa A4, A5, Letter, Legal, dll */
                margin: 10mm;
                /* atur margin dalam kertas cetak */
            }

            .print\:hidden {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .shadow-2xl {
                box-shadow: none !important;
            }
        }
    </style>
</head>

<body class="bg-gradient-to-br from-blue-50 to-gray-100 min-h-screen flex items-center justify-center p-6 font-sans">

    <div class="bg-white shadow-2xl rounded-2xl w-full max-w-3xl p-8 border border-gray-200 relative">
        <!-- Header -->
        <div class="text-center border-b pb-5 mb-4">
            <h1 class="text-4xl font-extrabold text-blue-700 tracking-wide">Kwitansi Pembayaran</h1>
            <p class="text-gray-500 text-sm mt-1">Kode Transaksi:
                <span class="font-semibold text-gray-800">{{ $dataPembayaran->kode_transaksi }}</span>
            </p>
        </div>

        <!-- Data Pembayaran -->
        <div class="grid grid-cols-2 gap-4 text-gray-700 mb-6">
            <div>
                <p><span class="font-medium">Tanggal Kunjungan:</span><br>
                    {{ \Carbon\Carbon::parse($dataPembayaran->emr->kunjungan->tanggal_kunjungan)->translatedFormat('d F Y') ?? '-' }}
                </p>
                <p class="mt-2"><span class="font-medium">Nama Pembayar:</span><br>
                    {{ $dataPembayaran->emr->kunjungan->pasien->nama_pasien ?? '-' }}
                </p>
            </div>
            <div>
                <p><span class="font-medium">Nomor Antrian:</span><br>
                    {{ $dataPembayaran->emr->kunjungan->no_antrian ?? '-' }}
                </p>
                <p class="mt-2"><span class="font-medium">Metode Pembayaran:</span><br>
                    {{ $dataPembayaran->metodePembayaran->nama ?? 'Tunai' }}
                </p>
            </div>
        </div>

        <!-- Detail Item Pembayaran -->
        <div class="mt-6">
            <h2 class="text-lg font-bold text-blue-700 mb-2 border-b pb-1">Rincian Pembayaran</h2>
            <table class="w-full border border-gray-300 rounded-xl overflow-hidden shadow-sm">
                <thead class="bg-blue-100 text-blue-700">
                    <tr>
                        <th class="py-2 px-3 text-left">No</th>
                        <th class="py-2 px-3 text-left">Nama Item</th>
                        <th class="py-2 px-3 text-center">Jumlah Item</th>
                        <th class="py-2 px-3 text-right">Harga (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @php
                        $total = 0;
                        $no = 1;
                    @endphp

                    {{-- Layanan --}}
                    @if ($dataPembayaran->emr->kunjungan->layanan->count() > 0)
                        @foreach ($dataPembayaran->emr->kunjungan->layanan as $layanan)
                            @php
                                $subtotal =
                                    ($layanan->pivot->jumlah ?? 1) *
                                    ($layanan->harga_layanan ?? ($layanan->harga ?? 0));
                                $total += $subtotal;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-3">{{ $no++ }}</td>
                                <td class="py-2 px-3">{{ $layanan->nama_layanan }}</td>
                                <td class="py-2 px-3 text-center">{{ $layanan->pivot->jumlah ?? 1 }}</td>
                                <td class="py-2 px-3 text-right">{{ number_format($subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- Obat --}}
                    @if ($dataPembayaran->emr->resep && $dataPembayaran->emr->resep->obat->count() > 0)
                        @foreach ($dataPembayaran->emr->resep->obat as $obat)
                            @php
                                $subtotal = ($obat->pivot->jumlah ?? 1) * ($obat->harga ?? ($obat->total_harga ?? 0));
                                $total += $subtotal;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="py-2 px-3">{{ $no++ }}</td>
                                <td class="py-2 px-3">{{ $obat->nama_obat }}</td>
                                <td class="py-2 px-3 text-center">{{ $obat->pivot->jumlah ?? 1 }}</td>
                                <td class="py-2 px-3 text-right">{{ number_format($subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- Total --}}
                    <tr class="bg-blue-50 font-semibold text-gray-800">
                        <td colspan="3" class="py-3 px-3 text-right">Total</td>
                        <td class="py-3 px-3 text-right text-blue-700">
                            {{ number_format($total, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Terbilang -->
        <div class="mt-4 text-gray-700 italic text-sm">
            <p>Terbilang: <span class="font-semibold text-blue-700">
                    Rp.{{ number_format($grandTotal, 0, ',', '.') }}
                </span></p>
        </div>

        <!-- Total Bayar -->
        <div class="border-t border-gray-300 mt-6 pt-4 text-right">
            <h2 class="text-xl font-bold text-gray-900">Total Bayar:
                <span class="text-blue-700">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </h2>
            <p class="text-sm text-gray-500 mt-1">Tanggal Pembayaran:
                {{ $dataPembayaran->updated_at->translatedFormat('d F Y') }}
            </p>
        </div>

        <!-- Tombol Print -->
        <div class="text-center mt-8 print:hidden">
            <button onclick="window.print()"
                class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-lg shadow transition">
                <i class="fa-solid fa-print mr-2"></i> Cetak Kwitansi
            </button>
        </div>

        <!-- Footer -->
        <div class="border-t mt-6 pt-4 text-center text-gray-600 text-sm">
            <p class="italic mb-1">Terima kasih atas kepercayaan Anda.</p>
            <p class="font-semibold text-gray-800 text-base">{{ $namaPT }}</p>
        </div>
    </div>
</body>

</html>
