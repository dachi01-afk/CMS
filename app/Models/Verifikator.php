<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Verifikator extends Model
{
    protected $table = 'verifikator';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
