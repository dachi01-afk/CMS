<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Services\DataTable;

class MutasiStokObat extends Model
{
    protected $table = 'mutasi_stok_obat';

    protected $guarded = [];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function farmasi()
    {
        return $this->belongsTo(Farmasi::class);
    }

    public function mutasiStokObatDetail()
    {
        return $this->hasMany(MutasiStokObatDetail::class);
    }


    public static function getEnumValues($column)
    {
        $instance = new static;
        $table = $instance->getTable();

        // Hapus DB::raw(), langsung masukkan string query-nya
        $results = DB::select("SHOW COLUMNS FROM {$table} WHERE Field = '{$column}'");

        // Cek apakah kolom ditemukan
        if (empty($results)) {
            return [];
        }

        $type = $results[0]->Type; // Mengambil data tipe seperti: enum('restock','return')

        preg_match('/^enum\((.*)\)$/', $type, $matches);

        $enum = [];
        if (isset($matches[1])) {
            foreach (explode(',', $matches[1]) as $value) {
                $v = trim($value, "'");
                $enum[] = [
                    'value' => $v,
                    'label' => ucfirst($v) // Biar rapi di tampilan (contoh: 'return' jadi 'Return')
                ];
            }
        }

        return $enum;
    }

    public function scopeGetData($query)
    {
        return $query;
    }
}
