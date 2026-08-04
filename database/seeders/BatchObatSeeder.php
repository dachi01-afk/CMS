<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BatchObatSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        // Obat 1 punya batch: 1,2
        DB::table('batch_obat')->updateOrInsert(
            ['id' => 1],
            [
                'obat_id' => 1,
                'nama_batch' => 'BATCH-00001-01',
                'tanggal_kadaluarsa_obat' => '2027-12-31',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('batch_obat')->updateOrInsert(
            ['id' => 2],
            [
                'obat_id' => 1,
                'nama_batch' => 'BATCH-00001-02',
                'tanggal_kadaluarsa_obat' => '2028-06-30',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        // Obat 2 punya batch: 3,4
        DB::table('batch_obat')->updateOrInsert(
            ['id' => 3],
            [
                'obat_id' => 2,
                'nama_batch' => 'BATCH-00002-01',
                'tanggal_kadaluarsa_obat' => '2027-11-30',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('batch_obat')->updateOrInsert(
            ['id' => 4],
            [
                'obat_id' => 2,
                'nama_batch' => 'BATCH-00002-02',
                'tanggal_kadaluarsa_obat' => '2028-08-31',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $this->command->info('✅ BatchObatSeeder OK (Batch 1..4).');
    }
}
