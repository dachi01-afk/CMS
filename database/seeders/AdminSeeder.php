<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleAdmin = User::where('role', 'Admin')->get();
        $faker = Faker::create();

        for ($i = 0; $i < $roleAdmin->count(); $i++) {
            Admin::create([
                'user_id' => $roleAdmin[$i]->id,
                'nama_admin' => $faker->name,
                'no_hp' => $faker->phoneNumber(),
            ]);
        }
    }
}
