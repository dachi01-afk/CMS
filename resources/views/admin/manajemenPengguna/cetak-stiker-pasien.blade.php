<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Stiker Pasien</title>
    <link href='{{ asset('storage/assets/royal_klinik.svg') }}' rel='shortcut icon'>
    @vite(['resources/css/app.css'])

    <style>
        /* ======================= PRINT MODE ======================= */
        @media print {
            * {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            body {
                background: #ffffff !important;
                margin: 0;
                padding: 0;
            }

            .print\:hidden {
                display: none !important;
            }

            .sticker-wrapper {
                border: none;
                box-shadow: none;
                padding: 3mm !important;
            }

            .label {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            /* ============ PAPER SIZE RULES ============ */

            /* A5 */
            body[data-paper="a5"] @page {
                size: A5 portrait;
                margin: 8mm;
            }

            body[data-paper="a5"] .sticker-wrapper {
                width: 148mm;
            }

            /* A6 */
            body[data-paper="a6"] @page {
                size: A6 portrait;
                margin: 6mm;
            }

            body[data-paper="a6"] .sticker-wrapper {
                width: 105mm;
            }

            /* DL (1/3 A4) */
            body[data-paper="dl"] @page {
                size: 99mm 210mm;
                margin: 6mm;
            }

            body[data-paper="dl"] .sticker-wrapper {
                width: 99mm;
            }

            /* 80mm thermal */
            body[data-paper="80mm"] @page {
                size: 80mm auto;
                margin: 3mm;
            }

            body[data-paper="80mm"] .sticker-wrapper {
                width: 72mm;
            }

            /* 58mm thermal */
            body[data-paper="58mm"] @page {
                size: 58mm auto;
                margin: 3mm;
            }

            body[data-paper="58mm"] .sticker-wrapper {
                width: 48mm;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-slate-100 text-gray-900 text-[10px] flex flex-col items-center p-6" data-paper="a5">

    {{-- Panel Pengaturan (hanya tampil di layar, hilang saat print) --}}
    <div class="print:hidden w-full max-w-xl mb-4 bg-white border border-slate-200 rounded-2xl shadow-sm p-4 space-y-3">
        <h2 class="text-base font-semibold text-slate-800">Pengaturan Cetak Stiker Pasien</h2>

        <div class="flex flex-wrap items-center gap-3">
            <label for="paper" class="text-sm font-medium text-slate-700">Ukuran Kertas:</label>

            <select id="paper"
                class="border border-slate-300 rounded-md px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                <option value="a5" selected>A5 (148 × 210 mm)</option>
                <option value="a6">A6 (105 × 148 mm)</option>
                <option value="dl">DL / 1/3 A4 (99 × 210 mm)</option>
                <option value="80mm">Thermal 80 mm</option>
                <option value="58mm">Thermal 58 mm</option>
            </select>

            <button id="btnPrint"
                class="ml-auto inline-flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow">
                Cetak Stiker
            </button>
        </div>

        <p class="text-[11px] text-slate-500">
            Pastikan ukuran kertas di dialog printer sesuai pilihan di atas. Gunakan <b>Scale 100%</b> agar proporsi
            stiker sesuai.
        </p>
    </div>

    {{-- Area Stiker --}}
    <div class="sticker-wrapper w-full max-w-md bg-white border border-slate-300 rounded-2xl shadow-md p-4 space-y-2">

        @for ($i = 0; $i < 3; $i++)
            <div class="label border border-slate-800 rounded-md p-2.5 h-[170px] flex flex-col justify-between">

                {{-- Nama --}}
                <div class="flex mb-1.5">
                    <span class="w-[28%] text-[9px] text-slate-600">Nama</span>
                    <span class="w-[72%] font-semibold text-[11px] tracking-tight uppercase">
                        {{ $pasien->nama_pasien }}
                    </span>
                </div>

                {{-- Tanggal lahir --}}
                <div class="flex mb-0.5">
                    <span class="w-[28%] text-[9px] text-slate-600">Tgl Lahir</span>
                    <span class="w-[72%] font-semibold text-[10px]">
                        @if ($pasien->tanggal_lahir)
                            {{ \Carbon\Carbon::parse($pasien->tanggal_lahir)->format('d-m-Y') }}
                            @if ($umur)
                                / {{ $umur }}
                            @endif
                        @else
                            -
                        @endif
                    </span>
                </div>

                {{-- No RM --}}
                <div class="flex mb-0.5">
                    <span class="w-[28%] text-[9px] text-slate-600">No RM</span>
                    <span class="w-[72%] font-semibold text-[10px]">
                        {{ $pasien->no_emr ?? '-' }}
                        @if ($pasien->jenis_kelamin)
                            ({{ $pasien->jenis_kelamin === 'Perempuan' ? 'P' : 'L' }})
                        @endif
                    </span>
                </div>

                {{-- NIK --}}
                <div class="flex mb-0.5">
                    <span class="w-[28%] text-[9px] text-slate-600">NIK</span>
                    <span class="w-[72%] font-semibold text-[10px]">
                        {{ $pasien->nik ?? '-' }}
                    </span>
                </div>
                
                {{-- Barcode --}}
                <div class="mt-1.5 flex justify-center items-end overflow-hidden h-[52px]">
                    <img src="https://bwipjs-api.metafloor.com/?bcid=code128&text={{ $pasien->no_emr }}&scale=1.3&includetext"
                        alt="Barcode RM" class="max-h-full w-auto">
                </div>

            </div>
        @endfor

    </div>

    <script>
        (function() {
            const paperSelect = document.getElementById('paper');
            const btnPrint = document.getElementById('btnPrint');

            function applyPaper() {
                if (!paperSelect) return;
                document.body.setAttribute('data-paper', paperSelect.value);
            }

            if (paperSelect) {
                paperSelect.addEventListener('change', applyPaper);
                applyPaper();
            }

            if (btnPrint) {
                btnPrint.addEventListener('click', function() {
                    applyPaper();
                    window.print();
                });
            }
        })();
    </script>
</body>

</html>
