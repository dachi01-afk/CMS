<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Depot extends Model
{
    protected $table = 'depot';

    protected $guarded = [];

    public function obat()
    {
        return $this->hasMany(Obat::class);
    }

    public function bph()
    {
        return $this->hasMany(BahanHabisPakai::class);
    }

    public function stokTransaksiDetal()
    {
        return $this->hasMany(StokTransaksiDetail::class);
    }

    public function batchObatDepot()
    {
        return $this->hasMany(BatchObatDepot::class);
    }

    public function batchBahanHabisPakaiDepot()
    {
        return $this->hasMany(BatchBahanHabisPakaiDepot::class);
    }

    public function tipeDepot()
    {
        return $this->belongsTo(TipeDepot::class);
    }

    public function mutasiStokObatDetail()
    {
        return $this->belongsTo(MutasiStokObatDetail::class);
    }

    public function depotObat()
    {
        return $this->belongsToMany(Obat::class, 'depot_obat', 'depot_id', 'obat_id')->withPivot('stok_obat')->withTimestamps();
    }

    public function depotBHP()
    {
        return $this->belongsToMany(BahanHabisPakai::class, 'depot_bhp', 'depot_id', 'bahan_habis_pakai_id')->withPivot('stok_barang')->withTimestamps();
    }

    public function scopeGetData($query, $bhpId = null)
    {
        // 1. Pastikan semua kolom utama menggunakan prefix 'depot.'
        $query->select([
            'depot.id',
            'depot.nama_depot',
            'depot.jumlah_stok_depot'
        ]);

        if ($bhpId) {
            $query->leftJoin('depot_bhp', function ($join) use ($bhpId) {
                // 2. Perbaiki 'depot.id' dan 'depot_bhp.depot_id' (gunakan underscore)
                $join->on('depot.id', '=', 'depot_bhp.depot_id')
                    ->where('depot_bhp.bahan_habis_pakai_id', '=', $bhpId);
            })
                // 3. Ambil stok spesifik dari pivot, jika null jadikan 0
                ->addSelect(DB::raw('COALESCE(depot_bhp.stok_barang, 0) as stok_barang'));
        } else {
            // 4. Jika bhpId kosong, tetap buat kolom stok_barang agar JS tidak error
            $query->addSelect(DB::raw('0 as stok_barang'));
        }

        // 5. Urutkan berdasarkan nama depot
        return $query->orderBy('depot.nama_depot', 'asc');
    }
}
