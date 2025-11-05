<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Farmasi extends Model
{
    protected $table = "farmasi";

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
