<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DentalExamination extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'dental_examinations';

    protected $fillable = [
        'pasien_id',
        'kunjungan_id',
        'order_layanan_id',
        'tanggal_kunjungan',
        'dpjp_nama',
        'ppjp_nama',
        'gigi_dewasa_atas',
        'gigi_dewasa_bawah',
        'gigi_anak_atas',
        'gigi_anak_bawah',
        'occlusi',
        'torus_palatinus',
        'torus_mandibularis',
        'palatum',
        'diastema_ada',
        'diastema_keterangan',
        'gigi_anomali_ada',
        'gigi_anomali_keterangan',
        'lain_lain',
        'd_index',
        'm_index',
        'f_index',
        'jumlah_foto',
        'jenis_foto',
        'jumlah_rontgen',
        'jenis_rontgen',
        'diperiksa_oleh',
        'tanggal_pemeriksaan',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_kunjungan' => 'date',
        'tanggal_pemeriksaan' => 'date',
        'gigi_dewasa_atas' => 'array',
        'gigi_dewasa_bawah' => 'array',
        'gigi_anak_atas' => 'array',
        'gigi_anak_bawah' => 'array',
        'diastema_ada' => 'boolean',
        'gigi_anomali_ada' => 'boolean',
        'd_index' => 'integer',
        'm_index' => 'integer',
        'f_index' => 'integer',
        'jumlah_foto' => 'integer',
        'jumlah_rontgen' => 'integer',
    ];

    public function pasien()
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class, 'kunjungan_id');
    }

    public function orderLayanan()
    {
        return $this->belongsTo(OrderLayanan::class, 'order_layanan_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getDmfTotalAttribute()
    {
        return ($this->d_index ?? 0) + ($this->m_index ?? 0) + ($this->f_index ?? 0);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePasien($query, $pasienId)
    {
        return $query->where('pasien_id', $pasienId);
    }
}