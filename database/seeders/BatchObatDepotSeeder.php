<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BatchObatDepotSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        /**
         * TARGET:
         * Obat 1 total 500 => Depot 1: 300, Depot 2: 200
         * Obat 2 total 1000 => Depot 1: 600, Depot 2: 400
         *
         * Split ke batch:
         * Obat 1:
         *  - batch 1: d1=180 d2=70
         *  - batch 2: d1=120 d2=130  (total d1=300 d2=200)
         *
         * Obat 2:
         *  - batch 3: d1=250 d2=150
         *  - batch 4: d1=350 d2=250  (total d1=600 d2=400)
         */

        $rows = [
            // batch 1 (obat 1)
            ['batch_obat_id' => 1, 'depot_id' => 1, 'stok_obat' => 180],
            ['batch_obat_id' => 1, 'depot_id' => 2, 'stok_obat' => 70],

            // batch 2 (obat 1)
            ['batch_obat_id' => 2, 'depot_id' => 1, 'stok_obat' => 120],
            ['batch_obat_id' => 2, 'depot_id' => 2, 'stok_obat' => 130],

            // batch 3 (obat 2)
            ['batch_obat_id' => 3, 'depot_id' => 1, 'stok_obat' => 250],
            ['batch_obat_id' => 3, 'depot_id' => 2, 'stok_obat' => 150],

            // batch 4 (obat 2)
            ['batch_obat_id' => 4, 'depot_id' => 1, 'stok_obat' => 350],
            ['batch_obat_id' => 4, 'depot_id' => 2, 'stok_obat' => 250],
        ];

        // A) Insert / update batch_obat_depot
        foreach ($rows as $r) {
            DB::table('batch_obat_depot')->updateOrInsert(
                [
                    'batch_obat_id' => $r['batch_obat_id'],
                    'depot_id'      => $r['depot_id'],
                ],
                [
                    'stok_obat'  => $r['stok_obat'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // B) Build depot_obat = SUM stok batch per depot per obat
        // depot_obat(depot_id, obat_id, stok_obat)
        $rekapDepotObat = DB::table('batch_obat_depot as bod')
            ->join('batch_obat as bo', 'bo.id', '=', 'bod.batch_obat_id')
            ->select('bod.depot_id', 'bo.obat_id', DB::raw('SUM(bod.stok_obat) as total_stok'))
            ->groupBy('bod.depot_id', 'bo.obat_id')
            ->get();

        foreach ($rekapDepotObat as $row) {
            DB::table('depot_obat')->updateOrInsert(
                [
                    'depot_id' => $row->depot_id,
                    'obat_id'  => $row->obat_id,
                ],
                [
                    'stok_obat'  => (int) $row->total_stok,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // C) Update obat.jumlah = SUM stok dari depot_obat (total stok global per obat)
        // (lebih sesuai definisi kamu: jumlah = total stok keseluruhan dari semua relasi)
        $rekapObat = DB::table('depot_obat')
            ->select('obat_id', DB::raw('SUM(stok_obat) as total_stok'))
            ->groupBy('obat_id')
            ->get();

        foreach ($rekapObat as $row) {
            DB::table('obat')->where('id', $row->obat_id)->update([
                'jumlah'     => (int) $row->total_stok,
                'updated_at' => $now,
            ]);
        }

        /**
         * NOTE:
         * ❌ Tidak update depot.jumlah_stok_depot di sini.
         * ✅ jumlah_stok_depot sebaiknya di-update FINAL oleh BatchBahanHabisPakaiDepotSeeder
         * karena harus include stok obat + stok bahan_habis_pakai.
         */

        $this->command->info('✅ BatchObatDepotSeeder OK (batch_obat_depot + depot_obat + obat.jumlah).');
    }
}
