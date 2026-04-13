<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emr_pengkajian_awal_penyakit_dalam_riwayat', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengkajian_id')
                ->constrained('emr_pengkajian_awal_penyakit_dalam', 'id', 'pengkajian_pd_riwayat_pengkajian_id')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('riwayat_penyakit')->nullable();
            $table->string('tahun', 20)->nullable();
            $table->text('riwayat_pengobatan')->nullable();
            $table->unsignedInteger('urutan')->default(1);

            $table->timestamps();

            $table->index(['pengkajian_id', 'urutan'], 'idx_pengkajian_pd_riwayat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emr_pengkajian_awal_penyakit_dalam_riwayat');
    }
};