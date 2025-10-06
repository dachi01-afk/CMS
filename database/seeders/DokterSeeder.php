<?php

namespace Database\Seeders;

use App\Models\Dokter;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class DokterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleDokter = User::where('role', 'Dokter')->get();
        $faker = Faker::create();
        $spesialis = ['Determatologi', 'Psikiatri', 'Onkologi', 'Kardiologi'];

        for ($i = 0; $i < $roleDokter->count(); $i++) {
            Dokter::create([
                // 'user_id' => $roleDokter[$i]->id,
                'nama_dokter' => $faker->name,
                'spesialisasi' => $faker->randomElement($spesialis),
                'email' => $faker->unique()->safeEmail,
                'no_hp' => $faker->phoneNumber(),
            ]);
        }
    }
}
