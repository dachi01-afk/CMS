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
        'dokter_id',
        'pasien_id',
        'tanggal_kunjungan',
        'no_antrian',
        'keluhan_awal',
    ];

    // Cast untuk tipe data yang sesuai
    protected $casts = [
        'tanggal_kunjungan' => 'datetime',
    ];

    // Relasi yang sudah ada - tetap sama
    public function resep()
    {
        return $this->hasMany(Resep::class);
    }

    public function tesLab()
    {
        return $this->hasMany(TesLab::class);
    }

    public function konsul()
    {
        return $this->hasMany(Konsul::class);
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class);
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class);
    }

    // Tambahkan relasi untuk EMR
    public function emr()
    {
        return $this->hasMany(EMR::class);
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
}
