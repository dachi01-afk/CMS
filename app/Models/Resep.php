<?php

namespace App\Models;

use App\Support\Audit\Auditable;
use Illuminate\Database\Eloquent\Model;

class Resep extends Model
{
    use Auditable;

    protected $table = 'resep';

    protected $guarded = [];

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class);
    }
    // public function apoteker()
    // {
    //     return $this->belongsTo(Apoteker::class);
    // }

    public function emr()
    {
        return $this->hasOne(Emr::class);
    }

    public function obat()
    {
        return $this->belongsToMany(Obat::class, 'resep_obat')
            ->withPivot('depot_id', 'jumlah', 'dosis', 'keterangan')
            ->withTimestamps();
    }

    public static function getDataResepObat($id)
    {
        $resep = self::with(['obat' => function ($obat) {
            $obat->select();
        }])->find($id);

        return $resep;
    }
}
