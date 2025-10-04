<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ObatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('obat')->insert([
            ['nama_obat' => 'Paracetamol', 'jumlah' => 100, 'dosis' => 500.00, 'created_at' => now(), 'updated_at' => now()],
            ['nama_obat' => 'Amoxicillin', 'jumlah' => 75, 'dosis' => 250.00, 'created_at' => now(), 'updated_at' => now()],
            ['nama_obat' => 'Vitamin C', 'jumlah' => 200, 'dosis' => 1000.00, 'created_at' => now(), 'updated_at' => now()],
            ['nama_obat' => 'Ibuprofen', 'jumlah' => 150, 'dosis' => 200.50, 'created_at' => now(), 'updated_at' => now()],
            ['nama_obat' => 'Cefixime Syrup', 'jumlah' => 50, 'dosis' => 62.50, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
