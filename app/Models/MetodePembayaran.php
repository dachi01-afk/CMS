<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetodePembayaran extends Model
{
    protected $table = 'metode_pembayaran';
    protected $guarded = [];

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function transaksiObat()
    {
        return $this->hasMany(PenjualanObat::class);
    }

    public function penjualanLayanan()
    {
        return $this->hasMany(PenjualanLayanan::class);
    }
}
