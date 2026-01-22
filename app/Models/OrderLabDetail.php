<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLabDetail extends Model
{
    protected $table = 'order_lab_detail';

    protected $guarded = [];

    public function orderLab()
    {
        return $this->belongsTo(OrderLab::class);
    }

    public function jenisPemeriksaanLab()
    {
        return $this->belongsTo(JenisPemeriksaanLab::class);
    }
}
