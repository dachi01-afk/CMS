<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepotBHP extends Model
{
    protected $table = 'depot_bhp';

    protected $guarded = [];

    public function bahanHabisPakai()
    {
        return $this->belongsTo(BahanHabisPakai::class);
    }

    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }
    
    public function scopeGetDataBHP($query, $depotId)
    {
        return $query->with('depot')->where('depot_id', $depotId);
    }
}
