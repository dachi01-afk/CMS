<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SatuanLabSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['nama_satuan' => 'mg/dL', 'created_at' => now(), 'updated_at' => now()],
            ['nama_satuan' => 'g/dL', 'created_at' => now(), 'updated_at' => now()],
            ['nama_satuan' => 'mmol/L', 'created_at' => now(), 'updated_at' => now()],
            ['nama_satuan' => 'U/L', 'created_at' => now(), 'updated_at' => now()],
            ['nama_satuan' => '10^3/uL', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('satuan_lab')->insert($data);
    }
}
