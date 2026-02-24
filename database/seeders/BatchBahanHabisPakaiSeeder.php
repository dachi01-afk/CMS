<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BatchBahanHabisPakaiSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // BHP 1 punya batch 1,2
        DB::table('batch_bahan_habis_pakai')->updateOrInsert(
            ['id' => 1],
            [
                'bahan_habis_pakai_id' => 1,
                'nama_batch' => 'BHPBATCH-00001-01',
                'tanggal_kadaluarsa_bahan_habis_pakai' => '2027-12-31',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('batch_bahan_habis_pakai')->updateOrInsert(
            ['id' => 2],
            [
                'bahan_habis_pakai_id' => 1,
                'nama_batch' => 'BHPBATCH-00001-02',
                'tanggal_kadaluarsa_bahan_habis_pakai' => '2028-06-30',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // BHP 2 punya batch 3,4
        DB::table('batch_bahan_habis_pakai')->updateOrInsert(
            ['id' => 3],
            [
                'bahan_habis_pakai_id' => 2,
                'nama_batch' => 'BHPBATCH-00002-01',
                'tanggal_kadaluarsa_bahan_habis_pakai' => '2027-11-30',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('batch_bahan_habis_pakai')->updateOrInsert(
            ['id' => 4],
            [
                'bahan_habis_pakai_id' => 2,
                'nama_batch' => 'BHPBATCH-00002-02',
                'tanggal_kadaluarsa_bahan_habis_pakai' => '2028-08-31',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->command->info('✅ BatchBahanHabisPakaiSeeder OK (Batch 1..4).');
    }
}
