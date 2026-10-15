<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel audit log bersifat append-only:
     * tidak ada kolom updated_at dan tidak boleh ada update/delete dari aplikasi.
     */
    public function up(): void
    {
        if (Schema::hasTable('audit_logs')) {
            return;
        }

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Pelaku aktivitas. Nullable karena aksi anonim (mis. login gagal
            // dengan email yang tidak terdaftar) tetap harus tercatat.
            // Sengaja TANPA foreign key agar histori log tidak ikut terhapus.
            $table->unsignedBigInteger('user_id')->nullable();

            // Snapshot identitas pelaku pada saat aksi terjadi, supaya log tetap
            // terbaca meski user diubah atau dihapus.
            $table->string('user_name')->nullable();
            $table->string('user_role', 50)->nullable();

            $table->string('action', 50);
            $table->string('module', 100);

            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();

            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            $table->text('description')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('url')->nullable();
            $table->string('http_method', 10)->nullable();

            // Hanya created_at: log tidak pernah diubah.
            $table->timestamp('created_at')->nullable();

            $table->index('user_id', 'audit_logs_user_id_index');
            $table->index('action', 'audit_logs_action_index');
            $table->index('module', 'audit_logs_module_index');
            $table->index(['auditable_type', 'auditable_id'], 'audit_logs_auditable_index');
            $table->index('created_at', 'audit_logs_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
