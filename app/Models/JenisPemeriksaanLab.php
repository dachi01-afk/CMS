<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPemeriksaanLab extends Model
{
    protected $table = 'jenis_pemeriksaan_lab';

    protected $guarded = [];

    public function satuanLab()
    {
        return $this->belongsTo(SatuanLab::class);
    }

    public function orderLabDetail()
    {
        return $this->hasMany(OrderLabDetail::class);
    }
}
