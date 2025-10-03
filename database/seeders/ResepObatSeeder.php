<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\RekamMedis;
use App\Models\DataObat;


class ResepObatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Get the IDs of existing medical records and drugs
        $rekamMedisIds = DB::table('rekam_medis')->pluck('id_rekam_medis');
        $obatIds = DB::table('data_obat')->pluck('id_obat');

        if ($rekamMedisIds->isEmpty() || $obatIds->isEmpty()) {
            $this->command->info('Rekam medis atau data obat tidak ditemukan. Silakan jalankan seeder terkait terlebih dahulu.');
            return;
        }

        // Define a list of common usage instructions
        $aturanPakai = [
            '3x sehari 1 tablet sesudah makan',
            '2x sehari 1 tablet sebelum makan',
            '1x sehari 1 kapsul malam hari',
            'Sesuai anjuran dokter',
            'Dioleskan tipis 2x sehari',
            'Tetes mata 1 tetes 3x sehari',
            '1x sehari 1 tablet',
            '2x sehari 1/2 tablet',
            'Satu sendok teh 3x sehari',
            'Disuntikkan sesuai dosis'
        ];

        for ($i = 0; $i < 20; $i++) {
            DB::table('resep_obat')->insert([
                'rekam_medis_id' => $faker->unique()->randomElement($rekamMedisIds),
                'obat_id' => $faker->randomElement($obatIds),
                'jumlah_obat' => $faker->numberBetween(1, 60),
                'aturan_pakai' => $faker->randomElement($aturanPakai),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
