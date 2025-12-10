<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Helper: drop semua foreign key yg nempel ke 1 kolom
     */
    private function dropForeignKeysForColumn(string $table, string $column): void
    {
        $database = DB::getDatabaseName();

        $constraints = DB::select(
            "SELECT CONSTRAINT_NAME 
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL",
            [$database, $table, $column]
        );

        foreach ($constraints as $constraint) {
            $name = $constraint->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE `$table` DROP FOREIGN KEY `$name`");
        }
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ===== Hapus kolom poli_id =====
        if (Schema::hasColumn('perawat', 'poli_id')) {

            // 1) Drop semua FK yg pakai poli_id
            $this->dropForeignKeysForColumn('perawat', 'poli_id');

            // 2) Drop kolom (index yg nempel akan ikut hilang)
            Schema::table('perawat', function (Blueprint $table) {
                $table->dropColumn('poli_id');
            });
        }

        // ===== Hapus kolom dokter_id =====
        if (Schema::hasColumn('perawat', 'dokter_id')) {

            // 1) Drop semua FK yg pakai dokter_id
            $this->dropForeignKeysForColumn('perawat', 'dokter_id');

            // 2) Drop kolom
            Schema::table('perawat', function (Blueprint $table) {
                $table->dropColumn('dokter_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perawat', function (Blueprint $table) {
            // Balikkan kolom poli_id (tanpa FK, cukup index)
            if (!Schema::hasColumn('perawat', 'poli_id')) {
                $table->unsignedBigInteger('poli_id')->nullable()->after('user_id');
                $table->index('poli_id', 'perawat_poli_id');
            }

            // Balikkan kolom dokter_id (tanpa FK, cukup index)
            if (!Schema::hasColumn('perawat', 'dokter_id')) {
                $table->unsignedBigInteger('dokter_id')->nullable()->after('poli_id');
                $table->index('dokter_id', 'perawat_dokter_id');
            }
        });
    }
};
