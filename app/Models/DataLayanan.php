<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataLayanan extends Model
{
    use HasFactory;

    protected $table = 'data_layanan';
    protected $primaryKey = 'id_layanan';

    protected $fillable = [
        'nama_layanan',
        'deskripsi',
        'harga',
    ];

    public function detailPembayaran()
    {
        return $this->hasMany(DetailPembayaranLayanan::class, 'layanan_id', 'id_layanan');
    }
}
