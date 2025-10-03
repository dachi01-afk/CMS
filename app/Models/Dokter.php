<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokter extends Model
{
    protected $table = 'dokter';

    protected $guarded = [];

    public function user()
    {
        $this->belongsTo(User::class);
    }
}
