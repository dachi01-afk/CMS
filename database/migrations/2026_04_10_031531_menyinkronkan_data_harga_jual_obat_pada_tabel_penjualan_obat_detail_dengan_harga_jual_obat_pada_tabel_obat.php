<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->validateStructure();

        $detailWithoutMasterObat = DB::table('penjualan_obat_detail as d')
            ->leftJoin('obat as o', 'o.id', '=', 'd.obat_id')
            ->whereNotNull('d.obat_id')
            ->whereNull('o.id')
            ->count();

        if ($detailWithoutMasterObat > 0) {
            throw new RuntimeException(
                "Gagal sinkron harga. Ada {$detailWithoutMasterObat} data penjualan_obat_detail yang obat_id-nya tidak ditemukan di tabel obat."
            );
        }

        $detailWithoutHargaJual = DB::table('penjualan_obat_detail as d')
            ->join('obat as o', 'o.id', '=', 'd.obat_id')
            ->whereNull('o.harga_jual_obat')
            ->count();

        if ($detailWithoutHargaJual > 0) {
            throw new RuntimeException(
                "Gagal sinkron harga. Ada {$detailWithoutHargaJual} data obat yang harga_jual_obat-nya masih NULL."
            );
        }

        DB::beginTransaction();

        try {
            /*
            |--------------------------------------------------------------------------
            | 1. Sinkron harga detail dari tabel obat
            |--------------------------------------------------------------------------
            | harga_satuan = obat.harga_jual_obat
            | sub_total    = jumlah * obat.harga_jual_obat
            */
            DB::statement("
                UPDATE penjualan_obat_detail d
                INNER JOIN obat o ON o.id = d.obat_id
                SET
                    d.harga_satuan = o.harga_jual_obat,
                    d.sub_total = ROUND(COALESCE(d.jumlah, 0) * o.harga_jual_obat, 2)
                WHERE d.obat_id IS NOT NULL
            ");

            /*
            |--------------------------------------------------------------------------
            | 2. Recalculate total_tagihan pada header penjualan_obat
            |--------------------------------------------------------------------------
            */
            DB::statement("
                UPDATE penjualan_obat p
                INNER JOIN (
                    SELECT
                        penjualan_obat_id,
                        ROUND(SUM(COALESCE(sub_total, 0)), 2) AS total_tagihan_baru
                    FROM penjualan_obat_detail
                    GROUP BY penjualan_obat_id
                ) x ON x.penjualan_obat_id = p.id
                SET p.total_tagihan = x.total_tagihan_baru
            ");

            /*
            |--------------------------------------------------------------------------
            | 3. Recalculate total_setelah_diskon bila kolomnya tersedia
            |--------------------------------------------------------------------------
            | Asumsi diskon_tipe:
            | - persen / percentage / percent
            | - nominal / rupiah / fixed
            | Bila tipe di project kamu berbeda, tinggal sesuaikan daftar nilainya.
            */
            if (
                Schema::hasColumn('penjualan_obat', 'diskon_tipe') &&
                Schema::hasColumn('penjualan_obat', 'diskon_nilai') &&
                Schema::hasColumn('penjualan_obat', 'total_setelah_diskon')
            ) {
                DB::statement("
                    UPDATE penjualan_obat
                    SET total_setelah_diskon = CASE
                        WHEN diskon_tipe IS NULL OR diskon_nilai IS NULL THEN total_tagihan
                        WHEN LOWER(diskon_tipe) IN ('persen', 'percentage', 'percent') THEN
                            GREATEST(total_tagihan - ROUND((total_tagihan * diskon_nilai) / 100, 2), 0)
                        WHEN LOWER(diskon_tipe) IN ('nominal', 'rupiah', 'fixed') THEN
                            GREATEST(total_tagihan - diskon_nilai, 0)
                        ELSE total_tagihan
                    END
                ");
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function down(): void
    {
        /*
        |----------------------------------------------------------------------
        | Sengaja dikosongkan
        |----------------------------------------------------------------------
        | Ini migration sinkronisasi / perbaikan data pada project live.
        | Rollback otomatis tidak aman karena nilai harga lama tidak disimpan.
        */
    }

    private function validateStructure(): void
    {
        $requiredTables = ['penjualan_obat', 'penjualan_obat_detail', 'obat'];

        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                throw new RuntimeException("Tabel {$table} tidak ditemukan.");
            }
        }

        $requiredDetailColumns = ['penjualan_obat_id', 'obat_id', 'jumlah', 'harga_satuan', 'sub_total'];
        foreach ($requiredDetailColumns as $column) {
            if (!Schema::hasColumn('penjualan_obat_detail', $column)) {
                throw new RuntimeException("Kolom penjualan_obat_detail.{$column} tidak ditemukan.");
            }
        }

        if (!Schema::hasColumn('obat', 'harga_jual_obat')) {
            throw new RuntimeException('Kolom obat.harga_jual_obat tidak ditemukan.');
        }

        if (!Schema::hasColumn('penjualan_obat', 'total_tagihan')) {
            throw new RuntimeException('Kolom penjualan_obat.total_tagihan tidak ditemukan.');
        }
    }
};
