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
        Schema::table('emr', function (Blueprint $table) {
            if (!Schema::hasColumn('emr', 'no_rm')) {
                $table->string('no_rm')->nullable()->after('resep_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emr', function (Blueprint $table) {
            //
        });
    }
};
