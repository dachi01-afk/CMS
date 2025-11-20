<?php

namespace Database\Seeders;

use App\Models\Layanan;
use App\Models\KategoriLayanan;
use Illuminate\Database\Seeder;

class LayananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil ID kategori "Pemeriksaan" dan "Non Pemeriksaan"
        $kategoriPemeriksaan = KategoriLayanan::where('nama_kategori', 'Pemeriksaan')->first();
        $kategoriNonPemeriksaan = KategoriLayanan::where('nama_kategori', 'Non Pemeriksaan')->first();

        // Kalau kategori belum ada, hentikan seeder biar kelihatan error-nya
        if (! $kategoriPemeriksaan || ! $kategoriNonPemeriksaan) {
            $this->command->error('Kategori "Pemeriksaan" atau "Non Pemeriksaan" belum ada. Jalankan KategoriLayananSeeder dulu.');
            return;
        }

        // === LAYANAN PEMERIKSAAN (semua pakai kategori "Pemeriksaan") ===
        $listLayanan = [
            [
                'nama_layanan'        => 'Konsultasi / pemeriksaan saja',
                'harga_layanan'       => 50000.00,
                'kategori_layanan_id' => $kategoriPemeriksaan->id,
            ],
            [
                'nama_layanan'        => 'Scaling / pembersihan karang gigi',
                'harga_layanan'       => 200000.00,
                'kategori_layanan_id' => $kategoriPemeriksaan->id,
            ],
            [
                'nama_layanan'        => 'Tambal gigi',
                'harga_layanan'       => 350000.00,
                'kategori_layanan_id' => $kategoriPemeriksaan->id,
            ],
            [
                'nama_layanan'        => 'Pencabutan gigi sederhana',
                'harga_layanan'       => 150000.00,
                'kategori_layanan_id' => $kategoriPemeriksaan->id,
            ],
            [
                'nama_layanan'        => 'Perawatan saluran akar (endodontik)',
                'harga_layanan'       => 300000.00,
                'kategori_layanan_id' => $kategoriPemeriksaan->id,
            ],

            // === LAYANAN NON PEMERIKSAAN (3 data) ===
            [
                'nama_layanan'        => 'Pendaftaran Pasien Baru',
                'harga_layanan'       => 10000.00,
                'kategori_layanan_id' => $kategoriNonPemeriksaan->id,
            ],
            [
                'nama_layanan'        => 'Pembuatan / Cetak Ulang Kartu Berobat',
                'harga_layanan'       => 15000.00,
                'kategori_layanan_id' => $kategoriNonPemeriksaan->id,
            ],
            [
                'nama_layanan'        => 'Cetak Ulang Rekam Medis / Resume Medis',
                'harga_layanan'       => 20000.00,
                'kategori_layanan_id' => $kategoriNonPemeriksaan->id,
            ],
        ];

        foreach ($listLayanan as $layanan) {
            Layanan::create($layanan);
        }
    }
}
