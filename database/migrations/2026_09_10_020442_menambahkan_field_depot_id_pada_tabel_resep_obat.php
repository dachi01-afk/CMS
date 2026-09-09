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
        Schema::table('resep_obat', function (Blueprint $table) {
            $table->foreignId('depot_id')->nullable()->after('obat_id')->constrained('depot', 'id', 'resep_obat_depot_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resep_obat', function (Blueprint $table) {
            $table->dropForeign('resep_obat_depot_id');
            $table->dropColumn('depot_id');
        });
    }
};
