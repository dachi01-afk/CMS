<?php

namespace App\Models;

use App\Models\Dokter;
use App\Models\EMR;
use App\Models\JadwalDokter;
use App\Models\Layanan;
use App\Models\OrderLab;
use App\Models\OrderRadiologi;
use App\Models\Pasien;
use App\Models\PenjualanLayanan;
use App\Models\Poli;
use Illuminate\Database\Eloquent\Builder;
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
        return $this->belongsTo(Dokter::class, 'dokter_id');
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class, 'poli_id');
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }

    public function jadwalDokter()
    {
        return $this->belongsTo(JadwalDokter::class, 'jadwal_dokter_id');
    }

    public function emr()
    {
        return $this->hasOne(EMR::class, 'kunjungan_id');
    }

    public function kunjunganLayanan()
    {
        return $this->belongsToMany(Layanan::class, 'kunjungan_layanan', 'kunjungan_id', 'layanan_id')
            ->withPivot(['jumlah']);
    }

    // Scope untuk filter berdasarkan status
    public function scopeByStatus(Builder $query, string|array $status)
    {
        if (is_array($status)) {
            return $query->whereIn('status', $status);
        }

        return $query->where('status', $status);
    }

    public function scopeUntukPerawat(Builder $query, Perawat $perawat)
    {
        $penugasan = $perawat->dokterPoli()
            ->get(['dokter_poli.dokter_id', 'dokter_poli.poli_id']);

        if ($penugasan->isEmpty()) {
            return $query->whereKey(-1);
        }

        return $query->where(function ($q) use ($penugasan) {
            foreach ($penugasan as $item) {
                $q->orWhere(function ($sub) use ($item) {
                    $sub->where('dokter_id', $item->dokter_id)
                        ->where('poli_id', $item->poli_id);
                });
            }
        });
    }

    // Scope untuk kunjungan hari ini
    public function scopeHariIni(Builder $query, $tanggal = null)
    {
        $tanggal = $tanggal ?? now()->toDateString();

        return $query->whereDate('tanggal_kunjungan', $tanggal);
    }

    public function scopeByPasien(Builder $query, $pasienId)
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