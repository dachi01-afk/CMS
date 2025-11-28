<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerawatDokterPoli extends Model
{
    protected $table = 'perawat_dokter_poli';

    protected $guarded = [];

    public function perawat()
    {
        return $this->belongsTo(Perawat::class);
    }

    public function dokterPoli()
    {
        return $this->belongsTo(Dokter::class);
    }
}
