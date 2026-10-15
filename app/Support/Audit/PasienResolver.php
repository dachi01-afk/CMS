<?php

namespace App\Support\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

/**
 * Mencari pasien yang berkaitan dengan sebuah data / route, supaya setiap
 * log (termasuk log "membuka data") bisa ditampilkan di Riwayat per Entitas.
 *
 * Untuk perubahan data, hal yang sama dikerjakan trigger MySQL
 * (AuditTriggerInstaller::pasienExpression).
 */
class PasienResolver
{
    /** Kolom relasi yang ditelusuri, beserta tabelnya. */
    protected const PARENTS = [
        'kunjungan_id' => 'kunjungan',
        'emr_id' => 'emr',
        'resep_id' => 'resep',
        'order_lab_id' => 'order_lab',
        'order_lab_detail_id' => 'order_lab_detail',
        'order_radiologi_id' => 'order_radiologi',
        'order_radiologi_detail_id' => 'order_radiologi_detail',
        'pembayaran_id' => 'pembayaran',
        'penjualan_obat_id' => 'penjualan_obat',
        'order_layanan_id' => 'order_layanan',
        'emr_kklp_id' => 'emr_kklp',
        'pengkajian_id' => 'emr_pengkajian_awal_penyakit_dalam',
    ];

    protected const MAX_DEPTH = 3;

    /** @var array<string, bool> */
    protected static array $tables = [];

    public static function forModel(Model $model): ?int
    {
        try {
            return static::forRow($model->getTable(), $model->getAttributes());
        } catch (Throwable) {
            return null;
        }
    }

    /** Cari pasien dari parameter route (mis. {noEMR}, {kunjunganId}, model terikat). */
    public static function forRoute(?Route $route): ?int
    {
        if (! $route) {
            return null;
        }

        try {
            $map = config('audit.route_parameter_tables', []);

            foreach ($route->parameters() as $name => $value) {
                if ($value instanceof Model) {
                    if ($id = static::forModel($value)) {
                        return $id;
                    }

                    continue;
                }

                $key = Str::lower(str_replace(['_', '-'], '', (string) $name));

                if (! isset($map[$key]) || $value === null || is_array($value)) {
                    continue;
                }

                [$table, $column] = $map[$key];

                if (! static::tableExists($table)) {
                    continue;
                }

                $row = DB::table($table)->where($column, $value)->first();

                if ($row && ($id = static::forRow($table, (array) $row))) {
                    return $id;
                }
            }
        } catch (Throwable) {
            return null;
        }

        return null;
    }

    public static function forRow(string $table, array $attributes, int $depth = 0): ?int
    {
        if ($table === 'pasien') {
            return isset($attributes['id']) ? (int) $attributes['id'] : null;
        }

        if (! empty($attributes['pasien_id'])) {
            return (int) $attributes['pasien_id'];
        }

        if (in_array($table, ['user', 'users'], true) && ! empty($attributes['id'])) {
            $id = DB::table('pasien')->where('user_id', $attributes['id'])->value('id');

            return $id ? (int) $id : null;
        }

        if ($depth >= self::MAX_DEPTH) {
            return null;
        }

        foreach (self::PARENTS as $column => $parent) {
            if (empty($attributes[$column]) || $parent === $table || ! static::tableExists($parent)) {
                continue;
            }

            $row = DB::table($parent)->where('id', $attributes[$column])->first();

            if ($row && ($id = static::forRow($parent, (array) $row, $depth + 1))) {
                return $id;
            }
        }

        return null;
    }

    protected static function tableExists(string $table): bool
    {
        return static::$tables[$table] ??= Schema::hasTable($table);
    }
}
