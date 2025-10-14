<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalDokter extends Model
{
    protected $table = 'jadwal_dokter';

    protected $guarded = [];

    public function dokter () {
        return $this->belongsTo(Dokter::class);
    }

    public function poli() {
        return $this->belongsTo(Poli::class);
    }
}
