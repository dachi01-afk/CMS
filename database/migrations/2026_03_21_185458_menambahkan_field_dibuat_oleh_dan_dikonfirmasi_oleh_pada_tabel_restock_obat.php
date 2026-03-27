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
        Schema::table('restock_obat', function (Blueprint $table) {
            $table->unsignedBigInteger('dibuat_oleh')->nullable()->after('status_transaksi');
            $table->unsignedBigInteger('dikonfirmasi_oleh')->nullable()->after('dibuat_oleh');
            $table->timestamp('dikonfirmasi_jam')->nullable()->after('dikonfirmasi_oleh');
            $table->dropColumn('status_transaksi');
            $table->enum('status_restock', ['Pending', 'Succeed', 'Canceled'])->after('dikonfirmasi_jam');
            $table->date('tanggal_terima')->nullable()->change();

            $table->foreign('dibuat_oleh')->references('id')->on('user')->nullOnDelete();
            $table->foreign('dikonfirmasi_oleh')->references('id')->on('user')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restock_obat', function (Blueprint $table) {
            $table->dropForeign(['dibuat_oleh']);
            $table->dropForeign(['dikonfirmasi_oleh']);
        });
    }
};
