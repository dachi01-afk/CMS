<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('hasil_lab', function (Blueprint $table) {
            $table->unsignedBigInteger('dokter_id')->nullable()->after('perawat_id');
            // optional FK
            // $table->foreign('dokter_id')->references('id')->on('dokter')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('hasil_lab', function (Blueprint $table) {
            // optional drop FK dulu kalau pakai foreign
            $table->dropColumn('dokter_id');
        });
    }
};
