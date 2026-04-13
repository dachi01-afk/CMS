<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emr_kklp', function (Blueprint $table) {
            if (!Schema::hasColumn('emr_kklp', 'tekanan_darah')) {
                $table->string('tekanan_darah')->nullable()->after('kesadaran');
            }
            if (!Schema::hasColumn('emr_kklp', 'nadi')) {
                $table->string('nadi')->nullable()->after('tekanan_darah');
            }
            if (!Schema::hasColumn('emr_kklp', 'respirasi')) {
                $table->string('respirasi')->nullable()->after('nadi');
            }
            if (!Schema::hasColumn('emr_kklp', 'suhu')) {
                $table->string('suhu')->nullable()->after('respirasi');
            }
            if (!Schema::hasColumn('emr_kklp', 'tinggi_badan')) {
                $table->string('tinggi_badan')->nullable()->after('suhu');
            }
            if (!Schema::hasColumn('emr_kklp', 'berat_badan')) {
                $table->string('berat_badan')->nullable()->after('tinggi_badan');
            }
            if (!Schema::hasColumn('emr_kklp', 'imt')) {
                $table->string('imt')->nullable()->after('berat_badan');
            }
            if (!Schema::hasColumn('emr_kklp', 'lingkar_pinggang')) {
                $table->string('lingkar_pinggang')->nullable()->after('imt');
            }
            if (!Schema::hasColumn('emr_kklp', 'lingkar_panggul')) {
                $table->string('lingkar_panggul')->nullable()->after('lingkar_pinggang');
            }
            if (!Schema::hasColumn('emr_kklp', 'lingkar_lengan_atas')) {
                $table->string('lingkar_lengan_atas')->nullable()->after('lingkar_panggul');
            }
            if (!Schema::hasColumn('emr_kklp', 'status_gizi')) {
                $table->string('status_gizi')->nullable()->after('lingkar_lengan_atas');
            }
            if (!Schema::hasColumn('emr_kklp', 'waist_hip_ratio')) {
                $table->string('waist_hip_ratio')->nullable()->after('status_gizi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('emr_kklp', function (Blueprint $table) {
            $drop = [];

            foreach ([
                'tekanan_darah',
                'nadi',
                'respirasi',
                'suhu',
                'tinggi_badan',
                'berat_badan',
                'imt',
                'lingkar_pinggang',
                'lingkar_panggul',
                'lingkar_lengan_atas',
                'status_gizi',
                'waist_hip_ratio',
            ] as $col) {
                if (Schema::hasColumn('emr_kklp', $col)) {
                    $drop[] = $col;
                }
            }

            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};