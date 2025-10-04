<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apoteker extends Model
{
    protected $table = 'apoteker';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function resep()
    {
        return $this->hasMany(Resep::class);
    }
}
