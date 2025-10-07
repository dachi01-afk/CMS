<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimoni extends Model
{
    protected $table = 'testimoni';

    protected $guarded = [];

    public function pasien(){ 
        return $this->belongsTo(Pasien::class);
    }
}
