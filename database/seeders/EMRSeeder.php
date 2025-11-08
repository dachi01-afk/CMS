<?php

namespace Database\Seeders;

use App\Models\EMR;
use App\Models\Kunjungan;
use App\Models\Resep;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class EMRSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Ambil 5 kunjungan acak (atau kurang jika data terbatas)
        $kunjungans = Kunjungan::inRandomOrder()->take(5)->get();

        if ($kunjungans->isEmpty()) {
            $this->command?->warn('EMRSeeder dilewati: tabel kunjungan kosong.');
            return;
        }

        foreach ($kunjungans as $k) {
            // Pastikan ada resep untuk kunjungan ini
            $resep = Resep::firstOrCreate(['kunjungan_id' => $k->id]);

            EMR::create([
                'kunjungan_id'              => $k->id,
                'resep_id'                  => $resep->id,
                'keluhan_utama'             => $faker->sentence(10),
                'riwayat_penyakit_dahulu'   => $faker->sentence(8),
                'riwayat_penyakit_keluarga' => $faker->sentence(8),
                'tekanan_darah'             => $faker->numberBetween(100, 140) . '/' . $faker->numberBetween(60, 90),
                'suhu_tubuh'                => $faker->randomFloat(1, 36.0, 39.5),
                'nadi'                      => $faker->numberBetween(60, 100),
                'pernapasan'                => $faker->numberBetween(12, 24),
                'saturasi_oksigen'          => $faker->numberBetween(93, 100),
                'diagnosis'                 => $faker->sentence(6),
            ]);
        }

        $this->command?->info('EMRSeeder: 5 record dibuat.');
    }
}
