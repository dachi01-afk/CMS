<?php

namespace App\Models;

use App\Models\Dokter;
use App\Models\JadwalDokter;
use App\Models\MetodePembayaran;
use App\Models\OrderLayananDetail;
use App\Models\Pasien;
use App\Models\Poli;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class OrderLayanan extends Model
{
    protected $table = 'order_layanan';
    protected $guarded = [];

    protected $casts = [
        'tanggal_order' => 'datetime',
        'tanggal_pembayaran' => 'datetime',
    ];

    protected $appends = [
        'subtotal_rupiah',
        'total_bayar_rupiah',
    ];

    protected function subtotalRupiah(): Attribute
    {
        return Attribute::make(get: fn() => 'Rp. ' . number_format($this->subtotal, 0, ',', '.'));
    }

    protected function totalBayarRupiah(): Attribute
    {
        return Attribute::make(get: fn() => 'Rp. ' . number_format($this->total_bayar, 0, ',', '.'));
    }

    public function getFormatTanggalOrder()
    {
        return $this->tanggal_order ? $this->tanggal_order->translatedFormat('d F Y') : '-';
    }

    public function getFormatTanggalPembayaran()
    {
        return $this->tanggal_pembayaran ? $this->tanggal_pembayaran->translatedFormat('d F Y') : '-';
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class, 'poli_id');
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'dokter_id');
    }

    public function jadwalDokter()
    {
        return $this->belongsTo(JadwalDokter::class, 'jadwal_dokter_id');
    }

    public function metodePembayaran()
    {
        return $this->belongsTo(MetodePembayaran::class, 'metode_pembayaran_id');
    }

    public function details()
    {
        return $this->hasMany(OrderLayananDetail::class, 'order_layanan_id');
    }

    public function orderLayananDetail()
    {
        return $this->hasMany(OrderLayananDetail::class, 'order_layanan_id');
    }
}
