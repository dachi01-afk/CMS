<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1️⃣ Tambahkan kolom dokter_poli_id (nullable dulu)
        Schema::table('jadwal_dokter', function (Blueprint $table) {
            if (!Schema::hasColumn('jadwal_dokter', 'dokter_poli_id')) {
                $table->foreignId('dokter_poli_id')->nullable()->after('id');
            }
        });

        // 2️⃣ Backfill dari pasangan (dokter_id, poli_id)
        if (Schema::hasColumn('jadwal_dokter', 'dokter_id') && Schema::hasColumn('jadwal_dokter', 'poli_id')) {
            DB::statement("
                UPDATE jadwal_dokter jd
                JOIN dokter_poli dp
                  ON dp.dokter_id = jd.dokter_id
                 AND dp.poli_id   = jd.poli_id
                SET jd.dokter_poli_id = dp.id
                WHERE jd.dokter_poli_id IS NULL
            ");
        }

        // 3️⃣ Pasang index & FK
        Schema::table('jadwal_dokter', function (Blueprint $table) {
            $table->index(['dokter_poli_id', 'hari'], 'jadwal_dokter_dokter_poli_id');
            $table->foreign('dokter_poli_id', 'jadwal_dokter_dokter_poli_id')
                ->references('id')->on('dokter_poli')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
            
        // 4️⃣ (opsional) Unique slot
        try {
            Schema::table('jadwal_dokter', function (Blueprint $table) {
                $table->unique(['dokter_poli_id', 'hari', 'jam_awal', 'jam_selesai'], 'uniq_jd_slot_dokterpoli');
            });
        } catch (\Throwable $e) {
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
