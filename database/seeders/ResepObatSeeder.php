<?php

namespace Database\Seeders;

use App\Models\Obat;
use App\Models\Resep;
use App\Models\ResepObat;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ResepObatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataResep = Resep::all();
        $dataObat = Obat::all();
        $faker = Faker::create();

        for ($i = 0; $i < 30; $i++) {
            ResepObat::create([
                'resep_id' => $dataResep->random()->id,
                'obat_id' => $dataObat->random()->id,
                'jumlah' => $faker->numberBetween(30, 100),
                'dosis' => $faker->randomFloat(2, 1, 100),
                'status' => 'Belum Diambil',
            ]);
        }
    }
}
