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
            EMR::create([
                'kunjungan_id' => $kunjungan->id,
                'resep_id' => $dataResep->random()->id,
                'keluhan_utama' => $faker->sentence(10),
                'riwayat_penyakit_dahulu' => $faker->sentence(5),
                'riwayat_penyakit_keluarga' => $faker->sentence(5),
                'tekanan_darah' => rand(90, 140) . '/' . rand(60, 90),
                'suhu_tubuh' => $faker->randomFloat(1, 36, 39),
                'nadi' => rand(60, 100),
                'pernapasan' => rand(12, 25),
                'saturasi_oksigen' => rand(90, 100),
                'diagnosis' => $faker->sentence(5),
            ]);
        }
    }
}
