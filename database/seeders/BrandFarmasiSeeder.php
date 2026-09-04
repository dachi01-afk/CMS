<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BrandFarmasi;
use Illuminate\Support\Carbon;

class BrandFarmasiSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $items = [
            'Kimia Farma',
            'Kalbe',
            'Sanbe',
            'Dexa Medica',
            'Bernofarm',
            'Phapros',
            'Indofarma',
            'Pfizer',
            'Bayer',
        ];

        foreach ($items as $nama) {
            BrandFarmasi::updateOrCreate(
                ['nama_brand' => $nama],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
