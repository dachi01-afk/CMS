<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResumeDokter extends Model
{
    protected $table = 'resume_dokter';

    protected $fillable = [
        'emr_id','dokter_id',
        'ringkasan_kasus','diagnosis_utama','diagnosis_sekunder',
        'tindakan','terapi_ringkas','hasil_penunjang_ringkas',
        'kondisi_akhir','instruksi_pulang','rencana_tindak_lanjut',
        'status','finalized_at'
    ];

    public function emr()
    {
        return $this->belongsTo(Emr::class, 'emr_id');
    }

    public function dokter()
    {
        return $this->belongsTo(Dokter::class, 'dokter_id');
    }
}
