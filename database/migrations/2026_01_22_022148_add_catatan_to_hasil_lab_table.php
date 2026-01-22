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
        Schema::table('hasil_lab', function (Blueprint $table) {
            // Tambah kolom catatan setelah nilai_hasil
            $table->text('catatan')->nullable()->after('nilai_hasil');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_lab', function (Blueprint $table) {
            $table->dropColumn('catatan');
        });
    }
};