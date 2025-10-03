<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Kunjungan;

class RekamMedisSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Get the IDs of all existing visits to link the medical records
        $kunjunganIds = DB::table('kunjungan')->pluck('id_kunjungan');

        if ($kunjunganIds->isEmpty()) {
            $this->command->info('No visits found. Please run the kunjungan seeder first.');
            return;
        }

        for ($i = 0; $i < 20; $i++) {
            // Randomly select a visit ID
            $kunjunganId = $kunjunganIds->random();

            // Randomly set a prescription completion time, 70% of the time
            $waktuResepSelesai = $faker->boolean(70) ? $faker->dateTimeBetween('-1 week', 'now') : null;

            DB::table('rekam_medis')->insert([
                'kunjungan_id' => $kunjunganId,
                'waktu_resep_selesai' => $waktuResepSelesai,
                'keluhan' => $faker->sentence(10, true), // A short sentence for symptoms
                'prosedur_rencana' => $faker->paragraph(3, true), // A longer paragraph for the treatment plan
                'informasi_kondisi_pasien' => $faker->paragraph(2, true), // A paragraph for patient condition notes
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
