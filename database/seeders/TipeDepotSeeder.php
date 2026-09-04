<?php

namespace Database\Seeders;

use App\Models\TipeDepot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TipeDepotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['nama_tipe_depot' => 'Apotek'],
            ['nama_tipe_depot' => 'Gudang'],
            ['nama_tipe_depot' => 'Opname'],
        ];

        TipeDepot::insert($data);
    }
}
