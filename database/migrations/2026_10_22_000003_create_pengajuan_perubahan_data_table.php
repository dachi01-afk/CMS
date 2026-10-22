<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pengajuan_perubahan_data')) {
            return;
        }

        Schema::create('pengajuan_perubahan_data', function (Blueprint $table) {
            $table->id();

            // 'layanan' (diajukan Admin) atau 'obat' (diajukan Farmasi).
            $table->string('jenis_data', 20);

            // 'edit' atau 'hapus'.
            $table->string('aksi', 20);

            $table->unsignedBigInteger('target_id');
            $table->string('target_label');

            // Snapshot data sebelum pengajuan dan data yang diajukan.
            // Disimpan sebagai JSON agar Verifikator bisa membandingkan
            // nilai lama dan nilai baru tanpa mengubah data aslinya.
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();

            // 'menunggu', 'disetujui', atau 'ditolak'.
            $table->string('status', 20)->default('menunggu');

            $table->foreignId('diajukan_oleh')->nullable()
                ->constrained('user', 'id', 'pengajuan_diajukan_oleh')
                ->nullOnDelete()->cascadeOnUpdate();

            $table->foreignId('diverifikasi_oleh')->nullable()
                ->constrained('user', 'id', 'pengajuan_diverifikasi_oleh')
                ->nullOnDelete()->cascadeOnUpdate();

            $table->timestamp('diverifikasi_pada')->nullable();
            $table->text('catatan_verifikator')->nullable();

            $table->timestamps();

            $table->index(['status', 'jenis_data']);
            $table->index(['jenis_data', 'target_id', 'status'], 'pengajuan_target_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_perubahan_data');
    }
};
