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
        Schema::table('dental_examinations', function (Blueprint $table) {
            if (! Schema::hasColumn('dental_examinations', 'terapi')) {
                $table->text('terapi')->nullable()->after('lain_lain');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dental_examinations', function (Blueprint $table) {
            if (Schema::hasColumn('dental_examinations', 'terapi')) {
                $table->dropColumn('terapi');
            }
        });
    }
};