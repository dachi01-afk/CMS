<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $table = 'layanan';

    protected $guarded = [];

    // ✅ TAMBAHAN: Appends untuk memastikan harga_layanan selalu ada di JSON response
    protected $appends = ['harga_layanan'];

    // ✅ ACCESSOR: Auto-calculate harga_layanan dari kolom baru
    public function getHargaLayananAttribute()
    {
        // Prioritas:
        // 1. harga_setelah_diskon (kalau ada diskon)
        // 2. harga_sebelum_diskon (harga asli)
        // 3. Default 0
        
        $hargaSetelah = $this->attributes['harga_setelah_diskon'] ?? null;
        $hargaSebelum = $this->attributes['harga_sebelum_diskon'] ?? null;
        
        // Pakai harga setelah diskon kalau > 0, kalau tidak pakai harga sebelum diskon
        if ($hargaSetelah !== null && $hargaSetelah > 0) {
            return (float) $hargaSetelah;
        }
        
        return (float) ($hargaSebelum ?? 0);
    }

    // ===== RELASI =====
    
    public function kunjungan()
    {
        return $this->belongsToMany(
            Kunjungan::class,
            'kunjungan_layanan',
            'layanan_id',
            'kunjungan_id'
        )->withPivot('jumlah')
            ->withTimestamps();
    }

    public function kategoriLayanan()
    {
        return $this->belongsTo(KategoriLayanan::class);
    }

    public function penjualanLayanan()
    {
        return $this->hasMany(PenjualanLayanan::class);
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriLayanan::class, 'kategori_layanan_id');
    }

    public function orderLayananDetail()
    {
        return $this->hasMany(OrderLayananDetail::class);
    }

    public function layananPoli()
    {
        return $this->belongsToMany(Poli::class, 'layanan_poli', 'layanan_id', 'poli_id');
    }

    public function polis()
    {
        return $this->belongsToMany(Poli::class, 'layanan_poli', 'layanan_id', 'poli_id');
    }
}