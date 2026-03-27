<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestockBahanHabisPakai extends Model
{
    protected $table = 'restock_bahan_habis_pakai';

    protected $guarded = [];

    public function restockBahanHabisPakaiDetail()
    {
        return $this->hasMany(RestockBahanHabisPakaiDetail::class);
    }

    public function hutang()
    {
        return $this->hasOne(HutangBahanHabisPakai::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }
    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }
    public function dikonfirmasiOleh()
    {
        return $this->belongsTo(User::class, 'dikonfirmasi_oleh');
    }
}
