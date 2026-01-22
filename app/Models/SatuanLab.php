<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SatuanLab extends Model
{
    protected $table = 'satuan_lab';

    protected $guarded = [];

    public function jenisPemeriksaanLab() {
        return $this->hasMany(JenisPemeriksaanLab::class);
    }
}
