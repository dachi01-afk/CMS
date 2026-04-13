<?php

namespace App\Models;

use App\Models\Dokter;
use App\Models\Kunjungan;
use App\Models\Pasien;
use App\Models\Pembayaran;
use App\Models\Perawat;
use App\Models\Poli;
use App\Models\Resep;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EMR extends Model
{
    protected $table = 'emr';

    protected $guarded = [];

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class);
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class);
    }

    public function perawat()
    {
        return $this->belongsTo(Perawat::class);
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class);
    }

    public function resep()
    {
        return $this->belongsTo(Resep::class);
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class);
    }

    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'emr_id');
    }

    public function resumeDokter()
    {
        return $this->hasOne(\App\Models\ResumeDokter::class, 'emr_id');
    }

    public function scopeGetData($query)
    {
        return $query->with(['pasien', 'dokter', 'poli'])
            ->select('emr.*');
    }

    public function scopeFilterByPerawat($query, $perawatId)
    {
        return $query->where('perawat_id', $perawatId);
    }

<<<<<<< HEAD
    public function emrKklp()
    {
        return $this->hasOne(EmrKklp::class, 'emr_id');
    }

    public function pengkajianAwalPenyakitDalam()
    {
        return $this->hasOne(\App\Models\EmrPengkajianAwalPenyakitDalam::class, 'emr_id');
=======
    public function scopeHariIni(Builder $query, $tanggal = null)
    {
        $tanggal = $tanggal ?? now()->toDateString();

        return $query->whereDate('created_at', $tanggal);
>>>>>>> 966f6271fa8f074b540856c2d3d753633f8a11b1
    }
}
