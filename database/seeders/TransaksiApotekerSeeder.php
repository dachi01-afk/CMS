<?php

namespace Database\Seeders;

use App\Models\Apoteker;
use App\Models\Resep;
use App\Models\TransaksiApoteker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class TransaksiApotekerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataResep = Resep::all();
        $dataApoteker = Apoteker::all();
        $faker = Faker::create('id_ID');

        for ($i = 0; $i < 20; $i++) {
            TransaksiApoteker::create([
                'resep_id' => $dataResep->random()->id,
                'apoteker_id' => $dataApoteker->random()->id,
                'tanggal_transaksi_apoteker' => $faker->dateTimeBetween('-1 years', '-1 day'),
                'total_harga' => $faker->numberBetween(50000, 1000000),
            ]);
        }
    }
}
