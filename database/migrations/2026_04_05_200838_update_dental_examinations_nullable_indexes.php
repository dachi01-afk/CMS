<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dental_examinations', function (Blueprint $table) {
            // ✅ UBAH: Kolom DMF index jadi nullable
            $table->integer('d_index')->nullable()->default(0)->change();
            $table->integer('m_index')->nullable()->default(0)->change();
            $table->integer('f_index')->nullable()->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('dental_examinations', function (Blueprint $table) {
            $table->integer('d_index')->nullable(false)->change();
            $table->integer('m_index')->nullable(false)->change();
            $table->integer('f_index')->nullable(false)->change();
        });
    }
};