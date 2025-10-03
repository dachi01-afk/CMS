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
        // no fk
        $this->call([
            UserSeeder::class,
            PasienSeeder::class,
            TenagaMedisSeeder::class,
            KategoriObatSeeder::class,
            SatuanObatSeeder::class,
            DataObatSeeder::class,
            DataLayananSeeder::class,
            PoliSeeder::class,
            SupplierSeeder::class,
            DataPenjaminSeeder::class,
        ]);


        $this->call([
            PenanggungJawabSeeder::class,
            KunjunganSeeder::class,
            DetailPenjaminanKunjunganSeeder::class,
            RekamMedisSeeder::class,
            VitalSignSeeder::class,
            RiwayatPasienSeeder::class,
            PsikososialSpiritualSeeder::class,
            PengantarSeeder::class,
            JadwalPraktikSeeder::class,
            PembayaranSeeder::class,
            PembelianObatSeeder::class,
            DetailPembelianObatSeeder::class,
            ResepObatSeeder::class,
            DetailPembayaranObatSeeder::class,
            DetailPembayaranLayananSeeder::class,
            TenagaMedisPoliSeeder::class,
        ]);

        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
