<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\KategoriObat;
use App\Models\KunjunganLayanan;
use App\Models\MetodePembayaran;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            PoliSeeder::class,
            LayananSeeder::class,
            JenisSpesialisSeeder::class,
            AdminSeeder::class,
            DokterSeeder::class,
            ApotekerSeeder::class,
            PasienSeeder::class,
            ObatSeeder::class,
            JadwalDokterSeeder::class,
            KunjunganSeeder::class,
            KunjunganLayananSeeder::class,
            // TestimoniSeeder::class,
            ResepSeeder::class,
            ResepObatSeeder::class,
            EMRSeeder::class,
            MetodePembayaranSeeder::class,
            // TesLabSeeder::class,
            PembayaranSeeder::class,
            // AdministrasiSeeder::class,
        ]);
    }
}
