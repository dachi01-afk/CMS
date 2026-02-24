<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BatchBahanHabisPakaiDepotSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        /**
         * TARGET (contoh):
         * BHP 1 total 300 => depot 1: 200, depot 2: 100
         * BHP 2 total 700 => depot 1: 400, depot 2: 300
         *
         * Split ke batch:
         * BHP 1:
         *  - batch 1: d1=120 d2=30
         *  - batch 2: d1=80  d2=70
         *
         * BHP 2:
         *  - batch 3: d1=150 d2=100
         *  - batch 4: d1=250 d2=200
         */

        $rows = [
            // batch 1 (BHP 1)
            ['batch_bahan_habis_pakai_id' => 1, 'depot_id' => 1, 'stok_bahan_habis_pakai' => 120],
            ['batch_bahan_habis_pakai_id' => 1, 'depot_id' => 2, 'stok_bahan_habis_pakai' => 30],

            // batch 2 (BHP 1)
            ['batch_bahan_habis_pakai_id' => 2, 'depot_id' => 1, 'stok_bahan_habis_pakai' => 80],
            ['batch_bahan_habis_pakai_id' => 2, 'depot_id' => 2, 'stok_bahan_habis_pakai' => 70],

            // batch 3 (BHP 2)
            ['batch_bahan_habis_pakai_id' => 3, 'depot_id' => 1, 'stok_bahan_habis_pakai' => 150],
            ['batch_bahan_habis_pakai_id' => 3, 'depot_id' => 2, 'stok_bahan_habis_pakai' => 100],

            // batch 4 (BHP 2)
            ['batch_bahan_habis_pakai_id' => 4, 'depot_id' => 1, 'stok_bahan_habis_pakai' => 250],
            ['batch_bahan_habis_pakai_id' => 4, 'depot_id' => 2, 'stok_bahan_habis_pakai' => 200],
        ];

        // (Opsional) kalau mau seed ulang bersih, uncomment:
        // DB::table('batch_bahan_habis_pakai_depot')->truncate();

        // A) Insert / update batch_bahan_habis_pakai_depot
        foreach ($rows as $r) {
            DB::table('batch_bahan_habis_pakai_depot')->updateOrInsert(
                [
                    'batch_bahan_habis_pakai_id' => $r['batch_bahan_habis_pakai_id'],
                    'depot_id'                   => $r['depot_id'],
                ],
                [
                    'stok_bahan_habis_pakai' => $r['stok_bahan_habis_pakai'],
                    'created_at'             => $now,
                    'updated_at'             => $now,
                ]
            );
        }

        // B) Update bahan_habis_pakai.stok_barang = SUM semua stok batch (across depot) untuk item tsb
        $rekapBhp = DB::table('batch_bahan_habis_pakai_depot as bbd')
            ->join('batch_bahan_habis_pakai as bb', 'bb.id', '=', 'bbd.batch_bahan_habis_pakai_id')
            ->select('bb.bahan_habis_pakai_id', DB::raw('SUM(bbd.stok_bahan_habis_pakai) as total_stok'))
            ->groupBy('bb.bahan_habis_pakai_id')
            ->get();

        foreach ($rekapBhp as $row) {
            DB::table('bahan_habis_pakai')
                ->where('id', $row->bahan_habis_pakai_id)
                ->update([
                    'stok_barang' => (int) $row->total_stok,
                    'updated_at'  => $now,
                ]);
        }

        // C) FINAL: Update depot.jumlah_stok_depot = total obat + total bhp per depot

        // C1) total obat per depot (dari depot_obat)
        $obatPerDepot = DB::table('depot_obat')
            ->select('depot_id', DB::raw('SUM(stok_obat) as total_obat'))
            ->groupBy('depot_id')
            ->pluck('total_obat', 'depot_id');

        // C2) total bhp per depot (dari batch_bahan_habis_pakai_depot)
        $bhpPerDepot = DB::table('batch_bahan_habis_pakai_depot')
            ->select('depot_id', DB::raw('SUM(stok_bahan_habis_pakai) as total_bhp'))
            ->groupBy('depot_id')
            ->pluck('total_bhp', 'depot_id');

        // Update semua depot
        $depotIds = DB::table('depot')->pluck('id');

        foreach ($depotIds as $depotId) {
            $totalObat = (int) ($obatPerDepot[$depotId] ?? 0);
            $totalBhp  = (int) ($bhpPerDepot[$depotId] ?? 0);

            DB::table('depot')->where('id', $depotId)->update([
                'jumlah_stok_depot' => $totalObat + $totalBhp,
                'updated_at'        => $now,
            ]);
        }

        $this->command->info('✅ BatchBahanHabisPakaiDepotSeeder OK: batch_bhp_depot + update stok_barang + FINAL update jumlah_stok_depot (obat + bhp).');
    }
}
