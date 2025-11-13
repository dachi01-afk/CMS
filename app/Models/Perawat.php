<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perawat extends Model
{
    protected $table = "perawat";

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class);
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class);
    }

    public function perawat()
    {
        return $this->belongsTo(Perawat::class);
    }

    public function emr()
    {
        return $this->hasMany(Emr::class);
    }
}
