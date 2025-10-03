<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenjaminanKunjungan extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'detail_penjaminan_kunjungan';

    // Menentukan primary key secara eksplisit
    protected $primaryKey = 'id_detail_penjaminan';

    // Mengizinkan mass assignment untuk semua field (Guarded = array kosong)
    protected $guarded = [];

    // Relasi: Detail penjaminan dimiliki oleh satu Penjamin
    public function penjamin()
    {
        return $this->belongsTo(DataPenjamin::class, 'penjamin_id', 'id_penjamin');
    }

    // Relasi: Detail penjaminan dimiliki oleh satu Kunjungan
    public function kunjungan()
    {
        // Asumsi model Kunjungan berada di App\Models\Kunjungan
        return $this->belongsTo(Kunjungan::class, 'kunjungan_id', 'id_kunjungan');
    }

    // Casting untuk tanggal_berlaku
    protected $casts = [
        'tanggal_berlaku' => 'date',
    ];
}
