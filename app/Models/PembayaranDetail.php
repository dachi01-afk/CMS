<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranDetail extends Model
{
    protected $table = 'pembayaran_detail';

    protected $guarded = [];

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class);
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class);
    }

    public function resepObat()
    {
        return $this->belongsTo(ResepObat::class);
    }

    public function orderLabDetail()
    {
        return $this->belongsTo(OrderLabDetail::class);
    }

    public function orderRadiologiDetail()
    {
        return $this->belongsTo(OrderRadiologiDetail::class);
    }
}
