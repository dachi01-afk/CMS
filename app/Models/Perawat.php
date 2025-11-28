<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perawat extends Model
{
    protected $table = "perawat";

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function perawatDokterPoli()
    {
        return $this->belongsToMany(DokterPoli::class, 'perawat_dokter_poli', 'perawat_id', 'dokter_poli_id');
    }

    public function emr()
    {
        return $this->hasMany(Emr::class);
    }
}
