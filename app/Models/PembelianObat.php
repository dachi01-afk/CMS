<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PembelianObat extends Model
{
    use HasFactory;

    protected $table = 'pembelian_obat';
    protected $primaryKey = 'id_pembelian_obat';
    protected $guarded = [];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id_supplier');
    }

    public function detailPembelianObat(): HasMany
    {
        return $this->hasMany(DetailPembelianObat::class, 'pembelian_obat_id', 'id_pembelian_obat');
    }
}
