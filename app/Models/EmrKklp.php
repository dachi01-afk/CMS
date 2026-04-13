<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmrKklp extends Model
{
    use HasFactory;

    protected $table = 'emr_kklp';

    protected $guarded = [];

    public function emr()
    {
        return $this->belongsTo(Emr::class, 'emr_id');
    }

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class, 'kunjungan_id');
    }

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'dokter_id');
    }

    public function poli()
    {
        return $this->belongsTo(Poli::class, 'poli_id');
    }

    public function orangtua()
    {
        return $this->hasMany(EmrKklpOrangtua::class, 'emr_kklp_id');
    }

    public function heteroanamnesis()
    {
        return $this->hasOne(EmrKklpHeteroanamnesis::class, 'emr_kklp_id');
    }

    public function familyApgar()
    {
        return $this->hasOne(EmrKklpFamilyApgar::class, 'emr_kklp_id');
    }

    public function familyScreem()
    {
        return $this->hasOne(EmrKklpFamilyScreem::class, 'emr_kklp_id');
    }

    public function anggotaKeluarga()
    {
        return $this->hasMany(EmrKklpAnggotaKeluarga::class, 'emr_kklp_id');
    }

    public function homevisit()
    {
        return $this->hasMany(EmrKklpHomevisit::class, 'emr_kklp_id');
    }

    public function familyPlan()
    {
        return $this->hasMany(EmrKklpFamilyPlan::class, 'emr_kklp_id');
    }

    public function copcPlan()
    {
        return $this->hasMany(EmrKklpCopcPlan::class, 'emr_kklp_id');
    }

    public function ekstremitas()
    {
        return $this->hasMany(EmrKklpEkstremitas::class, 'emr_kklp_id');
    }
}