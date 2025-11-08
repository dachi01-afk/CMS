<?php

namespace Database\Seeders;

use App\Models\Perawat;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PerawatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rolePerawat = User::where('role', 'Perawat')->get();
        $faker = Faker::create('id_ID');
        $foto = 'foto_dokter.jpg';

        for ($i = 0; $i < $rolePerawat->count(); $i++) {
            Perawat::create([
                'user_id' => $rolePerawat[$i]->id,
                'nama_perawat' => $faker->name,
                'no_hp_perawat' => $faker->phoneNumber(),
                'foto_perawat' => $foto,
            ]);
        }
    }
}
