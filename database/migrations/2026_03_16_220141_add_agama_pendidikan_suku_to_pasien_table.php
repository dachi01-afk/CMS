<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pasien', function (Blueprint $table) {
            if (!Schema::hasColumn('pasien', 'agama')) {
                $table->string('agama')->nullable()->after('pekerjaan');
            }

            if (!Schema::hasColumn('pasien', 'pendidikan_terakhir')) {
                $table->string('pendidikan_terakhir')->nullable()->after('agama');
            }

            if (!Schema::hasColumn('pasien', 'suku_bangsa')) {
                $table->string('suku_bangsa')->nullable()->after('pendidikan_terakhir');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pasien', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('pasien', 'agama')) {
                $columns[] = 'agama';
            }

            if (Schema::hasColumn('pasien', 'pendidikan_terakhir')) {
                $columns[] = 'pendidikan_terakhir';
            }

            if (Schema::hasColumn('pasien', 'suku_bangsa')) {
                $columns[] = 'suku_bangsa';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};