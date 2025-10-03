<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Kunjungan;

class PengantarSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Get the IDs of all existing visits
        $kunjunganIds = DB::table('kunjungan')->pluck('id_kunjungan');

        // Check if there are any visits to link to
        if ($kunjunganIds->isEmpty()) {
            $this->command->info('No visits found. Please run the kunjungan seeder first.');
            return;
        }

        // Define a list of common relationships
        $hubungan = ['Orang Tua', 'Pasangan', 'Anak', 'Saudara Kandung', 'Teman', 'Lainnya'];

        // Create 20 dummy records
        for ($i = 0; $i < 20; $i++) {
            DB::table('pengantar')->insert([
                'kunjungan_id' => $faker->unique()->randomElement($kunjunganIds),
                'nama_lengkap' => $faker->name,
                'hubungan_dengan_pasien' => $faker->randomElement($hubungan),
                'alamat' => $faker->address,
                'no_tlp' => $faker->phoneNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
