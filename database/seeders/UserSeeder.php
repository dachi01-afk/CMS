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
        User::create([
            'username' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('passwordAdmin'),
            'role' => 'Admin',
        ]);

        // Seeder 1 Data User Dengan Role Dokter. 
        User::create([
            'username' => 'Dokter',
            'email' => 'dokter@gmail.com',
            'password' => Hash::make('passwordDokter'),
            'role' => 'Dokter',
        ]);


        // Seeder 1 Data User Dengan Role Farmasi. 
        User::create([
            'username' => 'Farmasi',
            'email' => 'farmasi@gmail.com',
            'password' => Hash::make('passwordFarmasi'),
            'role' => 'Farmasi',
        ]);

        User::create([
            'username' => 'Kasir',
            'email' => 'kasir@gmail.com',
            'password' => Hash::make('passwordKasir'),
            'role' => 'Kasir',
        ]);

        // Seeder 1 Data User Dengan Role Pasien. 
        User::create([
            'username' => 'Pasien',
            'email' => 'pasien@gmail.com',
            'password' => Hash::make('passwordPasien'),
            'role' => 'Pasien',
        ]);

        $faker = Faker::create();

        $role = ['Admin', 'Dokter', 'Farmasi', 'Kasir'];

        foreach ($role as $r) {
            for ($i = 0; $i < 4; $i++) {
                User::create([
                    'username' => $faker->username,
                    'email' => $faker->unique()->safeEmail,
                    'password' => Hash::make('password'),
                    'role' => $r,
                ]);
            }
        }

        $rolePasien = 'Pasien';

        for ($i = 0; $i < 19; $i++) {
            User::create([
                'username' => $faker->username,
                'email' => $faker->unique()->safeEmail,
                'password' => Hash::make('password'),
                'role' => $rolePasien,
            ]);
        }
    }
}
