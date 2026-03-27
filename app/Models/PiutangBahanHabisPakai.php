<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PiutangBahanHabisPakai extends Model
{
    protected $table = 'piutang_bahan_habis_pakai';

    protected $guarded = [];

    public function returnBahanHabisPakai()
    {
        return $this->belongsTo(ReturnBahanHabisPakai::class);
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
