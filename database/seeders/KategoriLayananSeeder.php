<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriLayananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('kategori_layanan')->insert([
            // --- Kategori dasar yang sudah ada ---
            [
                'nama_kategori'       => 'Pemeriksaan',
                'deskripsi_kategori'  => 'Kategori layanan ini adalah kategori yang khusus untuk melakukan pemeriksaan pasien (anamnesis, pemeriksaan fisik, dan penegakan diagnosis).',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
            [
                'nama_kategori'       => 'Non Pemeriksaan',
                'deskripsi_kategori'  => 'Kategori layanan ini adalah kategori yang tidak berhubungan langsung dengan pemeriksaan medis pasien.',
                'status_kategori'     => 'Aktif',
                'created_at'          => now(),
                'updated_at'          => now(),
            ],
        ]);
    }
}
