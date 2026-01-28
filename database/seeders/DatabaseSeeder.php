<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\KategoriObat;
use App\Models\KunjunganLayanan;
use App\Models\MetodePembayaran;
use App\Models\OrderLab;
use App\Models\SatuanLab;
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
            SuperAdminSeeder::class,
            PoliSeeder::class,
            KategoriLayananSeeder::class,
            KategoriObatSeeder::class,
            LayananSeeder::class,
            JenisSpesialisSeeder::class,
            AdminSeeder::class,
            DokterSeeder::class,
            DokterPoliSeeder::class,
            PerawatSeeder::class,
            FarmasiSeeder::class,
            KasirSeeder::class,
            PasienSeeder::class,
            BrandFarmasiSeeder::class,
            JenisObatSeeder::class,
            SatuanObatSeeder::class,
            ObatSeeder::class,
            JadwalDokterSeeder::class,
            KunjunganSeeder::class,
            // KunjunganLayananSeeder::class,
            // ResepSeeder::class,
            // ResepObatSeeder::class,
            // EMRSeeder::class,
            // MetodePembayaranSeeder::class,
            PerawatDokterPoliSeeder::class,
            // PembayaranSeeder::class,
            // TesLabSeeder::class,
            // AdministrasiSeeder::class,
            // TestimoniSeeder::class,
            // TipeDepotSeeder::class,
            // DepotSeeder::class,
            // DepotObatSeeder::class,
            SatuanLabSeeder::class,
            JenisPemeriksaanLabSeeder::class,
            OrderLabSeeder::class,
            OrderLabDetailSeeder::class,
        ]);
    }
}
