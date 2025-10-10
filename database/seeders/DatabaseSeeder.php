<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\KategoriObat;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AdminSeeder::class,
            JenisSpesialisSeeder::class,
            DokterSeeder::class,
            ApotekerSeeder::class,
            PasienSeeder::class,
            ObatSeeder::class,
            JadwalDokterSeeder::class,
            // KunjunganSeeder::class,
            // TestimoniSeeder::class,
            // ResepSeeder::class,
            // ResepObatSeeder::class,
            // TesLabSeeder::class,
            // KonsulSeeder::class,
            // PembayaranSeeder::class,
            // AdministrasiSeeder::class,
            // EMRSeeder::class,
            // TransaksiApotekerSeeder::class,
        ]);
    }
}
