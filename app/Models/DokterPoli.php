<?php

namespace App\Models;

use App\Models\Dokter;
use App\Models\Perawat;
use App\Models\Poli;
use Illuminate\Database\Eloquent\Model;

class DokterPoli extends Model
{
    protected $table = 'dokter_poli';
    protected $guarded = [];

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'dokter_id');
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class, 'poli_id');
    }

    public function perawats()
    {
        return $this->belongsToMany(
            Perawat::class,
            'perawat_dokter_poli',
            'dokter_poli_id',
            'perawat_id'
        );
    }
}
