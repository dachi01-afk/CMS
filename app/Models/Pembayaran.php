<?php

namespace App\Models;

use App\Support\Audit\Auditable;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use Auditable;

    protected $table = 'pembayaran';

    protected $guarded = [];

    protected $casts = [
        'tanggal_pembayaran' => 'datetime',
    ];

    public function emr()
    {
        return $this->belongsTo(EMR::class);
    }

    public function metodePembayaran()
    {
        return $this->belongsTo(MetodePembayaran::class);
    }

    public function pembayaranDetail()
    {
        return $this->hasMany(PembayaranDetail::class);
    }
}
