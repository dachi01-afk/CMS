<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisPemeriksaanRadiologiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'kode_pemeriksaan' => 'RAD-001',
                'nama_pemeriksaan' => 'Rontgen Thorax (Dada) PA',
                'harga_pemeriksaan_radiologi' => 150000.00, // Menyesuaikan field di tabel
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_pemeriksaan' => 'RAD-002',
                'nama_pemeriksaan' => 'USG Abdomen (Perut) Upper-Lower',
                'harga_pemeriksaan_radiologi' => 350000.00,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_pemeriksaan' => 'RAD-003',
                'nama_pemeriksaan' => 'Rontgen Ekstremitas (Tangan/Kaki)',
                'harga_pemeriksaan_radiologi' => 125000.00,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode_pemeriksaan' => 'RAD-004',
                'nama_pemeriksaan' => 'CT-Scan Kepala Non-Kontras',
                'harga_pemeriksaan_radiologi' => 1200000.00,
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('jenis_pemeriksaan_radiologi')->insert($data);
    }
}
