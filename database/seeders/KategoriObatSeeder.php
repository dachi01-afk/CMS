<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriObatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategori = [
            'Antibiotik',
            'Analgesik',
            'Antipiretik',
            'Antiinflamasi',
            'Antihistamin',
            'Vitamin',
            'Suplemen',
            'Obat Batuk',
            'Obat Flu',
            'Obat Maag',
            'Obat Hipertensi',
            'Obat Diabetes',
            'Obat Jantung',
            'Obat Kulit',
            'Obat Mata',
            'Obat Telinga',
            'Obat Gigi',
            'Obat Pencernaan',
            'Obat Pereda Nyeri',
            'Obat Herbal'
        ];

        foreach ($kategori as $nama_kategori) {
            DB::table('kategori_obat')->insert([
                'nama_kategori' => $nama_kategori,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
