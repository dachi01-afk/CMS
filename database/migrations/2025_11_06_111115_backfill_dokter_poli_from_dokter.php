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
        // 1) Buat tabel pivot kalau belum ada
        if (!Schema::hasTable('dokter_poli')) {
            Schema::create('dokter_poli', function (Blueprint $table) {
                $table->id();
                $table->foreignId('dokter_id')->constrained('dokter')->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreignId('poli_id')->constrained('poli')->cascadeOnDelete()->cascadeOnUpdate();
                $table->timestamps();

                $table->unique(['dokter_id', 'poli_id']); // cegah duplikat
            });
        } else {
            // pastikan unique pair ada
            try {
                Schema::table('dokter_poli', function (Blueprint $table) {
                    $table->unique(['dokter_id', 'poli_id']);
                });
            } catch (\Throwable $e) {
            }
        }

        // 2) Backfill dari kolom lama dokter.poli_id
        if (Schema::hasColumn('dokter', 'poli_id')) {
            $driver = DB::getDriverName();

            if ($driver === 'mysql') {
                // MySQL/MariaDB: aman dari duplikat via INSERT IGNORE
                DB::statement("
                    INSERT IGNORE INTO dokter_poli (dokter_id, poli_id, created_at, updated_at)
                    SELECT d.id, d.poli_id, NOW(), NOW()
                    FROM dokter d
                    WHERE d.poli_id IS NOT NULL
                ");
            } else {
                // PostgreSQL: pakai ON CONFLICT DO NOTHING
                DB::statement("
                    INSERT INTO dokter_poli (dokter_id, poli_id, created_at, updated_at)
                    SELECT d.id, d.poli_id, NOW(), NOW()
                    FROM dokter d
                    WHERE d.poli_id IS NOT NULL
                    ON CONFLICT (dokter_id, poli_id) DO NOTHING
                ");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokter', function (Blueprint $table) {
            //
        });
    }
};
