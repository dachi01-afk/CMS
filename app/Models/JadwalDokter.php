<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

class JadwalDokter extends Model
{
    protected $table = 'jadwal_dokter';

    protected $guarded = [];

    public function dokter()
    {
        return $this->belongsTo(Dokter::class);
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class);
    }

    public function dokterPoli()
    {
        // fk: jadwal_dokter.dokter_poli_id → dokter_poli.id
        return $this->belongsTo(DokterPoli::class);
    }

public function scopeAktifSekarang($query)
{
    $tz = config('app.timezone', 'Asia/Jakarta');
    $now = Carbon::now($tz);
    $jamSekarang = $now->format('H:i:s');

    // Support Indo & English (lowercase)
    $hariId = strtolower($now->locale('id')->isoFormat('dddd')); // senin
    $hariEn = strtolower($now->locale('en')->isoFormat('dddd')); // monday

    return $query->where(function($q) use ($hariId, $hariEn) {
            $q->whereRaw('LOWER(hari) = ?', [$hariId])
              ->orWhereRaw('LOWER(hari) = ?', [$hariEn]);
        })
        ->whereTime('jam_awal', '<=', $jamSekarang)
        ->whereTime('jam_selesai', '>=', $jamSekarang);
}

    public function orderLayanan()
    {
        return $this->hasMany(OrderLayanan::class);
    }
}
