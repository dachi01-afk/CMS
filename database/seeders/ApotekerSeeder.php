<?php

namespace Database\Seeders;

use App\Models\Apoteker;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class ApotekerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleApoteker = User::where('role', 'Apoteker')->get();
        $faker = Faker::create();
        $foto = 'foto_dokter.jpg';

        for ($i = 0; $i < $roleApoteker->count(); $i++) {
            Apoteker::create([
                'user_id' => $roleApoteker[$i]->id,
                'nama_apoteker' => $faker->name,
                'no_hp_apoteker' => $faker->phoneNumber(),
                'foto_apoteker' => $foto,
            ]);
        }
    }
}
