<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderLab;
use App\Models\OrderLabDetail;
use App\Models\JenisPemeriksaanLab;
use App\Models\Kunjungan;

class OrderLabSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Validasi Master Data Lab
        $countJenisLab = JenisPemeriksaanLab::count();
        if ($countJenisLab == 0) {
            $this->command->error('Tabel jenis_pemeriksaan_lab masih kosong!');
            return;
        }

        // 2. Validasi Master Data Kunjungan
        if (Kunjungan::count() == 0) {
            $this->command->error('Tabel kunjungan masih kosong! Jalankan KunjunganSeeder dulu.');
            return;
        }

        // 3. Buat 50 Data Order Lab menggunakan Factory
        OrderLab::factory()->count(50)->create()->each(function ($order) {

            // Setiap 1 Order, ambil random 1 sampai 4 jenis pemeriksaan
            $jenisPemeriksaan = JenisPemeriksaanLab::inRandomOrder()
                ->limit(rand(1, 4))
                ->get();

            foreach ($jenisPemeriksaan as $jenis) {
                OrderLabDetail::create([
                    'order_lab_id' => $order->id,
                    'jenis_pemeriksaan_lab_id' => $jenis->id,
                    // Logika status detail mengikuti status header
                    'status_pemeriksaan' => ($order->status == 'Selesai') ? 'Selesai' : 'Pending',
                ]);
            }
        });

        $this->command->info('50 Data Order Lab berhasil digenerate dengan relasi kunjungan acak.');
    }
}
