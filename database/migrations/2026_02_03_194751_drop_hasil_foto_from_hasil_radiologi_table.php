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
        Schema::table('hasil_radiologi', function (Blueprint $table) {
            $table->dropColumn('hasil_foto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasil_radiologi', function (Blueprint $table) {
            $table->string('hasil_foto', 255);
        });
    }
};
