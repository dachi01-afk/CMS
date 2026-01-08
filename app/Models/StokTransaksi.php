<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokTransaksi extends Model
{
    protected $table = 'stok_transaksi';

    protected $guarded = [];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function stokTransaksiDetail()
    {
        return $this->hasMany(StokTransaksiDetail::class);
    }
}
