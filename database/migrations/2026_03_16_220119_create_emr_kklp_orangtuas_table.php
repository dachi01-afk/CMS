<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emr_kklp_orangtua', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emr_kklp_id')
                ->constrained('emr_kklp')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->enum('hubungan', ['Ayah', 'Ibu']);
            $table->string('nama_lengkap')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('umur')->nullable();
            $table->text('alamat')->nullable();
            $table->string('telepon')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('agama')->nullable();
            $table->string('pendidikan_terakhir')->nullable();
            $table->string('suku_bangsa')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emr_kklp_orangtua');
    }
};