<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanObat extends Model
{
    protected $table = 'penjualan_obat';

    protected $guarded = [];

    public function pasien()
    {
        return $this->belongsTo(Pasien::class);
    }

    public function obat()
    {
        return $this->belongsTo(Obat::class);
    }

    public function metodePembayaran()
    {
        return $this->belongsTo(MetodePembayaran::class);
    }
}
