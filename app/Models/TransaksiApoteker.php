<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiApoteker extends Model
{
    protected $table = 'transaksi_apoteker';

    protected $guarded = [];

    public function resep()
    {
        return $this->belongsTo(Resep::class, 'resep_id');
    }

    public function apoteker()
    {
        return $this->belongsTo(Apoteker::class, 'apoteker_id');
    }
}
