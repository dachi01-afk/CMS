<?php

namespace Database\Seeders;

use App\Models\EMR;
use App\Models\Kunjungan;
use App\Models\Resep;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class EMRSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataKunjungan = Kunjungan::all();
        $dataResep = Resep::all();
        $faker = Faker::create('id_ID');

        foreach ($dataKunjungan as $kunjungan) {
            $banyakNyaKunjungan = rand(1, 5);
            for ($i = 0; $i < $banyakNyaKunjungan; $i++) {
                EMR::create([
                    'kunjungan_id' => $kunjungan->id,
                    'resep_id' => $dataResep->random()->id,
                ]);
            }
        }
    }
}
