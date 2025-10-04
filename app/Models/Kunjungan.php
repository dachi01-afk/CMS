<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kunjungan extends Model
{
    protected $table = 'kunjungan';

    protected $guarded = [];

    public function resep()
    {
        return $this->hasMany(Resep::class);
    }

    public function tesLab()
    {
        return $this->hasMany(TesLab::class);
    }
}
