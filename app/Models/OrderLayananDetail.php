<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLayananDetail extends Model
{
    protected $table = 'order_layanan_detail';

    protected $guarded = [];

    public function orderLayanan()
    {
        return $this->belongsTo(OrderLayanan::class);
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class);
    }
}
