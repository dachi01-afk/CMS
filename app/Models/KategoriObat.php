<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriObat extends Model
{
    protected $table = 'kategori_obat';

    protected $guarded = [];

    public function obat()
    {
        return $this->hasMany(Obat::class);
    }
}
