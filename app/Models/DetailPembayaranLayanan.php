<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPembayaranLayanan extends Model
{
    use HasFactory;

    protected $table = 'detail_pembayaran_layanan';
    protected $primaryKey = 'id_detail_layanan';

    protected $fillable = [
        'pembayaran_id',
        'layanan_id',
        'harga_satuan',
        'total_harga_item',
    ];

    public function pembayaran()
    {
        return $this->belongsTo(Pembayaran::class, 'pembayaran_id', 'id_pembayaran');
    }

    public function layanan()
    {
        return $this->belongsTo(DataLayanan::class, 'layanan_id', 'id_layanan');
    }
}
