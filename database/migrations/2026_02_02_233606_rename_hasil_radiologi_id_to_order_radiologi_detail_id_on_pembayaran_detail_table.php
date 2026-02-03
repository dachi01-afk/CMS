<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pembayaran_detail')) {
            return;
        }

        if (Schema::hasColumn('pembayaran_detail', 'hasil_radiologi_id')) {

            // 🔥 Drop FK lama (nama FK bisa beda-beda)
            $db = DB::getDatabaseName();

            $fks = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = ?
                  AND TABLE_NAME = 'pembayaran_detail'
                  AND COLUMN_NAME = 'hasil_radiologi_id'
                  AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$db]);

            foreach ($fks as $fk) {
                DB::statement("
                    ALTER TABLE pembayaran_detail
                    DROP FOREIGN KEY {$fk->CONSTRAINT_NAME}
                ");
            }

            // 🔁 Rename kolom
            Schema::table('pembayaran_detail', function (Blueprint $table) {
                $table->renameColumn(
                    'hasil_radiologi_id',
                    'order_radiologi_detail_id'
                );
            });

            // 🔗 Pasang FK baru
            Schema::table('pembayaran_detail', function (Blueprint $table) {
                $table->foreign(
                    'order_radiologi_detail_id',
                    'pembayaran_detail_order_radiologi_detail_fk'
                )
                ->references('id')
                ->on('order_radiologi_detail')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('pembayaran_detail')) {
            return;
        }

        if (Schema::hasColumn('pembayaran_detail', 'order_radiologi_detail_id')) {

            // drop FK baru
            try {
                Schema::table('pembayaran_detail', function (Blueprint $table) {
                    $table->dropForeign('pembayaran_detail_order_radiologi_detail_fk');
                });
            } catch (\Throwable $e) {}

            // rename balik
            Schema::table('pembayaran_detail', function (Blueprint $table) {
                $table->renameColumn(
                    'order_radiologi_detail_id',
                    'hasil_radiologi_id'
                );
            });
        }
    }
};
