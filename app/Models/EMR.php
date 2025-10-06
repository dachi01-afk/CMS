<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EMR extends Model
{
    protected $table = 'emr';

    protected $guarded = [];


    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class, 'kunjungan_id');
    }
}
