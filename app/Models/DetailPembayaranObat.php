<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DetailPembayaran extends Model
{
    use HasFactory;

    protected $table = 'detail_pembayaran_obat';
    protected $primaryKey = 'id_detail';
    protected $guarded = [];

    public function pembayaran(): BelongsTo
    {
        return $this->belongsTo(Pembayaran::class, 'pembayaran_id', 'id_pembayaran');
    }

    public function resepObat(): BelongsTo
    {
        return $this->belongsTo(ResepObat::class, 'resep_id', 'id_resep');
    }
}
