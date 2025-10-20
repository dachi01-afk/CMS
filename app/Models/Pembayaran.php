<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $guarded = [];

    public function emr()
    {
        return $this->belongsTo(EMR::class);
    }

    public function administrasi()
    {
        return $this->hasMany(Administrasi::class);
    }

    public function metodePembayaran() {
        return $this->belongsTo(MetodePembayaran::class);
    }
}
