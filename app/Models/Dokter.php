<?php

namespace App\Models;


use App\Models\JenisSpesialis;
use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    protected $table = 'dokter';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function poli()
    {
        return $this->belongsToMany(Poli::class, 'dokter_poli', 'dokter_id', 'poli_id')->withTimestamps();
    }

    public function jenisSpesialis()
    {
        return $this->belongsTo(JenisSpesialis::class);
    }

    public function jadwalDokter()
    {
        return $this->hasMany(JadwalDokter::class);
    }
}
