<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PiutangObat extends Model
{
    protected $table = 'piutang_obat';
    protected $guarded = [];

    public function returnObat()
    {
        return $this->belongsTo(ReturnObat::class, 'return_obat_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
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
        return $this->belongsTo(MetodePembayaran::class, 'metode_pembayaran_id');
    }
}
