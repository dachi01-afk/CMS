<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\KategoriObat;
use App\Models\SatuanObat;

class DataObatSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Get the IDs of all existing drug categories and units
        $kategoriObatIds = DB::table('kategori_obat')->pluck('id_kategori_obat');
        $satuanObatIds = DB::table('satuan_obat')->pluck('id_satuan_obat');

        if ($kategoriObatIds->isEmpty() || $satuanObatIds->isEmpty()) {
            $this->command->info('Kategori obat atau satuan obat tidak ditemukan. Silakan jalankan seeder terkait terlebih dahulu.');
            return;
        }

        for ($i = 0; $i < 20; $i++) {
            $hargaBeli = $faker->randomFloat(2, 5000, 250000);
            $hargaJual = $hargaBeli * $faker->randomFloat(2, 1.1, 1.5);
            $stok = $faker->numberBetween(10, 500);

            DB::table('data_obat')->insert([
                'kategori_obat_id' => $faker->randomElement($kategoriObatIds),
                'satuan_obat_id' => $faker->randomElement($satuanObatIds),
                'barcode' => $faker->unique()->ean13(),
                'nama_obat' => $faker->word() . ' ' . $faker->randomElement(['Paracetamol', 'Amoxicillin', 'Ibuprofen', 'Cetirizine', 'Vitamin C']),
                'nama_brand_farmasi' => $faker->company . ' Pharma',
                'jenis' => $faker->randomElement(['Generik', 'Paten']),
                'dosis' => $faker->randomFloat(2, 10, 500),
                'stok' => $stok,
                'stok_minimal' => $faker->numberBetween(5, 50),
                'expired_date' => $faker->dateTimeBetween('+6 months', '+3 years'),
                'nomor_batch' => $faker->unique()->uuid(),
                'harga_beli_satuan' => $hargaBeli,
                'harga_jual_umum' => $hargaJual,
                'kandungan' => $faker->paragraph(1, true),
                'is_kunci_harga' => $faker->boolean(20), // 20% chance of being locked
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
