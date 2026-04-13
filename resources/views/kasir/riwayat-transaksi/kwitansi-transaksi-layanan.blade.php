<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kwitansi Pembayaran</title>
    <link href='{{ asset('storage/assets/royal_klinik.svg') }}' rel='shortcut icon'>
    @vite(['resources/css/app.css'])
    <style>
        /* ====== LAYAR (SCREEN) ====== */
        body {
            background: #f8fafc;
        }

        .print\:hidden {}

        .receipt {
            max-width: 56rem;
        }

        /* ~max-w-3xl */

        /* Tabel rapih di layar */
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            padding: .5rem .75rem;
        }

        thead th {
            font-weight: 700;
        }

        /* ====== CETAK (PRINT) ====== */
        @media print {

            /* Perbaiki rendering warna/vektor */
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            body {
                background: #fff !important;
            }

            .print\:hidden {
                display: none !important;
            }

            .shadow-2xl {
                box-shadow: none !important;
            }

            .receipt {
                margin: 0 auto;
                border: 0;
                box-shadow: none;
                padding: 8mm;
            }

            table,
            tr,
            td,
            th {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            /* === Pilihan ukuran kertas berdasar atribut body[data-paper] === */

            /* A5 portrait */
            body[data-paper="a5"] @page {
                size: A5 portrait;
                margin: 10mm;
            }

            body[data-paper="a5"] .receipt {
                width: 148mm;
            }

            /* A6 portrait */
            body[data-paper="a6"] @page {
                size: A6 portrait;
                margin: 8mm;
            }

            body[data-paper="a6"] .receipt {
                width: 105mm;
                padding: 6mm;
            }

            body[data-paper="a6"] .shrink {
                font-size: 12px;
            }

            /* DL (1/3 A4) 99×210mm */
            body[data-paper="dl"] @page {
                size: 99mm 210mm;
                margin: 8mm;
            }

            body[data-paper="dl"] .receipt {
                width: 99mm;
                padding: 6mm;
            }

            body[data-paper="dl"] .shrink {
                font-size: 12px;
            }

            /* Thermal 80mm: area cetak ±72mm; tinggi auto */
            body[data-paper="80mm"] @page {
                size: 80mm auto;
                margin: 3mm;
            }

            body[data-paper="80mm"] .receipt {
                width: 72mm;
                padding: 3mm;
            }

            body[data-paper="80mm"] .shrink {
                font-size: 11px;
            }

            body[data-paper="80mm"] h1 {
                font-size: 16px;
            }

            body[data-paper="80mm"] .hide-thermal {
                display: none !important;
            }

            /* Thermal 58mm: area cetak ±48–54mm; tinggi auto */
            body[data-paper="58mm"] @page {
                size: 58mm auto;
                margin: 3mm;
            }

            body[data-paper="58mm"] .receipt {
                width: 48mm;
                padding: 3mm;
            }

            body[data-paper="58mm"] .shrink {
                font-size: 10px;
            }

            body[data-paper="58mm"] h1 {
                font-size: 14px;
            }

            body[data-paper="58mm"] .hide-thermal {
                display: none !important;
            }
        }
    </style>
</head>

{{-- Ganti default data-paper di sini: a5 | a6 | dl | 80mm | 58mm --}}

<body class="min-h-screen grid items-start justify-center p-6 font-sans" data-paper="a5">
    {{-- Panel atas (pilih ukuran kertas & tombol cetak) --}}
    <div class="w-full print:hidden mb-4">
        <div class="bg-white border rounded-xl p-4 shadow flex flex-col gap-3">
            <div class="flex flex-wrap items-center gap-3">
                <label for="paper" class="text-sm font-medium">Ukuran Kertas:</label>
                <select id="paper" class="border rounded px-3 py-2">
                    <option value="a5" selected>A5 (148 × 210 mm)</option>
                    <option value="a6">A6 (105 × 148 mm)</option>
                    <option value="dl">DL / 1/3 A4 (99 × 210 mm)</option>
                    <option value="80mm">Thermal 80 mm</option>
                    <option value="58mm">Thermal 58 mm</option>
                </select>

                <button id="btnPrint"
                    class="ml-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow">
                    Cetak Kwitansi
                </button>
            </div>
            <p class="text-xs text-gray-500">
                Tip: Pada dialog printer, pastikan <b>Paper size</b> sesuai pilihan di atas & <b>Scale = 100%</b>.
                Untuk thermal, pilih ukuran kertas driver seperti “Receipt 80mm”.
            </p>
        </div>
    </div>

    @php
        $isNonPemeriksaan = $summary->kategori_layanan === 'Non Pemeriksaan';
    @endphp

    {{-- ==== KWITANSI ==== --}}
    <div class="receipt bg-white shadow-2xl rounded-2xl w-full p-8 border border-gray-200 relative shrink">
        <!-- Header -->
        <div class="text-center border-b pb-5 mb-4">
            <h1 class="text-3xl md:text-4xl font-extrabold text-blue-700 tracking-wide">
                Kwitansi Pembayaran
            </h1>
            <p class="text-gray-500 text-sm mt-1">
                Kode Transaksi:
                <span class="font-semibold text-gray-800">{{ $summary->kode_transaksi }}</span>
            </p>
        </div>

        <!-- Data Pembayaran -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700 mb-6">
            <div>
                <p class="mt-2">
                    <span class="font-medium">Nama Pembayar:</span><br>
                    {{ $summary->pasien ?? '-' }}
                </p>
            </div>
            <div>
                <p class="mt-2">
                    <span class="font-medium">Metode Pembayaran:</span><br>
                    {{ $summary->metode_pembayaran ?? '-' }}
                </p>
            </div>
            <div>
                <p class="mt-2">
                    <span class="font-medium">Tanggal Order:</span><br>
                    {{ $summary->tanggal_order ?? '-' }}
                </p>
            </div>
            <div>
                <p class="mt-2">
                    <span class="font-medium">Tanggal Pembayaran:</span><br>
                    {{ $summary->tanggal_pembayaran ?? '-' }}
                </p>
            </div>
        </div>

        <!-- Detail Item Pembayaran (semua layanan dalam transaksi ini) -->
        <div class="mt-6">
            <h2 class="text-lg font-bold text-blue-700 mb-2 border-b pb-1">Rincian Pembayaran</h2>
            <table class="border border-gray-300 rounded-xl overflow-hidden shadow-sm w-full">
                <thead class="bg-blue-100 text-blue-700">
                    <tr>
                        <th class="text-left px-3 py-2">No</th>
                        <th class="text-left px-3 py-2">Nama Layanan</th>
                        <th class="text-left px-3 py-2">Kategori Layanan</th>
                        <th class="text-center px-3 py-2">Jumlah Layanan</th>
                        <th class="text-right px-3 py-2">Subtotal (Rp)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($dataOrderLayanan->orderLayananDetail as $index => $detail)
                        <tr>
                            <td class="px-3 py-2">{{ $index + 1 }}</td>
                            <td class="px-3 py-2">{{ $detail->layanan->nama_layanan ?? '-' }}</td>
                            <td class="px-3 py-2">{{ $detail->layanan->kategoriLayanan->nama_kategori ?? '-' }}</td>
                            <td class="text-center px-3 py-2">{{ $detail->qty }}</td>
                            <td class="text-right px-3 py-2">
                                {{ number_format($detail->total_harga_item, 0, ',', '.') }}</td>
                        </tr>

                        {{-- Ringkasan total + diskon + total akhir --}}
                        <tr class="bg-blue-50 font-medium text-gray-800">
                            <td colspan="4" class="text-right px-3 py-2">Total Sebelum Diskon</td>
                            <td class="text-right px-3 py-2">
                                {{ number_format($summary->total_sebelum_diskon, 0, ',', '.') }}
                            </td>
                        </tr>

                        <tr class="bg-blue-50 font-medium text-gray-800">
                            <td colspan="4" class="text-right px-3 py-2">Diskon</td>
                            <td class="text-right px-3 py-2">
                                - {{ number_format($summary->diskon_nominal, 0, ',', '.') }}
                            </td>
                        </tr>

                        <tr class="bg-blue-100 font-semibold text-gray-900">
                            <td colspan="4" class="text-right px-3 py-2">Total Setelah Diskon</td>
                            <td class="text-right text-blue-700 px-3 py-2">
                                {{ number_format($summary->total_setelah_diskon, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Terbilang (angka) – sementara pakai angka saja --}}
        @php $grandTotal = $summary->total_setelah_diskon; @endphp
        <div class="mt-4 text-gray-700 italic text-sm">
            <p>Terbilang:
                <span class="font-semibold text-blue-700">
                    Rp {{ number_format($grandTotal, 0, ',', '.') }}
                </span>
            </p>
        </div>

        <!-- Total Bayar, Uang Diterima, Kembalian & Tanggal -->
        <div class="border-t border-gray-300 mt-6 pt-4 text-right space-y-1">
            <h2 class="text-xl font-bold text-gray-900">
                Total Bayar:
                <span class="text-blue-700">
                    Rp {{ number_format($summary->total_setelah_diskon, 0, ',', '.') }}
                </span>
            </h2>
            <p class="text-sm text-gray-600">
                Uang Diterima:
                <span class="font-medium">
                    Rp {{ number_format($summary->uang_yang_diterima, 0, ',', '.') }}
                </span>
            </p>
            <p class="text-sm text-gray-600">
                Kembalian:
                <span class="font-medium">
                    Rp {{ number_format($summary->kembalian, 0, ',', '.') }}
                </span>
            </p>
            <p class="text-sm text-gray-500 mt-1">
                Tanggal Pembayaran:
                {{ $summary->tanggal_pembayaran ?? '-' }}
            </p>
        </div>

        <!-- Footer -->
        <div class="border-t mt-6 pt-4 text-center text-gray-600 text-sm">
            <p class="italic mb-1 hide-thermal">Terima kasih atas kepercayaan Anda.</p>
            <p class="font-semibold text-gray-800 text-base">{{ $namaPT }}</p>
        </div>
    </div>

    <script>
        (function() {
            const select = document.getElementById('paper');
            const btn = document.getElementById('btnPrint');

            function applyPaper() {
                if (!select) return;
                const val = select.value;
                document.body.setAttribute('data-paper', val);
            }

            if (select) {
                select.addEventListener('change', applyPaper);
                applyPaper();
            }

            if (btn) {
                btn.addEventListener('click', function() {
                    applyPaper();
                    window.print();
                });
            }
        })();
    </script>
</body>

</html>
