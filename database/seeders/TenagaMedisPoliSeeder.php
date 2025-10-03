<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TenagaMedis;
use App\Models\Poli;

class TenagaMedisPoliSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil semua ID dari tabel tenaga_medis dan poli
        $tenagaMedisIds = DB::table('tenaga_medis')->pluck('id_tenaga_medis');
        $poliIds = DB::table('poli')->pluck('id_poli');

        // Pastikan ada data di kedua tabel
        if ($tenagaMedisIds->isEmpty() || $poliIds->isEmpty()) {
            $this->command->info('Tidak ada data di tabel tenaga_medis atau poli. Silakan jalankan seeder terkait terlebih dahulu.');
            return;
        }

        // Tentukan jumlah relasi yang ingin dibuat (misal: 20)
        $jumlahRelasi = 20;

        for ($i = 0; $i < $jumlahRelasi; $i++) {
            try {
                // Pilih ID secara acak dari kedua tabel
                $tenagaMedisId = $tenagaMedisIds->random();
                $poliId = $poliIds->random();

                // Masukkan data ke tabel pivot tenaga_medis_poli
                DB::table('tenaga_medis_poli')->insert([
                    'tenaga_medis_id' => $tenagaMedisId,
                    'poli_id' => $poliId,
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // Jika terjadi duplikasi primary key (tenaga_medis_id dan poli_id), lewati
                continue;
            }
        }
    }
}
