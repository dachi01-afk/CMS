<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisObat;
use Illuminate\Support\Carbon;

class JenisObatSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $items = [
            'Tablet',
            'Kapsul',
            'Sirup',
            'Salep',
            'Serbuk',
        ];

        foreach ($items as $nama) {
            JenisObat::updateOrCreate(
                ['nama_jenis_obat' => $nama],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
