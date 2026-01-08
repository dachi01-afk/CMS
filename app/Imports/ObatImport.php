<?php

namespace App\Imports;

use App\Models\Obat;
use App\Models\BrandFarmasi;
use App\Models\KategoriObat;
use App\Models\JenisObat;
use App\Models\SatuanObat;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ObatImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // ===========================
            // 1️⃣ Mapping Nama ke ID
            // ===========================
            $brandId = BrandFarmasi::where('nama_brand', $row['brand_farmasi'] ?? '')->first()?->id;
            $kategoriId = KategoriObat::where('nama_kategori_obat', $row['kategori_obat'] ?? '')->first()?->id;
            $jenisId = JenisObat::where('nama_jenis_obat', $row['jenis_obat'] ?? '')->first()?->id;
            $satuanId = SatuanObat::where('nama_satuan_obat', $row['satuan_obat'] ?? '')->first()?->id;

            // ===========================
            // 2️⃣ Format harga dari "Rp 5.000" menjadi integer 5000
            // ===========================
            $totalHarga = isset($row['total_harga']) ? intval(preg_replace('/[^0-9]/', '', $row['total_harga'])) : 0;
            $hargaJual = isset($row['harga_jual_obat']) ? intval(preg_replace('/[^0-9]/', '', $row['harga_jual_obat'])) : 0;
            $hargaOtc = isset($row['harga_otc_obat']) ? intval(preg_replace('/[^0-9]/', '', $row['harga_otc_obat'])) : 0;

            // ===========================
            // 3️⃣ Simpan atau update data Obat
            // ===========================
            Obat::updateOrCreate(
                ['kode_obat' => $row['kode_obat']], // Kolom unik
                [
                    'nama_obat' => $row['nama_obat'] ?? null,
                    'brand_farmasi_id' => $brandId,
                    'kategori_obat_id' => $kategoriId,
                    'jenis_obat_id' => $jenisId,
                    'satuan_obat_id' => $satuanId,
                    'kandungan_obat' => $row['kandungan_obat'] ?? null,
                    'tanggal_kadaluarsa_obat' => $row['tanggal_kadaluarsa_obat'] ?? null,
                    'nomor_batch_obat' => $row['nomor_batch_obat'] ?? null,
                    'jumlah' => $row['stok_global_obat'] ?? 0,
                    'dosis' => $row['dosis_obat'] ?? null,
                    'total_harga' => $totalHarga,
                    'harga_jual_obat' => $hargaJual,
                    'harga_otc_obat' => $hargaOtc,
                    // kolom info depot diabaikan karena multi-line
                ]
            );
        }
    }
}
