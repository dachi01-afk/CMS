<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Konsul extends Model
{
    protected $table = 'konsul';

    protected $guarded = [];

    public function kunjungan() {
        return $this->belongsTo(Kunjungan::class);
    }
}
