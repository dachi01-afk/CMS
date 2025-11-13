<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EMR extends Model
{
    protected $table = 'emr';

    protected $guarded = [];


    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class);
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class);
    }

    public function perawat()
    {
        return $this->belongsTo(Perawat::class);
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class);
    }

    public function resep()
    {
        return $this->belongsTo(Resep::class);
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class);
    }
    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'emr_id');
    }
}
