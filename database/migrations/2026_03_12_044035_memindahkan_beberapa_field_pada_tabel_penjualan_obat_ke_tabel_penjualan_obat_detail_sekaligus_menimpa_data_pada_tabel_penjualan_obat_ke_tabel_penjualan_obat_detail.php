<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('penjualan_obat_detail')) {
            throw new \RuntimeException('Tabel penjualan_obat_detail tidak ditemukan.');
        }

        /*
        |--------------------------------------------------------------------------
        | 1. Tambah kolom yang dibutuhkan ke penjualan_obat_detail
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasColumn('penjualan_obat_detail', 'penjualan_obat_id')) {
            Schema::table('penjualan_obat_detail', function (Blueprint $table) {
                $table->unsignedBigInteger('penjualan_obat_id')->nullable()->after('id');
            });
        }

        if (!Schema::hasColumn('penjualan_obat_detail', 'obat_id')) {
            Schema::table('penjualan_obat_detail', function (Blueprint $table) {
                $table->unsignedBigInteger('obat_id')->nullable()->after('penjualan_obat_id');
            });
        }

        if (!Schema::hasColumn('penjualan_obat_detail', 'jumlah')) {
            Schema::table('penjualan_obat_detail', function (Blueprint $table) {
                $table->integer('jumlah')->default(1)->after('obat_id');
            });
        }

        if (!Schema::hasColumn('penjualan_obat_detail', 'harga_satuan')) {
            Schema::table('penjualan_obat_detail', function (Blueprint $table) {
                $table->decimal('harga_satuan', 15, 2)->nullable()->after('jumlah');
            });
        }

        if (!Schema::hasColumn('penjualan_obat_detail', 'sub_total')) {
            Schema::table('penjualan_obat_detail', function (Blueprint $table) {
                $table->decimal('sub_total', 15, 2)->nullable()->after('harga_satuan');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Backfill data lama dari penjualan_obat ke penjualan_obat_detail
        |--------------------------------------------------------------------------
        */
        $hasObatId             = Schema::hasColumn('penjualan_obat', 'obat_id');
        $hasJumlah             = Schema::hasColumn('penjualan_obat', 'jumlah');
        $hasSubTotal           = Schema::hasColumn('penjualan_obat', 'sub_total');
        $hasTotalTagihan       = Schema::hasColumn('penjualan_obat', 'total_tagihan');
        $hasTotalSetelahDiskon = Schema::hasColumn('penjualan_obat', 'total_setelah_diskon');
        $hasCreatedAt          = Schema::hasColumn('penjualan_obat', 'created_at');
        $hasUpdatedAt          = Schema::hasColumn('penjualan_obat', 'updated_at');

        if ($hasObatId) {
            $selects = ['id', 'obat_id'];

            if ($hasJumlah) {
                $selects[] = 'jumlah';
            }

            if ($hasSubTotal) {
                $selects[] = 'sub_total';
            }

            if ($hasTotalTagihan) {
                $selects[] = 'total_tagihan';
            }

            if ($hasTotalSetelahDiskon) {
                $selects[] = 'total_setelah_diskon';
            }

            if ($hasCreatedAt) {
                $selects[] = 'created_at';
            }

            if ($hasUpdatedAt) {
                $selects[] = 'updated_at';
            }

            DB::table('penjualan_obat')
                ->select($selects)
                ->orderBy('id')
                ->chunkById(200, function ($rows) {
                    $insertData = [];

                    foreach ($rows as $row) {
                        if (empty($row->obat_id)) {
                            continue;
                        }

                        $alreadyExists = DB::table('penjualan_obat_detail')
                            ->where('penjualan_obat_id', $row->id)
                            ->where('obat_id', $row->obat_id)
                            ->exists();

                        if ($alreadyExists) {
                            continue;
                        }

                        $jumlah = (property_exists($row, 'jumlah') && !is_null($row->jumlah))
                            ? (int) $row->jumlah
                            : 1;

                        if ($jumlah <= 0) {
                            $jumlah = 1;
                        }

                        $subTotal = 0;

                        if (property_exists($row, 'sub_total') && !is_null($row->sub_total)) {
                            $subTotal = (float) $row->sub_total;
                        } elseif (property_exists($row, 'total_tagihan') && !is_null($row->total_tagihan)) {
                            $subTotal = (float) $row->total_tagihan;
                        } elseif (property_exists($row, 'total_setelah_diskon') && !is_null($row->total_setelah_diskon)) {
                            $subTotal = (float) $row->total_setelah_diskon;
                        }

                        $hargaSatuan = round($subTotal / $jumlah, 2);

                        $insertData[] = [
                            'penjualan_obat_id' => $row->id,
                            'obat_id'           => $row->obat_id,
                            'jumlah'            => $jumlah,
                            'harga_satuan'      => $hargaSatuan,
                            'sub_total'         => $subTotal,
                            'created_at'        => property_exists($row, 'created_at') ? $row->created_at : null,
                            'updated_at'        => property_exists($row, 'updated_at') ? $row->updated_at : null,
                        ];
                    }

                    if (!empty($insertData)) {
                        DB::table('penjualan_obat_detail')->insert($insertData);
                    }
                }, 'id');
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Buat index baru untuk pasien_id
        |--------------------------------------------------------------------------
        | Penting karena FK pasien_id sebelumnya bisa jadi bergantung pada
        | index gabungan penjualan_obat_unique.
        */
        $this->createIndexIfNotExists(
            'penjualan_obat',
            'penjualan_obat_pasien_id_idx',
            ['pasien_id']
        );

        /*
        |--------------------------------------------------------------------------
        | 4. Drop FK obat_id jika ternyata ada
        |--------------------------------------------------------------------------
        */
        $this->dropForeignIfExists('penjualan_obat', 'obat_id');

        /*
        |--------------------------------------------------------------------------
        | 5. Drop index yang memakai obat_id
        |--------------------------------------------------------------------------
        */
        $this->dropIndexIfExists('penjualan_obat', 'penjualan_obat_obat_id');
        $this->dropIndexIfExists('penjualan_obat', 'penjualan_obat_unique');

        /*
        |--------------------------------------------------------------------------
        | 6. Baru drop kolom lama
        |--------------------------------------------------------------------------
        */
        $this->dropColumnIfExists('penjualan_obat', 'obat_id');
        $this->dropColumnIfExists('penjualan_obat', 'jumlah');
        $this->dropColumnIfExists('penjualan_obat', 'sub_total');
    }

    public function down(): void
    {
        if (!Schema::hasColumn('penjualan_obat', 'obat_id')) {
            DB::statement("ALTER TABLE `penjualan_obat` ADD COLUMN `obat_id` BIGINT UNSIGNED NULL AFTER `pasien_id`");
        }

        if (!Schema::hasColumn('penjualan_obat', 'jumlah')) {
            DB::statement("ALTER TABLE `penjualan_obat` ADD COLUMN `jumlah` INT NOT NULL DEFAULT 1 AFTER `kode_transaksi`");
        }

        if (!Schema::hasColumn('penjualan_obat', 'sub_total')) {
            DB::statement("ALTER TABLE `penjualan_obat` ADD COLUMN `sub_total` DECIMAL(15,2) NULL AFTER `kembalian`");
        }

        if (
            Schema::hasTable('penjualan_obat_detail') &&
            Schema::hasColumn('penjualan_obat_detail', 'penjualan_obat_id') &&
            Schema::hasColumn('penjualan_obat_detail', 'obat_id') &&
            Schema::hasColumn('penjualan_obat_detail', 'jumlah') &&
            Schema::hasColumn('penjualan_obat_detail', 'sub_total')
        ) {
            $details = DB::table('penjualan_obat_detail')
                ->orderBy('id')
                ->get();

            $updatedHeaderIds = [];

            foreach ($details as $detail) {
                if (in_array($detail->penjualan_obat_id, $updatedHeaderIds, true)) {
                    continue;
                }

                DB::table('penjualan_obat')
                    ->where('id', $detail->penjualan_obat_id)
                    ->update([
                        'obat_id'   => $detail->obat_id,
                        'jumlah'    => $detail->jumlah,
                        'sub_total' => $detail->sub_total,
                    ]);

                $updatedHeaderIds[] = $detail->penjualan_obat_id;
            }
        }

        $this->dropIndexIfExists('penjualan_obat', 'penjualan_obat_pasien_id_idx');

        $this->createIndexIfNotExists(
            'penjualan_obat',
            'penjualan_obat_obat_id',
            ['obat_id']
        );

        $this->createUniqueIndexIfNotExists(
            'penjualan_obat',
            'penjualan_obat_unique',
            ['pasien_id', 'obat_id', 'kode_transaksi']
        );
    }

    private function dropColumnIfExists(string $tableName, string $columnName): void
    {
        if (Schema::hasColumn($tableName, $columnName)) {
            DB::statement("ALTER TABLE `{$tableName}` DROP COLUMN `{$columnName}`");
        }
    }

    private function dropForeignIfExists(string $tableName, string $columnName): void
    {
        $databaseName = DB::getDatabaseName();

        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ", [$databaseName, $tableName, $columnName]);

        foreach ($foreignKeys as $foreignKey) {
            DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$foreignKey->CONSTRAINT_NAME}`");
        }
    }

    private function dropIndexIfExists(string $tableName, string $indexName): void
    {
        $databaseName = DB::getDatabaseName();

        $index = DB::select("
            SELECT 1
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND INDEX_NAME = ?
            LIMIT 1
        ", [$databaseName, $tableName, $indexName]);

        if (!empty($index)) {
            DB::statement("ALTER TABLE `{$tableName}` DROP INDEX `{$indexName}`");
        }
    }

    private function createIndexIfNotExists(string $tableName, string $indexName, array $columns): void
    {
        $databaseName = DB::getDatabaseName();

        $existing = DB::select("
            SELECT 1
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND INDEX_NAME = ?
            LIMIT 1
        ", [$databaseName, $tableName, $indexName]);

        if (!empty($existing)) {
            return;
        }

        $quotedColumns = collect($columns)
            ->map(fn($col) => "`{$col}`")
            ->implode(', ');

        DB::statement("ALTER TABLE `{$tableName}` ADD INDEX `{$indexName}` ({$quotedColumns})");
    }

    private function createUniqueIndexIfNotExists(string $tableName, string $indexName, array $columns): void
    {
        $databaseName = DB::getDatabaseName();

        $existing = DB::select("
            SELECT 1
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND INDEX_NAME = ?
            LIMIT 1
        ", [$databaseName, $tableName, $indexName]);

        if (!empty($existing)) {
            return;
        }

        $quotedColumns = collect($columns)
            ->map(fn($col) => "`{$col}`")
            ->implode(', ');

        DB::statement("ALTER TABLE `{$tableName}` ADD UNIQUE INDEX `{$indexName}` ({$quotedColumns})");
    }
};
