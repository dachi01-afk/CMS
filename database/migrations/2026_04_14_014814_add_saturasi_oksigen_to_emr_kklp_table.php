<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('emr_kklp', function (Blueprint $table) {
            if (!Schema::hasColumn('emr_kklp', 'saturasi_oksigen')) {
                $table->string('saturasi_oksigen', 255)->nullable()->after('imt');
            }
        });
    }

    public function down(): void
    {
        Schema::table('emr_kklp', function (Blueprint $table) {
            if (Schema::hasColumn('emr_kklp', 'saturasi_oksigen')) {
                $table->dropColumn('saturasi_oksigen');
            }
        });
    }
};