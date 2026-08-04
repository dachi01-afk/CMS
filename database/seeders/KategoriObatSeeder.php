<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriObat;
use Illuminate\Support\Carbon;

class KategoriObatSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $items = [
            'Analgesik & Antipiretik',
            'Antibiotik',
            'Vitamin & Suplemen',
            'Anti Inflamasi',
            'Sirup',
            'Obat Bebas',
        ];

        foreach ($items as $nama) {
            KategoriObat::updateOrCreate(
                ['nama_kategori_obat' => $nama],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
