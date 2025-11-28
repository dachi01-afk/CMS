<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DokterPoli extends Model
{
    protected $table = 'dokter_poli';

    protected $guarded = [];

    public function dokter()
    {
        return $this->belongsTo(Dokter::class);
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class);
    }

    public function perawatDokterPoli()
    {
        return $this->hasMany(PerawatDokterPoli::class);
    }

    public function perawat()
    {
        return $this->belongsToMany(Perawat::class, 'perawat_dokter_poli', 'dokter_poli_id', 'perawat_id');
    }
}
