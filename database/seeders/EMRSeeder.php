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
        // $dataKunjungan = Kunjungan::all();
        // $dataResep = Resep::all();
        // $faker = Faker::create('id_ID');

        // foreach ($dataKunjungan as $kunjungan) {
        //     // $banyakNyaKunjungan = rand(1, 5);
        //     for ($i = 0; $i < 2; $i++) {
        //         EMR::create([
        //             'kunjungan_id' => $kunjungan->id,
        //             'resep_id' => $dataResep->random()->id,
        //             'keluhan_utama' => $faker->sentence(10),  // teks panjang
        //             'riwayat_penyakit_sekarang' => $faker->sentence(5),
        //             'riwayat_penyakit_dahulu' => $faker->sentence(5),
        //             'riwayat_penyakit_keluarga' => $faker->sentence(5),
        //             'tekanan_darah' => rand(90, 140) . '/' . rand(60, 90),  // string 10 karakter, contoh '120/80'
        //             'suhu_tubuh' => $faker->randomFloat(1, 36, 39),  // desimal 4,1 antara 36.0 dan 39.0
        //             'nadi' => rand(60, 100),  // integer
        //             'pernapasan' => rand(12, 25),  // integer
        //             'saturasi_oksigen' => rand(90, 100),  // integer
        //             'diagnosis' => $faker->sentence(5),  // teks panjang
        //         ]);
        //     }
        // }

        EMR::create([
            'kunjungan_id' => 1,
            'resep_id' => 1,
        ]);
    }
}
