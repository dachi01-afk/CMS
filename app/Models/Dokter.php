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

    public function jenisSpesialis()
    {
        return $this->belongsTo(JenisSpesialis::class);
    }

    public function jadwalDokter()
    {
        return $this->hasMany(JadwalDokter::class);
    }
}
