<?php

namespace Database\Seeders;

use App\Models\BatchObat;
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
            BahanHabisPakaiSeeder::class,
            JadwalDokterSeeder::class,
            KunjunganSeeder::class,
            KunjunganLayananSeeder::class,
            ResepSeeder::class,
            ResepObatSeeder::class,
            EMRSeeder::class,
            MetodePembayaranSeeder::class,
            PerawatDokterPoliSeeder::class,
            PembayaranSeeder::class,
            SatuanLabSeeder::class,
            JenisPemeriksaanLabSeeder::class,
            JenisPemeriksaanRadiologiSeeder::class,
            // OrderLabSeeder::class,
            // OrderRadiologiSeeder::class,
        ]);
    }
}
