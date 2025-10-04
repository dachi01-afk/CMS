<?php

namespace Database\Seeders;

use App\Models\EMR;
use App\Models\Kunjungan;
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
        $faker = Faker::create('id_ID');

        foreach ($dataKunjungan as $kunjungan) {
            $banyakNyaKunjungan = rand(1, 5);
            for ($i = 0; $i < $banyakNyaKunjungan; $i++) {
                EMR::create([
                    'kunjungan_id' => $kunjungan->id,
                    'riwayat_penyakit' => $faker->sentence(),
                    'alergi' => $faker->sentence(),
                    'hasil_periksa' => $faker->sentence(),
                ]);
            }
        }
    }
}
