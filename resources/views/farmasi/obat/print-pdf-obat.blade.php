<!DOCTYPE html>
<html>
<head>
    <title>Data Obat</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 5px; vertical-align: top; }
        th { background: #f0f0f0; }
        td { white-space: pre-line; } /* agar \n di depot muncul */
    </style>
</head>
<body>
    <h2>Data Master Obat</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Obat</th>
                <th>Nama Obat</th>
                <th>Brand Farmasi</th>
                <th>Kategori Obat</th>
                <th>Jenis Obat</th>
                <th>Satuan Obat</th>
                <th>Kandungan Obat</th>
                <th>Tanggal Kadaluarsa</th>
                <th>Nomor Batch</th>
                <th>Stok Global</th>
                <th>Dosis</th>
                <th>Total Harga</th>
                <th>Harga Jual</th>
                <th>Harga OTC</th>
                <th>Informasi Depot</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($obats as $obat)
                @php
                    $infoDepot = $obat->depotObat->map(function($depot) {
                        return $depot->nama_depot . ' | ' . ($depot->tipeDepot->nama_tipe_depot ?? '-') . ' | ' . ($depot->jumlah_stok_depot ?? 0);
                    })->implode("\n");
                @endphp
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $obat->kode_obat }}</td>
                    <td>{{ $obat->nama_obat }}</td>
                    <td>{{ $obat->brandFarmasi->nama_brand ?? '-' }}</td>
                    <td>{{ $obat->kategoriObat->nama_kategori_obat ?? '-' }}</td>
                    <td>{{ $obat->jenisObat->nama_jenis_obat ?? '-' }}</td>
                    <td>{{ $obat->satuanObat->nama_satuan_obat ?? '-' }}</td>
                    <td>{{ $obat->kandungan_obat }}</td>
                    <td>{{ $obat->tanggal_kadaluarsa_obat }}</td>
                    <td>{{ $obat->nomor_batch_obat }}</td>
                    <td>{{ $obat->jumlah }}</td>
                    <td>{{ $obat->dosis }}</td>
                    <td>Rp {{ number_format($obat->total_harga,0,',','.') }}</td>
                    <td>Rp {{ number_format($obat->harga_jual_obat,0,',','.') }}</td>
                    <td>Rp {{ number_format($obat->harga_otc_obat,0,',','.') }}</td>
                    <td>{{ $infoDepot }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
