<?php

namespace Database\Seeders;

use App\Models\Layanan;
use Illuminate\Database\Seeder;

class LayananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $listLayanan = [
            ['nama_layanan' => 'Konsultasi / pemeriksaan saja', 'harga_layanan' => 50000.00],
            ['nama_layanan' => 'Scaling / pembersihan karang gigi', 'harga_layanan' => 200000.00],
            ['nama_layanan' => 'Tambal gigi', 'harga_layanan' => 350000.00],
            ['nama_layanan' => 'Pencabutan gigi sederhana', 'harga_layanan' => 150000.00],
            ['nama_layanan' => 'Perawatan saluran akar (endodontik)', 'harga_layanan' => 300000.00],
        ];

        foreach ($listLayanan as $layanan) {
            Layanan::create($layanan);
        }
    }
}
