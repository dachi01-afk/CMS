<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilRadiologi extends Model
{
    protected $table = 'hasil_radiologi';

    protected $guarded = [];

    public function orderRadiologiDetail()
    {
        return $this->belongsTo(OrderRadiologiDetail::class);
    }

    public function perawat()
    {
        return $this->belongsTo(Perawat::class);
    }
}
