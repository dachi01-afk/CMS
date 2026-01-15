<?php

namespace App\Imports;

use App\Models\BahanHabisPakai;
use App\Models\BrandFarmasi;
use App\Models\SatuanObat;   // sesuaikan nama model satuan kamu
use App\Models\JenisObat;    // sesuaikan nama model jenis kamu
use Carbon\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class BahanHabisPakaiImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    SkipsOnFailure,
    WithChunkReading,
    WithBatchInserts
{
    use SkipsFailures;

    public function model(array $row)
    {
        // Normalisasi key
        $kode = trim((string)($row['kode'] ?? ''));
        if ($kode === '') return null;

        $nama = trim((string)($row['nama_barang'] ?? ''));

        // ====== Resolve brand_farmasi ======
        // Support: bisa isi "brand_farmasi_id" atau "brand_farmasi" (nama)
        $brandId = $row['brand_farmasi_id'] ?? null;
        if (!$brandId && !empty($row['brand_farmasi'])) {
            $brandName = trim((string)$row['brand_farmasi']);
            $brandId = BrandFarmasi::firstOrCreate(
                ['nama_brand' => $brandName],
                ['nama_brand' => $brandName]
            )->id;
        }

        // ====== Resolve jenis ======
        $jenisId = $row['jenis_id'] ?? null;
        if (!$jenisId && !empty($row['jenis'])) {
            $jenisName = trim((string)$row['jenis']);
            // sesuaikan field model jenis kamu
            $jenisId = JenisObat::firstOrCreate(
                ['nama_jenis_obat' => $jenisName],
                ['nama_jenis_obat' => $jenisName]
            )->id;
        }

        // ====== Resolve satuan ======
        $satuanId = $row['satuan_id'] ?? null;
        if (!$satuanId && !empty($row['satuan'])) {
            $satuanName = trim((string)$row['satuan']);
            // sesuaikan field model satuan kamu
            $satuanId = SatuanObat::firstOrCreate(
                ['nama_satuan_obat' => $satuanName],
                ['nama_satuan_obat' => $satuanName]
            )->id;
        }

        $stok = (int)($row['stok_barang'] ?? 0); // 0 tetap valid
        $dosis = (float)($row['dosis'] ?? 0);

        // tanggal kadaluarsa bisa kosong
        $tgl = $row['tanggal_kadaluarsa_bhp'] ?? null;
        $tanggalKadaluarsa = null;
        if (!empty($tgl)) {
            try {
                // Excel bisa kirim format tanggal berbeda-beda
                $tanggalKadaluarsa = Carbon::parse($tgl)->format('Y-m-d');
            } catch (\Throwable $e) {
                $tanggalKadaluarsa = null;
            }
        }

        // Harga (biar aman, hapus "Rp", titik, dll)
        $parseMoney = function ($v) {
            $v = (string)($v ?? 0);
            $v = str_replace(['Rp', 'rp', ' ', '.', ','], ['', '', '', '', '.'], $v);
            return (float)$v;
        };

        $hargaJual = $parseMoney($row['harga_jual_umum_bhp'] ?? 0);
        $hargaBeli = $parseMoney($row['harga_beli_satuan_bhp'] ?? 0);
        $avgHpp    = $parseMoney($row['avg_hpp_bhp'] ?? 0);
        $hargaOtc  = $parseMoney($row['harga_otc_bhp'] ?? 0);

        // Update jika kode sudah ada, jika belum buat baru
        return BahanHabisPakai::updateOrCreate(
            ['kode' => $kode],
            [
                'brand_farmasi_id' => $brandId,
                'jenis_id'         => $jenisId,
                'satuan_id'        => $satuanId,
                'nama_barang'      => $nama,
                'stok_barang'      => $stok,
                'dosis'            => $dosis,
                'tanggal_kadaluarsa_bhp' => $tanggalKadaluarsa,
                'no_batch'         => $row['no_batch'] ?? null,
                'harga_beli_satuan_bhp'  => $hargaBeli,
                'avg_hpp_bhp'       => $avgHpp,
                'harga_jual_umum_bhp' => $hargaJual,
                'harga_otc_bhp'     => $hargaOtc,
                'keterangan'        => $row['keterangan'] ?? null,
            ]
        );
    }

    public function rules(): array
    {
        return [
            'kode' => ['required'],
            'nama_barang' => ['required'],

            // stok boleh 0
            'stok_barang' => ['nullable', 'numeric', 'min:0'],

            // harga boleh 0
            'harga_jual_umum_bhp' => ['nullable'],
            'harga_beli_satuan_bhp' => ['nullable'],
            'avg_hpp_bhp' => ['nullable'],
            'harga_otc_bhp' => ['nullable'],
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function headingRow(): int
    {
        return 4; // karena header tabel ada di baris 4 (A4)
    }
}
