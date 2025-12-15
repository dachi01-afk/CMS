<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

use App\Models\Depot;
use App\Models\Obat;
use App\Models\DepotObat;

class DepotObatSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $depotIds = Depot::pluck('id');
        $obatIds  = Obat::pluck('id');

        if ($depotIds->isEmpty() || $obatIds->isEmpty()) {
            $this->command->warn('⚠️ depot / obat kosong, DepotObatSeeder dibatalkan');
            return;
        }

        foreach ($depotIds as $depotId) {
            $obatRandom = $obatIds->random(rand(3, min(10, $obatIds->count())));

            foreach ($obatRandom as $obatId) {
                DepotObat::updateOrCreate(
                    [
                        'depot_id' => $depotId,
                        'obat_id'  => $obatId,
                    ],
                    [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }
}
