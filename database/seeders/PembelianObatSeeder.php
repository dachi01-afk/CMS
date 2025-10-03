<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\Supplier;

class PembelianObatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Get the IDs of all existing suppliers
        $supplierIds = DB::table('supplier')->pluck('id_supplier');

        if ($supplierIds->isEmpty()) {
            $this->command->info('Tidak ada supplier ditemukan. Silakan jalankan seeder supplier terlebih dahulu.');
            return;
        }

        for ($i = 0; $i < 20; $i++) {
            // Generate a realistic total purchase price
            $totalHarga = $faker->randomFloat(2, 500000, 5000000);

            DB::table('pembelian_obat')->insert([
                'tanggal_pembelian' => $faker->dateTimeBetween('-1 year', 'now'),
                'supplier_id' => $faker->randomElement($supplierIds),
                'total_harga' => $totalHarga,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
