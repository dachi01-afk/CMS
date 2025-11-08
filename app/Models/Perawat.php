<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perawat extends Model
{
    protected $table = "perawat";

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
