<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureRequiredStructure();

        $orphanPenjualanObat = DB::table('penjualan_obat_detail as d')
            ->leftJoin('penjualan_obat as p', 'p.id', '=', 'd.penjualan_obat_id')
            ->whereNotNull('d.penjualan_obat_id')
            ->whereNull('p.id')
            ->count();

        if ($orphanPenjualanObat > 0) {
            throw new RuntimeException(
                "Gagal menambahkan FK penjualan_obat_id. " .
                "Ditemukan {$orphanPenjualanObat} data detail yang tidak punya header penjualan_obat."
            );
        }

        $orphanObat = DB::table('penjualan_obat_detail as d')
            ->leftJoin('obat as o', 'o.id', '=', 'd.obat_id')
            ->whereNotNull('d.obat_id')
            ->whereNull('o.id')
            ->count();

        if ($orphanObat > 0) {
            throw new RuntimeException(
                "Gagal menambahkan FK obat_id. " .
                "Ditemukan {$orphanObat} data detail yang tidak punya master obat."
            );
        }

        $this->createIndexIfNotExists(
            'penjualan_obat_detail',
            'pod_penjualan_obat_id_idx',
            ['penjualan_obat_id']
        );

        $this->createIndexIfNotExists(
            'penjualan_obat_detail',
            'pod_obat_id_idx',
            ['obat_id']
        );

        if (!$this->foreignKeyExists('penjualan_obat_detail', 'pod_penjualan_obat_id_fk')) {
            Schema::table('penjualan_obat_detail', function (Blueprint $table) {
                $table->foreign('penjualan_obat_id', 'pod_penjualan_obat_id_fk')
                    ->references('id')
                    ->on('penjualan_obat')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
            });
        }

        if (!$this->foreignKeyExists('penjualan_obat_detail', 'pod_obat_id_fk')) {
            Schema::table('penjualan_obat_detail', function (Blueprint $table) {
                $table->foreign('obat_id', 'pod_obat_id_fk')
                    ->references('id')
                    ->on('obat')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');
            });
        }
    }

    public function down(): void
    {
        $this->dropForeignIfExists('penjualan_obat_detail', 'pod_penjualan_obat_id_fk');
        $this->dropForeignIfExists('penjualan_obat_detail', 'pod_obat_id_fk');

        $this->dropIndexIfExists('penjualan_obat_detail', 'pod_penjualan_obat_id_idx');
        $this->dropIndexIfExists('penjualan_obat_detail', 'pod_obat_id_idx');
    }

    private function ensureRequiredStructure(): void
    {
        if (!Schema::hasTable('penjualan_obat_detail')) {
            throw new RuntimeException('Tabel penjualan_obat_detail tidak ditemukan.');
        }

        if (!Schema::hasTable('penjualan_obat')) {
            throw new RuntimeException('Tabel penjualan_obat tidak ditemukan.');
        }

        if (!Schema::hasTable('obat')) {
            throw new RuntimeException('Tabel obat tidak ditemukan.');
        }

        if (!Schema::hasColumn('penjualan_obat_detail', 'penjualan_obat_id')) {
            throw new RuntimeException('Kolom penjualan_obat_id di penjualan_obat_detail tidak ditemukan.');
        }

        if (!Schema::hasColumn('penjualan_obat_detail', 'obat_id')) {
            throw new RuntimeException('Kolom obat_id di penjualan_obat_detail tidak ditemukan.');
        }
    }

    private function foreignKeyExists(string $tableName, string $constraintName): bool
    {
        $databaseName = DB::getDatabaseName();

        $result = DB::select("
            SELECT 1
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND CONSTRAINT_NAME = ?
              AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            LIMIT 1
        ", [$databaseName, $tableName, $constraintName]);

        return !empty($result);
    }

    private function dropForeignIfExists(string $tableName, string $constraintName): void
    {
        if ($this->foreignKeyExists($tableName, $constraintName)) {
            DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$constraintName}`");
        }
    }

    private function createIndexIfNotExists(string $tableName, string $indexName, array $columns): void
    {
        $databaseName = DB::getDatabaseName();

        $existing = DB::select("
            SELECT 1
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND INDEX_NAME = ?
            LIMIT 1
        ", [$databaseName, $tableName, $indexName]);

        if (!empty($existing)) {
            return;
        }

        $quotedColumns = collect($columns)
            ->map(fn ($col) => "`{$col}`")
            ->implode(', ');

        DB::statement("ALTER TABLE `{$tableName}` ADD INDEX `{$indexName}` ({$quotedColumns})");
    }

    private function dropIndexIfExists(string $tableName, string $indexName): void
    {
        $databaseName = DB::getDatabaseName();

        $existing = DB::select("
            SELECT 1
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND INDEX_NAME = ?
            LIMIT 1
        ", [$databaseName, $tableName, $indexName]);

        if (!empty($existing)) {
            DB::statement("ALTER TABLE `{$tableName}` DROP INDEX `{$indexName}`");
        }
    }
};