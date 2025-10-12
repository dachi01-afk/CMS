<?php

namespace Database\Seeders;

use App\Models\Apoteker;
use App\Models\Kunjungan;
use App\Models\Resep;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ResepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataKunjungan = Kunjungan::get();
        $dataApoteker = Apoteker::get();

        // foreach ($dataKunjungan as $kunjungan) {
        //     for ($i = 0; $i < 10; $i++) {
        //         Resep::create([
        //             'kunjungan_id' => $kunjungan->id,
        //         ]);
        //     }
        // }

        Resep::create([
            'kunjungan_id' => 1,
        ]);
    }
}
