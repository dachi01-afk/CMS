<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kunjungan extends Model
{
    protected $table = 'kunjungan';

    protected $guarded = [];

    public function resep()
    {
        return $this->hasMany(Resep::class);
    }

    public function tesLab()
    {
        return $this->hasMany(TesLab::class);
    }

    public function konsul()
    {
        return $this->hasMany(Konsul::class);
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class);
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class);
    }
}
