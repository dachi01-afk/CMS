<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * - pasien_id : pasien yang berkaitan dengan log, supaya halaman Riwayat
     *               per Entitas bisa menampilkan SEMUA aktivitas seorang pasien
     *               (kunjungan, rekam medis, resep, lab, pembayaran, dst).
     * - source    : dari mana aktivitas berasal: web, mobile, atau sistem.
     *
     * Trigger pencatatan dibuat otomatis setelah migrate selesai
     * (lihat App\Listeners\AuditDatabaseLifecycle).
     */
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_logs', 'pasien_id')) {
                $table->unsignedBigInteger('pasien_id')->nullable()->after('auditable_id');
                $table->index('pasien_id', 'audit_logs_pasien_id_index');
            }

            if (! Schema::hasColumn('audit_logs', 'source')) {
                $table->string('source', 20)->nullable()->after('http_method');
                $table->index('source', 'audit_logs_source_index');
            }
        });

        // Lengkapi log lama yang sudah ada sebelum kolom ini dibuat.
        DB::table('audit_logs')
            ->whereNull('pasien_id')
            ->where('auditable_type', 'App\\Models\\Pasien')
            ->update(['pasien_id' => DB::raw('auditable_id')]);

        if (Schema::hasTable('emr') && Schema::hasColumn('emr', 'pasien_id')) {
            DB::statement(
                'UPDATE audit_logs a JOIN emr e ON e.id = a.auditable_id
                 SET a.pasien_id = e.pasien_id
                 WHERE a.pasien_id IS NULL AND a.auditable_type = ?',
                ['App\\Models\\EMR']
            );
        }

        DB::table('audit_logs')->whereNull('source')->update([
            'source' => DB::raw("CASE WHEN url LIKE '%/api/%' THEN 'mobile' WHEN url IS NULL THEN 'sistem' ELSE 'web' END"),
        ]);
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'pasien_id')) {
                $table->dropIndex('audit_logs_pasien_id_index');
                $table->dropColumn('pasien_id');
            }

            if (Schema::hasColumn('audit_logs', 'source')) {
                $table->dropIndex('audit_logs_source_index');
                $table->dropColumn('source');
            }
        });
    }
};
