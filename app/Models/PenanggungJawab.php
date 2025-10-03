<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenanggungJawab extends Model
{
    use HasFactory;

    // Baris ini akan menyelesaikan masalah error "Table not found"
    protected $table = 'penanggung_jawab'; 

    protected $primaryKey = 'id'; 
    protected $guarded = [];

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'pasien_id', 'id_pasien');
    }
}