<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DetailPembayaranLayananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            // Asumsi ada 20 data di tabel pembayaran
            $pembayaranId = rand(1, 20);
            // Asumsi ada 20 data di tabel data_layanans
            $layananId = rand(1, 20);

            // Ambil harga dari tabel data_layanans (disini disimulasikan)
            // Di aplikasi nyata, harga ini diambil dari DB
            $hargaLayanan = DB::table('data_layanan')->where('id_layanan', $layananId)->value('harga');

            DB::table('detail_pembayaran_layanan')->insert([
                'pembayaran_id' => $pembayaranId,
                'layanan_id' => $layananId,
                'harga_satuan' => $hargaLayanan,
                'total_harga_item' => $hargaLayanan,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
