<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kunjungan extends Model
{
    use HasFactory;

    protected $table = 'kunjungan';

    // Ubah dari guarded ke fillable untuk security yang lebih baik katanya pid
    protected $fillable = [
        'poli_id',
        'pasien_id',
        'tanggal_kunjungan',
        'no_antrian',
        'keluhan_awal',
        'status',
    ];

    // Cast untuk tipe data yang sesuai
    protected $casts = [
        'tanggal_kunjungan' => 'datetime',
    ];

    // Relasi yang sudah ada - tetap sama
    // public function resep()
    // {
    //     return $this->hasMany(Resep::class);
    // }

    // Dokter langsung belongsTo lewat poli_id
    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'poli_id', 'poli_id');
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class);
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class);
    }

    // Tambahkan relasi untuk EMR
    public function emr()
    {
        return $this->hasOne(EMR::class);
    }

    public function kunjunganLayanan()
    {
        return $this->belongsToMany(Layanan::class, 'kunjungan_layanan', 'kunjungan_id', 'layanan_id')->withPivot(['jumlah']);
    }

    // Scope untuk filter berdasarkan status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope untuk kunjungan hari ini
    public function scopeToday($query)
    {
        return $query->whereDate('tanggal_kunjungan', today());
    }

    // Scope untuk kunjungan pasien tertentu
    public function scopeByPasien($query, $pasienId)
    {
        return $query->where('pasien_id', $pasienId);
    }

    // Accessor untuk format tanggal yang mudah dibaca
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
}
