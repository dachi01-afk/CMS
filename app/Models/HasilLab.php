<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilLab extends Model
{
    protected $table = 'hasil_lab';

    protected $guarded = [];

    public function orderLabDetail()
    {
        return $this->belongsTo(OrderLabDetail::class);
    }

    public function perawat()
    {
        return $this->belongsTo(Perawat::class);
    }

    public function scopeGetData($query) {
        $query->select();
    }
}
