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

    public function resep()
    {
        return $this->belongsTo(Resep::class);
    }

    public function pasien()
    {
        // Relasi ini “menelusuri” dari EMR → Kunjungan → Pasien
        return $this->hasOneThrough(
            Pasien::class,
            Kunjungan::class,
            'id',          // Foreign key di tabel kunjungan
            'id',          // Foreign key di tabel pasien
            'kunjungan_id', // Foreign key di tabel EMR
            'pasien_id'    // Kolom pasien_id di tabel kunjungan
        );
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class);
    }
}
