<?php

namespace App\Models;

use App\Models\ApproveDiskonPenjualanObat;
use App\Models\MetodePembayaran;
use App\Models\Pasien;
use App\Models\PenjualanObatDetail;
use Illuminate\Database\Eloquent\Model;

class PenjualanObat extends Model
{
    protected $table = 'penjualan_obat';

    protected $guarded = [];

    public function pasien()
    {
        return $this->belongsTo(Pasien::class);
    }

    public function metodePembayaran()
    {
        return $this->belongsTo(MetodePembayaran::class);
    }

    public function penjualanObatDetail()
    {
        return $this->hasMany(PenjualanObatDetail::class);
    }

    public function approveDiskon()
    {
        return $this->hasMany(ApproveDiskonPenjualanObat::class, 'penjualan_obat_id');
    }

    public function latestApprovedDiskon()
    {
        return $this->hasOne(ApproveDiskonPenjualanObat::class, 'penjualan_obat_id')
            ->where('status', 'approved')
            ->latestOfMany();
    }
}
