<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emr', function (Blueprint $table) {
            $table->decimal('tinggi_badan', 5, 2)->nullable()->after('suhu_tubuh');
            $table->decimal('berat_badan', 5, 2)->nullable()->after('tinggi_badan');
            $table->decimal('imt', 5, 2)->nullable()->after('berat_badan');
        });
    }

    public function down(): void
    {
        Schema::table('emr', function (Blueprint $table) {
            $table->dropColumn(['tinggi_badan', 'berat_badan', 'imt']);
        });
    }
};
