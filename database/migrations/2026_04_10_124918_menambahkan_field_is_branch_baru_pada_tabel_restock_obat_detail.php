<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use phpDocumentor\Reflection\Types\Nullable;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('restock_obat_detail', function (Blueprint $table) {
            $table->boolean('is_batch_baru')->nullable()->after('batch_obat_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restock_obat_detail', function (Blueprint $table) {
            //
        });
    }
};
