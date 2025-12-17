<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandFarmasi extends Model
{
    protected $table = 'brand_farmasi';

    protected $guarded = [];

    public function obat()
    {
        return $this->hasMany(Obat::class);
    }

    public function bhp()
    {
        return $this->hasMany(BahanHabisPakai::class);
    }
}
