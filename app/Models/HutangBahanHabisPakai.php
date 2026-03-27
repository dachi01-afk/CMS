<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HutangBahanHabisPakai extends Model
{
    protected $table = 'hutang_bahan_habis_pakai';

    protected $guarded = [];

    public function restockBahanHabisPakai()
    {
        return $this->belongsTo(RestockBahanHabisPakai::class);
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
    public function diupdateOleh()
    {
        return $this->belongsTo(User::class, 'diupdate_oleh');
    }
    public function metodePembayaran()
    {
        return $this->belongsTo(MetodePembayaran::class);
    }
}
