<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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


        // Seeder 1 Data User Dengan Role Apoteker. 
        User::create([
            'username' => 'Apoteker',
            'email' => 'apoteker@gmail.com',
            'password' => Hash::make('passwordApoteker'),
            'role' => 'Apoteker',
        ]);

        // Seeder 1 Data User Dengan Role Pasien. 
        User::create([
            'username' => 'Pasien',
            'email' => 'apoteker@gmail.com',
            'password' => Hash::make('passwordPasien'),
            'role' => 'Pasien',
        ]);
    }
}
