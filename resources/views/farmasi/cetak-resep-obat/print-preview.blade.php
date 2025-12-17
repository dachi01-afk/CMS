<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Preview Cetak Resep</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            background: #fff;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 15mm;
            margin: auto;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .clinic h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
        }

        .clinic p {
            margin: 2px 0;
            font-size: 12px;
            color: #6b7280;
        }

        .meta {
            text-align: right;
            font-size: 12px;
        }

        .divider {
            border-top: 2px solid #111827;
            margin: 10px 0 14px;
        }

        .title {
            text-align: center;
            margin-bottom: 10px;
        }

        .title h2 {
            margin: 0;
            font-size: 16px;
            letter-spacing: .5px;
        }

        .info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 16px;
            font-size: 12px;
        }

        .info .label {
            color: #6b7280;
            width: 90px;
            display: inline-block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            font-size: 12px;
        }

        table th,
        table td {
            border-bottom: 1px solid #e5e7eb;
            padding: 6px 5px;
            vertical-align: top;
        }

        table th {
            border-bottom: 1px solid #111827;
            text-align: left;
            text-transform: uppercase;
            font-size: 11px;
        }

        .col-no {
            width: 28px;
            text-align: center;
        }

        .col-jml {
            width: 50px;
            text-align: center;
        }

        .col-iter {
            width: 70px;
            text-align: center;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 20px;
            font-size: 12px;
        }

        .note {
            max-width: 65%;
            color: #6b7280;
            font-size: 11px;
        }

        .sign {
            text-align: center;
            min-width: 180px;
        }

        .sign .space {
            height: 70px;
            border-bottom: 1px dashed #9ca3af;
            margin: 6px 0;
        }

        .actions {
            width: 210mm;
            margin: 10px auto;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .btn {
            padding: 8px 14px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            background: #fff;
            font-size: 12px;
            cursor: pointer;
        }

        .btn.primary {
            background: #111827;
            color: #fff;
            border-color: #111827;
        }

        @media print {
            .actions {
                display: none;
            }

            .page {
                padding: 10mm;
            }
        }
    </style>
</head>

<body>

    @php
        $tipe = request('tipe_resep', 'resep_dokter');

        $judul = $tipe === 'resep_bebas' ? 'RESEP BEBAS / PENJUALAN OBAT' : 'RESEP DOKTER';

        $obat = request('obat', []);
    @endphp

    {{-- tombol (tidak ikut tercetak) --}}
    <div class="actions">
        <button class="btn" onclick="window.close()">Tutup</button>
        <button class="btn primary" onclick="window.print()">Print</button>
    </div>

    <div class="page">

        {{-- HEADER --}}
        <div class="header">
            <div class="clinic">
                <h1>ROYAL KLINIK</h1>
                <p>Jl. Contoh Alamat Klinik</p>
                <p>Telp. (061) 123456</p>
            </div>

            <div class="meta">
                <div><strong>Tanggal:</strong> {{ now()->format('d/m/Y') }}</div>
            </div>
        </div>

        <div class="divider"></div>

        {{-- JUDUL --}}
        <div class="title">
            <h2>{{ $judul }}</h2>
        </div>

        {{-- INFO PASIEN / DOKTER --}}
        <div class="info">
            <div><span class="label">Pasien</span>: {{ request('nama_pasien', '-') }}</div>
            <div><span class="label">Umur</span>: {{ request('umur', '-') }}</div>

            <div><span class="label">Alamat</span>: {{ request('alamat', '-') }}</div>
            <div><span class="label">BB</span>: {{ request('berat_badan', '-') }}</div>

            @if ($tipe !== 'resep_bebas')
                <div><span class="label">Dokter</span>: {{ request('nama_dokter', '-') }}</div>
                <div><span class="label">Poli</span>: {{ request('nama_poli', '-') }}</div>
            @endif
        </div>

        {{-- TABEL OBAT --}}
        <table>
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th>Nama Obat</th>
                    <th class="col-jml">Jml</th>
                    <th>Signatura</th>
                    <th>Detur</th>
                    <th class="col-iter">Iter</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($obat['obat_id'] ?? []) as $i => $obatId)
                    @php
                        $jumlah = $obat['jumlah'][$i] ?? 0;
                        $signatura = $obat['signatura'][$i] ?? '-';
                        $detur = $obat['detur'][$i] ?? '-';
                        $isIter = !empty($obat['is_iter'][$i]);
                        $iterJumlah = (int) ($obat['iter_jumlah'][$i] ?? 0);
                    @endphp
                    <tr>
                        <td class="col-no">{{ $i + 1 }}</td>
                        <td>{{ $obat['nama'][$i] ?? 'Obat' }}</td>
                        <td class="col-jml">{{ $jumlah }}</td>
                        <td>{{ $signatura }}</td>
                        <td>{{ $detur }}</td>
                        <td class="col-iter">
                            {{ $isIter && $iterJumlah > 0 ? 'Ya (' . $iterJumlah . 'x)' : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="color:#6b7280;">Tidak ada item obat</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- FOOTER --}}
        <div class="footer">
            <div class="note">
                Gunakan obat sesuai aturan pakai (signatura).
                Simpan obat di tempat sejuk dan jauh dari jangkauan anak-anak.
            </div>

            @if ($tipe !== 'resep_bebas')
                <div class="sign">
                    <div>Dokter</div>
                    <div class="space"></div>
                    <div><strong>{{ request('nama_dokter', '________________') }}</strong></div>
                </div>
            @endif
        </div>

    </div>
</body>

</html>
