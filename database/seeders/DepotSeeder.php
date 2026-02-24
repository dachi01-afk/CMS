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

        // ✅ Pastikan Depot ID 1 & 2 selalu ada (dibutuhkan untuk relasi stok)
        Depot::updateOrCreate(
            ['id' => 1],
            [
                'tipe_depot_id'     => $tipeDepotIds->random(),
                'nama_depot'        => 'Depot 1',
                'jumlah_stok_depot' => 0,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]
        );

        Depot::updateOrCreate(
            ['id' => 2],
            [
                'tipe_depot_id'     => $tipeDepotIds->random(),
                'nama_depot'        => 'Depot 2',
                'jumlah_stok_depot' => 0,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]
        );

        // ✅ Tambahan depot lain kalau kamu masih mau (misal sampai 5)
        // Mulai dari 3 supaya tidak ganggu depot 1 & 2
        for ($i = 3; $i <= 5; $i++) {
            Depot::updateOrCreate(
                ['id' => $i],
                [
                    'tipe_depot_id'     => $tipeDepotIds->random(),
                    'nama_depot'        => 'Depot ' . $faker->unique()->word(),
                    'jumlah_stok_depot' => 0,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]
            );
        }

        $this->command->info('✅ DepotSeeder OK (Depot 1 & 2 dipastikan ada, plus depot tambahan).');
    }
}
