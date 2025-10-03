<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PsikososialSpiritual extends Model
{
    use HasFactory;


    protected $table = 'psikososial_spiritual';
    protected $primaryKey = 'id_psikososial';
    protected $guarded = [];

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'pasien_id', 'id_pasien');
    }
}
