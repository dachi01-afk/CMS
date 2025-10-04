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

        for ($i = 0; $i < $roleApoteker->count(); $i++) {
            Apoteker::create([
                'user_id' => $roleApoteker[$i]->id,
                'nama_apoteker' => $faker->name,
                'email_apoteker' => $faker->unique()->safeEmail,
                'no_hp_apoteker' => $faker->phoneNumber(),
            ]);
        }
    }
}
