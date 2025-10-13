<?php

namespace Database\Seeders;

use App\Models\KunjunganLayanan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KunjunganLayananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        KunjunganLayanan::create([
            'kunjungan_id' => 1,
            'layanan_id' => 1,
            'jumlah' => 1,
        ]);
        KunjunganLayanan::create([
            'kunjungan_id' => 1,
            'layanan_id' => 2,
            'jumlah' => 2,
        ]);
    }
}
