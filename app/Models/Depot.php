<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Depot extends Model
{
    protected $table = 'depot';

    protected $guarded = [];

    public function obat()
    {
        return $this->hasMany(Obat::class);
    }

    public function tipeDepot()
    {
        return $this->belongsTo(TipeDepot::class);
    }

    public function depotObat()
    {
        return $this->belongsToMany(Obat::class, 'depot_obat', 'depot_id', 'obat_id');
    }
}
