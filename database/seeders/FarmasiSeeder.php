<?php

namespace Database\Seeders;

use App\Models\Farmasi;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class FarmasiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleFarmasi = User::where('role', 'Farmasi')->get();
        $faker = Faker::create('id_ID');
        $foto = 'foto_dokter.jpg';

        for ($i = 0; $i < $roleFarmasi->count(); $i++) {
            Farmasi::create([
                'user_id' => $roleFarmasi[$i]->id,
                'nama_farmasi' => $faker->name,
                'no_hp_farmasi' => $faker->phoneNumber(),
                'foto_farmasi' => $foto,
            ]);
        }
    }
}
