<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderRadiologi extends Model
{
    protected $table = 'order_radiologi';

    protected $guarded = [];

    public function dokter()
    {
        return $this->belongsTo(Dokter::class);
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class);
    }

    public function kunjugan()
    {
        return $this->belongsTo(Kunjungan::class);
    }

    public function orderRadiologiDetail()
    {
        return $this->hasMany(OrderRadiologiDetail::class);
    }
}
