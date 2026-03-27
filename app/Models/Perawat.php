<?php

namespace App\Models;

use App\Models\DokterPoli;
use App\Models\EMR;
use App\Models\HasilLab;
use App\Models\HasilRadiologi;
use App\Models\PerawatDokterPoli;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Perawat extends Model
{
    protected $table = 'perawat';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dokterPoli()
    {
        return $this->belongsToMany(
            DokterPoli::class,
            'perawat_dokter_poli',
            'perawat_id',
            'dokter_poli_id'
        );
    }

    public function emr()
    {
        return $this->hasMany(EMR::class, 'perawat_id');
    }

    public function hasilLab()
    {
        return $this->hasMany(HasilLab::class, 'perawat_id');
    }

    public function hasilRadiologi()
    {
        return $this->hasMany(HasilRadiologi::class, 'perawat_id');
    }

    public function perawatDokterPoli() {
        return $this->hasMany(PerawatDokterPoli::class);
    }
}
