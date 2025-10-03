<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;


class DetailPembayaranObatSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Get the IDs of existing payments and prescription items
        $pembayaranIds = DB::table('pembayaran')->pluck('id_pembayaran');
        $resepObatIds = DB::table('resep_obat')->pluck('id_resep');

        if ($pembayaranIds->isEmpty() || $resepObatIds->isEmpty()) {
            $this->command->info('Pembayaran atau resep obat tidak ditemukan. Silakan jalankan seeder terkait terlebih dahulu.');
            return;
        }

        for ($i = 0; $i < 20; $i++) {
            // Get a random prescription record
            $resepObat = DB::table('resep_obat')->where('id_resep', $faker->randomElement($resepObatIds))->first();

            // Get the drug and quantity from the prescription
            $obat = DB::table('data_obat')->where('id_obat', $resepObat->obat_id)->first();
            $jumlahObat = $resepObat->jumlah_obat;

            // Set the unit price from the drug data
            $hargaSatuan = $obat->harga_jual_umum;

            // Calculate the total item price
            $totalHargaItem = $hargaSatuan * $jumlahObat;

            DB::table('detail_pembayaran_obat')->insert([
                'pembayaran_id' => $faker->randomElement($pembayaranIds),
                'resep_id' => $resepObat->id_resep,
                'harga_satuan' => $hargaSatuan,
                'total_harga_item' => $totalHargaItem,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
