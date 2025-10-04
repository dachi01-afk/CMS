<?php

namespace Database\Seeders;

use App\Models\Pasien;
use App\Models\User;
use DateTime;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PasienSeeder extends Seeder
{
    public function run(): void
    {
        $rolePasien = User::where('role', 'Pasien')->get();
        $faker = Faker::create();

        for ($i = 0; $i < $rolePasien->count(); $i++) {
            Pasien::created([
                'user_id' => $rolePasien[$i]->id,
                'nama_pasien' => $faker->name,
                'alamat' => $faker->address,
                'tanggal_lahir' => $faker->dateTimeBetween('-100 years', '-1 day'), 
            ]);
        }
    }
}
