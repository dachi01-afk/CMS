<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Kunjungan;


class VitalSignSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Get all kunjungan IDs to link vital signs to an existing visit
        $kunjunganIds = DB::table('kunjungan')->pluck('id_kunjungan');

        // Stop if no visits exist
        if ($kunjunganIds->isEmpty()) {
            $this->command->info('No visits found. Please run the kunjungan seeder first.');
            return;
        }

        // Loop to create 20 vital sign records
        for ($i = 0; $i < 20; $i++) {
            DB::table('vital_sign')->insert([
                'kunjungan_id' => $faker->unique()->randomElement($kunjunganIds),
                'tinggi_badan' => $faker->randomFloat(2, 1.50, 1.85),
                'berat_badan' => $faker->randomFloat(2, 45.00, 95.00),
                'gula_darah' => $faker->randomFloat(2, 80.00, 120.00),
                'suhu_tubuh' => $faker->randomFloat(2, 36.00, 37.50),
                'sistole' => $faker->numberBetween(100, 140),
                'diastole' => $faker->numberBetween(60, 90),
                'laju_pernapasan' => $faker->numberBetween(12, 20),
                'lingkar_perut' => $faker->randomFloat(2, 60.00, 100.00),
                'denyut_nadi' => $faker->numberBetween(60, 100),
                'oksigen' => $faker->numberBetween(95, 100),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
