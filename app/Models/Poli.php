<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poli extends Model
{
    protected $table = 'poli';
    protected $guarded = [];

    public function dokter()
    {
        return $this->hasOne(Dokter::class);
    }

    public function kunjungan()
    {
        return $this->hasOne(Kunjungan::class);
    }
}
