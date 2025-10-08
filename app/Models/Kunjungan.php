<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kunjungan extends Model
{
    use HasFactory;

    protected $table = 'kunjungan';

    // Ubah dari guarded ke fillable untuk security yang lebih baik
    protected $fillable = [
        'dokter_id',
        'pasien_id', 
        'tanggal_kunjungan',
        'keluhan_awal',
        'status',
        'nama_rs_perujuk',
        'nama_dokter_perujuk'
    ];

    // Cast untuk tipe data yang sesuai
    protected $casts = [
        'tanggal_kunjungan' => 'datetime',
    ];

    // Relasi yang sudah ada - tetap sama
    public function resep()
    {
        return $this->hasMany(Resep::class, 'kunjungan_id', 'id');
    }

    public function tesLab()
    {
        return $this->hasMany(TesLab::class, 'kunjungan_id', 'id');
    }

    public function konsul()
    {
        return $this->hasMany(Konsul::class, 'kunjungan_id', 'id');
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'dokter_id', 'id');
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id', 'id_pasien');
    }

    // Tambahkan relasi untuk EMR
    public function emr()
    {
        return $this->hasMany(EMR::class, 'kunjungan_id', 'id');
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