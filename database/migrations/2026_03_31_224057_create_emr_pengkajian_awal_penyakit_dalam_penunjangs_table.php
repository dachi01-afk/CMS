<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emr_pengkajian_awal_penyakit_dalam_penunjang', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pengkajian_id')
                ->constrained('emr_pengkajian_awal_penyakit_dalam', 'id', 'pengkajian_pd_penunjang_pengkajian_id')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->enum('jenis_penunjang', ['Laboratorium', 'EKG', 'X-Ray', 'Lain-lain'])->nullable();
            $table->string('jenis_penunjang_lainnya')->nullable();
            $table->text('hasil_penunjang')->nullable();
            $table->dateTime('tanggal_penunjang')->nullable();
            $table->unsignedInteger('urutan')->default(1);

            $table->timestamps();

            $table->index(['pengkajian_id', 'jenis_penunjang'], 'idx_pengkajian_pd_penunjang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emr_pengkajian_awal_penyakit_dalam_penunjang');
    }
};