<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pasien', function (Blueprint $table) {
            $table->string('nama_pasien')->nullable()->change();
            $table->text('alamat')->nullable()->change();
            $table->date('tanggal_lahir')->nullable()->change();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pasien', function (Blueprint $table) {
            $table->string('nama_pasien')->nullable(false)->change();
            $table->text('alamat')->nullable(false)->change();
            $table->date('tanggal_lahir')->nullable(false)->change();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable(false)->change();
        });
    }
};