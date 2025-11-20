<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Stiker Obat</title>
    <link href='{{ asset('storage/assets/royal_klinik.svg') }}' rel='shortcut icon'>
    @vite(['resources/css/app.css'])

    <style>
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

            .sticker-sheet {
                box-shadow: none !important;
                border: none !important;
                padding: 3mm !important;
            }

            .label-card {
                page-break-inside: avoid;
                break-inside: avoid;
            }

            @page {
                size: 70mm 80mm;
                margin: 4mm;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-slate-100 flex flex-col items-center justify-start p-6 text-[10px] text-gray-900">

    {{-- Panel kontrol di layar --}}
    <div class="print:hidden w-full max-w-sm mb-4">
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 flex items-center gap-3">
            <div>
                <p class="text-sm font-semibold text-slate-800">
                    Cetak Stiker Resep Obat
                </p>
                <p class="text-[11px] text-slate-500">
                    Pastikan ukuran kertas di printer mendekati 70×80mm atau label kecil.
                </p>
            </div>
            <button id="btnPrint"
                class="ml-auto inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold">
                Cetak
            </button>
        </div>
    </div>

    @php
        $tglLabelFormatted = $tanggalLabel ? \Carbon\Carbon::parse($tanggalLabel)->translatedFormat('d F Y') : '-';
    @endphp

    <div class="sticker-sheet w-full max-w-xs bg-white border border-slate-300 rounded-xl shadow-md p-3">

        @foreach ($resep->obat as $obat)
            <div class="label-card border border-gray-900 rounded-[4px] p-3 mb-3 last:mb-0">

                {{-- Header fasilitas --}}
                <div class="text-center mb-2">
                    <p class="text-[11px] font-bold uppercase leading-tight">
                        {{ $namaFasilitas }}
                    </p>
                </div>

                {{-- No & Tanggal --}}
                <div class="flex justify-between text-[9px] mb-1.5">
                    <span>
                        No. <span class="font-semibold">{{ $loop->iteration }}</span>
                    </span>
                    <span>
                        Tgl. <span class="font-semibold">{{ $tglLabelFormatted }}</span>
                    </span>
                </div>

                {{-- Identitas pasien & obat --}}
                <table class="w-full text-[9px] leading-tight mb-1.5">
                    <tbody>
                        <tr>
                            <td class="w-[28%] align-top">Nama</td>
                            <td class="w-[4%]">:</td>
                            <td class="font-semibold uppercase">
                                {{ $pasien->nama_pasien ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="align-top">No. RM</td>
                            <td>:</td>
                            <td class="font-semibold">
                                {{ $pasien->no_emr ?? '-' }}
                            </td>
                        </tr>
                        {{-- 🔹 NIK Pasien --}}
                        <tr>
                            <td class="align-top">NIK</td>
                            <td>:</td>
                            <td class="font-semibold">
                                {{ $pasien->nik ?? '-' }}
                            </td>
                        </tr>
                        <tr>
                            <td class="align-top">Tgl Lahir</td>
                            <td>:</td>
                            <td class="font-semibold">
                                @if ($pasien && $pasien->tanggal_lahir)
                                    {{ \Carbon\Carbon::parse($pasien->tanggal_lahir)->format('d-m-Y') }}
                                    @if ($umur)
                                        / {{ $umur }}
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="align-top">Nama Obat</td>
                            <td>:</td>
                            <td class="font-semibold uppercase">
                                {{ $obat->nama_obat ?? '-' }}
                            </td>
                        </tr>
                        {{-- 🔹 Dosis Obat --}}
                        <tr>
                            <td class="align-top">Dosis</td>
                            <td>:</td>
                            <td class="font-semibold">
                                {{ $obat->dosis ?? '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                {{-- Aturan pakai & keterangan --}}
                <div class="mt-2 border-t border-gray-300 pt-1.5 space-y-0.5">
                    @php
                        $ket = trim($obat->pivot->keterangan ?? '');
                    @endphp
                    
                    {{-- Baris keterangan detail (jika ingin beda dengan aturan pakai, tinggal ubah isi) --}}
                    @if ($ket !== '')
                        <p class="text-[9px] leading-snug">
                            <span class="font-semibold">Keterangan:</span>
                            {{ $ket }}
                        </p>
                    @endif
                </div>

            </div>
        @endforeach

    </div>

    <script>
        (function() {
            const btn = document.getElementById('btnPrint');
            if (btn) {
                btn.addEventListener('click', function() {
                    window.print();
                });
            }
        })();
    </script>
</body>

</html>
