<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisPemeriksaanLabSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ambil ID satuan pertama (misal: mg/dL) sebagai contoh
        $satuan = DB::table('satuan_lab')->first();

        if (!$satuan) {
            $this->command->error('Data satuan_lab tidak ditemukan! Jalankan SatuanLabSeeder dulu.');
            return;
        }

        $data = [
            [
                'satuan_lab_id' => $satuan->id,
                'kode_pemeriksaan' => 'LAB-001',
                'nama_pemeriksaan' => 'Gula Darah Sewaktu',
                'nilai_normal' => 140,
                'harga_pemeriksaan_lab' => 45000.00,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'satuan_lab_id' => $satuan->id,
                'kode_pemeriksaan' => 'LAB-002',
                'nama_pemeriksaan' => 'Asam Urat',
                'nilai_normal' => 7,
                'harga_pemeriksaan_lab' => 35000.00,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'satuan_lab_id' => $satuan->id,
                'kode_pemeriksaan' => 'LAB-003',
                'nama_pemeriksaan' => 'Kolesterol Total',
                'nilai_normal' => 200,
                'harga_pemeriksaan_lab' => 60000.00,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('jenis_pemeriksaan_lab')->insert($data);
    }
}
