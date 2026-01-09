<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLayanan extends Model
{
    protected $table = 'order_layanan';

    protected $guarded = [];

    public function pasien()
    {
        return $this->belongsTo(Pasien::class);
    }
    public function poli()
    {
        return $this->belongsTo(Poli::class);
    }
    public function Dokter()
    {
        return $this->belongsTo(Dokter::class);
    }
    public function jadwalDokter()
    {
        return $this->belongsTo(JadwalDokter::class);
    }

    public function orderLayananDetail()
    {
        return $this->hasMany(OrderLayananDetail::class);
    }
}
