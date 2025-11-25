<?php

namespace Database\Seeders;

use App\Models\SuperAdmin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roleSuperAdmin = User::where('role', 'Super Admin')->get();

        $faker = Faker::create('id_ID');

        foreach ($roleSuperAdmin as $superAdmin) {
            SuperAdmin::updateOrCreate(['user_id' => $superAdmin->id],
                ['nama_super_admin' => $superAdmin->username,
                'no_hp_super_admin' => $faker->phoneNumber(),
            ]);
        }
    }
}
