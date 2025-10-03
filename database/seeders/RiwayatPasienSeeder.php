<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Pasien;

class RiwayatPasienSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Get IDs of all existing patients
        $pasienIds = DB::table('pasien')->pluck('id_pasien');

        if ($pasienIds->isEmpty()) {
            $this->command->info('Tidak ada data pasien ditemukan. Silakan jalankan seeder pasien terlebih dahulu.');
            return;
        }

        $alergi = ['Tidak ada', 'Debu', 'Makanan Laut', 'Obat-obatan', 'Serbuk Sari', 'Dingin'];

        for ($i = 0; $i < 20; $i++) {
            $hasRiwayatPenyakit = $faker->boolean(70); // 70% chance of having a medical history
            $hasRiwayatKeluarga = $faker->boolean(50); // 50% chance of a family history
            $hasRiwayatObat = $faker->boolean(40); // 40% chance of a medication history

            DB::table('riwayat_pasien')->insert([
                'pasien_id' => $faker->unique()->randomElement($pasienIds),
                'nama_alergi' => $faker->randomElement($alergi),
                'riwayat_penyakit_pasien' => $hasRiwayatPenyakit ? $faker->paragraph(2, true) : null,
                'riwayat_penyakit_keluarga' => $hasRiwayatKeluarga ? $faker->paragraph(2, true) : null,
                'riwayat_penggunaan_obat' => $hasRiwayatObat ? $faker->paragraph(2, true) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
