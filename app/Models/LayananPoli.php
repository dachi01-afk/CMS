<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayananPoli extends Model
{
    protected $table = 'layanan_poli';

    protected $guarded = [];

    public function layanan()
    {
        return $this->belongsTo(Layanan::class);
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class);
    }
}
