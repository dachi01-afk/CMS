<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Obat extends Model
{
    protected $table = 'obat';

    protected $guarded = [];

    public function resep()
    {
        return $this->belongsToMany(Resep::class, 'resep_obat', 'obat_id', 'resep_id')
            ->withPivot('jumlah', 'dosis')
            ->withTimestamps();
    }
}
