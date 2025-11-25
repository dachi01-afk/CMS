<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seeder 1 Data User Dengan Role Admin. 
        User::updateOrCreate([
            'username' => 'Super Admin',
            'email' => 'superAdmin@gmail.com',
            'password' => Hash::make('passwordSuperAdmin'),
            'role' => 'Super Admin',
        ]);

        User::updateOrCreate([
            'username' => 'Developer',
            'email' => 'developer@gmail.com',
            'password' => Hash::make('PasswordDeveloper'),
            'role' => 'Super Admin',
        ]);

        User::updateOrCreate([
            'username' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('passwordAdmin'),
            'role' => 'Admin',
        ]);

        // Seeder 1 Data User Dengan Role Dokter. 
        User::updateOrCreate([
            'username' => 'Dokter',
            'email' => 'dokter@gmail.com',
            'password' => Hash::make('passwordDokter'),
            'role' => 'Dokter',
        ]);


        // Seeder 1 Data User Dengan Role Farmasi. 
        User::updateOrCreate([
            'username' => 'Farmasi',
            'email' => 'farmasi@gmail.com',
            'password' => Hash::make('passwordFarmasi'),
            'role' => 'Farmasi',
        ]);

        // Seeder 1 Data User Dengan Role Perawat. 
        User::updateOrCreate([
            'username' => 'Perawat',
            'email' => 'perawat@gmail.com',
            'password' => Hash::make('passwordPerawat'),
            'role' => 'Perawat',
        ]);

        User::updateOrCreate([
            'username' => 'Kasir',
            'email' => 'kasir@gmail.com',
            'password' => Hash::make('passwordKasir'),
            'role' => 'Kasir',
        ]);

        // Seeder 1 Data User Dengan Role Pasien. 
        User::updateOrCreate([
            'username' => 'Pasien',
            'email' => 'pasien@gmail.com',
            'password' => Hash::make('passwordPasien'),
            'role' => 'Pasien',
        ]);

        $faker = Faker::create('id_ID');

        $role = ['Admin', 'Dokter', 'Farmasi', 'Perawat', 'Kasir'];

        foreach ($role as $r) {
            for ($i = 0; $i < 4; $i++) {
                User::updateOrCreate([
                    'username' => $faker->username,
                    'email' => $faker->unique()->safeEmail,
                    'password' => Hash::make('password'),
                    'role' => $r,
                ]);
            }
        }

        $rolePasien = 'Pasien';

        for ($i = 0; $i < 19; $i++) {
            User::updateOrCreate([
                'username' => $faker->username,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'),
                'role' => $rolePasien,
            ]);
        }
    }
}
