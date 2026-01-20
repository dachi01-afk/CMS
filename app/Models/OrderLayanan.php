<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderLayanan extends Model
{
    protected $table = 'order_layanan';
    protected $guarded = [];

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class, 'poli_id');
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'dokter_id');
    }

    public function jadwalDokter()
    {
        return $this->belongsTo(JadwalDokter::class, 'jadwal_dokter_id');
    }

    public function details()
    {
        return $this->hasMany(OrderLayananDetail::class, 'order_layanan_id');
    }

    public function orderLayananDetail()
    {
        return $this->hasMany(OrderLayananDetail::class, 'order_layanan_id');
    }
}
