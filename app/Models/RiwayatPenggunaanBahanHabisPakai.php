<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RiwayatPenggunaanBahanHabisPakai extends Model
{
    protected $table = 'riwayat_penggunaan_bahan_habis_pakai';

    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->user_id = Auth::id();
        });
    }

    public static function simpanData($data)
    {
        return DB::transaction(function () use ($data) {
            $dataBhp = BahanHabisPakai::where('id', $data['bahan_habis_pakai_id'])->lockForUpdate()->first();

            if ($dataBhp->stok_barang < $data['jumlah_pemakaian']) {
                throw new Exception("Stok barang tidak mencukupi! Sisa stok: {$dataBhp->stok_barang}");
            }

            $dataBhp->decrement('stok_barang', $data['jumlah_pemakaian']);

            return self::create([
                'bahan_habis_pakai_id' => $data['bahan_habis_pakai_id'],
                // 'user_id' => $data['user_id'],
                'jumlah_pemakaian' => $data['jumlah_pemakaian'],
                'tanggal_pemakaian' => $data['tanggal_pemakaian'],
                'keterangan' => $data['keterangan'] ?? null,
            ]);
        });
    }
}
