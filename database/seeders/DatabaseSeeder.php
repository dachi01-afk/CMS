<?php

namespace Database\Seeders;

use Database\Seeders\AdminSeeder;
use Database\Seeders\BatchObatDepotSeeder;
use Database\Seeders\BatchObatSeeder;
use Database\Seeders\BrandFarmasiSeeder;
use Database\Seeders\DepotObatSeeder;
use Database\Seeders\DepotSeeder;
use Database\Seeders\DokterPoliSeeder;
use Database\Seeders\DokterSeeder;
use Database\Seeders\FarmasiSeeder;
use Database\Seeders\JadwalDokterSeeder;
use Database\Seeders\JenisObatSeeder;
use Database\Seeders\JenisSpesialisSeeder;
use Database\Seeders\KasirSeeder;
use Database\Seeders\KategoriLayananSeeder;
use Database\Seeders\KategoriObatSeeder;
use Database\Seeders\LayananSeeder;
use Database\Seeders\MetodePembayaranSeeder;
use Database\Seeders\ObatSeeder;
use Database\Seeders\PasienSeeder;
use Database\Seeders\PerawatDokterPoliSeeder;
use Database\Seeders\PerawatSeeder;
use Database\Seeders\PoliSeeder;
use Database\Seeders\SatuanObatSeeder;
use Database\Seeders\SuperAdminSeeder;
use Database\Seeders\TipeDepotSeeder;
use Database\Seeders\UserSeeder;
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
            AdminSeeder::class,
            JenisSpesialisSeeder::class,
            DokterSeeder::class,
            PerawatSeeder::class,
            FarmasiSeeder::class,
            KasirSeeder::class,
            PasienSeeder::class,
            PoliSeeder::class,
            KategoriLayananSeeder::class,
            KategoriObatSeeder::class,
            LayananSeeder::class,
            DokterPoliSeeder::class,
            BrandFarmasiSeeder::class,
            TipeDepotSeeder::class,
            DepotSeeder::class,
            JenisObatSeeder::class,
            SatuanObatSeeder::class,
            ObatSeeder::class,
            BatchObatSeeder::class,
            BatchObatDepotSeeder::class,
            DepotObatSeeder::class,
            JadwalDokterSeeder::class,
            KunjunganSeeder::class,
            KunjunganLayananSeeder::class,
            ResepSeeder::class,
            ResepObatSeeder::class,
            EMRSeeder::class,
            MetodePembayaranSeeder::class,
            PerawatDokterPoliSeeder::class,
            SatuanLabSeeder::class,
            JenisPemeriksaanLabSeeder::class,
            JenisPemeriksaanRadiologiSeeder::class,
            OrderLabSeeder::class,
            OrderRadiologiSeeder::class,
            PembayaranSeeder::class,
        ]);
    }
}
