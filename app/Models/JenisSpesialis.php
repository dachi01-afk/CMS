<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisSpesialis extends Model
{
    use HasFactory;

    protected $table = 'jenis_spesialis';

    protected $guarded = [];

    public function dokter()
    {
        return $this->hasOne(Dokter::class, 'jenis_spesialis_id', 'id');
    }
}
