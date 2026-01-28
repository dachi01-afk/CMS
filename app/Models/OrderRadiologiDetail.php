<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderRadiologiDetail extends Model
{
    protected $table = 'order_radiologi_detail';

    protected $guarded = [];

    public function orderRadiologi()
    {
        return $this->belongsTo(OrderRadiologi::class);
    }

    public function jenisPemeriksaanRadiologi()
    {
        return $this->belongsTo(JenisPemeriksaanRadiologi::class);
    }

    public function hasilRadiologi()
    {
        return $this->hasMany(HasilRadiologi::class);
    }
}
