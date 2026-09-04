<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriLayanan;
use App\Models\Layanan;

class LayananSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriPemeriksaan = KategoriLayanan::where('nama_kategori', 'Pemeriksaan')->first();
        $kategoriNonPemeriksaan = KategoriLayanan::where('nama_kategori', 'Non Pemeriksaan')->first();

        if (! $kategoriPemeriksaan || ! $kategoriNonPemeriksaan) {
            $this->command?->error('Kategori "Pemeriksaan" atau "Non Pemeriksaan" belum ada. Jalankan KategoriLayananSeeder dulu.');
            return;
        }

        $listLayanan = [
            // ================= PEMERIKSAAN =================
            [
                'nama_layanan' => 'Konsultasi / pemeriksaan saja',
                'harga_sebelum_diskon' => 50000,
                'diskon' => 5000,
                'kategori_layanan_id' => $kategoriPemeriksaan->id,
            ],
            [
                'nama_layanan' => 'Scaling / pembersihan karang gigi',
                'harga_sebelum_diskon' => 200000,
                'diskon' => 20000,
                'kategori_layanan_id' => $kategoriPemeriksaan->id,
            ],
            [
                'nama_layanan' => 'Tambal gigi',
                'harga_sebelum_diskon' => 350000,
                'diskon' => 25000,
                'kategori_layanan_id' => $kategoriPemeriksaan->id,
            ],
            [
                'nama_layanan' => 'Pencabutan gigi sederhana',
                'harga_sebelum_diskon' => 150000,
                'diskon' => 10000,
                'kategori_layanan_id' => $kategoriPemeriksaan->id,
            ],
            [
                'nama_layanan' => 'Perawatan saluran akar (endodontik)',
                'harga_sebelum_diskon' => 300000,
                'diskon' => 30000,
                'kategori_layanan_id' => $kategoriPemeriksaan->id,
            ],

            // ================= NON PEMERIKSAAN =================
            [
                'nama_layanan' => 'Pendaftaran Pasien Baru',
                'harga_sebelum_diskon' => 10000,
                'diskon' => 0,
                'kategori_layanan_id' => $kategoriNonPemeriksaan->id,
            ],
            [
                'nama_layanan' => 'Pembuatan / Cetak Ulang Kartu Berobat',
                'harga_sebelum_diskon' => 15000,
                'diskon' => 2000,
                'kategori_layanan_id' => $kategoriNonPemeriksaan->id,
            ],
            [
                'nama_layanan' => 'Cetak Ulang Rekam Medis / Resume Medis',
                'harga_sebelum_diskon' => 20000,
                'diskon' => 5000,
                'kategori_layanan_id' => $kategoriNonPemeriksaan->id,
            ],
        ];

        foreach ($listLayanan as $data) {
            $hargaSetelahDiskon = (int) $data['harga_sebelum_diskon'] - (int) $data['diskon'];

            Layanan::updateOrCreate(
                [
                    'kategori_layanan_id' => $data['kategori_layanan_id'],
                    'nama_layanan' => $data['nama_layanan'],
                ],
                [
                    'harga_sebelum_diskon' => $data['harga_sebelum_diskon'],
                    'diskon'               => $data['diskon'],
                    'harga_setelah_diskon' => $hargaSetelahDiskon,
                    'is_global'            => 1,
                ]
            );
        }

        $this->command?->info('Seeder layanan sukses ✅');
    }
}
