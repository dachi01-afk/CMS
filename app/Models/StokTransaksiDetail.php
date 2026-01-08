<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokTransaksiDetail extends Model
{
    protected $table = 'stok_transaksi_detail';

    protected $guarded = [];

    public function stokTransaksi()
    {
        return $this->belongsTo(StokTransaksi::class);
    }

    public function obat()
    {
        return $this->belongsTo(Obat::class);
    }

    public function bhp()
    {
        return $this->belongsTo(BahanHabisPakai::class, 'bahan_habis_pakai_id');
    }

    public function satuanObat()
    {
        return $this->belongsTo(SatuanObat::class, 'satuan_id');
    }

    public function depot()
    {
        return $this->belongsTo(Depot::class);
    }
}
