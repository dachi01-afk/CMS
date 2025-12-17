<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatuanObat extends Model
{
    protected $table = 'satuan_obat';

    protected $guarded = [];

    public function obat()
    {
        return $this->hasMany(Obat::class);
    }

    public function bhp()
    {
        return $this->hasMany(BahanHabisPakai::class, 'satuan_id');
    }
}
