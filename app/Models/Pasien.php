<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pasien extends Model
{
    protected $table = 'pasien';
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class);
    }

    public function Testimoni()
    {
        return $this->hasMany(Testimoni::class);
    }

    public function obat()
    {
        return $this->belongsToMany(Obat::class, 'penjualan_obat', 'pasien_id', 'obat_id')
            ->withPivot(
                'kode_transaksi',
                'jumlah',
                'uang_yang_diterima',
                'kembalian',
                'sub_total',
                'tanggal_transaksi',
                'bukti_pembayaran',
                'status',
                'metode_pembayaran_id',
            )
            ->withTimestamps();
    }
}
