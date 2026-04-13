<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kunjungan extends Model
{
    use HasFactory;

    protected $table = 'kunjungan';

    protected $fillable = [
        'jadwal_dokter_id',
        'dokter_id',
        'poli_id',
        'pasien_id',
        'tanggal_kunjungan',
        'no_antrian',
        'keluhan_awal',
        'status',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'datetime',
    ];

    public function dokter()
    {
        return $this->belongsTo(Dokter::class);
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class);
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class);
    }

    public function jadwalDokter()
    {
        return $this->belongsTo(JadwalDokter::class);
    }

    public function emr()
    {
        return $this->hasOne(EMR::class);
    }

    public function kunjunganLayanan()
    {
        return $this->belongsToMany(Layanan::class, 'kunjungan_layanan', 'kunjungan_id', 'layanan_id')
            ->withPivot(['jumlah']);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('tanggal_kunjungan', today());
    }

    public function scopeByPasien($query, $pasienId)
    {
        return $query->where('pasien_id', $pasienId);
    }

    public function getFormattedDateAttribute()
    {
        return $this->tanggal_kunjungan->format('d M Y H:i');
    }

    public function layanan()
    {
        return $this->belongsToMany(Layanan::class, 'kunjungan_layanan', 'kunjungan_id', 'layanan_id')
            ->withPivot(['jumlah'])
            ->withTimestamps();
    }

    public function penjualanLayanan()
    {
        return $this->hasMany(PenjualanLayanan::class);
    }

    public function orderLab()
    {
        return $this->hasMany(OrderLab::class);
    }

    public function orderRadiologi()
    {
        return $this->hasMany(OrderRadiologi::class);
    }

    public function emrKklp()
    {
        return $this->hasOne(EmrKklp::class, 'kunjungan_id');
    }

    
}