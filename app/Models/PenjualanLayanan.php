<?php

namespace App\Models;

use App\Support\Audit\Auditable;
use Illuminate\Database\Eloquent\Model;

class PenjualanLayanan extends Model
{
    use Auditable;

    protected $table = "penjualan_layanan";

    protected $guarded = [];

    public function pasien()
    {
        return $this->belongsTo(Pasien::class);
    }

    public function layanan()
    {
        return $this->belongsTo(Layanan::class);
    }
    public function kategoriLayanan()
    {
        return $this->belongsTo(KategoriLayanan::class);
    }
    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class);
    }
    public function metodePembayaran()
    {
        return $this->belongsTo(MetodePembayaran::class);
    }
}
