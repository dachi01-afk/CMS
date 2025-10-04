<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resep extends Model
{
    protected $table = 'resep';

    protected $guarded = [];

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class);
    }
    public function apoteker()
    {
        return $this->belongsTo(Apoteker::class);
    }
}
