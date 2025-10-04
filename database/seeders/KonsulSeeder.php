<?php

namespace Database\Seeders;

use App\Models\Konsul;
use App\Models\Kunjungan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class KonsulSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataKunjungan = Kunjungan::all();
        $faker = Faker::create();

        foreach ($dataKunjungan as $kunjungan) {
            // Setiap kunjungan memiliki 1–3 tes lab
            $jumlahTes = rand(1, 3);

            for ($i = 0; $i < $jumlahTes; $i++) {
                Konsul::create([
                    'kunjungan_id' => $kunjungan->id,
                    'diagnosa' => $faker->word(),
                    'catatan' => $faker->word(),
                ]);
            }
        }
    }
}
