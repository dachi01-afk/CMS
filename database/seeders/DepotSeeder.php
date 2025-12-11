<?php

namespace Database\Seeders;

use App\Models\Depot;
use App\Models\TipeDepot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dataTipeDepot = TipeDepot::get();

        foreach ($dataTipeDepot as $tipeDepot) {
            Depot::create([
                'tipe_depot_id' => $tipeDepot->id,
                'nama_depot' => ''
            ]);
        }
    }
}
