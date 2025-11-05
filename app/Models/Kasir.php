<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kasir extends Model
{
    protected $table = 'kasir';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
