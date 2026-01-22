<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLab extends Model
{
    protected $table = 'order_lab';

    protected $guarded = [];

    public function dokter()
    {
        return $this->belongsTo(Dokter::class);
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class);
    }

    public function orderLabDetail()
    {
        return $this->hasMany(OrderLabDetail::class);
    }
}
