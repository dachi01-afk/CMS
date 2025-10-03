<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PoliSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        $poliList = [
            'Poli Umum',
            'Poli Gigi',
            'Poli Anak',
            'Poli Bedah',
            'Poli Mata',
            'Poli Kandungan dan Kebidanan',
            'Poli THT (Telinga, Hidung, Tenggorokan)',
            'Poli Penyakit Dalam',
            'Poli Kulit dan Kelamin',
            'Poli Jantung',
            'Poli Saraf',
            'Poli Gizi',
            'Poli Rehabilitasi Medis',
            'Poli Ortopedi',
            'Poli Urologi',
            'Poli Paru',
            'Poli Onkologi',
            'Poli Psikologi',
            'Poli Akupunktur',
            'Poli Gigi dan Mulut'
        ];

        foreach ($poliList as $poli) {
            DB::table('poli')->insert([
                'nama_poli' => $poli,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
