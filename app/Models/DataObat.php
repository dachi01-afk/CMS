<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DataObat extends Model
{
    use HasFactory;

    protected $table = 'data_obat';
    protected $primaryKey = 'id_obat';
    protected $guarded = [];

    public function kategoriObat(): BelongsTo
    {
        return $this->belongsTo(KategoriObat::class, 'kategori_obat_id', 'id_kategori_obat');
    }

    public function satuanObat(): BelongsTo
    {
        return $this->belongsTo(SatuanObat::class, 'satuan_obat_id', 'id_satuan_obat');
    }

    public function detailPembelianObat(): HasMany
    {
        return $this->hasMany(DetailPembelianObat::class, 'obat_id', 'id_obat');
    }

    public function resepObat(): HasMany
    {
        return $this->hasMany(ResepObat::class, 'obat_id', 'id_obat');
    }
}
