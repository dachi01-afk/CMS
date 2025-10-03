<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pengantar extends Model
{
    use HasFactory;

    protected $table = 'pengantar';
    protected $primaryKey = 'id_pengantar';
    protected $guarded = [];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class, 'kunjungan_id', 'id_kunjungan');
    }
}
