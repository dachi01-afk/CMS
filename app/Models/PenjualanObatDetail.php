<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenjualanObatDetail extends Model
{
    protected $table = 'penjualan_obat_detail';

    protected $guarded = [];

    public function penjualanObat()
    {
        return $this->belongsTo(PenjualanObat::class);
    }   

    public function obat()
    {
        return $this->belongsTo(Obat::class);
    }   
}
