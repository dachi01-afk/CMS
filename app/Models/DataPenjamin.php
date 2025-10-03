<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataPenjamin extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'data_penjamin';

    // Menentukan primary key secara eksplisit
    protected $primaryKey = 'id_penjamin';

    // Menonaktifkan incrementing untuk primary key jika bukan integer (opsional, tapi disarankan)
    public $incrementing = true;

    // Mengizinkan mass assignment untuk semua field (Guarded = array kosong)
    protected $guarded = [];

    // Relasi: Penjamin bisa memiliki banyak detail penjaminan kunjungan
    public function detailPenjaminanKunjungan()
    {
        return $this->hasMany(DetailPenjaminanKunjungan::class, 'penjamin_id', 'id_penjamin');
    }
}
