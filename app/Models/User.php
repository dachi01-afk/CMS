<?php

namespace App\Models;

use App\Support\Audit\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use Auditable, HasApiTokens, HasFactory, Notifiable;

    protected $table = 'user';

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'terakhir_login' => 'datetime',
        ];
    }

    protected $guarded = [];

    public function dokter()
    {
        return $this->hasOne(Dokter::class);
    }

    public function pasien()
    {
        return $this->hasOne(Pasien::class);
    }

    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    public function superAdmin()
    {
        return $this->hasOne(SuperAdmin::class);
    }

    public function farmasi()
    {
        return $this->hasOne(Farmasi::class);
    }

    public function kasir()
    {
        return $this->hasOne(Kasir::class);
    }

    public function auditor()
    {
        return $this->hasOne(Auditor::class);
    }

    /**
     * `terakhir_login` diperbarui otomatis oleh heartbeat setiap 60 detik.
     * Kolom ini tidak dicatat sebagai perubahan manajemen user supaya audit log
     * tidak penuh aktivitas presence yang berulang.
     */
    public function auditExcludedAttributes(): array
    {
        return ['terakhir_login'];
    }

    public function pengkajianAwalPenyakitDalamCreated()
    {
        return $this->hasMany(\App\Models\EmrPengkajianAwalPenyakitDalam::class, 'created_by');
    }

    public function pengkajianAwalPenyakitDalamUpdated()
    {
        return $this->hasMany(\App\Models\EmrPengkajianAwalPenyakitDalam::class, 'updated_by');
    }

    public function perawat()
    {
        return $this->hasOne(Perawat::class);
    }

    public function getNamaRoleAttribute()
    {
        return match ($this->role) {
            'Super Admin' => $this->superAdmin?->nama_super_admin ?? '-',
            'Admin' => $this->admin?->nama_admin ?? '-',
            'Dokter' => $this->dokter?->nama_dokter ?? '-',
            'Pasien' => $this->pasien?->nama_pasien ?? '-',
            'Farmasi' => $this->farmasi?->nama_farmasi ?? '-',
            'Perawat' => $this->perawat?->nama_perawat ?? '-',
            'Kasir' => $this->kasir?->nama_kasir ?? '-',
            'Auditor' => $this->auditor?->nama_auditor ?? '-',
            default => $this->name ?? '-',
        };
    }
}
