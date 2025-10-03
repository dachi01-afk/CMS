<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\TenagaMedis;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class JadwalPraktikSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $records = [];
        $now = now();
        $maxTenagaMedisId = 4; // Asumsi ada 4 Tenaga Medis (Dokter/Perawat)

        // Tentukan template jadwal realistis (Jam Mulai, Durasi dalam jam)
        $scheduleTemplates = [
            // Shift Pagi (3 jam atau 4 jam)
            ['start' => '08:00:00', 'duration' => 3],
            ['start' => '09:00:00', 'duration' => 4],
            // Shift Siang (3 jam)
            ['start' => '13:00:00', 'duration' => 3],
            // Shift Sore/Malam (4 jam)
            ['start' => '16:00:00', 'duration' => 4],
            ['start' => '18:00:00', 'duration' => 3],
        ];

        // Looping untuk membuat 20 record
        for ($i = 0; $i < 20; $i++) {
            $tenagaMedisId = $faker->numberBetween(1, $maxTenagaMedisId);

            // Tentukan tanggal praktik dalam 1-2 minggu ke depan
            $tanggalPraktik = $faker->dateTimeBetween('now', '+14 days')->format('Y-m-d');

            // Pilih template jadwal
            $template = $faker->randomElement($scheduleTemplates);
            $jamMulai = $template['start'];

            // Hitung jam selesai
            $jamSelesai = Carbon::parse($jamMulai)
                ->addHours($template['duration'])
                ->format('H:i:s');

            $records[] = [
                'tenaga_medis_id' => $tenagaMedisId,
                'tanggal_praktik' => $tanggalPraktik,
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('jadwal_praktik')->insert($records);
    }
}
