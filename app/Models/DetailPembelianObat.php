<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailPembelianObat extends Model
{
    use HasFactory;

    protected $table = 'detail_pembelian_obat';
    protected $primaryKey = 'id_detail_pembelian';
    protected $guarded = [];

    public function pembelianObat(): BelongsTo
    {
        return $this->belongsTo(PembelianObat::class, 'pembelian_obat_id', 'id_pembelian_obat');
    }

    public function obat(): BelongsTo
    {
        return $this->belongsTo(DataObat::class, 'obat_id', 'id_obat');
    }
}
