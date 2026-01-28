<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPemeriksaanRadiologi extends Model
{
    protected $table = 'jenis_pemeriksaan_radiologi';

    protected $guarded = [];

    public function orderRadiologiDetail()
    {
        return $this->hasMany(OrderRadiologiDetail::class);
    }
}
