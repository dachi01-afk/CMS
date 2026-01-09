<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerawatDokterPoli extends Model
{
    protected $table = 'perawat_dokter_poli';
    protected $guarded = [];

    public function perawat()
    {
        return $this->belongsTo(Perawat::class, 'perawat_id');
    }

    public function dokterPoli()
    {
        return $this->belongsTo(DokterPoli::class, 'dokter_poli_id');
    }
}
