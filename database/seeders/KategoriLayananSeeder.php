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
            [
                'nama_kategori' => 'Pemeriksaan',
                'deskripsi_kategori' => 'Kategori Layanan Ini Adalah Data Kategori Yang Khusus Untuk Melakukan Pemeriksaan',
                'status_kategori' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_kategori' => 'Non Pemeriksaan',
                'deskripsi_kategori' => 'Kategori Layanan Ini Adalah Data Kategori Yang Tidak Melakukan Pemeriksaan',
                'status_kategori' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
