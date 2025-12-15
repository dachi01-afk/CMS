<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Faker\Factory as Faker;

use App\Models\Depot;
use App\Models\TipeDepot;

class DepotSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $now   = Carbon::now();

        $tipeDepotIds = TipeDepot::pluck('id');

        if ($tipeDepotIds->isEmpty()) {
            $this->command->warn('⚠️ tipe_depot kosong, DepotSeeder dibatalkan');
            return;
        }

        // hanya create kalau depot masih kosong
        if (Depot::count() > 0) {
            $this->command->info('ℹ️ depot sudah ada, skip seeder');
            return;
        }

        // jumlah depot bebas, contoh 5
        for ($i = 1; $i <= 5; $i++) {
            Depot::create([
                'tipe_depot_id'    => $tipeDepotIds->random(), // ✅ FK murni dari DB
                'nama_depot'       => 'Depot ' . $faker->unique()->word(),
                'jumlah_stok_depot' => $faker->numberBetween(0, 500),
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }
    }
}
