<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\KategoriObat;
use App\Models\MetodePembayaran;
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
<<<<<<< HEAD
            KunjunganLayananSeeder::class,
            TestimoniSeeder::class,
=======
            // KunjunganLayananSeeder::class,
            // TestimoniSeeder::class,
>>>>>>> 889d6acc4c4e0f879d23a1ef106f70bcd595e7a9
            ResepSeeder::class, 
            ResepObatSeeder::class,
            EMRSeeder::class,
            MetodePembayaranSeeder::class,
            // TesLabSeeder::class,
            PembayaranSeeder::class,
<<<<<<< HEAD
            AdministrasiSeeder::class,
=======
            // AdministrasiSeeder::class,
>>>>>>> 889d6acc4c4e0f879d23a1ef106f70bcd595e7a9
        ]);
    }
}
