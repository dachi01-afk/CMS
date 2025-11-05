<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // TAMBAHKAN INI

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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

    public function farmasi()
    {
        return $this->hasOne(Farmasi::class);
    }

    public function kasir()
    {
        return $this->hasOne(Kasir::class);
    }
}
