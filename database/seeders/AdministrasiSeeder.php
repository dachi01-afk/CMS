<?php

namespace Database\Seeders;

use App\Models\Administrasi;
use App\Models\Pembayaran;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class AdministrasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataPembayaran = Pembayaran::all();
        $faker = Faker::create('id_ID');

        foreach ($dataPembayaran as $pembayaran) {
            $banyakNyaPembayaran = rand(1, 5);
            for ($i = 0; $i < $banyakNyaPembayaran; $i++) {
                Administrasi::create([
                    'pembayaran_id' => $pembayaran->id,
                    'laporan' => implode(' ', $faker->sentences(2)),
                    'tarif' => $faker->numberBetween(50000, 1000000),
                    'periode' => $faker->word,
                ]);
            }
        }
    }
}
