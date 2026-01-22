<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderLab extends Model
{
    use HasFactory;

    protected $table = 'order_lab';

    protected $guarded = [];

    public function dokter()
    {
        return $this->belongsTo(Dokter::class);
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class);
    }

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class);
    }

    public function orderLabDetail()
    {
        return $this->hasMany(OrderLabDetail::class);
    }

    /**
     * Scope untuk filter order khusus hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('tanggal_order', now()->toDateString());
    }

    /**
     * Scope untuk menyiapkan data dasar DataTables
     * Mengambil relasi yang diperlukan agar tidak N+1 Problem
     */
    public function scopeGetData($query)
    {
        return $query->with(['dokter', 'pasien', 'kunjungan', 'orderLabDetail.jenisPemeriksaanLab'])
            ->select('order_lab.*'); // Pastikan select table utama agar tidak bentrok id
    }

    public static function getDataById($id)
    {
        return self::with(['pasien', 'dokter', 'kunjungan', 'orderLabDetail.jenisPemeriksaanLab.satuanLab'])->findOrFail($id);
    }

    /**
     * LOGIC UTAMA: Filter data berdasarkan Perawat yang Login
     */
    public function scopeFilterByPerawat($query, $perawatId)
    {
        // Logika: Tampilkan Order Lab dimana dokter_id nya ada di dalam 
        // daftar dokter yang ditangani oleh perawat tersebut.

        return $query->whereIn('dokter_id', function ($subQuery) use ($perawatId) {
            // 1. Pilih dokter_id dari tabel dokter_poli
            $subQuery->select('dokter_poli.dokter_id')
                ->from('dokter_poli')
                // 2. Gabungkan dengan tabel perawat_dokter_poli (pivot yang kamu kirim gambarnya)
                ->join('perawat_dokter_poli', 'perawat_dokter_poli.dokter_poli_id', '=', 'dokter_poli.id')
                // 3. Filter dimana perawat_id nya sesuai input
                ->where('perawat_dokter_poli.perawat_id', $perawatId);
        });
    }
}
