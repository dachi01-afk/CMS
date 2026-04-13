<?php

namespace App\Models;

use App\Models\Obat;
use App\Models\PenjualanObat;
use Illuminate\Database\Eloquent\Model;

class PenjualanObatDetail extends Model
{
    protected $table = 'penjualan_obat_detail';
    protected $guarded = [];

    public function penjualanObat()
    {
        return $this->belongsTo(PenjualanObat::class, 'penjualan_obat_id');
    }

    public function obat()
    {
        return $this->belongsTo(Obat::class, 'obat_id');
    }
}
