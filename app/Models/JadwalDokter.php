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
        $now = Carbon::now();
        $jamSekarang = $now->format('H:i:s');

        // kalau hari disimpan sebagai teks "Senin"
        $hariIni = $now->isoFormat('dddd');

        return $query->where('hari', $hariIni)
            ->whereTime('jam_awal', '<=', $jamSekarang)
            ->whereTime('jam_selesai', '>=', $jamSekarang);
    }
}
