<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SatuanObat;
use Illuminate\Support\Carbon;

class SatuanObatSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $items = [
            'Tablet',
            'Kapsul',
            'Botol',
            'Strip',
            'Sachet',
            'Tube',
        ];

        foreach ($items as $nama) {
            SatuanObat::updateOrCreate(
                ['nama_satuan_obat' => $nama],
                ['created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
