<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BahanHabisPakai; // Pastikan Model ini sudah ada
use App\Models\BrandFarmasi;    // Sesuaikan nama Model-nya
use App\Models\JenisObat;       // Sesuaikan nama Model-nya
use App\Models\Satuan;          // Sesuaikan nama Model-nya
use App\Models\SatuanObat;
use Carbon\Carbon;

class BahanHabisPakaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan tabel master ada isinya agar tidak error
        if (BrandFarmasi::count() == 0 || SatuanObat::count() == 0) {
            $this->command->error('Data Brand atau Satuan masih kosong. Jalankan seeder master dulu!');
            return;
        }

        // Contoh membuat 10 data random menggunakan loop
        for ($i = 1; $i <= 10; $i++) {
            BahanHabisPakai::create([
                'brand_farmasi_id'      => BrandFarmasi::inRandomOrder()->first()->id,
                'jenis_id'              => JenisObat::inRandomOrder()->first()->id,
                'satuan_id'             => SatuanObat::inRandomOrder()->first()->id,
                'kode'                  => 'BHP-' . strtoupper(fake()->unique()->bothify('??###')),
                'nama_barang'           => fake()->randomElement(['Kapas Steril', 'Kasa Roll', 'Spuit 3cc', 'Handscoon M', 'Alkohol Swab']),
                'stok_barang'           => rand(50, 200),
                'dosis'                 => 0.00,
                'tanggal_kadaluarsa_bhp' => Carbon::now()->addYears(rand(1, 3))->format('Y-m-d'),
                'no_batch'              => 'BTCH-' . fake()->bothify('??##'),
                'harga_beli_satuan_bhp' => 10000.00,
                'avg_hpp_bhp'           => 10000.00,
                'harga_jual_umum_bhp'   => 15000.00,
                'harga_otc_bhp'         => 13500.00,
                'keterangan'            => 'Seed data via Model',
            ]);
        }

        $this->command->info('10 Data Bahan Habis Pakai berhasil ditambahkan via Model!');
    }
}