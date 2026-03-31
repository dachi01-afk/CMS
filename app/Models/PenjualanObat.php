<?php

namespace App\Models;

use App\Models\ApproveDiskonPenjualanObat;
use App\Models\MetodePembayaran;
use App\Models\Pasien;
use App\Models\PenjualanObatDetail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class PenjualanObat extends Model
{
    protected $table = 'penjualan_obat';

    protected $guarded = [];

    protected $casts = [
        'tanggal_transaksi' => 'datetime',
    ];

    protected $appends = [
        'total_tagihan_rupiah',
        'uang_yang_diterima_rupiah',
        'total_setelah_diskon_rupiah',
    ];

    protected function totalTagihanRupiah(): Attribute
    {
        return Attribute::make(get: fn() => 'Rp. ' . number_format($this->total_tagihan, 0, ',', '.'));
    }

    protected function uangYangDiterimaRupiah(): Attribute
    {
        return Attribute::make(get: fn() => 'Rp. ' . number_format($this->uang_yang_diterima, 0, ',', '.'));
    }

    protected function totalSetelahDiskonRupiah(): Attribute
    {
        return Attribute::make(get: fn() => 'Rp. ' . number_format($this->total_setelah_diskon, 0, ',', '.'));
    }

    public function getFormatTanggalTransaksi()
    {
        return $this->tanggal_transaksi ? $this->tanggal_transaksi->translatedFormat('d F Y') : '-';
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class);
    }

    public function metodePembayaran()
    {
        return $this->belongsTo(MetodePembayaran::class);
    }

    public function penjualanObatDetail()
    {
        return $this->hasMany(PenjualanObatDetail::class);
    }

    public function approveDiskon()
    {
        return $this->hasMany(ApproveDiskonPenjualanObat::class, 'penjualan_obat_id');
    }

    public function latestApprovedDiskon()
    {
        return $this->hasOne(ApproveDiskonPenjualanObat::class, 'penjualan_obat_id')
            ->where('status', 'approved')
            ->latestOfMany();
    }
}
