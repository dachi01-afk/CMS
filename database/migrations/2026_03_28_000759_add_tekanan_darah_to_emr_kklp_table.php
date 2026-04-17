<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('emr_kklp', function (Blueprint $table) {
            $table->string('tekanan_darah')->nullable()->after('kesadaran');
        });
    }

    public function down(): void
    {
        Schema::table('emr_kklp', function (Blueprint $table) {
            $table->dropColumn('tekanan_darah');
        });
    }
};