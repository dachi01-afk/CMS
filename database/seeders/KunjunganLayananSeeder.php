<?php

namespace Database\Seeders;

use App\Models\Kunjungan;
use App\Models\KunjunganLayanan;
use App\Models\Layanan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KunjunganLayananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // KunjunganLayanan::create([
        //     'kunjungan_id' => 1,
        //     'layanan_id' => 1,
        //     'jumlah' => 1,
        // ]);
        // KunjunganLayanan::create([
        //     'kunjungan_id' => 1,
        //     'layanan_id' => 2,
        //     'jumlah' => 2,
        // ]);

        // Pastikan ada data di tabel kunjungan dan layanan
        $kunjunganIds = Kunjungan::pluck('id')->toArray();
        $layananIds = Layanan::pluck('id')->toArray();

        if (empty($kunjunganIds) || empty($layananIds)) {
            $this->command->warn('⚠️ Seeder gagal: Data kunjungan atau layanan belum ada.');
            return;
        }

        // Generate data dummy untuk kunjungan_layanan
        foreach ($kunjunganIds as $kunjunganId) {
            // ambil 1–3 layanan acak untuk setiap kunjungan
            $randomLayanan = collect($layananIds)->random(rand(1, 3));

            foreach ($randomLayanan as $layananId) {
                KunjunganLayanan::create([
                    'kunjungan_id' => $kunjunganId,
                    'layanan_id' => $layananId,
                    'jumlah' => rand(1, 5),
                ]);
            }
        }

        $this->command->info('✅ Data dummy KunjunganLayanan berhasil ditambahkan!');
    }
}
