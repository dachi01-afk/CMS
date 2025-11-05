<?php

namespace Database\Seeders;

use App\Models\Kasir;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class KasirSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleKasir = User::where('role', 'Kasir')->get();
        $faker = Faker::create('id_ID');
        $foto = 'foto_dokter.jpg';

        for ($i = 0; $i < $roleKasir->count(); $i++) {
            Kasir::create([
                'user_id' => $roleKasir[$i]->id,
                'nama_kasir' => $faker->name,
                'no_hp_kasir' => $faker->phoneNumber(),
                'foto_kasir' => $foto,
            ]);
        }
    }
}
