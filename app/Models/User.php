<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLES = [
        'user'    => 'User',
        'kpisda'  => 'KPISDA',
        'kabalai' => 'Kepala Balai',
        'cctv'    => 'CCTV (hanya lihat CCTV)',
        'ppk'     => 'PPK (CCTV paket sendiri)',
        'admin'   => 'Super Admin',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'ppk_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canSeeAgendaKpisda(): bool
    {
        return in_array($this->role, ['admin', 'kpisda'], true);
    }

    public function canSeeAgendaKabalai(): bool
    {
        return in_array($this->role, ['admin', 'kabalai'], true);
    }

    /**
     * Akun yang hanya boleh membuka halaman CCTV: role "cctv" (semua kamera)
     * dan role "ppk" (hanya kamera di paket PPK-nya, lihat Cctv::scopeVisibleTo).
     */
    public function isCctvOnly(): bool
    {
        return in_array($this->role, ['cctv', 'ppk'], true);
    }

    /**
     * Kelola CCTV (tambah/pindah/hapus kamera): KPISDA, dan Super Admin.
     */
    public function canManageCctv(): bool
    {
        return in_array($this->role, ['admin', 'kpisda'], true);
    }

    /**
     * PPK yang ditautkan ke akun role "ppk".
     */
    public function ppk(): BelongsTo
    {
        return $this->belongsTo(Ppk::class);
    }
}
