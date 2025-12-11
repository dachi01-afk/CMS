<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepotObat extends Model
{
    protected $table = 'depot_obat';

    protected $guarded = [];

    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }

    public function obat()
    {
        return $this->belongsTo(Obat::class);
    }
}
