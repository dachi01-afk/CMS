<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use App\Models\PembelianObat;
use App\Models\DataObat;


class DetailPembelianObatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $pembelianObatIds = DB::table('pembelian_obat')->pluck('id_pembelian_obat');
        $obatIds = DB::table('data_obat')->pluck('id_obat');

        if ($pembelianObatIds->isEmpty() || $obatIds->isEmpty()) {
            $this->command->info('Pembelian obat atau data obat tidak ditemukan. Jalankan seeder terkait terlebih dahulu.');
            return;
        }

        for ($i = 0; $i < 20; $i++) {
            $jumlah = $faker->numberBetween(10, 500);
            $hargaBeli = $faker->randomFloat(2, 5000, 250000);
            $totalHarga = $jumlah * $hargaBeli;

            DB::table('detail_pembelian_obat')->insert([
                'pembelian_obat_id' => $faker->randomElement($pembelianObatIds),
                'obat_id' => $faker->randomElement($obatIds),
                'jumlah' => $totalHarga,
                'harga_beli' => $hargaBeli,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
