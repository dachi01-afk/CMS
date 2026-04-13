<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnBahanHabisPakai extends Model
{
    protected $table = 'return_bahan_habis_pakai';

    protected $guarded = [];

    public function returnBahanHabisPakaiDetail()
    {
        return $this->hasMany(ReturnBahanHabisPakaiDetail::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function depot()
    {
        return $this->belongsTo(Depot::class, 'depot_id');
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function diupdateOleh()
    {
        return $this->belongsTo(User::class, 'diupdate_oleh');
    }

    public function piutang()
    {
        return $this->hasOne(PiutangObat::class);
    }
}
