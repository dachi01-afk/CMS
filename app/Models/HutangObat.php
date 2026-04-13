<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HutangObat extends Model
{
    protected $table = 'hutang_obat';

    protected $guarded = [];

    public function restockObat()
    {
        return $this->belongsTo(RestockObat::class);
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
