<?php

namespace Database\Seeders;

use App\Models\JenisPemeriksaanRadiologi;
use App\Models\Kunjungan;
use App\Models\OrderRadiologi;
use App\Models\OrderRadiologiDetail;
use Database\Factories\OrderRadiologiFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderRadiologiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Validasi Master Data Lab
        $countJenisLab = JenisPemeriksaanRadiologi::count();
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
        OrderRadiologi::factory()->count(50)->create()->each(function ($order) {

            // Setiap 1 Order, ambil random 1 sampai 4 jenis pemeriksaan
            $jenisPemeriksaan = JenisPemeriksaanRadiologi::inRandomOrder()
                ->limit(rand(1, 4))
                ->get();

            foreach ($jenisPemeriksaan as $jenis) {
                OrderRadiologiDetail::create([
                    'order_radiologi_id' => $order->id,
                    'jenis_pemeriksaan_radiologi_id' => $jenis->id,
                    // Logika status detail mengikuti status header
                    'status_pemeriksaan' => ($order->status == 'Selesai') ? 'Selesai' : 'Pending',
                ]);
            }
        });

        $this->command->info('50 Data Order Lab berhasil digenerate dengan relasi kunjungan acak.');
    }
}
