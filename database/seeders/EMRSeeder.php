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

        // Ambil beberapa kunjungan acak (bisa 5, bisa semua, terserah kamu)
        $kunjungans = Kunjungan::inRandomOrder()->take(5)->get();

        if ($kunjungans->isEmpty()) {
            $this->command?->warn('EMRSeeder dilewati: tabel kunjungan kosong.');
            return;
        }

        foreach ($kunjungans as $k) {
            // Kalau kamu mau pastikan 1 kunjungan cuma punya 1 EMR:
            // $emrExisting = EMR::where('kunjungan_id', $k->id)->first();
            // if ($emrExisting) continue;

            // Pastikan ada resep untuk kunjungan ini
            $resep = Resep::firstOrCreate(['kunjungan_id' => $k->id]);

            EMR::create([
                // relasi utama
                'kunjungan_id' => $k->id,
                'resep_id'     => $resep->id,

                // 🔥 SNAPSHOT dari tabel kunjungan (sesuai konsep yang kau mau)
                'pasien_id'    => $k->pasien_id,
                'dokter_id'    => $k->dokter_id,
                'poli_id'      => $k->poli_id,

                // data klinis dummy
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

        $this->command?->info('EMRSeeder: EMR dummy dibuat dengan snapshot pasien/dokter/poli dari kunjungan.');
    }
}
