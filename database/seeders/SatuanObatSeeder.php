<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatuanObatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $satuan = [
            'Tablet',
            'Kapsul',
            'Sirup',
            'Tetes',
            'Salep',
            'Krim',
            'Injeksi',
            'Botol',
            'Ampul',
            'Box',
            'Sachet',
            'Strip',
            'Vial',
            'Gram',
            'Milligram',
            'Mililiter',
            'Unit',
            'Suppositoria',
            'Tube',
            'Pcs'
        ];

        foreach ($satuan as $nama_satuan) {
            DB::table('satuan_obat')->insert([
                'nama_satuan' => $nama_satuan,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
