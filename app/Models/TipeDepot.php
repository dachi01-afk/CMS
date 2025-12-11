<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipeDepot extends Model
{
    protected $table = 'tipe_depot';

    protected $guarded = [];

    public function depot()
    {
        return $this->hasMany(Depot::class);
    }
}
