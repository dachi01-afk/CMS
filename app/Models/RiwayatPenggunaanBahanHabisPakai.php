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
            $pivot = DB::table('depot_bhp')
                ->where('bahan_habis_pakai_id', $data['bahan_habis_pakai_id'])
                ->where('depot_id', $data['depot_id'])
                ->lockForUpdate()->first();

            if (!$pivot || $pivot->stok_barang < $data['jumlah_pemakaian']) {
                $sisa = $pivot ? $pivot->stok_barang : 0;
                throw new Exception("Stok di Depot ini tidak mencukupi! Sisa stok: {$sisa}");
            }

            $dataBhp = BahanHabisPakai::where('id', $data['bahan_habis_pakai_id'])->lockForUpdate()->first();

            $dataDepot = Depot::where('id', $data['depot_id'])->lockForUpdate()->first();

            DB::table('depot_bhp')
                ->where('bahan_habis_pakai_id', $data['bahan_habis_pakai_id'])
                ->where('depot_id', $data['depot_id'])
                ->decrement('stok_barang', $data['jumlah_pemakaian']);

            $dataBhp->decrement('stok_barang', $data['jumlah_pemakaian']);

            $dataDepot->decrement('jumlah_stok_depot', $data['jumlah_pemakaian']);

            return self::create([
                'bahan_habis_pakai_id' => $data['bahan_habis_pakai_id'],
                'depot_id' => $data['depot_id'],
                'jumlah_pemakaian' => $data['jumlah_pemakaian'],
                'tanggal_pemakaian' => $data['tanggal_pemakaian'],
                'keterangan' => $data['keterangan'] ?? null,
            ]);
        });
    }
}
